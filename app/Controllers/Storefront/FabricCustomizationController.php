<?php

namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\FabricCustomization;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Services\FabricCustomizationService;
use App\Services\RazorpayService;
use PDO;

class FabricCustomizationController extends Controller
{
    private $model;
    private $service;
    private $categoryModel;
    private $productModel;
    private $orderModel;

    public function __construct()
    {
        $this->model = new FabricCustomization();
        $this->service = new FabricCustomizationService();
        $this->categoryModel = new Category();
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    /**
     * Storefront Customizable Fabrics Listing (/customize)
     * Displays ONLY admin-added fabrics that have active customization rules
     */
    public function customizeListing()
    {
        $filters = [
            'category_id'   => $_GET['category_id'] ?? '',
            'garment_type'  => $_GET['garment_type'] ?? '',
            'search'        => $_GET['search'] ?? '',
            'sort'          => $_GET['sort'] ?? 'newest'
        ];

        $fabrics = $this->model->getCustomizableFabrics($filters);
        $categories = $this->categoryModel->getAllActive(null, 1);

        // Extract available garment types for filter buttons
        $garmentTypes = array_unique(array_filter(array_column($fabrics, 'garment_type')));

        $this->render('storefront/customize_listing', [
            'title'        => 'Custom Wholesale Fabric & Stitching Workshop',
            'fabrics'      => $fabrics,
            'categories'   => $categories,
            'garmentTypes' => $garmentTypes,
            'filters'      => $filters
        ]);
    }

    /**
     * Customization Workshop Detail Page (/fabrics/customize?fabric_id=...)
     * Runs based on the Fabric Rules: sizes available, pieces to stitch, consumption per piece
     */
    public function customizeWorkshop($id = null)
    {
        $fabricId = (int)($id ?: ($_GET['fabric_id'] ?? 0));
        $customizationId = (int)($_GET['id'] ?? 0);

        $userId = Session::get('user_id');
        if (!$userId) {
            Session::setFlash('info', 'Please log in to customize fabrics and view wholesale pricing.');
            $redirectTarget = '/fabrics/customize?' . ($fabricId > 0 ? 'fabric_id=' . $fabricId : 'id=' . $customizationId);
            $this->redirect('/login?redirect=' . urlencode($redirectTarget));
            return;
        }

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
            if (!$customization) {
                // Also check by customization ID in case $id is the customization ID
                $customization = $this->model->getById($fabricId);
            }
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            Session::setFlash('error', 'The requested fabric is currently not configured for custom stitching.');
            $this->redirect('/customize');
            return;
        }

        // Remember return URL for guest visitors (e.g. from Instagram / direct link)
        if (!Session::get('user_id')) {
            $currentUri = $_SERVER['REQUEST_URI'] ?? ('/fabrics/customize?fabric_id=' . (int)$fabricId);
            Session::set('return_to', $currentUri);
        }

        // Fetch user addresses if logged in
        $userAddresses = [];
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        $userAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $wishlistIds = [];
        try {
            $wishlistModel = new \App\Models\Wishlist();
            $wishlistIds = $wishlistModel->getProductIds($userId);
        } catch (\Throwable $e) {
            $wishlistIds = [];
        }

        $reviewModel = new \App\Models\Review();
        $customizationReviews = $reviewModel->getApprovedCustomizationReviews((int)$customization['id'], (int)$customization['fabric_id']);
        $customizationStats = $reviewModel->getCustomizationReviewStats((int)$customization['id'], (int)$customization['fabric_id']);

        // Fetch "You May Also Like" products (customizable products only, prioritizing different subcategories)
        $relatedProducts = [];
        try {
            $catId = !empty($customization['category_id']) ? (int)$customization['category_id'] : 0;
            $currentSubCatId = !empty($customization['sub_category_id']) ? (int)$customization['sub_category_id'] : (!empty($fabric['sub_category_id']) ? (int)$fabric['sub_category_id'] : 0);
            $currentFabricId = !empty($customization['fabric_id']) ? (int)$customization['fabric_id'] : 0;

            // Only fetch active customizable products (is_customizable = 1) from different subcategories / categories
            $candidateStmt = $db->prepare("
                SELECT p.*, 
                       c.name AS category_name, 
                       c.moq AS category_moq,
                       sc.name AS subcategory_name,
                       fc.id AS customization_id,
                       fc.program_name,
                       fc.garment_type,
                       fc.minimum_pieces AS custom_moq,
                       (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS primary_image,
                       pv_agg.base_price,
                       pv_agg.discount_price
                FROM products p
                JOIN categories c ON p.category_id = c.id AND c.status = 'active'
                JOIN fabric_customizations fc ON fc.fabric_id = p.id AND fc.status = 'active' AND fc.customization_enabled = 1
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id AND sc.status = 'active'
                LEFT JOIN (
                    SELECT product_id, 
                           MIN(base_price) AS base_price,
                           MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) AS discount_price 
                    FROM product_variants GROUP BY product_id
                ) pv_agg ON pv_agg.product_id = p.id
                WHERE p.status = 'active' 
                  AND p.is_customizable = 1 
                  AND p.id != ?
                ORDER BY (p.sub_category_id != ? AND p.sub_category_id IS NOT NULL) DESC, 
                         (p.category_id != ?) DESC, 
                         p.id DESC
                LIMIT 10
            ");
            $candidateStmt->execute([$currentFabricId, $currentSubCatId, $catId]);
            $relatedProducts = $candidateStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($relatedProducts)) {
                $relPids = array_map('intval', array_column($relatedProducts, 'id'));
                $relCids = array_filter(array_map('intval', array_column($relatedProducts, 'customization_id')));

                $groupedImgs = [];
                if (!empty($relPids)) {
                    $pidList = implode(',', $relPids);
                    $imgStmt = $db->query("SELECT product_id, image_path FROM product_images WHERE product_id IN ($pidList) ORDER BY is_primary DESC, sort_order ASC, id ASC");
                    while ($r = $imgStmt->fetch(PDO::FETCH_ASSOC)) {
                        $groupedImgs[$r['product_id']][] = $r['image_path'];
                    }
                }

                $groupedSizes = [];
                if (!empty($relCids)) {
                    $cidList = implode(',', $relCids);
                    $szStmt = $db->query("SELECT customization_id, size_name FROM fabric_customization_sizes WHERE customization_id IN ($cidList) AND enabled = 1 ORDER BY sort_order ASC, id ASC");
                    while ($sz = $szStmt->fetch(PDO::FETCH_ASSOC)) {
                        $groupedSizes[$sz['customization_id']][] = $sz['size_name'];
                    }
                }

                foreach ($relatedProducts as &$relItem) {
                    $relItem['images'] = $groupedImgs[$relItem['id']] ?? (!empty($relItem['primary_image']) ? [$relItem['primary_image']] : []);
                    $relItem['sizes'] = $groupedSizes[$relItem['customization_id']] ?? ['3XL', '4XL', 'L', 'M', 'XL', 'XXL'];
                }
                unset($relItem);
            }
        } catch (\Throwable $e) {
            $relatedProducts = [];
        }

        $canAddToCart = ($userId && Session::get('user_status') === 'active');

        $savedSizes = Session::get('custom_checkout_sizes_' . $customization['id']) 
            ?? Session::get('custom_checkout_sizes_f_' . $customization['fabric_id']) 
            ?? [];
        $savedSets  = Session::get('custom_checkout_sets_' . $customization['id']) 
            ?? Session::get('custom_checkout_sets_f_' . $customization['fabric_id']) 
            ?? [];
        $savedQty   = (int)(Session::get('custom_checkout_qty_' . $customization['id']) 
            ?? Session::get('custom_checkout_qty_f_' . $customization['fabric_id']) 
            ?? 1);
        $savedPkg   = Session::get('custom_checkout_pkg_' . $customization['id']) 
            ?? Session::get('custom_checkout_pkg_f_' . $customization['fabric_id']) 
            ?? 'with_cover';

        $this->render('storefront/fabric_customize', [
            'title'                => 'Customize: ' . htmlspecialchars($customization['fabric_name']) . ' (' . htmlspecialchars($customization['garment_type']) . ')',
            'customization'        => $customization,
            'userAddresses'        => $userAddresses,
            'wishlistIds'          => $wishlistIds,
            'userId'               => $userId,
            'customizationReviews' => $customizationReviews,
            'customizationStats'   => $customizationStats,
            'relatedProducts'      => $relatedProducts,
            'canAddToCart'         => $canAddToCart,
            'savedSizes'           => $savedSizes,
            'savedSets'            => $savedSets,
            'savedQty'             => $savedQty,
            'savedPkg'             => $savedPkg
        ]);
    }

    /**
     * Dedicated Custom Checkout & Razorpay Review Page
     */
    public function checkoutPage()
    {
        $userId = Session::get('user_id');
        $fabricId = (int)($_POST['fabric_id'] ?? ($_GET['fabric_id'] ?? 0));
        $customizationId = (int)($_POST['customization_id'] ?? ($_GET['id'] ?? ($_GET['customization_id'] ?? 0)));

        $redirectTarget = '/fabrics/customize/checkout?' . ($fabricId > 0 ? 'fabric_id=' . $fabricId : 'customization_id=' . $customizationId);

        if (!$userId || Session::get('user_status') !== 'active') {
            Session::setFlash('info', 'Please log in to review your custom garment order and proceed to payment.');
            $this->redirect('/login?redirect=' . urlencode($redirectTarget));
            return;
        }

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            Session::setFlash('error', 'Fabric customization program not found.');
            $this->redirect('/customize');
            return;
        }

        // Parse sets from POST, session or GET
        $sets = [];
        if (!empty($_POST['sets']) && is_array($_POST['sets'])) {
            $sets = $_POST['sets'];
            Session::set('custom_checkout_sets_' . $customization['id'], $sets);
            Session::set('custom_checkout_sets_f_' . $customization['fabric_id'], $sets);
        } elseif (!empty($_POST['sets_json'])) {
            $sets = json_decode($_POST['sets_json'], true) ?: [];
            Session::set('custom_checkout_sets_' . $customization['id'], $sets);
            Session::set('custom_checkout_sets_f_' . $customization['fabric_id'], $sets);
        } else {
            $sets = Session::get('custom_checkout_sets_' . $customization['id']) 
                ?? Session::get('custom_checkout_sets_f_' . $customization['fabric_id']) 
                ?? [];
        }

        // Parse sizes from POST, session or GET
        $sizes = [];
        if (!empty($_POST['sizes']) && is_array($_POST['sizes'])) {
            $sizes = $_POST['sizes'];
            Session::set('custom_checkout_sizes_' . $customization['id'], $sizes);
            Session::set('custom_checkout_sizes_f_' . $customization['fabric_id'], $sizes);
        } elseif (!empty($_GET['sizes']) && is_array($_GET['sizes'])) {
            $sizes = $_GET['sizes'];
            Session::set('custom_checkout_sizes_' . $customization['id'], $sizes);
            Session::set('custom_checkout_sizes_f_' . $customization['fabric_id'], $sizes);
        } else {
            $sizes = Session::get('custom_checkout_sizes_' . $customization['id']) 
                ?? Session::get('custom_checkout_sizes_f_' . $customization['fabric_id']) 
                ?? [];
        }

        // If sizes is empty but sets are available, aggregate sizes from sets
        if (empty($sizes) && !empty($sets)) {
            $sizes = [];
            foreach ($sets as $setData) {
                if (!empty($setData['sizes']) && is_array($setData['sizes'])) {
                    foreach ($setData['sizes'] as $szName => $szVal) {
                        $sizes[$szName] = ($sizes[$szName] ?? 0) + (int)$szVal;
                    }
                }
            }
            Session::set('custom_checkout_sizes_' . $customization['id'], $sizes);
            Session::set('custom_checkout_sizes_f_' . $customization['fabric_id'], $sizes);
        }

        // If sizes are still empty, default to base configuration from the program
        if (empty($sizes) && !empty($customization['sizes'])) {
            foreach ($customization['sizes'] as $sz) {
                $sizes[$sz['size_name']] = (int)($sz['base_quantity'] ?? 2);
            }
        }

        $fabricQuantity = max(1, (int)($_POST['fabric_quantity'] ?? ($_GET['fabric_quantity'] ?? (Session::get('custom_checkout_qty_' . $customization['id']) ?? Session::get('custom_checkout_qty_f_' . $customization['fabric_id']) ?? 1))));
        Session::set('custom_checkout_qty_' . $customization['id'], $fabricQuantity);
        Session::set('custom_checkout_qty_f_' . $customization['fabric_id'], $fabricQuantity);

        $packagingOption = $_POST['packaging_option'] ?? ($_GET['packaging_option'] ?? (Session::get('custom_checkout_pkg_' . $customization['id']) ?? Session::get('custom_checkout_pkg_f_' . $customization['fabric_id']) ?? 'with_cover'));
        Session::set('custom_checkout_pkg_' . $customization['id'], $packagingOption);
        Session::set('custom_checkout_pkg_f_' . $customization['fabric_id'], $packagingOption);
        $withoutCover = ($packagingOption === 'without_cover');
        $discountPct = (float)($customization['without_cover_discount_percentage'] ?? 0.0);

        // Calculate consumption and pricing
        $consumption = $this->service->calculateConsumption(
            $customization, 
            $sizes, 
            (float)$customization['fabric_stock'],
            $fabricQuantity,
            $sets
        );
        if (!$consumption['is_valid']) {
            $msg = !empty($consumption['errors']) ? implode(' ', $consumption['errors']) : 'Customization piece requirements not met.';
            Session::setFlash('error', $msg);
            $this->redirect('/fabrics/customize?' . ($fabricId > 0 ? 'fabric_id=' . $fabricId : 'id=' . $customization['id']));
            return;
        }
        $fabricQuantity = (int)($consumption['fabric_quantity'] ?? $fabricQuantity);
        Session::set('custom_checkout_qty_' . $customization['id'], $fabricQuantity);
        Session::set('custom_checkout_qty_f_' . $customization['fabric_id'], $fabricQuantity);

        $pricing = $this->service->calculatePricing(
            (float)$customization['fabric_price'],
            (float)$consumption['required_fabric'],
            0.00,
            (int)$consumption['total_pieces'],
            5.0,
            $withoutCover,
            $discountPct
        );

        $delivery = $this->service->calculateDeliveryTimeline(
            (int)$consumption['total_pieces'],
            (int)($customization['delivery_days'] ?? 7),
            (int)($customization['delivery_base_pieces'] ?? 100)
        );

        // Fetch user addresses
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        $userAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userStmt = $db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        $this->render('storefront/fabric_checkout', [
            'title'           => 'Checkout & Payment: ' . htmlspecialchars($customization['fabric_name']) . ' Customization',
            'customization'   => $customization,
            'sizes'           => $sizes,
            'sets'            => $sets,
            'fabricQuantity'  => $fabricQuantity,
            'packagingOption' => $packagingOption,
            'consumption'     => $consumption,
            'pricing'         => $pricing,
            'delivery'        => $delivery,
            'userAddresses'   => $userAddresses,
            'user'            => $user
        ]);
    }

    /**
     * AJAX Live Calculation Endpoint
     */
    public function calculateAjax()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $fabricId = (int)($input['fabric_id'] ?? 0);
        $customizationId = (int)($input['customization_id'] ?? 0);
        $sizes = $input['sizes'] ?? [];
        $sets = $input['sets'] ?? [];
        $fabricQuantity = max(1, (int)($input['fabric_quantity'] ?? 1));

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            echo json_encode(['success' => false, 'error' => 'Customization rule not found']);
            exit;
        }

        $packagingOption = $input['packaging_option'] ?? 'with_cover';
        $withoutCover = ($packagingOption === 'without_cover' || !empty($input['without_cover']));
        $discountPct = (float)($customization['without_cover_discount_percentage'] ?? 0.0);

        $consumptionResult = $this->service->calculateConsumption(
            $customization, 
            $sizes, 
            (float)$customization['fabric_stock'],
            $fabricQuantity,
            $sets
        );

        $pricingResult = $this->service->calculatePricing(
            (float)$customization['fabric_price'],
            (float)$consumptionResult['required_fabric'],
            0.00, // stitching charge
            (int)$consumptionResult['total_pieces'],
            5.0, // GST 5%
            $withoutCover,
            $discountPct
        );

        $deliveryResult = $this->service->calculateDeliveryTimeline(
            (int)$consumptionResult['total_pieces'],
            (int)($customization['delivery_days'] ?? 7),
            (int)($customization['delivery_base_pieces'] ?? 100)
        );

        echo json_encode([
            'success'     => true,
            'consumption' => $consumptionResult,
            'pricing'     => $pricingResult,
            'delivery'    => $deliveryResult
        ]);
        exit;
    }

    /**
     * OPTION 1: Submit Custom Order Request (Quote / Request for Approval)
     * Direct custom order request without upfront payment
     */
    public function requestOrderAjax()
    {
        header('Content-Type: application/json');

        if (!Session::get('user_id')) {
            echo json_encode([
                'success'       => false, 
                'require_login' => true,
                'message'       => 'Please log in to submit a custom order request.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $fabricId = (int)($input['fabric_id'] ?? 0);
        $customizationId = (int)($input['customization_id'] ?? 0);
        $sizes = $input['sizes'] ?? [];
        $sets = $input['sets'] ?? [];
        $fabricQuantity = max(1, (int)($input['fabric_quantity'] ?? 1));
        $shippingAddress = trim($input['shipping_address'] ?? 'Wholesale Delivery on File');
        $customerNotes = trim($input['notes'] ?? '');

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            echo json_encode(['success' => false, 'message' => 'Fabric rule not found']);
            exit;
        }

        $packagingOption = $input['packaging_option'] ?? 'with_cover';
        $withoutCover = ($packagingOption === 'without_cover' || !empty($input['without_cover']));
        $discountPct = (float)($customization['without_cover_discount_percentage'] ?? 0.0);

        // Validate consumption & stock
        $calc = $this->service->calculateConsumption($customization, $sizes, (float)$customization['fabric_stock'], $fabricQuantity, $sets);
        if (!$calc['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $calc['errors'])]);
            exit;
        }
        $fabricQuantity = (int)($calc['fabric_quantity'] ?? $fabricQuantity);

        $pricing = $this->service->calculatePricing(
            (float)$customization['fabric_price'],
            (float)$calc['required_fabric'],
            0.00,
            (int)$calc['total_pieces'],
            5.0,
            $withoutCover,
            $discountPct
        );

        $delivery = $this->service->calculateDeliveryTimeline(
            (int)$calc['total_pieces'],
            (int)($customization['delivery_days'] ?? 7),
            (int)($customization['delivery_base_pieces'] ?? 100)
        );

        $labelQuantities = $input['label_quantities'] ?? [];

        $db = Database::getInstance();
        $userId = $this->resolveValidUserId($db);

        try {
            $db->beginTransaction();

            $orderNumber = $this->generateCustomizeOrderNumber($db, 'GBC');

            $customizationData = [
                'customization_id'    => $customization['id'],
                'fabric_quantity'     => $fabricQuantity,
                'program_name'        => $customization['program_name'],
                'garment_type'        => $customization['garment_type'],
                'feeding_type'        => $customization['feeding_type'],
                'packaging_option'    => $packagingOption,
                'without_cover'       => $withoutCover,
                'discount_percentage' => $discountPct,
                'total_saved'         => $pricing['total_saved'] ?? 0.00,
                'delivery_timeline'   => $delivery,
                'fabric_name'         => $customization['fabric_name'],
                'fabric_sku'          => $customization['fabric_sku'],
                'fabric_unit'         => $customization['unit'],
                'total_pieces'        => $calc['total_pieces'],
                'required_fabric'     => $calc['required_fabric'],
                'base_consumption'    => $calc['base_consumption'],
                'wastage_percentage'  => $calc['wastage_percentage'],
                'wastage_amount'      => $calc['wastage_amount'],
                'price_per_unit'      => $pricing['price_per_unit'],
                'original_price'      => $pricing['original_price_per_unit'],
                'fabric_cost'         => $pricing['fabric_cost'],
                'stitching_cost'      => $pricing['stitching_cost'],
                'gst_amount'          => $pricing['gst_amount'],
                'grand_total'         => $pricing['total'],
                'order_mode'          => 'request_quote',
                'customer_notes'      => $customerNotes,
                'sizes'               => $calc['breakdown'],
                'sets'                => $sets,
                'label_quantities'    => $labelQuantities,
                'labels'              => $labelQuantities
            ];

            $orderStmt = $db->prepare("
                INSERT INTO orders (
                    user_id, order_number, total_amount, grand_total, shipping_address, 
                    status, payment_status, is_stock_reduced, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, 'placed', 'request_pending', 1, ?, NOW())
            ");

            $orderStmt->execute([
                $userId,
                $orderNumber,
                $pricing['subtotal'],
                $pricing['total'],
                $shippingAddress,
                "Custom Order Request ({$customization['program_name']} x {$fabricQuantity}) - " . $customerNotes
            ]);

            $orderId = (int)$db->lastInsertId();

            // Insert into order_items
            $itemStmt = $db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, customization_id, customization_data, 
                    quantity, unit_price, total_price
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $itemStmt->execute([
                $orderId,
                $customization['fabric_id'],
                $customization['id'],
                json_encode($customizationData),
                $calc['total_pieces'],
                $customization['fabric_price'],
                $pricing['total']
            ]);

            // Decrement fabric stock
            $stockStmt = $db->prepare("
                UPDATE products 
                SET stock_quantity = GREATEST(0, stock_quantity - ?) 
                WHERE id = ?
            ");
            $stockStmt->execute([(int)$calc['required_fabric'], $customization['fabric_id']]);

            // Sync variant stock to remaining fabric quantity
            $syncVarStmt = $db->prepare("
                UPDATE product_variants 
                SET current_stock = (SELECT stock_quantity FROM products WHERE id = :p1),
                    stock = (SELECT stock_quantity FROM products WHERE id = :p2),
                    inventory = (SELECT stock_quantity FROM products WHERE id = :p3)
                WHERE product_id = :p4
            ");
            $syncVarStmt->execute([
                'p1' => $customization['fabric_id'],
                'p2' => $customization['fabric_id'],
                'p3' => $customization['fabric_id'],
                'p4' => $customization['fabric_id']
            ]);

            $db->commit();

            echo json_encode([
                'success'      => true,
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'message'      => "Your Custom Order Request #{$orderNumber} has been submitted! Our tailoring & logistics team will review and contact you promptly.",
                'redirect_url' => BASE_URL . '/dashboard/orders'
            ]);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error placing order request: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * OPTION 2 (Step 1): Initialize Razorpay Online Payment
     * Generates Razorpay order and returns client credentials for Razorpay Modal
     */
    public function razorpayInitAjax()
    {
        header('Content-Type: application/json');

        if (!Session::get('user_id')) {
            echo json_encode([
                'success'       => false, 
                'require_login' => true,
                'message'       => 'Please log in to proceed with online payment.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $fabricId = (int)($input['fabric_id'] ?? 0);
        $customizationId = (int)($input['customization_id'] ?? 0);
        $sizes = $input['sizes'] ?? [];
        $sets = $input['sets'] ?? [];
        $fabricQuantity = max(1, (int)($input['fabric_quantity'] ?? 1));
        $shippingAddress = trim($input['shipping_address'] ?? 'Wholesale Delivery');
        $customerNotes = trim($input['notes'] ?? '');

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            echo json_encode(['success' => false, 'message' => 'Fabric customization rule not found']);
            exit;
        }

        $packagingOption = $input['packaging_option'] ?? 'with_cover';
        $withoutCover = ($packagingOption === 'without_cover' || !empty($input['without_cover']));
        $discountPct = (float)($customization['without_cover_discount_percentage'] ?? 0.0);

        $calc = $this->service->calculateConsumption($customization, $sizes, (float)$customization['fabric_stock'], $fabricQuantity, $sets);
        if (!$calc['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $calc['errors'])]);
            exit;
        }
        $fabricQuantity = (int)($calc['fabric_quantity'] ?? $fabricQuantity);

        $pricing = $this->service->calculatePricing(
            (float)$customization['fabric_price'],
            (float)$calc['required_fabric'],
            0.00,
            (int)$calc['total_pieces'],
            5.0,
            $withoutCover,
            $discountPct
        );

        $db = Database::getInstance();
        $user = $this->resolveValidUser($db);
        $userId = (int)$user['id'];

        $receiptId = 'CUST_' . strtoupper(substr(uniqid(), -8));
        $razorpayService = new RazorpayService();

        $fabricTitle = $customization['fabric_name'] ?? 'Custom Order';

        $rzpRes = $razorpayService->createOrder(
            $pricing['total'],
            $receiptId,
            [
                'fabric_id'        => $customization['fabric_id'],
                'customization_id' => $customization['id'],
                'fabric_quantity'  => $fabricQuantity,
                'fabric_name'      => substr($fabricTitle, 0, 40),
                'total_pieces'     => $calc['total_pieces'],
                'required_fabric'  => $calc['required_fabric'],
                'user_id'          => $userId
            ]
        );

        if (empty($rzpRes['success']) || empty($rzpRes['order']['id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to create Razorpay payment order: ' . ($rzpRes['error'] ?? 'Gateway request failed')
            ]);
            exit;
        }

        $rzpOrderId = $rzpRes['order']['id'];

        echo json_encode([
            'success'           => true,
            'razorpay_key'      => defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '',
            'razorpay_order_id' => $rzpOrderId,
            'amount_in_paise'   => (int)round($pricing['total'] * 100),
            'amount'            => $pricing['total'],
            'currency'          => defined('RAZORPAY_CURRENCY') ? RAZORPAY_CURRENCY : 'INR',
            'program_name'      => $fabricTitle,
            'user'              => [
                'name'    => $user['name'] ?? Session::get('user_name') ?? 'Wholesale Customer',
                'email'   => $user['email'] ?? '',
                'phone'   => $user['phone'] ?? ''
            ]
        ]);
        exit;
    }

    /**
     * OPTION 2 (Step 2): Verify Razorpay Payment and Record Paid Custom Order
     */
    public function razorpayVerifyAjax()
    {
        header('Content-Type: application/json');

        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $fabricId = (int)($input['fabric_id'] ?? 0);
        $customizationId = (int)($input['customization_id'] ?? 0);
        $sizes = $input['sizes'] ?? [];
        $sets = $input['sets'] ?? [];
        $fabricQuantity = max(1, (int)($input['fabric_quantity'] ?? 1));
        $shippingAddress = trim($input['shipping_address'] ?? 'Wholesale Delivery');
        $customerNotes = trim($input['notes'] ?? '');
        $razorpayPaymentId = trim($input['razorpay_payment_id'] ?? '');
        $razorpayOrderId = trim($input['razorpay_order_id'] ?? '');
        $razorpaySignature = trim($input['razorpay_signature'] ?? '');

        if (empty($razorpayPaymentId)) {
            echo json_encode(['success' => false, 'message' => 'Missing Razorpay payment ID.']);
            exit;
        }

        $customization = null;
        if ($fabricId > 0) {
            $customization = $this->model->getByFabricId($fabricId);
        } elseif ($customizationId > 0) {
            $customization = $this->model->getById($customizationId);
        }

        if (!$customization) {
            echo json_encode(['success' => false, 'message' => 'Fabric customization rule not found']);
            exit;
        }

        $packagingOption = $input['packaging_option'] ?? 'with_cover';
        $withoutCover = ($packagingOption === 'without_cover' || !empty($input['without_cover']));
        $discountPct = (float)($customization['without_cover_discount_percentage'] ?? 0.0);

        $calc = $this->service->calculateConsumption($customization, $sizes, (float)$customization['fabric_stock'], $fabricQuantity, $sets);
        if (!$calc['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $calc['errors'])]);
            exit;
        }

        $pricing = $this->service->calculatePricing(
            (float)$customization['fabric_price'],
            (float)$calc['required_fabric'],
            0.00,
            (int)$calc['total_pieces'],
            5.0,
            $withoutCover,
            $discountPct
        );

        $delivery = $this->service->calculateDeliveryTimeline(
            (int)$calc['total_pieces'],
            (int)($customization['delivery_days'] ?? 7),
            (int)($customization['delivery_base_pieces'] ?? 100)
        );

        // Verify Signature if signature is provided and not dummy mock
        $razorpayService = new RazorpayService();
        if (!empty($razorpaySignature) && strpos($razorpayOrderId, 'order_mock_') === false) {
            $isValidSig = $razorpayService->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);
            if (!$isValidSig) {
                // If signature failed, reject
                echo json_encode(['success' => false, 'message' => 'Razorpay payment verification failed (invalid signature).']);
                exit;
            }
        }

        $labelQuantities = $input['label_quantities'] ?? [];

        $db = Database::getInstance();
        $userId = $this->resolveValidUserId($db);

        try {
            $db->beginTransaction();

            $orderNumber = $this->generateCustomizeOrderNumber($db, 'GBC');

            $customizationData = [
                'customization_id'    => $customization['id'],
                'fabric_quantity'     => $fabricQuantity,
                'program_name'        => $customization['program_name'] ?? $customization['fabric_name'] ?? 'Custom Order',
                'garment_type'        => $customization['garment_type'],
                'feeding_type'        => $customization['feeding_type'],
                'packaging_option'    => $packagingOption,
                'without_cover'       => $withoutCover,
                'discount_percentage' => $discountPct,
                'total_saved'         => $pricing['total_saved'] ?? 0.00,
                'delivery_timeline'   => $delivery,
                'fabric_name'         => $customization['fabric_name'],
                'fabric_sku'          => $customization['fabric_sku'],
                'fabric_unit'         => $customization['unit'],
                'total_pieces'        => $calc['total_pieces'],
                'required_fabric'     => $calc['required_fabric'],
                'base_consumption'    => $calc['base_consumption'],
                'wastage_percentage'  => $calc['wastage_percentage'],
                'wastage_amount'      => $calc['wastage_amount'],
                'price_per_unit'      => $pricing['price_per_unit'],
                'original_price'      => $pricing['original_price_per_unit'],
                'fabric_cost'         => $pricing['fabric_cost'],
                'stitching_cost'      => $pricing['stitching_cost'],
                'gst_amount'          => $pricing['gst_amount'],
                'grand_total'         => $pricing['total'],
                'order_mode'          => 'razorpay_paid',
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_order_id'   => $razorpayOrderId,
                'customer_notes'      => $customerNotes,
                'sizes'               => $calc['breakdown'],
                'sets'                => $sets,
                'label_quantities'    => $labelQuantities,
                'labels'              => $labelQuantities
            ];

            $orderStmt = $db->prepare("
                INSERT INTO orders (
                    user_id, order_number, total_amount, grand_total, shipping_address, 
                    status, payment_status, is_stock_reduced, razorpay_order_id, razorpay_payment_id, 
                    notes, created_at
                ) VALUES (?, ?, ?, ?, ?, 'placed', 'paid', 1, ?, ?, ?, NOW())
            ");

            $orderStmt->execute([
                $userId,
                $orderNumber,
                $pricing['subtotal'],
                $pricing['total'],
                $shippingAddress,
                $razorpayOrderId,
                $razorpayPaymentId,
                "Online Paid Custom Garment ({$customization['program_name']}) - Payment ID: " . $razorpayPaymentId
            ]);

            $orderId = (int)$db->lastInsertId();

            // Insert into order_items
            $itemStmt = $db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, customization_id, customization_data, 
                    quantity, unit_price, total_price
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $itemStmt->execute([
                $orderId,
                $customization['fabric_id'],
                $customization['id'],
                json_encode($customizationData),
                $calc['total_pieces'],
                $customization['fabric_price'],
                $pricing['total']
            ]);

            // Decrement fabric stock
            $stockStmt = $db->prepare("
                UPDATE products 
                SET stock_quantity = GREATEST(0, stock_quantity - ?) 
                WHERE id = ?
            ");
            $stockStmt->execute([(int)$calc['required_fabric'], $customization['fabric_id']]);

            // Sync variant stock to remaining fabric quantity
            $syncVarStmt = $db->prepare("
                UPDATE product_variants 
                SET current_stock = (SELECT stock_quantity FROM products WHERE id = :p1),
                    stock = (SELECT stock_quantity FROM products WHERE id = :p2),
                    inventory = (SELECT stock_quantity FROM products WHERE id = :p3)
                WHERE product_id = :p4
            ");
            $syncVarStmt->execute([
                'p1' => $customization['fabric_id'],
                'p2' => $customization['fabric_id'],
                'p3' => $customization['fabric_id'],
                'p4' => $customization['fabric_id']
            ]);

            $db->commit();

            echo json_encode([
                'success'      => true,
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'message'      => "Payment successful! Your custom order #{$orderNumber} is confirmed and placed.",
                'redirect_url' => BASE_URL . '/order-success?order_id=' . $orderId
            ]);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error completing paid order: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Resolve a valid user record from the database in a single query to eliminate multiple roundtrips.
     */
    private function resolveValidUser(PDO $db): array
    {
        $userId = (int)Session::get('user_id');
        if ($userId > 0) {
            $stmt = $db->prepare("SELECT id, name, email, phone FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }
        }

        // Fallback to the first active user
        $fallbackStmt = $db->query("SELECT id, name, email, phone FROM users WHERE status = 'active' ORDER BY id ASC LIMIT 1");
        $fallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            Session::set('user_id', (int)$fallback['id']);
            return $fallback;
        }

        return ['id' => $userId, 'name' => 'Wholesale Customer', 'email' => '', 'phone' => ''];
    }

    /**
     * Resolve a valid user_id from the database to strictly prevent foreign key constraint violations.
     */
    private function resolveValidUserId(PDO $db): int
    {
        $u = $this->resolveValidUser($db);
        return (int)$u['id'];
    }

    /**
     * Generates a sequential, dynamic customize order number in the format GBC000001, GBC000002, etc.
     */
    private function generateCustomizeOrderNumber(PDO $db, string $prefix = 'GBC'): string
    {
        $stmt = $db->prepare("
            SELECT order_number 
            FROM orders 
            WHERE order_number LIKE ? 
            ORDER BY id DESC 
            LIMIT 100
        ");
        $stmt->execute([$prefix . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $maxSeq = 0;
        foreach ($rows as $ordNum) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $ordNum, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $orderNumber = $prefix . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);

        // Safety check: ensure absolute uniqueness
        $checkStmt = $db->prepare("SELECT id FROM orders WHERE order_number = ? LIMIT 1");
        $checkStmt->execute([$orderNumber]);
        while ($checkStmt->fetch()) {
            $nextSeq++;
            $orderNumber = $prefix . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
            $checkStmt->execute([$orderNumber]);
        }

        return $orderNumber;
    }
}
