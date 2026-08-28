<?php
declare(strict_types=1);

/**
 * Global helper functions.
 */

/**
 * Ensure the mysqli extension is loaded. If not (e.g. the app is run with a PHP
 * build that lacks mysqli), show a clear, actionable error instead of a fatal
 * "Call to undefined function mysqli_report()" crash.
 *
 * When $cli is true, prints a plain-text message and exits with a non-zero code
 * (used by the CLI installer).
 */
function check_mysqli(bool $cli = false): void
{
    if (function_exists('mysqli_connect')) {
        return;
    }

    if ($cli) {
        fwrite(STDERR, "ERROR: The PHP mysqli extension is not loaded.\n");
        fwrite(STDERR, "This PHP build has no mysqli support, so SchoolERP cannot connect to MySQL.\n");
        fwrite(STDERR, "Use the XAMPP PHP (C:\\xampp\\php\\php.exe), which has mysqli enabled.\n");
        exit(1);
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PHP mysqli missing</title>';
    echo '<link rel="stylesheet" href="assets/css/app.css"></head><body>';
    echo '<div class="auth-wrap"><div class="auth-box"><div class="auth-head"><h1>Configuration error</h1>';
    echo '<p>The PHP mysqli extension is not loaded</p></div><div class="auth-body">';
    echo '<div class="alert alert-danger">SchoolERP requires the <strong>mysqli</strong> PHP extension, but the '
        . 'current PHP build does not have it. Start the dev server with the XAMPP PHP which ships mysqli enabled:';
    echo '<pre style="background:#0f172a;color:#a5f3fc;padding:12px;border-radius:8px;overflow:auto;">'
        . htmlspecialchars('C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public public\_router.php') . '</pre>';
    echo '<p class="text-muted">Current PHP: ' . htmlspecialchars(phpversion() ?: 'unknown')
        . ' &nbsp;&mdash;&nbsp; ' . htmlspecialchars(PHP_BINARY) . '</p>';
    echo '</div></div></div></body></html>';
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    App::redirect($path);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $_SESSION['_old'][$key] ?? $default;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_drain(): array
{
    $msgs = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $msgs;
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

/** Guard: require a logged in user; redirect to login otherwise. */
function require_login(): void
{
    if (!Auth::check()) {
        redirect('login');
    }
}

/** Is the current user a superadmin (full authorization)? */
function is_superadmin(): bool
{
    return (Auth::user()['role'] ?? '') === 'superadmin';
}

/** Is the current user an admin or superadmin? */
function is_admin(): bool
{
    return in_array(Auth::user()['role'] ?? '', ['admin', 'superadmin'], true);
}

/** Guard: require one of the given roles. Superadmin bypasses every check. */
function require_role(array $roles): void
{
    require_login();
    if (is_superadmin()) return;
    if (!in_array(Auth::user()['role'] ?? '', $roles, true)) {
        flash_set('danger', 'You do not have permission to access that area.');
        redirect('dashboard');
    }
}

/** Guard: only the superadmin may pass. */
function require_superadmin(): void
{
    require_login();
    if (!is_superadmin()) {
        flash_set('danger', 'Only the superadmin may perform this action.');
        redirect('dashboard');
    }
}

/**
 * Is the given staff id linked to a superadmin user account?
 * Superadmin records are protected: no other role may view/edit/delete them.
 */
function is_protected_staff(int $staffId): bool
{
    if ($staffId <= 0) return false;
    $row = Database::one(
        "SELECT u.id FROM users u WHERE u.staff_id = ? AND u.role = 'superadmin' LIMIT 1",
        [$staffId]
    );
    return (bool)$row;
}

/** May the current user manage the given staff record (false if it is a protected superadmin and they are not superadmin)? */
function can_manage_staff(int $staffId): bool
{
    if (is_superadmin()) return true;
    return !is_protected_staff($staffId);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrf_token(), (string)$sent)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
}

/** Human friendly date. */
function fmt_date(?string $d): string
{
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date('M j, Y', $ts) : $d;
}

function fmt_date_time(?string $d): string
{
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date('M j, Y g:i A', $ts) : $d;
}

function money(mixed $n): string
{
    return number_format((float)($n ?? 0), 2);
}
