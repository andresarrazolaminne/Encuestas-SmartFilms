<?php
/**
 * Formulario de login. POST a /login (Fase 2).
 */
$pageTitle = 'Iniciar sesión';
$error = $error ?? null;
ob_start();
?>
<h1>Iniciar sesión</h1>
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post" action="/login">
    <label>Usuario <input type="text" name="username" required autocomplete="username"></label>
    <label>Contraseña <input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit">Entrar</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
