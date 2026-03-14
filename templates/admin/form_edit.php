<?php
$pageTitle = 'Editar: ' . htmlspecialchars($form['title']);
$error = $error ?? null;
$success = $success ?? null;
$definitionJson = isset($_POST['definition']) ? $_POST['definition'] : json_encode($form['definition'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$configJson = isset($_POST['config']) ? $_POST['config'] : json_encode($form['config'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
ob_start();
?>
<h1>Editar formulario</h1>
<p>
    <a href="/admin">← Listado</a> —
    <a href="/f/<?= htmlspecialchars($form['slug']) ?>" target="_blank">Ver formulario público</a>
</p>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" action="/admin/forms/<?= (int) $form['id'] ?>">
    <p>
        <label>Título<br><input type="text" name="title" required value="<?= htmlspecialchars($form['title']) ?>" size="50"></label>
    </p>
    <p>
        <label>Slug (URL: /f/slug)<br><input type="text" name="slug" required value="<?= htmlspecialchars($form['slug']) ?>" size="40"></label>
    </p>
    <p>
        <label>Definition (JSON: secciones y campos)<br>
            <textarea name="definition" rows="18" cols="80" style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($definitionJson) ?></textarea>
        </label>
    </p>
    <p>
        <label>Config (JSON: tema y página de respuesta)<br>
            <textarea name="config" rows="12" cols="80" style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($configJson) ?></textarea>
        </label>
    </p>
    <p><button type="submit">Guardar</button></p>
</form>

<p><small>Puedes pegar el contenido de <code>data/smartfilms_definition.json</code> en Definition para usar el formulario SmartFilms completo.</small></p>

<hr style="margin: 2rem 0;">
<p>
    <form method="post" action="/admin/forms/<?= (int) $form['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('¿Eliminar este formulario? Se borrarán también todas las respuestas.');">
        <button type="submit" style="background:#c00; color:#fff;">Eliminar formulario</button>
    </form>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
