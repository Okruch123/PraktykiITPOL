<?php
header('Content-Type: application/json');

// --- 1. POŁĄCZENIE Z BAZĄ DANYCH ---
$dbHost = 'localhost';
$dbName = 'praktyki_itpol'; // Nazwa Twojej bazy
$dbUser = 'root';            
$dbPass = '';                

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Błąd połączenia z bazą: ' . $e->getMessage()]);
    exit;
}

// --- 2. ODBIÓR DANYCH Z FRONTENDU (JS) ---
$input = json_decode(file_get_contents('php://input'), true);

$amount    = floatval($input['amount'] ?? 0);
$courtId   = intval($input['courtId'] ?? 1);
$clientId  = intval($input['userId'] ?? 1); // ID użytkownika z tabeli `users`
$dateStr   = trim($input['dateStr'] ?? date('Y-m-d'));
$startHour = intval($input['startHour'] ?? 10);
$endHour   = intval($input['endHour'] ?? 11);

if ($amount <= 0) {
    echo json_encode(['error' => 'Niepoprawna kwota']);
    exit;
}

// Formatowanie godzin pod typ TIME w MySQL (np. 10:00:00)
$startTime = sprintf('%02d:00:00', $startHour);
$endTime   = sprintf('%02d:00:00', $endHour);

// Identyfikatory i kody
$codeID = 'SET-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)); // Kod np. SET-A8F19B2C
$sessionId = 'p24_' . time() . '_' . rand(100, 999);

// --- 3. ZAPIS DO BAZY DANYCH (TRANSAKCJA SQL) ---
try {
    $pdo->beginTransaction();

    // A. Dodanie rekordu głównego w tabeli `reservations`
    $stmtRes = $pdo->prepare("INSERT INTO reservations (court_ID, client_ID, codeID) VALUES (?, ?, ?)");
    $stmtRes->execute([$courtId, $clientId, $codeID]);
    $reservationId = $pdo->lastInsertId();

    // B. Dodanie szczegółów terminu do `reservation_items`
    $stmtItem = $pdo->prepare("
        INSERT INTO reservation_items (reservation_id, court_id, reservation_date, start_time, end_time, price) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtItem->execute([$reservationId, $courtId, $dateStr, $startTime, $endTime, $amount]);

    // C. Utworzenie wpisu płatności w `payments` (status: pending)
    $stmtPay = $pdo->prepare("
        INSERT INTO payments (reservation_id, user_id, amount, currency, provider, status) 
        VALUES (?, ?, ?, 'PLN', 'przelewy24', 'pending')
    ");
    $stmtPay->execute([$reservationId, $clientId, $amount]);
    $paymentId = $pdo->lastInsertId();

    // D. Dodanie transakcji płatności z session_id w `payment_transactions`
    $stmtTrans = $pdo->prepare("
        INSERT INTO payment_transactions (payment_id, session_id, status, amount) 
        VALUES (?, ?, 'pending', ?)
    ");
    $stmtTrans->execute([$paymentId, $sessionId, $amount]);

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Błąd zapisu w bazie danych: ' . $e->getMessage()]);
    exit;
}

// --- 4. OBSŁUGA TEST MODE / MOCK ---
$TEST_MODE = true; 

if ($TEST_MODE) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // W trybie testowym zatwierdzamy płatność w bazie
    $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?")->execute([$paymentId]);
    $pdo->prepare("UPDATE payment_transactions SET status = 'completed' WHERE payment_id = ?")->execute([$paymentId]);

    $returnUrl = $protocol . '://' . $host . '/PraktykiITPOL/strona/?status=success&code=' . $codeID;
    
    echo json_encode([
        'url' => $returnUrl,
        'codeID' => $codeID
    ]);
    exit;
}

// --- 5. PRAWDZIWA INTEGRACJA P24 ---
$envPath = __DIR__ . '/../../restricted/p24.env';

if (!file_exists($envPath)) {
    echo json_encode(['error' => 'Brak pliku .env']);
    exit;
}

$env = parse_ini_file($envPath);

$merchantId = (int)($env['P24_MERCHANT_ID'] ?? 0);
$posId      = (int)($env['P24_POS_ID'] ?? $merchantId);
$crcKey     = trim($env['P24_CRC_KEY'] ?? '');
$apiKey     = trim($env['P24_API_KEY'] ?? '');
$baseUrl    = ($env['P24_MODE'] ?? 'sandbox') === 'sandbox' 
              ? 'https://sandbox.przelewy24.pl' 
              : 'https://secure.przelewy24.pl';

$amountInGrosze = (int)round($amount * 100);

$signData = json_encode([
    'sessionId'  => $sessionId,
    'merchantId' => $merchantId,
    'amount'     => $amountInGrosze,
    'currency'   => 'PLN',
    'crc'        => $crcKey
], JSON_UNESCAPED_SLASHES);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$returnUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/PraktykiITPOL/strona/?status=success&code=' . $codeID;

$payload = [
    'merchantId'  => $merchantId,
    'posId'       => $posId,
    'sessionId'   => $sessionId,
    'amount'      => $amountInGrosze,
    'currency'    => 'PLN',
    'description' => 'Rezerwacja kortu - Kod: ' . $codeID,
    'email'       => 'klient@example.com',
    'country'     => 'PL',
    'language'    => 'pl',
    'urlReturn'   => $returnUrl, 
    'sign'        => hash('sha384', $signData)
];

$ch = curl_init($baseUrl . '/api/v1/transaction/register');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($posId . ':' . $apiKey)
    ]
]);

$rawResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response = json_decode($rawResponse, true);

if (isset($response['data']['token'])) {
    echo json_encode([
        'url' => $baseUrl . '/trnRequest/' . $response['data']['token'],
        'codeID' => $codeID
    ]);
} else {
    echo json_encode(['error' => 'Błąd P24', 'httpCode' => $httpCode, 'details' => $response]);
}
?>