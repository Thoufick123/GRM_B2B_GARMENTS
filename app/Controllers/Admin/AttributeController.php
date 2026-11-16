<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Attribute;
use App\Models\AttributeValue;

use App\Models\Category;
use App\Models\SubCategory;

class AttributeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'view');

        $attributeModel = new Attribute();
        $attributes = $attributeModel->getAll($isCustomize ? 1 : 0);
        
        $attributeValueModel = new AttributeValue();
        foreach ($attributes as &$attr) {
            $attr['values'] = $attributeValueModel->getByAttributeId($attr['id']);
        }

        $this->render('admin/catalog/attributes/index', [
            'title' => $isCustomize ? 'Customize Attributes' : 'Manage Attributes',
            'attributes' => $attributes,
            'module' => $module,
            'isCustomize' => $isCustomize
        ], 'admin');
    }

    public function create()
    {
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'create');

        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, $isCustomize ? 1 : 0);

        $this->render('admin/catalog/attributes/create', [
            'title' => $isCustomize ? 'Add Customize Attribute' : 'Add Attribute',
            'categories' => $categories,
            'module' => $module,
            'isCustomize' => $isCustomize
        ], 'admin');
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'edit');

        $attributeModel = new Attribute();
        $attribute = $attributeModel->findById($id);

        if (!$attribute) {
            Session::setFlash('error', 'Attribute not found.');
            $this->redirect('/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : ''));
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, $isCustomize ? 1 : 0);

        $subCategoryModel = new SubCategory();
        $subCategories = [];
        if ($attribute['category_id']) {
            $subCategories = $subCategoryModel->getByCategory($attribute['category_id']);
        }

        $attributeValueModel = new AttributeValue();
        $attributeValues = $attributeValueModel->getByAttributeId($id);

        $this->render('admin/catalog/attributes/edit', [
            'title' => $isCustomize ? 'Edit Customize Attribute' : 'Edit Attribute',
            'attribute' => $attribute,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'values' => $attributeValues,
            'module' => $module,
            'isCustomize' => $isCustomize
        ], 'admin');
    }

    public function store()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'create');
        $isJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $name = trim($_POST['name'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $subCategoryId = !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null;
        $inputType = trim($_POST['input_type'] ?? 'checkbox');
        $code = trim($_POST['attribute_code'] ?? '');

        if (empty($name) || empty($categoryId)) {
            $msg = 'Category and Attribute Name are required.';
            if ($isJson) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
            $this->redirect('/admin/catalog/attributes/create');
            return;
        }

        $db = \Core\Database::getInstance();
        $description = trim($_POST['description'] ?? '');

        // Handle values (supports both array and comma-separated string)
        $rawValues = $_POST['values'] ?? [];
        $colorCodes = $_POST['color_codes'] ?? [];
        
        if (is_string($rawValues)) {
            $values = array_filter(array_map('trim', explode(',', $rawValues)), fn($v) => $v !== '');
        } else {
            $values = (array)$rawValues;
        }

        // Check if an attribute with the same name already exists in this category
        if ($subCategoryId) {
            $stmtExists = $db->prepare("
                SELECT * FROM attributes 
                WHERE category_id = ? 
                  AND (sub_category_id = ? OR sub_category_id IS NULL)
                  AND LOWER(TRIM(name)) = LOWER(TRIM(?))
                LIMIT 1
            ");
            $stmtExists->execute([$categoryId, $subCategoryId, $name]);
        } else {
            $stmtExists = $db->prepare("
                SELECT * FROM attributes 
                WHERE category_id = ? 
                  AND LOWER(TRIM(name)) = LOWER(TRIM(?))
                LIMIT 1
            ");
            $stmtExists->execute([$categoryId, $name]);
        }
        $existingAttr = $stmtExists->fetch(\PDO::FETCH_ASSOC);

        if ($existingAttr) {
            $attributeId = (int)$existingAttr['id'];

            // Update description if newly provided
            if (!empty($description) && (empty($existingAttr['description']) || $existingAttr['description'] !== $description)) {
                $db->prepare("UPDATE attributes SET description = ? WHERE id = ?")->execute([$description, $attributeId]);
            }

            // Append new values under existing attribute without duplicates
            if (!empty($values)) {
                $existingValsStmt = $db->prepare("SELECT LOWER(TRIM(value)) FROM attribute_values WHERE attribute_id = ?");
                $existingValsStmt->execute([$attributeId]);
                $existingValList = $existingValsStmt->fetchAll(\PDO::FETCH_COLUMN);

                $attributeValueModel = new AttributeValue();
                $idx = 0;
                foreach ($values as $valKey => $val) {
                    $val = trim((string)$val);
                    if ($val === '') continue;

                    if (!in_array(strtolower($val), $existingValList)) {
                        $colorCode = isset($colorCodes[$valKey]) ? trim($colorCodes[$valKey]) : (isset($colorCodes[$idx]) ? trim($colorCodes[$idx]) : null);
                        $attributeValueModel->create([
                            'attribute_id' => $attributeId,
                            'value' => $val,
                            'color_code' => !empty($colorCode) ? $colorCode : null
                        ]);
                        $existingValList[] = strtolower($val);
                    }
                    $idx++;
                }
            }

            $successMsg = "Added new options to existing '{$name}' attribute!";
            if ($isJson) {
                echo json_encode([
                    'success' => true,
                    'message' => $successMsg,
                    'attribute_id' => $attributeId,
                    'name' => $name,
                    'merged' => true
                ]);
                exit;
            }

            Session::setFlash('success', $successMsg);
            $redirectUrl = '/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : '');
            $this->redirect($redirectUrl);
            return;
        }

        // Auto-generate attribute_code if empty
        if (empty($code)) {
            $code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
            $code = trim($code, '_');
            if (empty($code)) $code = 'attr_' . time();
        }

        // Ensure unique attribute code
        $stmtCode = $db->prepare("SELECT COUNT(*) FROM attributes WHERE attribute_code = ?");
        $stmtCode->execute([$code]);
        if ($stmtCode->fetchColumn() > 0) {
            $code .= '_' . substr(uniqid(), -4);
        }

        $data = [
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'name' => $name,
            'attribute_code' => $code,
            'input_type' => $inputType,
            'description' => $description,
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_searchable' => isset($_POST['is_searchable']) ? 1 : 0,
            'is_filterable' => isset($_POST['is_filterable']) ? 1 : 0,
            'is_variant' => isset($_POST['is_variant']) ? 1 : 0,
            'show_on_product_form' => isset($_POST['show_on_product_form']) ? 1 : 1,
            'is_visible_on_website' => isset($_POST['is_visible_on_website']) ? 1 : 1
        ];

        try {
            $attributeModel = new Attribute();
            $attributeId = $attributeModel->create($data);

            if (!empty($values)) {
                $attributeValueModel = new AttributeValue();
                $idx = 0;
                foreach ($values as $valKey => $val) {
                    $val = trim((string)$val);
                    if ($val === '') continue;
                    
                    $colorCode = isset($colorCodes[$valKey]) ? trim($colorCodes[$valKey]) : (isset($colorCodes[$idx]) ? trim($colorCodes[$idx]) : null);
                    $attributeValueModel->create([
                        'attribute_id' => $attributeId,
                        'value' => $val,
                        'color_code' => !empty($colorCode) ? $colorCode : null
                    ]);
                    $idx++;
                }
            }

            $successMsg = "Attribute '{$name}' created successfully!";
            if ($isJson) {
                echo json_encode([
                    'success' => true,
                    'message' => $successMsg,
                    'attribute_id' => $attributeId,
                    'name' => $name
                ]);
                exit;
            }

            $module = $_POST['module'] ?? ($_GET['module'] ?? '');
            $isCustomize = ($module === 'customize');
            Session::setFlash('success', $successMsg);
            $this->redirect('/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : ''));
        } catch (\Exception $e) {
            $errMsg = 'Error creating attribute: ' . $e->getMessage();
            if ($isJson) {
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            $module = $_POST['module'] ?? ($_GET['module'] ?? '');
            $isCustomize = ($module === 'customize');
            Session::setFlash('error', $errMsg);
            $this->redirect('/admin/catalog/attributes/create' . ($isCustomize ? '?module=customize' : ''));
        }
    }

    public function update()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'edit');
        $redirectListUrl = '/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectListUrl);
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'sub_category_id' => !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null,
            'name' => trim($_POST['name'] ?? ''),
            'attribute_code' => trim($_POST['attribute_code'] ?? ''),
            'input_type' => trim($_POST['input_type'] ?? 'textbox'),
            'description' => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_searchable' => isset($_POST['is_searchable']) ? 1 : 0,
            'is_filterable' => isset($_POST['is_filterable']) ? 1 : 0,
            'is_variant' => isset($_POST['is_variant']) ? 1 : 0,
            'show_on_product_form' => isset($_POST['show_on_product_form']) ? 1 : 0,
            'is_visible_on_website' => isset($_POST['is_visible_on_website']) ? 1 : 0
        ];

        if ($id <= 0 || empty($data['name']) || empty($data['attribute_code']) || empty($data['category_id'])) {
            Session::setFlash('error', 'Invalid input or missing required fields.');
            $this->redirect('/admin/catalog/attributes/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
        }

        try {
            $attributeModel = new Attribute();
            $attributeModel->update($id, $data);

            // Handle values sync
            $rawValues = $_POST['values'] ?? [];
            $rawColorCodes = $_POST['color_codes'] ?? [];
            $submittedItems = [];
            foreach ($rawValues as $idx => $v) {
                $v = trim($v);
                $c = isset($rawColorCodes[$idx]) ? trim($rawColorCodes[$idx]) : null;
                if ($v !== '') {
                    $submittedItems[] = [
                        'value' => $v,
                        'color_code' => $c
                    ];
                }
            }

            $attributeValueModel = new AttributeValue();
            $existing = $attributeValueModel->getByAttributeId($id);

            // Delete values that were removed by the user
            $submittedValueNames = array_column($submittedItems, 'value');
            foreach ($existing as $exist) {
                if (!in_array($exist['value'], $submittedValueNames)) {
                    $attributeValueModel->delete((int)$exist['id']);
                }
            }

            // Add or update values
            $existingMap = [];
            foreach ($existing as $exist) {
                $existingMap[$exist['value']] = $exist;
            }

            foreach ($submittedItems as $item) {
                if (isset($existingMap[$item['value']])) {
                    $existId = (int)$existingMap[$item['value']]['id'];
                    $attributeValueModel->update($existId, [
                        'value' => $item['value'],
                        'color_code' => $item['color_code']
                    ]);
                } else {
                    $attributeValueModel->create([
                        'attribute_id' => $id,
                        'value' => $item['value'],
                        'color_code' => $item['color_code']
                    ]);
                }
            }

            Session::setFlash('success', 'Attribute updated successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error updating attribute: ' . $e->getMessage());
            $this->redirect('/admin/catalog/attributes/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
        }

        $this->redirect($redirectListUrl);
    }

    public function delete()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'delete');
        $redirectListUrl = '/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Invalid request method.']);
            }
            $this->redirect($redirectListUrl);
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $id = (int)($jsonInput['id'] ?? 0);
        }
        
        if ($id > 0) {
            try {
                $db = \Core\Database::getInstance();
                $db->beginTransaction();

                $db->exec("SET FOREIGN_KEY_CHECKS=0");

                // Clean up references in product tables
                $db->prepare("DELETE FROM product_variant_attributes WHERE attribute_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM product_attribute_values WHERE attribute_id = ?")->execute([$id]);

                // Delete associated attribute values first
                $stmtVal = $db->prepare("DELETE FROM attribute_values WHERE attribute_id = ?");
                $stmtVal->execute([$id]);

                // Delete attribute
                $attributeModel = new Attribute();
                $attributeModel->delete($id);

                $db->exec("SET FOREIGN_KEY_CHECKS=1");

                $db->commit();
                if (class_exists('\Core\Cache')) {
                    \Core\Cache::delete('catalog_active');
                }
                if ($this->isJsonRequest()) {
                    $this->json(['success' => true, 'message' => 'Attribute deleted successfully.']);
                }
                Session::setFlash('success', 'Attribute deleted successfully.');
            } catch (\Exception $e) {
                if (isset($db) && $db->inTransaction()) {
                    $db->exec("SET FOREIGN_KEY_CHECKS=1");
                    $db->rollBack();
                }
                if ($this->isJsonRequest()) {
                    $this->json(['success' => false, 'message' => 'Error deleting attribute: ' . $e->getMessage()]);
                }
                Session::setFlash('error', 'Error deleting attribute: ' . $e->getMessage());
            }
        }

        $this->redirect($redirectListUrl);
    }

    public function storeValue()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : ''));
        }

        $attributeId = (int)($_POST['attribute_id'] ?? 0);
        $value = trim($_POST['value'] ?? '');

        if ($attributeId <= 0 || empty($value)) {
            Session::setFlash('error', 'Attribute and Value are required.');
            $this->redirect('/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : ''));
        }

        try {
            $attributeValueModel = new AttributeValue();
            $attributeValueModel->create(['attribute_id' => $attributeId, 'value' => $value]);
            Session::setFlash('success', 'Attribute value added successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error adding value: ' . $e->getMessage());
        }

        $this->redirect('/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : ''));
    }

    public function deleteValue()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_attributes' : 'attributes', 'delete');
        $redirectUrl = '/admin/catalog/attributes' . ($isCustomize ? '?module=customize' : '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectUrl);
        }

        $id = (int)($_POST['id'] ?? 0);
        $isJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($id > 0) {
            try {
                $attributeValueModel = new AttributeValue();
                $attributeValueModel->delete($id);
                if ($isJson) {
                    echo json_encode(['success' => true]);
                    exit;
                }
                Session::setFlash('success', 'Attribute value deleted successfully.');
            } catch (\Exception $e) {
                if ($isJson) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                Session::setFlash('error', 'Cannot delete attribute value: ' . $e->getMessage());
            }
        }

        $this->redirect($redirectUrl);
    }

    public function ajaxGetForCategory()
    {
        header('Content-Type: application/json');
        
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $subCategoryId = isset($_GET['sub_category_id']) ? (int)$_GET['sub_category_id'] : 0;
        
        if ($categoryId <= 0) {
            echo json_encode([]);
            exit;
        }

        $db = \Core\Database::getInstance();
        
        if ($subCategoryId > 0) {
            $sql = "SELECT * FROM attributes 
                    WHERE status = 'active' 
                    AND category_id = :category_id 
                    AND (sub_category_id IS NULL OR sub_category_id = :sub_category_id)
                    ORDER BY display_order ASC, name ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'category_id' => $categoryId,
                'sub_category_id' => $subCategoryId
            ]);
        } else {
            $sql = "SELECT * FROM attributes 
                    WHERE status = 'active' 
                    AND category_id = :category_id 
                    ORDER BY display_order ASC, name ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'category_id' => $categoryId
            ]);
        }
        $attributes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $attributeValueModel = new AttributeValue();
        $mergedAttributes = [];
        $nameIndex = [];

        foreach ($attributes as $attr) {
            $attrKey = strtolower(trim($attr['name']));
            $attrValues = $attributeValueModel->getByAttributeId($attr['id']);

            if (isset($nameIndex[$attrKey])) {
                // Merge options into already registered attribute
                $existingIdx = $nameIndex[$attrKey];
                $existingValTexts = array_map(fn($v) => strtolower(trim($v['value'])), $mergedAttributes[$existingIdx]['values']);

                foreach ($attrValues as $val) {
                    $valLower = strtolower(trim($val['value']));
                    if (!in_array($valLower, $existingValTexts)) {
                        $mergedAttributes[$existingIdx]['values'][] = $val;
                        $existingValTexts[] = $valLower;
                    }
                }

                // Keep description if original was empty
                if (empty($mergedAttributes[$existingIdx]['description']) && !empty($attr['description'])) {
                    $mergedAttributes[$existingIdx]['description'] = $attr['description'];
                }
            } else {
                $attr['values'] = $attrValues;
                $nameIndex[$attrKey] = count($mergedAttributes);
                $mergedAttributes[] = $attr;
            }
        }

        echo json_encode($mergedAttributes);
        exit;
    }

    private function isJsonRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
