<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../../PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/../db_getters/config.php');


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
        $content .= ' | Data: ' .
            json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    file_put_contents(
        $logFile,
        $content . PHP_EOL,
        FILE_APPEND
    );
}


writeDebugLog('--- START 2FA ---');


/*
|--------------------------------------------------------------------------
| BAZA
|--------------------------------------------------------------------------
*/

if (!$config) {

    writeDebugLog(
        'Błąd połączenia z bazą',
        mysqli_connect_error()
    );

    responseJson(
        false,
        'Błąd połączenia z bazą.'
    );
}


/*
|--------------------------------------------------------------------------
| SESJA
|--------------------------------------------------------------------------
*/

$sessionId = session_id();

if (!$sessionId) {

    responseJson(
        false,
        'Brak aktywnej sesji.'
    );
}


writeDebugLog(
    'Session ID',
    substr($sessionId, 0, 10) . '...'
);


/*
|--------------------------------------------------------------------------
| POBRANIE USER_ID Z users_sessions
|--------------------------------------------------------------------------
*/

$stmtSession = mysqli_prepare(
    $config,
    "SELECT user_id
     FROM users_sessions
     WHERE session = ?
     LIMIT 1"
);

if (!$stmtSession) {

    writeDebugLog(
        'Błąd prepare users_sessions',
        mysqli_error($config)
    );

    responseJson(
        false,
        'Błąd serwera.'
    );
}


mysqli_stmt_bind_param(
    $stmtSession,
    's',
    $sessionId
);

mysqli_stmt_execute($stmtSession);

$resultSession =
    mysqli_stmt_get_result($stmtSession);

$sessionUser =
    mysqli_fetch_assoc($resultSession);

mysqli_stmt_close($stmtSession);


if (!$sessionUser) {

    writeDebugLog(
        'Nie znaleziono sesji w users_sessions'
    );

    responseJson(
        false,
        'Sesja wygasła. Zaloguj się ponownie.'
    );
}


$userId = (int)$sessionUser['user_id'];


/*
|--------------------------------------------------------------------------
| POBRANIE UŻYTKOWNIKA
|--------------------------------------------------------------------------
*/

$stmtUser = mysqli_prepare(
    $config,
    "SELECT ID, email, first_name, twoFactorEnabled
     FROM users
     WHERE ID = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmtUser,
    'i',
    $userId
);

mysqli_stmt_execute($stmtUser);

$resultUser =
    mysqli_stmt_get_result($stmtUser);

$user =
    mysqli_fetch_assoc($resultUser);

mysqli_stmt_close($stmtUser);


if (!$user) {

    responseJson(
        false,
        'Nie znaleziono użytkownika.'
    );
}


$email =
    $user['email'];

$current2FA =
    (int)$user['twoFactorEnabled'];


/*
|--------------------------------------------------------------------------
| ODCZYT REQUESTU
|--------------------------------------------------------------------------
*/

$rawInput =
    file_get_contents('php://input');

$data =
    json_decode($rawInput, true) ?? $_POST;

$action =
    $data['action'] ?? '';


writeDebugLog(
    'Action',
    $action
);


/*
|--------------------------------------------------------------------------
| WYŚLIJ KOD
|--------------------------------------------------------------------------
*/

if ($action === 'send') {

    /*
     * Jeżeli obecnie 2FA = 0
     * kod będzie służył do WŁĄCZENIA.
     *
     * Jeżeli obecnie 2FA = 1
     * kod będzie służył do WYŁĄCZENIA.
     */

    $codeAction =
        ($current2FA === 1)
            ? 'disable'
            : 'enable';


    /*
     * Usuń stare/niewykorzystane kody użytkownika.
     */

    $stmtDelete = mysqli_prepare(
        $config,
        "DELETE FROM two_factor_codes
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmtDelete,
        'i',
        $userId
    );

    mysqli_stmt_execute($stmtDelete);

    mysqli_stmt_close($stmtDelete);


    /*
     * Losowy kod 6 cyfr.
     */

    $code =
        str_pad(
            (string)random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );


    /*
     * Ważność 10 minut.
     */

    $expiresAt =
        date(
            'Y-m-d H:i:s',
            time() + 600
        );


    /*
     * Zapis kodu.
     */

    $stmtCode = mysqli_prepare(
        $config,
        "INSERT INTO two_factor_codes
        (user_id, code, action, expires_at)
        VALUES (?, ?, ?, ?)"
    );

    if (!$stmtCode) {

        writeDebugLog(
            'Błąd INSERT kodu',
            mysqli_error($config)
        );

        responseJson(
            false,
            'Nie udało się wygenerować kodu.'
        );
    }


    mysqli_stmt_bind_param(
        $stmtCode,
        'isss',
        $userId,
        $code,
        $codeAction,
        $expiresAt
    );


    if (!mysqli_stmt_execute($stmtCode)) {

        writeDebugLog(
            'Błąd wykonania INSERT',
            mysqli_stmt_error($stmtCode)
        );

        mysqli_stmt_close($stmtCode);

        responseJson(
            false,
            'Nie udało się zapisać kodu.'
        );
    }


    mysqli_stmt_close($stmtCode);


    /*
     * MAIL
     */

    $mail =
        new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host =
            'smtp.gmail.com';

        $mail->SMTPAuth =
            true;

        $mail->Username =
            'oskarjablonski069@gmail.com';

        $mail->Password =
            'zlwd ypeb bfnq ycsv';

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            587;

        $mail->CharSet =
            'UTF-8';


        $mail->setFrom(
            'oskarjablonski069@gmail.com',
            'SETPOINT Rezerwacje'
        );

        $mail->addAddress(
            $email,
            $user['first_name'] ?? ''
        );


        $mail->isHTML(true);

        $mail->Subject =
            'Kod weryfikacyjny - SETPOINT';


        $mail->Body = '
            <h2>Weryfikacja dwuetapowa</h2>

            <p>
                Twój kod weryfikacyjny:
            </p>

            <h1 style="letter-spacing: 8px;">
                ' . htmlspecialchars($code) . '
            </h1>

            <p>
                Kod jest ważny przez 10 minut.
            </p>

            <p>
                Jeżeli to nie Ty wykonujesz tę operację,
                zignoruj tę wiadomość.
            </p>
        ';


        $mail->AltBody =
            'Twój kod weryfikacyjny SETPOINT: ' .
            $code .
            '. Kod jest ważny przez 10 minut.';


        $mail->send();


        writeDebugLog(
            'Kod wysłany',
            [
                'user_id' => $userId,
                'action' => $codeAction,
                'email' => $email
            ]
        );


        responseJson(
            true,
            'Kod został wysłany.'
        );


    } catch (Exception $e) {

        writeDebugLog(
            'PHPMailer error',
            $mail->ErrorInfo
        );


        /*
         * Jeżeli mail się nie wysłał,
         * usuwamy kod z bazy.
         */

        $stmtDelete = mysqli_prepare(
            $config,
            "DELETE FROM two_factor_codes
             WHERE user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmtDelete,
            'i',
            $userId
        );

        mysqli_stmt_execute($stmtDelete);

        mysqli_stmt_close($stmtDelete);


        responseJson(
            false,
            'Nie udało się wysłać wiadomości e-mail.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| POTWIERDZENIE KODU
|--------------------------------------------------------------------------
*/

if ($action === 'confirm') {

    $code =
        trim($data['code'] ?? '');


    if (!preg_match('/^\d{6}$/', $code)) {

        responseJson(
            false,
            'Nieprawidłowy kod.'
        );
    }


    /*
     * Pobieramy aktualny kod.
     */

    $stmtCode = mysqli_prepare(
        $config,
        "SELECT id, action, expires_at
         FROM two_factor_codes
         WHERE user_id = ?
         AND code = ?
         AND used = 0
         LIMIT 1"
    );


    mysqli_stmt_bind_param(
        $stmtCode,
        'is',
        $userId,
        $code
    );

    mysqli_stmt_execute($stmtCode);

    $resultCode =
        mysqli_stmt_get_result($stmtCode);

    $codeRow =
        mysqli_fetch_assoc($resultCode);

    mysqli_stmt_close($stmtCode);


    if (!$codeRow) {

        responseJson(
            false,
            'Nieprawidłowy kod.'
        );
    }


    /*
     * Sprawdzenie czasu.
     */

    if (
        strtotime($codeRow['expires_at'])
        < time()
    ) {

        /*
         * Usuwamy wygasły kod.
         */

        $stmtDelete = mysqli_prepare(
            $config,
            "DELETE FROM two_factor_codes
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmtDelete,
            'i',
            $codeRow['id']
        );

        mysqli_stmt_execute($stmtDelete);

        mysqli_stmt_close($stmtDelete);


        responseJson(
            false,
            'Kod wygasł. Wyślij nowy kod.'
        );
    }


    /*
     * Ustawienie 2FA.
     */

    $newValue =
        ($codeRow['action'] === 'enable')
            ? 1
            : 0;


    $stmtUpdate = mysqli_prepare(
        $config,
        "UPDATE users
         SET twoFactorEnabled = ?
         WHERE ID = ?
         LIMIT 1"
    );


    mysqli_stmt_bind_param(
        $stmtUpdate,
        'ii',
        $newValue,
        $userId
    );


    if (!mysqli_stmt_execute($stmtUpdate)) {

        writeDebugLog(
            'Błąd UPDATE twoFactorEnabled',
            mysqli_stmt_error($stmtUpdate)
        );

        mysqli_stmt_close($stmtUpdate);

        responseJson(
            false,
            'Nie udało się zmienić ustawienia 2FA.'
        );
    }


    mysqli_stmt_close($stmtUpdate);


    /*
     * Kod wykorzystany.
     */

    $stmtUsed = mysqli_prepare(
        $config,
        "UPDATE two_factor_codes
         SET used = 1
         WHERE id = ?"
    );


    mysqli_stmt_bind_param(
        $stmtUsed,
        'i',
        $codeRow['id']
    );

    mysqli_stmt_execute($stmtUsed);

    mysqli_stmt_close($stmtUsed);


    writeDebugLog(
        '2FA zmienione',
        [
            'user_id' => $userId,
            'new_value' => $newValue
        ]
    );


    responseJson(
        true,
        $newValue === 1
            ? 'Weryfikacja dwuetapowa została włączona.'
            : 'Weryfikacja dwuetapowa została wyłączona.',
        [
            'twoFactorEnabled' => $newValue
        ]
    );
}


responseJson(
    false,
    'Nieprawidłowa akcja.'
);