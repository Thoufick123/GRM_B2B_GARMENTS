<?php
/**
 * Migration: Create rate_limits table and add Razorpay columns to orders and refund_requests.
 * Run via CLI: php config/migrate_rate_limits_and_razorpay.php
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "Connected to database successfully.\n";

    // 1. Create rate_limits table
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
    echo "[OK] Table 'rate_limits' created / verified.\n";

    // 2. Add razorpay_order_id to orders
    $ordersCols = $db->query("SHOW COLUMNS FROM `orders`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('razorpay_order_id', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `razorpay_order_id` VARCHAR(100) NULL AFTER `cashfree_order_id`");
        echo "[OK] Column 'razorpay_order_id' added to 'orders'.\n";
    } else {
        echo "[SKIP] Column 'razorpay_order_id' already exists in 'orders'.\n";
    }

    // 3. Add razorpay_payment_id to orders
    if (!in_array('razorpay_payment_id', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `razorpay_payment_id` VARCHAR(100) NULL AFTER `razorpay_order_id`");
        echo "[OK] Column 'razorpay_payment_id' added to 'orders'.\n";
    } else {
        echo "[SKIP] Column 'razorpay_payment_id' already exists in 'orders'.\n";
    }

    // 4. Add razorpay_refund_id to refund_requests if table exists
    $tables = $db->query("SHOW TABLES LIKE 'refund_requests'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($tables)) {
        $refundCols = $db->query("SHOW COLUMNS FROM `refund_requests`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('razorpay_refund_id', $refundCols)) {
            $db->exec("ALTER TABLE `refund_requests` ADD COLUMN `razorpay_refund_id` VARCHAR(100) NULL AFTER `cashfree_refund_id`");
            echo "[OK] Column 'razorpay_refund_id' added to 'refund_requests'.\n";
        } else {
            echo "[SKIP] Column 'razorpay_refund_id' already exists in 'refund_requests'.\n";
        }
    }

    // 5. Add tax_verified and verification_data to business_profiles
    $bpTables = $db->query("SHOW TABLES LIKE 'business_profiles'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($bpTables)) {
        $bpCols = $db->query("SHOW COLUMNS FROM `business_profiles`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('tax_verified', $bpCols)) {
            $db->exec("ALTER TABLE `business_profiles` ADD COLUMN `tax_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `instagram_profile`");
            echo "[OK] Column 'tax_verified' added to 'business_profiles'.\n";
        } else {
            echo "[SKIP] Column 'tax_verified' already exists in 'business_profiles'.\n";
        }

        if (!in_array('verification_data', $bpCols)) {
            $db->exec("ALTER TABLE `business_profiles` ADD COLUMN `verification_data` LONGTEXT NULL AFTER `tax_verified`");
            echo "[OK] Column 'verification_data' added to 'business_profiles'.\n";
        } else {
            echo "[SKIP] Column 'verification_data' already exists in 'business_profiles'.\n";
        }
    }

    echo "\nAll migrations completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
