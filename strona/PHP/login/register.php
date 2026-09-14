<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once(__DIR__ . '/../../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function writeDebugLog($message, $data = null) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $content = "[$timestamp] $message";
    if ($data !== null) {
        $content .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    file_put_contents($logFile, $content . PHP_EOL, FILE_APPEND);
}

writeDebugLog("--- ROZPOCZĘCIE ŻĄDANIA REJESTRACJI ---");

require_once('../db_getters/config.php');

if (!$config) {
    $err = mysqli_connect_error();
    writeDebugLog("BŁĄD: Połączenie z bazą nie powiodło się", $err);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą: ' . $err]);
    exit;
}

// Pobranie danych z requestu JSON lub POST
$rawInput = file_get_contents('php://input');
writeDebugLog("Odebrany raw input", $rawInput);

$data = json_decode($rawInput, true) ?? $_POST;
writeDebugLog("Zdekodowane dane JS", $data);

$email       = trim($data['email'] ?? '');
$password    = $data['password'] ?? '';
$confirmPass = $data['confirmPassword'] ?? ($data['confirm_password'] ?? '');
$username    = trim($data['username'] ?? '');

$firstName   = trim($data['first_name'] ?? ($username ?: 'Użytkownik'));
$secondName  = trim($data['second_name'] ?? '');
$surname     = trim($data['surname'] ?? 'Brak');
$phoneNumber = trim($data['phone_number'] ?? '');

// --- WALIDACJA ---

if (empty($email) || empty($password)) {
    writeDebugLog("WALIDACJA ODRZUCONA: Pusty email lub hasło");
    echo json_encode(['success' => false, 'message' => 'Wypełnij pola email oraz hasło.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    writeDebugLog("WALIDACJA ODRZUCONA: Zły format email", $email);
    echo json_encode(['success' => false, 'message' => 'Podano niepoprawny adres e-mail.']);
    exit;
}

if ($password !== $confirmPass) {
    writeDebugLog("WALIDACJA ODRZUCONA: Hasła nie są identyczne");
    echo json_encode(['success' => false, 'message' => 'Hasła nie są identyczne.']);
    exit;
}

// --- WERYFIKACJA DUBLATÓW ---

// 1. Tabela users
$stmtUsers = mysqli_prepare($config, "SELECT ID FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmtUsers, "s", $email);
mysqli_stmt_execute($stmtUsers);
mysqli_stmt_store_result($stmtUsers);

if (mysqli_stmt_num_rows($stmtUsers) > 0) {
    writeDebugLog("ODRZUCONO: Użytkownik istnieje w tabeli `users`", $email);
    echo json_encode(['success' => false, 'message' => 'Konto z tym adresem e-mail już istnieje. Zaloguj się.']);
    mysqli_stmt_close($stmtUsers);
    exit;
}
mysqli_stmt_close($stmtUsers);

// 2. Tabela pending_users
$stmtPending = mysqli_prepare($config, "SELECT ID FROM pending_users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmtPending, "s", $email);
mysqli_stmt_execute($stmtPending);
mysqli_stmt_store_result($stmtPending);

if (mysqli_stmt_num_rows($stmtPending) > 0) {
    writeDebugLog("ODRZUCONO: Użytkownik już w `pending_users`", $email);
    echo json_encode(['success' => false, 'message' => 'Link weryfikacyjny został już wysłany na ten adres e-mail.']);
    mysqli_stmt_close($stmtPending);
    exit;
}
mysqli_stmt_close($stmtPending);

// --- ZAPIS W `pending_users` ---

$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$verificationToken = bin2hex(random_bytes(16));
$expirationDate = date('Y-m-d', strtotime('+1 day'));

$insertQuery = "INSERT INTO pending_users 
    (email, password_hash, phone_number, first_name, second_name, surname, verification_token, verification_expiration_date) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmtInsert = mysqli_prepare($config, $insertQuery);

if (!$stmtInsert) {
    $dbErr = mysqli_error($config);
    writeDebugLog("BŁĄD SQL PREPARE", $dbErr);
    echo json_encode(['success' => false, 'message' => 'Błąd przygotowania SQL: ' . $dbErr]);
    exit;
}

mysqli_stmt_bind_param(
    $stmtInsert, 
    "ssssssss", 
    $email, 
    $passwordHash, 
    $phoneNumber, 
    $firstName, 
    $secondName, 
    $surname, 
    $verificationToken, 
    $expirationDate
);

if (mysqli_stmt_execute($stmtInsert)) {
    mysqli_stmt_close($stmtInsert);
    writeDebugLog("SUKCES: Dodano użytkownika do `pending_users`", ['email' => $email, 'token' => $verificationToken]);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $activationUrl = "$protocol://$host/PraktykiITPOL/strona/PHP/login/verify.php?token=$verificationToken";

    // --- WYSYŁKA MAILA PRZEZ PHPMAILER ---
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

        $mail->setFrom('oskarjablonski069@gmail.com', 'SETPOINT Rezerwacje');
        $mail->addAddress($email, $firstName);

        $mail->isHTML(true);
        $mail->Subject = 'Potwierdzenie rejestracji - SETPOINT';
        $mail->Body    = "Witaj <b>" . htmlspecialchars($firstName) . "</b>,<br><br>Aby aktywować konto w serwisie SETPOINT, kliknij poniższy link:<br><a href='$activationUrl'>$activationUrl</a><br><br>Link jest ważny do: " . $expirationDate;
        $mail->AltText = "Witaj $firstName, aby aktywować konto przejdź pod adres: $activationUrl";

        $mail->send();
        writeDebugLog("SUKCES: E-mail wysłany przez PHPMailer do", $email);

        echo json_encode([
            'success' => true, 
            'message' => 'Rejestracja udana! Potwierdź link wysłany na e-mail, aby móc się zalogować.'
        ]);

    } catch (Exception $e) {
        $errorMsg = "Błąd PHPMailer: " . $mail->ErrorInfo;
        writeDebugLog("BŁĄD PHPMailer", $errorMsg);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Rejestracja zapisana w bazie, ale nie wysłano maila: ' . $mail->ErrorInfo
        ]);
    }

} else {
    $sqlExecErr = mysqli_stmt_error($stmtInsert);
    writeDebugLog("BŁĄD EXECUTE SQL", $sqlExecErr);
    echo json_encode(['success' => false, 'message' => 'Nie udało się zarejestrować (SQL): ' . $sqlExecErr]);
    mysqli_stmt_close($stmtInsert);
}