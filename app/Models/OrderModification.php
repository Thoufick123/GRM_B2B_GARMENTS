<?php
namespace App\Models;

use Core\Model;

class OrderModification extends Model
{
    private function ensureModificationItemsSchema(): void
    {
        $columns = $this->db->query("SHOW COLUMNS FROM `order_modification_items`")->fetchAll(\PDO::FETCH_ASSOC);
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[strtolower($column['Field'])] = $column;
        }

        if (!isset($columnMap['order_item_id'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `order_item_id` BIGINT NULL");
        } elseif (strtoupper($columnMap['order_item_id']['Null']) !== 'YES') {
            $this->db->exec("ALTER TABLE `order_modification_items` MODIFY COLUMN `order_item_id` BIGINT NULL");
        }
        if (!isset($columnMap['product_id'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `product_id` BIGINT NULL");
        }
        if (!isset($columnMap['variant_id'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `variant_id` BIGINT NULL");
        }
        if (!isset($columnMap['original_product_name'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `original_product_name` VARCHAR(255) NULL");
        }
        if (!isset($columnMap['original_variant_name'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `original_variant_name` VARCHAR(255) NULL");
        }
        if (!isset($columnMap['original_image'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `original_image` VARCHAR(255) NULL");
        }
        if (!isset($columnMap['replacement_product_name'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `replacement_product_name` VARCHAR(255) NULL");
        }
        if (!isset($columnMap['replacement_variant_name'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `replacement_variant_name` VARCHAR(255) NULL");
        }
        if (!isset($columnMap['replacement_image'])) {
            $this->db->exec("ALTER TABLE `order_modification_items` ADD COLUMN `replacement_image` VARCHAR(255) NULL");
        }
        if (isset($columnMap['change_type']) && strpos($columnMap['change_type']['Type'], "'added'") === false) {
            $this->db->exec("
                ALTER TABLE `order_modification_items`
                MODIFY COLUMN `change_type` ENUM('removed', 'qty_changed', 'price_changed', 'replaced', 'unchanged', 'added')
                NOT NULL DEFAULT 'unchanged'
            ");
        }
    }

    /**
     * Create a new modification record and its items.
     */
    public function createModification(int $orderId, int $adminId, string $reason, float $originalTotal, float $revisedTotal, array $itemChanges, ?string $screenshotPath = null, string $extraPaymentStatus = 'pending', ?string $extraPaymentNote = null): int
    {
        $this->ensureModificationItemsSchema();
        $this->db->beginTransaction();
        try {
            // Insert modification header
            $stmt = $this->db->prepare("
                INSERT INTO order_modifications (order_id, modified_by, reason, payment_screenshot, extra_payment_status, extra_payment_note, status, original_total, revised_total, expires_at)
                VALUES (:order_id, :admin, :reason, :screenshot, :eps, :epn, 'accepted', :orig, :revised, DATE_ADD(NOW(), INTERVAL 48 HOUR))
            ");
            $stmt->execute([
                'order_id'   => $orderId,
                'admin'      => $adminId,
                'reason'     => $reason,
                'screenshot' => $screenshotPath,
                'eps'        => $extraPaymentStatus,
                'epn'        => $extraPaymentNote,
                'orig'       => $originalTotal,
                'revised'    => $revisedTotal,
            ]);
            $modId = (int)$this->db->lastInsertId();

            // Prepare lookup statements for original item and replacement product details
            $getOrigDetails = $this->db->prepare("
                SELECT oi.product_id, oi.variant_id,
                       p.name as orig_pname, pv.name as orig_vname,
                       COALESCE(
                           (SELECT image_path FROM product_images WHERE variant_id = oi.variant_id ORDER BY is_primary DESC, id ASC LIMIT 1),
                           (SELECT image_path FROM product_images WHERE product_id = oi.product_id ORDER BY is_primary DESC, id ASC LIMIT 1)
                       ) as orig_img
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.id = ?
            ");

            $getRepDetails = $this->db->prepare("
                SELECT p.name as rep_pname, pv.name as rep_vname,
                       COALESCE(
                           (SELECT image_path FROM product_images WHERE variant_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1),
                           (SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1)
                       ) as rep_img
                FROM products p
                LEFT JOIN product_variants pv ON pv.id = ?
                WHERE p.id = ?
            ");

            // Insert per-item changes with original and replacement names
            $itemStmt = $this->db->prepare("
                INSERT INTO order_modification_items
                    (modification_id, order_item_id, product_id, variant_id, change_type, old_qty, new_qty, old_price, new_price, replacement_variant_id, note, original_product_name, original_variant_name, original_image, replacement_product_name, replacement_variant_name, replacement_image)
                VALUES
                    (:mid, :item_id, :pid, :vid, :ctype, :oqty, :nqty, :oprice, :nprice, :rep_vid, :note, :orig_pname, :orig_vname, :orig_img, :rep_pname, :rep_vname, :rep_img)
            ");
            foreach ($itemChanges as $change) {
                $origPName = !empty($change['original_product_name']) ? $change['original_product_name'] : null;
                $origVName = !empty($change['original_variant_name']) ? $change['original_variant_name'] : null;
                $origImg   = !empty($change['original_image']) ? $change['original_image'] : null;
                $repPName  = !empty($change['replacement_product_name']) ? $change['replacement_product_name'] : null;
                $repVName  = !empty($change['replacement_variant_name']) ? $change['replacement_variant_name'] : null;
                $repImg    = !empty($change['replacement_image']) ? $change['replacement_image'] : null;

                if ((empty($origPName) || empty($origImg)) && !empty($change['order_item_id'])) {
                    $getOrigDetails->execute([$change['order_item_id']]);
                    $origInfo = $getOrigDetails->fetch(\PDO::FETCH_ASSOC);
                    if ($origInfo) {
                        if (empty($origPName)) $origPName = $origInfo['orig_pname'];
                        if (empty($origVName)) $origVName = $origInfo['orig_vname'];
                        if (empty($origImg))   $origImg   = $origInfo['orig_img'];
                    }
                }

                if ((empty($repPName) || empty($repImg)) && !empty($change['product_id'])) {
                    $vidParam = $change['variant_id'] ?? null;
                    $getRepDetails->execute([$vidParam, $change['product_id'], $vidParam, $change['product_id']]);
                    $repInfo = $getRepDetails->fetch(\PDO::FETCH_ASSOC);
                    if ($repInfo) {
                        if (empty($repPName)) $repPName = $repInfo['rep_pname'];
                        if (empty($repVName)) $repVName = $repInfo['rep_vname'];
                        if (empty($repImg))   $repImg   = $repInfo['rep_img'];
                    }
                }

                $itemStmt->execute([
                    'mid'        => $modId,
                    'item_id'    => $change['order_item_id'] ?? null,
                    'pid'        => $change['product_id'] ?? null,
                    'vid'        => $change['variant_id'] ?? null,
                    'ctype'      => $change['change_type'],

                    'oqty'       => $change['old_qty'],
                    'nqty'       => $change['new_qty'],
                    'oprice'     => $change['old_price'],
                    'nprice'     => $change['new_price'],
                    'rep_vid'    => $change['replacement_variant_id'] ?? null,
                    'note'       => $change['note'] ?? null,
                    'orig_pname' => $origPName,
                    'orig_vname' => $origVName,
                    'orig_img'   => $origImg,
                    'rep_pname'  => $repPName,
                    'rep_vname'  => $repVName,
                    'rep_img'    => $repImg,
                ]);
            }

            // Fetch the modification items to apply them immediately
            $itemsStmt = $this->db->prepare("SELECT * FROM order_modification_items WHERE modification_id = ?");
            $itemsStmt->execute([$modId]);
            $modItems = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

            $updateProductStock = $this->db->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + ?) WHERE id = ?");
            $updateVariantStock = $this->db->prepare("UPDATE product_variants SET current_stock = GREATEST(0, current_stock + ?), stock = GREATEST(0, stock + ?) WHERE id = ?");
            $syncParentStock = $this->db->prepare("UPDATE products SET stock_quantity = (SELECT COALESCE(SUM(current_stock), 0) FROM product_variants WHERE product_id = ?) WHERE id = ?");

            $getOrigItem = $this->db->prepare("SELECT product_id, variant_id FROM order_items WHERE id = ?");

            foreach ($modItems as $mItem) {
                $qtyDiff = 0; // Negative means deduct stock, positive means restore stock
                $pid = null;
                $vid = null;

                if ($mItem['change_type'] === 'removed') {
                    $getOrigItem->execute([$mItem['order_item_id']]);
                    $orig = $getOrigItem->fetch();
                    if ($orig) {
                        $pid = $orig['product_id'];
                        $vid = $orig['variant_id'];
                        $qtyDiff = $mItem['old_qty']; // Restore original qty
                    }
                    $this->db->prepare("DELETE FROM order_items WHERE id = ?")->execute([$mItem['order_item_id']]);
                } elseif ($mItem['change_type'] === 'added') {
                    $this->db->prepare("
                        INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, total_price)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $orderId,
                        $mItem['product_id'],
                        $mItem['variant_id'],
                        $mItem['new_qty'],
                        $mItem['new_price'],
                        $mItem['new_qty'] * $mItem['new_price']
                    ]);
                    $pid = $mItem['product_id'];
                    $vid = $mItem['variant_id'];
                    $qtyDiff = -$mItem['new_qty']; // Deduct new qty
                } elseif ($mItem['change_type'] === 'replaced') {
                    $getOrigItem->execute([$mItem['order_item_id']]);
                    $orig = $getOrigItem->fetch(\PDO::FETCH_ASSOC);
                    if ($orig) {
                        $oldProductId = (int)$orig['product_id'];
                        $oldVariantId = (int)($orig['variant_id'] ?? 0);
                        if ($oldVariantId) {
                            $updateVariantStock->execute([$mItem['old_qty'], $mItem['old_qty'], $oldVariantId]);
                            $syncParentStock->execute([$oldProductId, $oldProductId]);
                        } else {
                            $updateProductStock->execute([$mItem['old_qty'], $oldProductId]);
                        }
                    }

                    $pid = (int)$mItem['product_id'];
                    $vid = (int)($mItem['variant_id'] ?? 0);
                    if ($vid) {
                        $updateVariantStock->execute([-$mItem['new_qty'], -$mItem['new_qty'], $vid]);
                        $syncParentStock->execute([$pid, $pid]);
                    } else {
                        $updateProductStock->execute([-$mItem['new_qty'], $pid]);
                    }

                    $this->db->prepare("
                        UPDATE order_items
                        SET product_id = ?, variant_id = ?, quantity = ?, unit_price = ?, total_price = ?
                        WHERE id = ?
                    ")->execute([
                        $pid,
                        $vid ?: null,
                        $mItem['new_qty'],
                        $mItem['new_price'],
                        $mItem['new_qty'] * $mItem['new_price'],
                        $mItem['order_item_id'],
                    ]);
                } elseif (in_array($mItem['change_type'], ['qty_changed', 'price_changed'])) {
                    $getOrigItem->execute([$mItem['order_item_id']]);
                    $orig = $getOrigItem->fetch();
                    if ($orig) {
                        $pid = $orig['product_id'];
                        $vid = $orig['variant_id'];
                        $qtyDiff = $mItem['old_qty'] - $mItem['new_qty']; // >0 restore, <0 deduct
                    }
                    $this->db->prepare("
                        UPDATE order_items 
                        SET quantity = ?, unit_price = ?, total_price = ?
                        WHERE id = ?
                    ")->execute([
                        $mItem['new_qty'],
                        $mItem['new_price'],
                        $mItem['new_qty'] * $mItem['new_price'],
                        $mItem['order_item_id']
                    ]);
                }

                // Apply inventory adjustments
                if ($qtyDiff !== 0 && $pid) {
                    if ($vid) {
                        $updateVariantStock->execute([$qtyDiff, $qtyDiff, $vid]);
                        $syncParentStock->execute([$pid, $pid]);
                    } else {
                        $updateProductStock->execute([$qtyDiff, $pid]);
                    }
                }
            }

            // Calculate revised subtotal (sum of non-removed items)
            $revisedSubtotal = 0.0;
            foreach ($modItems as $mItem) {
                if ($mItem['change_type'] !== 'removed') {
                    $revisedSubtotal += ((int)$mItem['new_qty']) * ((float)$mItem['new_price']);
                }
            }

            // Mark order as having a modification and update total_amount and revised grand total
            $this->db->prepare("UPDATE orders SET total_amount = ?, grand_total = ?, has_modification = 1 WHERE id = ?")->execute([$revisedSubtotal, $revisedTotal, $orderId]);

            $this->db->commit();
            return $modId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all modifications (admin listing).
     */
    public function getAllModifications(): array
    {
        $stmt = $this->db->query("
            SELECT om.*, 
                   o.order_number, o.grand_total as order_grand_total,
                   u.name as buyer_name, u.email as buyer_email, u.phone as buyer_phone,
                   a.name as admin_name
            FROM order_modifications om
            JOIN orders o ON om.order_id = o.id
            JOIN users u ON o.user_id = u.id
            JOIN users a ON om.modified_by = a.id
            ORDER BY om.created_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get one modification with all its item details.
     */
    public function getModificationWithItems(int $modId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT om.*,
                   o.order_number, o.user_id as buyer_id, o.grand_total as order_grand_total,
                   o.shipping_address,
                   u.name as buyer_name, u.email as buyer_email, u.phone as buyer_phone,
                   a.name as admin_name
            FROM order_modifications om
            JOIN orders o ON om.order_id = o.id
            JOIN users u ON o.user_id = u.id
            JOIN users a ON om.modified_by = a.id
            WHERE om.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $modId]);
        $mod = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$mod) return null;

        // Get item changes
        $iStmt = $this->db->prepare("
            SELECT omi.*,
                   oi.quantity as orig_qty, oi.unit_price as orig_unit_price,
                   COALESCE(omi.original_product_name, p.name, p2.name, 'Customer First Order Product') as original_product_name,
                   COALESCE(omi.original_variant_name, pv.name, pv2.name) as original_variant_name,
                   COALESCE(omi.replacement_product_name, p2.name, p.name, 'Replacement Product') as replacement_product_name,
                   COALESCE(omi.replacement_variant_name, pv2.name, pv.name, rpv.name) as replacement_variant_name,
                   COALESCE(omi.replacement_variant_name, pv2.name, pv.name, rpv.name) as replacement_product_variant_name,
                   COALESCE(
                       omi.original_image,
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p2.id AND is_primary = 1 LIMIT 1)
                   ) as original_image,
                   COALESCE(
                       omi.replacement_image,
                       (SELECT image_path FROM product_images WHERE product_id = p2.id AND is_primary = 1 LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
                   ) as replacement_image,
                   COALESCE(omi.original_product_name, p.name, p2.name, 'Original Item') as product_name,
                   COALESCE(omi.original_variant_name, pv.name, pv2.name) as variant_name,
                   COALESCE(
                       omi.original_image,
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p2.id AND is_primary = 1 LIMIT 1)
                   ) as product_image
            FROM order_modification_items omi
            LEFT JOIN order_items oi ON omi.order_item_id = oi.id
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN products p2 ON omi.product_id = p2.id
            LEFT JOIN product_variants pv2 ON omi.variant_id = pv2.id
            LEFT JOIN product_variants rpv ON omi.replacement_variant_id = rpv.id
            WHERE omi.modification_id = :mid
        ");
        $iStmt->execute(['mid' => $modId]);
        $mod['items'] = $iStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $mod;
    }

    /**
     * Get pending modification for a buyer's order (for buyer review page).
     */
    public function getPendingModificationForBuyer(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT om.id, om.order_id, o.order_number, om.reason, om.original_total, om.revised_total, om.expires_at, om.created_at
            FROM order_modifications om
            JOIN orders o ON om.order_id = o.id
            WHERE o.user_id = :uid AND om.status = 'pending_buyer'
            ORDER BY om.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Count pending modifications awaiting buyer response.
     */
    public function countPendingBuyer(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM order_modifications WHERE status = 'pending_buyer'")->fetchColumn();
    }

    /**
     * Buyer responds: accept or reject.
     */
    public function buyerRespond(int $modId, int $userId, string $decision): bool
    {
        // Verify ownership
        $stmt = $this->db->prepare("
            SELECT om.id, om.order_id, om.revised_total, om.status
            FROM order_modifications om
            JOIN orders o ON om.order_id = o.id
            WHERE om.id = :mid AND o.user_id = :uid AND om.status = 'pending_buyer'
            LIMIT 1
        ");
        $stmt->execute(['mid' => $modId, 'uid' => $userId]);
        $mod = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$mod) return false;

        $this->db->beginTransaction();
        try {
            $newStatus = ($decision === 'accept') ? 'accepted' : 'rejected';
            $this->db->prepare("
                UPDATE order_modifications SET status = :s, buyer_responded_at = NOW() WHERE id = :id
            ")->execute(['s' => $newStatus, 'id' => $modId]);

            if ($decision === 'accept') {
                // Fetch the modification items to apply them
                $itemsStmt = $this->db->prepare("SELECT * FROM order_modification_items WHERE modification_id = ?");
                $itemsStmt->execute([$modId]);
                $modItems = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

                $updateProductStock = $this->db->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + ?) WHERE id = ?");
                $updateVariantStock = $this->db->prepare("UPDATE product_variants SET current_stock = GREATEST(0, current_stock + ?), stock = GREATEST(0, stock + ?) WHERE id = ?");
                $syncParentStock = $this->db->prepare("UPDATE products SET stock_quantity = (SELECT COALESCE(SUM(current_stock), 0) FROM product_variants WHERE product_id = ?) WHERE id = ?");

                $getOrigItem = $this->db->prepare("SELECT product_id, variant_id FROM order_items WHERE id = ?");

                foreach ($modItems as $mItem) {
                    $qtyDiff = 0; // Negative means deduct stock, positive means restore stock
                    $pid = null;
                    $vid = null;

                    if ($mItem['change_type'] === 'removed') {
                        $getOrigItem->execute([$mItem['order_item_id']]);
                        $orig = $getOrigItem->fetch();
                        if ($orig) {
                            $pid = $orig['product_id'];
                            $vid = $orig['variant_id'];
                            $qtyDiff = $mItem['old_qty']; // Restore original qty
                        }
                        $this->db->prepare("DELETE FROM order_items WHERE id = ?")->execute([$mItem['order_item_id']]);
                    } elseif ($mItem['change_type'] === 'added') {
                        $this->db->prepare("
                            INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, total_price)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ")->execute([
                            $mod['order_id'],
                            $mItem['product_id'],
                            $mItem['variant_id'],
                            $mItem['new_qty'],
                            $mItem['new_price'],
                            $mItem['new_qty'] * $mItem['new_price']
                        ]);
                        $pid = $mItem['product_id'];
                        $vid = $mItem['variant_id'];
                        $qtyDiff = -$mItem['new_qty']; // Deduct new qty
                    } elseif ($mItem['change_type'] === 'replaced') {
                        $getOrigItem->execute([$mItem['order_item_id']]);
                        $orig = $getOrigItem->fetch(\PDO::FETCH_ASSOC);
                        if ($orig) {
                            $oldProductId = (int)$orig['product_id'];
                            $oldVariantId = (int)($orig['variant_id'] ?? 0);
                            if ($oldVariantId) {
                                $updateVariantStock->execute([$mItem['old_qty'], $mItem['old_qty'], $oldVariantId]);
                                $syncParentStock->execute([$oldProductId, $oldProductId]);
                            } else {
                                $updateProductStock->execute([$mItem['old_qty'], $oldProductId]);
                            }
                        }

                        $pid = (int)$mItem['product_id'];
                        $vid = (int)($mItem['variant_id'] ?? 0);
                        if ($vid) {
                            $updateVariantStock->execute([-$mItem['new_qty'], -$mItem['new_qty'], $vid]);
                            $syncParentStock->execute([$pid, $pid]);
                        } else {
                            $updateProductStock->execute([-$mItem['new_qty'], $pid]);
                        }

                        $this->db->prepare("
                            UPDATE order_items
                            SET product_id = ?, variant_id = ?, quantity = ?, unit_price = ?, total_price = ?
                            WHERE id = ?
                        ")->execute([
                            $pid,
                            $vid ?: null,
                            $mItem['new_qty'],
                            $mItem['new_price'],
                            $mItem['new_qty'] * $mItem['new_price'],
                            $mItem['order_item_id'],
                        ]);
                    } elseif (in_array($mItem['change_type'], ['qty_changed', 'price_changed'])) {
                        $getOrigItem->execute([$mItem['order_item_id']]);
                        $orig = $getOrigItem->fetch();
                        if ($orig) {
                            $pid = $orig['product_id'];
                            $vid = $orig['variant_id'];
                            $qtyDiff = $mItem['old_qty'] - $mItem['new_qty']; // >0 restore, <0 deduct
                        }
                        $this->db->prepare("
                            UPDATE order_items 
                            SET quantity = ?, unit_price = ?, total_price = ?
                            WHERE id = ?
                        ")->execute([
                            $mItem['new_qty'],
                            $mItem['new_price'],
                            $mItem['new_qty'] * $mItem['new_price'],
                            $mItem['order_item_id']
                        ]);
                    }

                    // Apply inventory adjustments
                    if ($qtyDiff !== 0 && $pid) {
                        if ($vid) {
                            $updateVariantStock->execute([$qtyDiff, $qtyDiff, $vid]);
                            $syncParentStock->execute([$pid, $pid]);
                        } else {
                            $updateProductStock->execute([$qtyDiff, $pid]);
                        }
                    }
                }

                // Update the order's grand total
                $this->db->prepare("
                    UPDATE orders SET grand_total = :rt, has_modification = 0, status = 'placed' WHERE id = :oid
                ")->execute(['rt' => $mod['revised_total'], 'oid' => $mod['order_id']]);
            } else {
                // Buyer rejected → cancel order
                $this->db->prepare("
                    UPDATE orders SET status = 'cancelled', has_modification = 0 WHERE id = :oid
                ")->execute(['oid' => $mod['order_id']]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
