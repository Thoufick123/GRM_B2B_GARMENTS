<?php
/**
 * Master Database Migration Script for GRM B2B Marketplace
 * Creates all application tables with full relational integrity and seeds defaults.
 * Safe to run multiple times (idempotent with IF NOT EXISTS and INSERT IGNORE).
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "========================================================\n";
    echo " Starting Master Database Migration for GRM B2B...\n";
    echo "========================================================\n";

    // Disable foreign key checks during creation to prevent ordering issues
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tables = [

        // 1. ROLES
        "roles" => "
        CREATE TABLE IF NOT EXISTS `roles` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `permissions` LONGTEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 2. USERS
        "users" => "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `unique_buyer_id` VARCHAR(50) NULL,
            `unique_vendor_id` VARCHAR(50) NULL,
            `name` VARCHAR(255) NULL,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `phone` VARCHAR(20) UNIQUE NOT NULL,
            `profile_picture` VARCHAR(500) NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL DEFAULT 'buyer',
            `role_id` BIGINT NULL,
            `status` ENUM('pending', 'under_review', 'approved', 'active', 'rejected', 'blocked', 'deactivated') NOT NULL DEFAULT 'pending',
            `permissions` LONGTEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_role` (`role`),
            INDEX `idx_users_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 3. SESSIONS
        "sessions" => "
        CREATE TABLE IF NOT EXISTS `sessions` (
            `id` VARCHAR(128) PRIMARY KEY,
            `data` MEDIUMTEXT NOT NULL,
            `timestamp` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sessions_timestamp` (`timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 4. PASSWORD RESETS
        "password_resets" => "
        CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL,
            `otp` VARCHAR(10) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_pr_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 5. USER EMAIL LOGS
        "user_email_logs" => "
        CREATE TABLE IF NOT EXISTS `user_email_logs` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `old_email` VARCHAR(255) NOT NULL,
            `new_email` VARCHAR(255) NOT NULL,
            `reason` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_uel_user_id` (`user_id`),
            INDEX `idx_uel_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 6. BUSINESS PROFILES
        "business_profiles" => "
        CREATE TABLE IF NOT EXISTS `business_profiles` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNIQUE NOT NULL,
            `business_name` VARCHAR(255) NOT NULL,
            `shop_location` VARCHAR(255) NULL,
            `gst_number` VARCHAR(15) NULL,
            `gst_document` VARCHAR(255) NULL,
            `pan_number` VARCHAR(10) NULL,
            `pan_document` VARCHAR(255) NULL,
            `aadhar_number` VARCHAR(12) NULL,
            `no_gst_reason` VARCHAR(255) NULL,
            `instagram_link` VARCHAR(255) NULL,
            `instagram_profile` VARCHAR(255) NULL,
            `tax_verified` TINYINT(1) NOT NULL DEFAULT 0,
            `verification_data` LONGTEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 7. REGISTRATION REQUESTS
        "registration_requests" => "
        CREATE TABLE IF NOT EXISTS `registration_requests` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `gst_flag` BOOLEAN NOT NULL DEFAULT FALSE,
            `status` ENUM('pending', 'under_review', 'approved', 'rejected', 'resubmission_required') NOT NULL DEFAULT 'pending',
            `rejection_reason` TEXT NULL,
            `reviewed_by` BIGINT NULL,
            `reviewed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 8. ADDRESSES
        "addresses" => "
        CREATE TABLE IF NOT EXISTS `addresses` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `label` VARCHAR(50) NULL,
            `line1` VARCHAR(255) NOT NULL,
            `line2` VARCHAR(255) NULL,
            `city` VARCHAR(100) NOT NULL,
            `state` VARCHAR(100) NOT NULL,
            `postal_code` VARCHAR(10) NOT NULL,
            `country` VARCHAR(50) NOT NULL DEFAULT 'India',
            `is_default` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 9. MEDIA (Local File Uploads)
        "media" => "
        CREATE TABLE IF NOT EXISTS `media` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_type` VARCHAR(50) NOT NULL,
            `purpose` VARCHAR(50) DEFAULT 'shop_verification',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 10. MEDIA UPLOADS (Cloudinary/Central Media Manager)
        "media_uploads" => "
        CREATE TABLE IF NOT EXISTS `media_uploads` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `original_filename` VARCHAR(255) NOT NULL,
            `file_hash` VARCHAR(64) NOT NULL,
            `cloudinary_url` TEXT NOT NULL,
            `public_id` VARCHAR(255) NOT NULL,
            `file_size` BIGINT DEFAULT 0,
            `uploaded_by` BIGINT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_media_hash` (`file_hash`),
            INDEX `idx_media_uploader` (`uploaded_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 11. VENDOR TYPES
        "vendor_types" => "
        CREATE TABLE IF NOT EXISTS `vendor_types` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `display_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vt_slug` (`slug`),
            INDEX `idx_vt_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 12. VENDOR PROFILES
        "vendor_profiles" => "
        CREATE TABLE IF NOT EXISTS `vendor_profiles` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNIQUE NOT NULL,
            `unique_vendor_id` VARCHAR(50) UNIQUE NULL,
            `store_name` VARCHAR(255) NOT NULL,
            `company_name` VARCHAR(255) NULL,
            `vendor_type` VARCHAR(100) NOT NULL DEFAULT 'wholesaler',
            `gst_number` VARCHAR(15) NULL,
            `pan_number` VARCHAR(10) NULL,
            `no_gst_reason` VARCHAR(255) NULL,
            `address` VARCHAR(255) NOT NULL,
            `city` VARCHAR(100) NOT NULL,
            `state` VARCHAR(100) NOT NULL,
            `pincode` VARCHAR(10) NOT NULL,
            `bank_name` VARCHAR(100) NULL,
            `account_number` VARCHAR(50) NULL,
            `ifsc_code` VARCHAR(20) NULL,
            `account_holder_name` VARCHAR(150) NULL,
            `upi_id` VARCHAR(100) NULL,
            `upi_name` VARCHAR(150) NULL,
            `commission_rate` DECIMAL(5, 2) NULL,
            `description` TEXT NULL,
            `shop_images` TEXT NULL,
            `gst_document` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 13. VENDOR REGISTRATION REQUESTS
        "vendor_registration_requests" => "
        CREATE TABLE IF NOT EXISTS `vendor_registration_requests` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `status` ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            `rejection_reason` TEXT NULL,
            `reviewed_by` BIGINT NULL,
            `reviewed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 14. VENDOR BANK ACCOUNTS
        "vendor_bank_accounts" => "
        CREATE TABLE IF NOT EXISTS `vendor_bank_accounts` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `vendor_id` BIGINT NOT NULL,
            `bank_name` VARCHAR(150) NOT NULL,
            `account_holder_name` VARCHAR(150) NOT NULL,
            `account_number` VARCHAR(50) NOT NULL,
            `ifsc_code` VARCHAR(25) NOT NULL,
            `branch_name` VARCHAR(150) NULL,
            `account_type` ENUM('current', 'savings') NOT NULL DEFAULT 'current',
            `upi_id` VARCHAR(100) NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vba_vendor` (`vendor_id`),
            INDEX `idx_vba_primary` (`is_primary`),
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 15. CATEGORIES
        "categories" => "
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) UNIQUE NOT NULL,
            `hsn_code` VARCHAR(50) NULL,
            `description` TEXT NULL,
            `image_path` VARCHAR(255) NULL,
            `moq` INT NOT NULL DEFAULT 10,
            `min_order_value` DECIMAL(10, 2) NULL DEFAULT NULL,
            `min_cart_value` DECIMAL(10, 2) NULL DEFAULT NULL,
            `commission_rate` DECIMAL(5, 2) NULL,
            `sgst` DECIMAL(5, 2) DEFAULT 0.00,
            `cgst` DECIMAL(5, 2) DEFAULT 0.00,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `is_customizable` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 16. SUB CATEGORIES
        "sub_categories" => "
        CREATE TABLE IF NOT EXISTS `sub_categories` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `category_id` BIGINT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) UNIQUE NOT NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `is_customizable` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 17. CATEGORY REQUESTS
        "category_requests" => "
        CREATE TABLE IF NOT EXISTS `category_requests` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `vendor_id` BIGINT NOT NULL,
            `request_type` ENUM('category', 'subcategory') NOT NULL DEFAULT 'category',
            `parent_category_id` BIGINT NULL,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `hsn_code` VARCHAR(50) NULL,
            `sgst` DECIMAL(5, 2) DEFAULT 0.00,
            `cgst` DECIMAL(5, 2) DEFAULT 0.00,
            `description` TEXT NULL,
            `attributes_json` LONGTEXT NULL,
            `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            `rejection_reason` TEXT NULL,
            `reviewed_by` BIGINT NULL,
            `reviewed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 18. ATTRIBUTES
        "attributes" => "
        CREATE TABLE IF NOT EXISTS `attributes` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `category_id` BIGINT NULL,
            `sub_category_id` BIGINT NULL,
            `name` VARCHAR(100) NOT NULL,
            `attribute_code` VARCHAR(100) NULL,
            `input_type` VARCHAR(50) DEFAULT 'textbox',
            `description` TEXT NULL,
            `display_order` INT DEFAULT 0,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `is_required` TINYINT(1) DEFAULT 0,
            `is_searchable` TINYINT(1) DEFAULT 0,
            `is_filterable` TINYINT(1) DEFAULT 0,
            `is_variant` TINYINT(1) DEFAULT 0,
            `show_on_product_form` TINYINT(1) DEFAULT 0,
            `is_visible_on_website` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_attr_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 19. ATTRIBUTE VALUES
        "attribute_values" => "
        CREATE TABLE IF NOT EXISTS `attribute_values` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `attribute_id` BIGINT NOT NULL,
            `value` VARCHAR(255) NOT NULL,
            `color_code` VARCHAR(50) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 20. SIZE CHARTS
        "size_charts" => "
        CREATE TABLE IF NOT EXISTS `size_charts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `category_id` INT NULL DEFAULT 0,
            `category_name` VARCHAR(255) NULL,
            `sub_category_id` INT NULL DEFAULT 0,
            `sub_category_name` VARCHAR(255) NULL,
            `dress_type` VARCHAR(100) NULL,
            `image_url` VARCHAR(500) NULL,
            `tolerance_note` VARCHAR(255) DEFAULT 'Size in inches (+ or - 0.5\")',
            `columns_json` TEXT NOT NULL,
            `rows_json` LONGTEXT NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 21. PRODUCTS
        "products" => "
        CREATE TABLE IF NOT EXISTS `products` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `vendor_id` BIGINT NULL,
            `category_id` BIGINT NOT NULL,
            `sub_category_id` BIGINT NULL,
            `size_chart_id` INT NULL,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) UNIQUE NOT NULL,
            `base_sku` VARCHAR(100) NULL,
            `description` TEXT NULL,
            `how_to_use` TEXT NULL,
            `why_choose` TEXT NULL,
            `custom_fields` TEXT NULL,
            `wholesale_price` DECIMAL(10, 2) NOT NULL,
            `retail_price` DECIMAL(10, 2) NULL,
            `weight` DECIMAL(10, 2) NULL,
            `weight_unit` VARCHAR(10) DEFAULT 'kg',
            `stock_quantity` INT NOT NULL DEFAULT 0,
            `moq_override` INT NULL,
            `max_amount` DECIMAL(10, 2) DEFAULT 0.00,
            `commission_rate` DECIMAL(5, 2) NULL,
            `sgst` DECIMAL(5, 2) DEFAULT 0.00,
            `cgst` DECIMAL(5, 2) DEFAULT 0.00,
            `total_gst` DECIMAL(5, 2) DEFAULT 0.00,
            `is_customizable` TINYINT(1) DEFAULT 0,
            `status` ENUM('active', 'draft', 'archived') NOT NULL DEFAULT 'draft',
            `approval_status` ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
            `rejection_reason` TEXT NULL,
            `approved_by` BIGINT NULL,
            `approved_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
            FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 22. PRODUCT VARIANTS
        "product_variants" => "
        CREATE TABLE IF NOT EXISTS `product_variants` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `product_id` BIGINT NOT NULL,
            `name` VARCHAR(255) NULL,
            `sku` VARCHAR(100) UNIQUE NULL,
            `size` VARCHAR(50) NULL,
            `color` VARCHAR(50) NULL,
            `base_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `discount_price` DECIMAL(10, 2) NULL,
            `weight` DECIMAL(10, 2) NULL,
            `weight_unit` VARCHAR(10) DEFAULT 'kg',
            `inventory` INT NOT NULL DEFAULT 0,
            `current_stock` INT NOT NULL DEFAULT 0,
            `stock` INT NOT NULL DEFAULT 0,
            `low_stock_alert` INT DEFAULT 5,
            `status` VARCHAR(50) DEFAULT 'active',
            `max_amount` DECIMAL(10, 2) DEFAULT 0.00,
            `sgst` DECIMAL(5, 2) DEFAULT 0.00,
            `cgst` DECIMAL(5, 2) DEFAULT 0.00,
            `total_gst` DECIMAL(5, 2) DEFAULT 0.00,
            `base_quantity` INT DEFAULT 2,
            `max_quantity` INT DEFAULT 4,
            `fabric_consumption` DECIMAL(8, 4) DEFAULT 1.0000,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 23. PRODUCT IMAGES
        "product_images" => "
        CREATE TABLE IF NOT EXISTS `product_images` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `product_id` BIGINT NOT NULL,
            `variant_id` BIGINT NULL,
            `image_path` VARCHAR(255) NOT NULL,
            `is_primary` BOOLEAN DEFAULT FALSE,
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 24. PRODUCT ATTRIBUTE VALUES
        "product_attribute_values" => "
        CREATE TABLE IF NOT EXISTS `product_attribute_values` (
            `product_id` BIGINT NOT NULL,
            `attribute_id` BIGINT NOT NULL,
            `attribute_value_id` BIGINT NOT NULL,
            `value_text` VARCHAR(255) NULL,
            PRIMARY KEY (`product_id`, `attribute_id`, `attribute_value_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 25. PRODUCT VARIANT ATTRIBUTES
        "product_variant_attributes" => "
        CREATE TABLE IF NOT EXISTS `product_variant_attributes` (
            `variant_id` BIGINT NOT NULL,
            `attribute_id` BIGINT NOT NULL,
            `attribute_value_id` BIGINT NOT NULL,
            PRIMARY KEY (`variant_id`, `attribute_id`, `attribute_value_id`),
            FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 26. PRODUCT VARIANT IMAGES
        "product_variant_images" => "
        CREATE TABLE IF NOT EXISTS `product_variant_images` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `variant_id` BIGINT NOT NULL,
            `image_path` VARCHAR(255) NOT NULL,
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 27. PRODUCT VOLUME TIERS
        "product_volume_tiers" => "
        CREATE TABLE IF NOT EXISTS `product_volume_tiers` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `product_id` BIGINT NOT NULL,
            `min_qty` INT NOT NULL,
            `max_qty` INT NULL,
            `tier_price` DECIMAL(10, 2) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 28. CART ITEMS
        "cart_items" => "
        CREATE TABLE IF NOT EXISTS `cart_items` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `product_id` BIGINT NOT NULL,
            `variant_id` BIGINT NULL,
            `customization_id` BIGINT NULL,
            `customization_data` LONGTEXT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 29. WISHLISTS
        "wishlists" => "
        CREATE TABLE IF NOT EXISTS `wishlists` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `product_id` BIGINT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `user_product_unique` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 30. ORDERS
        "orders" => "
        CREATE TABLE IF NOT EXISTS `orders` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT NOT NULL,
            `order_number` VARCHAR(50) UNIQUE NOT NULL,
            `total_amount` DECIMAL(12, 2) NOT NULL,
            `status` ENUM(
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
            ) NOT NULL DEFAULT 'pending',
            `courier_name` VARCHAR(100) NULL,
            `tracking_number` VARCHAR(100) NULL,
            `tracking_url` VARCHAR(255) NULL,
            `cancellation_reason` VARCHAR(500) NULL,
            `cancelled_by_role` VARCHAR(20) NULL DEFAULT NULL,
            `cancelled_at` DATETIME NULL,
            `cashfree_order_id` VARCHAR(100) NULL,
            `razorpay_order_id` VARCHAR(100) NULL,
            `razorpay_payment_id` VARCHAR(100) NULL,
            `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
            `shipping_address` TEXT NOT NULL,
            `shipping_fee` DECIMAL(10, 2) DEFAULT 0.00,
            `weight_fee` DECIMAL(10, 2) DEFAULT 0.00,
            `platform_fee` DECIMAL(10, 2) DEFAULT 0.00,
            `packaging_fee` DECIMAL(10, 2) DEFAULT 0.00,
            `fee_breakdown` LONGTEXT NULL,
            `grand_total` DECIMAL(12, 2) DEFAULT 0.00,
            `cod_advance_paid` DECIMAL(10, 2) DEFAULT 0.00,
            `balance_amount` DECIMAL(12, 2) DEFAULT 0.00,
            `has_modification` TINYINT(1) NOT NULL DEFAULT 0,
            `notes` TEXT NULL,
            `packing_video_1` VARCHAR(255) NULL,
            `packing_video_2` VARCHAR(255) NULL,
            `packed_at` DATETIME NULL,
            `delivered_at` DATETIME NULL,
            `packing_video_expires_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 31. ORDER ITEMS
        "order_items" => "
        CREATE TABLE IF NOT EXISTS `order_items` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `order_id` BIGINT NOT NULL,
            `product_id` BIGINT NOT NULL,
            `variant_id` BIGINT NULL,
            `customization_id` BIGINT NULL,
            `customization_data` LONGTEXT NULL,
            `quantity` INT NOT NULL,
            `unit_price` DECIMAL(10, 2) NOT NULL,
            `total_price` DECIMAL(12, 2) NOT NULL,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 32. ORDER MODIFICATIONS
        "order_modifications" => "
        CREATE TABLE IF NOT EXISTS `order_modifications` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `order_id` BIGINT NOT NULL,
            `modified_by` BIGINT NOT NULL,
            `reason` TEXT NOT NULL,
            `payment_screenshot` VARCHAR(255) NULL,
            `extra_payment_status` VARCHAR(50) NULL,
            `extra_payment_note` TEXT NULL,
            `status` ENUM('pending_buyer', 'accepted', 'rejected', 'cancelled', 'applied') NOT NULL DEFAULT 'pending_buyer',
            `original_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `revised_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `buyer_responded_at` TIMESTAMP NULL,
            `expires_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`modified_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 33. ORDER MODIFICATION ITEMS
        "order_modification_items" => "
        CREATE TABLE IF NOT EXISTS `order_modification_items` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `modification_id` BIGINT NOT NULL,
            `order_item_id` BIGINT NOT NULL,
            `change_type` ENUM('removed', 'qty_changed', 'price_changed', 'replaced', 'unchanged') NOT NULL DEFAULT 'unchanged',
            `old_qty` INT NOT NULL DEFAULT 0,
            `new_qty` INT NOT NULL DEFAULT 0,
            `old_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `new_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `replacement_variant_id` BIGINT NULL,
            `note` VARCHAR(255) NULL,
            FOREIGN KEY (`modification_id`) REFERENCES `order_modifications`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 34. ORDER RULES
        "order_rules" => "
        CREATE TABLE IF NOT EXISTS `order_rules` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `category_id` BIGINT NOT NULL,
            `second_category_rules` LONGTEXT NULL,
            `min_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 35. FEE CATEGORIES
        "fee_categories" => "
        CREATE TABLE IF NOT EXISTS `fee_categories` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) UNIQUE NOT NULL,
            `is_active` BOOLEAN DEFAULT TRUE,
            `is_weight_based` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 36. FEE RULES
        "fee_rules" => "
        CREATE TABLE IF NOT EXISTS `fee_rules` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `fee_name` VARCHAR(255) NOT NULL,
            `fee_category_id` BIGINT NOT NULL,
            `fee_type` ENUM('Fixed Amount', 'Percentage') NOT NULL,
            `flat_fee_value` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `application_states` TEXT NULL,
            `payment_method` ENUM('COD', 'Online', 'Both') NOT NULL DEFAULT 'Both',
            `minimum_order_amount` DECIMAL(10, 2) DEFAULT NULL,
            `maximum_order_amount` DECIMAL(10, 2) DEFAULT NULL,
            `active` BOOLEAN DEFAULT TRUE,
            `weight_slabs` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 37. PACKING FEE RULES
        "packing_fee_rules" => "
        CREATE TABLE IF NOT EXISTS `packing_fee_rules` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `min_amount` INT NOT NULL,
            `max_amount` INT NOT NULL,
            `packing_fee` DECIMAL(10, 2) NOT NULL,
            `is_active` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 38. CANCELLATION RULES
        "cancellation_rules" => "
        CREATE TABLE IF NOT EXISTS `cancellation_rules` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `rule_name` VARCHAR(255) NOT NULL,
            `payment_method` ENUM('online', 'cod', 'both') NOT NULL DEFAULT 'both',
            `cancellation_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `refund_percentage` INT NOT NULL DEFAULT 100,
            `sla_days` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` BIGINT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 39. REFUND REQUESTS
        "refund_requests" => "
        CREATE TABLE IF NOT EXISTS `refund_requests` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `order_id` BIGINT NOT NULL,
            `user_id` BIGINT NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `reason` TEXT NOT NULL,
            `cancellation_type` ENUM('full', 'partial') NOT NULL DEFAULT 'full',
            `status` ENUM(
                'pending_admin_approval',
                'processing',
                'completed',
                'rejected'
            ) NOT NULL DEFAULT 'pending_admin_approval',
            `refund_method` VARCHAR(50) NULL,
            `refund_details` TEXT NULL,
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
            INDEX `idx_rr_status` (`status`),
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 40. FABRIC CUSTOMIZATIONS
        "fabric_customizations" => "
        CREATE TABLE IF NOT EXISTS `fabric_customizations` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `fabric_id` BIGINT NOT NULL,
            `program_name` VARCHAR(255) NOT NULL,
            `garment_type` VARCHAR(100) NOT NULL,
            `category_id` BIGINT NULL,
            `sub_category_id` BIGINT NULL,
            `feeding_type` VARCHAR(100) DEFAULT 'Non-Feeding',
            `minimum_pieces` INT NOT NULL DEFAULT 10,
            `required_pieces` INT NOT NULL DEFAULT 10,
            `maximum_pieces` INT NOT NULL DEFAULT 100,
            `wastage_percentage` DECIMAL(5, 2) DEFAULT 0.00,
            `without_cover_discount_percentage` DECIMAL(5, 2) DEFAULT 0.00,
            `delivery_days` INT DEFAULT 7,
            `delivery_base_pieces` INT DEFAULT 100,
            `unit` VARCHAR(50) DEFAULT 'Meter',
            `customization_enabled` TINYINT(1) DEFAULT 1,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`fabric_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 41. FABRIC CUSTOMIZATION SIZES
        "fabric_customization_sizes" => "
        CREATE TABLE IF NOT EXISTS `fabric_customization_sizes` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `customization_id` BIGINT NOT NULL,
            `size_id` BIGINT NULL,
            `size_name` VARCHAR(50) NOT NULL,
            `fabric_consumption` DECIMAL(8, 4) NOT NULL DEFAULT 1.0000,
            `base_quantity` INT NOT NULL DEFAULT 2,
            `max_quantity` INT NOT NULL DEFAULT 4,
            `unit` VARCHAR(50) DEFAULT 'Meter',
            `enabled` TINYINT(1) DEFAULT 1,
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`customization_id`) REFERENCES `fabric_customizations`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 42. CUSTOMIZATION LABELS
        "customization_labels" => "
        CREATE TABLE IF NOT EXISTS `customization_labels` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `group_name` VARCHAR(255) NULL,
            `sub_group` VARCHAR(255) NULL,
            `name` VARCHAR(255) NOT NULL,
            `display_label` VARCHAR(500) NOT NULL,
            `description` TEXT NULL,
            `sort_order` INT DEFAULT 0,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_cl_status` (`status`),
            INDEX `idx_cl_group` (`group_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 43. FABRIC CUSTOMIZATION LABEL REL
        "fabric_customization_label_rel" => "
        CREATE TABLE IF NOT EXISTS `fabric_customization_label_rel` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `customization_id` BIGINT NOT NULL,
            `label_id` BIGINT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_customization_label` (`customization_id`, `label_id`),
            INDEX `idx_rel_customization` (`customization_id`),
            INDEX `idx_rel_label` (`label_id`),
            FOREIGN KEY (`customization_id`) REFERENCES `fabric_customizations`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`label_id`) REFERENCES `customization_labels`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 44. REVIEWS
        "reviews" => "
        CREATE TABLE IF NOT EXISTS `reviews` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `product_id` BIGINT NOT NULL,
            `customization_id` BIGINT NULL,
            `user_id` BIGINT NOT NULL,
            `order_id` BIGINT NOT NULL,
            `rating` INT NOT NULL DEFAULT 5,
            `review_text` TEXT NULL,
            `photos` LONGTEXT NULL,
            `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_review_product` (`product_id`),
            INDEX `idx_review_user` (`user_id`),
            INDEX `idx_review_order` (`order_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 45. VENDOR PAYOUT REQUESTS
        "vendor_payout_requests" => "
        CREATE TABLE IF NOT EXISTS `vendor_payout_requests` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `payout_number` VARCHAR(50) UNIQUE NOT NULL,
            `vendor_id` BIGINT NOT NULL,
            `amount` DECIMAL(12, 2) NOT NULL,
            `fee_deducted` DECIMAL(10, 2) DEFAULT 0.00,
            `net_amount` DECIMAL(12, 2) NOT NULL,
            `payout_method` VARCHAR(50) DEFAULT 'bank_transfer',
            `bank_name` VARCHAR(150) NULL,
            `account_number` VARCHAR(50) NULL,
            `ifsc_code` VARCHAR(25) NULL,
            `account_holder_name` VARCHAR(150) NULL,
            `upi_id` VARCHAR(100) NULL,
            `vendor_notes` TEXT NULL,
            `status` ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
            `transaction_reference` VARCHAR(100) NULL,
            `admin_notes` TEXT NULL,
            `processed_by` BIGINT NULL,
            `processed_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vpr_vendor` (`vendor_id`),
            INDEX `idx_vpr_status` (`status`),
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 46. VENDOR TRANSACTIONS
        "vendor_transactions" => "
        CREATE TABLE IF NOT EXISTS `vendor_transactions` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `transaction_number` VARCHAR(60) UNIQUE NOT NULL,
            `vendor_id` BIGINT NOT NULL,
            `payout_request_id` BIGINT NULL,
            `order_id` BIGINT NULL,
            `type` ENUM('order_earning', 'payout_withdrawal', 'payout_refund', 'adjustment') NOT NULL,
            `gross_amount` DECIMAL(12, 2) NOT NULL,
            `net_amount` DECIMAL(12, 2) NOT NULL,
            `balance_after` DECIMAL(12, 2) NOT NULL,
            `status` ENUM('pending', 'cleared', 'cancelled') NOT NULL DEFAULT 'pending',
            `description` VARCHAR(500) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vt_vendor` (`vendor_id`),
            INDEX `idx_vt_payout` (`payout_request_id`),
            INDEX `idx_vt_status` (`status`),
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 47. SUPPORT TICKETS
        "support_tickets" => "
        CREATE TABLE IF NOT EXISTS `support_tickets` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `ticket_number` VARCHAR(50) UNIQUE NOT NULL,
            `vendor_id` BIGINT NOT NULL,
            `subject` VARCHAR(255) NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
            `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
            `message` TEXT NOT NULL,
            `attachment` VARCHAR(255) NULL,
            `admin_notes` TEXT NULL,
            `last_reply_by` ENUM('vendor', 'admin') NOT NULL DEFAULT 'vendor',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_st_vendor` (`vendor_id`),
            INDEX `idx_st_status` (`status`),
            INDEX `idx_st_priority` (`priority`),
            FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 48. SUPPORT TICKET MESSAGES
        "support_ticket_messages" => "
        CREATE TABLE IF NOT EXISTS `support_ticket_messages` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` BIGINT NOT NULL,
            `sender_id` BIGINT NOT NULL,
            `sender_role` ENUM('vendor', 'admin', 'super_admin', 'staff') NOT NULL,
            `message` TEXT NOT NULL,
            `attachment` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_stm_ticket` (`ticket_id`),
            INDEX `idx_stm_sender` (`sender_id`),
            FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 49. SETTINGS
        "settings" => "
        CREATE TABLE IF NOT EXISTS `settings` (
            `setting_key` VARCHAR(100) PRIMARY KEY,
            `setting_value` LONGTEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 50. SYSTEM SETTINGS
        "system_settings" => "
        CREATE TABLE IF NOT EXISTS `system_settings` (
            `key_name` VARCHAR(100) PRIMARY KEY,
            `key_value` LONGTEXT NULL,
            `description` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 51. CMS COMPONENTS (Fallback if not created yet)
        "cms_components" => "
        CREATE TABLE IF NOT EXISTS `cms_components` (
            `id` VARCHAR(100) PRIMARY KEY,
            `section_type` VARCHAR(100) NOT NULL,
            `content_data` LONGTEXT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 52. CMS LAYOUTS (Fallback if not created yet)
        "cms_layouts" => "
        CREATE TABLE IF NOT EXISTS `cms_layouts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `page_name` VARCHAR(100) NOT NULL,
            `layout_data` LONGTEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 53. CMS PROJECTS (Fallback if not created yet)
        "cms_projects" => "
        CREATE TABLE IF NOT EXISTS `cms_projects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `subtitle` VARCHAR(255) NULL,
            `description` TEXT NULL,
            `images` LONGTEXT NULL,
            `videos` LONGTEXT NULL,
            `sort_order` INT DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 54. COURIER SERVICES
        "courier_services" => "
        CREATE TABLE IF NOT EXISTS `courier_services` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `default_url` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 55. RATE LIMITS
        "rate_limits" => "
        CREATE TABLE IF NOT EXISTS `rate_limits` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `key_name` VARCHAR(191) NOT NULL UNIQUE,
            `hits` INT NOT NULL DEFAULT 1,
            `expires_at` BIGINT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_rate_limits_key` (`key_name`),
            INDEX `idx_rate_limits_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];

    $createdCount = 0;
    foreach ($tables as $name => $sql) {
        $db->exec($sql);
        echo "  [OK] Table `{$name}` verified/created.\n";
        $createdCount++;
    }

    // Upgrade existing tables with any missing columns (Hostinger compatibility patch)
    echo "\n  Upgrading existing table columns...\n";
    $ordersCols = array_map('strtolower', $db->query("SHOW COLUMNS FROM `orders`")->fetchAll(PDO::FETCH_COLUMN));
    if (!in_array('razorpay_order_id', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `razorpay_order_id` VARCHAR(100) NULL AFTER `cashfree_order_id`");
    }
    if (!in_array('razorpay_payment_id', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `razorpay_payment_id` VARCHAR(100) NULL AFTER `razorpay_order_id`");
    }
    if (!in_array('cancelled_by_role', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `cancelled_by_role` VARCHAR(20) NULL DEFAULT NULL AFTER `cancellation_reason`");
    }
    if (!in_array('courier_name', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `courier_name` VARCHAR(100) NULL AFTER `status`");
    }
    if (!in_array('tracking_number', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `tracking_number` VARCHAR(100) NULL AFTER `courier_name`");
    }
    if (!in_array('tracking_url', $ordersCols)) {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `tracking_url` VARCHAR(255) NULL AFTER `tracking_number`");
    }

    $refundCols = array_map('strtolower', $db->query("SHOW COLUMNS FROM `refund_requests`")->fetchAll(PDO::FETCH_COLUMN));
    if (!in_array('razorpay_refund_id', $refundCols)) {
        $db->exec("ALTER TABLE `refund_requests` ADD COLUMN `razorpay_refund_id` VARCHAR(100) NULL AFTER `cashfree_refund_id`");
    }
    if (!in_array('refund_method', $refundCols)) {
        $db->exec("ALTER TABLE `refund_requests` ADD COLUMN `refund_method` VARCHAR(50) NULL AFTER `cancellation_type`");
    }
    if (!in_array('refund_details', $refundCols)) {
        $db->exec("ALTER TABLE `refund_requests` ADD COLUMN `refund_details` TEXT NULL AFTER `refund_method`");
    }

    // Seed default couriers if empty
    $courierCount = (int)$db->query("SELECT COUNT(*) FROM `courier_services`")->fetchColumn();
    if ($courierCount === 0) {
        $db->exec("
            INSERT IGNORE INTO `courier_services` (`name`, `default_url`, `is_active`) VALUES
            ('ST Courier', 'https://stcourier.com/track', 1),
            ('Professional Couriers', 'https://www.tpcindia.com', 1),
            ('DTDC', 'https://www.dtdc.in', 1),
            ('Delhivery', 'https://www.delhivery.com', 1),
            ('Blue Dart', 'https://www.bluedart.com', 1),
            ('India Post', 'https://www.indiapost.gov.in', 1);
        ");
    }

    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\nAll {$createdCount} tables initialized successfully!\n\n";

    // ========================================================
    // SEED ESSENTIAL INITIAL DATA (Idempotent)
    // ========================================================
    echo "========================================================\n";
    echo " Seeding Initial Data & System Settings...\n";
    echo "========================================================\n";

    // 1. Super Admin User
    $adminCheck = $db->query("SELECT id FROM users WHERE email = 'admin@grmb2b.com' OR role = 'super_admin' LIMIT 1")->fetch();
    if (!$adminCheck) {
        $adminPassHash = password_hash('SuperAdmin@2026', PASSWORD_BCRYPT);
        $db->prepare("
            INSERT INTO users (name, email, phone, password_hash, role, status)
            VALUES ('Super Admin', 'admin@grmb2b.com', '9999999999', ?, 'super_admin', 'active')
        ")->execute([$adminPassHash]);
        echo "  [SEED] Super Admin created (admin@grmb2b.com / SuperAdmin@2026).\n";
    } else {
        echo "  [INFO] Super Admin account already exists.\n";
    }

    // 2. Default Vendor Types
    $defaultTypes = [
        ['Wholesaler', 'wholesaler', 'Bulk wholesale suppliers and B2B stockists', 1, 1],
        ['Manufacturer', 'manufacturer', 'Direct product manufacturers and factories', 0, 2],
        ['Distributor', 'distributor', 'Authorized regional distributors and supply agents', 0, 3],
        ['Brand Owner', 'brand', 'Direct trademark / brand proprietors and creators', 0, 4]
    ];
    $checkVt = $db->prepare("SELECT id FROM vendor_types WHERE slug = ?");
    $insertVt = $db->prepare("INSERT INTO vendor_types (name, slug, description, is_active, display_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultTypes as $vt) {
        $checkVt->execute([$vt[1]]);
        if (!$checkVt->fetch()) {
            $insertVt->execute($vt);
            echo "  [SEED] Vendor Type added: {$vt[0]}.\n";
        }
    }

    // 3. Default Fee Categories
    $feeCategories = ['Shipping Fee', 'Weight Fee', 'Platform Fee', 'Packing Fee', 'COD Advance'];
    $catCheck = $db->prepare("SELECT id FROM fee_categories WHERE name = ?");
    $catInsert = $db->prepare("INSERT INTO fee_categories (name, is_active) VALUES (?, 1)");
    foreach ($feeCategories as $fc) {
        $catCheck->execute([$fc]);
        if (!$catCheck->fetch()) {
            $catInsert->execute([$fc]);
            echo "  [SEED] Fee Category added: {$fc}.\n";
        }
    }

    // 4. Default Settings
    $defaultSettings = [
        'cancellations_enabled' => '1',
        'cancellation_reason_options' => json_encode([
            'Found better price elsewhere',
            'Order placed by mistake',
            'Delivery time is too long',
            'Item specifications not meeting requirements',
            'Cash flow or budget constraints',
            'Other business reasons'
        ]),
        'packing_slip_config' => json_encode([
            'company_name'  => 'GRM Garments',
            'subtitle'      => 'FORWARDING WITH CARE',
            'logo_url'      => '',
            'address'       => "123 Textile Park, Industrial Estate\nTirupur, Tamil Nadu - 641602, India",
            'phone'         => '+91 98765 43210',
            'email'         => 'care@grmgarments.com',
            'gstin'         => '33BFGPG7507C2ZW',
            'notes'         => 'Declaration: We declare that this packing slip shows the actual items described and that all particulars are true and correct.',
            'show_logo'     => 1,
            'show_gstin'    => 1,
            'show_notes'    => 1,
            'show_prices'   => 0
        ])
    ];
    $setStmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaultSettings as $sk => $sv) {
        $setStmt->execute([$sk, $sv]);
    }
    echo "  [SEED] Default application settings verified.\n";

    // 5. Default System Settings
    $defaultSysSettings = [
        'default_commission_rate' => ['10.00', 'Default platform commission percentage across marketplace'],
        'min_withdrawal_amount' => ['500.00', 'Minimum wallet balance required for vendor withdrawal'],
        'vendor_revenue_holding_days' => ['7', 'Number of holding days before delivered order revenues become withdrawable'],
        'cancellations_enabled' => ['1', 'Buyer self-service cancellation toggle']
    ];
    $sysStmt = $db->prepare("
        INSERT INTO system_settings (key_name, key_value, description)
        VALUES (:key, :val, :desc)
        ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)
    ");
    foreach ($defaultSysSettings as $sk => $sdef) {
        $sysStmt->execute(['key' => $sk, 'val' => $sdef[0], 'desc' => $sdef[1]]);
    }
    echo "  [SEED] Default finance system settings verified.\n";

    // 6. Default Customization Labels
    $lblCount = (int)$db->query("SELECT COUNT(*) FROM customization_labels")->fetchColumn();
    if ($lblCount === 0) {
        $seeds = [
            ['Maternity', 'Feeding', 'Full Sleeve', 'Maternity -> Feeding -> Full Sleeve', 'Full sleeve maternity feeding style with concealed zips', 1],
            ['Maternity', 'Feeding', '3/4th Sleeve', 'Maternity -> Feeding -> 3/4th Sleeve', '3/4th sleeve maternity feeding style with invisible zips', 2],
            ['Maternity', 'Feeding', 'Half Sleeve', 'Maternity -> Feeding -> Half Sleeve', 'Half sleeve maternity feeding style', 3],
            ['Maternity', 'Feeding', 'Sleeveless', 'Maternity -> Feeding -> Sleeveless', 'Sleeveless maternity feeding pattern', 4],
            ['Maternity', 'Non-Feeding', 'Full Sleeve', 'Maternity -> Non-Feeding -> Full Sleeve', 'Regular pregnancy maternity wear with full sleeve', 5],
            ['Maternity', 'Non-Feeding', 'Half Sleeve', 'Maternity -> Non-Feeding -> Half Sleeve', 'Regular pregnancy maternity wear with half sleeve', 6],
            ['Standard', 'Regular Fit', 'Round Neck', 'Standard -> Regular Fit -> Round Neck', 'Standard crew/round neckline tailoring', 7],
            ['Standard', 'Regular Fit', 'V-Neck', 'Standard -> Regular Fit -> V-Neck', 'Standard V-neckline tailoring', 8],
            ['Standard', 'Regular Fit', 'Collar Neck', 'Standard -> Regular Fit -> Collar Neck', 'Mandarin or regular shirt collar tailoring', 9],
            ['Nightwear', 'Front Open', 'Full Button', 'Nightwear -> Front Open -> Full Button', 'Front opening buttons nightwear styling', 10],
        ];
        $lblStmt = $db->prepare("
            INSERT INTO customization_labels (group_name, sub_group, name, display_label, description, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        foreach ($seeds as $s) {
            $lblStmt->execute($s);
        }
        echo "  [SEED] " . count($seeds) . " default customization labels seeded.\n";
    }

    // 7. Verify All Existing Tables in DB
    echo "\n========================================================\n";
    echo " Final Database Verification\n";
    echo "========================================================\n";
    $finalTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Current Database: " . $db->query("SELECT DATABASE()")->fetchColumn() . "\n";
    echo "Total tables in database: " . count($finalTables) . "\n";
    foreach ($finalTables as $idx => $tbl) {
        $num = $idx + 1;
        $rowCount = (int)$db->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        echo "  " . str_pad((string)$num, 2, ' ', STR_PAD_LEFT) . ". `{$tbl}` ({$rowCount} rows)\n";
    }

    echo "\n>>> Database migration completed successfully! <<<\n";
} catch (\Throwable $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
