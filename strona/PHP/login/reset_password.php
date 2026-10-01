<?php
    header('Content-Type: application/json; charset=utf-8');

    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    require_once(__DIR__ . '/../db_getters/config.php');

    if (!($pdo instanceof PDO)) {
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
    $code = trim($data['code'] ?? '');
    $password = $data['password'] ?? '';

    if ($email === '' || $code === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Wypełnij wszystkie pola.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();

        if ($userId === false) {
            echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika.']);
            exit;
        }

        $stmtCode = $pdo->prepare(
            "SELECT id, expires_at
            FROM two_factor_codes
            WHERE user_id = ? AND code = ? AND action = 'reset'
            LIMIT 1"
        );
        $stmtCode->execute([$userId, $code]);
        $codeData = $stmtCode->fetch(PDO::FETCH_ASSOC);

        if (!$codeData) {
            echo json_encode(['success' => false, 'message' => 'Nieprawidłowy kod weryfikacyjny.']);
            exit;
        }

        if (strtotime($codeData['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'Kod wygasł. Wyślij nowy kod.']);
            exit;
        }

        $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmtUpdate = $pdo->prepare(
            'UPDATE users SET password_hash = ? WHERE id = ?'
        );
        $stmtUpdate->execute([$newPasswordHash, $userId]);

        $stmtDelete = $pdo->prepare(
            'DELETE FROM two_factor_codes WHERE id = ?'
        );
        $stmtDelete->execute([$codeData['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Hasło zostało pomyślnie zmienione.'
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