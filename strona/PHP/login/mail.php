<?php
require_once(__DIR__ . '/../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (mysqli_stmt_execute($stmtInsert)) {
    mysqli_stmt_close($stmtInsert);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $activationUrl = "$protocol://$host/PraktykiITPOL/strona/PHP/login/verify.php?token=$verificationToken";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'oskarjablonski069@gmail.com';
        $mail->Password   = 'zlwd ypeb bfnq ycsv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug [$level]: $str");
        };

        $mail->setFrom('oskarjablonski069@gmail.com', 'SETPOINT Rezerwacje');
        $mail->addAddress($email, $firstName);

        $mail->isHTML(true);
        $mail->Subject = 'Potwierdzenie rejestracji - SETPOINT';
        $mail->Body    = "Witaj <b>" . htmlspecialchars($firstName) . "</b>,<br><br>Aby aktywować konto w serwisie SETPOINT, kliknij poniższy link:<br><a href='$activationUrl'>$activationUrl</a>";
        $mail->AltText = "Witaj $firstName, aby aktywować konto przejdź pod adres: $activationUrl";

        $mail->send();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rejestracja udana! E-mail został wysłany pomyślnie.'
        ]);

    } catch (Exception $e) {
        $errorMsg = "Błąd PHPMailer: " . $mail->ErrorInfo . " | Wyjątek: " . $e->getMessage();
        error_log($errorMsg);
        
        echo json_encode([
            'success' => false, 
            'message' => $errorMsg
        ]);
    }

} else {
    $sqlExecErr = mysqli_stmt_error($stmtInsert);
    writeDebugLog("BŁĄD EXECUTE SQL", $sqlExecErr);
    echo json_encode(['success' => false, 'message' => 'Nie udało się zarejestrować (SQL): ' . $sqlExecErr]);
    mysqli_stmt_close($stmtInsert);
}