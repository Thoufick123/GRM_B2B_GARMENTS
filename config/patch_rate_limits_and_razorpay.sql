-- ==============================================================================
-- Database Patch: rate_limits table & Razorpay tracking columns
-- Target DB: MySQL / TiDB / Hostinger (u405685755_grm_b2b)
-- ==============================================================================

-- 1. Create rate_limits table for checkout & OTP rate limiting
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

-- 2. Add Razorpay Order and Payment tracking columns to orders table
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `razorpay_order_id` VARCHAR(100) NULL AFTER `cashfree_order_id`,
    ADD COLUMN IF NOT EXISTS `razorpay_payment_id` VARCHAR(100) NULL AFTER `razorpay_order_id`;

-- 3. Add Razorpay Refund reference tracking column to refund_requests table (if refund_requests exists)
ALTER TABLE `refund_requests`
    ADD COLUMN IF NOT EXISTS `razorpay_refund_id` VARCHAR(100) NULL AFTER `cashfree_refund_id`;

-- 4. Add tax_verified and verification_data to business_profiles table
ALTER TABLE `business_profiles`
    ADD COLUMN IF NOT EXISTS `tax_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `instagram_profile`,
    ADD COLUMN IF NOT EXISTS `verification_data` LONGTEXT NULL AFTER `tax_verified`;

