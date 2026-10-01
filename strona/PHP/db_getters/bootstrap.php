<?php

$envPath = __DIR__ . "/../../restricted/passes.env";

if (!file_exists($envPath)) {
    error_log("ENV ERROR: Nie znaleziono pliku: " . $envPath);
    throw new RuntimeException("Brak pliku konfiguracyjnego.");
}

$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

if ($env === false) {
    error_log("ENV ERROR: Nie udało się odczytać pliku passes.env");
    throw new RuntimeException("Błąd odczytu konfiguracji.");
}

foreach ($env as $key => $value) {
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
}