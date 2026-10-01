<?php
require_once(__DIR__ . '/../db_getters/config.php');

header('Content-Type: application/json; charset=utf-8');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$amount    = floatval($input['amount'] ?? 0);
$courtId   = intval($input['courtId'] ?? 1);
$userId    = intval($input['userId'] ?? 0);
$email     = trim($input['email'] ?? '');
$dateStr   = trim($input['dateStr'] ?? date('Y-m-d'));

$startHour = intval($input['startHour'] ?? ($input['from'] ?? 0));
$endHour   = intval($input['endHour'] ?? ($input['to'] ?? 0));

if ($startHour <= 0 || $endHour <= 0 || $startHour >= $endHour) {
    echo json_encode(['error' => 'Niepoprawne godziny rezerwacji.']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['error' => 'Niepoprawna kwota.']);
    exit;
}

if ($userId <= 0 && !empty($email)) {
    $stmtUser = $pdo->prepare("SELECT ID FROM users WHERE email = ?");
    $stmtUser->execute([$email]);
    $userRow = $stmtUser->fetch();
    if ($userRow) {
        $userId = intval($userRow['ID']);
    }
}

if ($userId <= 0) {
    $stmtFirst = $pdo->query("SELECT ID FROM users ORDER BY ID ASC LIMIT 1");
    $firstUser = $stmtFirst->fetch();
    if ($firstUser) {
        $userId = intval($firstUser['ID']);
    } else {
        echo json_encode(['error' => 'Brak użytkowników w bazie danych.']);
        exit;
    }
}

$startHourInt = (int)$startHour;
$endHourInt   = (int)$endHour;

$codeID = 'SET-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
$sessionId = 'p24_' . time() . '_' . rand(100, 999);

try {
    $pdo->beginTransaction();

    $checkStmt = $pdo->prepare("
        SELECT ri.id FROM reservation_items ri
        JOIN reservations r ON ri.reservation_id = r.ID
        JOIN payments p ON p.reservation_id = r.ID
        WHERE ri.court_id = ? 
          AND ri.reservation_date = ? 
          AND p.status IN ('pending', 'paid')
          AND (ri.start_time < ? AND ri.end_time > ?)
    ");
    $checkStmt->execute([$courtId, $dateStr, $endHourInt, $startHourInt]);
    
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Wybrane godziny są już zajęte. Odśwież stronę i wybierz inny termin.']);
        exit;
    }

    $stmtRes = $pdo->prepare("INSERT INTO reservations (court_ID, client_ID, codeID) VALUES (?, ?, ?)");
    $stmtRes->execute([$courtId, $userId, $codeID]);
    $reservationId = $pdo->lastInsertId();
    
    $stmtItem = $pdo->prepare("
        INSERT INTO reservation_items (reservation_id, court_id, reservation_date, start_time, end_time, price) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtItem->execute([$reservationId, $courtId, $dateStr, $startHourInt, $endHourInt, $amount]);

    $stmtPay = $pdo->prepare("
        INSERT INTO payments (reservation_id, user_id, amount, currency, provider, status) 
        VALUES (?, ?, ?, 'PLN', 'przelewy24', 'pending')
    ");
    $stmtPay->execute([$reservationId, $userId, $amount]);
    $paymentId = $pdo->lastInsertId();

    $stmtTrans = $pdo->prepare("
        INSERT INTO payment_transactions (payment_id, session_id, status, amount) 
        VALUES (?, ?, 'pending', ?)
    ");
    $stmtTrans->execute([$paymentId, $sessionId, $amount]);

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Błąd bazy danych: ' . $e->getMessage()]);
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$returnUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/PraktykiITPOL/strona/?status=success&code=' . $codeID;

echo json_encode([
    'url' => $returnUrl,
    'codeID' => $codeID
]);
exit;
?>