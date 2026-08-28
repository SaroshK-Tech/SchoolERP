<?php
declare(strict_types=1);

/**
 * CLI installer: creates the school_erp database and loads schema + seed data.
 *
 * Usage (from the project root):
 *   php database/install.php
 *
 * Connects as the configured MySQL user (root/empty by default). If MySQL
 * rejects the credentials, edit config/config.php (or a config/local.php) with
 * the correct DB credentials first.
 */

$root = dirname(__DIR__);
$config = array_replace_recursive(
    require $root . '/config/config.php',
    is_file($root . '/config/local.php') ? require $root . '/config/local.php' : []
);

$db = $config['db'];
echo "Installing SchoolERP database...\n";
echo "Target: {$db['user']}@{$db['host']}:{$db['port']}  db={$db['name']}\n";

if (!function_exists('mysqli_connect')) {
    fwrite(STDERR, "ERROR: The PHP mysqli extension is not loaded.\n");
    fwrite(STDERR, "This PHP build has no mysqli support. Use the XAMPP PHP:\n");
    fwrite(STDERR, "  C:\\xampp\\php\\php.exe database/install.php\n");
    fwrite(STDERR, "Current PHP binary: " . PHP_BINARY . "\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db['host'], $db['user'], $db['pass'], '', (int)$db['port']);
if ($conn->connect_error) {
    fwrite(STDERR, "Cannot connect to MySQL as '{$db['user']}'.\n");
    fwrite(STDERR, "Error: {$conn->connect_error}\n");
    fwrite(STDERR, "Fix the credentials in config/config.php (or config/local.php), then re-run.\n");
    exit(1);
}
echo "Connected.\n";

$schema = file_get_contents($root . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "Could not read database/schema.sql\n");
    exit(1);
}

// Split schema into statements (schema.sql uses ';' terminators without procedures).
$statements = array_filter(array_map('trim', explode(';', $schema)), fn($s) => $s !== '');
foreach ($statements as $stmt) {
    if ($conn->query($stmt) === false) {
        fwrite(STDERR, "Error executing:\n$stmt\n  -> {$conn->error}\n");
        // Continue - CREATE DATABASE / USE may already exist etc.
    }
}

echo "\nDatabase '{$db['name']}' is ready.\n";
echo "Default admin login:  admin / admin123\n";
$conn->close();
exit(0);
