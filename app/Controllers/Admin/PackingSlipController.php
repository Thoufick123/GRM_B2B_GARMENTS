<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\PackingSlipConfig;

class PackingSlipController extends Controller
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
     * Print single packing slip for an order (from All Orders or Customize Orders)
     */
    public function printSlip()
    {
        $canView = $this->hasPermission('all_orders', 'view')
                || $this->hasPermission('packing_slip_config', 'view')
                || $this->hasPermission('packing_videos', 'view')
                || $this->hasPermission('custom_orders', 'view');
        if (!$canView) {
            $this->requirePermission('all_orders', 'view');
            return;
        }

        $orderId = $_GET['order_id'] ?? $_GET['id'] ?? null;
        if (!$orderId) {
            die("Order ID is required.");
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, u.name as user_name, u.phone as user_phone, u.email as user_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ? OR o.order_number = ?
            LIMIT 1
        ");
        $stmt->execute([$orderId, $orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            die("Order not found.");
        }

        $orders = [$order];
        $itemsByOrder = $this->loadItemsForOrders($orders);
        $config = PackingSlipConfig::get();
        $cmsLogo = $this->getCmsLogo();

        $this->render('admin/orders/packing_slip_print', [
            'orders'       => $orders,
            'itemsByOrder' => $itemsByOrder,
            'config'       => $config,
            'cmsLogo'      => $cmsLogo
        ], 'blank');
    }

    /**
     * Print multiple packing slips in bulk
     */
    public function printBulk()
    {
        $canView = $this->hasPermission('all_orders', 'view')
                || $this->hasPermission('packing_slip_config', 'view')
                || $this->hasPermission('packing_videos', 'view')
                || $this->hasPermission('custom_orders', 'view');
        if (!$canView) {
            $this->requirePermission('all_orders', 'view');
            return;
        }

        $orderIds = [];
        if (!empty($_POST['order_ids'])) {
            $orderIds = is_array($_POST['order_ids']) ? $_POST['order_ids'] : explode(',', $_POST['order_ids']);
        } elseif (!empty($_GET['order_ids'])) {
            $orderIds = explode(',', $_GET['order_ids']);
        }

        $orderIds = array_filter(array_map('trim', $orderIds));

        if (empty($orderIds)) {
            die("No orders selected for bulk print.");
        }

        $db = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        
        $stmt = $db->prepare("
            SELECT o.*, u.name as user_name, u.phone as user_phone, u.email as user_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id IN ($placeholders) OR o.order_number IN ($placeholders)
            ORDER BY o.id DESC
        ");
        // Bind twice since query searches id or order_number
        $params = array_merge($orderIds, $orderIds);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($orders)) {
            die("Selected orders were not found.");
        }

        $itemsByOrder = $this->loadItemsForOrders($orders);
        $config = PackingSlipConfig::get();
        $cmsLogo = $this->getCmsLogo();

        $this->render('admin/orders/packing_slip_print', [
            'orders'       => $orders,
            'itemsByOrder' => $itemsByOrder,
            'config'       => $config,
            'cmsLogo'      => $cmsLogo
        ], 'blank');
    }

    /**
     * Load order items handling standard catalog products and customized fabric items
     */
    private function loadItemsForOrders(array $orders): array
    {
        if (empty($orders)) return [];
        $db = Database::getInstance();

        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        $stmtItems = $db->prepare("
            SELECT oi.*, 
                   COALESCE(p.name, 'Custom Item') as name, 
                   pv.name as variant_name, 
                   c.hsn_code as category_hsn_code
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id IN ($placeholders)
            ORDER BY oi.id ASC
        ");
        $stmtItems->execute($orderIds);
        $rawItems = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

        $itemsByOrder = [];
        foreach ($rawItems as $item) {
            // For custom fabric items, unpack customization data
            if (!empty($item['customization_data'])) {
                $cdata = is_array($item['customization_data']) ? $item['customization_data'] : json_decode($item['customization_data'], true);
                if (!empty($cdata['garment_type']) || !empty($cdata['style_name'])) {
                    $item['variant_name'] = trim(($cdata['garment_type'] ?? '') . ' ' . ($cdata['style_name'] ?? ''));
                }
            }
            $itemsByOrder[$item['order_id']][] = $item;
        }

        return $itemsByOrder;
    }

    private function getCmsLogo(): string
    {
        $db = Database::getInstance();
        try {
            $stmt = $db->query("SELECT content_data FROM cms_components WHERE section_type = 'navbar' AND is_active = 1 ORDER BY id DESC LIMIT 1");
            $comp = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($comp && !empty($comp['content_data'])) {
                $navData = json_decode($comp['content_data'], true);
                return $navData['global_settings']['logo'] ?? $navData['logo'] ?? '';
            }
        } catch (\Throwable $e) {}
        return '';
    }

    /**
     * Send RAW TSPL print job directly to local Windows thermal printer (e.g. TSC TE244 on USB002)
     */
    public function directPrint()
    {
        $this->requirePermission('all_orders', 'view');
        header('Content-Type: application/json');

        $printerName = trim($_POST['printer_name'] ?? 'TSC TE244');
        $rawBase64 = $_POST['raw_base64'] ?? '';
        $rawText = $_POST['raw_tspl'] ?? '';

        if (empty($rawBase64) && empty($rawText)) {
            echo json_encode(['success' => false, 'message' => 'No TSPL data provided to print.']);
            exit;
        }

        $data = !empty($rawBase64) ? base64_decode($rawBase64) : $rawText;

        // Save to temporary PRN file
        $tmpDir = sys_get_temp_dir();
        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'tspl_' . uniqid() . '.prn';
        file_put_contents($tmpFile, $data);

        $psScript = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'RawPrinter.ps1';

        $cmd = 'powershell -ExecutionPolicy Bypass -File ' . escapeshellarg($psScript) 
             . ' -PrinterName ' . escapeshellarg($printerName) 
             . ' -FilePath ' . escapeshellarg($tmpFile) . ' 2>&1';

        $output = shell_exec($cmd);
        @unlink($tmpFile);

        if (strpos($output, 'result: True') !== false || strpos($output, 'True') !== false) {
            echo json_encode([
                'success' => true,
                'message' => "Successfully sent directly to {$printerName}!"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Failed to communicate with {$printerName}: " . trim($output)
            ]);
        }
        exit;
    }
}
