<?php
    require_once(__DIR__ . '/../db_getters/config.php');

    $mainPageUrl = '../../index.php';

    function redirectVerification($mainPageUrl, $status, $message)
    {
        $query = http_build_query([
            'verify_status' => $status,
            'msg' => $message
        ]);

        header("Location: {$mainPageUrl}?{$query}");
        exit;
    }

    if (!($pdo instanceof PDO)) {
        http_response_code(500);
        exit('Błąd połączenia z bazą danych.');
    }

    $token = trim($_GET['token'] ?? '');

    if ($token === '') {
        redirectVerification($mainPageUrl, 'error', 'Nie podano tokenu aktywacyjnego.');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT ID, email, password_hash, phone_number, first_name,
                    second_name, surname, verification_expiration_date
            FROM pending_users
            WHERE verification_token = ?
            LIMIT 1
            FOR UPDATE'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $pdo->rollBack();
            redirectVerification(
                $mainPageUrl,
                'error',
                'Nieprawidłowy lub już wykorzystany link aktywacyjny.'
            );
        }

        if (strtotime($row['verification_expiration_date']) < time()) {
            $stmtDelete = $pdo->prepare('DELETE FROM pending_users WHERE ID = ?');
            $stmtDelete->execute([$row['ID']]);
            $pdo->commit();

            redirectVerification(
                $mainPageUrl,
                'error',
                'Link aktywacyjny wygasł. Zarejestruj się ponownie.'
            );
        }

        $stmtInsert = $pdo->prepare(
            'INSERT INTO users
                (email, password_hash, phone_number, first_name, second_name, surname)
            VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmtInsert->execute([
            $row['email'],
            $row['password_hash'],
            $row['phone_number'],
            $row['first_name'],
            $row['second_name'],
            $row['surname']
        ]);

        $stmtDelete = $pdo->prepare('DELETE FROM pending_users WHERE ID = ?');
        $stmtDelete->execute([$row['ID']]);

        $pdo->commit();

        redirectVerification(
            $mainPageUrl,
            'success',
            'Konto aktywowane pomyślnie! Możesz się zalogować.'
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        redirectVerification(
            $mainPageUrl,
            'error',
            'Błąd podczas aktywowania konta.'
        );
    }
?>