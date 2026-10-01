<?php
    require("config.php");

    header('Content-Type: application/json; charset=utf-8');

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    $email = trim($data['email'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        echo json_encode(['success' => false]);
        exit;
    }

    echo json_encode([
        "name" => $res["first_name"]
            . ($res["second_name"] !== "" ? ' ' : '')
            . $res["second_name"]
            . ' '
            . $res["surname"],
        "email" => $res["email"],
        "phone" => $res["phone_number"],
        "twoFactorEnabled" => $res["twoFactorEnabled"]
    ]);
?>