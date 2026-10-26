<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\SizeChart;
use App\Models\Category;
use App\Models\SubCategory;
use Core\Session;
use App\Core\CloudinaryUploader;

class SizeChartController extends Controller
{
    private SizeChart $sizeChartModel;
    private Category $categoryModel;
    private SubCategory $subCategoryModel;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->sizeChartModel = new SizeChart();
        $this->categoryModel = new Category();
        $this->subCategoryModel = new SubCategory();
    }

    public function index(): void
    {
        $this->requirePermission('cms_size_charts', 'view');
        $charts = $this->sizeChartModel->getAll();
        $categories = $this->categoryModel->getAll();

        // Process charts for listing & stats
        foreach ($charts as &$chart) {
            $chart['columns'] = json_decode($chart['columns_json'] ?? '[]', true) ?: [];
            $chart['rows'] = json_decode($chart['rows_json'] ?? '[]', true) ?: [];
            $chart['sizes_list'] = array_filter(array_map(function($r) {
                return $r['Size'] ?? ($r['size'] ?? ($r[0] ?? ''));
            }, $chart['rows']));
        }
        unset($chart);

        $this->render('admin/cms/size_charts/index', [
            'title' => 'Size Charts Management',
            'charts' => $charts,
            'categories' => $categories,
            'totalCharts' => count($charts),
            'activeCharts' => count(array_filter($charts, fn($c) => (int)$c['is_active'] === 1))
        ], 'admin');
    }

    public function create(): void
    {
        $this->requirePermission('cms_size_charts', 'create');
        $categories = $this->categoryModel->getAll();
        $subCategories = $this->subCategoryModel->getAll();

        $this->render('admin/cms/size_charts/create', [
            'title' => 'Add New Size Chart',
            'categories' => $categories,
            'subCategories' => $subCategories
        ], 'admin');
    }

    public function store(): void
    {
        $this->requirePermission('cms_size_charts', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/size-charts');
        }

        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            Session::setFlash('error', 'Size chart title is required.');
            $this->redirect('/admin/cms/size-charts/create');
            return;
        }

        $categoryId = (int)($_POST['category_id'] ?? 0);
        $categoryName = trim($_POST['category_name'] ?? '');
        
        if ($categoryId > 0 && empty($categoryName)) {
            $cat = $this->categoryModel->getById($categoryId);
            if ($cat) {
                $categoryName = $cat['name'];
            }
        }

        $subCategoryId = !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null;
        $subCategoryName = trim($_POST['sub_category_name'] ?? '');
        if ($subCategoryId && empty($subCategoryName)) {
            $subCat = $this->subCategoryModel->findById($subCategoryId);
            if ($subCat) {
                $subCategoryName = $subCat['name'];
            }
        }

        $dressType = trim($_POST['dress_type'] ?? '');
        $toleranceNote = trim($_POST['tolerance_note'] ?? 'Size in inches (+ or - 0.5")');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        // Columns Parsing
        $columns = $_POST['columns'] ?? [];
        if (!is_array($columns) || empty($columns)) {
            $columns = ['Size', 'Full Length', 'Bust', 'Sleeve length'];
        }
        $columns = array_values(array_filter(array_map('trim', $columns), fn($c) => $c !== ''));

        // Rows Parsing
        $rowsData = [];
        $sizes = $_POST['row_sizes'] ?? [];
        $cells = $_POST['row_cells'] ?? [];

        if (is_array($sizes) && !empty($sizes)) {
            foreach ($sizes as $rowIndex => $sizeVal) {
                $sizeVal = trim((string)$sizeVal);
                if ($sizeVal === '') continue;

                $rowItem = ['Size' => $sizeVal];
                foreach ($columns as $colIndex => $colName) {
                    if ($colName === 'Size') continue;
                    $rowItem[$colName] = trim((string)($cells[$rowIndex][$colIndex] ?? ($cells[$rowIndex][$colName] ?? '')));
                }
                $rowsData[] = $rowItem;
            }
        }

        // If JSON payload was passed directly via interactive editor
        if (isset($_POST['columns_json_payload']) && !empty($_POST['columns_json_payload'])) {
            $decodedCols = json_decode($_POST['columns_json_payload'], true);
            if (is_array($decodedCols) && !empty($decodedCols)) {
                $columns = array_values(array_filter(array_map('trim', $decodedCols), fn($c) => $c !== ''));
            }
        }
        if (isset($_POST['rows_json_payload']) && !empty($_POST['rows_json_payload'])) {
            $decodedPayload = json_decode($_POST['rows_json_payload'], true);
            if (is_array($decodedPayload) && !empty($decodedPayload)) {
                $filteredRows = array_values(array_filter($decodedPayload, function($r) {
                    if (!is_array($r)) return false;
                    foreach ($r as $v) {
                        if (trim((string)$v) !== '') return true;
                    }
                    return false;
                }));
                if (!empty($filteredRows)) {
                    $rowsData = $filteredRows;
                }
            }
        }

        // Handle image upload via Cloudinary
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new CloudinaryUploader();
            $cloudinaryResponse = $uploader->uploadImage($_FILES['image']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $imageUrl = $cloudinaryResponse['secure_url'];
            } else {
                Session::setFlash('error', 'Failed to upload size chart image to Cloudinary.');
            }
        }

        $this->sizeChartModel->create([
            'title' => $title,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'sub_category_id' => $subCategoryId,
            'sub_category_name' => $subCategoryName,
            'dress_type' => $dressType,
            'image_url' => $imageUrl,
            'tolerance_note' => $toleranceNote,
            'columns_json' => json_encode($columns, JSON_UNESCAPED_UNICODE),
            'rows_json' => json_encode($rowsData, JSON_UNESCAPED_UNICODE),
            'is_active' => $isActive,
            'sort_order' => $sortOrder
        ]);

        Session::setFlash('success', 'Size chart "' . htmlspecialchars($title) . '" created successfully.');
        $this->redirect('/admin/cms/size-charts');
    }

    public function edit(): void
    {
        $this->requirePermission('cms_size_charts', 'edit');
        $id = (int)($_GET['id'] ?? 0);
        $chart = $this->sizeChartModel->getById($id);
        if (!$chart) {
            Session::setFlash('error', 'Size chart not found.');
            $this->redirect('/admin/cms/size-charts');
            return;
        }

        $chart['columns'] = json_decode($chart['columns_json'] ?? '[]', true) ?: [];
        $chart['rows'] = json_decode($chart['rows_json'] ?? '[]', true) ?: [];
        $categories = $this->categoryModel->getAll();
        $subCategories = !empty($chart['category_id']) ? $this->subCategoryModel->getByCategory((int)$chart['category_id']) : $this->subCategoryModel->getAll();

        $this->render('admin/cms/size_charts/edit', [
            'title' => 'Edit Size Chart - ' . htmlspecialchars($chart['title']),
            'chart' => $chart,
            'categories' => $categories,
            'subCategories' => $subCategories
        ], 'admin');
    }

    public function update(): void
    {
        $this->requirePermission('cms_size_charts', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/size-charts');
        }

        $id = (int)($_POST['id'] ?? 0);
        $chart = $this->sizeChartModel->getById($id);
        if (!$chart) {
            Session::setFlash('error', 'Size chart not found.');
            $this->redirect('/admin/cms/size-charts');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            Session::setFlash('error', 'Size chart title is required.');
            $this->redirect('/admin/cms/size-charts/edit?id=' . $id);
            return;
        }

        $categoryId = (int)($_POST['category_id'] ?? 0);
        $categoryName = trim($_POST['category_name'] ?? '');
        
        if ($categoryId > 0 && empty($categoryName)) {
            $cat = $this->categoryModel->getById($categoryId);
            if ($cat) {
                $categoryName = $cat['name'];
            }
        }

        $subCategoryId = !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null;
        $subCategoryName = trim($_POST['sub_category_name'] ?? '');
        if ($subCategoryId && empty($subCategoryName)) {
            $subCat = $this->subCategoryModel->findById($subCategoryId);
            if ($subCat) {
                $subCategoryName = $subCat['name'];
            }
        }

        $dressType = trim($_POST['dress_type'] ?? '');
        $toleranceNote = trim($_POST['tolerance_note'] ?? 'Size in inches (+ or - 0.5")');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        // Columns & Rows Payload
        $columns = $_POST['columns'] ?? [];
        if (!is_array($columns) || empty($columns)) {
            $columns = json_decode($chart['columns_json'], true) ?: ['Size', 'Chest', 'Length'];
        }
        $columns = array_values(array_filter(array_map('trim', $columns), fn($c) => $c !== ''));

        // If JSON payload was passed directly via interactive editor
        if (isset($_POST['columns_json_payload']) && !empty($_POST['columns_json_payload'])) {
            $decodedCols = json_decode($_POST['columns_json_payload'], true);
            if (is_array($decodedCols) && !empty($decodedCols)) {
                $columns = array_values(array_filter(array_map('trim', $decodedCols), fn($c) => $c !== ''));
            }
        }
        if (isset($_POST['rows_json_payload']) && !empty($_POST['rows_json_payload'])) {
            $decodedPayload = json_decode($_POST['rows_json_payload'], true);
            if (is_array($decodedPayload) && !empty($decodedPayload)) {
                $filteredRows = array_values(array_filter($decodedPayload, function($r) {
                    if (!is_array($r)) return false;
                    foreach ($r as $v) {
                        if (trim((string)$v) !== '') return true;
                    }
                    return false;
                }));
                if (!empty($filteredRows)) {
                    $rowsData = $filteredRows;
                }
            }
        }

        if (empty($rowsData)) {
            $sizes = $_POST['row_sizes'] ?? [];
            $cells = $_POST['row_cells'] ?? [];
            if (is_array($sizes) && !empty($sizes)) {
                foreach ($sizes as $rowIndex => $sizeVal) {
                    $sizeVal = trim((string)$sizeVal);
                    if ($sizeVal === '') continue;

                    $rowItem = ['Size' => $sizeVal];
                    foreach ($columns as $colIndex => $colName) {
                        if ($colName === 'Size') continue;
                        $rowItem[$colName] = trim((string)($cells[$rowIndex][$colIndex] ?? ($cells[$rowIndex][$colName] ?? '')));
                    }
                    $rowsData[] = $rowItem;
                }
            }
        }

        // Handle image upload / removal via Cloudinary
        $imageUrl = $chart['image_url'] ?? null;
        if (isset($_POST['remove_image']) && (int)$_POST['remove_image'] === 1) {
            $imageUrl = null;
        }
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new CloudinaryUploader();
            $cloudinaryResponse = $uploader->uploadImage($_FILES['image']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $imageUrl = $cloudinaryResponse['secure_url'];
            } else {
                Session::setFlash('error', 'Failed to upload size chart image to Cloudinary.');
            }
        }

        $this->sizeChartModel->update($id, [
            'title' => $title,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'sub_category_id' => $subCategoryId,
            'sub_category_name' => $subCategoryName,
            'dress_type' => $dressType,
            'image_url' => $imageUrl,
            'tolerance_note' => $toleranceNote,
            'columns_json' => json_encode($columns, JSON_UNESCAPED_UNICODE),
            'rows_json' => json_encode($rowsData, JSON_UNESCAPED_UNICODE),
            'is_active' => $isActive,
            'sort_order' => $sortOrder
        ]);

        Session::setFlash('success', 'Size chart "' . htmlspecialchars($title) . '" updated successfully.');
        $this->redirect('/admin/cms/size-charts');
    }

    public function delete(): void
    {
        $this->requirePermission('cms_size_charts', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/size-charts');
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->sizeChartModel->delete($id);

        Session::setFlash('success', 'Size chart deleted successfully.');
        $this->redirect('/admin/cms/size-charts');
    }

    public function toggleStatus(): void
    {
        $this->requirePermission('cms_size_charts', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/size-charts');
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->sizeChartModel->toggleActive($id);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        Session::setFlash('success', 'Status toggled successfully.');
        $this->redirect('/admin/cms/size-charts');
    }

    public function createCategoryAjax(): void
    {
        $this->requirePermission('cms_size_charts', 'create');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Category name is required.']);
            exit;
        }

        // Generate clean URL slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = trim($slug, '-');
        if (empty($slug)) {
            $slug = 'cat-' . time();
        }

        // Check if category name already exists
        $all = $this->categoryModel->getAll();
        foreach ($all as $c) {
            if (strcasecmp($c['name'], $name) === 0) {
                echo json_encode([
                    'success' => true,
                    'category' => $c,
                    'message' => 'Category already exists.'
                ]);
                exit;
            }
        }

        try {
            $catId = $this->categoryModel->create([
                'name' => $name,
                'slug' => $slug,
                'status' => 'active'
            ]);

            $created = $this->categoryModel->findById($catId);
            if (!$created) {
                $created = ['id' => $catId, 'name' => $name, 'slug' => $slug];
            }

            echo json_encode([
                'success' => true,
                'category' => $created,
                'message' => 'Category created successfully!'
            ]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
