<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../db_getters/config.php');

if (!$config) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą.']);
    exit;
}

if (!isset($_SESSION['pending_2fa_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak aktywnej sesji weryfikacji 2FA. Zaloguj się ponownie.']);
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

$stmtCode = mysqli_prepare(
    $config,
    "SELECT id, expires_at FROM two_factor_codes WHERE user_id = ? AND code = ? AND used = 0 LIMIT 1"
);
mysqli_stmt_bind_param($stmtCode, 'is', $userId, $code);
mysqli_stmt_execute($stmtCode);
$resultCode = mysqli_stmt_get_result($stmtCode);
$codeRow = mysqli_fetch_assoc($resultCode);
mysqli_stmt_close($stmtCode);

if (!$codeRow) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy kod weryfikacyjny.']);
    exit;
}

if (strtotime($codeRow['expires_at']) < time()) {
    $stmtDel = mysqli_prepare($config, "DELETE FROM two_factor_codes WHERE id = ?");
    mysqli_stmt_bind_param($stmtDel, 'i', $codeRow['id']);
    mysqli_stmt_execute($stmtDel);
    mysqli_stmt_close($stmtDel);

    echo json_encode(['success' => false, 'message' => 'Kod wygasł. Zaloguj się ponownie.']);
    exit;
}

// Oznacz kod jako zużyty
$stmtUsed = mysqli_prepare($config, "UPDATE two_factor_codes SET used = 1 WHERE id = ?");
mysqli_stmt_bind_param($stmtUsed, 'i', $codeRow['id']);
mysqli_stmt_execute($stmtUsed);
mysqli_stmt_close($stmtUsed);

// Pobierz dane użytkownika
$stmtUser = mysqli_prepare($config, "SELECT id, email, is_admin FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtUser, 'i', $userId);
mysqli_stmt_execute($stmtUser);
$resUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resUser);
mysqli_stmt_close($stmtUser);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika.']);
    exit;
}

// Utworzenie właściwej sesji po udanym 2FA
unset($_SESSION['pending_2fa_user_id']);
session_regenerate_id(true);
$sessionId = session_id();

$stmtSession = mysqli_prepare($config, "INSERT INTO users_sessions (user_id, session) VALUES (?, ?)");
mysqli_stmt_bind_param($stmtSession, 'is', $userId, $sessionId);
mysqli_stmt_execute($stmtSession);
mysqli_stmt_close($stmtSession);

echo json_encode([
    'success' => true,
    'message' => 'Zalogowano pomyślnie.',
    'user' => [
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'is_admin' => $user['is_admin']
    ]
]);