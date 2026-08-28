<?php
declare(strict_types=1);

class Schema
{
    /** True if the app database + core tables exist and are reachable. */
    public static function installed(): bool
    {
        try {
            $db = App::config('db');
            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli($db['host'], $db['user'], $db['pass'], $db['name'], (int)$db['port']);
            if ($conn->connect_error) {
                return false;
            }
            $r = $conn->query("SHOW TABLES LIKE 'users'");
            $ok = $r !== false && $r->num_rows > 0;
            $conn->close();
            return $ok;
        } catch (Throwable $e) {
            return false;
        }
    }
}
