<?php
namespace App\Models;

use Core\Database;

/**
 * CancellationRule model
 *
 * Maps specific order statuses and payment methods to cancellation fees, 
 * refund percentages, and SLAs.
 */
class CancellationRule
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    /**
     * Ensure database schema supports cancellation_rules and varchar payment_method
     */
    private function ensureSchema(): void
    {
        try {
            $this->db->exec("
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
            ");

            $col = $this->db->query("SHOW COLUMNS FROM cancellation_rules LIKE 'payment_method'")->fetch(\PDO::FETCH_ASSOC);
            if ($col && strpos(strtolower($col['Type']), 'varchar') === false) {
                $this->db->exec("ALTER TABLE cancellation_rules MODIFY COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'both'");
            }
        } catch (\Throwable $e) {}
    }

    // ---------------------------------------------------------------------------
    // READ
    // ---------------------------------------------------------------------------

    /** All rules ordered by payment method and status */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM cancellation_rules ORDER BY id DESC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cancellation_rules WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByStatus(string $status): ?array
    {
        $status = strtolower(trim($status));
        $statuses = [$status];
        if ($status === 'shipped') $statuses[] = 'shipping';
        if ($status === 'shipping') $statuses[] = 'shipped';
        if ($status === 'delivered') $statuses[] = 'delivery';
        if ($status === 'delivery') $statuses[] = 'delivered';
        if ($status === 'out_for_delivery') $statuses[] = 'out of delivery';
        if ($status === 'out of delivery') $statuses[] = 'out_for_delivery';

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $stmt = $this->db->prepare("SELECT * FROM cancellation_rules WHERE LOWER(TRIM(rule_name)) IN ($placeholders) LIMIT 1");
        $stmt->execute($statuses);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ---------------------------------------------------------------------------
    // WRITE
    // ---------------------------------------------------------------------------

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO cancellation_rules
                (rule_name, payment_method, cancellation_fee, refund_percentage, sla_days, is_active, created_by)
            VALUES
                (:rule_name, :payment_method, :cancellation_fee, :refund_percentage, :sla_days, :is_active, :created_by)
        ");
        $stmt->execute([
            'rule_name'         => $data['rule_name'],
            'payment_method'    => $data['payment_method'],
            'cancellation_fee'  => (float)($data['cancellation_fee'] ?? 0),
            'refund_percentage' => (int)($data['refund_percentage'] ?? 100),
            'sla_days'          => (int)($data['sla_days'] ?? 0),
            'is_active'         => (int)($data['is_active'] ?? 1),
            'created_by'        => $data['created_by'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cancellation_rules
            SET rule_name         = :rule_name,
                payment_method    = :payment_method,
                cancellation_fee  = :cancellation_fee,
                refund_percentage = :refund_percentage,
                sla_days          = :sla_days,
                is_active         = :is_active
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'                => $id,
            'rule_name'         => $data['rule_name'],
            'payment_method'    => $data['payment_method'],
            'cancellation_fee'  => (float)($data['cancellation_fee'] ?? 0),
            'refund_percentage' => (int)($data['refund_percentage'] ?? 100),
            'sla_days'          => (int)($data['sla_days'] ?? 0),
            'is_active'         => (int)($data['is_active'] ?? 1),
        ]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cancellation_rules SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cancellation_rules WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------
    // Eligibility engine (used by Storefront RefundController)
    // ---------------------------------------------------------------------------

    /**
     * Look up the specific rule for an order's status and payment method.
     * Returns an array with financial/SLA metrics.
     */
    public function getRuleForOrder(array $order): array
    {
        $pm = strtolower($order['payment_method'] ?? '');
        $pmType = ($pm === 'cod') ? 'cod' : 'prepaid';
        
        $status = strtolower(trim($order['status'] ?? 'pending'));

        // Prevent cancellation if order is already cancelled or refund is already in progress/done
        if (in_array($status, ['cancelled', 'refund_pending', 'refund_processing', 'refund_completed', 'refund_rejected'])) {
            return [
                'eligible'          => false,
                'cancellation_fee'  => 0.00,
                'refund_percentage' => 0,
                'sla_days'          => 0,
                'reason'            => 'Order is already ' . str_replace('_', ' ', $status)
            ];
        }

        // Build status aliases
        $statuses = [$status];
        if ($status === 'shipped') $statuses[] = 'shipping';
        if ($status === 'shipping') $statuses[] = 'shipped';
        if ($status === 'delivered') $statuses[] = 'delivery';
        if ($status === 'delivery') $statuses[] = 'delivered';
        if ($status === 'out_for_delivery') $statuses[] = 'out of delivery';
        if ($status === 'out of delivery') $statuses[] = 'out_for_delivery';

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $stmt = $this->db->prepare("
            SELECT * FROM cancellation_rules 
            WHERE is_active = 1 
              AND (payment_method IN (?, 'all', 'both', 'prepaid', 'online') OR payment_method IS NULL OR payment_method = '')
              AND LOWER(TRIM(rule_name)) IN ($placeholders)
            ORDER BY id DESC
            LIMIT 1
        ");
        
        $stmt->execute(array_merge([$pmType], $statuses));
        $rule = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($rule) {
            return [
                'eligible'          => true,
                'cancellation_fee'  => (float)$rule['cancellation_fee'],
                'refund_percentage' => (int)($rule['refund_percentage'] ?? 100),
                'sla_days'          => (int)($rule['sla_days'] ?? 0),
                'reason'            => '',
                'rule_name'         => $rule['rule_name']
            ];
        }

        // If no active rule is configured by admin for this status, cancellation is not allowed
        return [
            'eligible'          => false,
            'cancellation_fee'  => 0.00,
            'refund_percentage' => 0,
            'sla_days'          => 0,
            'reason'            => 'No active cancellation rule configured for status: ' . $status
        ];
    }
}
