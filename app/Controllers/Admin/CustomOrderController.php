<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\Order;

class CustomOrderController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->orderModel = new Order();
    }

    /**
     * List all customized fabric orders & requests
     */
    public function index()
    {
        $this->requirePermission('custom_orders', 'view');

        $status = $_GET['status'] ?? 'all';
        $orders = $this->orderModel->getCustomOrders($status);
        $statusCounts = $this->orderModel->getCustomOrderStatusCounts();

        $this->render('admin/custom_orders/index', [
            'title' => 'Customized Garments & Fabric Orders',
            'orders' => $orders,
            'currentStatus' => $status,
            'statusCounts' => $statusCounts
        ], 'admin');
    }

    /**
     * Update order status via AJAX or POST
     */
    public function updateStatus()
    {
        $this->requirePermission('custom_orders', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = $input['order_id'] ?? $_POST['order_id'] ?? null;
            $status = $input['status'] ?? $_POST['status'] ?? null;
            $paymentStatus = $input['payment_status'] ?? $_POST['payment_status'] ?? null;

            if (!$orderId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Order ID and Status are required.']);
                return;
            }

            $allowedStatuses = ['placed', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
            
            if (!in_array($status, $allowedStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status provided.']);
                return;
            }

            $db = Database::getInstance();

            // Find order by ID or order_number
            $orderStmt = $db->prepare("SELECT id, order_number, status FROM orders WHERE id = :order_id OR order_number = :order_number LIMIT 1");
            $orderStmt->execute(['order_id' => $orderId, 'order_number' => $orderId]);
            $order = $orderStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                return;
            }

            $currentStatus = $order['status'] ?? 'placed';

            // Strict Forward-Only Transition map: Once moved, status can NEVER be changed backwards.
            $allowedTransitions = [
                'placed'           => ['packed', 'cancelled'],
                'packed'           => ['shipped', 'cancelled'],
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

                // If transitioning to packed without packing video, require uploading video
                if ($status === 'packed' && empty($order['packing_video_1']) && empty($order['packing_video_2'])) {
                    echo json_encode([
                        'success' => false,
                        'require_video' => true,
                        'message' => 'A packing video is required to transition this custom order to Packed. Please record and upload a packing video.'
                    ]);
                    return;
                }
            }

            $sets = ["status = :status", "updated_at = NOW()"];
            $params = [
                'status' => $status,
                'id'     => $order['id']
            ];

            if ($paymentStatus && in_array($paymentStatus, ['paid', 'request_pending', 'pending'])) {
                $sets[] = "payment_status = :pstatus";
                $params['pstatus'] = $paymentStatus;
            }

            $courierName = trim($input['courier_name'] ?? $_POST['courier_name'] ?? '');
            $trackingNumber = trim($input['tracking_number'] ?? $_POST['tracking_number'] ?? '');
            $trackingUrl = trim($input['tracking_url'] ?? $_POST['tracking_url'] ?? '');

            if ($courierName !== '') {
                $sets[] = "courier_name = :courier_name";
                $params['courier_name'] = $courierName;
            }
            if ($trackingNumber !== '') {
                $sets[] = "tracking_number = :tracking_number";
                $params['tracking_number'] = $trackingNumber;
            }
            if ($trackingUrl !== '') {
                $sets[] = "tracking_url = :tracking_url";
                $params['tracking_url'] = $trackingUrl;
            }

            if ($status === 'packed') {
                $sets[] = "packed_at = IFNULL(packed_at, NOW())";
            } elseif ($status === 'delivered') {
                $sets[] = "delivered_at = IFNULL(delivered_at, NOW())";
                $sets[] = "packing_video_expires_at = IFNULL(packing_video_expires_at, DATE_ADD(NOW(), INTERVAL 30 DAY))";
            } elseif ($status === 'cancelled') {
                $sets[] = "cancelled_at = IFNULL(cancelled_at, NOW())";
                $sets[] = "cancelled_by_role = 'admin'";
            }

            $sql = "UPDATE orders SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $db->prepare($sql);
            $res = $stmt->execute($params);

            if ($res) {
                // If cancelled, restore stock
                if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
                    $orderModel = new Order();
                    $orderModel->restoreStockForOrderId($order['id']);
                }

                // If packed, try sending invoice email
                if ($status === 'packed' && $order['status'] !== 'packed') {
                    try {
                        $invoiceResult = \App\Services\InvoiceService::generateInvoicePdf($order['order_number']);
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
                    } catch (\Throwable $e) {
                        error_log("[CustomOrderController Status Packed] Invoice/Email dispatch note: " . $e->getMessage());
                    }
                }

                echo json_encode(['success' => true, 'message' => "Order status successfully updated to " . ucfirst(str_replace('_', ' ', $status))]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error updating status.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload packing videos & transition custom order to 'packed'
     */
    public function updatePacked()
    {
        ini_set('display_errors', '0');
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->requirePermission('custom_orders', 'edit');

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

            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = :oid OR order_number = :onum LIMIT 1");
            $stmt->execute(['oid' => $orderId, 'onum' => $orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Custom order not found']);
                return;
            }

            $currentStatus = strtolower(trim($order['status'] ?? ''));
            if ($currentStatus !== 'placed' && $currentStatus !== 'packed') {
                echo json_encode(['success' => false, 'message' => 'Custom order must be in "Placed" state to transition to "Packed".']);
                return;
            }

            // Permission check for packing video upload (Create for new video, Edit for existing video)
            $hasVideoUpload = (isset($_FILES['packing_video_1']) && $_FILES['packing_video_1']['error'] !== UPLOAD_ERR_NO_FILE)
                           || (isset($_FILES['packing_video_2']) && $_FILES['packing_video_2']['error'] !== UPLOAD_ERR_NO_FILE);
            if ($hasVideoUpload) {
                if (!$this->hasPermission('packing_videos', 'edit') && !$this->hasPermission('custom_orders', 'edit') && !$this->hasPermission('all_orders', 'edit')) {
                    echo json_encode(['success' => false, 'message' => 'Permission denied: You do not have permission to upload/edit packing videos.']);
                    return;
                }
            }

            // Create directories if needed
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

            $maxSizeBytes = 3 * 1024 * 1024; // 3 MB max per video
            $allowedExts = ['mp4', 'webm', 'mov', 'mkv', 'avi', 'm4v', '3gp', 'ogg'];
            $actualOrderNumber = $order['order_number'];

            // Process Video 1
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
                            error_log("[CustomOrder Packed] Cloudinary Video 1 error: " . $ce->getMessage());
                        }
                    }

                    if (!$uploadedCloud) {
                        $filename = 'packing_v1_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time() . '.' . $ext;
                        $dest = $primaryUploadDir . '/' . $filename;
                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            @copy($file['tmp_name'], $dest);
                        }
                        if (file_exists($dest)) {
                            $video1Path = 'uploads/packing_videos/' . $filename;
                            if (is_dir(BASE_PATH . '/public/uploads/packing_videos')) {
                                @copy($dest, BASE_PATH . '/public/uploads/packing_videos/' . $filename);
                            }
                        }
                    }
                }
            } elseif (!empty($_POST['packing_video_url_1'])) {
                $video1Path = trim($_POST['packing_video_url_1']);
            }

            // Process Video 2
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
                            error_log("[CustomOrder Packed] Cloudinary Video 2 error: " . $ce->getMessage());
                        }
                    }

                    if (!$uploadedCloud) {
                        $filename = 'packing_v2_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $actualOrderNumber) . '_' . time() . '.' . $ext;
                        $dest = $primaryUploadDir . '/' . $filename;
                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            @copy($file['tmp_name'], $dest);
                        }
                        if (file_exists($dest)) {
                            $video2Path = 'uploads/packing_videos/' . $filename;
                            if (is_dir(BASE_PATH . '/public/uploads/packing_videos')) {
                                @copy($dest, BASE_PATH . '/public/uploads/packing_videos/' . $filename);
                            }
                        }
                    }
                }
            } elseif (!empty($_POST['packing_video_url_2'])) {
                $video2Path = trim($_POST['packing_video_url_2']);
            }

            // Must provide at least 1 video to transition from placed to packed
            if (empty($video1Path) && empty($video2Path)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'At least one packing video is required to mark the order as Packed. Please select Video 1.'
                ]);
                return;
            }

            // Update order status, videos, and packed_at timestamp
            $upStmt = $db->prepare("
                UPDATE orders 
                SET status = 'packed',
                    packing_video_1 = :v1,
                    packing_video_2 = :v2,
                    packed_at = IFNULL(packed_at, NOW()),
                    updated_at = NOW()
                WHERE id = :id
            ");
            $upStmt->execute([
                'v1' => $video1Path,
                'v2' => $video2Path,
                'id' => $order['id']
            ]);

            // Dispatch dynamic invoice and email to buyer
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
                error_log("[CustomOrder Packed] Invoice dispatch error: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Custom order successfully transitioned to Packed! Packing video saved and Tax Invoice PDF emailed to customer.',
                'email_sent' => $emailSent
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
