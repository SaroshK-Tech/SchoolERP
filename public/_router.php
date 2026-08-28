<?php
/**
 * Router file for PHP's built-in dev server.
 * Run:  php -S 127.0.0.1:8000 -t public public/_router.php
 * Serves existing static files directly and routes everything else to index.php.
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static file
}
require __DIR__ . '/index.php';
