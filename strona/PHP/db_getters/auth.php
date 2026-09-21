<?php
    require("config.php");
    header('Content-Type: application/json; charset=utf-8');
    
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    $email = trim($data['email'] ?? '');
    $sessionID = trim($data['sessionID'] ?? '');

    // Zabezpieczenie przed SQL injection
    $email = mysqli_real_escape_string($config, $email);

    $sql = mysqli_query($config, "SELECT ID FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($sql);
    $userid = $user["ID"] ?? 0;

    $result = false;

    if ($userid) {
        $sql2 = mysqli_query($config, "SELECT * FROM users_sessions WHERE user_id = '$userid'");
        while($res = mysqli_fetch_array($sql2)){
            // Sprawdzamy czy sesja z bazy zgadza się z tą przesłaną z ciasteczek
            if($res["session"] == $sessionID || $res["session"] == session_id()){
                $result = true;
                break;
            }
        }
    }

    // KLUCZOWE: Użycie "echo" oraz zmiana klucza na "success", aby JS poprawnie to odczytał
    echo json_encode(["success" => $result]);
?>