<?php
    require("config.php");
    header('Content-Type: application/json; charset=utf-8');

    $courtId = intval($_GET['courtId'] ?? 1);
    $dateStr = trim($_GET['dateStr'] ?? date('Y-m-d'));

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

    $bookedHours = [];
    foreach ($reservations as $res) {
        $start = intval($res['start_time']);
        $end = intval($res['end_time']);
        
        for ($h = $start; $h < $end; $h++) {
            if (!in_array($h, $bookedHours)) {
                $bookedHours[] = $h;
            }
        }
    }

    echo json_encode(['bookedHours' => $bookedHours]);
    exit;
?>