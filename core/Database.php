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
        $hostLower = strtolower(trim((string)($config['host'] ?? '')));
        $isLocalHost = in_array($hostLower, ['localhost', '127.0.0.1', '::1']);
        $isTiDB = str_contains($hostLower, 'tidbcloud.com') || str_contains($hostLower, 'pingcap');
        $needsSSL = (!empty($config['ssl']) || $isTiDB) && !$isLocalHost;

        if ($needsSSL) {
            $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            
            // Locate cacert.pem across multiple possible standard paths
            $candidatePaths = [
                defined('BASE_PATH') ? BASE_PATH . '/cacert.pem' : '',
                dirname(__DIR__) . '/cacert.pem',
                __DIR__ . '/../cacert.pem',
                'C:/xampp/htdocs/GRM b2b/GRM_B2B_GARMENTS/cacert.pem'
            ];
            foreach ($candidatePaths as $p) {
                if (!empty($p) && file_exists($p)) {
                    $options[\PDO::MYSQL_ATTR_SSL_CA] = realpath($p) ?: $p;
                    break;
                }
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

                if ($attempt < $maxRetries) {
                    usleep(300000); // Wait 300ms before retry
                    continue;
                }
                throw new \Exception("Database connection failed: " . $lastException->getMessage());
            }
        }

        return self::$instance;
    }
}
