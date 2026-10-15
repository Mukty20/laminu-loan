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
        if (is_file($envFile)) {
            if (!is_readable($envFile)) {
                throw new RuntimeException('The .env configuration file is not readable.');
            }
            $contents = file_get_contents($envFile);
            if ($contents === false) {
                throw new RuntimeException('Unable to read the .env configuration file.');
            }

            foreach (preg_split('/\r\n|\r|\n/', $contents) as $lineNumber => $line) {
                if ($lineNumber === 0) {
                    $line = ltrim($line, "\xEF\xBB\xBF");
                }
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                    continue;
                }

                if (!preg_match('/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=(.*)$/', $line, $matches)) {
                    throw new RuntimeException('Invalid .env syntax on line ' . ($lineNumber + 1) . '.');
                }

                $value = trim($matches[2]);
                if (strlen($value) >= 2) {
                    $quote = $value[0];
                    if (($quote === '"' || $quote === "'") && substr($value, -1) === $quote) {
                        $value = substr($value, 1, -1);
                    }
                }

                $env[$matches[1]] = $value;
            }
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
            error_log('Lamuni database connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('<h2>Database connection failed</h2><p>Check your .env file or database credentials.</p>');
        }
    }

    // Prevent instantiation
    private function __construct() {}
    private function __clone() {}
}
