<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    $configPath = __DIR__ . '/../db_getters/config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Nie znaleziono pliku config.php w ścieżce: " . $configPath);
    }

    require_once($configPath);

    if (!isset($config) || !$config) {
        throw new Exception('Brak połączenia z bazą danych w config.php.');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userEmail = $_SESSION['user_email'] ?? ($_COOKIE['email'] ?? null);

    if (!$userEmail) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Brak autoryzacji. Zaloguj się ponownie.']);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane wejściowe.']);
        exit;
    }

    $fullName = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $oldPassword =$data['oldPassword'] ?? '';
    $newPassword =$data['newPassword'] ?? '';

    // Rozdzielamy pełne imię i nazwisko na imię oraz nazwisko (jeśli podano spację)
    $nameParts = explode(' ',$fullName, 2);
    $firstName =$nameParts[0] ?? '';
    $surname =$nameParts[1] ?? '';

    // Pobierz istniejące kolumny w tabeli `users`, aby skrypt automatycznie dostosował zapytanie
    $columnsResult = mysqli_query($config, "SHOW COLUMNS FROM users");
    $dbColumns = [];
    while ($col = mysqli_fetch_assoc($columnsResult)) {
        $dbColumns[] =$col['Field'];
    }

    // Pobierz użytkownika
    $stmt = mysqli_prepare($config, "SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Błąd zapytania SELECT: ' . mysqli_error($config));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $userEmail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId,$currentPasswordHash);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika w bazie.']);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Obsługa zmiany hasła
    $updatePassword = false;
    $newPasswordHash = '';

    if (!empty($newPassword)) {
        if (empty($oldPassword)) {
            echo json_encode(['success' => false, 'message' => 'Podaj stare hasło, aby je zmienić.']);
            exit;
        }

        if (!password_verify($oldPassword,$currentPasswordHash)) {
            echo json_encode(['success' => false, 'message' => 'Stare hasło jest nieprawidłowe.']);
            exit;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Nowe hasło musi mieć co najmniej 6 znaków.']);
            exit;
        }

        $updatePassword = true;
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Dynamiczne budowanie zapytania UPDATE na podstawie kolumn dostępnych w tabeli `users`
    $fieldsToUpdate = [];
    $types = '';$values = [];

    // Sprawdzenie kolumn imienia/nazwiska
    if (in_array('first_name', $dbColumns)) {$fieldsToUpdate[] = "first_name = ?";
        $types .= 's';
        $values[] =$firstName;
    }
    if (in_array('surname', $dbColumns)) {$fieldsToUpdate[] = "surname = ?";
        $types .= 's';
        $values[] =$surname;
    }
    // Awaryjnie, gdyby baza miała kolumnę `name` zamiast rozdzielenia
    if (in_array('name', $dbColumns) && !in_array('first_name', $dbColumns)) {$fieldsToUpdate[] = "name = ?";
        $types .= 's';
        $values[] =$fullName;
    }

    // Sprawdzenie kolumny telefonu
    if (in_array('phone_number', $dbColumns)) {$fieldsToUpdate[] = "phone_number = ?";
        $types .= 's';
        $values[] =$phone;
    } elseif (in_array('phone', $dbColumns)) {$fieldsToUpdate[] = "phone = ?";
        $types .= 's';
        $values[] =$phone;
    }

    // Dodanie hasła, jeśli jest zmieniane
    if ($updatePassword) {$fieldsToUpdate[] = "password_hash = ?";
        $types .= 's';
        $values[] =$newPasswordHash;
    }

    if (empty($fieldsToUpdate)) {
        throw new Exception('Nie znaleziono pasujących kolumn do aktualizacji w tabeli users.');
    }

    // Dodanie ID użytkownika do parametrów WHERE
    $types .= 'i';
    $values[] =$userId;

    $sql = "UPDATE users SET " . implode(', ', $fieldsToUpdate) . " WHERE id = ?";
    
    $updateStmt = mysqli_prepare($config,$sql);
    if (!$updateStmt) {
        throw new Exception('Błąd przygotowania zapytania UPDATE: ' . mysqli_error($config));
    }

    // Dynamiczne wiązanie parametrów (call_user_func_array)
    $bindParams = array_merge([$types], $values);$tmp = [];
    foreach ($bindParams as $key =>$value) {
        $tmp[$key] = &$bindParams[$key];
    }
    call_user_func_array([$updateStmt, 'bind_param'],$tmp);

    if (mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Profil zaktualizowany pomyślnie.']);
        exit;
    } else {
        $sqlError = mysqli_stmt_error($updateStmt);
        mysqli_stmt_close($updateStmt);
        throw new Exception('Błąd zapisu SQL: ' . $sqlError);
    }

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd serwera: ' . $e->getMessage()
    ]);
    exit;
}