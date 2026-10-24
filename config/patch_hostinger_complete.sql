-- ==============================================================================
-- GRM B2B — Hostinger Database Complete Synchronization Patch
-- Fixes: 500 Internal Server Error (Missing razorpay_order_id, courier, tracking, and cancellation columns)
-- Safe & Idempotent (Can be executed directly in Hostinger phpMyAdmin)
-- ==============================================================================

-- 1. Create rate_limits table if missing
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

-- 2. Create courier_services table if missing
CREATE TABLE IF NOT EXISTS `courier_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `default_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `courier_services` (`name`, `default_url`, `is_active`) VALUES
('ST Courier', 'https://stcourier.com/track', 1),
('Professional Couriers', 'https://www.tpcindia.com', 1),
('DTDC', 'https://www.dtdc.in', 1),
('Delhivery', 'https://www.delhivery.com', 1),
('Blue Dart', 'https://www.bluedart.com', 1),
('India Post', 'https://www.indiapost.gov.in', 1);

-- 3. Create cancellation_rules table if missing
CREATE TABLE IF NOT EXISTS `cancellation_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rule_name` VARCHAR(255) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'both',
    `cancellation_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `refund_percentage` INT NOT NULL DEFAULT 100,
    `sla_days` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` BIGINT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create refund_requests table if missing
CREATE TABLE IF NOT EXISTS `refund_requests` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT NOT NULL,
    `user_id` BIGINT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `reason` TEXT NOT NULL,
    `cancellation_type` ENUM('full', 'partial') NOT NULL DEFAULT 'full',
    `refund_method` VARCHAR(50) NULL,
    `refund_details` TEXT NULL,
    `status` ENUM(
        'pending_admin_approval',
        'processing',
        'completed',
        'rejected'
    ) NOT NULL DEFAULT 'pending_admin_approval',
    `cashfree_refund_id` VARCHAR(100) NULL,
    `razorpay_refund_id` VARCHAR(100) NULL,
    `cashfree_order_id` VARCHAR(100) NULL,
    `admin_notes` TEXT NULL,
    `processed_by` BIGINT NULL,
    `processed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rr_order_id` (`order_id`),
    INDEX `idx_rr_user_id` (`user_id`),
    INDEX `idx_rr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Extend orders table status ENUM to include refund states
ALTER TABLE `orders` MODIFY COLUMN `status` ENUM(
    'pending',
    'placed',
    'packed',
    'shipped',
    'out_for_delivery',
    'delivered',
    'cancelled',
    'refund_pending',
    'refund_processing',
    'refund_completed',
    'refund_failed'
) NOT NULL DEFAULT 'pending';

-- 6. Add missing columns to orders table
-- (Executed safely with IF NOT EXISTS)
ALTER TABLE `orders` 
    ADD COLUMN IF NOT EXISTS `courier_name` VARCHAR(100) NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `tracking_number` VARCHAR(100) NULL AFTER `courier_name`,
    ADD COLUMN IF NOT EXISTS `tracking_url` VARCHAR(255) NULL AFTER `tracking_number`,
    ADD COLUMN IF NOT EXISTS `cancellation_reason` VARCHAR(500) NULL,
    ADD COLUMN IF NOT EXISTS `cancelled_by_role` VARCHAR(20) NULL DEFAULT NULL AFTER `cancellation_reason`,
    ADD COLUMN IF NOT EXISTS `cancelled_at` DATETIME NULL AFTER `cancelled_by_role`,
    ADD COLUMN IF NOT EXISTS `cashfree_order_id` VARCHAR(100) NULL AFTER `cancelled_at`,
    ADD COLUMN IF NOT EXISTS `razorpay_order_id` VARCHAR(100) NULL AFTER `cashfree_order_id`,
    ADD COLUMN IF NOT EXISTS `razorpay_payment_id` VARCHAR(100) NULL AFTER `razorpay_order_id`,
    ADD COLUMN IF NOT EXISTS `packing_video_1` VARCHAR(255) NULL AFTER `notes`,
    ADD COLUMN IF NOT EXISTS `packing_video_2` VARCHAR(255) NULL AFTER `packing_video_1`,
    ADD COLUMN IF NOT EXISTS `packed_at` DATETIME NULL AFTER `packing_video_2`,
    ADD COLUMN IF NOT EXISTS `delivered_at` DATETIME NULL AFTER `packed_at`,
    ADD COLUMN IF NOT EXISTS `packing_video_expires_at` DATETIME NULL AFTER `delivered_at`;

-- 7. Add missing columns to refund_requests table
ALTER TABLE `refund_requests`
    ADD COLUMN IF NOT EXISTS `refund_method` VARCHAR(50) NULL AFTER `cancellation_type`,
    ADD COLUMN IF NOT EXISTS `refund_details` TEXT NULL AFTER `refund_method`,
    ADD COLUMN IF NOT EXISTS `cashfree_refund_id` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `razorpay_refund_id` VARCHAR(100) NULL AFTER `cashfree_refund_id`,
    ADD COLUMN IF NOT EXISTS `cashfree_order_id` VARCHAR(100) NULL AFTER `razorpay_refund_id`;

-- 8. Add tax verification columns to business_profiles table
ALTER TABLE `business_profiles`
    ADD COLUMN IF NOT EXISTS `tax_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `instagram_profile`,
    ADD COLUMN IF NOT EXISTS `verification_data` LONGTEXT NULL AFTER `tax_verified`;

-- 9. Add profile picture to users table
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `profile_picture` VARCHAR(500) NULL AFTER `phone`;
