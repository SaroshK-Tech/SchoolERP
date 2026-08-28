<?php
declare(strict_types=1);

/**
 * Front controller + router for SchoolERP.
 * Run locally:  php -S 127.0.0.1:8000 -t public public/index.php
 * Or point Apache's document root at public/.
 */

$base = dirname(__DIR__);
require $base . '/app/App.php';
require $base . '/app/Database.php';
require $base . '/app/Auth.php';
require $base . '/app/helpers.php';
require $base . '/app/view.php';
require $base . '/app/Router.php';
require $base . '/app/Schema.php';

App::boot();

// Fail fast with a clear message if the PHP mysqli extension is missing.
check_mysqli();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$requestPath = '/' . ltrim($requestPath, '/');
$isBypass = in_array($requestPath, ['/login', '/install.php'], true)
            || str_starts_with($requestPath, '/assets/');

// If DB/schema is missing and this isn't a bypass route, guide to the installer.
if (!$isBypass && !Schema::installed()) {
    $baseUrl = rtrim((string)App::config('app.base_url', ''), '/');
    header('Location: ' . ($baseUrl ?: '') . '/install.php');
    exit;
}

require_once $base . '/app/routes.php';

$route = new Router();
register_routes($route);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = urldecode($path);
// Strip base_url prefix if configured
$baseUrl = rtrim((string)App::config('app.base_url', ''), '/');
if ($baseUrl && str_starts_with($path, $baseUrl)) {
    $path = substr($path, strlen($baseUrl));
}

$route->dispatch($method, $path);
