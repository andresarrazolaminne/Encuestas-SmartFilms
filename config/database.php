<?php

/**
 * Conexión PDO a MySQL usando variables de entorno (.env).
 * Uso: require_once __DIR__ . '/config/database.php'; $pdo = get_db();
 */

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '';
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASS') ?: '';

    if ($host === '' || $name === '' || $user === '') {
        throw new RuntimeException(
            'Faltan variables de BD. Revisa .env: DB_HOST, DB_NAME, DB_USER, DB_PASS.'
        );
    }

    $dsn = "mysql:host=" . $host . ";dbname=" . $name . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}
