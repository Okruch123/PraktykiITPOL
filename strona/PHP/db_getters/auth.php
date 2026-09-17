<?php
    require("config.php");
    header('Content-Type: application/json; charset=utf-8');
    
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    $email = trim($data['email'] ?? '');

    $sql = mysqli_query($config, "SELECT ID FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($sql);
    $userid = $user["ID"];

    $sql2 = mysqli_query($config, "SELECT * FROM users_sessions WHERE user_id = '$userid'");
    $result = false;
    while($res = mysqli_fetch_array($sql2)){
        if($res["session"] == session_id()){
            $result = true;
        }
    }
    echo $result ? "true" : "false";
    json_encode(["result" => $result]);
?>