<?php
$pageTitle = 'Editar: ' . htmlspecialchars($form['title']);
$error = $error ?? null;
$success = $success ?? null;
$t = $form['config']['theme'] ?? [];
$rp = $form['config']['responsePage'] ?? [];
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

    <fieldset class="apariencia-section">
        <legend>Apariencia del formulario público</legend>
        <p><label>URL del logo<br><input type="url" name="theme_logo_url" value="<?= htmlspecialchars($t['logoUrl'] ?? '') ?>" placeholder="https://..."></label></p>
        <p><label>Texto del encabezado<br><input type="text" name="theme_header_text" value="<?= htmlspecialchars($t['headerText'] ?? '') ?>" size="50" placeholder="Ej: Encuesta SmartFilms"></label></p>
        <p><label>Color de fondo del encabezado<br><input type="text" name="theme_header_background" value="<?= htmlspecialchars($t['headerBackground'] ?? '#6b21a8') ?>" size="12"></label></p>
        <p><label>Color del texto del encabezado<br><input type="text" name="theme_header_text_color" value="<?= htmlspecialchars($t['headerTextColor'] ?? '#ffffff') ?>" size="12"></label></p>
        <p><label>Color de fondo de la página<br><input type="text" name="theme_background" value="<?= htmlspecialchars($t['background'] ?? '#f5f5f5') ?>" size="12"></label></p>
        <p><label>URL imagen de fondo (opcional)<br><input type="url" name="theme_background_image" value="<?= htmlspecialchars($t['backgroundImage'] ?? '') ?>" placeholder="https://..."></label></p>
        <p><label>Color principal (títulos, bordes)<br><input type="text" name="theme_primary_color" value="<?= htmlspecialchars($t['primaryColor'] ?? '#6b21a8') ?>" size="12"></label></p>
        <p><label>Color del texto<br><input type="text" name="theme_text_color" value="<?= htmlspecialchars($t['textColor'] ?? '#1f2937') ?>" size="12"></label></p>
        <p><label>Fuente (CSS)<br><input type="text" name="theme_font_family" value="<?= htmlspecialchars($t['fontFamily'] ?? 'Inter, sans-serif') ?>" size="40"></label></p>
        <p><label>Ancho máximo del contenido (ej: 560px, 720px)<br><input type="text" name="theme_container_max_width" value="<?= htmlspecialchars($t['containerMaxWidth'] ?? '560px') ?>" size="12"></label></p>
        <p><label>Border radius (ej: 8px)<br><input type="text" name="theme_border_radius" value="<?= htmlspecialchars($t['borderRadius'] ?? '8px') ?>" size="12"></label></p>
        <p><strong>Botón Enviar</strong></p>
        <p><label>Fondo del botón<br><input type="text" name="theme_button_background" value="<?= htmlspecialchars($t['buttonBackground'] ?? '#6b21a8') ?>" size="12"></label></p>
        <p><label>Color del texto del botón<br><input type="text" name="theme_button_text_color" value="<?= htmlspecialchars($t['buttonTextColor'] ?? '#ffffff') ?>" size="12"></label></p>
        <p><label>Border radius del botón (ej: 8px)<br><input type="text" name="theme_button_border_radius" value="<?= htmlspecialchars($t['buttonBorderRadius'] ?? '8px') ?>" size="12"></label></p>
    </fieldset>

    <fieldset class="apariencia-section thank-section">
        <legend>Página de agradecimiento</legend>
        <p><label>Título<br><input type="text" name="response_page_title" value="<?= htmlspecialchars($rp['title'] ?? '¡Gracias por participar!') ?>" size="50" placeholder="Ej: ¡Gracias por participar!"></label></p>
        <p><label>Mensaje<br><textarea name="response_page_message" rows="4" cols="60" placeholder="Ej: Tu respuesta ha sido registrada."><?= htmlspecialchars($rp['message'] ?? 'Tu respuesta ha sido registrada.') ?></textarea></label></p>
        <p><label>Redirigir a URL (opcional; si lo rellenas, el usuario irá a esta URL en lugar de ver la página de agradecimiento)<br><input type="url" name="response_page_redirect_url" value="<?= htmlspecialchars($rp['redirectUrl'] ?? '') ?>" size="50" placeholder="https://..."></label></p>
        <p><label>Texto del botón (opcional)<br><input type="text" name="response_page_button_text" value="<?= htmlspecialchars($rp['buttonText'] ?? '') ?>" size="40" placeholder="Ej: Volver al inicio"></label></p>
        <p><label>URL del botón (opcional; a dónde lleva el botón. Puede ser https://... o / para inicio)<br><input type="text" name="response_page_button_url" value="<?= htmlspecialchars($rp['buttonUrl'] ?? '') ?>" size="50" placeholder="https://... o /"></label></p>
    </fieldset>

    <p>
        <label>Definition (JSON: secciones y campos)<br>
            <textarea name="definition" rows="18" cols="80" style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($definitionJson) ?></textarea>
        </label>
    </p>
    <p>
        <label>Config (JSON: respuesta después de enviar y opciones avanzadas)<br>
            <textarea name="config" rows="12" cols="80" style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($configJson) ?></textarea>
        </label>
    </p>
    <p><button type="submit">Guardar</button></p>
</form>

<p><small>Puedes pegar el contenido de <code>data/smartfilms_definition.json</code> en Definition para usar el formulario SmartFilms completo. Los campos de Apariencia tienen prioridad sobre el JSON de Config al guardar.</small></p>

<hr style="margin: 2rem 0;">
<p>
    <form method="post" action="/admin/forms/<?= (int) $form['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('¿Eliminar este formulario? Se borrarán también todas las respuestas.');">
        <button type="submit" style="background:#c00; color:#fff;">Eliminar formulario</button>
    </form>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
