<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once(__DIR__ . '/../db_getters/config.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!$config) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd połączenia z bazą.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Podaj email i hasło.'
    ]);
    exit;
}

$stmt = mysqli_prepare(
    $config,
    "SELECT id, email, first_name, password_hash, is_admin, twoFactorEnabled
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd przygotowania zapytania.'
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd wykonania zapytania.'
    ]);
    exit;
}

mysqli_stmt_bind_result(
    $stmt,
    $userId,
    $userEmail,
    $userFirstName,
    $userPassword,
    $user_is_admin,
    $twoFactorEnabled
);

if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy email lub hasło.'
    ]);
    exit;
}

mysqli_stmt_close($stmt);

if (!password_verify($password, $userPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy email lub hasło.'
    ]);
    exit;
}

session_start();

// Jeśli 2FA jest włączone dla tego konta
if ((int)$twoFactorEnabled === 1) {
    $_SESSION['pending_2fa_user_id'] = $userId;

    // Usuń stare kody użytkownika
    $stmtDelete = mysqli_prepare(
        $config,
        "DELETE FROM two_factor_codes WHERE user_id = ?"
    );
    mysqli_stmt_bind_param($stmtDelete, 'i', $userId);
    mysqli_stmt_execute($stmtDelete);
    mysqli_stmt_close($stmtDelete);

    // Wygeneruj 6-cyfrowy kod
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    // Zapisz kod w bazie
    $stmtCode = mysqli_prepare(
        $config,
        "INSERT INTO two_factor_codes (user_id, code, action, expires_at) VALUES (?, ?, 'enable', ?)"
    );
    mysqli_stmt_bind_param($stmtCode, 'iss', $userId, $code, $expiresAt);
    mysqli_stmt_execute($stmtCode);
    mysqli_stmt_close($stmtCode);

    // Wyślij e-mail przez PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'oskarjablonski069@gmail.com';
        $mail->Password = 'zlwd ypeb bfnq ycsv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('oskarjablonski069@gmail.com', 'SETPOINT Rezerwacje');
        $mail->addAddress($userEmail, $userFirstName ?? '');
        $mail->isHTML(true);
        $mail->Subject = 'Kod weryfikacyjny logowania - SETPOINT';
        $mail->Body = '
            <h2>Weryfikacja dwuetapowa logowania</h2>
            <p>Twój kod weryfikacyjny:</p>
            <h1 style="letter-spacing: 8px;">' . htmlspecialchars($code) . '</h1>
            <p>Kod jest ważny przez 10 minut.</p>
            <p>Jeżeli to nie Ty próbujesz się zalogować, zignoruj tę wiadomość.</p>
        ';
        $mail->AltBody = 'Twój kod weryfikacyjny SETPOINT: ' . $code . '. Kod jest ważny przez 10 minut.';
        $mail->send();
    } catch (Exception $e) {
        // Obsługa błędu wysyłki
    }

    echo json_encode([
        'success' => true,
        'requires_2fa' => true,
        'message' => 'Wymagana weryfikacja dwuetapowa. Wprowadź kod wysłany na e-mail.'
    ]);
    exit;
}

// Standardowe logowanie (gdy 2FA jest wyłączone)
session_regenerate_id(true);
$sessionId = session_id();

$stmt = mysqli_prepare(
    $config,
    "INSERT INTO users_sessions (user_id, session) VALUES (?, ?)"
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Nie udało się utworzyć sesji.'
    ]);
    exit;
}
mysqli_stmt_bind_param($stmt, "is", $userId, $sessionId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'requires_2fa' => false,
    'message' => 'Zalogowano pomyślnie.',
    'user' => [
        'id' => (int)$userId,
        'email' => $userEmail,
        'is_admin' => $user_is_admin
    ]
]);