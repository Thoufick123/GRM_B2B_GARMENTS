<?php
namespace App\Models;

use Core\Model;

class Order extends Model
{
    public function createOrder(int $userId, array $cartItems, float $totalAmount, string $address, array $feeDetails = [])
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->query("SELECT MAX(id) FROM orders");
            $lastId = (int)$stmt->fetchColumn();
            $orderNumber = 'PENDING-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, order_number, total_amount, shipping_address, status, payment_status,
                    shipping_fee, weight_fee, platform_fee, packaging_fee, fee_breakdown,
                    grand_total, cod_advance_paid, balance_amount
                )
                VALUES (
                    :uid, :onum, :total, :addr, 'pending', 'pending',
                    :shipping_fee, :weight_fee, :platform_fee, :packaging_fee, :fee_breakdown,
                    :grand_total, :cod_advance_paid, :balance_amount
                )
            ");
            $stmt->execute([
                'uid' => $userId,
                'onum' => $orderNumber,
                'total' => $totalAmount,
                'addr' => $address,
                'shipping_fee' => $feeDetails['shipping_fee'] ?? 0.00,
                'weight_fee' => $feeDetails['weight_fee'] ?? 0.00,
                'platform_fee' => $feeDetails['platform_fee'] ?? 0.00,
                'packaging_fee' => $feeDetails['packaging_fee'] ?? 0.00,
                'fee_breakdown' => isset($feeDetails['fee_breakdown']) ? json_encode($feeDetails['fee_breakdown']) : null,
                'grand_total' => $feeDetails['grand_total'] ?? $totalAmount,
                'cod_advance_paid' => $feeDetails['cod_advance_paid'] ?? 0.00,
                'balance_amount' => $feeDetails['balance_amount'] ?? 0.00
            ]);
            
            $orderId = $this->db->lastInsertId();

            // Safety cleanup: Ensure no stale/orphan records exist for this orderId
            $this->db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
            $this->db->prepare("DELETE FROM order_modifications WHERE order_id = ?")->execute([$orderId]);

            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, variant_id, customization_id, customization_data, quantity, unit_price, total_price)
                VALUES (:oid, :pid, :vid, :cid, :cdata, :q, :up, :tp)
            ");

            foreach ($cartItems as $item) {
                $gst = (float)($item['variant_total_gst'] ?? 0);
                if ($gst <= 0) $gst = (float)($item['product_total_gst'] ?? 0);
                if ($gst <= 0) $gst = (float)($item['category_sgst'] ?? 0) + (float)($item['category_cgst'] ?? 0);

                if (!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) {
                    $rawEffective = (float)$item['variant_discount_price'];
                } else {
                    $rawEffective = (!empty($item['variant_price']) && (float)$item['variant_price'] > 0) 
                        ? (float)$item['variant_price'] 
                        : (float)($item['wholesale_price'] ?? 0);
                }

                $unitPrice = $rawEffective;

                $customizationDataJson = null;
                if (!empty($item['customization_data'])) {
                    $customizationDataJson = is_array($item['customization_data']) ? json_encode($item['customization_data']) : $item['customization_data'];
                }

                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['product_id'],
                    'vid' => $item['variant_id'] ?? null,
                    'cid' => !empty($item['customization_id']) ? $item['customization_id'] : null,
                    'cdata' => $customizationDataJson,
                    'q' => $item['quantity'],
                    'up' => $unitPrice,
                    'tp' => round($unitPrice * $item['quantity'], 2)
                ]);
            }

            $this->db->commit();
            return $orderNumber;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function getAllOrders($statusFilter = 'all')
    {
        if (is_bool($statusFilter)) {
            $statusFilter = $statusFilter ? 'pending' : 'paid';
        }

        $whereClause = "";
        $params = [];

        if ($statusFilter === 'all') {
            $whereClause = "(o.payment_status = 'paid' OR (o.status != 'pending' AND o.order_number NOT LIKE 'PENDING-%'))";
        } elseif ($statusFilter === 'pending') {
            $whereClause = "((o.payment_status != 'paid' AND o.status != 'cancelled') OR o.status = 'pending' OR o.order_number LIKE 'PENDING-%')";
        } elseif ($statusFilter === 'paid') {
            $whereClause = "o.payment_status = 'paid'";
        } elseif ($statusFilter === 'placed') {
            $whereClause = "o.status = 'placed' AND (o.payment_status = 'paid' OR o.order_number NOT LIKE 'PENDING-%')";
        } elseif (in_array($statusFilter, ['packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'])) {
            $whereClause = "o.status = :status_filter";
            $params['status_filter'] = $statusFilter;
        } else {
            $whereClause = "(o.payment_status = 'paid' OR (o.status != 'pending' AND o.order_number NOT LIKE 'PENDING-%'))";
        }

        // Exclude customize orders: customize orders are restricted to the Customize Orders page only
        $whereClause .= " AND NOT EXISTS (
            SELECT 1 FROM order_items oi_chk 
            WHERE oi_chk.order_id = o.id 
              AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
        ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'";

        $sql = "
            SELECT o.*, 
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   (SELECT COUNT(*) FROM order_modifications om WHERE om.order_id = o.id AND om.status IN ('accepted', 'applied') AND om.created_at >= o.created_at) as has_modification,
                   (SELECT COUNT(*) FROM orders o_prev WHERE o_prev.user_id = o.user_id AND o_prev.payment_status = 'paid' AND o_prev.id < o.id) as repeat_count,
                   (SELECT GROUP_CONCAT(DISTINCT vp.store_name SEPARATOR ', ') 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    JOIN vendor_profiles vp ON p.vendor_id = vp.user_id 
                    WHERE oi.order_id = o.id) as vendor_names
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE {$whereClause}
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate repeat order badge
        foreach ($orders as &$order) {
            $order['repeat_count'] = (int)($order['repeat_count'] ?? 0);
            $order['has_modification'] = (int)$order['has_modification'] > 0;
        }

        return $orders;
    }

    /**
     * Get all custom/customized orders.
     */
    public function getCustomOrders($statusFilter = 'all'): array
    {
        $where = ["(EXISTS (SELECT 1 FROM order_items oi_chk WHERE oi_chk.order_id = o.id AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)) OR o.order_number LIKE 'GBC%' OR o.order_number LIKE 'GRM-CUST-%')"];
        $params = [];

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            if ($statusFilter === 'request_pending') {
                $where[] = "o.payment_status = 'request_pending'";
            } elseif ($statusFilter === 'paid') {
                $where[] = "o.payment_status = 'paid'";
            } elseif (in_array($statusFilter, ['placed', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'])) {
                $where[] = "o.status = :status_filter";
                $params['status_filter'] = $statusFilter;
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT o.*, 
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count,
                   (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_custom_pieces
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE {$whereClause}
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch custom items for each order
        $itemStmt = $this->db->prepare("
            SELECT oi.*, p.name as product_name, p.base_sku as fabric_sku,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ");

        foreach ($orders as &$order) {
            $itemStmt->execute(['order_id' => $order['id']]);
            $order['items'] = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($order['items'] as &$item) {
                if (!empty($item['customization_data'])) {
                    $item['customization_parsed'] = is_array($item['customization_data']) ? $item['customization_data'] : json_decode($item['customization_data'], true);
                } else {
                    $item['customization_parsed'] = null;
                }
            }
        }

        return $orders;
    }

    /**
     * Status counts for custom orders.
     */
    public function getCustomOrderStatusCounts(): array
    {
        $counts = [
            'all' => 0,
            'request_pending' => 0,
            'paid' => 0,
            'placed' => 0,
            'packed' => 0,
            'shipped' => 0,
            'out_for_delivery' => 0,
            'delivered' => 0,
            'cancelled' => 0,
        ];

        $sql = "
            SELECT o.status, o.payment_status, COUNT(*) as cnt
            FROM orders o
            WHERE (EXISTS (SELECT 1 FROM order_items oi_chk WHERE oi_chk.order_id = o.id AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)) OR o.order_number LIKE 'GBC%' OR o.order_number LIKE 'GRM-CUST-%')
            GROUP BY o.status, o.payment_status
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $st = $row['status'];
            $c = (int)$row['cnt'];
            $counts['all'] += $c;
            if (isset($counts[$st])) {
                $counts[$st] += $c;
            }
            if ($row['payment_status'] === 'request_pending') {
                $counts['request_pending'] += $c;
            } elseif ($row['payment_status'] === 'paid') {
                $counts['paid'] += $c;
            }
        }

        return $counts;
    }

    public function getOrderStatusCounts(): array
    {
        $counts = [
            'all' => 0,
            'placed' => 0,
            'packed' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'pending' => 0
        ];

        $stmt = $this->db->query("
            SELECT o.status, o.payment_status, o.order_number
            FROM orders o
            WHERE NOT EXISTS (
                SELECT 1 FROM order_items oi_chk 
                WHERE oi_chk.order_id = o.id 
                  AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
            ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
        ");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $st = $row['status'];
            $isPending = ($row['payment_status'] !== 'paid' && $st !== 'cancelled') || $st === 'pending' || strpos($row['order_number'], 'PENDING-') === 0;

            if ($isPending) {
                $counts['pending']++;
            } else {
                $counts['all']++;
                if (isset($counts[$st])) {
                    $counts[$st]++;
                }
            }
        }

        return $counts;
    }

    public function getVendorOrders(int $vendorId, $statusFilter = 'all')
    {
        if (is_bool($statusFilter)) {
            $statusFilter = $statusFilter ? 'pending' : 'paid';
        }

        $whereClause = "";
        $params = [
            'vendor_id1' => $vendorId,
            'vendor_id2' => $vendorId,
            'vendor_id3' => $vendorId,
            'vendor_id_exists' => $vendorId
        ];

        if ($statusFilter === 'all') {
            $whereClause = "(o.payment_status = 'paid' OR (o.status != 'pending' AND o.order_number NOT LIKE 'PENDING-%'))";
        } elseif ($statusFilter === 'pending') {
            $whereClause = "((o.payment_status != 'paid' AND o.status != 'cancelled') OR o.status = 'pending' OR o.order_number LIKE 'PENDING-%')";
        } elseif ($statusFilter === 'paid') {
            $whereClause = "o.payment_status = 'paid'";
        } elseif ($statusFilter === 'placed') {
            $whereClause = "o.status = 'placed' AND (o.payment_status = 'paid' OR o.order_number NOT LIKE 'PENDING-%')";
        } elseif (in_array($statusFilter, ['packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'])) {
            $whereClause = "o.status = :status_filter";
            $params['status_filter'] = $statusFilter;
        } else {
            $whereClause = "(o.payment_status = 'paid' OR (o.status != 'pending' AND o.order_number NOT LIKE 'PENDING-%'))";
        }

        $whereClause .= " AND NOT EXISTS (
            SELECT 1 FROM order_items oi_chk 
            WHERE oi_chk.order_id = o.id 
              AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
        ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'";
        
        $sql = "
            SELECT o.*, 
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   (SELECT COUNT(*) FROM order_modifications om WHERE om.order_id = o.id AND om.status IN ('accepted', 'applied') AND om.created_at >= o.created_at) as has_modification,
                   (SELECT COUNT(*) FROM orders o_prev WHERE o_prev.user_id = o.user_id AND o_prev.payment_status = 'paid' AND o_prev.id < o.id) as repeat_count,
                   (SELECT SUM(oi.quantity) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id AND p.vendor_id = :vendor_id1) as vendor_item_qty,
                   (SELECT SUM(oi.total_price) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id AND p.vendor_id = :vendor_id2) as vendor_subtotal,
                   (SELECT GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id AND p.vendor_id = :vendor_id3) as vendor_product_names
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE {$whereClause}
              AND EXISTS (
                  SELECT 1 FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = o.id AND p.vendor_id = :vendor_id_exists
              )
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['repeat_count'] = (int)($order['repeat_count'] ?? 0);
            $order['has_modification'] = (int)$order['has_modification'] > 0;
        }

        return $orders;
    }

    public function getVendorPendingOrdersCount(int $vendorId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT o.id)
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE p.vendor_id = :vendor_id
              AND o.status IN ('placed', 'packed')
              AND o.status != 'cancelled'
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        return (int)$stmt->fetchColumn();
    }

    public function getVendorStatusCounts(int $vendorId): array
    {
        $counts = [
            'all' => 0,
            'placed' => 0,
            'packed' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'pending' => 0
        ];

        $stmt = $this->db->prepare("
            SELECT o.status, o.payment_status, o.order_number, COUNT(DISTINCT o.id) as cnt
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE p.vendor_id = :vendor_id
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
            GROUP BY o.id, o.status, o.payment_status, o.order_number
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $st = $row['status'];
            $c = (int)$row['cnt'];
            $isPending = ($row['payment_status'] !== 'paid' && $st !== 'cancelled') || $st === 'pending' || strpos($row['order_number'], 'PENDING-') === 0;

            if ($isPending) {
                $counts['pending'] += $c;
            } else {
                $counts['all'] += $c;
                if (isset($counts[$st])) {
                    $counts[$st] += $c;
                }
            }
        }

        return $counts;
    }

    private function getPreviousOrderCount(int $userId, int $currentOrderId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM orders 
            WHERE user_id = :uid AND payment_status = 'paid' AND id < :oid
        ");
        $stmt->execute(['uid' => $userId, 'oid' => $currentOrderId]);
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus(string $orderNumber, string $status)
    {
        if ($status === 'delivered') {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = :status, 
                    delivered_at = COALESCE(delivered_at, NOW()), 
                    packing_video_expires_at = COALESCE(packing_video_expires_at, DATE_ADD(NOW(), INTERVAL 30 DAY)) 
                WHERE order_number = :id
            ");
            return $stmt->execute(['status' => $status, 'id' => $orderNumber]);
        } elseif ($status === 'packed') {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = :status, 
                    packed_at = COALESCE(packed_at, NOW()) 
                WHERE order_number = :id
            ");
            return $stmt->execute(['status' => $status, 'id' => $orderNumber]);
        }

        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE order_number = :id");
        return $stmt->execute(['status' => $status, 'id' => $orderNumber]);
    }

    public function updatePackedStatus(string $orderNumber, ?string $video1 = null, ?string $video2 = null)
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = 'packed',
                packing_video_1 = COALESCE(:v1, packing_video_1),
                packing_video_2 = COALESCE(:v2, packing_video_2),
                packed_at = NOW()
            WHERE order_number = :id
        ");
        return $stmt->execute([
            'v1' => $video1,
            'v2' => $video2,
            'id' => $orderNumber
        ]);
    }

    public function updateCourier(string $orderNumber, string $courierName, string $trackingNumber, string $trackingUrl = '')
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET courier_name = :cn, tracking_number = :tn, tracking_url = :tu
            WHERE order_number = :id
        ");
        return $stmt->execute([
            'cn' => $courierName,
            'tn' => $trackingNumber,
            'tu' => $trackingUrl,
            'id' => $orderNumber
        ]);
    }

    public function reduceStockForOrder(string $orderNumber)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_number = ?");
            $stmt->execute([$orderNumber]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new \Exception("Order not found");
            }

            $stmtItems = $this->db->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$order['id']]);
            $items = $stmtItems->fetchAll();

            $updateProductStmt = $this->db->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity - ?, sold_count = sold_count + ?
                WHERE id = ?
            ");

            $updateVariantStmt = $this->db->prepare("
                UPDATE product_variants 
                SET current_stock = current_stock - ?
                WHERE id = ?
            ");

            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                
                // Always update the main product's sold count and stock quantity
                // (Assuming stock_quantity on main product is the sum of variants or represents total stock)
                $updateProductStmt->execute([$qty, $qty, $item['product_id']]);

                if (!empty($item['variant_id'])) {
                    $updateVariantStmt->execute([$qty, $item['variant_id']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to reduce stock for order {$orderNumber}: " . $e->getMessage());
            return false;
        }
    }

    public function restoreStockForOrderId(int $orderId)
    {
        try {
            $this->db->beginTransaction();

            $stmtItems = $this->db->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$orderId]);
            $items = $stmtItems->fetchAll();

            $updateProductStmt = $this->db->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ?, sold_count = sold_count - ?
                WHERE id = ?
            ");

            $updateVariantStmt = $this->db->prepare("
                UPDATE product_variants 
                SET current_stock = current_stock + ?
                WHERE id = ?
            ");

            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                
                $updateProductStmt->execute([$qty, $qty, $item['product_id']]);

                if (!empty($item['variant_id'])) {
                    $updateVariantStmt->execute([$qty, $item['variant_id']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to restore stock for order ID {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    public function getTopSellingProducts(int $limit = 3)
    {
        $stmt = $this->db->prepare("
            SELECT p.name, SUM(oi.quantity) as total_sold
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') 
              AND o.payment_status = 'paid'
              AND oi.customization_id IS NULL
              AND oi.customization_data IS NULL
              AND o.order_number NOT LIKE 'GBC%'
              AND o.order_number NOT LIKE 'GRM-CUST-%'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getOrderStatusDistribution()
    {
        $stmt = $this->db->query("
            SELECT o.status, COUNT(*) as count 
            FROM orders o
            WHERE o.payment_status = 'paid'
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
            GROUP BY o.status
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVendorTopSellingProducts(int $vendorId, int $limit = 3)
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.base_sku, p.base_sku as sku, p.wholesale_price, p.wholesale_price as price, p.stock_quantity,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as primary_image,
                   SUM(oi.quantity) as total_sold,
                   SUM(oi.quantity) as total_qty_sold,
                   COALESCE(SUM(oi.total_price), 0) as total_revenue_generated
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE p.vendor_id = :vendor_id 
              AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') 
              AND o.payment_status = 'paid'
            GROUP BY p.id, p.name, p.base_sku, p.wholesale_price, p.stock_quantity
            ORDER BY total_sold DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':vendor_id', $vendorId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVendorOrderStatusDistribution(int $vendorId)
    {
        $stmt = $this->db->prepare("
            SELECT o.status, COUNT(DISTINCT o.id) as count 
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE p.vendor_id = :vendor_id
              AND o.payment_status = 'paid'
            GROUP BY o.status
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVendorSalesStats(int $vendorId): array
    {
        $stmtSetting = $this->db->prepare("SELECT key_value FROM system_settings WHERE key_name = 'vendor_revenue_holding_days' LIMIT 1");
        $stmtSetting->execute();
        $holdingDaysVal = $stmtSetting->fetchColumn();
        $holdingDays = ($holdingDaysVal !== false && $holdingDaysVal !== null && $holdingDaysVal !== '') ? (int)$holdingDaysVal : 7;

        // 1. Total revenue for vendor's products in paid non-cancelled orders (Matured vs Holding)
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN ({$holdingDays} <= 0 OR TIMESTAMPDIFF(DAY, o.created_at, NOW()) >= {$holdingDays}) THEN oi.total_price ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN ({$holdingDays} > 0 AND TIMESTAMPDIFF(DAY, o.created_at, NOW()) < {$holdingDays}) THEN oi.total_price ELSE 0 END), 0) as holding_revenue,
                COALESCE(SUM(oi.total_price), 0) as all_time_revenue,
                COALESCE(SUM(oi.quantity), 0) as total_units_sold,
                COUNT(DISTINCT CASE WHEN ({$holdingDays} <= 0 OR TIMESTAMPDIFF(DAY, o.created_at, NOW()) >= {$holdingDays}) THEN o.id ELSE NULL END) as total_orders,
                COUNT(DISTINCT CASE WHEN ({$holdingDays} > 0 AND TIMESTAMPDIFF(DAY, o.created_at, NOW()) < {$holdingDays}) THEN o.id ELSE NULL END) as holding_orders_count,
                COUNT(DISTINCT o.id) as all_time_orders
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE p.vendor_id = :vendor_id
              AND o.payment_status = 'paid'
              AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed')
        ");
        $stmt->execute([
            'vendor_id' => $vendorId
        ]);
        $sales = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        // 2. Pending fulfillment orders count
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT o.id)
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE p.vendor_id = :vendor_id
              AND o.payment_status = 'paid'
              AND o.status IN ('placed', 'packed', 'shipped', 'out_for_delivery')
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        $pendingOrders = (int)$stmt->fetchColumn();

        // 3. Delivered orders count
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT o.id)
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE p.vendor_id = :vendor_id
              AND o.payment_status = 'paid'
              AND o.status = 'delivered'
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        $deliveredOrders = (int)$stmt->fetchColumn();

        // 4. Products count & Low stock count
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN stock_quantity <= 5 THEN 1 ELSE 0 END) as low_stock_products
            FROM products 
            WHERE vendor_id = :vendor_id
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        $prodStats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return [
            'total_revenue' => (float)($sales['total_revenue'] ?? 0),
            'holding_revenue' => (float)($sales['holding_revenue'] ?? 0),
            'all_time_revenue' => (float)($sales['all_time_revenue'] ?? 0),
            'total_units_sold' => (int)($sales['total_units_sold'] ?? 0),
            'total_orders' => (int)($sales['total_orders'] ?? 0),
            'holding_orders_count' => (int)($sales['holding_orders_count'] ?? 0),
            'all_time_orders' => (int)($sales['all_time_orders'] ?? 0),
            'holding_days' => $holdingDays,
            'pending_orders' => $pendingOrders,
            'delivered_orders' => $deliveredOrders,
            'total_products' => (int)($prodStats['total_products'] ?? 0),
            'low_stock_products' => (int)($prodStats['low_stock_products'] ?? 0)
        ];
    }

    // ---------------------------------------------------------------------------
    // Refund / Cancellation helpers
    // ---------------------------------------------------------------------------

    /**
     * Fetch a single order by its numeric ID, optionally scoped to a user.
     * Returns null if not found or not owned by $userId.
     */
    public function getOrderById(int $id, ?int $userId = null): ?array
    {
        $sql    = "SELECT * FROM orders WHERE id = :id";
        $params = ['id' => $id];

        if ($userId !== null) {
            $sql         .= " AND user_id = :uid";
            $params['uid'] = $userId;
        }

        $stmt = $this->db->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Update order status and optionally record a cancellation reason / timestamp.
     */
    public function updateOrderCancellationStatus(
        int     $orderId,
        string  $status,
        ?string $reason = null,
        ?string $cancelledByRole = null
    ): bool {
        $sets   = ['status = :status', 'updated_at = NOW()'];
        $params = ['id' => $orderId, 'status' => $status];

        if ($reason !== null) {
            $sets[]            = 'cancellation_reason = :reason';
            $params['reason']  = $reason;
        }

        if ($cancelledByRole !== null) {
            $sets[]            = 'cancelled_by_role = :role';
            $params['role']    = $cancelledByRole;
        }

        if (in_array($status, ['cancelled', 'refund_pending', 'refund_processing', 'refund_completed', 'refund_failed'])) {
            $sets[] = 'cancelled_at = NOW()';
        }

        $sql  = 'UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * The set of statuses where a user may request cancellation/refund based on active cancellation rules.
     */
    public static function getRefundEligibleStatuses(): array
    {
        try {
            $db = \Core\Database::getInstance();
            $stmt = $db->query("SELECT DISTINCT LOWER(TRIM(rule_name)) as rule_name FROM cancellation_rules WHERE is_active = 1");
            $rules = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($rules)) {
                $aliases = [];
                foreach ($rules as $r) {
                    $aliases[] = $r;
                    if ($r === 'shipping') $aliases[] = 'shipped';
                    if ($r === 'shipped') $aliases[] = 'shipping';
                    if ($r === 'delivery') $aliases[] = 'delivered';
                    if ($r === 'delivered') $aliases[] = 'delivery';
                    if ($r === 'out_for_delivery') $aliases[] = 'out of delivery';
                    if ($r === 'out of delivery') $aliases[] = 'out_for_delivery';
                }
                return array_values(array_unique($aliases));
            }
        } catch (\Exception $e) {
            // fallback
        }
        return ['placed', 'packed', 'shipping', 'shipped', 'out_for_delivery', 'delivered'];
    }

    public function getOrdersByUser(int $userId, int $limit = 10, int $offset = 0)
    {
        $stmt = $this->db->prepare("
            SELECT o.*, 
                   (SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_name,
                   (SELECT pi.image_path FROM order_items oi JOIN product_images pi ON oi.product_id = pi.product_id WHERE oi.order_id = o.id AND pi.is_primary = 1 LIMIT 1) as first_product_image,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items
            FROM orders o
            WHERE o.user_id = :uid
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalOrdersCountByUser(int $userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getBuyerOrderStats(int $userId)
    {
        $stats = [
            'total_orders' => 0,
            'delivered_spent' => 0.0,
            'avg_order_value' => 0.0,
            'delivered_count' => 0,
            'cancelled_count' => 0,
            'preferred_payment' => '-'
        ];

        // Total paid orders
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid AND payment_status = 'paid'");
        $stmt->execute(['uid' => $userId]);
        $stats['total_orders'] = (int)$stmt->fetchColumn();

        // Delivered stats
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, SUM(COALESCE(grand_total, total_amount)) as total_spent
            FROM orders 
            WHERE user_id = :uid AND status = 'delivered' AND payment_status = 'paid'
        ");
        $stmt->execute(['uid' => $userId]);
        $deliveredData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $stats['delivered_count'] = (int)($deliveredData['count'] ?? 0);
        $stats['delivered_spent'] = (float)($deliveredData['total_spent'] ?? 0);
        
        if ($stats['delivered_count'] > 0) {
            $stats['avg_order_value'] = $stats['delivered_spent'] / $stats['delivered_count'];
        }

        // Cancelled count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid AND status IN ('cancelled', 'refund_processing', 'refund_completed')");
        $stmt->execute(['uid' => $userId]);
        $stats['cancelled_count'] = (int)$stmt->fetchColumn();

        // Preferred payment - We'll just hardcode Razorpay if they have orders, as it's the main gateway
        // $stats['preferred_payment'] = 'Cashfree'; // Archived
        if ($stats['total_orders'] > 0) {
            $stats['preferred_payment'] = 'Razorpay';
        }

        return $stats;
    }

    public function getPendingBuyersSummary()
    {
        $sql = "
            SELECT u.id as user_id, u.unique_buyer_id, u.name as buyer_name, u.email as buyer_email, u.phone as buyer_phone, u.status as user_status,
                   bp.business_name, bp.shop_location,
                   COUNT(o.id) as pending_orders_count,
                   SUM(COALESCE(o.grand_total, o.total_amount)) as total_pending_amount,
                   MAX(o.created_at) as latest_order_date,
                   MIN(o.created_at) as first_order_date
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN business_profiles bp ON u.id = bp.user_id
            WHERE o.payment_status != 'paid' AND o.status NOT IN ('cancelled', 'delivered')
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
            GROUP BY u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.status, bp.business_name, bp.shop_location
            ORDER BY latest_order_date DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBuyerPendingOrdersWithItems(int $userId)
    {
        // 1. Fetch buyer info
        $stmtBuyer = $this->db->prepare("
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.status as user_status, u.created_at as registered_at,
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number
            FROM users u
            LEFT JOIN business_profiles bp ON u.id = bp.user_id
            WHERE u.id = ?
        ");
        $stmtBuyer->execute([$userId]);
        $buyer = $stmtBuyer->fetch(\PDO::FETCH_ASSOC);

        if (!$buyer) {
            return null;
        }

        // 2. Fetch all pending orders for this buyer
        $stmtOrders = $this->db->prepare("
            SELECT o.*
            FROM orders o
            WHERE o.user_id = ? AND o.payment_status != 'paid' AND o.status NOT IN ('cancelled', 'delivered')
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
            ORDER BY o.created_at DESC
        ");
        $stmtOrders->execute([$userId]);
        $orders = $stmtOrders->fetchAll(\PDO::FETCH_ASSOC);

        // 3. For each order, fetch items
        if (!empty($orders)) {
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            
            $stmtItems = $this->db->prepare("
                SELECT oi.*, p.name, p.weight, 
                       COALESCE(
                           (SELECT image_path FROM product_images WHERE variant_id = oi.variant_id ORDER BY is_primary DESC, id ASC LIMIT 1),
                           (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1)
                       ) as image,
                       pv.name as variant_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.order_id IN ($placeholders)
                ORDER BY oi.id ASC
            ");
            $stmtItems->execute($orderIds);
            $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

            $itemsByOrder = [];
            foreach ($items as $item) {
                $itemsByOrder[$item['order_id']][] = $item;
            }

            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrder[$order['id']] ?? [];
            }
        }

        return [
            'buyer' => $buyer,
            'orders' => $orders
        ];
    }
}