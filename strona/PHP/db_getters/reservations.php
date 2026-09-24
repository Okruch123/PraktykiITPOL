<?php
header('Content-Type: application/json');

// --- 1. POŁĄCZENIE Z BAZĄ DANYCH ---
$dbHost = 'localhost';$dbName = 'praktyki_itpol';
$dbUser = 'root';$dbPass = '';                

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser,$dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Błąd połączenia z bazą: ' . $e->getMessage()]);
    exit;
}

// --- 2. ODBIÓR DANYCH Z FRONTENDU (JS) + DEBUG --- 
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Zapisujemy surowe dane do pliku debug_log.txt
file_put_contents('debug_log.txt', "Odebrano z JS:\n" . print_r($input, true) . "\n-----------------\n", FILE_APPEND);

$amount    = floatval($input['amount'] ?? 0);
$courtId   = intval($input['courtId'] ?? 1);
$userId    = intval($input['userId'] ?? 0);
$email     = trim($input['email'] ?? '');
$dateStr   = trim($input['dateStr'] ?? date('Y-m-d'));

// Bezpieczne pobranie godzin z różnych możliwych kluczy
$startHour = intval($input['startHour'] ?? ($input['from'] ?? ($input['begin'] ?? 0)));
$endHour   = intval($input['endHour'] ?? ($input['to'] ?? ($input['end'] ?? 0)));

// Sprawdzenie poprawności godzin
if ($startHour <= 0 || $endHour <= 0) {
    echo json_encode([
        'error' => 'Odebrano zerowe godziny z frontendu!',
        'debug_received' => $input
    ]);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['error' => 'Niepoprawna kwota']);
    exit;
}

// Pobranie ID użytkownika z bazy na podstawie e-maila
if ($userId <= 0 && !empty($email)) {
    $stmtUser =$pdo->prepare("SELECT ID FROM users WHERE email = ?");
    $stmtUser->execute([$email]);
    $userRow =$stmtUser->fetch();
    if ($userRow) {
        $userId = intval($userRow['ID']);
    }
}

// Jeśli nadal brak ID, bierzemy pierwszego użytkownika z tabeli
if ($userId <= 0) {
    $stmtFirst =$pdo->query("SELECT ID FROM users ORDER BY ID ASC LIMIT 1");
    $firstUser =$stmtFirst->fetch();
    if ($firstUser) {
        $userId = intval($firstUser['ID']);
    } else {
        echo json_encode(['error' => 'Brak użytkowników w bazie danych. Zarejestruj się najpierw.']);
        exit;
    }
}

// Jawne rzutowanie i formatowanie godzin
$startHourInt = (int)$startHour;
$endHourInt   = (int)$endHour;

$startTimeFormatted = sprintf('%02d:00:00', $startHourInt);
$endTimeFormatted   = sprintf('%02d:00:00', $endHourInt);

// Identyfikatory i kody
$codeID = 'SET-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
$sessionId = 'p24_' . time() . '_' . rand(100, 999);

// --- 3. ZAPIS DO BAZY DANYCH (TRANSAKCJA SQL + BLOKADA TERMINU) ---
try {
    $pdo->beginTransaction();

    // Sprawdzenie, czy termin nie został zajęty
    $checkStmt =$pdo->prepare("
        SELECT ri.id FROM reservation_items ri
        JOIN reservations r ON ri.reservation_id = r.ID
        JOIN payments p ON p.reservation_id = r.ID
        WHERE ri.court_id = ? 
          AND ri.reservation_date = ? 
          AND p.status IN ('pending', 'paid')
          AND (ri.start_time < ? AND ri.end_time > ?)
    ");
    $checkStmt->execute([$courtId,$dateStr, $endTimeFormatted,$startTimeFormatted]);
    
    if ($checkStmt->fetch()) {$pdo->rollBack();
        echo json_encode(['error' => 'Ten termin został właśnie zarezerwowany przez kogoś innego. Wybierz inną godzinę.']);
        exit;
    }

    // A. Dodanie rekordu głównego w `reservations`
    $stmtRes =$pdo->prepare("INSERT INTO reservations (court_ID, client_ID, codeID) VALUES (?, ?, ?)");
    $stmtRes->execute([$courtId, $userId,$codeID]);
    $reservationId =$pdo->lastInsertId();

    // B. Dodanie szczegółów do `reservation_items` z zabezpieczeniem błędu SQL
    try {
        $stmtItem =$pdo->prepare("
            INSERT INTO reservation_items (reservation_id, court_id, reservation_date, start_time, end_time, price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtItem->execute([$reservationId,$courtId, $dateStr,$startTimeFormatted, $endTimeFormatted,$amount]);
    } catch (PDOException $ex) {$pdo->rollBack();
        echo json_encode(['error' => 'Błąd SQL w reservation_items: ' . $ex->getMessage()]);
        exit;
    }

    // --- DODATKOWY DEBUG BAZY ---
    $debugCheck =$pdo->prepare("SELECT start_time, end_time FROM reservation_items WHERE reservation_id = ?");
    $debugCheck->execute([$reservationId]);
    $dbRecord =$debugCheck->fetch();
    // ----------------------------

    // C. Utworzenie wpisu płatności w `payments`
    $stmtPay =$pdo->prepare("
        INSERT INTO payments (reservation_id, user_id, amount, currency, provider, status) 
        VALUES (?, ?, ?, 'PLN', 'przelewy24', 'pending')
    ");
    $stmtPay->execute([$reservationId, $userId,$amount]);
    $paymentId =$pdo->lastInsertId();

    // D. Dodanie transakcji w `payment_transactions`
    $stmtTrans =$pdo->prepare("
        INSERT INTO payment_transactions (payment_id, session_id, status, amount) 
        VALUES (?, ?, 'pending', ?)
    ");
    $stmtTrans->execute([$paymentId, $sessionId,$amount]);

    $pdo->commit();

} catch (PDOException $e) {$pdo->rollBack();
    echo json_encode(['error' => 'Błąd zapisu w bazie danych: ' . $e->getMessage()]);
    exit;
}

// --- 4. OBSŁUGA TEST MODE / MOCK ---
$TEST_MODE = true; 

if ($TEST_MODE) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';$host = $_SERVER['HTTP_HOST'];$pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?")->execute([$paymentId]);
    $pdo->prepare("UPDATE payment_transactions SET status = 'completed' WHERE payment_id = ?")->execute([$paymentId]);

    $returnUrl =$protocol . '://' . $host . '/PraktykiITPOL/strona/?status=success&code=' .$codeID;
    
    echo json_encode([
        'url' => $returnUrl,
        'codeID' => $codeID,
        'saved_start' => $startTimeFormatted,
        'saved_end' => $endTimeFormatted,
        'db_actually_stored_start' => $dbRecord['start_time'] ?? 'BRAK',
        'db_actually_stored_end' => $dbRecord['end_time'] ?? 'BRAK'
    ]);
    exit;
}
?>