<?php
/**
 * Layout base. Espera $pageTitle (string) y $content (string HTML).
 */
$pageTitle = $pageTitle ?? 'Encuestas SmartFilms';
$content = $content ?? '';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <main class="container<?= !empty($layoutWide) ? ' container-wide' : '' ?>">
        <?= $content ?>
    </main>
    <script src="/js/app.js" defer></script>
</body>
</html>
