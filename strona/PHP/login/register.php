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

    function writeDebugLog($message)
    {
        $logFile = __DIR__ . '/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }

    if (!($pdo instanceof PDO)) {
        writeDebugLog('BŁĄD: Nieprawidłowe połączenie PDO.');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą danych.']);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        $data = $_POST;
    }

    $email       = trim($data['email'] ?? '');
    $password    = $data['password'] ?? '';
    $confirmPass = $data['confirmPassword'] ?? ($data['confirm_password'] ?? '');
    $username    = trim($data['username'] ?? '');

    $firstName   = trim($data['first_name'] ?? ($username ?: 'Użytkownik'));
    $secondName  = trim($data['second_name'] ?? '');
    $surname     = trim($data['surname'] ?? 'Brak');
    $phoneNumber = trim($data['phone_number'] ?? '');

    if ($email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Wypełnij pola email oraz hasło.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Podano niepoprawny adres e-mail.']);
        exit;
    }

    if ($password !== $confirmPass) {
        echo json_encode(['success' => false, 'message' => 'Hasła nie są identyczne.']);
        exit;
    }

    try {
        $stmtUsers = $pdo->prepare(
            'SELECT ID FROM users WHERE email = ? LIMIT 1'
        );
        $stmtUsers->execute([$email]);

        if ($stmtUsers->fetchColumn() !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Konto z tym adresem e-mail już istnieje. Zaloguj się.'
            ]);
            exit;
        }

        $stmtPending = $pdo->prepare(
            'SELECT ID FROM pending_users WHERE email = ? LIMIT 1'
        );
        $stmtPending->execute([$email]);

        if ($stmtPending->fetchColumn() !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Link weryfikacyjny został już wysłany na ten adres e-mail.'
            ]);
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $verificationToken = bin2hex(random_bytes(16));
        $expirationDate = date('Y-m-d', strtotime('+1 day'));

        $stmtInsert = $pdo->prepare(
            'INSERT INTO pending_users
                (email, password_hash, phone_number, first_name, second_name, surname,
                verification_token, verification_expiration_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmtInsert->execute([
            $email,
            $passwordHash,
            $phoneNumber,
            $firstName,
            $secondName,
            $surname,
            $verificationToken,
            $expirationDate
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        writeDebugLog('BŁĄD: Operacja PDO nie powiodła się.');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Nie udało się zapisać rejestracji.']);
        exit;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $activationUrl = $protocol . '://' . $host
        . '/PraktykiITPOL/strona/PHP/login/verify.php?token='
        . urlencode($verificationToken);

    try {
        $smtpHost   = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $smtpPort   = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
        $smtpSecure = strtolower($_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
        $smtpUsername = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
        $smtpPassword = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
        $fromName   = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'SETPOINT Rezerwacje';

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
        $mail->addAddress($email, $firstName);
        $mail->isHTML(true);
        $mail->Subject = 'Potwierdzenie rejestracji - SETPOINT';

        $safeName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');
        $safeExpirationDate = htmlspecialchars($expirationDate, ENT_QUOTES, 'UTF-8');

        $mail->Body = "Witaj <b>{$safeName}</b>,<br><br>"
            . 'Aby aktywować konto w serwisie SETPOINT, kliknij poniższy link:<br>'
            . "<a href=\"{$safeUrl}\">{$safeUrl}</a><br><br>"
            . "Link jest ważny do: {$safeExpirationDate}";

        $mail->AltBody = "Witaj {$firstName}, aby aktywować konto przejdź pod adres: "
            . "{$activationUrl}. Link jest ważny do: {$expirationDate}";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'Rejestracja udana! Potwierdź link wysłany na e-mail, aby móc się zalogować.'
        ]);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
        writeDebugLog('BŁĄD: Nie udało się wysłać e-maila rejestracyjnego.');

        echo json_encode([
            'success' => false,
            'message' => 'Rejestracja została zapisana, ale nie udało się wysłać e-maila.'
        ]);
    }
?>