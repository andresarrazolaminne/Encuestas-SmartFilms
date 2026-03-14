<?php
/**
 * Fija la contraseña del usuario 'admin' a 'admin123'.
 * Ejecutar una vez desde la raíz del proyecto: php fix_admin_password.php
 * Luego se puede borrar este archivo.
 */
$baseDir = __DIR__;
require_once $baseDir . '/config/load_env.php';
load_env($baseDir);
require_once $baseDir . '/config/database.php';

$pdo = get_db();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$hash]);
$n = $stmt->rowCount();
echo $n > 0 ? "OK. Contraseña de 'admin' actualizada a: admin123\n" : "No se encontró usuario 'admin'.\n";
