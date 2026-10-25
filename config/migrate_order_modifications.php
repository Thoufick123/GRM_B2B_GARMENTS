<?php
require_once __DIR__ . '/constants.php';
require_once dirname(__DIR__) . '/core/Database.php';

$db = Core\Database::getInstance();

// 1. order_modifications table
$db->exec("
CREATE TABLE IF NOT EXISTS order_modifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    modified_by INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending_buyer','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending_buyer',
    original_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    revised_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    buyer_responded_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE CASCADE
)
");
echo "order_modifications table created\n";

// 2. order_modification_items table
$db->exec("
CREATE TABLE IF NOT EXISTS order_modification_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    modification_id BIGINT NOT NULL,
    order_item_id BIGINT NULL,
    product_id BIGINT NULL,
    variant_id BIGINT NULL,
    change_type ENUM('removed','qty_changed','price_changed','replaced','unchanged','added') NOT NULL DEFAULT 'unchanged',
    old_qty INT NOT NULL DEFAULT 0,
    new_qty INT NOT NULL DEFAULT 0,
    old_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    new_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    replacement_variant_id BIGINT NULL,
    note VARCHAR(255) NULL,
    FOREIGN KEY (modification_id) REFERENCES order_modifications(id) ON DELETE CASCADE
)
");
echo "order_modification_items table created\n";

// 3. Add has_modification flag to orders for quick lookup
$cols = $db->query("SHOW COLUMNS FROM orders LIKE 'has_modification'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE orders ADD COLUMN has_modification TINYINT(1) NOT NULL DEFAULT 0");
    echo "has_modification column added to orders\n";
} else {
    echo "has_modification already exists\n";
}

echo "Migration complete!\n";
