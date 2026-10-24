<?php
// Enable GZip output compression for all HTML/JSON responses (60-80% smaller payload)
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// If running on PHP's built-in web server, serve static files with proper MIME types & range headers
if (php_sapi_name() === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $targetFile = null;
    $path = realpath(__DIR__ . $urlPath);
    if ($path && is_file($path)) {
        $targetFile = $path;
    } else {
        $publicPath = realpath(__DIR__ . '/public' . $urlPath);
        if ($publicPath && is_file($publicPath)) {
            $targetFile = $publicPath;
        }
    }

    if ($targetFile) {
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $mimes = [
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mov'  => 'video/quicktime',
            'webp' => 'image/webp',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'pdf'  => 'application/pdf',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'woff2'=> 'font/woff2',
            'woff' => 'font/woff',
            'ttf'  => 'font/ttf'
        ];

        if (isset($mimes[$ext])) {
            $mime = $mimes[$ext];
            $mtime = filemtime($targetFile);
            $size = filesize($targetFile);
            $etag = '"' . md5($targetFile . $mtime . $size) . '"';

            header('Content-Type: ' . $mime);
            header('ETag: ' . $etag);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
            header('Cache-Control: public, max-age=86400');
            header('Accept-Ranges: bytes');

            // Return 304 if browser already has fresh copy
            if (
                (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) ||
                (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime)
            ) {
                header('HTTP/1.1 304 Not Modified');
                exit;
            }

            header('Content-Length: ' . $size);
            if (in_array($ext, ['mp4', 'webm', 'mov'])) {
                header('Content-Disposition: inline; filename="' . basename($targetFile) . '"');
            }
            readfile($targetFile);
            exit;
        }
        return false;
    }
}

// Front Controller

// 1. Load basic constants
require_once __DIR__ . '/config/constants.php';

// Load Composer autoloader (PHPMailer, etc)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Simple Autoloader (PSR-4 simplified)
spl_autoload_register(function ($className) {
    // Convert namespace to full file path
    // Example: App\Controllers\Storefront\HomeController -> app/Controllers/Storefront/HomeController.php
    $classPath = str_replace('\\', '/', $className);
    
    // Map 'Core\' namespace to the 'core' directory
    if (strpos($className, 'Core\\') === 0) {
        $classPath = 'core/' . substr($classPath, 5);
    } 
    // Map 'App\' namespace to the 'app' directory
    elseif (strpos($className, 'App\\') === 0) {
        $classPath = 'app/' . substr($classPath, 4);
    }

    $file = BASE_PATH . '/' . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Start Session
require_once CORE_PATH . '/Session.php';
Core\Session::init();

// Note: Real-time user status, permissions, and block enforcement are handled centrally in Core\Controller::__construct()

// 4. Initialize Router
$router = new Core\Router();

// Define routes (can be moved to a separate routes.php file later if it gets too large)
// Home Route
$router->get('/', 'App\Controllers\Storefront\HomeController@index');
$router->get('/clear-cache', 'App\Controllers\Storefront\HomeController@clearCache');
$router->get('/about', 'App\Controllers\Storefront\HomeController@about');
$router->get('/about-us', 'App\Controllers\Storefront\HomeController@about');
$router->get('/terms', 'App\Controllers\Storefront\HomeController@terms');
$router->get('/terms-and-conditions', 'App\Controllers\Storefront\HomeController@terms');
$router->get('/term-and-condition', 'App\Controllers\Storefront\HomeController@terms');
$router->get('/privacy', 'App\Controllers\Storefront\HomeController@privacyPolicy');
$router->get('/privacy-policy', 'App\Controllers\Storefront\HomeController@privacyPolicy');
$router->get('/privacy-and-policies', 'App\Controllers\Storefront\HomeController@privacyPolicy');
$router->get('/return-policies', 'App\Controllers\Storefront\HomeController@privacyPolicy');
$router->get('/return-policy', 'App\Controllers\Storefront\HomeController@privacyPolicy');
$router->get('/size-chart', 'App\Controllers\Storefront\HomeController@sizeChart');
$router->get('/api/size-chart', 'App\Controllers\Storefront\HomeController@apiSizeChart');
$router->get('/project', 'App\Controllers\Storefront\ProjectController@index');
$router->get('/projects', 'App\Controllers\Storefront\ProjectController@index');

// Admin CMS Size Chart Routes
$router->get('/admin/cms/size-charts', 'App\Controllers\Admin\SizeChartController@index');
$router->get('/admin/cms/size-charts/create', 'App\Controllers\Admin\SizeChartController@create');
$router->post('/admin/cms/size-charts/store', 'App\Controllers\Admin\SizeChartController@store');
$router->get('/admin/cms/size-charts/edit', 'App\Controllers\Admin\SizeChartController@edit');
$router->post('/admin/cms/size-charts/update', 'App\Controllers\Admin\SizeChartController@update');
$router->post('/admin/cms/size-charts/delete', 'App\Controllers\Admin\SizeChartController@delete');
$router->post('/admin/cms/size-charts/toggle', 'App\Controllers\Admin\SizeChartController@toggle');
$router->post('/admin/cms/size-charts/create-category', 'App\Controllers\Admin\SizeChartController@createCategoryAjax');

// CMS API Routes
$router->get('/api/cms/layout', 'App\Controllers\Storefront\CmsApiController@getLayout');
$router->post('/api/cms/layout', 'App\Controllers\Storefront\CmsApiController@updateLayout');

// Auth Routes
$router->get('/login', 'App\Controllers\Storefront\AuthController@showLogin');
$router->post('/login', 'App\Controllers\Storefront\AuthController@login');

// Forgot Password Routes
$router->get('/forgot-password', 'App\Controllers\Storefront\ForgotPasswordController@showEmailForm');
$router->post('/forgot-password', 'App\Controllers\Storefront\ForgotPasswordController@sendOtp');
$router->get('/verify-otp', 'App\Controllers\Storefront\ForgotPasswordController@showOtpForm');
$router->post('/verify-otp', 'App\Controllers\Storefront\ForgotPasswordController@verifyOtp');
$router->get('/reset-password', 'App\Controllers\Storefront\ForgotPasswordController@showResetForm');
$router->post('/reset-password', 'App\Controllers\Storefront\ForgotPasswordController@resetPassword');

$router->get('/register', 'App\Controllers\Storefront\AuthController@showRegister');
$router->post('/register', 'App\Controllers\Storefront\AuthController@register');
$router->get('/register/verify', 'App\Controllers\Storefront\AuthController@showVerifyOtp');
$router->post('/register/verify', 'App\Controllers\Storefront\AuthController@verifyOtp');
$router->post('/register/resend-otp', 'App\Controllers\Storefront\AuthController@resendOtp');
$router->post('/api/check-user-exists', 'App\Controllers\Storefront\AuthController@checkUserExists');

// Vendor Auth Routes
$router->get('/vendor-register', 'App\Controllers\Storefront\VendorAuthController@showRegister');
$router->post('/vendor-register', 'App\Controllers\Storefront\VendorAuthController@register');
$router->get('/vendor-register/verify', 'App\Controllers\Storefront\VendorAuthController@showVerifyOtp');
$router->post('/vendor-register/verify', 'App\Controllers\Storefront\VendorAuthController@verifyOtp');
$router->post('/vendor-register/resend-otp', 'App\Controllers\Storefront\VendorAuthController@resendOtp');

$router->get('/vendor/register', 'App\Controllers\Storefront\VendorAuthController@showRegister');
$router->post('/vendor/register', 'App\Controllers\Storefront\VendorAuthController@register');
$router->get('/vendor/register/verify', 'App\Controllers\Storefront\VendorAuthController@showVerifyOtp');
$router->post('/vendor/register/verify', 'App\Controllers\Storefront\VendorAuthController@verifyOtp');
$router->post('/vendor/register/resend-otp', 'App\Controllers\Storefront\VendorAuthController@resendOtp');
$router->get('/catalog', 'App\Controllers\Storefront\CatalogController@index');
$router->get('/category', 'App\Controllers\Storefront\CatalogController@index');
$router->get('/catalog/ajax-attributes', 'App\Controllers\Storefront\CatalogController@ajaxAttributes');
$router->get('/api/search', 'App\Controllers\Storefront\CatalogController@ajaxSearch');
$router->get('/product', 'App\Controllers\Storefront\CatalogController@view');
$router->post('/logout', 'App\Controllers\Storefront\AuthController@logout');
$router->get('/logout', 'App\Controllers\Storefront\AuthController@logout');

// Storefront Fabric Customization Routes
$router->get('/customize', 'App\Controllers\Storefront\FabricCustomizationController@customizeListing');
$router->get('/customize/fabric/{id}', 'App\Controllers\Storefront\FabricCustomizationController@customizeWorkshop');
$router->get('/fabrics/customize', 'App\Controllers\Storefront\FabricCustomizationController@customizeWorkshop');
$router->get('/fabrics/customize/{id}', 'App\Controllers\Storefront\FabricCustomizationController@customizeWorkshop');
$router->get('/fabrics/customize/checkout', 'App\Controllers\Storefront\FabricCustomizationController@checkoutPage');
$router->post('/fabrics/customize/checkout', 'App\Controllers\Storefront\FabricCustomizationController@checkoutPage');
$router->post('/fabrics/customization/calculate', 'App\Controllers\Storefront\FabricCustomizationController@calculateAjax');
$router->post('/fabrics/customization/request-order', 'App\Controllers\Storefront\FabricCustomizationController@requestOrderAjax');
$router->post('/fabrics/customization/razorpay-init', 'App\Controllers\Storefront\FabricCustomizationController@razorpayInitAjax');
$router->post('/fabrics/customization/razorpay-verify', 'App\Controllers\Storefront\FabricCustomizationController@razorpayVerifyAjax');

// Wishlist Routes
$router->get('/wishlist', 'App\Controllers\Storefront\WishlistController@index');
$router->post('/wishlist/toggle', 'App\Controllers\Storefront\WishlistController@toggle');
$router->get('/wishlist/ajax-get', 'App\Controllers\Storefront\WishlistController@ajaxGet');

// Dashboard Routes
$router->get('/dashboard', 'App\Controllers\Storefront\DashboardController@index');
$router->get('/dashboard/profile', 'App\Controllers\Storefront\DashboardController@profile');
$router->post('/dashboard/profile/update', 'App\Controllers\Storefront\DashboardController@updateProfile');
$router->post('/dashboard/profile/check-email', 'App\Controllers\Storefront\DashboardController@checkEmailAvailability');
$router->post('/dashboard/profile/request-email-change', 'App\Controllers\Storefront\DashboardController@requestEmailChange');
$router->post('/dashboard/profile/resend-email-otp', 'App\Controllers\Storefront\DashboardController@resendEmailChangeOtp');
$router->post('/dashboard/profile/verify-email-change', 'App\Controllers\Storefront\DashboardController@verifyEmailChange');
$router->get('/dashboard/orders', 'App\Controllers\Storefront\DashboardController@orders');
$router->get('/dashboard/orders/view', 'App\Controllers\Storefront\DashboardController@orderDetail');
$router->post('/dashboard/orders/cancel', 'App\Controllers\Storefront\DashboardController@cancelOrder');
$router->post('/dashboard/orders/request-refund', 'App\Controllers\Storefront\RefundController@submitCancellation');
$router->get('/dashboard/orders/invoice', 'App\Controllers\Storefront\InvoiceController@download');
$router->get('/dashboard/settings', 'App\Controllers\Storefront\DashboardController@settings');
$router->get('/dashboard/addresses', 'App\Controllers\Storefront\DashboardController@addresses');
$router->post('/dashboard/addresses/add', 'App\Controllers\Storefront\DashboardController@addAddress');
$router->post('/dashboard/addresses/edit', 'App\Controllers\Storefront\DashboardController@editAddress');
$router->post('/dashboard/addresses/delete', 'App\Controllers\Storefront\DashboardController@deleteAddress');
$router->post('/dashboard/addresses/set-default', 'App\Controllers\Storefront\DashboardController@setDefaultAddress');

// Review Routes (Storefront)
$router->get('/dashboard/reviews', 'App\Controllers\Storefront\ReviewController@index');
$router->post('/dashboard/reviews/submit', 'App\Controllers\Storefront\ReviewController@ajaxSubmitReview');
$router->post('/reviews/vote-helpful', 'App\Controllers\Storefront\ReviewController@voteHelpful');

// Cart Routes
$router->get('/cart', 'App\Controllers\Storefront\CartController@index');
$router->post('/cart/add', 'App\Controllers\Storefront\CartController@add');
$router->post('/cart/update', 'App\Controllers\Storefront\CartController@update');
$router->post('/cart/remove', 'App\Controllers\Storefront\CartController@remove');
$router->post('/cart/ajax-add', 'App\Controllers\Storefront\CartController@ajaxAdd');
$router->post('/cart/ajax-update', 'App\Controllers\Storefront\CartController@ajaxUpdate');
$router->post('/cart/ajax-remove', 'App\Controllers\Storefront\CartController@ajaxRemove');
$router->get('/cart/ajax-get', 'App\Controllers\Storefront\CartController@ajaxGet');
$router->post('/cart/switch-variant', 'App\Controllers\Storefront\CartController@switchVariant');
$router->post('/cart/ajax-switch-variant', 'App\Controllers\Storefront\CartController@ajaxSwitchVariant');

// Checkout Routes
$router->get('/checkout', 'App\Controllers\Storefront\CheckoutController@index');
$router->get('/checkout/place-order', 'App\Controllers\Storefront\CheckoutController@placeOrder');
$router->post('/checkout/place-order', 'App\Controllers\Storefront\CheckoutController@placeOrder');
$router->get('/checkout/verify', 'App\Controllers\Storefront\CheckoutController@verifyPayment');
$router->post('/checkout/verify', 'App\Controllers\Storefront\CheckoutController@verifyPayment');
$router->get('/checkout/retry', 'App\Controllers\Storefront\CheckoutController@retryPayment');

$router->get('/order-success', 'App\Controllers\Storefront\CheckoutController@success');
$router->post('/checkout/address/add', 'App\Controllers\Storefront\CheckoutController@addAddress');
$router->post('/checkout/address/edit', 'App\Controllers\Storefront\CheckoutController@editAddress');

// Admin Order Rules Routes
$router->get('/api/subcategories', 'App\Controllers\Admin\OrderRuleController@apiSecondCategories');

$router->get('/admin/order-rules', 'App\Controllers\Admin\OrderRuleController@index');
$router->get('/admin/order-rules/create', 'App\Controllers\Admin\OrderRuleController@create');
$router->post('/admin/order-rules/store', 'App\Controllers\Admin\OrderRuleController@store');
$router->get('/admin/order-rules/edit', 'App\Controllers\Admin\OrderRuleController@edit');
$router->post('/admin/order-rules/update', 'App\Controllers\Admin\OrderRuleController@update');
$router->post('/admin/order-rules/delete', 'App\Controllers\Admin\OrderRuleController@delete');

// Fee Rules Routes
$router->get('/admin/fee-rules', 'App\Controllers\Admin\FeeRuleController@index');
$router->get('/admin/fee-rules/create', 'App\Controllers\Admin\FeeRuleController@create');
$router->post('/admin/fee-rules/store', 'App\Controllers\Admin\FeeRuleController@store');
$router->get('/admin/fee-rules/edit', 'App\Controllers\Admin\FeeRuleController@edit');
$router->post('/admin/fee-rules/update', 'App\Controllers\Admin\FeeRuleController@update');
$router->post('/admin/fee-rules/delete', 'App\Controllers\Admin\FeeRuleController@delete');
$router->post('/admin/fee-rules/add-category', 'App\Controllers\Admin\FeeRuleController@addCategory');
$router->post('/admin/fee-rules/delete-category', 'App\Controllers\Admin\FeeRuleController@deleteCategory');


// Checkout Fee Calculation API
$router->get('/api/calculate-checkout-fees', 'App\Controllers\Storefront\FeeApiController@calculate');
$router->post('/api/calculate-checkout-fees', 'App\Controllers\Storefront\FeeApiController@calculate');

// Notification API Routes
$router->get('/api/notifications', 'App\Controllers\Storefront\NotificationController@getAjax');
$router->post('/api/notifications/read', 'App\Controllers\Storefront\NotificationController@markAsRead');

// Admin Routes
$router->get('/admin/dashboard', 'App\Controllers\Admin\DashboardController@index');

$router->get('/admin/staff', 'App\Controllers\Admin\StaffController@index');
$router->get('/admin/staff/create', 'App\Controllers\Admin\StaffController@create');
$router->post('/admin/staff/store', 'App\Controllers\Admin\StaffController@store');
$router->get('/admin/staff/edit', 'App\Controllers\Admin\StaffController@edit');
$router->post('/admin/staff/update', 'App\Controllers\Admin\StaffController@update');
$router->post('/admin/staff/delete', 'App\Controllers\Admin\StaffController@delete');
$router->post('/admin/staff/toggle-status', 'App\Controllers\Admin\StaffController@toggleStatus');

$router->get('/admin/staff/roles', 'App\Controllers\Admin\StaffController@roles');
$router->post('/admin/staff/roles/store', 'App\Controllers\Admin\StaffController@storeRole');
$router->post('/admin/staff/roles/delete', 'App\Controllers\Admin\StaffController@deleteRole');

$router->get('/admin/buyers', 'App\Controllers\Admin\BuyerController@index');
$router->get('/admin/buyers/pending', 'App\Controllers\Admin\BuyerController@pending');
$router->get('/admin/buyers/view', 'App\Controllers\Admin\BuyerController@view');
$router->post('/admin/buyers/approve', 'App\Controllers\Admin\BuyerController@approve');
$router->post('/admin/buyers/reject', 'App\Controllers\Admin\BuyerController@reject');
$router->post('/admin/buyers/toggle-block', 'App\Controllers\Admin\BuyerController@toggleBlock');
$router->get('/admin/buyers/verify', 'App\Controllers\Admin\BuyerController@ajaxVerify');

// Admin Vendor Routes
$router->get('/admin/vendors', 'App\Controllers\Admin\VendorAdminController@index');
$router->get('/admin/vendors/dashboard', 'App\Controllers\Admin\VendorAdminController@dashboard');
$router->get('/admin/vendors/export-revenue', 'App\Controllers\Admin\VendorAdminController@exportRevenue');
$router->get('/admin/vendors/pending', 'App\Controllers\Admin\VendorAdminController@pending');
$router->get('/admin/vendors/view', 'App\Controllers\Admin\VendorAdminController@view');
$router->post('/admin/vendors/approve', 'App\Controllers\Admin\VendorAdminController@approve');
$router->post('/admin/vendors/reject', 'App\Controllers\Admin\VendorAdminController@reject');

// Vendor Portal Shortcut & Direct Alias Routes
$router->get('/vendor', 'App\Controllers\Admin\DashboardController@vendorPortalRedirect');
$router->get('/vendor/dashboard', 'App\Controllers\Admin\DashboardController@vendorDashboard');
$router->get('/vendor-dashboard', 'App\Controllers\Admin\DashboardController@vendorDashboard');
$router->get('/vendor/products', 'App\Controllers\Admin\ProductController@index');
$router->get('/vendor/orders', 'App\Controllers\Admin\OrderController@index');
$router->get('/vendor/inventory', 'App\Controllers\Admin\InventoryController@index');
$router->get('/vendors/dashboard', 'App\Controllers\Admin\VendorAdminController@dashboard');
$router->get('/vendors', 'App\Controllers\Admin\VendorAdminController@index');

// Admin Catalog Dashboard
$router->get('/admin/catalog', 'App\Controllers\Admin\CatalogController@index');
$router->post('/admin/catalog/category', 'App\Controllers\Admin\CatalogController@storeCategory');
$router->post('/admin/catalog/product', 'App\Controllers\Admin\CatalogController@storeProduct');

// Admin Catalog Routes - Categories
$router->get('/admin/catalog/categories', 'App\Controllers\Admin\CategoryController@index');
$router->get('/admin/catalog/categories/check-delete', 'App\Controllers\Admin\CategoryController@checkDelete');
$router->post('/admin/catalog/categories/store', 'App\Controllers\Admin\CategoryController@store');
$router->post('/admin/catalog/categories/update', 'App\Controllers\Admin\CategoryController@update');
$router->post('/admin/catalog/categories/delete', 'App\Controllers\Admin\CategoryController@delete');
$router->post('/admin/catalog/categories/global-cart-limit', 'App\Controllers\Admin\CategoryController@updateGlobalCartLimit');

// Admin & Vendor Category Requests
$router->get('/admin/catalog/category-requests', 'App\Controllers\Admin\CategoryRequestController@index');
$router->post('/admin/catalog/category-requests/store', 'App\Controllers\Admin\CategoryRequestController@store');
$router->post('/admin/catalog/categories/request', 'App\Controllers\Admin\CategoryRequestController@store');
$router->get('/admin/catalog/category-requests/my-requests', 'App\Controllers\Admin\CategoryRequestController@myRequests');
$router->post('/admin/catalog/category-requests/approve', 'App\Controllers\Admin\CategoryRequestController@approve');
$router->post('/admin/catalog/category-requests/reject', 'App\Controllers\Admin\CategoryRequestController@reject');

// Admin Catalog Routes - SubCategories
$router->get('/admin/catalog/subcategories', 'App\Controllers\Admin\SubCategoryController@index');
$router->get('/admin/catalog/subcategories/api-get-by-category', 'App\Controllers\Admin\SubCategoryController@ajaxGetByCategory');
$router->post('/admin/catalog/subcategories/store', 'App\Controllers\Admin\SubCategoryController@store');
$router->post('/admin/catalog/subcategories/update', 'App\Controllers\Admin\SubCategoryController@update');
$router->post('/admin/catalog/subcategories/delete', 'App\Controllers\Admin\SubCategoryController@delete');

// Admin Catalog Routes - Attributes
$router->get('/admin/catalog/attributes', 'App\Controllers\Admin\AttributeController@index');
$router->get('/admin/catalog/attributes/create', 'App\Controllers\Admin\AttributeController@create');
$router->get('/admin/catalog/attributes/edit', 'App\Controllers\Admin\AttributeController@edit');
$router->get('/admin/catalog/attributes/api-get-by-category', 'App\Controllers\Admin\AttributeController@ajaxGetForCategory');
$router->post('/admin/catalog/attributes/store', 'App\Controllers\Admin\AttributeController@store');
$router->post('/admin/catalog/attributes/update', 'App\Controllers\Admin\AttributeController@update');
$router->post('/admin/catalog/attributes/delete', 'App\Controllers\Admin\AttributeController@delete');
$router->post('/admin/catalog/attributes/store-value', 'App\Controllers\Admin\AttributeController@storeValue');
$router->post('/admin/catalog/attributes/delete-value', 'App\Controllers\Admin\AttributeController@deleteValue');

// Inventory Routes
$router->get('/admin/inventory', 'App\Controllers\Admin\InventoryController@index');
$router->get('/admin/inventory/view', 'App\Controllers\Admin\InventoryController@view');
$router->get('/admin/inventory/variant-details', 'App\Controllers\Admin\InventoryController@getVariantDetails');
$router->post('/admin/inventory/update', 'App\Controllers\Admin\InventoryController@update');

// Admin Catalog Routes - Products
$router->get('/admin/catalog/products', 'App\Controllers\Admin\ProductController@index');
$router->get('/admin/catalog/products/create', 'App\Controllers\Admin\ProductController@create');
$router->get('/admin/catalog/products/edit', 'App\Controllers\Admin\ProductController@edit');
$router->get('/admin/catalog/products/view', 'App\Controllers\Admin\ProductController@view');
$router->post('/admin/catalog/products/store', 'App\Controllers\Admin\ProductController@store');
$router->post('/admin/catalog/products/update', 'App\Controllers\Admin\ProductController@update');
$router->post('/admin/catalog/products/delete', 'App\Controllers\Admin\ProductController@delete');
$router->post('/admin/catalog/products/delete-image', 'App\Controllers\Admin\ProductController@deleteImage');
$router->post('/admin/catalog/products/approve', 'App\Controllers\Admin\ProductController@approve');
$router->post('/admin/catalog/products/reject', 'App\Controllers\Admin\ProductController@reject');

// Admin Bill Modifications (Separate Module)
$router->get('/admin/order-modifications', 'App\Controllers\Admin\OrderModificationController@index');
$router->get('/admin/order-modifications/export-detailed', 'App\Controllers\Admin\OrderModificationController@exportDetailed');
$router->get('/admin/order-modifications/create', 'App\Controllers\Admin\OrderModificationController@create');
$router->post('/admin/order-modifications/store', 'App\Controllers\Admin\OrderModificationController@store');
$router->get('/admin/order-modifications/view', 'App\Controllers\Admin\OrderModificationController@view');
$router->get('/admin/order-modifications/search-products', 'App\Controllers\Admin\OrderModificationController@searchProducts');
$router->get('/admin/order-modifications/get-subcategories', 'App\Controllers\Admin\OrderModificationController@getSubcategories');


// Storefront: Buyer Order Modification Review
$router->get('/order-modification/review', 'App\Controllers\Storefront\OrderModificationController@review');
$router->post('/order-modification/respond', 'App\Controllers\Storefront\OrderModificationController@respond');

// Super Admin Media Manager
$router->post('/admin/media/upload', 'App\Controllers\Admin\MediaController@upload');
$router->post('/admin/media/delete', 'App\Controllers\Admin\MediaController@delete');

// CMS Builder Route
$router->get('/admin/cms/builder', 'App\Controllers\Admin\CmsAdminController@builder');
$router->post('/admin/cms/delete-component', 'App\Controllers\Admin\CmsAdminController@deleteComponent');

// CMS Hero Banners
$router->get('/admin/cms/hero-banners', 'App\Controllers\Admin\HeroBannerController@index');
$router->get('/admin/cms/hero-banners/create', 'App\Controllers\Admin\HeroBannerController@create');
$router->post('/admin/cms/hero-banners/store', 'App\Controllers\Admin\HeroBannerController@store');
$router->get('/admin/cms/hero-banners/edit', 'App\Controllers\Admin\HeroBannerController@edit');
$router->post('/admin/cms/hero-banners/update', 'App\Controllers\Admin\HeroBannerController@update');
$router->post('/admin/cms/hero-banners/delete', 'App\Controllers\Admin\HeroBannerController@delete');

// CMS Main Banners
$router->get('/admin/cms/main-banners', 'App\Controllers\Admin\MainBannerController@index');
$router->get('/admin/cms/main-banners/create', 'App\Controllers\Admin\MainBannerController@create');
$router->post('/admin/cms/main-banners/store', 'App\Controllers\Admin\MainBannerController@store');
$router->get('/admin/cms/main-banners/edit', 'App\Controllers\Admin\MainBannerController@edit');
$router->post('/admin/cms/main-banners/update', 'App\Controllers\Admin\MainBannerController@update');
$router->post('/admin/cms/main-banners/delete', 'App\Controllers\Admin\MainBannerController@delete');

// CMS Navbars
$router->get('/admin/cms/navbars', 'App\Controllers\Admin\NavbarController@index');
$router->get('/admin/cms/navbars/create', 'App\Controllers\Admin\NavbarController@create');
$router->post('/admin/cms/navbars/store', 'App\Controllers\Admin\NavbarController@store');
$router->get('/admin/cms/navbars/edit', 'App\Controllers\Admin\NavbarController@edit');
$router->post('/admin/cms/navbars/update', 'App\Controllers\Admin\NavbarController@update');
$router->post('/admin/cms/navbars/delete', 'App\Controllers\Admin\NavbarController@delete');

// CMS Top Bars
$router->get('/admin/cms/topbars', 'App\Controllers\Admin\TopBarController@index');
$router->get('/admin/cms/topbars/create', 'App\Controllers\Admin\TopBarController@create');
$router->post('/admin/cms/topbars/store', 'App\Controllers\Admin\TopBarController@store');
$router->get('/admin/cms/topbars/edit', 'App\Controllers\Admin\TopBarController@edit');
$router->post('/admin/cms/topbars/update', 'App\Controllers\Admin\TopBarController@update');
$router->post('/admin/cms/topbars/delete', 'App\Controllers\Admin\TopBarController@delete');

// CMS Footers
$router->get('/admin/cms/footers', 'App\Controllers\Admin\FooterController@index');
$router->get('/admin/cms/footers/create', 'App\Controllers\Admin\FooterController@create');
$router->post('/admin/cms/footers/store', 'App\Controllers\Admin\FooterController@store');
$router->get('/admin/cms/footers/edit', 'App\Controllers\Admin\FooterController@edit');
$router->post('/admin/cms/footers/update', 'App\Controllers\Admin\FooterController@update');
$router->post('/admin/cms/footers/delete', 'App\Controllers\Admin\FooterController@delete');

// CMS About Us
$router->get('/admin/cms/about-us', 'App\Controllers\Admin\AboutUsController@index');
$router->get('/admin/cms/about-us/create', 'App\Controllers\Admin\AboutUsController@create');
$router->post('/admin/cms/about-us/store', 'App\Controllers\Admin\AboutUsController@store');
$router->get('/admin/cms/about-us/edit', 'App\Controllers\Admin\AboutUsController@edit');
$router->post('/admin/cms/about-us/update', 'App\Controllers\Admin\AboutUsController@update');
$router->post('/admin/cms/about-us/delete', 'App\Controllers\Admin\AboutUsController@delete');

// CMS Reviews
$router->get('/admin/cms/reviews', 'App\Controllers\Admin\CmsReviewController@index');
$router->get('/admin/cms/reviews/create', 'App\Controllers\Admin\CmsReviewController@create');
$router->post('/admin/cms/reviews/store', 'App\Controllers\Admin\CmsReviewController@store');
$router->get('/admin/cms/reviews/edit', 'App\Controllers\Admin\CmsReviewController@edit');
$router->post('/admin/cms/reviews/update', 'App\Controllers\Admin\CmsReviewController@update');
$router->post('/admin/cms/reviews/delete', 'App\Controllers\Admin\CmsReviewController@delete');

// CMS Projects (Our Project)
$router->get('/admin/cms/projects', 'App\Controllers\Admin\ProjectController@index');
$router->get('/admin/cms/projects/create', 'App\Controllers\Admin\ProjectController@create');
$router->post('/admin/cms/projects/store', 'App\Controllers\Admin\ProjectController@store');
$router->get('/admin/cms/projects/edit', 'App\Controllers\Admin\ProjectController@edit');
$router->post('/admin/cms/projects/update', 'App\Controllers\Admin\ProjectController@update');
$router->post('/admin/cms/projects/delete', 'App\Controllers\Admin\ProjectController@delete');
$router->post('/admin/cms/projects/toggle-status', 'App\Controllers\Admin\ProjectController@toggleStatus');

// CMS Product Card Settings (Global)
$router->get('/admin/cms/product-card-settings', 'App\Controllers\Admin\ProductCardSettingsController@index');
$router->post('/admin/cms/product-card-settings/update', 'App\Controllers\Admin\ProductCardSettingsController@update');

// CMS Product Carousels
$router->get('/admin/cms/product-carousels', 'App\Controllers\Admin\ProductCarouselController@index');
$router->get('/admin/cms/product-carousels/create', 'App\Controllers\Admin\ProductCarouselController@create');
$router->post('/admin/cms/product-carousels/store', 'App\Controllers\Admin\ProductCarouselController@store');
$router->get('/admin/cms/product-carousels/edit', 'App\Controllers\Admin\ProductCarouselController@edit');
$router->post('/admin/cms/product-carousels/update', 'App\Controllers\Admin\ProductCarouselController@update');
$router->post('/admin/cms/product-carousels/delete', 'App\Controllers\Admin\ProductCarouselController@delete');

// CMS Category Carousels
$router->get('/admin/cms/category-carousels', 'App\Controllers\Admin\CategoryCarouselController@index');
$router->get('/admin/cms/category-carousels/create', 'App\Controllers\Admin\CategoryCarouselController@create');
$router->post('/admin/cms/category-carousels/store', 'App\Controllers\Admin\CategoryCarouselController@store');
$router->get('/admin/cms/category-carousels/edit', 'App\Controllers\Admin\CategoryCarouselController@edit');
$router->post('/admin/cms/category-carousels/update', 'App\Controllers\Admin\CategoryCarouselController@update');
$router->post('/admin/cms/category-carousels/delete', 'App\Controllers\Admin\CategoryCarouselController@delete');

// Categories Grid (CMS)
$router->get('/admin/cms/categories-grid', 'App\Controllers\Admin\CategoriesGridController@index');
$router->get('/admin/cms/categories-grid/create', 'App\Controllers\Admin\CategoriesGridController@create');
$router->post('/admin/cms/categories-grid/store', 'App\Controllers\Admin\CategoriesGridController@store');
$router->get('/admin/cms/categories-grid/edit', 'App\Controllers\Admin\CategoriesGridController@edit');
$router->post('/admin/cms/categories-grid/update', 'App\Controllers\Admin\CategoriesGridController@update');
$router->post('/admin/cms/categories-grid/delete', 'App\Controllers\Admin\CategoriesGridController@delete');

// CMS Reels Container
$router->get('/admin/cms/reels', 'App\Controllers\Admin\ReelsController@index');
$router->get('/admin/cms/reels/create', 'App\Controllers\Admin\ReelsController@create');
$router->post('/admin/cms/reels/store', 'App\Controllers\Admin\ReelsController@store');
$router->get('/admin/cms/reels/edit', 'App\Controllers\Admin\ReelsController@edit');
$router->post('/admin/cms/reels/update', 'App\Controllers\Admin\ReelsController@update');
$router->post('/admin/cms/reels/delete', 'App\Controllers\Admin\ReelsController@delete');
$router->post('/admin/cms/reels/toggle-status', 'App\Controllers\Admin\ReelsController@toggleStatus');

$router->get('/admin/media', 'App\Controllers\Admin\MediaController@index');
$router->get('/admin/media/ajax-get', 'App\Controllers\Admin\MediaController@ajaxGet');
$router->get('/admin/media/check-usage', 'App\Controllers\Admin\MediaController@checkUsage');

// Admin Orders
$router->get('/admin/orders', 'App\Controllers\Admin\OrderController@index');
$router->get('/admin/orders/pending', 'App\Controllers\Admin\OrderController@pending');
$router->get('/admin/orders/view', 'App\Controllers\Admin\OrderController@view');
$router->get('/admin/orders/invoice', 'App\Controllers\Storefront\InvoiceController@download');
$router->get('/admin/orders/print', 'App\Controllers\Admin\OrderController@printSlips');
$router->get('/admin/orders/export', 'App\Controllers\Admin\OrderController@export');
$router->get('/admin/orders/packing-videos', 'App\Controllers\Admin\OrderController@packingVideos');
$router->post('/admin/orders/cleanup-packing-videos', 'App\Controllers\Admin\OrderController@cleanupPackingVideos');
$router->post('/admin/orders/update-status', 'App\Controllers\Admin\OrderController@updateStatus');
$router->post('/admin/orders/update-packed', 'App\Controllers\Admin\OrderController@updatePacked');
$router->post('/admin/orders/update-courier', 'App\Controllers\Admin\OrderController@updateCourier');
$router->get('/admin/orders/courier-services', 'App\Controllers\Admin\OrderController@getCourierServices');
$router->post('/admin/orders/courier-services/add', 'App\Controllers\Admin\OrderController@addCourierService');
$router->post('/admin/orders/courier-services/delete', 'App\Controllers\Admin\OrderController@deleteCourierService');

// Admin Packing Slip Config & 100mm Thermal Slip Printing
$router->get('/admin/orders/packing-slip-config', 'App\Controllers\Admin\PackingSlipConfigController@index');
$router->post('/admin/orders/packing-slip-config/save', 'App\Controllers\Admin\PackingSlipConfigController@save');
$router->get('/admin/orders/packing-slip', 'App\Controllers\Admin\PackingSlipController@printSlip');
$router->get('/admin/orders/packing-slips/bulk', 'App\Controllers\Admin\PackingSlipController@printBulk');
$router->post('/admin/orders/packing-slips/bulk', 'App\Controllers\Admin\PackingSlipController@printBulk');
$router->post('/admin/orders/packing-slips/direct-print', 'App\Controllers\Admin\PackingSlipController@directPrint');

// Admin Fabric Customization Rules
$router->get('/admin/fabric-customizations', 'App\Controllers\Admin\FabricCustomizationController@index');
$router->get('/admin/fabric-customizations/create', 'App\Controllers\Admin\FabricCustomizationController@create');
$router->post('/admin/fabric-customizations/store', 'App\Controllers\Admin\FabricCustomizationController@store');
$router->get('/admin/fabric-customizations/edit', 'App\Controllers\Admin\FabricCustomizationController@edit');
$router->post('/admin/fabric-customizations/update', 'App\Controllers\Admin\FabricCustomizationController@update');
$router->post('/admin/fabric-customizations/delete', 'App\Controllers\Admin\FabricCustomizationController@delete');
$router->post('/admin/fabric-customizations/toggle-status', 'App\Controllers\Admin\FabricCustomizationController@toggleStatus');
$router->get('/admin/fabric-customizations/ajax-product-info', 'App\Controllers\Admin\FabricCustomizationController@ajaxProductInfo');

// Admin Customization Labels & Templates
$router->get('/admin/customization-labels', 'App\Controllers\Admin\CustomizationLabelController@index');
$router->get('/admin/customization-labels/create', 'App\Controllers\Admin\CustomizationLabelController@create');
$router->post('/admin/customization-labels/store', 'App\Controllers\Admin\CustomizationLabelController@store');
$router->get('/admin/customization-labels/edit', 'App\Controllers\Admin\CustomizationLabelController@edit');
$router->post('/admin/customization-labels/update', 'App\Controllers\Admin\CustomizationLabelController@update');
$router->post('/admin/customization-labels/delete', 'App\Controllers\Admin\CustomizationLabelController@delete');
$router->post('/admin/customization-labels/toggle-status', 'App\Controllers\Admin\CustomizationLabelController@toggleStatus');
$router->post('/admin/customization-labels/quick-add', 'App\Controllers\Admin\CustomizationLabelController@quickAddAjax');

// Admin Customize Orders
$router->get('/admin/customize/orders', 'App\Controllers\Admin\CustomOrderController@index');
$router->post('/admin/customize/orders/update-status', 'App\Controllers\Admin\CustomOrderController@updateStatus');
$router->post('/admin/customize/orders/update-packed', 'App\Controllers\Admin\CustomOrderController@updatePacked');

// Admin Cancellations (Step 1: review & approve/reject)
$router->get('/admin/cancellations', 'App\Controllers\Admin\CancellationController@index');
$router->post('/admin/cancellations/approve', 'App\Controllers\Admin\CancellationController@approve');
$router->post('/admin/cancellations/reject', 'App\Controllers\Admin\CancellationController@reject');

// Admin Cancellation Rules
$router->get('/admin/cancellations/rules', 'App\Controllers\Admin\CancellationRuleController@index');
$router->post('/admin/cancellations/rules/add', 'App\Controllers\Admin\CancellationRuleController@add');
$router->post('/admin/cancellations/rules/update', 'App\Controllers\Admin\CancellationRuleController@update');
$router->post('/admin/cancellations/rules/toggle', 'App\Controllers\Admin\CancellationRuleController@toggle');
$router->post('/admin/cancellations/rules/delete', 'App\Controllers\Admin\CancellationRuleController@delete');
$router->post('/admin/cancellations/rules/toggle-global', 'App\Controllers\Admin\CancellationRuleController@toggleGlobal');

// Admin Refunds (Step 2: Cashfree payment processing — sub of Cancellations)
$router->get('/admin/cancellations/refunds', 'App\Controllers\Admin\RefundController@index');
$router->post('/admin/cancellations/refunds/process', 'App\Controllers\Admin\RefundController@process');

// Legacy redirect: keep /admin/refunds working
$router->get('/admin/refunds', 'App\Controllers\Admin\CancellationController@index');

// Admin Reviews
$router->get('/admin/reviews', 'App\Controllers\Admin\ReviewController@index');
$router->post('/admin/reviews/update-status', 'App\Controllers\Admin\ReviewController@updateStatus');

// Finance & Commission Module Routes (Admin & Vendor)
$router->get('/admin/finance', 'App\Controllers\Admin\FinanceController@payments');
$router->get('/admin/finance/payments', 'App\Controllers\Admin\FinanceController@payments');
$router->get('/admin/finance/withdrawals', 'App\Controllers\Admin\FinanceController@withdrawals');
$router->post('/admin/finance/withdrawals/request', 'App\Controllers\Admin\FinanceController@requestWithdrawal');
$router->post('/admin/finance/withdrawals/process', 'App\Controllers\Admin\FinanceController@processWithdrawal');
$router->get('/admin/finance/commissions', 'App\Controllers\Admin\FinanceController@commissions');
$router->post('/admin/finance/commissions/save', 'App\Controllers\Admin\FinanceController@saveCommissionSettings');
$router->get('/admin/finance/transactions', 'App\Controllers\Admin\FinanceController@transactions');

// Admin System Settings, Feature Flags & Data Backup Routes
$router->get('/admin/settings', 'App\Controllers\Admin\SettingsController@index');
$router->post('/admin/settings/toggle-vendor', 'App\Controllers\Admin\SettingsController@toggleVendorModule');
$router->get('/admin/settings/backup/export', 'App\Controllers\Admin\SettingsController@exportBackup');
$router->post('/admin/settings/backup/import', 'App\Controllers\Admin\SettingsController@importBackup');

// Vendor Settings & Bank Accounts Routes
$router->get('/admin/vendor/settings', 'App\Controllers\Admin\VendorSettingsController@index');
$router->post('/admin/vendor/settings/bank-accounts/save', 'App\Controllers\Admin\VendorSettingsController@saveBankAccount');
$router->post('/admin/vendor/settings/bank-accounts/set-primary', 'App\Controllers\Admin\VendorSettingsController@setPrimaryBankAccount');
$router->post('/admin/vendor/settings/bank-accounts/delete', 'App\Controllers\Admin\VendorSettingsController@deleteBankAccount');

// Support & Helpdesk Routes (Admin & Vendor)
$router->get('/admin/support', 'App\Controllers\Admin\SupportController@index');
$router->post('/admin/support/create', 'App\Controllers\Admin\SupportController@create');
$router->get('/admin/support/view', 'App\Controllers\Admin\SupportController@viewDetails');
$router->get('/admin/support/view/{id}', 'App\Controllers\Admin\SupportController@viewDetails');
$router->post('/admin/support/reply', 'App\Controllers\Admin\SupportController@reply');
$router->post('/admin/support/reply/{id}', 'App\Controllers\Admin\SupportController@reply');
$router->post('/admin/support/status', 'App\Controllers\Admin\SupportController@updateStatus');
$router->post('/admin/support/status/{id}', 'App\Controllers\Admin\SupportController@updateStatus');
$router->post('/admin/support/priority', 'App\Controllers\Admin\SupportController@updatePriority');
$router->post('/admin/support/priority/{id}', 'App\Controllers\Admin\SupportController@updatePriority');

// 5. Dispatch the request
try {
    // Robust URL parsing for both Apache and PHP built-in server
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Strip base path if running in a subfolder (e.g., XAMPP)
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    if ($basePath !== '/' && $basePath !== '' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
    
    $url = rtrim($uri, '/') ?: '/';
    if ($url !== '/' && $url[0] !== '/') {
        $url = '/' . $url;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $router->dispatch($method, $url);
} catch (Exception $e) {
    // Very basic error handling
    if ($e->getCode() === 404) {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
