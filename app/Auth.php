<?php
declare(strict_types=1);

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = Database::one(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => (int)App::config('security.password_cost', 12)])) {
            Database::execute(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [password_hash($password, PASSWORD_BCRYPT, ['cost' => (int)App::config('security.password_cost', 12)]), (int)$user['id']]
            );
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$user['id'];
        Database::execute("UPDATE users SET last_login_at = NOW() WHERE id = ?", [(int)$user['id']]);
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['uid']);
    }

    /** Current user (users row joined with staff), or null. */
    public static function user(): ?array
    {
        if (!self::check()) return null;
        return Database::one(
            "SELECT u.*, s.first_name, s.last_name, s.employee_no, s.profile_photo,
                    CONCAT(s.first_name, ' ', s.last_name) AS full_name
             FROM users u
             LEFT JOIN staff s ON s.id = u.staff_id
             WHERE u.id = ?",
            [$_SESSION['uid']]
        );
    }

    public static function id(): ?int
    {
        return $_SESSION['uid'] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION['uid']);
        session_regenerate_id(true);
    }
}
