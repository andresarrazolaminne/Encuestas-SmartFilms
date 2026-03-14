<?php

/**
 * Autenticación simple: sesión, login con BD, logout, proteger rutas.
 */
class Auth
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        self::$started = true;
    }

    /** Usuario logueado (array con id, username) o null */
    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /** ¿Hay sesión activa? */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Intenta login. Devuelve true si OK, false si falla.
     */
    public static function login(string $username, string $password): bool
    {
        self::start();
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username']];
        return true;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    /** Si no hay sesión, redirige a /login y termina. */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }
        header('Location: /login');
        exit;
    }
}
