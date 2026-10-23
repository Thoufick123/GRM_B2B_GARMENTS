<?php
namespace Core;

class RateLimiter
{
    private static bool $tableChecked = false;

    private static function ensureTableExists(\PDO $db): void
    {
        if (self::$tableChecked) {
            return;
        }
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `rate_limits` (
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `key_name` VARCHAR(191) NOT NULL UNIQUE,
                    `hits` INT NOT NULL DEFAULT 1,
                    `expires_at` BIGINT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_rate_limits_key` (`key_name`),
                    INDEX `idx_rate_limits_expires` (`expires_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            self::$tableChecked = true;
        } catch (\Throwable $t) {
            error_log("RateLimiter table creation warning: " . $t->getMessage());
        }
    }

    /**
     * Tries to consume a token from the rate limit bucket.
     * Returns true if allowed, false if limit exceeded.
     */
    public static function attempt(string $key, int $maxHits, int $decaySeconds): bool
    {
        try {
            $db = Database::getInstance();
            self::ensureTableExists($db);

            $now = time();
            $expires = $now + $decaySeconds;

            // Clean up expired limits
            $db->exec("DELETE FROM rate_limits WHERE expires_at < $now");

            $stmt = $db->prepare("SELECT hits FROM rate_limits WHERE key_name = :key FOR UPDATE");
            $stmt->execute(['key' => $key]);
            $row = $stmt->fetch();

            if ($row) {
                if ($row['hits'] >= $maxHits) {
                    return false; // Rate limit exceeded
                }
                // Increment
                $db->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE key_name = :key")->execute(['key' => $key]);
                return true;
            } else {
                // Create new record
                $db->prepare("INSERT INTO rate_limits (key_name, hits, expires_at) VALUES (:key, 1, :expires)")
                   ->execute(['key' => $key, 'expires' => $expires]);
                return true;
            }
        } catch (\Throwable $e) {
            // Fail open so database issues never block valid users from placing an order
            error_log("RateLimiter::attempt failed: " . $e->getMessage());
            return true;
        }
    }
}
