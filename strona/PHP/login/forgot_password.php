<?php
// Wymuszenie czystego buforowania wyjścia, aby żadne ostrzeżenia PHP nie zepsuły formatu JSON
ob_start();

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
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');

if ($email === '') {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Podaj adres e-mail.']);
    exit;
}

// Sprawdź czy użytkownik istnieje
$stmt = mysqli_prepare($config, "SELECT id, email, first_name FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Błąd zapytania SQL.']);
    exit;
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $userId, $userEmail, $userFirstName);
$exists = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$exists) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Nie znaleziono konta z takim adresem e-mail.']);
    exit;
}

// Usuń stare kody resetowania dla tego użytkownika
$stmtDelete = mysqli_prepare($config, "DELETE FROM two_factor_codes WHERE user_id = ?");
mysqli_stmt_bind_param($stmtDelete, 'i', $userId);
mysqli_stmt_execute($stmtDelete);
mysqli_stmt_close($stmtDelete);

// Wygeneruj 6-cyfrowy kod (poprawiono time() + 600)
$code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minut ważności

// Zapisz kod w tabeli two_factor_codes (z action = 'reset')
$stmtCode = mysqli_prepare($config, "INSERT INTO two_factor_codes (user_id, code, action, expires_at) VALUES (?, ?, 'reset', ?)");
mysqli_stmt_bind_param($stmtCode, 'iss', $userId, $code, $expiresAt);
mysqli_stmt_execute($stmtCode);
mysqli_stmt_close($stmtCode);

// Wysyłka maila PHPMailer
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
    $mail->Subject = 'Resetowanie hasła - SETPOINT';
    $mail->Body = '
        <h2>Resetowanie hasła</h2>
        <p>Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta.</p>
        <p>Twój kod weryfikacyjny:</p>
        <h1 style="letter-spacing: 8px;">' . htmlspecialchars($code) . '</h1>
        <p>Kod jest ważny przez 10 minut.</p>
        <p>Jeżeli to nie Ty prosiłeś o zmianę hasła, zignoruj tę wiadomość.</p>
    ';
    $mail->AltBody = 'Twój kod resetowania hasła SETPOINT: ' . $code;
    $mail->send();
    
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Kod wysłany na e-mail.']);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Nie udało się wysłać e-maila z kodem.']);
}