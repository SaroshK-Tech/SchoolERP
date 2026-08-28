<?php
declare(strict_types=1);

/**
 * Global helper functions.
 */

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

/** Guard: require one of the given roles. */
function require_role(array $roles): void
{
    require_login();
    if (!in_array(Auth::user()['role'] ?? '', $roles, true)) {
        flash_set('danger', 'You do not have permission to access that area.');
        redirect('dashboard');
    }
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
