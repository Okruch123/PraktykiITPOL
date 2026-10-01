<?php
    require("config.php");

    header('Content-Type: application/json; charset=utf-8');

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    $email = trim($data['email'] ?? '');
    $sessionID = trim($data['sessionID'] ?? '');

    $stmt = $pdo->prepare(
        'SELECT 1
        FROM users AS u
        JOIN users_sessions AS s ON s.user_id = u.ID
        WHERE u.email = ?
        AND (s.session = ? OR s.session = ?)
        LIMIT 1'
    );

    $stmt->execute([$email, $sessionID, session_id()]);
    $result = $stmt->fetchColumn() !== false;

    echo json_encode(['success' => $result]);
?>