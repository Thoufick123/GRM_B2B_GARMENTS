<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AttributeValue;
use App\Models\Attribute;
use App\Models\ProductVariant;

class ProductController extends Controller
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
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'view');
        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : (!empty($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : null);
        $status = $_GET['status'] ?? 'all';

        $productModel = new Product();
        $products = $productModel->getAllAdmin($vendorId, $status, $isCustomize ? 1 : 0);
        $pendingCount = $productModel->getPendingApprovalCount($vendorId);

        if (!empty($products)) {
            $productIds = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            
            $db = \Core\Database::getInstance();
            $stmt = $db->prepare("SELECT product_id, name, sku, base_price, discount_price, inventory, current_stock, sgst, cgst, total_gst, max_amount, base_quantity, max_quantity, fabric_consumption FROM product_variants WHERE product_id IN ($placeholders) ORDER BY id ASC");
            $stmt->execute($productIds);
            $allVariants = $stmt->fetchAll();
            
            $variantsByProduct = [];
            foreach ($allVariants as $v) {
                $variantsByProduct[$v['product_id']][] = $v;
            }
            
            foreach ($products as &$prod) {
                $prod['variants'] = $variantsByProduct[$prod['id']] ?? [];
            }
        }
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, $isCustomize ? 1 : 0);

        $subCategoryModel = new SubCategory();
        $subCategories = $subCategoryModel->getAll($isCustomize ? 1 : 0);

        $this->render('admin/catalog/products/index', [
            'title' => ($module === 'customize') ? 'Customizable Products & Fabrics' : ($isVendor ? 'My Products' : 'Manage Products'),
            'products' => $products,
            'isVendor' => $isVendor,
            'currentStatus' => $status,
            'pendingCount' => $pendingCount,
            'module' => $module,
            'isCustomize' => $isCustomize
        ], 'admin');
    }

    public function create()
    {
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'create');
        $isVendor = (Session::get('user_role') === 'vendor');

        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, $isCustomize ? 1 : 0);

        $sizeChartModel = new \App\Models\SizeChart();
        $sizeCharts = $sizeChartModel->getAllActive();

        $attrValueModel = new AttributeValue();
        $attributes = $attrValueModel->getAllGroupedByAttribute();

        $vendors = [];
        if (!$isVendor && is_vendor_module_enabled()) {
            $userModel = new \App\Models\User();
            $vendors = $userModel->getActiveVendors();
        }

        $fabricRule = [
            'minimum_pieces' => 10,
            'maximum_pieces' => 100,
            'without_cover_discount_percentage' => 0.00,
            'delivery_days' => 7,
            'delivery_base_pieces' => 100,
            'status' => 'active'
        ];
        $customizationLabelModel = new \App\Models\CustomizationLabel();
        $customizationLabels = $customizationLabelModel->getAllActive();
        $selectedLabelIds = [];
        $allLabels = $customizationLabels;

        $this->render('admin/catalog/products/create', [
            'title' => ($module === 'customize') ? 'Add Customizable Product / Fabric' : 'Add Product',
            'categories' => $categories,
            'sizeCharts' => $sizeCharts,
            'attributes' => $attributes,
            'isVendor' => $isVendor,
            'vendors' => $vendors,
            'module' => $module,
            'isCustomize' => $isCustomize,
            'fabricRule' => $fabricRule,
            'customizationLabels' => $customizationLabels,
            'selectedLabelIds' => $selectedLabelIds,
            'allLabels' => $allLabels
        ], 'admin');
    }

    public function view()
    {
        $id = $_GET['id'] ?? null;
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'view');
        if (!$id) {
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
        }

        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        if ($isVendor && (int)($product['vendor_id'] ?? 0) !== (int)Session::get('user_id')) {
            Session::setFlash('error', 'You do not have permission to view this product.');
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
            return;
        }

        // Get Category and SubCategory details
        $categoryModel = new Category();
        $category = !empty($product['category_id']) ? $categoryModel->findById($product['category_id']) : null;
        
        $subCategory = null;
        if (!empty($product['sub_category_id'])) {
            $subCategoryModel = new SubCategory();
            $subCategory = $subCategoryModel->findById($product['sub_category_id']);
        }

        // Get Variants
        $variantModel = new ProductVariant();
        $variants = $variantModel->getByProductId($id);

        // Get Images
        $images = $productModel->getImages($id);

        $sizeChart = null;
        if (!empty($product['size_chart_id'])) {
            $sizeChartModel = new \App\Models\SizeChart();
            $sizeChart = $sizeChartModel->getById((int)$product['size_chart_id']);
        }

        $this->render('admin/catalog/products/view', [
            'product' => $product,
            'category' => $category,
            'subCategory' => $subCategory,
            'sizeChart' => $sizeChart,
            'variants' => $variants,
            'images' => $images,
            'isVendor' => $isVendor,
            'module' => $module,
            'isCustomize' => $isCustomize
        ], 'admin');
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productModel = new Product();
        $product = $productModel->findById($id);
        
        $module = $_GET['module'] ?? '';
        $isCustomize = ($module === 'customize');

        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'edit');

        if (!$product) {
            Session::setFlash('error', 'Product not found.');
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        if ($isVendor && (int)($product['vendor_id'] ?? 0) !== (int)Session::get('user_id')) {
            Session::setFlash('error', 'You do not have permission to edit this product.');
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
            return;
        }


        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, $isCustomize ? 1 : 0);

        $sizeChartModel = new \App\Models\SizeChart();
        $sizeCharts = $sizeChartModel->getAllActive();

        $subCategoryModel = new SubCategory();
        $subCategories = !empty($product['category_id']) ? $subCategoryModel->getByCategory($product['category_id']) : [];

        $attrValueModel = new AttributeValue();
        $attributes = $attrValueModel->getAllGroupedByAttribute();

        $selectedAttributes = $productModel->getAttributes($product['id']);

        $variantModel = new ProductVariant();
        $variants = $variantModel->getByProductId($product['id']);
        
        $productImages = $productModel->getImages($product['id']);

        $vendors = [];
        if (!$isVendor && is_vendor_module_enabled()) {
            $userModel = new \App\Models\User();
            $vendors = $userModel->getActiveVendors();
        }

        $fabricRule = null;
        $selectedLabelIds = [];
        $customizationLabelModel = new \App\Models\CustomizationLabel();
        $customizationLabels = $customizationLabelModel->getAllActive();

        if (!empty($product['id'])) {
            $db = \Core\Database::getInstance();
            $stmtFR = $db->prepare("SELECT * FROM fabric_customizations WHERE fabric_id = ? LIMIT 1");
            $stmtFR->execute([(int)$product['id']]);
            $fabricRule = $stmtFR->fetch(\PDO::FETCH_ASSOC);

            if ($fabricRule) {
                $assignedLabels = $customizationLabelModel->getLabelsByCustomizationId($fabricRule['id']);
                $selectedLabelIds = array_column($assignedLabels, 'id');
            }
        }
        if (!$fabricRule) {
            $fabricRule = [
                'minimum_pieces' => 10,
                'maximum_pieces' => 100,
                'without_cover_discount_percentage' => 0.00,
                'delivery_days' => 7,
                'delivery_base_pieces' => 100,
                'status' => 'active'
            ];
        }

        $allLabels = $customizationLabels;
        $assignedLabelIds = $selectedLabelIds;
        $customizationSizes = [];
        $customizationRule = $fabricRule;

        $this->render('admin/catalog/products/edit', [
            'title' => ($isCustomize) ? 'Edit Customizable Product / Fabric' : 'Edit Product',
            'product' => $product,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'sizeCharts' => $sizeCharts,
            'attributes' => $attributes,
            'selectedAttributes' => $selectedAttributes,
            'variants' => $variants,
            'productImages' => $productImages,
            'isVendor' => $isVendor,
            'vendors' => $vendors,
            'module' => $module,
            'isCustomize' => $isCustomize,
            'fabricRule' => $fabricRule,
            'customizationLabels' => $customizationLabels,
            'selectedLabelIds' => $selectedLabelIds,
            'allLabels' => $allLabels,
            'assignedLabelIds' => $assignedLabelIds,
            'customizationSizes' => $customizationSizes,
            'customizationRule' => $customizationRule
        ], 'admin');
    }

    public function store()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        $vendorId = $isVendor ? (int)Session::get('user_id') : (!empty($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : null);

        $postedVariants = !empty($_POST['variants']) ? $_POST['variants'] : (!empty($_POST['new_variants']) ? $_POST['new_variants'] : []);

        if (empty($postedVariants)) {
            Session::setFlash('error', 'Please generate or configure at least one variant with price' . (!$isCustomize ? ', weight,' : '') . ' and stock.');
            $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
        }

        $firstVariant = reset($postedVariants);
        $firstVariantBasePrice = (float)($firstVariant['base_price'] ?? 0);
        $firstVariantDiscountPrice = (isset($firstVariant['discount_price']) && $firstVariant['discount_price'] !== '') ? (float)$firstVariant['discount_price'] : null;
        $firstVariantEffectivePrice = ($firstVariantDiscountPrice !== null && $firstVariantDiscountPrice > 0) ? $firstVariantDiscountPrice : $firstVariantBasePrice;
        $firstVariantWeight = !empty($firstVariant['weight']) ? (float)$firstVariant['weight'] : null;
        $firstVariantSgst = isset($firstVariant['sgst']) && $firstVariant['sgst'] !== '' ? (float)$firstVariant['sgst'] : 0.00;
        $firstVariantCgst = isset($firstVariant['cgst']) && $firstVariant['cgst'] !== '' ? (float)$firstVariant['cgst'] : 0.00;
        $firstVariantTotalGst = isset($firstVariant['total_gst']) && $firstVariant['total_gst'] !== '' ? (float)$firstVariant['total_gst'] : ($firstVariantSgst + $firstVariantCgst);
        $firstVariantMaxAmount = isset($firstVariant['max_amount']) && $firstVariant['max_amount'] !== '' ? (float)$firstVariant['max_amount'] : null;

        $approvalStatus = $isVendor ? 'pending' : 'approved';
        $status = $isVendor ? 'draft' : ($_POST['status'] ?? 'active');

        // Extract custom description fields
        $customFields = [];
        if (!empty($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
            foreach ($_POST['custom_fields'] as $cf) {
                $fName = trim($cf['name'] ?? '');
                $fVal = trim($cf['value'] ?? '');
                if ($fName !== '' || $fVal !== '') {
                    $customFields[] = [
                        'name' => $fName,
                        'value' => $fVal
                    ];
                }
            }
        }
        $customFieldsJson = !empty($customFields) ? json_encode($customFields, JSON_UNESCAPED_UNICODE) : null;

        $data = [
            'vendor_id' => $vendorId,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'sub_category_id' => !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null,
            'size_chart_id' => !empty($_POST['size_chart_id']) ? (int)$_POST['size_chart_id'] : null,
            'is_customizable' => $isCustomize ? 1 : 0,
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'how_to_use' => !empty($_POST['how_to_use']) ? trim($_POST['how_to_use']) : null,
            'why_choose' => !empty($_POST['why_choose']) ? trim($_POST['why_choose']) : null,
            'custom_fields' => $customFieldsJson,
            'wholesale_price' => $firstVariantEffectivePrice,
            'retail_price' => $firstVariantBasePrice,
            'weight' => $firstVariantWeight,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'moq_override' => (!$isCustomize && !empty($_POST['moq_override'])) ? (int)$_POST['moq_override'] : null,
            'status' => $status,
            'approval_status' => $approvalStatus,
            'rejection_reason' => null,
            'sgst' => $firstVariantSgst,
            'cgst' => $firstVariantCgst,
            'total_gst' => $firstVariantTotalGst,
            'max_amount' => $firstVariantMaxAmount
        ];

        if ($data['category_id'] <= 0 || empty($data['name']) || empty($data['slug']) || empty($data['description']) || (!$isCustomize && empty($data['moq_override']))) {
            Session::setFlash('error', 'Category, ' . ($isCustomize ? 'Fabric Name' : 'Product Name') . ', Slug, and Description' . (!$isCustomize ? ', and MOQ' : '') . ' are all required.');
            $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
        }

        // Validate variants price, discount price, and weight
        foreach ($postedVariants as $vData) {
            $basePrice = isset($vData['base_price']) && $vData['base_price'] !== '' ? (float)$vData['base_price'] : 0;
            $discountPrice = (isset($vData['discount_price']) && $vData['discount_price'] !== '' && (float)$vData['discount_price'] > 0) ? (float)$vData['discount_price'] : null;
            $weight = isset($vData['weight']) && $vData['weight'] !== '' ? (float)$vData['weight'] : 0;
            $sku = trim($vData['sku'] ?? '');

            if ($basePrice <= 0) {
                Session::setFlash('error', 'Base Price is required and must be greater than 0 for all variants.');
                $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
                return;
            }

            if ($discountPrice !== null && $discountPrice >= $basePrice) {
                Session::setFlash('error', 'Discount Price (₹' . number_format($discountPrice, 2) . ') must be less than Base Price (₹' . number_format($basePrice, 2) . ') for all variants.');
                $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
                return;
            }

            if (!$isCustomize && $weight <= 0) {
                Session::setFlash('error', 'Weight is required and must be greater than 0 kg for all variants.');
                $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
                return;
            }

            if (empty($sku)) {
                Session::setFlash('error', 'Variant SKU is required and cannot be empty.');
                $this->redirect('/admin/catalog/products/create' . ($isCustomize ? '?module=customize' : ''));
                return;
            }
        }

        try {
            $db = \Core\Database::getInstance();
            $db->beginTransaction();

            // --- SKU Auto-Disambiguation for Store ---
            $skus = [];
            foreach ($postedVariants as $vKey => &$vData) {
                $sku = trim($vData['sku'] ?? '');
                if (empty($sku)) {
                    $baseSkuSlug = !empty($data['slug']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['slug']), 0, 6)) : 'PRD';
                    $sku = $baseSkuSlug . '-' . ($vKey + 1);
                }
                $sku = $this->ensureUniqueSku($sku, 0, $skus);
                $skus[] = $sku;
                $vData['sku'] = $sku;
                if (isset($_POST['variants'][$vKey])) {
                    $_POST['variants'][$vKey]['sku'] = $sku;
                }
                if (isset($_POST['new_variants'][$vKey])) {
                    $_POST['new_variants'][$vKey]['sku'] = $sku;
                }
            }
            unset($vData);

            $productModel = new Product();
            
            // Check for duplicate slug - automatically increment to make unique
            if ($productModel->findBySlug($data['slug'])) {
                $origSlug = $data['slug'];
                $slugCounter = 2;
                while ($productModel->findBySlug($origSlug . '-' . $slugCounter)) {
                    $slugCounter++;
                }
                $data['slug'] = $origSlug . '-' . $slugCounter;
            }

            $productId = $productModel->create($data);
            
            // Sync attributes
            $attributeValues = $_POST['attributes'] ?? [];
            $parsedAttributes = $this->parsePostedAttributes($attributeValues);
            $productModel->syncAttributes($productId, $parsedAttributes);
            
            // Process Variants
            $variantModel = new ProductVariant();
            $postedVariants = !empty($_POST['variants']) ? $_POST['variants'] : (!empty($_POST['new_variants']) ? $_POST['new_variants'] : []);
            
            // Purge any orphan/stale variants for this productId before inserting
            $variantModel->deleteByProductId($productId);

            if (empty($postedVariants)) {
                // Insert a default variant for inventory tracking if no variants were provided
                $variantModel->create([
                    'product_id' => $productId,
                    'name' => 'Default',
                    'sku' => $data['slug'],
                    'inventory' => 0,
                    'base_price' => $data['wholesale_price']
                ]);
            } else {
                $firstVariantImage = null;
                $fileGroup = !empty($_POST['variants']) ? 'variants' : 'new_variants';
                foreach ($postedVariants as $key => $vData) {
                    $vData['product_id'] = $productId;
                    if ($isCustomize) {
                        $vData['inventory'] = (int)($_POST['stock_quantity'] ?? 0);
                        $vData['current_stock'] = (int)($_POST['stock_quantity'] ?? 0);
                    }
                    $variantId = $variantModel->create($vData);
                    
                    if (!empty($vData['attributes']) && is_array($vData['attributes'])) {
                        $variantModel->syncAttributes($variantId, $vData['attributes']);
                    }
                    if (!empty($vData['image_paths']) && is_array($vData['image_paths'])) {
                        $uniqueImgs = array_values(array_unique(array_filter($vData['image_paths'])));
                        foreach ($uniqueImgs as $imgUrl) {
                            if (!empty($imgUrl)) {
                                $productModel->addImage($productId, $imgUrl, false, $variantId);
                                if (!$firstVariantImage) {
                                    $firstVariantImage = $imgUrl;
                                }
                            }
                        }
                    }
                }
            }

            // Handle Primary Image (with fallback to first variant image if primary not specified)
            $primaryImgPath = !empty($_POST['primary_image_path']) ? trim($_POST['primary_image_path']) : $firstVariantImage;
            if (!empty($primaryImgPath)) {
                $productModel->addImage($productId, $primaryImgPath, true, null, 0);
            }

            // Handle Additional Gallery Images (up to 4 gallery images = total 5 with primary)
            if (!empty($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
                $sort = 1;
                foreach ($_POST['gallery_images'] as $gImg) {
                    $gPath = trim($gImg);
                    if (!empty($gPath) && $gPath !== $primaryImgPath && $sort <= 4) {
                        $productModel->addImage($productId, $gPath, false, null, $sort);
                        $sort++;
                    }
                }
            }

            // Handle Fabric Customization Rules, Labels & Sizes when module is customize
            if ($isCustomize) {
                $minPieces = isset($_POST['minimum_pieces']) ? max(1, (int)$_POST['minimum_pieces']) : 10;
                $maxPieces = isset($_POST['maximum_pieces']) ? max(1, (int)$_POST['maximum_pieces']) : 100;
                $coverDiscount = isset($_POST['without_cover_discount_percentage']) ? (float)$_POST['without_cover_discount_percentage'] : 0.00;
                $deliveryDays = isset($_POST['delivery_days']) ? max(1, (int)$_POST['delivery_days']) : 7;
                $deliveryBasePieces = isset($_POST['delivery_base_pieces']) ? max(1, (int)$_POST['delivery_base_pieces']) : 100;
                $ruleStatus = $_POST['rule_status'] ?? ($_POST['customization_status'] ?? 'active');

                // Collect label IDs from both potential input names
                $labelIds = [];
                if (!empty($_POST['customization_labels']) && is_array($_POST['customization_labels'])) {
                    $labelIds = array_map('intval', $_POST['customization_labels']);
                } elseif (!empty($_POST['label_ids']) && is_array($_POST['label_ids'])) {
                    $labelIds = array_map('intval', $_POST['label_ids']);
                }

                // Build variant sizes from posted fabric_sizes or fallback to posted variants
                $variantSizes = [];
                $sort = 1;
                if (!empty($_POST['fabric_sizes']) && is_array($_POST['fabric_sizes'])) {
                    foreach ($_POST['fabric_sizes'] as $sData) {
                        $sName = trim($sData['size_name'] ?? '');
                        if ($sName !== '') {
                            $variantSizes[] = [
                                'size_name' => $sName,
                                'fabric_consumption' => !empty($sData['fabric_consumption']) ? (float)$sData['fabric_consumption'] : 1.00,
                                'base_quantity' => isset($sData['base_quantity']) ? max(0, (int)$sData['base_quantity']) : 2,
                                'max_quantity' => isset($sData['max_quantity']) ? max(1, (int)$sData['max_quantity']) : 4,
                                'enabled' => isset($sData['enabled']) ? (int)$sData['enabled'] : 1,
                                'sort_order' => $sort++
                            ];
                        }
                    }
                } elseif (!empty($postedVariants)) {
                    foreach ($postedVariants as $vData) {
                        $vName = trim($vData['name'] ?? '');
                        if ($vName !== '') {
                            $variantSizes[] = [
                                'size_name' => $vName,
                                'fabric_consumption' => !empty($vData['fabric_consumption']) ? (float)$vData['fabric_consumption'] : 1.00,
                                'base_quantity' => isset($vData['base_quantity']) && $vData['base_quantity'] !== '' ? max(1, (int)$vData['base_quantity']) : 2,
                                'max_quantity' => isset($vData['max_quantity']) && $vData['max_quantity'] !== '' ? max(1, (int)$vData['max_quantity']) : 4,
                                'enabled' => 1,
                                'sort_order' => $sort++
                            ];
                        }
                    }
                }
                if (empty($variantSizes)) {
                    $variantSizes = [
                        ['size_name' => 'M', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 1],
                        ['size_name' => 'L', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 2],
                        ['size_name' => 'XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 3],
                        ['size_name' => 'XXL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 4],
                        ['size_name' => '3XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 5],
                        ['size_name' => '4XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 6],
                    ];
                }

                $fcModel = new \App\Models\FabricCustomization();
                $stmt = $db->prepare("SELECT id FROM fabric_customizations WHERE fabric_id = ? LIMIT 1");
                $stmt->execute([$productId]);
                $existingFc = $stmt->fetch();

                $fcData = [
                    'fabric_id' => $productId,
                    'program_name' => $data['name'] . ' Rule',
                    'garment_type' => 'Custom Garment',
                    'category_id' => $data['category_id'],
                    'sub_category_id' => $data['sub_category_id'] ?? null,
                    'feeding_type' => 'Maternity / Non-Feeding',
                    'minimum_pieces' => $minPieces,
                    'required_pieces' => $minPieces,
                    'maximum_pieces' => $maxPieces,
                    'without_cover_discount_percentage' => $coverDiscount,
                    'delivery_days' => $deliveryDays,
                    'delivery_base_pieces' => $deliveryBasePieces,
                    'wastage_percentage' => 0.00,
                    'unit' => 'Quantity',
                    'customization_enabled' => 1,
                    'status' => $ruleStatus
                ];

                if ($existingFc) {
                    $fcModel->update($existingFc['id'], $fcData, $variantSizes, $labelIds);
                    $ruleId = (int)$existingFc['id'];
                } else {
                    $ruleId = $fcModel->create($fcData, $variantSizes, $labelIds);
                }

                if ($ruleId && !empty($labelIds)) {
                    (new \App\Models\CustomizationLabel())->syncCustomizationLabels($ruleId, $labelIds);
                }
            }

            $db->commit();
            \Core\Cache::delete('catalog_active');
            \Core\Cache::delete('home_active_products');
            \Core\Cache::delete('customizable_fabrics_default_v2');
            \Core\Cache::delete('fabric_customization_fid_' . (int)$productId);
            Session::setFlash('success', $isVendor ? 'Product submitted successfully! It is now pending administrator review.' : ($isCustomize ? 'Fabric created successfully.' : 'Product created successfully.'));
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Error creating product: ' . $e->getMessage());
        }

        $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
    }

    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        $productModel = new Product();
        $existingProduct = $productModel->findById($id);
        if (!$existingProduct) {
            Session::setFlash('error', 'Product not found.');
            $this->redirect('/admin/catalog/products');
        }

        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize') 
            || (!empty($_POST['is_customizable']) && (int)$_POST['is_customizable'] === 1) 
            || (!empty($existingProduct['is_customizable']));

        if (!$isCustomize && $id > 0) {
            $dbCheck = \Core\Database::getInstance();
            $stmtCheck = $dbCheck->prepare("SELECT id FROM fabric_customizations WHERE fabric_id = ? LIMIT 1");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetch()) {
                $isCustomize = true;
            }
        }

        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
        }

        $isVendor = (Session::get('user_role') === 'vendor');
        if ($isVendor && (int)($existingProduct['vendor_id'] ?? 0) !== (int)Session::get('user_id')) {
            Session::setFlash('error', 'You do not have permission to edit this product.');
            $this->redirect('/admin/catalog/products');
        }

        $vendorId = $isVendor ? (int)Session::get('user_id') : (!empty($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : null);

        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        if (!$isCustomize && !empty($id)) {
            if (!empty($existingProduct['is_customizable'])) {
                $isCustomize = true;
            } else {
                $db = \Core\Database::getInstance();
                $stmt = $db->prepare("SELECT id FROM fabric_customizations WHERE fabric_id = ? LIMIT 1");
                $stmt->execute([(int)$id]);
                if ($stmt->fetch()) {
                    $isCustomize = true;
                }
            }
        }

        $postedVariants = $_POST['variants'] ?? [];
        $newVariants = $_POST['new_variants'] ?? [];
        $allVariantsList = array_merge(is_array($postedVariants) ? $postedVariants : [], is_array($newVariants) ? $newVariants : []);

        if (empty($allVariantsList)) {
            Session::setFlash('error', 'Please configure at least one variant with price' . (!$isCustomize ? ', weight,' : '') . ' and stock.');
            $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
        }

        $firstVariant = reset($allVariantsList);
        $firstVariantBasePrice = (float)($firstVariant['base_price'] ?? 0);
        $firstVariantDiscountPrice = (isset($firstVariant['discount_price']) && $firstVariant['discount_price'] !== '') ? (float)$firstVariant['discount_price'] : null;
        $firstVariantEffectivePrice = ($firstVariantDiscountPrice !== null && $firstVariantDiscountPrice > 0) ? $firstVariantDiscountPrice : $firstVariantBasePrice;
        $firstVariantWeight = !empty($firstVariant['weight']) ? (float)$firstVariant['weight'] : null;
        $firstVariantSgst = isset($firstVariant['sgst']) && $firstVariant['sgst'] !== '' ? (float)$firstVariant['sgst'] : 0.00;
        $firstVariantCgst = isset($firstVariant['cgst']) && $firstVariant['cgst'] !== '' ? (float)$firstVariant['cgst'] : 0.00;
        $firstVariantTotalGst = isset($firstVariant['total_gst']) && $firstVariant['total_gst'] !== '' ? (float)$firstVariant['total_gst'] : ($firstVariantSgst + $firstVariantCgst);
        $firstVariantMaxAmount = isset($firstVariant['max_amount']) && $firstVariant['max_amount'] !== '' ? (float)$firstVariant['max_amount'] : null;

        // Extract custom description fields
        $customFields = [];
        if (!empty($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
            foreach ($_POST['custom_fields'] as $cf) {
                $fName = trim($cf['name'] ?? '');
                $fVal = trim($cf['value'] ?? '');
                if ($fName !== '' || $fVal !== '') {
                    $customFields[] = [
                        'name' => $fName,
                        'value' => $fVal
                    ];
                }
            }
        }
        $customFieldsJson = !empty($customFields) ? json_encode($customFields, JSON_UNESCAPED_UNICODE) : null;

        $data = [
            'vendor_id' => $vendorId,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'sub_category_id' => !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null,
            'size_chart_id' => !empty($_POST['size_chart_id']) ? (int)$_POST['size_chart_id'] : null,
            'is_customizable' => $isCustomize ? 1 : 0,
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'how_to_use' => !empty($_POST['how_to_use']) ? trim($_POST['how_to_use']) : null,
            'why_choose' => !empty($_POST['why_choose']) ? trim($_POST['why_choose']) : null,
            'custom_fields' => $customFieldsJson,
            'wholesale_price' => $firstVariantEffectivePrice,
            'retail_price' => $firstVariantBasePrice,
            'weight' => $firstVariantWeight,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'moq_override' => (!$isCustomize && !empty($_POST['moq_override'])) ? (int)$_POST['moq_override'] : null,
            'status' => $isVendor ? 'draft' : ($_POST['status'] ?? 'active'),
            'sgst' => $firstVariantSgst,
            'cgst' => $firstVariantCgst,
            'total_gst' => $firstVariantTotalGst,
            'max_amount' => $firstVariantMaxAmount
        ];

        if ($isVendor) {
            $data['approval_status'] = 'pending';
            $data['rejection_reason'] = null;
        }

        if ($id <= 0 || $data['category_id'] <= 0 || empty($data['name']) || empty($data['slug']) || empty($data['description']) || (!$isCustomize && empty($data['moq_override']))) {
            Session::setFlash('error', 'Category, ' . ($isCustomize ? 'Fabric Name' : 'Product Name') . ', Slug, and Description' . (!$isCustomize ? ', and MOQ' : '') . ' are all required.');
            $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
        }

        foreach ($allVariantsList as $vData) {
            $basePrice = isset($vData['base_price']) && $vData['base_price'] !== '' ? (float)$vData['base_price'] : 0;
            $discountPrice = (isset($vData['discount_price']) && $vData['discount_price'] !== '' && (float)$vData['discount_price'] > 0) ? (float)$vData['discount_price'] : null;
            $weight = isset($vData['weight']) && $vData['weight'] !== '' ? (float)$vData['weight'] : 0;
            $sku = trim($vData['sku'] ?? '');

            if ($basePrice <= 0) {
                Session::setFlash('error', 'Base Price is required and must be greater than 0 for all variants.');
                $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
                return;
            }

            if ($discountPrice !== null && $discountPrice >= $basePrice) {
                Session::setFlash('error', 'Discount Price (₹' . number_format($discountPrice, 2) . ') must be less than Base Price (₹' . number_format($basePrice, 2) . ') for all variants.');
                $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
                return;
            }

            if (!$isCustomize && $weight <= 0) {
                Session::setFlash('error', 'Weight is required and must be greater than 0 kg for all variants.');
                $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
                return;
            }

            if (empty($sku)) {
                Session::setFlash('error', 'Variant SKU is required and cannot be empty.');
                $this->redirect('/admin/catalog/products/edit?id=' . $id . ($isCustomize ? '&module=customize' : ''));
                return;
            }
        }

        try {
            $db = \Core\Database::getInstance();
            $db->beginTransaction();

            // --- SKU Validation for Update ---
            $postedVariants = $_POST['variants'] ?? [];
            $newVariants = $_POST['new_variants'] ?? [];
            
            // Combine all variants for validation (maintaining ID keys for existing variants)
            $allVariants = [];
            if (is_array($postedVariants)) {
                foreach ($postedVariants as $k => $v) { $allVariants[$k] = $v; }
            }
            if (is_array($newVariants)) {
                foreach ($newVariants as $k => $v) { $allVariants['new_'.$k] = $v; }
            }
            
            if (!empty($allVariants)) {
                $skus = [];
                foreach ($allVariants as $vId => &$vData) {
                    $sku = trim($vData['sku'] ?? '');
                    if (empty($sku)) {
                        $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['slug']), 0, 6)) . '-1';
                    }
                    $sku = $this->ensureUniqueSku($sku, $id, $skus);
                    $skus[] = $sku;
                    $vData['sku'] = $sku;
                    if (strpos((string)$vId, 'new_') === 0) {
                        $actualK = substr((string)$vId, 4);
                        if (isset($_POST['new_variants'][$actualK])) {
                            $_POST['new_variants'][$actualK]['sku'] = $sku;
                        }
                    } else {
                        if (isset($_POST['variants'][$vId])) {
                            $_POST['variants'][$vId]['sku'] = $sku;
                        }
                    }
                }
                unset($vData);
            }
            // ----------------------

            $productModel = new Product();
            
            // Check for duplicate slug on update
            $existingProduct = $productModel->findBySlug($data['slug']);
            if ($existingProduct && $existingProduct['id'] != $id) {
                $origSlug = $data['slug'];
                $slugCounter = 2;
                while ($chk = $productModel->findBySlug($origSlug . '-' . $slugCounter)) {
                    if ($chk['id'] == $id) break;
                    $slugCounter++;
                }
                $data['slug'] = $origSlug . '-' . $slugCounter;
            }

            $productModel->update($id, $data);
            
            // Sync attributes
            $attributeValues = $_POST['attributes'] ?? [];
            $parsedAttributes = $this->parsePostedAttributes($attributeValues);
            $productModel->syncAttributes($id, $parsedAttributes);

            $variantModel = new ProductVariant();
            $activeVariantIds = [];
            $firstVariantImage = null;

            // Update existing variants
            if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
                foreach ($_POST['variants'] as $vId => $vData) {
                    $vId = (int)$vId;
                    if ($vId > 0) {
                        if ($isCustomize) {
                            $vData['inventory'] = (int)($_POST['stock_quantity'] ?? 0);
                            $vData['current_stock'] = (int)($_POST['stock_quantity'] ?? 0);
                        }
                        $variantModel->update($vId, $vData);
                        $activeVariantIds[] = $vId;
                        
                        if (!empty($vData['attributes']) && is_array($vData['attributes'])) {
                            $variantModel->syncAttributes($vId, $vData['attributes']);
                        }

                        // Sync variant images: delete all then re-insert kept + new images
                        $productModel->deleteVariantImages($vId);
                        $keptImages  = !empty($vData['existing_image_paths']) && is_array($vData['existing_image_paths']) ? $vData['existing_image_paths'] : [];
                        $newImages   = !empty($vData['image_paths'])          && is_array($vData['image_paths'])          ? $vData['image_paths']          : [];
                        $allImages   = array_values(array_unique(array_filter(array_merge($keptImages, $newImages))));
                        foreach ($allImages as $imgUrl) {
                            $productModel->addImage($id, $imgUrl, false, $vId);
                            if (!$firstVariantImage) {
                                $firstVariantImage = $imgUrl;
                            }
                        }
                    }
                }
            }

            // Create new variants
            if (!empty($_POST['new_variants']) && is_array($_POST['new_variants'])) {
                foreach ($_POST['new_variants'] as $key => $vData) {
                    $vData['product_id'] = $id;
                    if ($isCustomize) {
                        $vData['inventory'] = (int)($_POST['stock_quantity'] ?? 0);
                        $vData['current_stock'] = (int)($_POST['stock_quantity'] ?? 0);
                    }
                    $variantId = $variantModel->create($vData);
                    $activeVariantIds[] = $variantId;
                    
                    if (!empty($vData['attributes']) && is_array($vData['attributes'])) {
                        $variantModel->syncAttributes($variantId, $vData['attributes']);
                    }

                    if (!empty($vData['image_paths']) && is_array($vData['image_paths'])) {
                        $uniqueImgs = array_values(array_unique(array_filter($vData['image_paths'])));
                        foreach ($uniqueImgs as $imgUrl) {
                            if (!empty($imgUrl)) {
                                $productModel->addImage($id, $imgUrl, false, $variantId);
                                if (!$firstVariantImage) {
                                    $firstVariantImage = $imgUrl;
                                }
                            }
                        }
                    }
                }
            }

            // Delete removed variants
            $variantModel->deleteUnused($id, $activeVariantIds);

            // Handle Primary Image
            $primaryImgPath = '';
            if (isset($_POST['primary_image_path'])) {
                $primaryImgPath = trim($_POST['primary_image_path']);
                if (!empty($primaryImgPath)) {
                    $productModel->deletePrimaryImage($id);
                    $productModel->addImage($id, $primaryImgPath, true, null, 0);
                } else {
                    $productModel->deletePrimaryImage($id);
                    if ($firstVariantImage) {
                        $productModel->addImage($id, $firstVariantImage, true, null, 0);
                        $primaryImgPath = $firstVariantImage;
                    }
                }
            } else {
                $existingPrimary = $productModel->getImages($id);
                if (empty($existingPrimary) && $firstVariantImage) {
                    $productModel->addImage($id, $firstVariantImage, true, null, 0);
                    $primaryImgPath = $firstVariantImage;
                }
            }

            // Handle Additional Gallery Images (up to 4 gallery images = total 5 with primary)
            if (isset($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
                $db = \Core\Database::getInstance();
                $stmtDel = $db->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 0 AND variant_id IS NULL");
                $stmtDel->execute([$id]);

                $sort = 1;
                foreach ($_POST['gallery_images'] as $gImg) {
                    $gPath = trim($gImg);
                    if (!empty($gPath) && $gPath !== $primaryImgPath && $sort <= 4) {
                        $productModel->addImage($id, $gPath, false, null, $sort);
                        $sort++;
                    }
                }
                     // Handle Fabric Customization Rules, Labels & Sizes update when module is customize
            if ($isCustomize) {
                $minPieces = isset($_POST['minimum_pieces']) ? max(1, (int)$_POST['minimum_pieces']) : 10;
                $maxPieces = isset($_POST['maximum_pieces']) ? max(1, (int)$_POST['maximum_pieces']) : 100;
                $coverDiscount = isset($_POST['without_cover_discount_percentage']) ? (float)$_POST['without_cover_discount_percentage'] : 0.00;
                $deliveryDays = isset($_POST['delivery_days']) ? max(1, (int)$_POST['delivery_days']) : 7;
                $deliveryBasePieces = isset($_POST['delivery_base_pieces']) ? max(1, (int)$_POST['delivery_base_pieces']) : 100;
                $ruleStatus = $_POST['rule_status'] ?? ($_POST['customization_status'] ?? 'active');

                // Collect label IDs from both potential input names
                $labelIds = [];
                if (!empty($_POST['customization_labels']) && is_array($_POST['customization_labels'])) {
                    $labelIds = array_map('intval', $_POST['customization_labels']);
                } elseif (!empty($_POST['label_ids']) && is_array($_POST['label_ids'])) {
                    $labelIds = array_map('intval', $_POST['label_ids']);
                }

                // Collect all posted variants (existing + new)
                $allVariants = array_merge(
                    !empty($_POST['variants']) && is_array($_POST['variants']) ? array_values($_POST['variants']) : [],
                    !empty($_POST['new_variants']) && is_array($_POST['new_variants']) ? array_values($_POST['new_variants']) : []
                );

                $variantSizes = [];
                $sort = 1;
                if (!empty($_POST['fabric_sizes']) && is_array($_POST['fabric_sizes'])) {
                    foreach ($_POST['fabric_sizes'] as $sData) {
                        $sName = trim($sData['size_name'] ?? '');
                        if ($sName !== '') {
                            $variantSizes[] = [
                                'size_name' => $sName,
                                'fabric_consumption' => !empty($sData['fabric_consumption']) ? (float)$sData['fabric_consumption'] : 1.00,
                                'base_quantity' => isset($sData['base_quantity']) ? max(0, (int)$sData['base_quantity']) : 2,
                                'max_quantity' => isset($sData['max_quantity']) ? max(1, (int)$sData['max_quantity']) : 4,
                                'enabled' => isset($sData['enabled']) ? (int)$sData['enabled'] : 1,
                                'sort_order' => $sort++
                            ];
                        }
                    }
                } elseif (!empty($allVariants)) {
                    foreach ($allVariants as $vData) {
                        $vName = trim($vData['name'] ?? '');
                        if ($vName !== '') {
                            $variantSizes[] = [
                                'size_name' => $vName,
                                'fabric_consumption' => !empty($vData['fabric_consumption']) ? (float)$vData['fabric_consumption'] : 1.00,
                                'base_quantity' => isset($vData['base_quantity']) && $vData['base_quantity'] !== '' ? max(1, (int)$vData['base_quantity']) : 2,
                                'max_quantity' => isset($vData['max_quantity']) && $vData['max_quantity'] !== '' ? max(1, (int)$vData['max_quantity']) : 4,
                                'enabled' => 1,
                                'sort_order' => $sort++
                            ];
                        }
                    }
                }
                if (empty($variantSizes)) {
                    $variantSizes = [
                        ['size_name' => 'M', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 1],
                        ['size_name' => 'L', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 2],
                        ['size_name' => 'XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 3],
                        ['size_name' => 'XXL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 4],
                        ['size_name' => '3XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 5],
                        ['size_name' => '4XL', 'fabric_consumption' => 1.00, 'base_quantity' => 2, 'max_quantity' => 4, 'enabled' => 1, 'sort_order' => 6],
                    ];
                }

                $fcModel = new \App\Models\FabricCustomization();
                $stmt = $db->prepare("SELECT id FROM fabric_customizations WHERE fabric_id = ? LIMIT 1");
                $stmt->execute([$id]);
                $existingFc = $stmt->fetch();

                $fcData = [
                    'fabric_id' => $id,
                    'program_name' => $data['name'] . ' Rule',
                    'garment_type' => 'Custom Garment',
                    'category_id' => $data['category_id'],
                    'sub_category_id' => $data['sub_category_id'] ?? null,
                    'feeding_type' => 'Maternity / Non-Feeding',
                    'minimum_pieces' => $minPieces,
                    'required_pieces' => $minPieces,
                    'maximum_pieces' => $maxPieces,
                    'without_cover_discount_percentage' => $coverDiscount,
                    'delivery_days' => $deliveryDays,
                    'delivery_base_pieces' => $deliveryBasePieces,
                    'wastage_percentage' => 0.00,
                    'unit' => 'Quantity',
                    'customization_enabled' => 1,
                    'status' => $ruleStatus
                ];

                if ($existingFc) {
                    $fcModel->update($existingFc['id'], $fcData, $variantSizes, $labelIds);
                    $ruleId = (int)$existingFc['id'];
                } else {
                    $ruleId = $fcModel->create($fcData, $variantSizes, $labelIds);
                }

                if ($ruleId && !empty($labelIds)) {
                    (new \App\Models\CustomizationLabel())->syncCustomizationLabels($ruleId, $labelIds);
                }
            }       }

            $db->commit();
            \Core\Cache::delete('catalog_active');
            \Core\Cache::delete('home_active_products');
            \Core\Cache::delete('customizable_fabrics_default_v2');
            \Core\Cache::delete('fabric_customization_fid_' . (int)$id);
            Session::setFlash('success', $isCustomize ? 'Fabric updated successfully.' : 'Product updated successfully.');
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Error updating product: ' . $e->getMessage());
        }

        $this->redirect('/admin/catalog/products' . ($isCustomize ? '?module=customize' : ''));
    }

    public function delete()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'delete');
        $redirectUrl = '/admin/catalog/products' . ($isCustomize ? '?module=customize' : '');

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
                $productModel = new Product();
                $existingProduct = $productModel->findById($id);
                if (!$existingProduct) {
                    if ($this->isJsonRequest()) {
                        $this->json(['success' => false, 'message' => 'Product not found.']);
                    }
                    Session::setFlash('error', 'Product not found.');
                    $this->redirect($redirectUrl);
                    return;
                }

                if (!$isCustomize && !empty($existingProduct['is_customizable'])) {
                    $isCustomize = true;
                    $redirectUrl = '/admin/catalog/products?module=customize';
                }

                $isVendor = (Session::get('user_role') === 'vendor');
                if ($isVendor && (int)($existingProduct['vendor_id'] ?? 0) !== (int)Session::get('user_id')) {
                    if ($this->isJsonRequest()) {
                        $this->json(['success' => false, 'message' => 'You do not have permission to delete this product.']);
                    }
                    Session::setFlash('error', 'You do not have permission to delete this product.');
                    $this->redirect($redirectUrl);
                    return;
                }

                $productModel->delete($id);
                \Core\Cache::delete('customizable_fabrics_default_v2');
                \Core\Cache::delete('fabric_customization_fid_' . (int)$id);
                
                if ($this->isJsonRequest()) {
                    $this->json(['success' => true, 'message' => ($isCustomize ? 'Fabric' : 'Product') . ' deleted successfully.']);
                }
                Session::setFlash('success', ($isCustomize ? 'Fabric' : 'Product') . ' deleted successfully.');
            } catch (\Exception $e) {
                if ($this->isJsonRequest()) {
                    $this->json(['success' => false, 'message' => 'Cannot delete product: ' . $e->getMessage()]);
                }
                Session::setFlash('error', 'Cannot delete product: ' . $e->getMessage());
            }
        }

        $this->redirect($redirectUrl);
    }

    private function parsePostedAttributes(array $postedAttributes): array
    {
        $parsed = [];
        foreach ($postedAttributes as $attrId => $val) {
            $attrId = (int)$attrId;
            if (is_array($val)) {
                // Checkbox / Multiselect (picklist)
                foreach ($val as $v) {
                    $parsed[] = [
                        'attribute_id' => $attrId,
                        'attribute_value_id' => (int)$v,
                        'value_text' => null
                    ];
                }
            } else {
                // Single value (could be picklist ID for dropdown, or text for textbox)
                $attributeModel = new Attribute();
                $attr = $attributeModel->findById($attrId);
                if (!$attr) continue;

                $val = trim($val);
                if ($val === '') continue;

                if (in_array($attr['input_type'], ['dropdown', 'multiselect', 'checkbox', 'colorpicker'])) {
                    $parsed[] = [
                        'attribute_id' => $attrId,
                        'attribute_value_id' => (int)$val,
                        'value_text' => null
                    ];
                } else {
                    $parsed[] = [
                        'attribute_id' => $attrId,

                        'attribute_value_id' => null,
                        'value_text' => $val
                    ];
                }
            }
        }
        return $parsed;
    }

    public function deleteImage()
    {
        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $isCustomize = ($module === 'customize');
        $this->requirePermission($isCustomize ? 'customize_products' : 'products', 'edit');
        
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($_POST['product_id'] ?? ($jsonInput['product_id'] ?? 0));
        $imageId = (int)($_POST['image_id'] ?? ($jsonInput['image_id'] ?? 0));
        $imagePath = trim($_POST['image_path'] ?? ($jsonInput['image_path'] ?? ''));

        $deleted = false;
        $db = \Core\Database::getInstance();

        if ($imageId > 0) {
            $stmt = $db->prepare("DELETE FROM product_images WHERE id = ?");
            $stmt->execute([$imageId]);
            $deleted = true;
        } elseif ($productId > 0 && !empty($imagePath)) {
            $productModel = new Product();
            $productModel->deleteImageByPath($productId, $imagePath);
            $deleted = true;
        }

        if ($deleted) {
            \Core\Cache::delete('catalog_active');
            if ($this->isJsonRequest()) {
                $this->json(['success' => true, 'message' => 'Image removed successfully.']);
            }
            Session::setFlash('success', 'Image removed successfully.');
        } else {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Image not found or missing parameters.'], 400);
            }
            Session::setFlash('error', 'Image not found.');
        }

        $module = $_POST['module'] ?? ($_GET['module'] ?? '');
        $this->redirect('/admin/catalog/products/edit?id=' . $productId . ($module === 'customize' ? '&module=customize' : ''));
    }

    public function approve()
    {
        $role = Session::get('user_role');
        if ($role !== 'super_admin' && !$this->hasPermission('products', 'edit')) {
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
                exit;
            }
            Session::setFlash('error', 'Unauthorized action.');
            $this->redirect('/admin/catalog/products');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $msg = 'Invalid product ID.';
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
            $this->redirect('/admin/catalog/products');
            return;
        }

        try {
            $productModel = new Product();
            $product = $productModel->findById($id);
            if (!$product) {
                throw new \Exception('Product not found.');
            }

            $productModel->approveProduct($id, (int)Session::get('user_id'));
            \Core\Cache::delete('catalog_active');

            $msg = "Product '{$product['name']}' has been approved and is now active on the catalog!";
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => true, 'message' => $msg]);
                exit;
            }
            Session::setFlash('success', $msg);
        } catch (\Exception $e) {
            $msg = 'Approval failed: ' . $e->getMessage();
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
        }
        $this->redirect('/admin/catalog/products');
    }

    public function reject()
    {
        $role = Session::get('user_role');
        if ($role !== 'super_admin' && !$this->hasPermission('products', 'edit')) {
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
                exit;
            }
            Session::setFlash('error', 'Unauthorized action.');
            $this->redirect('/admin/catalog/products');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? 'Not approved by administrator');

        if (!$id) {
            $msg = 'Invalid product ID.';
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
            $this->redirect('/admin/catalog/products');
            return;
        }

        try {
            $productModel = new Product();
            $product = $productModel->findById($id);
            if (!$product) {
                throw new \Exception('Product not found.');
            }

            $productModel->rejectProduct($id, (int)Session::get('user_id'), $reason);
            \Core\Cache::delete('catalog_active');

            $msg = "Product '{$product['name']}' has been rejected.";
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => true, 'message' => $msg]);
                exit;
            }
            Session::setFlash('success', $msg);
        } catch (\Exception $e) {
            $msg = 'Rejection failed: ' . $e->getMessage();
            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
        }
        $this->redirect('/admin/catalog/products');
    }

    private function isJsonRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Ensures an SKU is strictly unique across product_variants and within the current submission.
     * Auto-disambiguates with an incrementing suffix instead of crashing product creation.
     */
    private function ensureUniqueSku(string $sku, int $excludeProductId = 0, array $alreadyUsed = []): string
    {
        $db = \Core\Database::getInstance();
        $baseSku = trim($sku);
        if ($baseSku === '') {
            $baseSku = 'SKU-' . strtoupper(substr(uniqid(), -6));
        }
        $candidate = $baseSku;
        $counter = 1;

        while (true) {
            if (in_array($candidate, $alreadyUsed)) {
                $candidate = $baseSku . '-' . $counter++;
                continue;
            }

            if ($excludeProductId > 0) {
                $stmt = $db->prepare("SELECT id FROM product_variants WHERE sku = ? AND product_id != ? LIMIT 1");
                $stmt->execute([$candidate, $excludeProductId]);
            } else {
                $stmt = $db->prepare("SELECT id FROM product_variants WHERE sku = ? LIMIT 1");
                $stmt->execute([$candidate]);
            }

            if ($stmt->fetch()) {
                $candidate = $baseSku . '-' . $counter++;
                continue;
            }

            return $candidate;
        }
    }
}
