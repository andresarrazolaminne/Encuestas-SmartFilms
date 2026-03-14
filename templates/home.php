<?php
/**
 * Página de inicio: redirige a login o al listado admin según sesión.
 * Si no hay sesión, mostrar enlace a login.
 */
$pageTitle = 'Inicio';
ob_start();
?>
<h1>Encuestas SmartFilms</h1>
<p>Sistema de gestión de formularios dinámicos.</p>
<p><a href="/login">Iniciar sesión</a></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
