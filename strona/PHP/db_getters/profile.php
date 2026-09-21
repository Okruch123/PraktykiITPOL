<?php
    require("config.php");
    header('Content-Type: application/json');
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    $email = trim($data['email'] ?? '');

    $sql = mysqli_query($config, "SELECT * FROM users WHERE email = '$email'");
    $res = mysqli_fetch_assoc($sql);
    echo json_encode([
        "name" => $res["first_name"].($res["second_name"] != "" ? ' ' : '').$res["second_name"].' '.$res["surname"],
        "email" => $email,
        "phone" => $res["phone_number"]
    ]);
?>