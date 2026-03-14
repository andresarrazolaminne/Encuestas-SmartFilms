<?php
$formRepo = new FormRepository(get_db());
$forms = $formRepo->listByUser(Auth::user()['id']);
$pageTitle = 'Mis formularios';
ob_start();
?>
<h1>Mis formularios</h1>
<p>Hola, <?= htmlspecialchars(Auth::user()['username'] ?? '') ?>.
    <a href="/admin/forms/new">Nuevo formulario</a>
    <form method="post" action="/admin/forms/new-from-smartfilms" style="display:inline;">
        <button type="submit">Crear desde plantilla SmartFilms</button>
    </form>
    — <a href="/logout">Cerrar sesión</a>
</p>
<?php if (!empty($_GET['error']) && $_GET['error'] === 'plantilla'): ?>
    <p class="error">No se pudo cargar la plantilla. Revisa que exista data/smartfilms_definition.json.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p class="success">Formulario eliminado.</p>
<?php endif; ?>
<?php if (!empty($_GET['error']) && $_GET['error'] === 'delete'): ?>
    <p class="error">No se pudo eliminar el formulario.</p>
<?php endif; ?>
<?php if (empty($forms)): ?>
    <p>No tienes formularios aún. <strong>Crear desde plantilla SmartFilms</strong> crea el formulario con las 8 secciones ya definidas.</p>
<?php else: ?>
    <ul>
        <?php foreach ($forms as $f): ?>
            <li>
                <strong><?= htmlspecialchars($f['title']) ?></strong>
                (slug: <code><?= htmlspecialchars($f['slug']) ?></code>)
                — <a href="/admin/forms/<?= (int) $f['id'] ?>">Editar</a>
                — <a href="/admin/forms/<?= (int) $f['id'] ?>/responses">Respuestas</a>
                — <a href="/f/<?= htmlspecialchars($f['slug']) ?>" target="_blank">Ver público</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
