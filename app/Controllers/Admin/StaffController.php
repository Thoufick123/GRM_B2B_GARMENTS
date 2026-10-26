<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\User;
use App\Models\Role;

class StaffController extends Controller
{
    private $modules = [
        'Dashboard' => [
            'dashboard' => ['label' => 'Dashboard Overview', 'actions' => ['view']],
        ],
        'Buyers' => [
            'all_buyers' => ['label' => 'All Buyers', 'actions' => ['view', 'edit']],
            'pending_buyers' => ['label' => 'Pending Buyers', 'actions' => ['view', 'edit']],
        ],
        'Orders' => [
            'all_orders' => ['label' => 'All Orders', 'actions' => ['view', 'edit']],
            'pending_payments' => ['label' => 'Pending Payments', 'actions' => ['view', 'edit']],
            'bill_modifications' => ['label' => 'Bill Modifications', 'actions' => ['view', 'edit']],
            'packing_videos' => ['label' => 'Packing Videos & Backup', 'actions' => ['view', 'edit']],
            'packing_slip_config' => ['label' => 'Packing Slip Config', 'actions' => ['view', 'edit']],
        ],
        'Reviews' => [
            'reviews' => ['label' => 'Reviews', 'actions' => ['view', 'edit', 'delete']],
        ],
        'Cancellations' => [
            'all_cancellations' => ['label' => 'All Cancellations', 'actions' => ['view', 'edit']],
            'refunds' => ['label' => 'Refunds', 'actions' => ['view', 'edit']],
            'cancellation_rules' => ['label' => 'Cancellation Rules', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Catalog' => [
            'categories' => ['label' => 'Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
            'subcategories' => ['label' => 'Sub Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
            'attributes' => ['label' => 'Attributes', 'actions' => ['view', 'create', 'edit', 'delete']],
            'products' => ['label' => 'Products', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Customize' => [
            'customize_categories' => ['label' => 'Category', 'actions' => ['view', 'create', 'edit', 'delete']],
            'customize_subcategories' => ['label' => 'Sub Category', 'actions' => ['view', 'create', 'edit', 'delete']],
            'customize_attributes' => ['label' => 'Attributes', 'actions' => ['view', 'create', 'edit', 'delete']],
            'customize_products' => ['label' => 'Products / Fabrics', 'actions' => ['view', 'create', 'edit', 'delete']],
            'fabric_customizations' => ['label' => 'Fabric Customization Rules', 'actions' => ['view', 'create', 'edit', 'delete']],
            'customization_labels' => ['label' => 'Customization Labels', 'actions' => ['view', 'create', 'edit', 'delete']],
            'custom_orders' => ['label' => 'Custom Orders', 'actions' => ['view', 'edit']],
        ],
        'Inventory' => [
            'inventory' => ['label' => 'Inventory Management', 'actions' => ['view', 'edit']],
        ],
        'Rules' => [
            'order_rules' => ['label' => 'Order Rules', 'actions' => ['view', 'create', 'edit', 'delete']],
            'fee_rules' => ['label' => 'Fee Rules', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'CMS Builder' => [
            'cms_builder' => ['label' => 'Layout Builder', 'actions' => ['view', 'edit']],
            'cms_navbars' => ['label' => 'Navbars', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_topbars' => ['label' => 'Top Bars', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_hero_banners' => ['label' => 'Hero Banners', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_main_banners' => ['label' => 'Main Banners', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_product_carousels' => ['label' => 'Product Carousels', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_category_carousels' => ['label' => 'Category Carousels', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_categories_grid' => ['label' => 'Categories Grid', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_footers' => ['label' => 'Footers', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_about_us' => ['label' => 'About Us', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_size_charts' => ['label' => 'Size Charts', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_product_card' => ['label' => 'Product Card Settings', 'actions' => ['view', 'edit']],
            'cms_reviews' => ['label' => 'CMS Reviews', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_projects' => ['label' => 'Our Project', 'actions' => ['view', 'create', 'edit', 'delete']],
            'cms_reels' => ['label' => 'Reels Container', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Staff' => [
            'staff_management' => ['label' => 'Staff Members', 'actions' => ['view', 'create', 'edit', 'delete']],
            'roles' => ['label' => 'Roles & Permissions', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Media' => [
            'media_manager' => ['label' => 'Media Manager', 'actions' => ['view', 'create', 'delete']],
        ],
        'Settings' => [
            'settings' => ['label' => 'System & Backup Settings', 'actions' => ['view', 'edit']],
        ],
        'Vendors' => [
            'vendor_analytics' => ['label' => 'Vendor Revenue Analytics', 'actions' => ['view', 'edit']],
            'all_vendors' => ['label' => 'All Vendors Directory', 'actions' => ['view', 'edit', 'delete']],
            'pending_vendors' => ['label' => 'Pending Vendor Approvals', 'actions' => ['view', 'edit']],
            'vendor_staff' => ['label' => 'Vendor Staff & Sub-Accounts', 'actions' => ['view', 'create', 'edit', 'delete']],
            'bulk_catalog' => ['label' => 'Bulk CSV Catalog Import', 'actions' => ['view', 'create', 'edit']],
            'volume_pricing' => ['label' => 'Volume Pricing Tier Matrix', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Finance' => [
            'payments' => ['label' => 'Payments & Wallet', 'actions' => ['view', 'edit']],
            'withdrawals' => ['label' => 'Withdrawal Requests & Payouts', 'actions' => ['view', 'edit']],
            'commissions' => ['label' => 'Commission Settings', 'actions' => ['view', 'create', 'edit']],
            'transactions' => ['label' => 'Financial Ledger & Transactions', 'actions' => ['view']],
        ],
        'Support' => [
            'support' => ['label' => 'Support & Helpdesk', 'actions' => ['view', 'create', 'edit', 'delete']],
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $this->requirePermission('staff_management', 'view');
        $userModel = new User();
        $staff = $userModel->getStaff();

        $this->render('admin/staff/index', [
            'title' => 'Staff Management',
            'staff' => $staff
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('staff_management', 'create');
        $roleModel = new Role();
        $roles = $roleModel->getAllRoles();

        $this->render('admin/staff/form', [
            'title' => 'Add Staff Member',
            'roles' => $roles,
            'modules' => $this->modules
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('staff_management', 'create');
        $roleId = $_POST['role_id'] ?? null;
        $isCustom = ($roleId === 'custom');
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => 'staff', // Base role
            'role_id' => $isCustom ? null : $roleId, // Custom RBAC role
            'status' => $_POST['status'] ?? 'active',
            'permissions' => $isCustom ? $this->sanitizePermissions($_POST['permissions'] ?? []) : null
        ];

        // Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($roleId)) {
            Session::setFlash('error', 'Name, email, password, and Role are required.');
            $this->redirect('/admin/staff/create');
        }

        $userModel = new User();
        
        // Check if email exists
        if ($userModel->findByEmail($data['email'])) {
            Session::setFlash('error', 'Email already exists.');
            $this->redirect('/admin/staff/create');
        }

        try {
            $userModel->create($data);
            Session::setFlash('success', 'Staff member added successfully.');
            $this->redirect('/admin/staff');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to add staff: ' . $e->getMessage());
            $this->redirect('/admin/staff/create');
        }
    }

    public function edit()
    {
        $this->requirePermission('staff_management', 'edit');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/staff');
        }

        $userModel = new User();
        $staff = $userModel->findById($id);

        if (!$staff || !in_array($staff['role'], ['manager', 'staff'])) {
            Session::setFlash('error', 'Staff member not found.');
            $this->redirect('/admin/staff');
        }

        $roleModel = new Role();
        $roles = $roleModel->getAllRoles();

        // Ensure permissions are decoded if they are string
        if (!empty($staff['permissions']) && is_string($staff['permissions'])) {
            $staff['permissions'] = json_decode($staff['permissions'], true);
        }

        $this->render('admin/staff/form', [
            'title' => 'Edit Staff Member',
            'staff' => $staff,
            'roles' => $roles,
            'modules' => $this->modules
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('staff_management', 'edit');
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/staff');
        }

        $roleId = $_POST['role_id'] ?? null;
        $isCustom = ($roleId === 'custom');

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => 'staff',
            'role_id' => $isCustom ? null : $roleId,
            'status' => $_POST['status'] ?? 'active',
            'permissions' => $isCustom ? $this->sanitizePermissions($_POST['permissions'] ?? []) : null
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        $userModel = new User();
        
        // Check if another user has this email
        $existing = $userModel->findByEmail($data['email']);
        if ($existing && $existing['id'] != $id) {
            Session::setFlash('error', 'Email already in use by another user.');
            $this->redirect('/admin/staff/edit?id=' . $id);
        }

        try {
            $userModel->updateStaff($id, $data);
            Session::setFlash('success', 'Staff member updated successfully.');
            $this->redirect('/admin/staff');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to update staff: ' . $e->getMessage());
            $this->redirect('/admin/staff/edit?id=' . $id);
        }
    }

    public function delete()
    {
        $this->requirePermission('staff_management', 'delete');
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/staff');
        }
        
        if ($id == Session::get('user_id')) {
            Session::setFlash('error', 'You cannot delete yourself.');
            $this->redirect('/admin/staff');
        }

        $userModel = new User();
        try {
            $userModel->deleteUser($id);
            Session::setFlash('success', 'Staff member deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to delete staff member.');
        }

        $this->redirect('/admin/staff');
    }

    public function toggleStatus()
    {
        ini_set('display_errors', 0);
        file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "toggleStatus called\n", FILE_APPEND);
        
        try {
            $this->requirePermission('staff_management', 'edit');
            file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Permission passed\n", FILE_APPEND);
            
            header('Content-Type: application/json');
            
            $raw  = file_get_contents('php://input');
            file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Raw input: $raw\n", FILE_APPEND);
            
            $data = json_decode($raw, true);
            $id     = $data['id'] ?? null;
            $status = $data['status'] ?? null;
            
            if (!$id || !in_array($status, ['active', 'deactivated'])) {
                ob_clean();
                file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Invalid input\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                return;
            }
            
            if ($id == Session::get('user_id')) {
                ob_clean();
                file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Own status error\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'You cannot change your own status.']);
                return;
            }
            
            $db   = \Core\Database::getInstance();
            file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "DB connected\n", FILE_APPEND);
            
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role IN ('staff','manager')");
            $stmt->execute([$status, $id]);
            ob_clean();
            
            file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Success\n", FILE_APPEND);
            echo json_encode(['success' => true]);
            
        } catch (\Throwable $e) {
            ob_clean();
            file_put_contents(BASE_PATH . '/scratch/toggle_log.txt', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        return;
    }

    // --- RBAC Role Management ---

    public function roles()
    {
        $this->requirePermission('roles', 'view');
        $roleModel = new Role();
        $roles = $roleModel->getAllRoles();
        
        $this->render('admin/staff/role_assign', [
            'title' => 'Role Assign',
            'roles' => $roles,
            'modules' => $this->modules
        ], 'admin');
    }

    public function storeRole()
    {
        $roleId = $_POST['role_id'] ?? null;
        if ($roleId) {
            $this->requirePermission('roles', 'edit');
        } else {
            $this->requirePermission('roles', 'create');
        }
        
        $roleName = trim($_POST['role_name'] ?? '');
        $permissions = $this->sanitizePermissions($_POST['permissions'] ?? []);
        
        if (empty($roleName)) {
            Session::setFlash('error', 'Role name is required.');
            $this->redirect('/admin/staff/roles');
            return;
        }

        $roleModel = new Role();

        // Prevent duplicate role name (case-insensitive check)
        $existing = $roleModel->findByName($roleName, $roleId ? (int)$roleId : null);
        if ($existing) {
            Session::setFlash('error', "A role named '" . htmlspecialchars($roleName) . "' already exists. Please choose a different role name.");
            $this->redirect('/admin/staff/roles');
            return;
        }

        // Prevent reserved system roles
        $reservedRoles = ['super_admin', 'super admin', 'superadmin'];
        if (in_array(strtolower($roleName), $reservedRoles)) {
            Session::setFlash('error', "The role name '" . htmlspecialchars($roleName) . "' is reserved for system administration.");
            $this->redirect('/admin/staff/roles');
            return;
        }
        try {
            if ($roleId) {
                $roleModel->update($roleId, [
                    'name' => $roleName,
                    'permissions' => $permissions
                ]);
                Session::setFlash('success', 'Role updated successfully.');
            } else {
                $roleModel->create([
                    'name' => $roleName,
                    'permissions' => $permissions
                ]);
                Session::setFlash('success', 'Role created successfully.');
            }
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to save role: ' . $e->getMessage());
        }

        $this->redirect('/admin/staff/roles');
    }

    public function deleteRole()
    {
        $this->requirePermission('roles', 'delete');
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/admin/staff/roles');
        }

        $roleModel = new Role();
        try {
            $roleModel->deleteRole($id);
            Session::setFlash('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/staff/roles');
    }

    /**
     * Sanitize and format permissions against all known modules
     */
    private function sanitizePermissions($submittedPermissions)
    {
        $sanitized = [];
        if (!is_array($submittedPermissions)) {
            $submittedPermissions = [];
        }
        foreach ($this->modules as $group => $subModules) {
            foreach ($subModules as $key => $details) {
                if (isset($submittedPermissions[$key]) && is_array($submittedPermissions[$key])) {
                    $actions = [];
                    foreach ($details['actions'] as $act) {
                        if (!empty($submittedPermissions[$key][$act])) {
                            $actions[$act] = '1';
                        }
                    }
                    $sanitized[$key] = $actions;
                } else {
                    $sanitized[$key] = [];
                }
            }
        }

        // If fabric_customizations was not explicitly set but customization_labels or customize_products has permissions, sync it
        if (empty($sanitized['fabric_customizations']) && !empty($sanitized['customization_labels'])) {
            $sanitized['fabric_customizations'] = $sanitized['customization_labels'];
        }
        if (empty($sanitized['fabric_customizations']) && !empty($sanitized['customize_products'])) {
            $sanitized['fabric_customizations'] = $sanitized['customize_products'];
        }
        if (empty($sanitized['customize_products']) && !empty($sanitized['fabric_customizations'])) {
            $sanitized['customize_products'] = $sanitized['fabric_customizations'];
        }

        return $sanitized;
    }
}
