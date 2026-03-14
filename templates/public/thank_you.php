<?php
$thankTitle = $thankTitle ?? 'Gracias';
$thankMessage = $thankMessage ?? 'Tu respuesta ha sido registrada.';
$theme = $form['config']['theme'] ?? [];
$bg = $theme['background'] ?? '#f5f5f5';
$bgImage = trim($theme['backgroundImage'] ?? '');
$primary = $theme['primaryColor'] ?? '#6b21a8';
$textColor = $theme['textColor'] ?? '#1f2937';
$fontFamily = $theme['fontFamily'] ?? 'Inter, sans-serif';
$logoUrl = trim($theme['logoUrl'] ?? '');
$headerBg = $theme['headerBackground'] ?? $primary;
$headerTextColor = $theme['headerTextColor'] ?? '#ffffff';
$radius = $theme['borderRadius'] ?? '8px';
$btnBg = $theme['buttonBackground'] ?? $primary;
$btnTextColor = $theme['buttonTextColor'] ?? '#ffffff';
$btnRadius = $theme['buttonBorderRadius'] ?? $radius;
$rp = $form['config']['responsePage'] ?? [];
$buttonText = trim($rp['buttonText'] ?? '');
$buttonUrl = trim($rp['buttonUrl'] ?? '');
$showButton = $buttonText !== '' && $buttonUrl !== '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($thankTitle) ?></title>
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
        .thank-page-wrap { padding: 1.5rem; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .thank-header {
            background: <?= htmlspecialchars($headerBg) ?>;
            color: <?= htmlspecialchars($headerTextColor) ?>;
            padding: 1rem 1.5rem;
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            width: 100%;
            text-align: center;
        }
        .thank-header img { max-height: 56px; max-width: 180px; vertical-align: middle; display: block; margin: 0 auto; }
        .thank-box {
            max-width: 440px;
            text-align: center;
            background: #fff;
            padding: 2rem;
            border-radius: <?= htmlspecialchars($radius) ?>;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-top: 1rem;
        }
        .thank-box h1 { color: <?= htmlspecialchars($primary) ?>; margin: 0 0 1rem; font-size: 1.5rem; }
        .thank-box .message { line-height: 1.5; }
        .thank-box .thank-btn {
            display: inline-block;
            margin-top: 1.25rem;
            padding: 0.65rem 1.25rem;
            background: <?= htmlspecialchars($btnBg) ?>;
            color: <?= htmlspecialchars($btnTextColor) ?>;
            text-decoration: none;
            border-radius: <?= htmlspecialchars($btnRadius) ?>;
            font-weight: 500;
            font-size: 1rem;
        }
        .thank-box .thank-btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="thank-page-wrap">
        <?php if ($logoUrl !== ''): ?>
            <header class="thank-header">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
            </header>
        <?php endif; ?>
        <div class="thank-box">
            <h1><?= htmlspecialchars($thankTitle) ?></h1>
            <div class="message"><?= nl2br(htmlspecialchars($thankMessage)) ?></div>
            <?php if ($showButton): ?>
                <a href="<?= htmlspecialchars($buttonUrl) ?>" class="thank-btn"><?= htmlspecialchars($buttonText) ?></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
