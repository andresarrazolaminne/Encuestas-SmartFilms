<?php
$sections = $form['definition']['sections'] ?? [];
usort($sections, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
$theme = $form['config']['theme'] ?? [];
$bg = $theme['background'] ?? '#f5f5f5';
$bgImage = trim($theme['backgroundImage'] ?? '');
$primary = $theme['primaryColor'] ?? '#6b21a8';
$textColor = $theme['textColor'] ?? '#1f2937';
$fontFamily = $theme['fontFamily'] ?? 'Inter, sans-serif';
$radius = $theme['borderRadius'] ?? '8px';
$logoUrl = trim($theme['logoUrl'] ?? '');
$headerText = trim($theme['headerText'] ?? '');
$headerBg = $theme['headerBackground'] ?? $primary;
$headerTextColor = $theme['headerTextColor'] ?? '#ffffff';
$containerWidth = $theme['containerMaxWidth'] ?? '560px';
$btnBg = $theme['buttonBackground'] ?? $primary;
$btnTextColor = $theme['buttonTextColor'] ?? '#ffffff';
$btnRadius = $theme['buttonBorderRadius'] ?? $radius;
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
        body {
            font-family: <?= htmlspecialchars($fontFamily) ?>;
            color: <?= htmlspecialchars($textColor) ?>;
            background: <?= htmlspecialchars($bg) ?>;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            <?php if ($bgImage !== ''): ?>
            background-image: url('<?= htmlspecialchars($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            <?php endif; ?>
        }
        .form-page-wrap { padding: 1.5rem; min-height: 100vh; }
        .form-header {
            background: <?= htmlspecialchars($headerBg) ?>;
            color: <?= htmlspecialchars($headerTextColor) ?>;
            padding: 1rem 1.5rem;
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            text-align: center;
        }
        .form-header img { max-height: 60px; max-width: 200px; vertical-align: middle; }
        .form-header h1 { margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: 600; }
        .form-container {
            max-width: <?= htmlspecialchars($containerWidth) ?>;
            margin: 0 auto;
            background: #fff;
            padding: 1.5rem;
            border-radius: <?= htmlspecialchars($radius) ?>;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .section { margin-bottom: 2rem; }
        .section-title { font-size: 1.1rem; margin-bottom: 1rem; color: <?= htmlspecialchars($primary) ?>; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .field input[type="text"], .field input[type="number"], .field input[type="email"], .field select, .field textarea {
            width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #ccc; border-radius: <?= htmlspecialchars($radius) ?>; font: inherit;
        }
        .field input:focus, .field select:focus, .field textarea:focus { outline: 2px solid <?= htmlspecialchars($primary) ?>; outline-offset: 0; }
        .field textarea { min-height: 100px; resize: vertical; }
        .form-actions { margin-top: 1.5rem; }
        button[type="submit"] {
            background: <?= htmlspecialchars($btnBg) ?>;
            color: <?= htmlspecialchars($btnTextColor) ?>;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: <?= htmlspecialchars($btnRadius) ?>;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 500;
        }
        button[type="submit"]:hover { opacity: 0.9; }
        .error { color: #c00; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="form-page-wrap">
        <?php if ($logoUrl !== '' || $headerText !== ''): ?>
            <header class="form-header">
                <?php if ($logoUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
                <?php endif; ?>
                <?php if ($headerText !== ''): ?>
                    <h1><?= htmlspecialchars($headerText) ?></h1>
                <?php endif; ?>
            </header>
        <?php endif; ?>
    <div class="form-container">
        <?php if ($headerText === '' && $logoUrl === ''): ?>
            <h1><?= htmlspecialchars($form['title']) ?></h1>
        <?php endif; ?>
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
            <p class="form-actions"><button type="submit">Enviar</button></p>
        </form>
    </div>
    </div>
</body>
</html>
