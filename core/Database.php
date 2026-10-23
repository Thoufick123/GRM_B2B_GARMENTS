<?php
namespace Core;

class Database
{
    private static ?\PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): \PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        return self::connect();
    }

    public static function reconnect(): \PDO
    {
        self::$instance = null;
        return self::connect();
    }

    private static function connect(): \PDO
    {
        $config = require CONFIG_PATH . '/database.php';
        $tz = defined('APP_TIMEZONE') ? APP_TIMEZONE : 'Asia/Kolkata';
        $offset = (new \DateTime('now', new \DateTimeZone($tz)))->format('P');
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,    // Real prepared statements for security & speed
            \PDO::ATTR_STRINGIFY_FETCHES   => false,   // Return native PHP types (int/float), not strings
            \PDO::ATTR_PERSISTENT          => false,   // Disable persistent connection to avoid stale SSL sockets closed by remote cloud DB
            \PDO::ATTR_TIMEOUT             => 10,      // Connection timeout in seconds
            \PDO::MYSQL_ATTR_INIT_COMMAND  => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '{$offset}'",
        ];

        // If remote Cloud Database (TiDB / AWS RDS) explicitly requires SSL and is not localhost
        $isLocalHost = in_array(strtolower(trim((string)($config['host'] ?? ''))), ['localhost', '127.0.0.1', '::1']);
        if (!empty($config['ssl']) && !$isLocalHost) {
            $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $caPath = dirname(__DIR__) . '/cacert.pem';
            if (file_exists($caPath)) {
                $options[\PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            }
        }

        $maxRetries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                self::$instance = @new \PDO($dsn, $config['user'], $config['pass'], $options);
                return self::$instance;
            } catch (\Throwable $e) {
                $lastException = $e;

                // Self-healing fallback: If SSL was configured but the MySQL server doesn't support it (e.g. Hostinger local MySQL)
                if (isset($options[\PDO::MYSQL_ATTR_SSL_CA]) || isset($options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT])) {
                    try {
                        unset($options[\PDO::MYSQL_ATTR_SSL_CA], $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
                        self::$instance = @new \PDO($dsn, $config['user'], $config['pass'], $options);
                        return self::$instance;
                    } catch (\Throwable $fallbackEx) {
                        $lastException = $fallbackEx;
                    }
                }

                if ($attempt < $maxRetries) {
                    usleep(200000); // Wait 200ms before retry
                    continue;
                }
                throw new \Exception("Database connection failed: " . $lastException->getMessage());
            }
        }

        return self::$instance;
    }
}
