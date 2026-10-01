<?php
    ob_start();
    header('Content-Type: application/json; charset=utf-8');

    try {
        $pdoPath = __DIR__ . '/../db_getters/config.php';

        if (!file_exists($pdoPath)) {
            throw new RuntimeException('Nie znaleziono pliku config.php.');
        }

        require_once($pdoPath);

        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new RuntimeException('Nieprawidłowe połączenie PDO.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userEmail = $_SESSION['user_email'] ?? ($_COOKIE['email'] ?? null);

        if (!$userEmail) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Brak autoryzacji. Zaloguj się ponownie.'
            ]);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            echo json_encode([
                'success' => false,
                'message' => 'Nieprawidłowe dane wejściowe.'
            ]);
            exit;
        }

        $fullName = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $oldPassword = $data['oldPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $surname = $nameParts[1] ?? '';

        // Pobierz dostępne kolumny tabeli przez PDO.
        $columnsResult = $pdo->query('SHOW COLUMNS FROM users');
        $dbColumns = [];

        while ($column = $columnsResult->fetch(PDO::FETCH_ASSOC)) {
            $dbColumns[] = $column['Field'];
        }

        // Pobierz użytkownika.
        $stmt = $pdo->prepare(
            'SELECT id, password_hash FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$userEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Nie znaleziono użytkownika w bazie.'
            ]);
            exit;
        }

        $userId = $user['id'];
        $currentPasswordHash = $user['password_hash'];

        // Obsługa zmiany hasła.
        $updatePassword = false;
        $newPasswordHash = '';

        if ($newPassword !== '') {
            if ($oldPassword === '') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Podaj stare hasło, aby je zmienić.'
                ]);
                exit;
            }

            if (!password_verify($oldPassword, $currentPasswordHash)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Stare hasło jest nieprawidłowe.'
                ]);
                exit;
            }

            if (strlen($newPassword) < 6) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nowe hasło musi mieć co najmniej 6 znaków.'
                ]);
                exit;
            }

            $updatePassword = true;
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Zbuduj UPDATE na podstawie rozpoznanych kolumn.
        $fieldsToUpdate = [];
        $values = [];

        if (in_array('first_name', $dbColumns, true)) {
            $fieldsToUpdate[] = 'first_name = ?';
            $values[] = $firstName;
        }

        if (in_array('surname', $dbColumns, true)) {
            $fieldsToUpdate[] = 'surname = ?';
            $values[] = $surname;
        }

        if (
            in_array('name', $dbColumns, true)
            && !in_array('first_name', $dbColumns, true)
        ) {
            $fieldsToUpdate[] = 'name = ?';
            $values[] = $fullName;
        }

        if (in_array('phone_number', $dbColumns, true)) {
            $fieldsToUpdate[] = 'phone_number = ?';
            $values[] = $phone;
        } elseif (in_array('phone', $dbColumns, true)) {
            $fieldsToUpdate[] = 'phone = ?';
            $values[] = $phone;
        }

        if ($updatePassword) {
            $fieldsToUpdate[] = 'password_hash = ?';
            $values[] = $newPasswordHash;
        }

        if (!$fieldsToUpdate) {
            throw new RuntimeException(
                'Nie znaleziono pasujących kolumn do aktualizacji w tabeli users.'
            );
        }

        $values[] = $userId;

        $sql = 'UPDATE users SET '
            . implode(', ', $fieldsToUpdate)
            . ' WHERE id = ?';

        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($values);

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Profil zaktualizowany pomyślnie.'
        ]);
    } catch (Throwable $e) {
        error_log($e->getMessage());

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Wystąpił błąd serwera.'
        ]);
    }
?>