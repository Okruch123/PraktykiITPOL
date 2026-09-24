<?php
session_start();
header('Content-Type: application/json');

// Dołącz plik z połączeniem do bazy danych
require_once '../db_getters/config.php'; 

try {
    $sessionId = session_id();

    if (!empty($sessionId) && isset($pdo)) {
        // Usuwanie wpisu z tabeli users_sessions na podstawie kolumny 'session'
        $stmt = $pdo->prepare("DELETE FROM users_sessions WHERE session = ?");
        $stmt->execute([$sessionId]);
    }
} catch (Exception $e) {
    // Opcjonalna obsługa błędu bazy danych
}

// Czyszczenie danych sesji PHP
$_SESSION = array();

// Usunięcie ciasteczka sesji przeglądarki
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Zniszczenie sesji na serwerze
session_destroy();

echo json_encode(['success' => true, 'message' => 'Wylogowano pomyślnie']);
exit;