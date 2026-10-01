<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once(__DIR__ . '/../db_getters/config.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;

function respondJson(array $response, int $status = 200): void
{
    if (ob_get_level() > 0) {
        ob_clean();
    }

    http_response_code($status);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log('login.php: brak poprawnego połączenia PDO.');
    respondJson(['success' => false, 'message' => 'Błąd połączenia z bazą.'], 500);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    respondJson(['success' => false, 'message' => 'Podaj email i hasło.'], 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, email, first_name, password_hash, is_admin, twoFactorEnabled
         FROM users
         WHERE email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('login.php — błąd zapytania: ' . $e->getMessage());
    respondJson(['success' => false, 'message' => 'Błąd bazy danych.'], 500);
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    respondJson(['success' => false, 'message' => 'Nieprawidłowy email lub hasło.'], 401);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
session_regenerate_id(true);

$userId = (int)$user['id'];

if ((int)$user['twoFactorEnabled'] === 1) {
    $_SESSION['pending_2fa_user_id'] = $userId;

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    try {
        $pdo->beginTransaction();

        $stmtDelete = $pdo->prepare(
            'DELETE FROM two_factor_codes WHERE user_id = ?'
        );
        $stmtDelete->execute([$userId]);

        $stmtCode = $pdo->prepare(
            "INSERT INTO two_factor_codes (user_id, code, action, expires_at)
             VALUES (?, ?, 'enable', ?)"
        );
        $stmtCode->execute([$userId, $code, $expiresAt]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('login.php — błąd zapisu kodu 2FA: ' . $e->getMessage());
        respondJson(['success' => false, 'message' => 'Nie udało się przygotować weryfikacji.'], 500);
    }

    try {
        $smtpHost   = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $smtpPort   = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
        $smtpSecure = strtolower($_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
        $smtpUser   = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
        $smtpPass   = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
        $fromName   = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'SETPOINT Rezerwacje';

        if (!$smtpUser || !$smtpPass) {
            throw new RuntimeException('Brak konfiguracji SMTP.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;

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

        $mail->setFrom($smtpUser, $fromName);
        $mail->addAddress($user['email'], $user['first_name'] ?? '');
        $mail->isHTML(true);
        $mail->Subject = 'Kod weryfikacyjny logowania - SETPOINT';

        $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $mail->Body = '
            <h2>Weryfikacja dwuetapowa logowania</h2>
            <p>Twój kod weryfikacyjny:</p>
            <h1 style="letter-spacing: 8px;">' . $safeCode . '</h1>
            <p>Kod jest ważny przez 10 minut.</p>
        ';
        $mail->AltBody = 'Twój kod weryfikacyjny SETPOINT: '
            . $code . '. Kod jest ważny przez 10 minut.';

        $mail->send();
    } catch (Throwable $e) {
        error_log('========== SMTP ERROR ==========');
        error_log('Typ: ' . get_class($e));
        error_log('Błąd: ' . $e->getMessage());
        error_log('Plik: ' . $e->getFile());
        error_log('Linia: ' . $e->getLine());
        error_log('================================');

        respondJson([
            'success' => false,
            'message' => 'Błąd SMTP: ' . $e->getMessage()
        ], 500);
    }

    respondJson([
        'success' => true,
        'requires_2fa' => true,
        'message' => 'Kod weryfikacyjny został wysłany na e-mail.'
    ]);
}

try {
    $sessionId = session_id();

    $stmtSession = $pdo->prepare(
        'INSERT INTO users_sessions (user_id, session) VALUES (?, ?)'
    );
    $stmtSession->execute([$userId, $sessionId]);
} catch (PDOException $e) {
    error_log('login.php — błąd zapisu sesji: ' . $e->getMessage());
    respondJson(['success' => false, 'message' => 'Nie udało się utworzyć sesji.'], 500);
}

respondJson([
    'success' => true,
    'requires_2fa' => false,
    'message' => 'Zalogowano pomyślnie.',
    'user' => [
        'id' => $userId,
        'email' => $user['email'],
        'is_admin' => $user['is_admin']
    ]
]);
?>