<?php
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    session_start();

    require_once(__DIR__ . '/../db_getters/config.php');

    if (!($pdo instanceof PDO)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą.']);
        exit;
    }

    if (!isset($_SESSION['pending_2fa_user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Brak aktywnej sesji weryfikacji 2FA. Zaloguj się ponownie.'
        ]);
        exit;
    }

    $userId = (int)$_SESSION['pending_2fa_user_id'];

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        $data = $_POST;
    }

    $code = trim($data['code'] ?? '');

    if (!preg_match('/^\d{6}$/', $code)) {
        echo json_encode(['success' => false, 'message' => 'Nieprawidłowy format kodu.']);
        exit;
    }

    try {
        $stmtCode = $pdo->prepare(
            'SELECT id, expires_at
            FROM two_factor_codes
            WHERE user_id = ? AND code = ? AND used = 0
            LIMIT 1'
        );
        $stmtCode->execute([$userId, $code]);
        $codeRow = $stmtCode->fetch(PDO::FETCH_ASSOC);

        if (!$codeRow) {
            echo json_encode(['success' => false, 'message' => 'Nieprawidłowy kod weryfikacyjny.']);
            exit;
        }

        if (strtotime($codeRow['expires_at']) < time()) {
            $stmtDelete = $pdo->prepare(
                'DELETE FROM two_factor_codes WHERE id = ?'
            );
            $stmtDelete->execute([$codeRow['id']]);

            echo json_encode(['success' => false, 'message' => 'Kod wygasł. Zaloguj się ponownie.']);
            exit;
        }

        $stmtUsed = $pdo->prepare(
            'UPDATE two_factor_codes SET used = 1 WHERE id = ? AND used = 0'
        );
        $stmtUsed->execute([$codeRow['id']]);

        if ($stmtUsed->rowCount() !== 1) {
            echo json_encode(['success' => false, 'message' => 'Kod został już wykorzystany.']);
            exit;
        }

        $stmtUser = $pdo->prepare(
            'SELECT id, email, is_admin
            FROM users
            WHERE id = ?
            LIMIT 1'
        );
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika.']);
            exit;
        }

        unset($_SESSION['pending_2fa_user_id']);
        session_regenerate_id(true);
        $sessionId = session_id();

        $stmtSession = $pdo->prepare(
            'INSERT INTO users_sessions (user_id, session) VALUES (?, ?)'
        );
        $stmtSession->execute([$userId, $sessionId]);

        echo json_encode([
            'success' => true,
            'message' => 'Zalogowano pomyślnie.',
            'user' => [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'is_admin' => $user['is_admin']
            ]
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Błąd zapytania do bazy danych.'
        ]);
    }
?>