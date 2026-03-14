<?php

/**
 * Front controller – Encuestas SmartFilms.
 * Apache: .htaccess redirige aquí. Servidor PHP: usar como router (ver más abajo).
 */

// Servidor integrado PHP: dejar que sirva CSS/JS/imágenes
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
if (preg_match('#\.(css|js|ico|png|jpg|jpeg|gif|svg|woff2?)$#', $path)) {
    $f = __DIR__ . $path;
    if (file_exists($f) && is_file($f)) {
        return false; // el servidor sirve el archivo
    }
}

// Mostrar errores en desarrollo para no devolver respuesta vacía
if (getenv('APP_ENV') !== 'production') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$baseDir = dirname(__DIR__);

// Sesiones en una carpeta del proyecto (evita permisos en /var/lib/php/sessions)
$sessionDir = $baseDir . '/storage/sessions';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0755, true);
}
session_save_path($sessionDir);

try {
    require_once $baseDir . '/config/load_env.php';
    load_env($baseDir);

    require_once $baseDir . '/config/database.php';
    require_once $baseDir . '/src/Router.php';
    require_once $baseDir . '/src/Auth.php';
    require_once $baseDir . '/src/FormRepository.php';

    Auth::start();
} catch (Throwable $e) {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body>';
    echo '<h1>Error al iniciar</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p>Archivo: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '</body></html>';
    exit;
}

// Rutas
Router::get('/', function () use ($baseDir) {
    if (Auth::check()) {
        header('Location: /admin');
        exit;
    }
    require $baseDir . '/templates/home.php';
});

Router::get('/login', function () use ($baseDir) {
    if (Auth::check()) {
        header('Location: /admin');
        exit;
    }
    $error = null;
    require $baseDir . '/templates/login.php';
});

Router::post('/login', function () use ($baseDir) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if (Auth::login($user, $pass)) {
        header('Location: /admin');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
    require $baseDir . '/templates/login.php';
});

Router::get('/logout', function () {
    Auth::logout();
    header('Location: /login');
    exit;
});

Router::get('/admin', function () use ($baseDir) {
    require $baseDir . '/templates/admin/dashboard.php';
});

$formRepo = new FormRepository(get_db());

Router::get('/admin/forms/new', function () use ($baseDir, $formRepo) {
    $error = null;
    require $baseDir . '/templates/admin/form_new.php';
});

Router::post('/admin/forms/new', function () use ($baseDir, $formRepo) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($slug));
    $slug = preg_replace('/-+/', '-', trim($slug, '-'));
    if ($title === '' || $slug === '') {
        $error = 'Título y slug son obligatorios.';
        require $baseDir . '/templates/admin/form_new.php';
        return;
    }
    if ($formRepo->slugExists($slug)) {
        $error = 'Ese slug ya está en uso.';
        require $baseDir . '/templates/admin/form_new.php';
        return;
    }
    $userId = Auth::user()['id'];
    $id = $formRepo->create($userId, $slug, $title);
    header('Location: /admin/forms/' . $id);
    exit;
});

Router::post('/admin/forms/new-from-smartfilms', function () use ($baseDir, $formRepo) {
    $userId = Auth::user()['id'];
    $slug = 'encuesta-smartfilms';
    $base = 0;
    while ($formRepo->slugExists($slug)) {
        $base++;
        $slug = 'encuesta-smartfilms-' . $base;
    }
    $jsonPath = $baseDir . '/data/smartfilms_definition.json';
    if (!is_file($jsonPath)) {
        header('Location: /admin?error=plantilla');
        exit;
    }
    $definition = json_decode(file_get_contents($jsonPath), true);
    if (!is_array($definition) || empty($definition['sections'])) {
        header('Location: /admin?error=plantilla');
        exit;
    }
    $title = 'Encuesta SmartFilms' . ($base > 0 ? ' (' . $base . ')' : '');
    $id = $formRepo->createWithDefinition($userId, $slug, $title, $definition);
    header('Location: /admin/forms/' . $id);
    exit;
});

Router::get('/admin/forms/{id}', function (array $params) use ($baseDir, $formRepo) {
    $id = (int) $params['id'];
    $form = $formRepo->findById($id, Auth::user()['id']);
    if (!$form) {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Formulario no encontrado</h1><p><a href="/admin">Volver</a></p></body></html>';
        return;
    }
    $error = null;
    $success = null;
    require $baseDir . '/templates/admin/form_edit.php';
});

Router::get('/admin/forms/{id}/responses', function (array $params) use ($baseDir, $formRepo) {
    $id = (int) $params['id'];
    $form = $formRepo->findById($id, Auth::user()['id']);
    if (!$form) {
        header('Location: /admin');
        exit;
    }
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, response_data, created_at, ip FROM form_responses WHERE form_id = ? ORDER BY created_at DESC');
    $stmt->execute([$form['id']]);
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($responses as &$r) {
        $r['response_data'] = is_string($r['response_data']) ? json_decode($r['response_data'], true) : $r['response_data'];
    }
    unset($r);
    $pageTitle = 'Respuestas: ' . $form['title'];
    require $baseDir . '/templates/admin/responses.php';
});

Router::post('/admin/forms/{id}/delete', function (array $params) use ($formRepo) {
    $id = (int) $params['id'];
    if ($formRepo->delete($id, Auth::user()['id'])) {
        header('Location: /admin?deleted=1');
    } else {
        header('Location: /admin?error=delete');
    }
    exit;
});

Router::post('/admin/forms/{id}', function (array $params) use ($baseDir, $formRepo) {
    $id = (int) $params['id'];
    $form = $formRepo->findById($id, Auth::user()['id']);
    if (!$form) {
        header('Location: /admin');
        exit;
    }
    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($slug));
    $slug = preg_replace('/-+/', '-', trim($slug, '-'));
    if ($title === '' || $slug === '') {
        $error = 'Título y slug son obligatorios.';
        require $baseDir . '/templates/admin/form_edit.php';
        return;
    }
    if ($formRepo->slugExists($slug, $id)) {
        $error = 'Ese slug ya está en uso.';
        require $baseDir . '/templates/admin/form_edit.php';
        return;
    }
    $definitionRaw = $_POST['definition'] ?? '';
    $configRaw = $_POST['config'] ?? '';
    $definition = json_decode($definitionRaw, true);
    $config = json_decode($configRaw, true);
    if ($definition === null && $definitionRaw !== '' && $definitionRaw !== '{}') {
        $error = 'Definition: JSON no válido.';
        require $baseDir . '/templates/admin/form_edit.php';
        return;
    }
    if ($config === null && $configRaw !== '' && $configRaw !== '{}') {
        $error = 'Config: JSON no válido.';
        require $baseDir . '/templates/admin/form_edit.php';
        return;
    }
    $formRepo->update($id, Auth::user()['id'], [
        'title' => $title,
        'slug' => $slug,
        'definition' => $definition ?? $form['definition'],
        'config' => $config ?? $form['config'],
    ]);
    $form = $formRepo->findById($id, Auth::user()['id']);
    $success = 'Guardado.';
    require $baseDir . '/templates/admin/form_edit.php';
});

Router::get('/f/{slug}', function (array $params) use ($baseDir, $formRepo) {
    $form = $formRepo->findBySlug($params['slug'] ?? '');
    if (!$form) {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>No encontrado</title></head><body>';
        echo '<h1>Formulario no encontrado</h1><p><a href="/">Inicio</a></p></body></html>';
        return;
    }
    $formError = null;
    require $baseDir . '/templates/public/form_view.php';
});

Router::post('/f/{slug}', function (array $params) use ($baseDir, $formRepo) {
    $form = $formRepo->findBySlug($params['slug'] ?? '');
    if (!$form) {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>No encontrado</title></head><body><h1>Formulario no encontrado</h1></body></html>';
        return;
    }
    $definition = $form['definition'];
    $responseData = [];
    $errors = [];
    foreach ($definition['sections'] ?? [] as $sec) {
        foreach ($sec['fields'] ?? [] as $field) {
            $id = $field['id'] ?? '';
            if ($id === '') continue;
            $val = $_POST[$id] ?? null;
            if (is_array($val)) {
                $responseData[$id] = $val;
            } elseif ($val !== null && $val !== '') {
                $responseData[$id] = $field['type'] === 'number' ? (int) $val : trim((string) $val);
            } else {
                $responseData[$id] = $field['type'] === 'checkbox' ? [] : '';
            }
            if (!empty($field['required']) && ($responseData[$id] === '' || $responseData[$id] === [])) {
                $errors[$id] = 'Requerido';
            }
        }
    }
    if (!empty($errors)) {
        $formError = 'Completa los campos obligatorios.';
        require $baseDir . '/templates/public/form_view.php';
        return;
    }
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO form_responses (form_id, response_data, ip, user_agent) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $form['id'],
        json_encode($responseData),
        $_SERVER['REMOTE_ADDR'] ?? null,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
    ]);
    $rp = $form['config']['responsePage'] ?? [];
    if (!empty($rp['redirectUrl'])) {
        header('Location: ' . $rp['redirectUrl']);
        exit;
    }
    $thankTitle = $rp['title'] ?? 'Gracias';
    $thankMessage = $rp['message'] ?? 'Tu respuesta ha sido registrada.';
    require $baseDir . '/templates/public/thank_you.php';
});

// Despachar
try {
    $path = Router::path();
    if (str_starts_with($path, '/admin') && !Auth::check()) {
        header('Location: /login');
        exit;
    }

    $match = Router::match();
    if ($match !== null) {
        [$handler, $params] = $match;
        $handler($params);
    } else {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body>';
        echo '<h1>Página no encontrada</h1><p><a href="/">Inicio</a></p></body></html>';
    }
} catch (Throwable $e) {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body>';
    echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p></body></html>';
}
