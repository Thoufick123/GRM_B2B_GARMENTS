<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Product;

class InventoryController extends Controller
{
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $role = \Core\Session::get('user_role');
        if (!$role) {
            \Core\Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->productModel = new Product();
    }

    public function index()
    {
        $this->requirePermission('inventory', 'view');
        $isVendor = (\Core\Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)\Core\Session::get('user_id') : null;

        $summary = $this->productModel->getInventorySummary($vendorId);
        $lowStockAlerts = $this->productModel->getLowStockAlerts($vendorId);
        $inventoryList = $this->productModel->getInventoryList($vendorId);

        $this->render('admin/inventory/index', [
            'title' => $isVendor ? 'My Inventory' : 'Inventory Management',
            'summary' => $summary,
            'lowStockAlerts' => $lowStockAlerts,
            'inventoryList' => $inventoryList,
            'isVendor' => $isVendor
        ], 'admin');
    }

    public function export()
    {
        $this->requirePermission('inventory', 'view');
        $isVendor = (\Core\Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)\Core\Session::get('user_id') : null;
        $inventoryList = $this->productModel->getInventoryList($vendorId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="inventory-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \RuntimeException('Unable to open inventory export stream.');
        }

        $safeText = static function ($value): string {
            $value = (string)$value;
            return preg_match('/^[\t\r\n ]*[=+\-@]/', $value) ? "'" . $value : $value;
        };

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, [
            'Product',
            'Category',
            'SKU',
            'Variant Count',
            'Current Stock',
            'Total Stock',
            'Reserved Stock',
            'Low Stock Alert',
            'Stock Status',
            'Vendor'
        ]);

        foreach ($inventoryList as $item) {
            $currentStock = max(0, (int)($item['current_stock'] ?? 0));
            $totalStock = max(0, (int)($item['total_stock'] ?? 0));
            $lowStockAlert = max(0, (int)($item['low_stock_alert'] ?? 0));
            $stockStatus = $currentStock === 0
                ? 'Out of Stock'
                : ($currentStock <= $lowStockAlert ? 'Low Stock' : 'In Stock');

            fputcsv($output, [
                $safeText($item['product_name'] ?? ''),
                $safeText($item['category_name'] ?? 'Uncategorized'),
                $safeText($item['sku'] ?? ''),
                max(0, (int)($item['variant_count'] ?? 0)),
                $currentStock,
                $totalStock,
                max(0, (int)($item['reserve_stock'] ?? 0)),
                $lowStockAlert,
                $stockStatus,
                $safeText($item['vendor_store_name'] ?? '')
            ]);
        }

        fclose($output);
        exit;
    }

    public function view()
    {
        $this->requirePermission('inventory', 'view');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            \Core\Session::setFlash('error', 'No product ID provided.');
            $this->redirect('/admin/inventory');
        }

        $product = $this->productModel->getInventoryProductViewDetails($id);
        if (!$product) {
            \Core\Session::setFlash('error', 'Product not found.');
            $this->redirect('/admin/inventory');
        }

        $isVendor = (\Core\Session::get('user_role') === 'vendor');
        if ($isVendor && (int)($product['vendor_id'] ?? 0) !== (int)\Core\Session::get('user_id')) {
            \Core\Session::setFlash('error', 'You do not have permission to view this product inventory.');
            $this->redirect('/admin/inventory');
            return;
        }

        $variants = $this->productModel->getInventoryProductVariants($id);

        $this->render('admin/inventory/view', [
            'title' => 'Product Details',
            'product' => $product,
            'variants' => $variants,
            'isVendor' => $isVendor
        ], 'admin');
    }

    public function getVariantDetails()
    {
        $this->requirePermission('inventory', 'view');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['error' => 'No variant ID provided'], 400);
            return;
        }

        $details = $this->productModel->getVariantStockDetails($id);
        if ($details) {
            $isVendor = (\Core\Session::get('user_role') === 'vendor');
            if ($isVendor) {
                $prod = $this->productModel->findById($details['product_id']);
                if (!$prod || (int)($prod['vendor_id'] ?? 0) !== (int)\Core\Session::get('user_id')) {
                    $this->json(['error' => 'Unauthorized access to variant'], 403);
                    return;
                }
            }
            $this->json($details);
        } else {
            $this->json(['error' => 'Variant not found'], 404);
        }
    }

    public function update()
    {
        $this->requirePermission('inventory', 'edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $variantId   = $_POST['variant_id']   ?? null;
            $totalStock  = $_POST['total_stock']  ?? null;
            // current_stock is submitted as a hidden field from the modal
            $currentStock = isset($_POST['current_stock']) ? (int)$_POST['current_stock'] : null;

            if ($variantId && $totalStock !== null) {
                $isVendor = (\Core\Session::get('user_role') === 'vendor');
                if ($isVendor) {
                    $vDetails = $this->productModel->getVariantStockDetails($variantId);
                    if (!$vDetails) {
                        \Core\Session::setFlash('error', 'Variant not found.');
                        $this->redirect('/admin/inventory');
                        return;
                    }
                    $prod = $this->productModel->findById($vDetails['product_id']);
                    if (!$prod || (int)($prod['vendor_id'] ?? 0) !== (int)\Core\Session::get('user_id')) {
                        \Core\Session::setFlash('error', 'You do not have permission to update stock for this product.');
                        $this->redirect('/admin/inventory');
                        return;
                    }
                }

                $totalStock = (int)$totalStock;

                $this->productModel->updateVariantStock($variantId, $totalStock, $currentStock);
                \Core\Cache::delete('catalog_active');
                
                \Core\Session::setFlash('success', 'Stock updated successfully.');
            } else {
                \Core\Session::setFlash('error', 'Invalid stock data provided.');
            }
        }
        
        $redirectTo = $_POST['redirect_to'] ?? '/admin/inventory';
        $this->redirect($redirectTo);
    }
}
