<?php

/**
 * Prueba de conexión a la BD desde el navegador.
 * Acceder a: http://tu-dominio/public/test_db.php
 * Eliminar o restringir en producción.
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/load_env.php';
load_env($baseDir);
require_once $baseDir . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = get_db();
    $stmt = $pdo->query('SELECT DATABASE() as db');
    $row = $stmt->fetch();
    echo "Conexión OK.\n";
    echo "Base de datos: " . ($row['db'] ?? '?') . "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage() . "\n";
}
