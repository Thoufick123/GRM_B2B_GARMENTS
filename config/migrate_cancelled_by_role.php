<?php
/**
 * Migration: Add cancelled_by_role column to orders table
 */
require_once __DIR__ . '/constants.php';
require_once dirname(__DIR__) . '/core/Database.php';

try {
    $db = Core\Database::getInstance();

    echo "Running migration: Add cancelled_by_role field to orders table...\n";

    $existingColumns = $db->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('cancelled_by_role', $existingColumns)) {
        $db->exec("ALTER TABLE orders ADD COLUMN cancelled_by_role VARCHAR(20) NULL DEFAULT NULL AFTER cancellation_reason");
        echo "Added column: cancelled_by_role\n";
    } else {
        echo "Column already exists: cancelled_by_role\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
