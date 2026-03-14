<?php

/**
 * Router simple: obtiene el path de la petición y devuelve la ruta coincidente.
 * Uso: $route = Router::match($_SERVER['REQUEST_URI']);
 */
class Router
{
    /** @var array<string, callable> path => handler */
    private static array $routes = [];

    /**
     * Obtiene el path limpio (sin query string, sin barras duplicadas).
     */
    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    /**
     * Registra una ruta GET. $pattern puede contener {id} para parámetros.
     * Ej: get('/admin/forms/{id}', $handler)
     */
    public static function get(string $pattern, callable $handler): void
    {
        self::add('GET', $pattern, $handler);
    }

    public static function post(string $pattern, callable $handler): void
    {
        self::add('POST', $pattern, $handler);
    }

    private static function add(string $method, string $pattern, callable $handler): void
    {
        $key = $method . ' ' . $pattern;
        self::$routes[$key] = $handler;
    }

    /**
     * Busca un handler para el path y método actual. Devuelve [handler, params] o null.
     */
    public static function match(): ?array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = self::path();

        foreach (self::$routes as $key => $handler) {
            if (strpos($key, $method . ' ') !== 0) {
                continue;
            }
            $pattern = substr($key, strlen($method) + 1);
            $params = self::matchPattern($pattern, $path);
            if ($params !== null) {
                return [$handler, $params];
            }
        }
        return null;
    }

    /**
     * Convierte patrón tipo /admin/forms/{id} en regex y extrae parámetros.
     */
    private static function matchPattern(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $path, $m)) {
            return null;
        }
        $params = [];
        foreach ($m as $k => $v) {
            if (!is_int($k)) {
                $params[$k] = $v;
            }
        }
        return $params;
    }
}
