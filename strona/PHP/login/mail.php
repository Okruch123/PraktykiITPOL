<?php
require_once(__DIR__ . '/../db_getters/config.php');

require_once(__DIR__ . '/../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($stmtInsert) || !$stmtInsert instanceof PDOStatement || !$stmtInsert->execute()) {
        throw new RuntimeException('Nie udało się zapisać danych rejestracji.');
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $activationUrl = $protocol . '://' . $host
        . '/PraktykiITPOL/strona/PHP/login/verify.php?token='
        . urlencode($verificationToken);

    $smtpHost     = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpPort     = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
    $smtpSecure   = strtolower($_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
    $smtpUsername = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
    $smtpPassword = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
    $fromName     = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'SETPOINT Rezerwacje';

    if (!$smtpUsername || !$smtpPassword) {
        throw new RuntimeException('Brak konfiguracji SMTP.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;

    if ($smtpSecure === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($smtpSecure === 'tls' || $smtpSecure === 'starttls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    }

    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($smtpUsername, $fromName);
    $mail->addAddress($email, $firstName ?? '');
    $mail->isHTML(true);
    $mail->Subject = 'Potwierdzenie rejestracji - SETPOINT';

    $safeName = htmlspecialchars($firstName ?? '', ENT_QUOTES, 'UTF-8');
    $safeUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');

    $mail->Body = "Witaj <b>{$safeName}</b>,<br><br>"
        . "Aby aktywować konto w serwisie SETPOINT, kliknij poniższy link:<br>"
        . "<a href=\"{$safeUrl}\">{$safeUrl}</a>";
    $mail->AltBody = "Witaj " . ($firstName ?? '') . ", aby aktywować konto przejdź pod adres: "
        . $activationUrl;

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Rejestracja udana! E-mail został wysłany pomyślnie.'
    ]);
} catch (\Throwable $e) {
    error_log($e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Nie udało się zapisać rejestracji lub wysłać e-maila.'
    ]);
}
?>