<?php
declare(strict_types=1);

/**
 * Web-based installer.
 * When the app can't reach the database, users are pointed here (or you can
 * visit /install.php directly). It creates the database + schema + seed data
 * using the configured DB credentials, then reports the default admin login.
 *
 * SECURITY: after a successful install you should DELETE this file.
 */

$root = dirname(__DIR__);
$config = array_replace_recursive(
    require $root . '/config/config.php',
    is_file($root . '/config/local.php') ? require $root . '/config/local.php' : []
);
$db = $config['db'];

function install_html_start(string $title): void {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="stylesheet" href="assets/css/app.css">';
    echo '</head><body><div class="auth-wrap"><div class="auth-box"><div class="auth-head"><h1>SchoolERP Installer</h1><p>' . htmlspecialchars($title) . '</p></div><div class="auth-body">';
}
function install_html_end(): void {
    echo '</div></div></div></body></html>';
}

$action = $_GET['run'] ?? '';

if ($action !== 'install') {
    install_html_start('Setup required');
    echo '<p class="text-muted">The SchoolERP database has not been installed, or the app cannot connect to it.</p>';
    echo '<p><strong>Target:</strong> <code>' . htmlspecialchars($db['user'] . '@' . $db['host'] . ':' . $db['port'] . ' / ' . $db['name']) . '</code></p>';
    echo '<form method="get" action="install.php"><input type="hidden" name="run" value="install">';
    echo '<button type="submit" class="btn btn-primary btn-block">Install Database</button></form>';
    echo '<p style="margin-top:12px;" class="text-muted">If connection fails, edit <code>config/config.php</code> (or create <code>config/local.php</code>) with the correct DB credentials.</p>';
    install_html_end();
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db['host'], $db['user'], $db['pass'], '', (int)$db['port']);

install_html_start('Installing…');
if ($conn->connect_error) {
    echo '<div class="alert alert-danger">Could not connect to MySQL as <code>' . htmlspecialchars($db['user']) . '</code>: ' . htmlspecialchars($conn->connect_error) . '</div>';
    echo '<p class="text-muted">Verify DB credentials in <code>config/config.php</code> (or <code>config/local.php</code>), then reload this page.</p>';
    echo '<a class="btn btn-outline" href="install.php">Try again</a>';
    install_html_end();
    exit;
}

$schema = file_get_contents($root . '/database/schema.sql');
if ($schema === false) {
    echo '<div class="alert alert-danger">Could not read database/schema.sql</div>';
    install_html_end();
    exit;
}

$errors = [];
$statements = array_filter(array_map('trim', explode(';', $schema)), fn($s) => $s !== '');
foreach ($statements as $stmt) {
    if ($conn->query($stmt) === false && stripos($conn->error, 'already exists') === false) {
        $errors[] = htmlspecialchars($conn->error);
    }
}

if ($errors) {
    echo '<div class="alert alert-danger">Installation hit errors:<br>' . nl2br(implode("\n", array_slice($errors, 0, 8))) . '</div>';
} else {
    echo '<div class="alert alert-success">Database <strong>' . htmlspecialchars($db['name']) . '</strong> created and seeded successfully.</div>';
    echo '<p><strong>Default login:</strong> username <code>admin</code> / password <code>admin123</code></p>';
    echo '<p style="margin-top:12px;" class="text-muted">For security, delete <code>public/install.php</code> now.</p>';
    echo '<a class="btn btn-primary" href="index.php">Go to Login</a>';
}
$conn->close();
install_html_end();
