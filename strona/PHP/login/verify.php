<?php
require_once(__DIR__ . '/../db_getters/config.php');

$token = $_GET['token'] ?? '';

// Ścieżka do głównej strony Twojej aplikacji (dopasuj index.php lub index.html w zależności od tego, jak nazywa się główny plik w folderze strona/)
$mainPageUrl = "../../index.php"; 

if (empty($token)) {
    header("Location: $mainPageUrl?verify_status=error&msg=" . urlencode("Nie podano tokenu aktywacyjnego."));
    exit;
}

// 1. Sprawdź, czy token istnieje w pending_users
$stmt = mysqli_prepare($config, "SELECT * FROM pending_users WHERE verification_token = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Sprawdź datę ważności tokena
    if (strtotime($row['verification_expiration_date']) < time()) {
        mysqli_stmt_close($stmt);
        
        $delStmt = mysqli_prepare($config, "DELETE FROM pending_users WHERE ID = ?");
        mysqli_stmt_bind_param($delStmt, "i", $row['ID']);
        mysqli_stmt_execute($delStmt);
        mysqli_stmt_close($delStmt);

        header("Location: $mainPageUrl?verify_status=error&msg=" . urlencode("Link aktywacyjny wygasł. Zarejestruj się ponownie."));
        exit;
    }

    // 2. Przenieś użytkownika do tabeli docelowej `users`
    $insertUser = mysqli_prepare($config, "INSERT INTO users (email, password_hash, phone_number, first_name, second_name, surname) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param(
        $insertUser, 
        "ssssss", 
        $row['email'], 
        $row['password_hash'], 
        $row['phone_number'], 
        $row['first_name'], 
        $row['second_name'], 
        $row['surname']
    );

    if (mysqli_stmt_execute($insertUser)) {
        mysqli_stmt_close($insertUser);

        // 3. Usuń z pending_users
        $delStmt = mysqli_prepare($config, "DELETE FROM pending_users WHERE ID = ?");
        mysqli_stmt_bind_param($delStmt, "i", $row['ID']);
        mysqli_stmt_execute($delStmt);
        mysqli_stmt_close($delStmt);
        mysqli_stmt_close($stmt);

        // Używamy unikalnego parametru verify_status zamiast ogólnego status
        header("Location: $mainPageUrl?verify_status=success&msg=" . urlencode("Konto aktywowane pomyślnie! Możesz się zalogować."));
        exit;
    } else {
        mysqli_stmt_close($insertUser);
        header("Location: $mainPageUrl?verify_status=error&msg=" . urlencode("Błąd podczas zapisywania użytkownika w bazie."));
        exit;
    }

} else {
    mysqli_stmt_close($stmt);
    header("Location: $mainPageUrl?verify_status=error&msg=" . urlencode("Nieprawidłowy lub już wykorzystany link aktywacyjny."));
    exit;
}