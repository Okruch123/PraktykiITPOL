<?php
    require("config.php");
    ini_set('display_errors', 0);
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=utf-8');

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $email = trim($input['email'] ?? '');

    if (empty($email)) {
        echo json_encode([]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.ID as id,
                r.codeID as codeID,
                ri.court_id,
                ri.reservation_date as date,
                ri.start_time as begin,
                ri.end_time as end,
                ri.price
            FROM reservations r
            JOIN users u ON r.client_ID = u.ID
            JOIN reservation_items ri ON ri.reservation_id = r.ID
            WHERE u.email = ?
            ORDER BY id DESC
        ");
        $stmt->execute([$email]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($reservations);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
?>