<?php
namespace Core;

class Controller
{
    protected $userPermissions = [];

    public function __construct()
    {
        $userId = \Core\Session::get('user_id');
        if ($userId) {
            // Always fetch latest to ensure instant UI updates when Super Admin changes roles/status
            $db   = \Core\Database::getInstance();
            $stmt = $db->prepare("
                SELECT u.status, u.role, u.permissions AS custom_permissions, r.permissions AS role_permissions
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();

            if ($userData) {
                \Core\Session::set('user_status', $userData['status']);
                \Core\Session::set('user_role', $userData['role']);
                $blockedStatuses = ['blocked', 'inactive', 'deactivated'];
                if (in_array($userData['status'], $blockedStatuses)) {
                    // Clear all session data to force logout
                    \Core\Session::remove('user_id');
                    \Core\Session::remove('user_role');
                    \Core\Session::remove('user_status');
                    $msg = ($userData['status'] === 'inactive' || $userData['status'] === 'deactivated')
                        ? 'Your account has been deactivated. Please contact the administrator.'
                        : 'Your account has been blocked by the administrator.';
                    \Core\Session::setFlash('error', $msg);
                    $this->redirect('/login');
                }

                if ($userData['role'] === 'vendor' && !is_vendor_module_enabled()) {
                    \Core\Session::remove('user_id');
                    \Core\Session::remove('user_role');
                    \Core\Session::remove('user_status');
                    \Core\Session::setFlash('error', 'Vendor operations and portal access are currently disabled by platform administration.');
                    $this->redirect('/login');
                }

                // Load RBAC permissions
                $permissionsJson = !empty($userData['role_permissions']) ? $userData['role_permissions'] : $userData['custom_permissions'];
                if (!empty($permissionsJson)) {
                    if (is_array($permissionsJson)) {
                        $this->userPermissions = $permissionsJson;
                    } else {
                        $this->userPermissions = json_decode($permissionsJson, true) ?? [];
                    }
                }
            } else {
                \Core\Session::remove('user_id');
                \Core\Session::remove('user_role');
                \Core\Session::remove('user_status');
            }
        }
    }

    /**
     * Check if current user has permission for a specific module and action
     */
    public function hasPermission($module, $action)
    {
        $role = \Core\Session::get('user_role');
        
        // Super Admin has all permissions
        if ($role === 'super_admin') {
            return true;
        }

        // Vendors have permissions to their own portal modules
        if ($role === 'vendor') {
            if (!is_vendor_module_enabled()) {
                return false;
            }
            $vendorAllowed = [
                'dashboard' => ['view'],
                'products' => ['view', 'create', 'edit', 'delete'],
                'inventory' => ['view', 'edit'],
                'all_orders' => ['view', 'edit'],
                'pending_payments' => ['view'],
                'orders' => ['view', 'edit'],
                'vendor_portal' => ['view', 'edit'],
                'vendor_management' => ['view'],
                'category_requests' => ['view', 'create'],
                'categories' => ['view'],
                'subcategories' => ['view'],
                'attributes' => ['view', 'create', 'edit'],
                'media_manager' => ['view', 'create', 'delete'],
                'finance' => ['view'],
                'payments' => ['view'],
                'withdrawals' => ['view', 'create'],
                'transactions' => ['view'],
                'reviews' => ['view', 'edit'],
                'settings' => ['view', 'create', 'edit', 'delete'],
                'support' => ['view', 'create', 'edit', 'delete']
            ];
            if (isset($vendorAllowed[$module]) && in_array($action, $vendorAllowed[$module])) {
                return true;
            }
            return false;
        }

        // Fabric Customization Rules: check explicit perms, then customization_labels, then customize_products, then products
        if ($module === 'fabric_customizations') {
            $relevant = ['fabric_customizations', 'customization_labels', 'customize_products', 'products'];
            foreach ($relevant as $rel) {
                if (!empty($this->userPermissions[$rel][$action])) {
                    return true;
                }
                // Write actions inherently grant view permission
                if ($action === 'view' && (
                    !empty($this->userPermissions[$rel]['create']) ||
                    !empty($this->userPermissions[$rel]['edit']) ||
                    !empty($this->userPermissions[$rel]['delete'])
                )) {
                    return true;
                }
                // Create permission allows editing rules (configuration)
                if ($action === 'edit' && !empty($this->userPermissions[$rel]['create'])) {
                    return true;
                }
            }
            return false;
        }

        // Customization Labels: check explicit perms, then fabric_customizations, then customize_products, then products
        if ($module === 'customization_labels') {
            $relevant = ['customization_labels', 'fabric_customizations', 'customize_products', 'products'];
            foreach ($relevant as $rel) {
                if (!empty($this->userPermissions[$rel][$action])) {
                    return true;
                }
                if ($action === 'view' && (
                    !empty($this->userPermissions[$rel]['create']) ||
                    !empty($this->userPermissions[$rel]['edit']) ||
                    !empty($this->userPermissions[$rel]['delete'])
                )) {
                    return true;
                }
                if ($action === 'edit' && !empty($this->userPermissions[$rel]['create'])) {
                    return true;
                }
            }
            return false;
        }

        // If the specific module key is set in userPermissions, strictly use its configured permissions
        if (isset($this->userPermissions[$module])) {
            if (!empty($this->userPermissions[$module][$action])) {
                return true;
            }
            // Write actions inherently grant view permission
            if ($action === 'view') {
                foreach (['create', 'edit', 'delete'] as $wAct) {
                    if (!empty($this->userPermissions[$module][$wAct])) {
                        return true;
                    }
                }
            }
            return false;
        }

        // Fallback mappings for legacy roles
        $fallbacks = [
            'customize_categories' => 'categories',
            'customize_subcategories' => 'subcategories',
            'customize_attributes' => 'attributes',
            'customize_products' => 'products',
            'custom_orders' => 'all_orders',
            'packing_videos' => 'all_orders',
            'packing_slip_config' => 'all_orders',
            'cms_navbars' => 'cms_builder',
            'cms_topbars' => 'cms_builder',
            'cms_hero_banners' => 'cms_builder',
            'cms_main_banners' => 'cms_builder',
            'cms_product_carousels' => 'cms_builder',
            'cms_category_carousels' => 'cms_builder',
            'cms_categories_grid' => 'cms_builder',
            'cms_footers' => 'cms_builder',
            'cms_about_us' => 'cms_builder',
            'cms_size_charts' => 'cms_builder',
            'cms_product_card' => 'cms_builder',
            'cms_reviews' => 'cms_builder',
            'cms_projects' => 'cms_builder',
            'cms_reels' => 'cms_builder',
        ];

        // If not explicitly set, check fallback module
        if (isset($fallbacks[$module])) {
            $fallbackModule = $fallbacks[$module];
            if (isset($this->userPermissions[$fallbackModule][$action])) {
                return !empty($this->userPermissions[$fallbackModule][$action]);
            }
            // If the fallback module itself has a fallback (e.g. customize_products -> products)
            if (isset($fallbacks[$fallbackModule]) && isset($this->userPermissions[$fallbacks[$fallbackModule]][$action])) {
                return !empty($this->userPermissions[$fallbacks[$fallbackModule]][$action]);
            }
        }

        return false;
    }

    /**
     * Check if current user has ANY permission (view, create, edit, delete) for a module
     */
    public function hasAnyPermission($module)
    {
        $role = \Core\Session::get('user_role');
        if ($role === 'super_admin') {
            return true;
        }

        if ($role === 'vendor') {
            if (!is_vendor_module_enabled()) {
                return false;
            }
            $vendorAllowedModules = [
                'dashboard', 'products', 'inventory', 'all_orders', 
                'pending_payments', 'orders', 'vendor_portal', 
                'vendor_management', 'category_requests', 'categories', 
                'subcategories', 'attributes', 'media_manager',
                'finance', 'payments', 'withdrawals', 'transactions',
                'reviews', 'settings', 'support'
            ];
            return in_array($module, $vendorAllowedModules);
        }

        if ($module === 'fabric_customizations') {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                if ($this->hasPermission('fabric_customizations', $action)) {
                    return true;
                }
            }
            return false;
        }

        if ($module === 'customization_labels') {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                if ($this->hasPermission('customization_labels', $action)) {
                    return true;
                }
            }
            return false;
        }

        if (isset($this->userPermissions[$module])) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                if (!empty($this->userPermissions[$module][$action])) {
                    return true;
                }
            }
            return false;
        }

        $fallbacks = [
            'customize_categories' => 'categories',
            'customize_subcategories' => 'subcategories',
            'customize_attributes' => 'attributes',
            'customize_products' => 'products',
            'custom_orders' => 'all_orders',
            'packing_videos' => 'all_orders',
            'packing_slip_config' => 'all_orders',
            'cms_navbars' => 'cms_builder',
            'cms_topbars' => 'cms_builder',
            'cms_hero_banners' => 'cms_builder',
            'cms_main_banners' => 'cms_builder',
            'cms_product_carousels' => 'cms_builder',
            'cms_category_carousels' => 'cms_builder',
            'cms_categories_grid' => 'cms_builder',
            'cms_footers' => 'cms_builder',
            'cms_about_us' => 'cms_builder',
            'cms_size_charts' => 'cms_builder',
            'cms_product_card' => 'cms_builder',
            'cms_reviews' => 'cms_builder',
            'cms_projects' => 'cms_builder',
            'cms_reels' => 'cms_builder',
        ];

        if (isset($fallbacks[$module]) && isset($this->userPermissions[$fallbacks[$module]])) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                if (!empty($this->userPermissions[$fallbacks[$module]][$action])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Require permission or redirect
     */
    protected function requirePermission($module, $action)
    {
        if (!$this->hasPermission($module, $action)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json', true, 403);
                echo json_encode(['success' => false, 'error' => 'You do not have permission to perform this action.']);
                exit;
            }

            $role = \Core\Session::get('user_role');
            if ($role === 'buyer') {
                \Core\Session::setFlash('error', 'You are currently signed in as a Buyer. Management modules require an Admin or Vendor account.');
                $this->redirect('/dashboard');
                return;
            }

            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
            if ($basePath !== '/' && $basePath !== '' && strpos($currentUri, $basePath) === 0) {
                $currentUri = substr($currentUri, strlen($basePath));
            }
            $currentUri = rtrim($currentUri, '/') ?: '/';

            // If they are on dashboard (or trying to access dashboard) but don't have dashboard view access
            if ($currentUri === '/admin/dashboard' || $currentUri === '/admin' || $module === 'dashboard') {
                
                // Complete ordered fallback map of all modules to their primary view routes
                $fallbackRoutes = [
                    'all_buyers'            => '/admin/buyers',
                    'pending_buyers'        => '/admin/buyers/pending',
                    'all_orders'            => '/admin/orders',
                    'pending_payments'      => '/admin/orders/pending',
                    'bill_modifications'    => '/admin/order-modifications',
                    'packing_videos'        => '/admin/orders/packing-videos',
                    'packing_slip_config'   => '/admin/orders/packing-slip-config',
                    'reviews'               => '/admin/reviews',
                    'all_cancellations'     => '/admin/cancellations',
                    'refunds'               => '/admin/cancellations/refunds',
                    'cancellation_rules'    => '/admin/cancellations/rules',
                    'categories'            => '/admin/catalog/categories',
                    'subcategories'         => '/admin/catalog/subcategories',
                    'attributes'            => '/admin/catalog/attributes',
                    'products'              => '/admin/catalog/products',
                    'customize_categories'    => '/admin/catalog/categories?module=customize',
                    'customize_subcategories' => '/admin/catalog/subcategories?module=customize',
                    'customize_attributes'    => '/admin/catalog/attributes?module=customize',
                    'customize_products'      => '/admin/catalog/products?module=customize',
                    'customization_labels'    => '/admin/customization-labels',
                    'custom_orders'           => '/admin/customize/orders',
                    'fabric_customizations' => '/admin/fabric-customizations',
                    'inventory'             => '/admin/inventory',
                    'order_rules'           => '/admin/order-rules',
                    'fee_rules'             => '/admin/fee-rules',
                    'cms_builder'           => '/admin/cms/builder',
                    'cms_navbars'           => '/admin/cms/navbars',
                    'cms_topbars'           => '/admin/cms/topbars',
                    'cms_hero_banners'      => '/admin/cms/hero-banners',
                    'cms_main_banners'      => '/admin/cms/main-banners',
                    'cms_product_carousels' => '/admin/cms/product-carousels',
                    'cms_category_carousels'=> '/admin/cms/category-carousels',
                    'cms_categories_grid'   => '/admin/cms/categories-grid',
                    'cms_footers'           => '/admin/cms/footers',
                    'cms_about_us'          => '/admin/cms/about-us',
                    'cms_size_charts'       => '/admin/cms/size-charts',
                    'cms_product_card'      => '/admin/cms/product-card-settings',
                    'cms_reviews'           => '/admin/cms/reviews',
                    'cms_projects'          => '/admin/cms/projects',
                    'cms_reels'             => '/admin/cms/reels',
                    'staff_management'      => '/admin/staff',
                    'roles'                 => '/admin/staff/roles',
                    'media_manager'         => '/admin/media',
                    'settings'              => '/admin/settings',
                    'vendor_analytics'      => '/admin/vendors/dashboard',
                    'all_vendors'           => '/admin/vendors',
                    'pending_vendors'       => '/admin/vendors/pending',
                    'vendor_staff'          => '/admin/vendors',
                    'bulk_catalog'          => '/admin/catalog/products',
                    'volume_pricing'        => '/admin/catalog/products',
                    'payments'              => '/admin/finance/payments',
                    'withdrawals'           => '/admin/finance/withdrawals',
                    'commissions'           => '/admin/finance/commissions',
                    'transactions'          => '/admin/finance/transactions',
                    'support'               => '/admin/support',
                ];
                
                foreach ($fallbackRoutes as $mod => $route) {
                    if (($this->hasPermission($mod, 'view') || $this->hasAnyPermission($mod)) && $currentUri !== $route) {
                        $this->redirect($route);
                        return;
                    }
                }
                
                // If they truly have NO view permissions anywhere
                echo "<!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1'>
                    <title>Access Denied - " . APP_NAME . "</title>
                    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap' rel='stylesheet'>
                    <style>
                        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                        .card { background: white; border: 1px solid #f3f4f6; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); padding: 40px; max-width: 440px; width: 100%; text-align: center; }
                        .icon { width: 64px; height: 64px; background: #fef2f2; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; color: #ef4444; font-size: 28px; }
                        h2 { font-size: 22px; font-weight: 800; color: #111827; margin: 0 0 10px; }
                        p { font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 24px; }
                        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px 20px; background: #F25996; color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; text-decoration: none; transition: background 0.2s; }
                        .btn:hover { background: #e04481; }
                    </style>
                </head>
                <body>
                    <div class='card'>
                        <div class='icon'>🔒</div>
                        <h2>Access Restricted</h2>
                        <p>Your staff account does not currently have permissions assigned to view any admin modules. Please contact your Super Administrator to configure your role.</p>
                        <form action='" . BASE_URL . "/logout' method='POST'>
                            <button type='submit' class='btn'>Log Out</button>
                        </form>
                    </div>
                </body>
                </html>";
                exit;
            } else {
                \Core\Session::setFlash('error', 'You do not have permission to perform this action.');
                $this->redirect('/admin/dashboard');
            }
        }
    }
    /**
     * Render a view file within a layout.
     *
     * @param string $view The view path (e.g., 'storefront/home')
     * @param array $data Data to extract into the view
     * @param string $layout The layout to use (default: 'main')
     */
    protected function render(string $view, array $data = [], string $layout = 'main')
    {
        $_pageData = $data;

        // Extract data to make variables available in the view
        extract($_pageData);

        // Capture the view content
        ob_start();
        $viewFile = APP_PATH . '/Views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View file not found: {$viewFile}");
        }
        $content = ob_get_clean();

        // Restore original controller data so subcomponents (like CMS blocks) cannot leak or overwrite layout variables
        extract($_pageData);

        // Include the layout and pass the content to it
        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            // Fallback if no layout exists, just output content
            echo $content;
        }
    }

    /**
     * Redirect to a specific URL
     */
    protected function redirect(string $url)
    {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    /**
     * Send a JSON response and exit
     */
    protected function json($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
