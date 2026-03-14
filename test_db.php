<?php

/**
 * Prueba de conexión a la base de datos.
 * Ejecutar: php test_db.php
 */

$baseDir = __DIR__;
require_once $baseDir . '/config/load_env.php';
load_env($baseDir);

require_once $baseDir . '/config/database.php';

try {
    $pdo = get_db();
    echo "Conexión OK.\n";
    $stmt = $pdo->query('SELECT DATABASE() as db');
    $row = $stmt->fetch();
    echo "Base de datos: " . ($row['db'] ?? '?') . "\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
