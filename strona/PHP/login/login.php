<?php

header('Content-Type: application/json; charset=utf-8');


ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once(__DIR__ . '/../db_getters/config.php');

if (!$config) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Błąd połączenia z bazą.'
    ]);

    exit;
}

$rawInput = file_get_contents('php://input');

$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Podaj email i hasło.'
    ]);

    exit;
}

$stmt = mysqli_prepare(
    $config,
    "SELECT id, email, password_hash, is_admin
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Błąd przygotowania zapytania.'
    ]);

    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Błąd wykonania zapytania.'
    ]);

    exit;
}

mysqli_stmt_bind_result(
    $stmt,
    $userId,
    $userEmail,
    $userPassword,
    $user_is_admin
);

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy email lub hasło.'
    ]);

    exit;
}

mysqli_stmt_close($stmt);

if (!password_verify($password, $userPassword)) {

    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy email lub hasło.'
    ]);

    exit;
}

session_start();

session_regenerate_id(true);

$sessionId = session_id();

$stmt = mysqli_prepare(
    $config,
    "INSERT INTO users_sessions (user_id, session)
     VALUES (?, ?)"
);

if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Nie udało się utworzyć sesji.'
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $userId,
    $sessionId
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Nie udało się zapisać sesji.'
    ]);

    exit;
}

mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'message' => 'Zalogowano pomyślnie.',
    'user' => [
        'id' => (int)$userId,
        'email' => $userEmail,
        'is_admin' => $user_is_admin
    ]
]);