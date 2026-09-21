<?php
header('Content-Type: application/json; charset=utf-8');

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
    echo json_encode(['error' => 'Błąd połączenia z bazą']);
    exit;
}

$courtId = intval($_GET['courtId'] ?? 1);
$dateStr = trim($_GET['dateStr'] ?? date('Y-m-d'));

// Pobieramy przedziały godzinowe dla aktywnych rezerwacji (status pending lub paid)
$stmt = $pdo->prepare("
    SELECT ri.start_time, ri.end_time 
    FROM reservation_items ri
    JOIN reservations r ON ri.reservation_id = r.ID
    JOIN payments p ON p.reservation_id = r.ID
    WHERE ri.court_id = ? 
      AND ri.reservation_date = ? 
      AND p.status IN ('pending', 'paid')
");
$stmt->execute([$courtId, $dateStr]);
$reservations = $stmt->fetchAll();

// Tworzymy tablicę zawierającą wszystkie pojedyncze godziny, które są zajęte
$bookedHours = [];
foreach ($reservations as $res) {
    $start = intval($res['start_time']);
    $end = intval($res['end_time']);
    
    // Jeśli rezerwacja trwa np. od 11 do 13, to zajęte są godziny 11 i 12
    for ($h = $start; $h < $end; $h++) {
        if (!in_array($h, $bookedHours)) {
            $bookedHours[] = $h;
        }
    }
}

echo json_encode(['bookedHours' => $bookedHours]);
exit;
?>