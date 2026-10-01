<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../db_getters/config.php');


try {
    $sessionId = session_id();

    if (!empty($sessionId) && isset($pdo)) {
        $stmt = $pdo->prepare("DELETE FROM users_sessions WHERE session = ?");
        $stmt->execute([$sessionId]);
    }
} catch (Exception $e) {
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

echo json_encode(['success' => true, 'message' => 'Wylogowano pomyślnie']);
exit;