<?php
namespace App\Models;

use Core\Model;

class OrderModification extends Model
{
    /**
     * Create a new modification record and its items.
     */
    public function createModification(int $orderId, int $adminId, string $reason, float $originalTotal, float $revisedTotal, array $itemChanges, ?string $screenshotPath = null, string $extraPaymentStatus = 'pending', ?string $extraPaymentNote = null): int
    {
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

            // Insert per-item changes
            $itemStmt = $this->db->prepare("
                INSERT INTO order_modification_items
                    (modification_id, order_item_id, product_id, variant_id, change_type, old_qty, new_qty, old_price, new_price, replacement_variant_id, note)
                VALUES
                    (:mid, :item_id, :pid, :vid, :ctype, :oqty, :nqty, :oprice, :nprice, :rep_vid, :note)
            ");
            foreach ($itemChanges as $change) {
                $itemStmt->execute([
                    'mid'     => $modId,
                    'item_id' => $change['order_item_id'] ?? null,
                    'pid'     => $change['product_id'] ?? null,
                    'vid'     => $change['variant_id'] ?? null,
                    'ctype'   => $change['change_type'],

                    'oqty'    => $change['old_qty'],
                    'nqty'    => $change['new_qty'],
                    'oprice'  => $change['old_price'],
                    'nprice'  => $change['new_price'],
                    'rep_vid' => $change['replacement_variant_id'] ?? null,
                    'note'    => $change['note'] ?? null,
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
                } elseif (in_array($mItem['change_type'], ['qty_changed', 'price_changed', 'replaced'])) {
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
                   COALESCE(p.name, p2.name) as product_name,
                   COALESCE(pv.name, pv2.name) as variant_name,
                   COALESCE(
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p2.id AND is_primary = 1 LIMIT 1)
                   ) as product_image,
                   rpv.name as replacement_variant_name
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
                            INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal)
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
                    } elseif (in_array($mItem['change_type'], ['qty_changed', 'price_changed', 'replaced'])) {
                        $getOrigItem->execute([$mItem['order_item_id']]);
                        $orig = $getOrigItem->fetch();
                        if ($orig) {
                            $pid = $orig['product_id'];
                            $vid = $orig['variant_id'];
                            $qtyDiff = $mItem['old_qty'] - $mItem['new_qty']; // >0 restore, <0 deduct
                        }
                        $this->db->prepare("
                            UPDATE order_items 
                            SET quantity = ?, unit_price = ?, subtotal = ?
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
