<?php
    header('Content-Type: application/json');

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
        echo json_encode([]);
        exit;
    }

    // Odbieramy dane przesłane przez JavaScript (JSON z adresem e-mail)
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $email = trim($input['email'] ?? '');

    if (empty($email)) {
        echo json_encode([]);
        exit;
    }

    try {
        // Pobieramy rezerwacje powiązane z danym użytkownikiem przez jego e-mail
        $stmt = $pdo->prepare("
            SELECT 
                r.ID as id,
                ri.court_id,
                ri.reservation_date as date,
                ri.start_time as begin,
                ri.end_time as end,
                ri.price
            FROM reservations r
            JOIN users u ON r.client_ID = u.ID
            JOIN reservation_items ri ON ri.reservation_id = r.ID
            WHERE u.email = ?
            ORDER BY ri.reservation_date DESC, ri.start_time DESC
        ");
        $stmt->execute([$email]);
        $reservations = $stmt->fetchAll();

        // Zwracamy czysty JSON z tablicą rezerwacji
        echo json_encode($reservations);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
?>