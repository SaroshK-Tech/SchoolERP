<?php
declare(strict_types=1);

class Database
{
    private static ?mysqli $instance = null;

    public static function conn(): mysqli
    {
        if (self::$instance instanceof mysqli && !self::$instance->connect_errno) {
            return self::$instance;
        }

        if (!function_exists('mysqli_connect')) {
            throw new RuntimeException('The PHP mysqli extension is not loaded. SchoolERP requires mysqli. Use the XAMPP PHP (C:\xampp\php\php.exe).');
        }

        $c = App::config('db');
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli($c['host'], $c['user'], $c['pass'], $c['name'], (int)$c['port']);
        $db->set_charset($c['charset'] ?? 'utf8mb4');

        self::$instance = $db;
        return $db;
    }

    /** Run a prepared statement and return the statement (caller fetches). */
    public static function run(string $sql, array $params = []): mysqli_stmt
    {
        $db = self::conn();
        $stmt = $db->prepare($sql);
        if ($params) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p))      $types .= 'i';
                elseif (is_float($p))$types .= 'd';
                elseif (is_null($p)) $types .= 's';
                else                 $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /** Fetch all rows as associative array. */
    public static function all(string $sql, array $params = []): array
    {
        $r = self::run($sql, $params)->get_result();
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Fetch a single row (assoc) or null. */
    public static function one(string $sql, array $params = []): ?array
    {
        $rows = self::all($sql, $params);
        return $rows[0] ?? null;
    }

    /** Fetch a single scalar value or null. */
    public static function scalar(string $sql, array $params = []): mixed
    {
        $row = self::one($sql, $params);
        if (!$row) return null;
        return reset($row);
    }

    /** Run insert, return last insert id. */
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int)self::conn()->insert_id;
    }

    /** Run update/delete, return affected rows. */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::run($sql, $params);
        return (int)$stmt->affected_rows;
    }

    /** Escape a raw value (for building IN(...) lists etc.). Use sparingly. */
    public static function quote(mixed $v): string
    {
        return "'" . self::conn()->real_escape_string((string)$v) . "'";
    }
}
