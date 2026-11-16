<?php
/**
 * Assembles the complete Master SQL schema from migrate_all_tables.php
 */
$source = file_get_contents(__DIR__ . '/../config/migrate_all_tables.php');

$output = "-- ==============================================================================\n";
$output .= "-- GRM B2B MARKETPLACE — COMPLETE MASTER DATABASE SCHEMA\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Compatibility: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+, TiDB Cloud\n";
$output .= "-- Features: 55+ Application Tables, Relational Integrity & Default Seeds\n";
$output .= "-- Safe to run directly in phpMyAdmin, MySQL CLI, or Cloud Database Terminals\n";
$output .= "-- ==============================================================================\n\n";

$output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$output .= "SET time_zone = '+05:30';\n";
$output .= "SET NAMES utf8mb4;\n\n";

// Extract table DDLs
preg_match_all('/"([a-z0-9_]+)"\s*=>\s*"\s*(CREATE TABLE IF NOT EXISTS[\s\S]*?);"/i', $source, $matches, PREG_SET_ORDER);

$tableCount = 0;
foreach ($matches as $m) {
    $tableName = $m[1];
    $ddl = trim($m[2]);
    $tableCount++;
    $output .= "-- -----------------------------------------------------------------------------\n";
    $output .= "-- Table: {$tableName}\n";
    $output .= "-- -----------------------------------------------------------------------------\n";
    $output .= $ddl . ";\n\n";
}

$output .= "-- ==============================================================================\n";
$output .= "-- DEFAULT SEEDS & SYSTEM INITIALIZATION\n";
$output .= "-- ==============================================================================\n\n";

// 1. Super Admin Seed
$adminHash = '$2y$10$JkWUWXJptUdDB97iS8t/ReT2x82TjH2upeREQkSh5PbZKGgjNT5Uq'; // SuperAdmin@2026
$output .= "-- 1. Super Admin Root User (admin@grmb2b.com / SuperAdmin@2026)\n";
$output .= "INSERT INTO `users` (`name`, `email`, `phone`, `password_hash`, `role`, `status`)\n";
$output .= "VALUES ('Super Admin', 'admin@grmb2b.com', '9999999999', '{$adminHash}', 'super_admin', 'active')\n";
$output .= "ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role` = 'super_admin', `status` = 'active';\n\n";

// 2. Default Courier Services
$output .= "-- 2. Default Courier Services\n";
$output .= "INSERT IGNORE INTO `courier_services` (`name`, `default_url`, `is_active`) VALUES\n";
$output .= "('ST Courier', 'https://stcourier.com/track', 1),\n";
$output .= "('Professional Couriers', 'https://www.tpcindia.com', 1),\n";
$output .= "('DTDC', 'https://www.dtdc.in', 1),\n";
$output .= "('Delhivery', 'https://www.delhivery.com', 1),\n";
$output .= "('Blue Dart', 'https://www.bluedart.com', 1),\n";
$output .= "('India Post', 'https://www.indiapost.gov.in', 1);\n\n";

// 3. Default Vendor Types
$output .= "-- 3. Default Vendor Types\n";
$output .= "INSERT IGNORE INTO `vendor_types` (`name`, `slug`, `description`, `is_active`, `display_order`) VALUES\n";
$output .= "('Wholesaler', 'wholesaler', 'Bulk wholesale suppliers and B2B stockists', 1, 1),\n";
$output .= "('Manufacturer', 'manufacturer', 'Direct product manufacturers and factories', 0, 2),\n";
$output .= "('Distributor', 'distributor', 'Authorized regional distributors and supply agents', 0, 3),\n";
$output .= "('Brand Owner', 'brand', 'Direct trademark / brand proprietors and creators', 0, 4);\n\n";

// 4. Default Fee Categories
$output .= "-- 4. Default Fee Categories\n";
$output .= "INSERT IGNORE INTO `fee_categories` (`name`, `is_active`) VALUES\n";
$output .= "('Shipping Fee', 1),\n";
$output .= "('Weight Fee', 1),\n";
$output .= "('Platform Fee', 1),\n";
$output .= "('Packing Fee', 1),\n";
$output .= "('COD Advance', 1);\n\n";

// 5. Default Cancellation Rules
$output .= "-- 5. Default Cancellation Rules\n";
$output .= "INSERT IGNORE INTO `cancellation_rules` (`rule_name`, `payment_method`, `cancellation_fee`, `refund_percentage`, `sla_days`, `is_active`) VALUES\n";
$output .= "('Default Full Refund Rule', 'both', 0.00, 100, 7, 1);\n\n";

// 6. Default Settings
$reasons = json_encode([
    'Found better price elsewhere',
    'Order placed by mistake',
    'Delivery time is too long',
    'Item specifications not meeting requirements',
    'Cash flow or budget constraints',
    'Other business reasons'
]);
$packingSlip = json_encode([
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
]);
$output .= "-- 6. Default System Settings\n";
$output .= "INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES\n";
$output .= "('cancellations_enabled', '1'),\n";
$output .= "('cancellation_reason_options', " . json_encode($reasons) . "),\n";
$output .= "('packing_slip_config', " . json_encode($packingSlip) . ");\n\n";

// 7. System Settings
$output .= "-- 7. Platform Finance Settings\n";
$output .= "INSERT INTO `system_settings` (`key_name`, `key_value`, `description`) VALUES\n";
$output .= "('default_commission_rate', '10.00', 'Default platform commission percentage across marketplace'),\n";
$output .= "('min_withdrawal_amount', '500.00', 'Minimum wallet balance required for vendor withdrawal'),\n";
$output .= "('vendor_revenue_holding_days', '7', 'Number of holding days before delivered order revenues become withdrawable'),\n";
$output .= "('cancellations_enabled', '1', 'Buyer self-service cancellation toggle')\n";
$output .= "ON DUPLICATE KEY UPDATE `key_value` = VALUES(`key_value`);\n\n";

// 8. Customization Labels
$output .= "-- 8. Default Customization Labels\n";
$output .= "INSERT IGNORE INTO `customization_labels` (`group_name`, `sub_group`, `name`, `display_label`, `description`, `sort_order`, `status`) VALUES\n";
$output .= "('Maternity', 'Feeding', 'Full Sleeve', 'Maternity -> Feeding -> Full Sleeve', 'Full sleeve maternity feeding style with concealed zips', 1, 'active'),\n";
$output .= "('Maternity', 'Feeding', '3/4th Sleeve', 'Maternity -> Feeding -> 3/4th Sleeve', '3/4th sleeve maternity feeding style with invisible zips', 2, 'active'),\n";
$output .= "('Maternity', 'Feeding', 'Half Sleeve', 'Maternity -> Feeding -> Half Sleeve', 'Half sleeve maternity feeding style', 3, 'active'),\n";
$output .= "('Maternity', 'Feeding', 'Sleeveless', 'Maternity -> Feeding -> Sleeveless', 'Sleeveless maternity feeding pattern', 4, 'active'),\n";
$output .= "('Maternity', 'Non-Feeding', 'Full Sleeve', 'Maternity -> Non-Feeding -> Full Sleeve', 'Regular pregnancy maternity wear with full sleeve', 5, 'active'),\n";
$output .= "('Maternity', 'Non-Feeding', 'Half Sleeve', 'Maternity -> Non-Feeding -> Half Sleeve', 'Regular pregnancy maternity wear with half sleeve', 6, 'active'),\n";
$output .= "('Standard', 'Regular Fit', 'Round Neck', 'Standard -> Regular Fit -> Round Neck', 'Standard crew/round neckline tailoring', 7, 'active'),\n";
$output .= "('Standard', 'Regular Fit', 'V-Neck', 'Standard -> Regular Fit -> V-Neck', 'Standard V-neckline tailoring', 8, 'active'),\n";
$output .= "('Standard', 'Regular Fit', 'Collar Neck', 'Standard -> Regular Fit -> Collar Neck', 'Mandarin or regular shirt collar tailoring', 9, 'active'),\n";
$output .= "('Nightwear', 'Front Open', 'Full Button', 'Nightwear -> Front Open -> Full Button', 'Front opening buttons nightwear styling', 10, 'active');\n\n";

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$output .= "-- ==============================================================================\n";
$output .= "-- END OF SCHEMA ({$tableCount} Tables Generated)\n";
$output .= "-- ==============================================================================\n";

// Write to files
file_put_contents(__DIR__ . '/../config/setup.sql', $output);
file_put_contents(__DIR__ . '/../config/grm_b2b_complete_schema.sql', $output);
file_put_contents(__DIR__ . '/../public/grm_b2b_complete_schema.sql', $output);

echo "Successfully generated schema with {$tableCount} tables!\n";
echo "1. config/setup.sql\n";
echo "2. config/grm_b2b_complete_schema.sql\n";
echo "3. public/grm_b2b_complete_schema.sql (Downloadable at /grm_b2b_complete_schema.sql)\n";
