<?php
    require("config.php");
    header('Content-Type: application/json; charset=utf-8');
    
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    $email = trim($data['email'] ?? '');

    $sql = mysqli_query($config, "SELECT ID FROM users WHERE email = '$email'");
    $userid = mysqli_fetch_array($sql)["ID"];

    $sql = mysqli_query($config, "SELECT * FROM user_sessions WHERE user_id = '$userid'");
    $result = false;
    while($res = mysqli_fetch_array($sql)){
        if($res["session"] == session_id()){
            $result = true;
        }
    }
    json_encode(["result" => $result]);
?>