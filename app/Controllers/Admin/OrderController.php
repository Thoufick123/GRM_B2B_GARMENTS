<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Order;

class OrderController extends Controller
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

    public function index()
    {
        $this->requirePermission('all_orders', 'view');
        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : null;
        $status = $_GET['status'] ?? 'all';

        $orderModel = new Order();
        if ($isVendor) {
            $orders = $orderModel->getVendorOrders($vendorId, $status);
            $statusCounts = $orderModel->getVendorStatusCounts($vendorId);
            $topProducts = $orderModel->getVendorTopSellingProducts($vendorId, 3);
            $statusDistribution = $orderModel->getVendorOrderStatusDistribution($vendorId);
        } else {
            $orders = $orderModel->getAllOrders($status);
            $statusCounts = $orderModel->getOrderStatusCounts();
            $topProducts = $orderModel->getTopSellingProducts(3);
            $statusDistribution = $orderModel->getOrderStatusDistribution();
        }

        $this->render('admin/orders/index', [
            'title' => $isVendor ? 'My Orders' : 'All Orders',
            'orders' => $orders,
            'isPendingView' => ($status === 'pending'),
            'currentStatus' => $status,
            'statusCounts' => $statusCounts,
            'isVendor' => $isVendor,
            'topProducts' => json_encode($topProducts),
            'statusDistribution' => json_encode($statusDistribution)
        ], 'admin');
    }

    public function pending()
    {
        if (!$this->hasPermission('all_orders', 'view') && !$this->hasPermission('pending_payments', 'view')) {
            $this->requirePermission('all_orders', 'view');
        }

        $orderModel = new Order();
        $buyerId = !empty($_GET['buyer_id']) ? (int)$_GET['buyer_id'] : null;

        if ($buyerId) {
            $data = $orderModel->getBuyerPendingOrdersWithItems($buyerId);
            if (!$data) {
                Session::setFlash('error', 'Buyer profile or pending orders not found.');
                $this->redirect('/admin/orders/pending');
                return;
            }

            $this->render('admin/orders/pending_buyer_view', [
                'title' => 'Pending Orders - ' . ($data['buyer']['name'] ?? 'Buyer #' . $buyerId),
                'buyer' => $data['buyer'],
                'orders' => $data['orders']
            ], 'admin');
            return;
        }

        $pendingBuyers = $orderModel->getPendingBuyersSummary();

        $this->render('admin/orders/pending', [
            'title' => 'Pending Orders (By Buyer)',
            'buyers' => $pendingBuyers,
            'isPendingView' => true
        ], 'admin');
    }

    public function updateStatus()
    {
        $this->requirePermission('all_orders', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$orderId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        $validStatuses = ['placed', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }

        $orderModel = new Order();
        
        // We need the order ID (int) for restoring stock, so let's get it first
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT id, status FROM orders WHERE order_number = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : null;
        if ($isVendor) {
            $chk = $db->prepare("SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? AND p.vendor_id = ? LIMIT 1");
            $chk->execute([$order['id'], $vendorId]);
            if (!$chk->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized order access']);
                return;
            }
        }

        $currentStatus = $order['status'];

        // Strict Forward-Only Transition map: Once moved, status can NEVER be changed backwards.
        $allowedTransitions = [
            'placed'           => ['packed'],
            'packed'           => ['shipped'],
            'shipped'          => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
            'delivered'        => [],
            'cancelled'        => []
        ];

        if ($status !== $currentStatus) {
            $allowed = $allowedTransitions[$currentStatus] ?? [];
            if (!in_array($status, $allowed)) {
                $formatCurrent = ucwords(str_replace('_', ' ', $currentStatus));
                $formatTarget = ucwords(str_replace('_', ' ', $status));
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid transition: Cannot change status from {$formatCurrent} to {$formatTarget}. Order status follows a strictly forward workflow (Placed → Packed → Shipped → Out for Delivery → Delivered) and cannot be reverted backwards."
                ]);
                return;
            }
        }

        if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
            $db = \Core\Database::getInstance();
            $db->prepare("UPDATE orders SET status = 'cancelled', cancelled_by_role = 'admin', cancelled_at = NOW() WHERE id = ?")->execute([$order['id']]);
            $result = true;
        } else {
            // $orderId here is already the order_number string (passed from JS as data-id)
            $result = $orderModel->updateStatus($orderId, $status);
        }

        if ($result) {
            // Restore inventory stock if transitioned to cancelled
            if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
                $orderModel->restoreStockForOrderId($order['id']);
            }

            // If transitioned to packed, set packed_at and send invoice email to buyer
            if ($status === 'packed') {
                $db->prepare("UPDATE orders SET packed_at = IFNULL(packed_at, NOW()) WHERE id = ?")->execute([$order['id']]);
                try {
                    $invoiceResult = \App\Services\InvoiceService::generateInvoicePdf($orderId);
                    if ($invoiceResult) {
                        $emailService = new \App\Services\EmailService();
                        $emailService->sendOrderPackedWithInvoice(
                            $invoiceResult['user'],
                            $invoiceResult['order'],
                            $invoiceResult['pdf_binary'],
                            $invoiceResult['filename'],
                            $order['packing_video_1'] ?? null,
                            $order['packing_video_2'] ?? null
                        );
                    }
                } catch (\Exception $e) {
                    error_log("[OrderController Status Packed] Invoice/Email dispatch error: " . $e->getMessage());
                }
            }

            // If transitioned to delivered, set delivered_at and 30-day retention expiry
            if ($status === 'delivered') {
                $db->prepare("UPDATE orders SET delivered_at = IFNULL(delivered_at, NOW()), packing_video_expires_at = IFNULL(packing_video_expires_at, DATE_ADD(NOW(), INTERVAL 30 DAY)) WHERE id = ?")->execute([$order['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }

    public function updateCourier()
    {
        $this->requirePermission('all_orders', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $courierName = trim($input['courier_name'] ?? '');
        $trackingNumber = trim($input['tracking_number'] ?? '');
        $trackingUrl = trim($input['tracking_url'] ?? '');

        if (!$orderId || empty($courierName)) {
            echo json_encode(['success' => false, 'message' => 'Order ID and Courier Name are required']);
            return;
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM orders WHERE order_number = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : null;
        if ($isVendor) {
            $chk = $db->prepare("SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? AND p.vendor_id = ? LIMIT 1");
            $chk->execute([$order['id'], $vendorId]);
            if (!$chk->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized order access']);
                return;
            }
        }

        $orderModel = new Order();
        $result = $orderModel->updateCourier($orderId, $courierName, $trackingNumber, $trackingUrl);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Courier updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }

    public function getCourierServices()
    {
        header('Content-Type: application/json');
        try {
            $db = \Core\Database::getInstance();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `courier_services` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL UNIQUE,
                    `default_url` VARCHAR(255) NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed defaults if table is empty
            $cnt = (int)$db->query("SELECT COUNT(*) FROM `courier_services`")->fetchColumn();
            if ($cnt === 0) {
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

            $stmt = $db->query("SELECT id, name, default_url FROM courier_services WHERE is_active = 1 ORDER BY name ASC");
            $couriers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'couriers' => $couriers]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addCourierService()
    {
        $role = \Core\Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager', 'staff', 'vendor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim($input['name'] ?? '');
        $defaultUrl = trim($input['default_url'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Courier name is required']);
            exit;
        }

        try {
            $db = \Core\Database::getInstance();
            $stmt = $db->prepare("SELECT id, is_active FROM courier_services WHERE LOWER(name) = LOWER(?) LIMIT 1");
            $stmt->execute([$name]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['is_active'] == 0) {
                    $upd = $db->prepare("UPDATE courier_services SET is_active = 1, default_url = ? WHERE id = ?");
                    $upd->execute([$defaultUrl, $existing['id']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Courier service already exists']);
                    exit;
                }
            } else {
                $ins = $db->prepare("INSERT INTO courier_services (name, default_url, is_active) VALUES (?, ?, 1)");
                $ins->execute([$name, $defaultUrl]);
            }

            $stmt = $db->query("SELECT id, name, default_url FROM courier_services WHERE is_active = 1 ORDER BY name ASC");
            $couriers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'message' => 'Courier added successfully', 'couriers' => $couriers, 'added_name' => $name]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteCourierService()
    {
        $role = \Core\Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager', 'staff', 'vendor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');

        if (!$id && empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Courier ID or name is required']);
            exit;
        }

        try {
            $db = \Core\Database::getInstance();
            if ($id) {
                $stmt = $db->prepare("DELETE FROM courier_services WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = $db->prepare("DELETE FROM courier_services WHERE LOWER(name) = LOWER(?)");
                $stmt->execute([$name]);
            }

            $stmt = $db->query("SELECT id, name, default_url FROM courier_services WHERE is_active = 1 ORDER BY name ASC");
            $couriers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'message' => 'Courier deleted successfully', 'couriers' => $couriers]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function updatePacked()
    {
        ini_set('display_errors', '0');
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->requirePermission('all_orders', 'edit');
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                return;
            }

            $orderId = trim($_POST['order_id'] ?? '');
            if (!$orderId) {
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                return;
            }

            $db = \Core\Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR id = ? LIMIT 1");
            $stmt->execute([$orderId, $orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }

            $actualOrderNumber = $order['order_number'];

            $isVendor = (Session::get('user_role') === 'vendor');
            $vendorId = $isVendor ? (int)Session::get('user_id') : null;
            if ($isVendor) {
                $chk = $db->prepare("SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? AND p.vendor_id = ? LIMIT 1");
                $chk->execute([$order['id'], $vendorId]);
                if (!$chk->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized order access']);
                    return;
                }
            }

            // Validate state transition (allow from placed or re-uploading on packed)
            $currentStatus = strtolower(trim($order['status'] ?? ''));
            if ($currentStatus !== 'placed' && $currentStatus !== 'packed') {
                echo json_encode(['success' => false, 'message' => 'Order must be in "Placed" state to transition to "Packed".']);
                return;
            }

            // Handle packing video upload directories
            $uploadDirs = [
                PUBLIC_PATH . '/uploads/packing_videos',
                BASE_PATH . '/public/uploads/packing_videos',
                BASE_PATH . '/uploads/packing_videos'
            ];
            foreach ($uploadDirs as $dir) {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
            }
            $primaryUploadDir = PUBLIC_PATH . '/uploads/packing_videos';

            $cloudinaryUploader = null;
            if (file_exists(BASE_PATH . '/app/Core/CloudinaryUploader.php')) {
                require_once BASE_PATH . '/app/Core/CloudinaryUploader.php';
                $cloudinaryUploader = new \App\Core\CloudinaryUploader();
            }

            $video1Path = $order['packing_video_1'] ?? null;
            $video2Path = $order['packing_video_2'] ?? null;

            $maxSizeBytes = 3 * 1024 * 1024; // 3 MB max limit per video
            $allowedExts = ['mp4', 'webm', 'mov', 'mkv', 'avi', 'm4v', '3gp', 'ogg'];

            // Video 1 upload
            if (isset($_FILES['packing_video_1']) && $_FILES['packing_video_1']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['packing_video_1']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['packing_video_1']['size'] > $maxSizeBytes) {
                    $mb = number_format(($_FILES['packing_video_1']['size'] ?? 0) / (1024 * 1024), 2);
                    echo json_encode(['success' => false, 'message' => "Video 1 file size ({$mb}MB) exceeds the maximum allowed limit of 3MB."]);
                    return;
                }
                if ($_FILES['packing_video_1']['error'] === UPLOAD_ERR_OK && !empty($_FILES['packing_video_1']['tmp_name'])) {
                    $file = $_FILES['packing_video_1'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExts)) {
                        echo json_encode(['success' => false, 'message' => 'Video 1 must be a valid video format (MP4, WebM, MOV).']);
                        return;
                    }
                    
                    $uploadedCloud = false;
                    if ($cloudinaryUploader) {
                        try {
                            $publicId = 'packing_videos/v1_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time();
                            $cloudRes = $cloudinaryUploader->uploadMedia($file['tmp_name'], $publicId, $file['name']);
                            if ($cloudRes && !empty($cloudRes['secure_url'])) {
                                $video1Path = $cloudRes['secure_url'];
                                $uploadedCloud = true;
                            }
                        } catch (\Throwable $ce) {
                            error_log("[OrderController Packed] Cloudinary Video 1 exception: " . $ce->getMessage());
                        }
                    }

                    if (!$uploadedCloud) {
                        $filename = 'packing_v1_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time() . '.' . $ext;
                        $destination = $primaryUploadDir . '/' . $filename;
                        if (!move_uploaded_file($file['tmp_name'], $destination)) {
                            @copy($file['tmp_name'], $destination);
                        }
                        if (file_exists($destination)) {
                            $video1Path = 'uploads/packing_videos/' . $filename;
                            // Also sync to other public folder if exists
                            if (is_dir(BASE_PATH . '/public/uploads/packing_videos')) {
                                @copy($destination, BASE_PATH . '/public/uploads/packing_videos/' . $filename);
                            }
                        }
                    }
                }
            } elseif (!empty($_POST['packing_video_url_1'])) {
                $video1Path = trim($_POST['packing_video_url_1']);
            }

            // Video 2 upload
            if (isset($_FILES['packing_video_2']) && $_FILES['packing_video_2']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['packing_video_2']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['packing_video_2']['size'] > $maxSizeBytes) {
                    $mb = number_format(($_FILES['packing_video_2']['size'] ?? 0) / (1024 * 1024), 2);
                    echo json_encode(['success' => false, 'message' => "Video 2 file size ({$mb}MB) exceeds the maximum allowed limit of 3MB."]);
                    return;
                }
                if ($_FILES['packing_video_2']['error'] === UPLOAD_ERR_OK && !empty($_FILES['packing_video_2']['tmp_name'])) {
                    $file = $_FILES['packing_video_2'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExts)) {
                        echo json_encode(['success' => false, 'message' => 'Video 2 must be a valid video format (MP4, WebM, MOV).']);
                        return;
                    }

                    $uploadedCloud = false;
                    if ($cloudinaryUploader) {
                        try {
                            $publicId = 'packing_videos/v2_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time();
                            $cloudRes = $cloudinaryUploader->uploadMedia($file['tmp_name'], $publicId, $file['name']);
                            if ($cloudRes && !empty($cloudRes['secure_url'])) {
                                $video2Path = $cloudRes['secure_url'];
                                $uploadedCloud = true;
                            }
                        } catch (\Throwable $ce) {
                            error_log("[OrderController Packed] Cloudinary Video 2 exception: " . $ce->getMessage());
                        }
                    }

                    if (!$uploadedCloud) {
                        $filename = 'packing_v2_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time() . '.' . $ext;
                        $destination = $primaryUploadDir . '/' . $filename;
                        if (!move_uploaded_file($file['tmp_name'], $destination)) {
                            @copy($file['tmp_name'], $destination);
                        }
                        if (file_exists($destination)) {
                            $video2Path = 'uploads/packing_videos/' . $filename;
                            if (is_dir(BASE_PATH . '/public/uploads/packing_videos')) {
                                @copy($destination, BASE_PATH . '/public/uploads/packing_videos/' . $filename);
                            }
                        }
                    }
                }
            } elseif (!empty($_POST['packing_video_url_2'])) {
                $video2Path = trim($_POST['packing_video_url_2']);
            }

            $orderModel = new Order();
            $res = $orderModel->updatePackedStatus($actualOrderNumber, $video1Path, $video2Path);

            if ($res) {
                // Generate dynamic invoice and send email to buyer
                $emailSent = false;
                try {
                    $invoiceResult = \App\Services\InvoiceService::generateInvoicePdf($actualOrderNumber);
                    if ($invoiceResult) {
                        $emailService = new \App\Services\EmailService();
                        $emailSent = $emailService->sendOrderPackedWithInvoice(
                            $invoiceResult['user'],
                            $invoiceResult['order'],
                            $invoiceResult['pdf_binary'],
                            $invoiceResult['filename'],
                            $video1Path,
                            $video2Path
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("[OrderController Packed] Invoice/Email dispatch error: " . $e->getMessage());
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Order marked as Packed! ' . ($emailSent ? 'Invoice PDF emailed to customer.' : 'Invoice generated.')
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update order to Packed state in database.']);
            }
        } catch (\Throwable $e) {
            error_log("[OrderController Packed] Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error packing order: ' . $e->getMessage()
            ]);
        }
    }

    public function view()
    {
        $this->requirePermission('all_orders', 'view');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/orders');
            return;
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : null;

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.order_number = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            Session::setFlash('error', 'Order not found');
            $this->redirect('/admin/orders');
            return;
        }

        $itemsQuery = "
            SELECT oi.*, p.name, p.weight, p.wholesale_price, p.vendor_id,
                   vp.store_name as vendor_store_name, vp.unique_vendor_id,
                   p.sgst as product_sgst, p.cgst as product_cgst, p.total_gst as product_total_gst,
                   pv.name as variant_name, pv.base_price as variant_base_price, pv.discount_price as variant_discount_price,
                   pv.sgst as variant_sgst, pv.cgst as variant_cgst, pv.total_gst as variant_total_gst,
                   c.sgst as category_sgst, c.cgst as category_cgst, c.hsn_code as category_hsn_code,
                   COALESCE(
                       (SELECT image_path FROM product_images WHERE variant_id = oi.variant_id ORDER BY is_primary DESC, id ASC LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1)
                   ) as image
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            WHERE oi.order_id = ?
        ";

        if ($isVendor) {
            $itemsQuery .= " AND p.vendor_id = ?";
            $stmtItems = $db->prepare($itemsQuery . " ORDER BY oi.id ASC");
            $stmtItems->execute([$order['id'], $vendorId]);
        } else {
            $stmtItems = $db->prepare($itemsQuery . " ORDER BY oi.id ASC");
            $stmtItems->execute([$order['id']]);
        }
        $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

        if ($isVendor && empty($items)) {
            Session::setFlash('error', 'You do not have permission to view this order.');
            $this->redirect('/admin/orders');
            return;
        }

        $this->render('admin/orders/view', [
            'title' => 'Order Details - ' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'isVendor' => $isVendor
        ], 'admin');
    }

    public function printSlips() {
        $type = $_GET['type'] ?? 'paid';
        if ($type === 'pending') {
            $this->requirePermission('pending_payments', 'view');
        } else {
            $this->requirePermission('all_orders', 'view');
        }
        
        $orderModel = new Order();
        $orders = $orderModel->getAllOrders($type === 'pending');
        
        if (empty($orders)) {
            die("No orders found to print.");
        }
        
        $db = \Core\Database::getInstance();
        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        
        $stmtItems = $db->prepare("
            SELECT oi.*, p.name, pv.name as variant_name, c.hsn_code as category_hsn_code
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id IN ($placeholders)
        ");
        $stmtItems->execute($orderIds);
        
        $itemsByOrder = [];
        foreach ($stmtItems->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $itemsByOrder[$item['order_id']][] = $item;
        }

        // Apply modifications
        $modOrders = array_filter($orders, fn($o) => !empty($o['has_modification']));
        if (!empty($modOrders)) {
            $modOrderIds = array_column($modOrders, 'id');
            $modPlaceholders = implode(',', array_fill(0, count($modOrderIds), '?'));
            
            // Get latest accepted/applied modification for these orders
            $modStmt = $db->prepare("
                SELECT m.id, m.order_id 
                FROM order_modifications m
                INNER JOIN (
                    SELECT order_id, MAX(id) as max_id 
                    FROM order_modifications 
                    WHERE status IN ('accepted', 'applied') 
                    GROUP BY order_id
                ) latest ON m.id = latest.max_id
                WHERE m.order_id IN ($modPlaceholders)
            ");
            $modStmt->execute($modOrderIds);
            
            $activeMods = [];
            foreach ($modStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $activeMods[$row['id']] = $row['order_id'];
            }
            
            if (!empty($activeMods)) {
                $modIds = array_keys($activeMods);
                $miPlaceholders = implode(',', array_fill(0, count($modIds), '?'));
                
                $miStmt = $db->prepare("
                    SELECT omi.*, 
                           COALESCE(p.name, p2.name) as name, 
                           COALESCE(pv.name, pv2.name) as variant_name
                    FROM order_modification_items omi
                    LEFT JOIN order_items oi ON omi.order_item_id = oi.id
                    LEFT JOIN products p ON oi.product_id = p.id
                    LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                    LEFT JOIN products p2 ON omi.product_id = p2.id
                    LEFT JOIN product_variants pv2 ON omi.variant_id = pv2.id
                    WHERE omi.modification_id IN ($miPlaceholders)
                ");
                $miStmt->execute($modIds);
                
                // Clear original items for modified orders
                foreach ($activeMods as $orderId) {
                    $itemsByOrder[$orderId] = [];
                }
                
                foreach ($miStmt->fetchAll(\PDO::FETCH_ASSOC) as $mi) {
                    if ($mi['change_type'] === 'removed') continue;
                    
                    $orderId = $activeMods[$mi['modification_id']];
                    $itemsByOrder[$orderId][] = [
                        'name' => $mi['name'],
                        'variant_name' => $mi['variant_name'],
                        'quantity' => $mi['new_qty'],
                        'total_price' => $mi['new_qty'] * $mi['new_price']
                    ];
                }
            }
        }

        $this->render('admin/orders/print', [
            'orders' => $orders,
            'itemsByOrder' => $itemsByOrder
        ], 'blank');
    }

    public function export()
    {
        $this->requirePermission('all_orders', 'view');
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $db = \Core\Database::getInstance();
        $sql = "
            SELECT o.id, o.order_number, o.created_at, u.name as customer_name, u.email,
                   o.status, o.payment_status, o.total_amount, o.grand_total, o.shipping_fee
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE DATE(o.created_at) >= :start_date AND DATE(o.created_at) <= :end_date
              AND NOT EXISTS (
                  SELECT 1 FROM order_items oi_chk 
                  WHERE oi_chk.order_id = o.id 
                    AND (oi_chk.customization_id IS NOT NULL OR oi_chk.customization_data IS NOT NULL)
              ) AND o.order_number NOT LIKE 'GBC%' AND o.order_number NOT LIKE 'GRM-CUST-%'
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $itemStmt = $db->prepare("
            SELECT oi.quantity, oi.unit_price,
                   p.wholesale_price,
                   p.sgst as product_sgst, p.cgst as product_cgst, p.total_gst as product_total_gst,
                   pv.base_price as variant_base_price, pv.discount_price as variant_discount_price,
                   pv.sgst as variant_sgst, pv.cgst as variant_cgst, pv.total_gst as variant_total_gst,
                   c.sgst as category_sgst, c.cgst as category_cgst
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id = ?
        ");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=orders_export_' . $startDate . '_to_' . $endDate . '.csv');

        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, ['Order Number', 'Date', 'Customer Name', 'Email', 'Order Status', 'Payment Status', 'Taxable Base Amount', 'GST Tax Amount', 'Items Total (Incl. Tax)', 'Shipping Fee', 'Grand Total']);

        foreach ($orders as $order) {
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $orderBase = 0.0;
            $orderTax = 0.0;
            foreach ($items as $item) {
                $qty = (int)($item['quantity'] ?? 0);
                $unitPrice = (float)($item['unit_price'] ?? 0);

                $rawBase = 0.0;
                if (!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) {
                    $rawBase = (float)$item['variant_discount_price'];
                } elseif (!empty($item['variant_base_price']) && (float)$item['variant_base_price'] > 0) {
                    $rawBase = (float)$item['variant_base_price'];
                } elseif (!empty($item['wholesale_price']) && (float)$item['wholesale_price'] > 0) {
                    $rawBase = (float)$item['wholesale_price'];
                }

                $sgstRate = 0.0;
                $cgstRate = 0.0;

                $vSgst = (float)($item['variant_sgst'] ?? 0);
                $vCgst = (float)($item['variant_cgst'] ?? 0);
                $vTot  = (float)($item['variant_total_gst'] ?? 0);

                $pSgst = (float)($item['product_sgst'] ?? 0);
                $pCgst = (float)($item['product_cgst'] ?? 0);
                $pTot  = (float)($item['product_total_gst'] ?? 0);

                $cSgst = (float)($item['category_sgst'] ?? 0);
                $cCgst = (float)($item['category_cgst'] ?? 0);

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

                if ($rawBase > 0 && $unitPrice > $rawBase) {
                    $unitTax = round($unitPrice - $rawBase, 2);
                    $unitBase = $rawBase;
                } elseif ($rawBase > 0 && abs($unitPrice - $rawBase) < 0.01) {
                    $unitBase = $unitPrice;
                    $unitTax = 0.0;
                } elseif ($totGstRate > 0) {
                    $unitBase = round($unitPrice / (1 + ($totGstRate / 100)), 2);
                    $unitTax = round($unitPrice - $unitBase, 2);
                } else {
                    $unitBase = $unitPrice;
                    $unitTax = 0.0;
                }

                $lineBase = round($unitBase * $qty, 2);
                $lineTax = round($unitTax * $qty, 2);

                $orderBase += $lineBase;
                $orderTax += $lineTax;
            }

            fputcsv($output, [
                $order['order_number'],
                $order['created_at'],
                $order['customer_name'],
                $order['email'],
                $order['status'],
                $order['payment_status'],
                number_format($orderBase, 2, '.', ''),
                number_format($orderTax, 2, '.', ''),
                number_format($order['total_amount'], 2, '.', ''),
                number_format($order['shipping_fee'], 2, '.', ''),
                number_format($order['grand_total'], 2, '.', '')
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Delete all modification records for a specific order (super admin only).
     * Used to clear out incorrect/orphan modification badges.
     */
    public function deleteModification()
    {
        $this->requirePermission('all_orders', 'edit');
        $orderNumber = trim($_POST['order_number'] ?? '');
        if (empty($orderNumber)) {
            Session::setFlash('error', 'Order number is required.');
            $this->redirect('/admin/orders');
        }

        $db = \Core\Database::getInstance();
        // Get order id from order_number
        $stmt = $db->prepare("SELECT id FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            $this->redirect('/admin/orders');
        }

        $orderId = $order['id'];

        // Delete modification items first (FK), then modifications
        $db->prepare("DELETE omi FROM order_modification_items omi JOIN order_modifications om ON omi.modification_id = om.id WHERE om.order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM order_modifications WHERE order_id = ?")->execute([$orderId]);

        Session::setFlash('success', "Modification badge cleared for order {$orderNumber}.");
        $this->redirect('/admin/orders');
    }

    /**
     * Packing Videos & Retention Backup Management Dashboard
     */
    public function packingVideos()
    {
        $this->requirePermission('all_orders', 'view');
        $db = \Core\Database::getInstance();
        
        $stmt = $db->query("
            SELECT o.*, u.name as customer_name, u.email, u.phone as customer_phone
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.packing_video_1 IS NOT NULL 
               OR o.packing_video_2 IS NOT NULL 
               OR o.status IN ('packed', 'shipped', 'out_for_delivery', 'delivered')
            ORDER BY o.id DESC
        ");
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalWithVideos = 0;
        $activeVideos = 0;
        $expiredVideos = 0;
        $totalStorageBytes = 0;

        $uploadDir = PUBLIC_PATH . '/uploads/packing_videos';
        if (is_dir($uploadDir)) {
            foreach (glob($uploadDir . '/*') as $file) {
                if (is_file($file)) {
                    $totalStorageBytes += filesize($file);
                }
            }
        }

        $now = time();
        foreach ($orders as &$ord) {
            $hasVideos = !empty($ord['packing_video_1']) || !empty($ord['packing_video_2']);
            if ($hasVideos) {
                $totalWithVideos++;
            }

            $ord['is_delivered'] = ($ord['status'] === 'delivered');
            $ord['is_expired'] = false;
            $ord['days_left'] = 30;

            if ($ord['is_delivered'] || !empty($ord['delivered_at'])) {
                $delTime = !empty($ord['delivered_at']) ? strtotime($ord['delivered_at']) : strtotime($ord['updated_at'] ?? $ord['created_at']);
                $expiryTimestamp = !empty($ord['packing_video_expires_at']) ? strtotime($ord['packing_video_expires_at']) : strtotime('+30 days', $delTime);
                $ord['expiry_timestamp'] = $expiryTimestamp;
                if (empty($ord['delivered_at'])) {
                    $ord['delivered_at'] = date('Y-m-d H:i:s', $delTime);
                    // Persist delivered_at and packing_video_expires_at in database if missing
                    $db->prepare("UPDATE orders SET delivered_at = ?, packing_video_expires_at = ? WHERE id = ? AND delivered_at IS NULL")
                       ->execute([$ord['delivered_at'], date('Y-m-d H:i:s', $expiryTimestamp), $ord['id']]);
                }
                if ($now > $expiryTimestamp) {
                    $ord['is_expired'] = true;
                    if ($hasVideos) $expiredVideos++;
                } else {
                    $ord['days_left'] = max(0, ceil(($expiryTimestamp - $now) / 86400));
                    if ($hasVideos) $activeVideos++;
                }
            } else {
                if ($hasVideos) $activeVideos++;
            }
        }
        unset($ord);

        $this->render('admin/orders/packing_videos', [
            'title'             => 'Packing Videos & Retention Backup',
            'orders'            => $orders,
            'totalWithVideos'   => $totalWithVideos,
            'activeVideos'      => $activeVideos,
            'expiredVideos'     => $expiredVideos,
            'totalStorageBytes' => $totalStorageBytes,
        ], 'admin');
    }

    /**
     * AJAX Cleanup endpoint for expired packing videos (>30 days post delivery)
     */
    public function cleanupPackingVideos()
    {
        $this->requirePermission('all_orders', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        header('Content-Type: application/json');
        $db = \Core\Database::getInstance();

        try {
            $stmt = $db->query("
                SELECT id, order_number, packing_video_1, packing_video_2, delivered_at, packing_video_expires_at
                FROM orders
                WHERE (packing_video_1 IS NOT NULL OR packing_video_2 IS NOT NULL)
                  AND (
                    (packing_video_expires_at IS NOT NULL AND packing_video_expires_at < NOW())
                    OR (delivered_at IS NOT NULL AND delivered_at < DATE_SUB(NOW(), INTERVAL 30 DAY))
                  )
            ");
            $expiredOrders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $deletedFiles = 0;
            $clearedOrders = 0;

            $updateStmt = $db->prepare("UPDATE orders SET packing_video_1 = NULL, packing_video_2 = NULL WHERE id = ?");

            foreach ($expiredOrders as $ord) {
                if (!empty($ord['packing_video_1'])) {
                    $f1 = PUBLIC_PATH . '/' . ltrim($ord['packing_video_1'], '/');
                    if (file_exists($f1)) {
                        @unlink($f1);
                        $deletedFiles++;
                    }
                }
                if (!empty($ord['packing_video_2'])) {
                    $f2 = PUBLIC_PATH . '/' . ltrim($ord['packing_video_2'], '/');
                    if (file_exists($f2)) {
                        @unlink($f2);
                        $deletedFiles++;
                    }
                }
                $updateStmt->execute([$ord['id']]);
                $clearedOrders++;
            }

            echo json_encode([
                'success' => true,
                'message' => "Cleanup complete: {$clearedOrders} expired order records cleared, {$deletedFiles} video files purged from storage."
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Cleanup error: ' . $e->getMessage()
            ]);
        }
    }
}
