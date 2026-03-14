<?php
$sections = $form['definition']['sections'] ?? [];
usort($sections, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
$theme = $form['config']['theme'] ?? [];
$bg = $theme['background'] ?? '#f5f5f5';
$primary = $theme['primaryColor'] ?? '#6b21a8';
$textColor = $theme['textColor'] ?? '#1f2937';
$fontFamily = $theme['fontFamily'] ?? 'Inter, sans-serif';
$radius = $theme['borderRadius'] ?? '8px';
$formError = $formError ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form['title']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: <?= htmlspecialchars($fontFamily) ?>; color: <?= htmlspecialchars($textColor) ?>; background: <?= htmlspecialchars($bg) ?>; margin: 0; padding: 1.5rem; }
        .form-container { max-width: 560px; margin: 0 auto; }
        h1 { margin-top: 0; }
        .section { margin-bottom: 2rem; }
        .section-title { font-size: 1.1rem; margin-bottom: 1rem; color: <?= htmlspecialchars($primary) ?>; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .field input[type="text"], .field input[type="number"], .field input[type="email"], .field select, .field textarea {
            width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #ccc; border-radius: <?= htmlspecialchars($radius) ?>; font: inherit;
        }
        .field input:focus, .field select:focus, .field textarea:focus { outline: 2px solid <?= htmlspecialchars($primary) ?>; outline-offset: 0; }
        .field textarea { min-height: 100px; resize: vertical; }
        button[type="submit"] { background: <?= htmlspecialchars($primary) ?>; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: <?= htmlspecialchars($radius) ?>; font-size: 1rem; cursor: pointer; }
        button[type="submit"]:hover { opacity: 0.9; }
        .error { color: #c00; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><?= htmlspecialchars($form['title']) ?></h1>
        <?php if ($formError): ?>
            <p class="error"><?= htmlspecialchars($formError) ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <?php foreach ($sections as $sec): ?>
                <div class="section">
                    <h2 class="section-title"><?= htmlspecialchars($sec['title'] ?? '') ?></h2>
                    <?php foreach ($sec['fields'] ?? [] as $field):
                        $id = $field['id'] ?? '';
                        $label = $field['label'] ?? $id;
                        $req = !empty($field['required']);
                        $type = $field['type'] ?? 'text';
                    ?>
                        <div class="field">
                            <label for="f_<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?><?= $req ? ' *' : '' ?></label>
                            <?php if ($type === 'text' || $type === 'email'): ?>
                                <input type="<?= $type ?>" id="f_<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" value="<?= htmlspecialchars($_POST[$id] ?? '') ?>" placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $req ? 'required' : '' ?>>
                            <?php elseif ($type === 'number'): ?>
                                <input type="number" id="f_<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" value="<?= htmlspecialchars($_POST[$id] ?? '') ?>"
                                    <?= isset($field['min']) ? ' min="' . (int)$field['min'] . '"' : '' ?>
                                    <?= isset($field['max']) ? ' max="' . (int)$field['max'] . '"' : '' ?>
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $req ? 'required' : '' ?>>
                            <?php elseif ($type === 'textarea'): ?>
                                <textarea id="f_<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" rows="<?= (int)($field['rows'] ?? 4) ?>" placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $req ? 'required' : '' ?>><?= htmlspecialchars($_POST[$id] ?? '') ?></textarea>
                            <?php elseif ($type === 'select'): ?>
                                <select id="f_<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" <?= $req ? 'required' : '' ?>>
                                    <option value="">Selecciona...</option>
                                    <?php foreach ($field['options'] ?? [] as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>" <?= ($_POST[$id] ?? '') === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" id="f_<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" value="<?= htmlspecialchars($_POST[$id] ?? '') ?>" <?= $req ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <p><button type="submit">Enviar</button></p>
        </form>
    </div>
</body>
</html>
