<?php
// Ustawienie strefy czasowej (dostosuj do swojej, np. Warsaw)
date_default_timezone_set('Europe/Warsaw');

$dbHost = 'localhost';
$dbName = 'praktyki_itpol';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych.");
}

$code = trim($_GET['code'] ?? '');

if (empty($code)) {
    displayError("Nie podano kodu dostępu.");
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            r.ID as reservation_id,
            r.codeID,
            ri.reservation_date,
            ri.start_time,
            ri.end_time,
            ri.price,
            c.name as court_name,
            c.address,
            s.name as surface_name
        FROM reservations r
        JOIN reservation_items ri ON ri.reservation_id = r.ID
        JOIN courts c ON ri.court_id = c.ID
        LEFT JOIN surfaces s ON c.surface_id = s.ID
        WHERE r.codeID = ?
        LIMIT 1
    ");
    $stmt->execute([$code]);
    $reservation = $stmt->fetch();

} catch (PDOException $e) {
    displayError("Błąd zapytania do bazy danych.");
}

if (!$reservation) {
    displayError("Nie znaleziono rezerwacji dla podanego kodu lub kod jest nieprawidłowy.");
}

// --- WERYFIKACIJA CZASU I DATY ---
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

$resDate = $reservation['reservation_date'];
// Dajemy 15 minut tolerancji przed rozpoczęciem (np. wejście o 11:45 na rezerwację od 12:00)
$startTimeWithBuffer = date('H:i:s', strtotime($reservation['start_time'] . ' -15 minutes'));
$endTime = $reservation['end_time'];

if ($resDate !== $currentDate) {
    if ($resDate > $currentDate) {
        displayError("❌ Za wcześnie! Ta rezerwacja jest zaplanowana na inny dzień: <strong>$resDate</strong>.");
    } else {
        displayError("❌ Ta rezerwacja już wygasła (odbyła się dnia $resDate).");
    }
}

if ($currentTime < $startTimeWithBuffer) {
    displayError("❌ Za wcześnie na wejście! Twoja rezerwacja zaczyna się o <strong>" . substr($reservation['start_time'], 0, 5) . "</strong> (wejście możliwe 15 min przed czasem).");
}

if ($currentTime > $endTime) {
    displayError("❌ Czas tej rezerwacji już minął (trwała do <strong>" . substr($endTime, 0, 5) . "</strong>).");
}
// ---------------------------------

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weryfikacja wejścia - SetPoint</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #121212; color: #fff; text-align: center; padding: 40px 20px; margin: 0; }
        .card { background: #1e1e1e; max-width: 420px; margin: 0 auto; padding: 30px; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); border: 1px solid #333; }
        .success-badge { background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid #4ade80; padding: 8px 16px; border-radius: 20px; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #2a2a2a; font-size: 15px; }
        .info-row span:first-child { color: #888; }
        .info-row span:last-child { font-weight: 600; color: #fff; text-align: right; }
        .action-box { background: rgba(74, 222, 128, 0.1); border: 1px dashed #4ade80; padding: 15px; border-radius: 10px; margin-top: 25px; color: #4ade80; font-size: 14px; line-height: 1.5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-badge">✓ Wstęp autoryzowany</div>
        
        <div style="font-size: 13px; color: #aaa; margin-bottom: 15px;">Token: #<?php echo htmlspecialchars($reservation['codeID']); ?></div>

        <div class="info-row">
            <span>Kort</span>
            <span><?php echo htmlspecialchars($reservation['court_name']); ?></span>
        </div>
        <div class="info-row">
            <span>Data</span>
            <span><?php echo htmlspecialchars($reservation['reservation_date']); ?></span>
        </div>
        <div class="info-row">
            <span>Godziny</span>
            <span><?php echo substr($reservation['start_time'], 0, 5); ?> – <?php echo substr($reservation['end_time'], 0, 5); ?></span>
        </div>
        <div class="info-row">
            <span>Lokalizacja</span>
            <span><?php echo htmlspecialchars($reservation['address']); ?></span>
        </div>

        <div class="action-box">
            💡 BRAMKA OTWARTA / ŚWIATŁA WŁĄCZONE! <br>
            <span style="font-size: 12px; font-weight: normal; color: #aaa;">Miłej gry!</span>
        </div>
    </div>
</body>
</html>

<?php
function displayError($message) {
    echo '<!DOCTYPE html><html lang="pl"><head><meta charset="UTF-8"><title>Błąd weryfikacji</title><style>body{font-family:sans-serif;background:#121212;color:#fff;text-align:center;padding:50px;}.box{background:#1e1e1e;max-width:400px;margin:0 auto;padding:30px;border-radius:12px;border:1px solid #333;color:#f87171;line-height:1.6;}</style></head><body><div class="box"><h2 style="margin-top:0;">Odmowa dostępu</h2><p>' . $message . '</p></div></body></html>';
    exit;
}
?>