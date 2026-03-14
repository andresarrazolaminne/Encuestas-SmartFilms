<?php
$thankTitle = $thankTitle ?? 'Gracias';
$thankMessage = $thankMessage ?? 'Tu respuesta ha sido registrada.';
$theme = $form['config']['theme'] ?? [];
$bg = $theme['background'] ?? '#f5f5f5';
$primary = $theme['primaryColor'] ?? '#6b21a8';
$textColor = $theme['textColor'] ?? '#1f2937';
$fontFamily = $theme['fontFamily'] ?? 'Inter, sans-serif';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($thankTitle) ?></title>
    <style>
        body { font-family: <?= htmlspecialchars($fontFamily) ?>; color: <?= htmlspecialchars($textColor) ?>; background: <?= htmlspecialchars($bg) ?>; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .box { max-width: 400px; text-align: center; }
        h1 { color: <?= htmlspecialchars($primary) ?>; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1><?= htmlspecialchars($thankTitle) ?></h1>
        <p><?= htmlspecialchars($thankMessage) ?></p>
    </div>
</body>
</html>
