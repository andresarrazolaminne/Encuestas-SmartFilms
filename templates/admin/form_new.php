<?php
$pageTitle = 'Nuevo formulario';
$error = $error ?? null;
ob_start();
?>
<h1>Nuevo formulario</h1>
<p><a href="/admin">← Volver al listado</a></p>
<p>
    <form method="post" action="/admin/forms/new-from-smartfilms" style="display:inline;">
        <button type="submit">Crear desde plantilla SmartFilms</button>
    </form>
    (formulario con las 8 secciones: información básica, situación antes/después, emprendimiento, impacto social, vulnerabilidad, impacto cultural, testimonio)
</p>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="/admin/forms/new">
    <p>
        <label>Título del formulario<br>
            <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" size="50">
        </label>
    </p>
    <p>
        <label>Slug (solo letras, números y guiones; será la URL: /f/<strong>slug</strong>)<br>
            <input type="text" name="slug" required value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" size="40" placeholder="ej: encuesta-smartfilms">
        </label>
    </p>
    <p><button type="submit">Crear formulario</button></p>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
