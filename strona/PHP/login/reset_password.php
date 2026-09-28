<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once(__DIR__ . '/../db_getters/config.php');

if (!$config) {
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

// Pobierz id użytkownika
$stmt = mysqli_prepare($config, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $userId);
$exists = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$exists) {
    echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika.']);
    exit;
}

// Sprawdź poprawność kodu w tabeli two_factor_codes
$stmtCode = mysqli_prepare($config, "SELECT id, expires_at FROM two_factor_codes WHERE user_id = ? AND code = ? AND action = 'reset' LIMIT 1");
mysqli_stmt_bind_param($stmtCode, "is", $userId, $code);
mysqli_stmt_execute($stmtCode);
mysqli_stmt_bind_result($stmtCode, $codeId, $expiresAt);
$validCode = mysqli_stmt_fetch($stmtCode);
mysqli_stmt_close($stmtCode);

if (!$validCode) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy kod weryfikacyjny.']);
    exit;
}

if (strtotime($expiresAt) < time()) {
    echo json_encode(['success' => false, 'message' => 'Kod wygasł. Wyślij nowy kod.']);
    exit;
}

// Zaktualizuj hasło
$newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
$stmtUpdate = mysqli_prepare($config, "UPDATE users SET password_hash = ? WHERE id = ?");
mysqli_stmt_bind_param($stmtUpdate, "si", $newPasswordHash, $userId);
mysqli_stmt_execute($stmtUpdate);
mysqli_stmt_close($stmtUpdate);

// Usuń użyty kod
$stmtDel = mysqli_prepare($config, "DELETE FROM two_factor_codes WHERE id = ?");
mysqli_stmt_bind_param($stmtDel, "i", $codeId);
mysqli_stmt_execute($stmtDel);
mysqli_stmt_close($stmtDel);

echo json_encode(['success' => true, 'message' => 'Hasło zostało pomyślnie zmienione.']);