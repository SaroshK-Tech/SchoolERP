<?php
declare(strict_types=1);

class App
{
    private static array $config = [];

    public static function boot(): void
    {
        $base = dirname(__DIR__);
        $config = require $base . '/config/config.php';

        // Merge per-machine override if present.
        $local = $base . '/config/local.php';
        if (is_file($local)) {
            $config = array_replace_recursive($config, require $local);
        }
        self::$config = $config;

        date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

        if (($config['app']['debug'] ?? false)) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_name($config['security']['session_name'] ?? 'schoolerp_sess');
            session_start();
        }
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $cur = self::$config;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) {
                return $default;
            }
            $cur = $cur[$p];
        }
        return $cur;
    }

    public static function basePath(string $p = ''): string
    {
        return dirname(__DIR__) . ($p ? DIRECTORY_SEPARATOR . ltrim($p, '/\\') : '');
    }

    public static function viewPath(string $view): string
    {
        return self::basePath('app/views/' . $view . '.php');
    }

    public static function url(string $path = ''): string
    {
        $base = \App::config('app.base_url', '');
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . self::url($path));
        exit;
    }
}
