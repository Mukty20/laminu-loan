<?php
/**
 * Lamuni — Database Layer
 * ----------------------------------------
 * PDO singleton. Reads credentials from .env (if present),
 * otherwise falls back to hard-coded XAMPP defaults.
 *
 * Usage:
 *   $pdo = Database::getConnection();
 */

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns the shared PDO instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        // Load .env from project root (one level above /src)
        $envFile = __DIR__ . '/../.env';
        $env = [];
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
        }

        $host   = $env['DB_HOST'] ?? 'localhost';
        $dbName = $env['DB_NAME'] ?? 'lamuni';
        $user   = $env['DB_USER'] ?? 'root';
        $pass   = $env['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Surface a human-readable error instead of a stack trace
            http_response_code(500);
            die('<h2>Database connection failed</h2><p>Check your .env file or src/Database.php credentials.</p><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }

    // Prevent instantiation
    private function __construct() {}
    private function __clone() {}
}
