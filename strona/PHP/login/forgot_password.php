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

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log('forgot_password.php: brak poprawnego połączenia PDO.');
    respondJson([
        'success' => false,
        'message' => 'Błąd połączenia z bazą.'
    ], 500);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');

if ($email === '') {
    respondJson([
        'success' => false,
        'message' => 'Podaj adres e-mail.'
    ], 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, email, first_name
         FROM users
         WHERE email = ?
         LIMIT 1'
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respondJson([
            'success' => false,
            'message' => 'Nie znaleziono konta z takim adresem e-mail.'
        ], 404);
    }

    $stmtDelete = $pdo->prepare(
        'DELETE FROM two_factor_codes
         WHERE user_id = ?'
    );

    $stmtDelete->execute([
        $user['id']
    ]);

    $code = str_pad(
        (string) random_int(0, 999999),
        6,
        '0',
        STR_PAD_LEFT
    );

    $expiresAt = date(
        'Y-m-d H:i:s',
        time() + 600
    );

    $stmtCode = $pdo->prepare(
        "INSERT INTO two_factor_codes
            (user_id, code, action, expires_at)
         VALUES
            (?, ?, 'reset', ?)"
    );

    $stmtCode->execute([
        $user['id'],
        $code,
        $expiresAt
    ]);

} catch (PDOException $e) {

    error_log(
        'forgot_password.php — błąd bazy: '
        . $e->getMessage()
    );

    respondJson([
        'success' => false,
        'message' => 'Błąd zapytania do bazy danych.'
    ], 500);
}

try {
    $smtpHost   = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpPort   = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
    $smtpSecure = strtolower($_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
    $smtpUsername = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
    $smtpPassword = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
    $fromName   = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'SETPOINT Rezerwacje';

    if (!$smtpUsername || !$smtpPassword) {

        error_log(
            'forgot_password.php — brak SMTP_USERNAME lub SMTP_PASSWORD.'
        );

        throw new RuntimeException(
            'Brak konfiguracji SMTP.'
        );
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

    $mail->setFrom(
        $smtpUsername,
        $fromName
    );

    $mail->addAddress(
        $user['email'],
        $user['first_name'] ?? ''
    );

    $mail->isHTML(true);

    $mail->Subject = 'Resetowanie hasła - SETPOINT';

    $safeCode = htmlspecialchars(
        $code,
        ENT_QUOTES,
        'UTF-8'
    );

    $mail->Body = '
        <h2>Resetowanie hasła</h2>

        <p>
            Otrzymaliśmy prośbę o zresetowanie hasła
            do Twojego konta SETPOINT.
        </p>

        <p>
            Twój kod weryfikacyjny:
        </p>

        <h1 style="letter-spacing: 8px;">
            ' . $safeCode . '
        </h1>

        <p>
            Kod jest ważny przez 10 minut.
        </p>

        <p>
            Jeżeli to nie Ty prosiłeś o zmianę hasła,
            zignoruj tę wiadomość.
        </p>
    ';

    $mail->AltBody =
        'Twój kod resetowania hasła SETPOINT: '
        . $code
        . '. Kod jest ważny przez 10 minut.';

    $mail->send();

    respondJson([
        'success' => true,
        'message' => 'Kod wysłany na e-mail.'
    ]);

} catch (Throwable $e) {

    error_log(
        'forgot_password.php — błąd wysyłania e-maila: '
        . $e->getMessage()
    );

    respondJson([
        'success' => false,
        'message' => 'Nie udało się wysłać e-maila z kodem.'
    ], 500);
}