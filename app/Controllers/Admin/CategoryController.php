<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\ImageProcessor;
use App\Models\Category;
use App\Models\MediaUpload;
use App\Core\CloudinaryUploader;

class CategoryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Base admin authentication is handled by routing or BaseController usually.
        // We will strictly rely on requirePermission for authorization.
        $role = Session::get('user_role');
        if (!$role) {
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'view');

        $categoryModel = new Category();
        $categories = $categoryModel->getAll($isCustomize ? 1 : 0);

        // Fetch global minimum order cart value setting
        $globalMinCartValue = 0.0;
        try {
            $settingModel = new \App\Models\Setting();
            $globalMinCartValue = (float)$settingModel->get('min_order_cart_value', 0);
        } catch (\Throwable $e) {}
        
        $this->render('admin/catalog/categories/index', [
            'title' => $isCustomize ? 'Customize Categories' : 'Manage Categories',
            'categories' => $categories,
            'module' => $module,
            'isCustomize' => $isCustomize,
            'globalMinCartValue' => $globalMinCartValue
        ], 'admin');
    }

    public function updateGlobalCartLimit()
    {
        $module = $_POST['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'edit');
        $redirectUrl = '/admin/catalog/categories' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectUrl);
        }

        $rawVal = trim($_POST['min_order_cart_value'] ?? '');
        $limit = ($rawVal !== '' && is_numeric($rawVal) && (float)$rawVal >= 0) ? (float)$rawVal : 0.0;

        try {
            $settingModel = new \App\Models\Setting();
            $settingModel->set('min_order_cart_value', (string)$limit);

            if ($limit > 0) {
                Session::setFlash('success', 'Global minimum order cart value set to ₹' . number_format($limit, 2) . ' successfully.');
            } else {
                Session::setFlash('success', 'Global minimum order cart value requirement removed.');
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update global cart limit: ' . $e->getMessage());
        }

        $this->redirect($redirectUrl);
    }

    public function store()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize') || (!empty($_POST['is_customizable']) && (int)$_POST['is_customizable'] === 1);
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'create');
        $redirectUrl = '/admin/catalog/categories' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectUrl);
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = $name;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'hsn_code' => !empty($_POST['hsn_code']) ? trim($_POST['hsn_code']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'image_path' => '',
            'min_order_value' => !empty($_POST['min_order_value']) ? (float)$_POST['min_order_value'] : null,
            'min_cart_value' => !empty($_POST['min_cart_value']) ? (float)$_POST['min_cart_value'] : null,
            'sgst' => isset($_POST['sgst']) && $_POST['sgst'] !== '' ? (float)$_POST['sgst'] : 0.00,
            'cgst' => isset($_POST['cgst']) && $_POST['cgst'] !== '' ? (float)$_POST['cgst'] : 0.00,
            'status' => $_POST['status'] ?? 'active',
            'is_customizable' => $isCustomize ? 1 : 0
        ];

        // Handle image upload via Cloudinary
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new CloudinaryUploader();
            $cloudinaryResponse = $uploader->uploadImage($_FILES['image']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $data['image_path'] = $cloudinaryResponse['secure_url'];
            } else {
                Session::setFlash('error', 'Failed to upload category image to cloud storage.');
            }
        } elseif (isset($_POST['image_path'])) {
            $data['image_path'] = $_POST['image_path'];
        }

        if (empty($data['name'])) {
            Session::setFlash('error', 'Name is required.');
            $this->redirect($redirectUrl);
            return;
        }

        if (!empty($data['hsn_code']) && !preg_match('/^\d+$/', $data['hsn_code'])) {
            Session::setFlash('error', 'HSN Code must contain only numbers.');
            $this->redirect($redirectUrl);
            return;
        }

        try {
            $categoryModel = new Category();

            // Prevent duplicate category names
            $existingDuplicate = $categoryModel->findByName($data['name'], $isCustomize ? 1 : 0);
            if ($existingDuplicate) {
                Session::setFlash('error', 'A category named "' . htmlspecialchars($data['name']) . '" already exists. Duplicate categories are not allowed.');
                $this->redirect($redirectUrl);
                return;
            }

            $categoryModel->create($data);
            Session::setFlash('success', ($isCustomize ? 'Customize Category' : 'Category') . ' created successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error creating category: ' . $e->getMessage());
        }

        $this->redirect($redirectUrl);
    }

    public function update()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize') || (!empty($_POST['is_customizable']) && (int)$_POST['is_customizable'] === 1);
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'edit');
        $redirectUrl = '/admin/catalog/categories' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectUrl);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = $name;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'hsn_code' => !empty($_POST['hsn_code']) ? trim($_POST['hsn_code']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'min_order_value' => !empty($_POST['min_order_value']) ? (float)$_POST['min_order_value'] : null,
            'min_cart_value' => !empty($_POST['min_cart_value']) ? (float)$_POST['min_cart_value'] : null,
            'sgst' => isset($_POST['sgst']) && $_POST['sgst'] !== '' ? (float)$_POST['sgst'] : 0.00,
            'cgst' => isset($_POST['cgst']) && $_POST['cgst'] !== '' ? (float)$_POST['cgst'] : 0.00,
            'status' => $_POST['status'] ?? 'active',
            'is_customizable' => $isCustomize ? 1 : 0
        ];

        // Handle image upload via Cloudinary (fallback for traditional file upload)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new CloudinaryUploader();
            $cloudinaryResponse = $uploader->uploadImage($_FILES['image']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $data['image_path'] = $cloudinaryResponse['secure_url'];
            } else {
                Session::setFlash('error', 'Failed to upload new category image to cloud storage.');
            }
        } elseif (isset($_POST['image_path'])) {
            // Media selector sets the image_path directly
            $data['image_path'] = $_POST['image_path'];
        } else {
            // Retrieve current image_path if no new file is uploaded and image_path is not in POST
            $categoryModel = new Category();
            $existing = $categoryModel->findById($id);
            $data['image_path'] = $existing['image_path'] ?? '';
            if (!isset($_POST['is_customizable']) && isset($existing['is_customizable'])) {
                $data['is_customizable'] = (int)$existing['is_customizable'];
            }
        }

        if ($id <= 0 || empty($data['name']) || empty($data['slug'])) {
            Session::setFlash('error', 'Invalid input.');
            $this->redirect($redirectUrl);
            return;
        }

        if (!empty($data['hsn_code']) && !preg_match('/^\d+$/', $data['hsn_code'])) {
            Session::setFlash('error', 'HSN Code must contain only numbers.');
            $this->redirect($redirectUrl);
            return;
        }

        try {
            $categoryModel = new Category();

            // Prevent duplicate category names
            $existingDuplicate = $categoryModel->findByName($data['name'], $isCustomize ? 1 : 0, $id);
            if ($existingDuplicate) {
                Session::setFlash('error', 'A category named "' . htmlspecialchars($data['name']) . '" already exists. Duplicate categories are not allowed.');
                $this->redirect($redirectUrl);
                return;
            }
            $categoryModel->update($id, $data);

            // Sync updated GST rates to all products & variants in this category
            $newSgst = (float)$data['sgst'];
            $newCgst = (float)$data['cgst'];
            $newTotalGst = $newSgst + $newCgst;

            $db = \Core\Database::getInstance();
            $db->prepare("UPDATE products SET sgst = ?, cgst = ?, total_gst = ? WHERE category_id = ?")
               ->execute([$newSgst, $newCgst, $newTotalGst, $id]);

            $db->prepare("UPDATE product_variants pv JOIN products p ON pv.product_id = p.id SET pv.sgst = ?, pv.cgst = ?, pv.total_gst = ? WHERE p.category_id = ?")
               ->execute([$newSgst, $newCgst, $newTotalGst, $id]);

            // Recalculate max_amount for all variants in this category
            $variants = $db->query("SELECT pv.id, pv.base_price, pv.discount_price FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE p.category_id = {$id}")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($variants as $v) {
                $eff = (!empty($v['discount_price']) && (float)$v['discount_price'] > 0) ? (float)$v['discount_price'] : (float)$v['base_price'];
                $maxAmt = $eff;
                $db->prepare("UPDATE product_variants SET max_amount = ? WHERE id = ?")->execute([$maxAmt, $v['id']]);
            }

            // Recalculate max_amount for all products in this category
            $prods = $db->query("SELECT id, wholesale_price FROM products WHERE category_id = {$id}")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($prods as $p) {
                $firstVarMax = $db->query("SELECT max_amount FROM product_variants WHERE product_id = {$p['id']} ORDER BY id ASC LIMIT 1")->fetchColumn();
                $prodMax = $firstVarMax ? (float)$firstVarMax : (float)($p['wholesale_price'] ?? 0);
                $db->prepare("UPDATE products SET max_amount = ? WHERE id = ?")->execute([$prodMax, $p['id']]);
            }

            Session::setFlash('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error updating category: ' . $e->getMessage());
        }

        $this->redirect($redirectUrl);
    }

    public function delete()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'delete');
        $redirectUrl = '/admin/catalog/categories' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Invalid request method.']);
            }
            $this->redirect($redirectUrl);
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $id = (int)($jsonInput['id'] ?? 0);
        }
        
        if ($id > 0) {
            try {
                $categoryModel = new Category();
                $existingCat = $categoryModel->findById($id);
                if ($existingCat && !empty($existingCat['is_customizable'])) {
                    $isCustomize = true;
                    $redirectUrl = '/admin/catalog/categories?module=customize';
                }

                $counts = $categoryModel->getCounts($id);
                if ($counts['subcategory_count'] > 0 || $counts['attribute_count'] > 0 || $counts['product_count'] > 0) {
                    $reasons = [];
                    if ($counts['subcategory_count'] > 0) $reasons[] = "{$counts['subcategory_count']} sub-category(ies)";
                    if ($counts['attribute_count'] > 0) $reasons[] = "{$counts['attribute_count']} attribute(s)";
                    if ($counts['product_count'] > 0) $reasons[] = "{$counts['product_count']} product(s)";
                    $errMsg = "Cannot delete category: Category is currently linked to " . implode(', ', $reasons) . ". Deletion is restricted to prevent unassigned items.";
                    if ($this->isJsonRequest()) {
                        $this->json(['success' => false, 'message' => $errMsg]);
                        return;
                    }
                    Session::setFlash('error', $errMsg);
                    $this->redirect($redirectUrl);
                    return;
                }

                $categoryModel->delete($id);
                if (class_exists('\Core\Cache')) {
                    \Core\Cache::delete('catalog_active');
                }
                if ($this->isJsonRequest()) {
                    $this->json(['success' => true, 'message' => ($isCustomize ? 'Customize Category' : 'Category') . ' deleted successfully.']);
                    return;
                }
                Session::setFlash('success', ($isCustomize ? 'Customize Category' : 'Category') . ' deleted successfully.');
            } catch (\Exception $e) {
                if ($this->isJsonRequest()) {
                    $this->json(['success' => false, 'message' => $e->getMessage() ?: 'Cannot delete category.']);
                    return;
                }
                Session::setFlash('error', $e->getMessage() ?: 'Cannot delete category.');
            }
        }

        $this->redirect($redirectUrl);
    }

    public function checkDelete()
    {
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_categories' : 'categories', 'delete');
        $id = (int)($_GET['id'] ?? 0);
        
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if ($id > 0) {
            $categoryModel = new Category();
            $counts = $categoryModel->getCounts($id);
            $canDelete = ($counts['product_count'] === 0 && $counts['subcategory_count'] === 0 && ($counts['attribute_count'] ?? 0) === 0);
            echo json_encode([
                'success' => true,
                'can_delete' => $canDelete,
                'count' => $counts['product_count'],
                'product_count' => $counts['product_count'],
                'subcategory_count' => $counts['subcategory_count'],
                'attribute_count' => $counts['attribute_count'] ?? 0
            ]);
            exit;
        }
        
        echo json_encode(['success' => false]);
        exit;
    }

    private function isJsonRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
