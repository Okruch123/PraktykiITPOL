<?php
    header('Content-Type: application/json; charset=utf-8');

    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);

    session_start();

    require_once(__DIR__ . '/../db_getters/config.php');
    require_once(__DIR__ . '/../../../PHPMailer-master/src/Exception.php');
    require_once(__DIR__ . '/../../../PHPMailer-master/src/PHPMailer.php');
    require_once(__DIR__ . '/../../../PHPMailer-master/src/SMTP.php');

    use PHPMailer\PHPMailer\PHPMailer;

    function responseJson($success, $message, $extra = [])
    {
        echo json_encode(
            array_merge([
                'success' => $success,
                'message' => $message
            ], $extra),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    function writeDebugLog($message, $data = null)
    {
        $logFile = __DIR__ . '/two_factor_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $content = "[$timestamp] $message";

        if ($data !== null) {
            $content .= ' | Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        file_put_contents($logFile, $content . PHP_EOL, FILE_APPEND);
    }

    if (!($pdo instanceof PDO)) {
        writeDebugLog('Nieprawidłowe połączenie PDO.');
        responseJson(false, 'Błąd połączenia z bazą.');
    }

    $sessionId = session_id();

    if ($sessionId === '') {
        responseJson(false, 'Brak aktywnej sesji.');
    }

    try {
        $stmtSession = $pdo->prepare(
            'SELECT user_id FROM users_sessions WHERE session = ? LIMIT 1'
        );
        $stmtSession->execute([$sessionId]);
        $sessionUser = $stmtSession->fetch(PDO::FETCH_ASSOC);

        if (!$sessionUser) {
            responseJson(false, 'Sesja wygasła. Zaloguj się ponownie.');
        }

        $userId = (int)$sessionUser['user_id'];

        $stmtUser = $pdo->prepare(
            'SELECT ID, email, first_name, twoFactorEnabled
            FROM users
            WHERE ID = ?
            LIMIT 1'
        );
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            responseJson(false, 'Nie znaleziono użytkownika.');
        }

        $email = $user['email'];
        $current2FA = (int)$user['twoFactorEnabled'];

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        $action = $data['action'] ?? '';

        if ($action === 'send') {
            $codeAction = ($current2FA === 1) ? 'disable' : 'enable';

            $stmtDelete = $pdo->prepare(
                'DELETE FROM two_factor_codes WHERE user_id = ?'
            );
            $stmtDelete->execute([$userId]);

            $code = str_pad(
                (string)random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT
            );
            $expiresAt = date('Y-m-d H:i:s', time() + 600);

            $stmtCode = $pdo->prepare(
                'INSERT INTO two_factor_codes (user_id, code, action, expires_at)
                VALUES (?, ?, ?, ?)'
            );
            $stmtCode->execute([$userId, $code, $codeAction, $expiresAt]);

            try {
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
                $mail->addAddress($email, $user['first_name'] ?? '');
                $mail->isHTML(true);
                $mail->Subject = 'Kod weryfikacyjny - SETPOINT';
                $mail->Body = '
                    <h2>Weryfikacja dwuetapowa</h2>
                    <p>Twój kod weryfikacyjny:</p>
                    <h1 style="letter-spacing: 8px;">'
                        . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') .
                    '</h1>
                    <p>Kod jest ważny przez 10 minut.</p>
                    <p>Jeżeli to nie Ty wykonujesz tę operację, zignoruj tę wiadomość.</p>
                ';
                $mail->AltBody = 'Twój kod weryfikacyjny SETPOINT: '
                    . $code . '. Kod jest ważny przez 10 minut.';
                $mail->send();

                writeDebugLog('Kod 2FA wysłany.', [
                    'user_id' => $userId,
                    'action' => $codeAction
                ]);

                responseJson(true, 'Kod został wysłany.');
            } catch (\Throwable $e) {
                error_log($e->getMessage());

                $stmtDelete = $pdo->prepare(
                    'DELETE FROM two_factor_codes WHERE user_id = ?'
                );
                $stmtDelete->execute([$userId]);

                responseJson(false, 'Nie udało się wysłać wiadomości e-mail.');
            }
        }

        if ($action === 'confirm') {
            $code = trim($data['code'] ?? '');

            if (!preg_match('/^\d{6}$/', $code)) {
                responseJson(false, 'Nieprawidłowy kod.');
            }

            $stmtCode = $pdo->prepare(
                'SELECT id, action, expires_at
                FROM two_factor_codes
                WHERE user_id = ? AND code = ? AND used = 0
                LIMIT 1'
            );
            $stmtCode->execute([$userId, $code]);
            $codeRow = $stmtCode->fetch(PDO::FETCH_ASSOC);

            if (!$codeRow) {
                responseJson(false, 'Nieprawidłowy kod.');
            }

            if (strtotime($codeRow['expires_at']) < time()) {
                $stmtDelete = $pdo->prepare(
                    'DELETE FROM two_factor_codes WHERE id = ?'
                );
                $stmtDelete->execute([$codeRow['id']]);

                responseJson(false, 'Kod wygasł. Wyślij nowy kod.');
            }

            $newValue = ($codeRow['action'] === 'enable') ? 1 : 0;

            $stmtUpdate = $pdo->prepare(
                'UPDATE users SET twoFactorEnabled = ? WHERE ID = ? LIMIT 1'
            );
            $stmtUpdate->execute([$newValue, $userId]);

            $stmtUsed = $pdo->prepare(
                'UPDATE two_factor_codes SET used = 1 WHERE id = ?'
            );
            $stmtUsed->execute([$codeRow['id']]);

            writeDebugLog('Ustawienie 2FA zmienione.', [
                'user_id' => $userId,
                'new_value' => $newValue
            ]);

            responseJson(
                true,
                $newValue === 1
                    ? 'Weryfikacja dwuetapowa została włączona.'
                    : 'Weryfikacja dwuetapowa została wyłączona.',
                ['twoFactorEnabled' => $newValue]
            );
        }

        responseJson(false, 'Nieprawidłowa akcja.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        writeDebugLog('Błąd zapytania PDO.');
        http_response_code(500);
        responseJson(false, 'Błąd zapytania do bazy danych.');
    } catch (\Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        responseJson(false, 'Wystąpił błąd serwera.');
    }
?>