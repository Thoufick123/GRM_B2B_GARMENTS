<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\OrderModification;

class OrderModificationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
    }

    /**
     * List all order modifications.
     */
    public function index()
    {
        $this->requirePermission('bill_modifications', 'view');
        $modModel = new OrderModification();
        $modifications = $modModel->getAllModifications();

        $this->render('admin/order_modifications/index', [
            'title'         => 'Bill Modifications',
            'modifications' => $modifications,
        ], 'admin');
    }

    public function exportDetailed()
    {
        $this->requirePermission('bill_modifications', 'view');
        $db = \Core\Database::getInstance();
        
        $stmt = $db->query("
            SELECT m.*, o.order_number, o.created_at as order_date 
            FROM order_modifications m 
            JOIN orders o ON m.order_id = o.id 
            ORDER BY m.created_at DESC
        ");
        $modifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=order_modifications_detailed_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        // Add BOM for Excel UTF-8 support
        fputs($output, "\xEF\xBB\xBF");
        
        fputcsv($output, [
            'Modification ID', 'Order Number', 'Order Date', 'Status', 'Modification Type', 
            'Total Diff', 'Item Type', 'Item Name', 'Original Qty', 'Revised Qty', 'Original Price', 'Revised Price', 'Item Diff'
        ]);
        
        foreach ($modifications as $mod) {
            $stmtItems = $db->prepare("
                SELECT mi.*, p.name as product_name 
                FROM order_modification_items mi
                LEFT JOIN products p ON mi.product_id = p.id
                WHERE mi.modification_id = ?
            ");
            $stmtItems->execute([$mod['id']]);
            $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
            
            $amountDiff = $mod['revised_total'] - $mod['original_total'];
            
            if (empty($items)) {
                fputcsv($output, [
                    $mod['id'], $mod['order_number'], $mod['order_date'], $mod['status'], 
                    $mod['reason'], $amountDiff, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A'
                ]);
            } else {
                foreach ($items as $item) {
                    $itemDiff = ($item['new_price'] * $item['new_qty']) - ($item['old_price'] * $item['old_qty']);
                    fputcsv($output, [
                        $mod['id'], $mod['order_number'], $mod['order_date'], $mod['status'], 
                        $mod['reason'], $amountDiff, 
                        $item['change_type'], $item['product_name'] ?? 'Unknown', 
                        $item['old_qty'], $item['new_qty'], 
                        $item['old_price'], $item['new_price'], 
                        $itemDiff
                    ]);
                }
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Show the "Create Modification" form for a specific order.
     */
    public function create()
    {
        if (!$this->hasPermission('bill_modifications', 'edit') && !$this->hasPermission('custom_orders', 'edit') && !$this->hasPermission('orders', 'edit')) {
            $this->requirePermission('bill_modifications', 'edit');
        }
        $orderNumber = $_GET['order'] ?? null;
        if (!$orderNumber) {
            Session::setFlash('error', 'Order not specified.');
            $this->redirect('/admin/order-modifications');
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM orders o JOIN users u ON o.user_id = u.id
            WHERE o.order_number = ? LIMIT 1
        ");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            $this->redirect('/admin/order-modifications');
        }

        // Get order items with tax and pricing
        $iStmt = $db->prepare("
            SELECT oi.*, p.name as product_name,
                   pv.name as variant_name,
                   COALESCE(
                       (SELECT image_path FROM product_images WHERE variant_id = oi.variant_id ORDER BY is_primary DESC, id ASC LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1)
                   ) as product_image,
                   COALESCE(p.moq_override, c.moq, 10) as moq,
                   p.wholesale_price as base_price,
                   p.max_amount as product_max_amount,
                   p.sgst as product_sgst, p.cgst as product_cgst, p.total_gst as product_total_gst,
                   pv.base_price as variant_price,
                   pv.discount_price as variant_discount_price,
                   pv.max_amount as variant_max_amount,
                   pv.sgst as variant_sgst, pv.cgst as variant_cgst, pv.total_gst as variant_total_gst,
                   c.sgst as category_sgst, c.cgst as category_cgst
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        $iStmt->execute([$order['id']]);
        $items = $iStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Detect if this is a custom order (GBC%, GRM-CUST-%, or custom items)
        $isCustomOrder = (strpos($orderNumber, 'GBC') === 0 || strpos($orderNumber, 'GRM-CUST-') === 0);
        if (!$isCustomOrder) {
            foreach ($items as $item) {
                if (!empty($item['customization_id']) || !empty($item['customization_data'])) {
                    $isCustomOrder = true;
                    break;
                }
            }
        }

        // Get categories for the modal dropdown:
        // When modifying a custom order, show ONLY customizable categories (is_customizable = 1)
        // Otherwise, show standard catalog categories (is_customizable = 0 or null)
        $categoryModel = new \App\Models\Category();
        $categories = $categoryModel->getAllActive(null, $isCustomOrder ? 1 : 0);

        $this->render('admin/order_modifications/create', [
            'title'         => 'Create Bill Modification — Order ' . $orderNumber,
            'order'         => $order,
            'items'         => $items,
            'categories'    => $categories,
            'isCustomOrder' => $isCustomOrder,
        ], 'admin');
    }

    /**
     * Store a new modification.
     */
    public function store()
    {
        if (!$this->hasPermission('bill_modifications', 'edit') && !$this->hasPermission('custom_orders', 'edit') && !$this->hasPermission('orders', 'edit')) {
            $this->requirePermission('bill_modifications', 'edit');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/order-modifications');
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason  = trim($_POST['reason'] ?? '');

        if (!$orderId || empty($reason)) {
            Session::setFlash('error', 'Order and reason are required.');
            $this->redirect('/admin/order-modifications');
        }

        $db = \Core\Database::getInstance();
        $stmtOrder = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmtOrder->execute([$orderId]);
        $order = $stmtOrder->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            $this->redirect('/admin/order-modifications');
        }

        // Detect if modifying a customized order
        $isCustomOrder = (strpos($order['order_number'], 'GBC') === 0 || strpos($order['order_number'], 'GRM-CUST-') === 0);
        if (!$isCustomOrder) {
            $chkStmt = $db->prepare("SELECT 1 FROM order_items WHERE order_id = ? AND (customization_id IS NOT NULL OR customization_data IS NOT NULL) LIMIT 1");
            $chkStmt->execute([$orderId]);
            if ($chkStmt->fetch()) {
                $isCustomOrder = true;
            }
        }

        $otherFees     = max(0, (float)($_POST['other_fees'] ?? 0));
        $originalTotal = (float)$order['grand_total'];

        // Collect item changes from POST
        $itemChanges       = [];
        $revisedItemsTotal = 0.0;

        $itemIds    = $_POST['item_id'] ?? [];
        $newQtys    = $_POST['new_qty'] ?? [];
        $oldQtys    = $_POST['old_qty'] ?? [];
        $oldPrices  = $_POST['old_price'] ?? [];
        $newPrices  = $_POST['new_price'] ?? [];
        $itemNotes  = $_POST['item_note'] ?? [];
        $removed    = $_POST['removed'] ?? []; // array of item_ids marked removed

        foreach ($itemIds as $idx => $itemId) {
            $itemId   = (int)$itemId;
            $oldQty   = (int)($oldQtys[$idx] ?? 0);
            $newQty   = (int)($newQtys[$idx] ?? $oldQty);
            $oldPrice = (float)($oldPrices[$idx] ?? 0);
            $newPrice = (float)($newPrices[$idx] ?? $oldPrice);
            $note     = trim($itemNotes[$idx] ?? '');

            $isRemoved = in_array((string)$itemId, array_map('strval', $removed));

            if ($isRemoved) {
                $changeType = 'removed';
                $newQty     = 0;
                $newPrice   = 0;
            } elseif ($newQty !== $oldQty && $newPrice !== $oldPrice) {
                $changeType         = 'price_changed';
            } elseif ($newQty !== $oldQty) {
                $changeType         = 'qty_changed';
            } elseif ($newPrice !== $oldPrice) {
                $changeType         = 'price_changed';
            } else {
                $changeType         = 'unchanged';
            }

            $itemChanges[] = [
                'order_item_id'          => $itemId,
                'change_type'            => $changeType,
                'old_qty'                => $oldQty,
                'new_qty'                => $newQty,
                'old_price'              => $oldPrice,
                'new_price'              => $newPrice,
                'replacement_variant_id' => null,
                'note'                   => $note,
                'product_id'             => null,
                'variant_id'             => null,
            ];
        }

        // Parse entirely new items
        $newProductIds = $_POST['new_p_id'] ?? [];
        $newVariantIds = $_POST['new_v_id'] ?? [];
        $newPQtys      = $_POST['new_p_qty'] ?? [];
        $newPPrices    = $_POST['new_p_price'] ?? [];
        $newPNotes     = $_POST['new_p_note'] ?? [];
        $newReplaceItemIds = $_POST['new_replace_item_id'] ?? [];

        foreach ($newProductIds as $idx => $pid) {
            $pid    = (int)$pid;
            if (!$pid) continue;

            $vid    = !empty($newVariantIds[$idx]) ? (int)$newVariantIds[$idx] : null;
            $qty    = (int)($newPQtys[$idx] ?? 1);
            $price  = (float)($newPPrices[$idx] ?? 0);
            $note   = trim($newPNotes[$idx] ?? '');
            $replaceItemId = (int)($newReplaceItemIds[$idx] ?? 0);

            if ($replaceItemId > 0) {
                $existingChangeIndex = null;
                foreach ($itemChanges as $changeIndex => $change) {
                    if ((int)$change['order_item_id'] === $replaceItemId) {
                        $existingChangeIndex = $changeIndex;
                        break;
                    }
                }

                if ($existingChangeIndex === null) {
                    Session::setFlash('error', 'The item selected for replacement is not part of this order.');
                    $this->redirect('/admin/order-modifications');
                    return;
                }

                $itemChanges[$existingChangeIndex] = array_merge($itemChanges[$existingChangeIndex], [
                    'change_type' => 'replaced',
                    'new_qty' => $qty,
                    'new_price' => $price,
                    'product_id' => $pid,
                    'variant_id' => $vid,
                    'replacement_variant_id' => $vid,
                    'note' => $note,
                ]);
                continue;
            }

            $itemChanges[] = [
                'order_item_id'          => null,
                'change_type'            => 'added',
                'old_qty'                => 0,
                'new_qty'                => $qty,
                'old_price'              => 0,
                'new_price'              => $price,
                'replacement_variant_id' => null,
                'note'                   => $note,
                'product_id'             => $pid,
                'variant_id'             => $vid,
            ];
        }

        foreach ($itemChanges as $change) {
            if ($change['change_type'] !== 'removed') {
                $revisedItemsTotal += (int)$change['new_qty'] * (float)$change['new_price'];
            }
        }

        $revisedTotal = $revisedItemsTotal + $otherFees;

        // Fetch existing items to resolve product IDs for validation
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT id, product_id FROM order_items WHERE order_id = :oid");
        $stmt->execute(['oid' => $orderId]);
        $existingItems = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $existingItems[$row['id']] = $row['product_id'];
        }

        $validationProductIds = [];
        foreach ($itemChanges as $ic) {
            if ($ic['change_type'] === 'removed') continue;
            
            $pid = $ic['change_type'] === 'replaced'
                ? $ic['product_id']
                : ($ic['order_item_id'] ? ($existingItems[$ic['order_item_id']] ?? null) : $ic['product_id']);
            if ($pid) {
                $validationProductIds[$pid] = true;
            }
        }

        $productInfo = [];
        if (!empty($validationProductIds)) {
            $inClause = implode(',', array_keys($validationProductIds));
            $stmt = $db->query("
                SELECT p.id, p.name, p.category_id, c.name as category_name, COALESCE(p.moq_override, c.moq, 10) as moq 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id IN ($inClause)
            ");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $productInfo[$row['id']] = $row;
            }
        }

        $categoryTotals = [];
        $validationErrors = [];
        
        foreach ($itemChanges as $ic) {
            if ($ic['change_type'] === 'removed') continue;
            
            $pid = $ic['change_type'] === 'replaced'
                ? $ic['product_id']
                : ($ic['order_item_id'] ? ($existingItems[$ic['order_item_id']] ?? null) : $ic['product_id']);
            if (!$pid || !isset($productInfo[$pid])) continue;
            
            $info = $productInfo[$pid];
            $qty = $ic['new_qty'];
            
            // For custom orders: validate that all active/added products belong ONLY to customizable categories
            if ($isCustomOrder) {
                $catId = $info['category_id'];
                $catCheck = $db->prepare("SELECT is_customizable FROM categories WHERE id = ?");
                $catCheck->execute([$catId]);
                $catRow = $catCheck->fetch(\PDO::FETCH_ASSOC);
                if (!$catRow || (int)$catRow['is_customizable'] !== 1) {
                    $validationErrors[] = "Customized orders can only include products from customizable categories. Product <strong>{$info['name']}</strong> is not customizable.";
                }
            } else {
                if ($qty > 0 && $qty < $info['moq']) {
                    $validationErrors[] = "Minimum order quantity for <strong>{$info['name']}</strong> is {$info['moq']} pieces.";
                }
            }
            
            $catId = $info['category_id'];
            if (!isset($categoryTotals[$catId])) {
                $categoryTotals[$catId] = ['name' => $info['category_name'], 'total' => 0];
            }
            $categoryTotals[$catId]['total'] += ($qty * $ic['new_price']);
        }
        
        // Standard wholesale rules only apply to non-custom orders
        if (!$isCustomOrder) {
            $ruleErrors = $this->validateOrderRules($categoryTotals);
            $validationErrors = array_merge($validationErrors, $ruleErrors);
        }
        
        if (!empty($validationErrors)) {
            $stmt = $db->prepare("SELECT order_number FROM orders WHERE id = :oid");
            $stmt->execute(['oid' => $orderId]);
            $orderNumber = $stmt->fetchColumn();
            
            \Core\Session::setFlash('error', implode('<br>', $validationErrors));
            $this->redirect("/admin/order-modifications/create?order={$orderNumber}");
        }

        // Check if there are any actual changes
        $hasActualChanges = false;
        foreach ($itemChanges as $ic) {
            if ($ic['change_type'] !== 'unchanged') {
                $hasActualChanges = true;
                break;
            }
        }
        
        if (!$hasActualChanges && abs($originalTotal - $revisedTotal) < 0.01) {
            $stmt = $db->prepare("SELECT order_number FROM orders WHERE id = :oid");
            $stmt->execute(['oid' => $orderId]);
            $orderNumber = $stmt->fetchColumn();
            
            \Core\Session::setFlash('success', 'No changes were made to the order.');
            $this->redirect("/admin/orders/{$orderNumber}");
        }

        // Handle payment screenshot upload (upload to Cloudinary cloud storage)
        $screenshotPath = null;
        if (!empty($_FILES['payment_screenshot']['tmp_name']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['payment_screenshot'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
            if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                require_once BASE_PATH . '/app/Core/CloudinaryUploader.php';
                $uploader = new \App\Core\CloudinaryUploader();
                $publicId = 'modifications/pay_' . time() . '_' . uniqid();
                $uploadResult = $uploader->uploadMedia($file['tmp_name'], $publicId, $file['name']);
                if ($uploadResult && isset($uploadResult['secure_url'])) {
                    $screenshotPath = $uploadResult['secure_url'];
                }
            }
        }
        // Capture extra payment details
        $extraPaymentStatus = $_POST['extra_payment_status'] ?? 'pending';
        $extraPaymentNote = $_POST['extra_payment_note'] ?? null;

        try {
            $modModel = new OrderModification();
            $modId    = $modModel->createModification(
                $orderId,
                (int)Session::get('user_id'),
                $reason,
                $originalTotal,
                $revisedTotal,
                $itemChanges,
                $screenshotPath,
                $extraPaymentStatus,
                $extraPaymentNote
            );

            Session::setFlash('success', 'Bill modification created and applied successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to modify order: ' . $e->getMessage());
        }

        $this->redirect('/admin/order-modifications');
    }

    /**
     * API endpoint to search products to add to a modification.
     */
    public function searchProducts()
    {
        if (!$this->hasPermission('bill_modifications', 'edit') && !$this->hasPermission('custom_orders', 'edit') && !$this->hasPermission('orders', 'edit')) {
            $this->requirePermission('bill_modifications', 'edit');
        }
        header('Content-Type: application/json');

        $categoryId = (int)($_GET['category_id'] ?? 0);
        $subCategoryId = (int)($_GET['sub_category_id'] ?? 0);
        $isCustom = isset($_GET['is_custom']) ? (int)$_GET['is_custom'] : null;
        $orderId = (int)($_GET['order_id'] ?? 0);
        
        $db = \Core\Database::getInstance();

        if ($isCustom === null && $orderId > 0) {
            $stmt = $db->prepare("SELECT order_number FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $ordRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($ordRow && (strpos($ordRow['order_number'], 'GBC') === 0 || strpos($ordRow['order_number'], 'GRM-CUST-') === 0)) {
                $isCustom = 1;
            }
        }

        if ($isCustom === null && $categoryId > 0) {
            $cStmt = $db->prepare("SELECT is_customizable FROM categories WHERE id = ?");
            $cStmt->execute([$categoryId]);
            $catRow = $cStmt->fetch(\PDO::FETCH_ASSOC);
            if ($catRow && (int)$catRow['is_customizable'] === 1) {
                $isCustom = 1;
            }
        }

        $customCondition = ($isCustom === 1) ? "c.is_customizable = 1" : "(c.is_customizable = 0 OR c.is_customizable IS NULL)";
        
        if ($categoryId > 0) {
            $sql = "
                SELECT p.id as product_id, p.name as product_name, p.wholesale_price as base_price, 
                       p.max_amount as product_max_amount,
                       p.sgst as product_sgst, p.cgst as product_cgst, p.total_gst as product_total_gst,
                       pv.id as variant_id, pv.name as variant_name, pv.base_price as variant_price,
                       pv.discount_price as variant_discount_price,
                       pv.max_amount as variant_max_amount,
                       pv.sgst as variant_sgst, pv.cgst as variant_cgst, pv.total_gst as variant_total_gst,
                       c.sgst as category_sgst, c.cgst as category_cgst,
                       COALESCE(
                           (SELECT image_path FROM product_images WHERE variant_id = pv.id ORDER BY is_primary DESC, id ASC LIMIT 1),
                           (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1)
                       ) as image_path,
                       COALESCE(p.moq_override, c.moq, 10) as moq,
                       GREATEST(pv.current_stock, 0) as variant_stock,
                       CASE 
                           WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                           ELSE COALESCE(
                               (SELECT SUM(GREATEST(pv2.current_stock, 0)) FROM product_variants pv2 WHERE pv2.product_id = p.id),
                               GREATEST(p.stock_quantity, 0)
                           )
                       END as product_stock
                FROM products p
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = :cid AND p.status = 'active' AND $customCondition
            ";
            $params = ['cid' => $categoryId];
            
            if ($subCategoryId > 0) {
                $sql .= " AND p.sub_category_id = :scid";
                $params['scid'] = $subCategoryId;
            }
            
            $sql .= " ORDER BY p.name ASC, pv.name ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        } else {
            // Return nothing if no category is selected
            echo json_encode([]);
            exit;
        }

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($results as $row) {
            $stock = $row['variant_id'] ? (int)($row['variant_stock'] ?? 0) : (int)($row['product_stock'] ?? 0);
            $moq = (int)($row['moq'] ?? 1);
            if ($isCustom === 1 && empty($row['moq_override'])) {
                $moq = 1;
            }
            if ($moq < 1) {
                $moq = 1;
            }
            
            // Validation: Do not show products if current stock is less than MOQ (cannot be ordered)
            if ($stock < $moq) {
                continue;
            }

            // Determine effective base price
            if (!empty($row['variant_discount_price']) && (float)$row['variant_discount_price'] > 0) {
                $unitPrice = (float)$row['variant_discount_price'];
            } elseif (!empty($row['variant_price']) && (float)$row['variant_price'] > 0) {
                $unitPrice = (float)$row['variant_price'];
            } else {
                $unitPrice = (float)($row['base_price'] ?? 0);
            }

            // Determine tax rates
            $sgstRate = 0.0;
            $cgstRate = 0.0;

            $vSgst = (float)($row['variant_sgst'] ?? 0);
            $vCgst = (float)($row['variant_cgst'] ?? 0);
            $vTot  = (float)($row['variant_total_gst'] ?? 0);

            $pSgst = (float)($row['product_sgst'] ?? 0);
            $pCgst = (float)($row['product_cgst'] ?? 0);
            $pTot  = (float)($row['product_total_gst'] ?? 0);

            $cSgst = (float)($row['category_sgst'] ?? 0);
            $cCgst = (float)($row['category_cgst'] ?? 0);

            if ($vSgst > 0 || $vCgst > 0) {
                $sgstRate = $vSgst;
                $cgstRate = $vCgst;
            } elseif ($vTot > 0) {
                $sgstRate = round($vTot / 2, 2);
                $cgstRate = round($vTot / 2, 2);
            } elseif ($pSgst > 0 || $pCgst > 0) {
                $sgstRate = $pSgst;
                $cgstRate = $pCgst;
            } elseif ($pTot > 0) {
                $sgstRate = round($pTot / 2, 2);
                $cgstRate = round($pTot / 2, 2);
            } elseif ($cSgst > 0 || $cCgst > 0) {
                $sgstRate = $cSgst;
                $cgstRate = $cCgst;
            }

            $totGstRate = $sgstRate + $cgstRate;

            $formatted[] = [
                'id' => $row['variant_id'] ? $row['product_id'] . '-' . $row['variant_id'] : $row['product_id'],
                'product_id' => $row['product_id'],
                'variant_id' => $row['variant_id'],
                'name' => $row['product_name'] . ($row['variant_name'] ? ' (' . $row['variant_name'] . ')' : ''),
                'gst_rate' => $totGstRate,
                'sgst_rate' => $sgstRate,
                'cgst_rate' => $cgstRate,
                'price' => $unitPrice,
                'image' => $row['image_path'] ?? '',
                'moq' => $moq,
                'stock' => $stock
            ];
        }

        echo json_encode($formatted);
        exit;
    }

    /**
     * API endpoint to get subcategories for a given category.
     */
    public function getSubcategories()
    {
        if (!$this->hasPermission('bill_modifications', 'edit') && !$this->hasPermission('custom_orders', 'edit') && !$this->hasPermission('orders', 'edit')) {
            $this->requirePermission('bill_modifications', 'edit');
        }
        header('Content-Type: application/json');

        $categoryId = (int)($_GET['category_id'] ?? 0);
        if ($categoryId <= 0) {
            echo json_encode([]);
            exit;
        }

        $isCustom = isset($_GET['is_custom']) ? (int)$_GET['is_custom'] : null;
        $db = \Core\Database::getInstance();
        if ($isCustom === null) {
            $cStmt = $db->prepare("SELECT is_customizable FROM categories WHERE id = ?");
            $cStmt->execute([$categoryId]);
            $catRow = $cStmt->fetch(\PDO::FETCH_ASSOC);
            if ($catRow && (int)$catRow['is_customizable'] === 1) {
                $isCustom = 1;
            }
        }

        $customCondition = ($isCustom === 1) ? "c.is_customizable = 1" : "(c.is_customizable = 0 OR c.is_customizable IS NULL)";

        $stmt = $db->prepare("
            SELECT sc.id, sc.name 
            FROM sub_categories sc 
            JOIN categories c ON sc.category_id = c.id
            WHERE sc.category_id = :cid AND $customCondition
            ORDER BY sc.name ASC
        ");
        $stmt->execute(['cid' => $categoryId]);
        $subCategories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($subCategories);
        exit;
    }

    /**
     * View a single modification detail.
     */
    public function view()
    {
        $this->requirePermission('bill_modifications', 'view');
        $modId = (int)($_GET['id'] ?? 0);
        if (!$modId) {
            $this->redirect('/admin/order-modifications');
        }

        $modModel = new OrderModification();
        $mod      = $modModel->getModificationWithItems($modId);

        if (!$mod) {
            Session::setFlash('error', 'Modification not found.');
            $this->redirect('/admin/order-modifications');
        }

        $this->render('admin/order_modifications/view', [
            'title' => 'Bill Modification — ' . ($mod['order_number'] ?? ''),
            'mod'   => $mod,
        ], 'admin');
    }

    private function validateOrderRules($categoryTotals)
    {
        $errors = [];
        if (empty($categoryTotals)) {
            return $errors;
        }

        $mainCatId = null;
        $maxTotal = -1;
        foreach ($categoryTotals as $catId => $data) {
            if ($data['total'] > $maxTotal) {
                $maxTotal = $data['total'];
                $mainCatId = $catId;
            }
        }

        if (!$mainCatId) {
            return $errors;
        }

        $ruleModel = new \App\Models\OrderRule();
        $activeRules = $ruleModel->getActiveRules();
        $categoryModel = new \App\Models\Category();
        $allCategories = $categoryModel->getAll();
        $categoryNames = [];
        foreach ($allCategories as $cat) {
            $categoryNames[$cat['id']] = $cat['name'];
        }

        $mainRule = null;
        foreach ($activeRules as $rule) {
            if ($rule['category_id'] == $mainCatId) {
                $mainRule = $rule;
                break;
            }
        }

        if ($mainRule) {
            $mainMinAmount = $mainRule['min_amount'];
            $secondCategoryRules = json_decode($mainRule['second_category_rules'], true) ?? [];
            $total = $categoryTotals[$mainCatId]['total'];

            if ($total > 0 && $total < $mainMinAmount) {
                $errors[] = "You must order a minimum of ₹" . format_price($mainMinAmount) . " from <strong>" . htmlspecialchars($mainRule['category_name']) . "</strong>. The modified order currently has ₹" . format_price($total) . ".";
            }

            if (!empty($secondCategoryRules)) {
                foreach ($secondCategoryRules as $scId => $secondMinAmount) {
                    $secondTotal = 0;
                    if (isset($categoryTotals[$scId])) {
                        $secondTotal = $categoryTotals[$scId]['total'];
                    }
                    
                    if ($secondTotal > 0 && $secondTotal < $secondMinAmount) {
                        $scName = isset($categoryNames[$scId]) ? htmlspecialchars($categoryNames[$scId]) : 'Category';
                        $errors[] = "Because the primary category is <strong>" . htmlspecialchars($mainRule['category_name']) . "</strong>, the order from <strong>" . $scName . "</strong> must be at least ₹" . format_price($secondMinAmount) . ". The modified order currently has ₹" . format_price($secondTotal) . ".";
                    }
                }
            }
        }

        $enforcedCategories = [];
        if ($mainRule) {
            $enforcedCategories[] = $mainCatId;
            if (!empty($secondCategoryRules)) {
                foreach ($secondCategoryRules as $scId => $amt) {
                    $enforcedCategories[] = $scId;
                }
            }
        }

        foreach ($categoryTotals as $catId => $data) {
            if (!in_array($catId, $enforcedCategories)) {
                $cat = $categoryModel->findById($catId);
                if (!empty($cat['min_order_value']) && $cat['min_order_value'] > 0) {
                    if ($data['total'] < $cat['min_order_value']) {
                        $errors[] = "You must order a minimum of ₹" . format_price($cat['min_order_value']) . " from <strong>" . htmlspecialchars($cat['name']) . "</strong>. The modified order currently has ₹" . format_price($data['total']) . ".";
                    }
                }
            }
        }
        
        return $errors;
    }
}
