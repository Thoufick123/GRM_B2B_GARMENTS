<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\CancellationRule;

/**
 * Admin\CancellationRuleController
 *
 * Routes:
 *   GET  /admin/cancellations/rules         → index()
 *   POST /admin/cancellations/rules/add     → add()
 *   POST /admin/cancellations/rules/update  → update()
 *   POST /admin/cancellations/rules/toggle  → toggle()
 *   POST /admin/cancellations/rules/delete  → delete()
 *
 * Security: super_admin and manager only.
 */
class CancellationRuleController extends Controller
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

    // -------------------------------------------------------------------------
    // GET /admin/cancellations/rules
    // -------------------------------------------------------------------------

    public function index(): void
    {
        if (!$this->hasPermission('cancellation_rules', 'view') && !$this->hasAnyPermission('cancellation_rules')) {
            $this->requirePermission('cancellation_rules', 'view');
        }

        $model = new CancellationRule();
        $settingModel = new \App\Models\Setting();
        $this->render('admin/cancellations/rules', [
            'title' => 'Cancellation Rules',
            'rules' => $model->getAll(),
            'cancellationsEnabled' => $settingModel->get('cancellations_enabled', '1') === '1',
        ], 'admin');
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/rules/add
    // -------------------------------------------------------------------------

    public function add(): void
    {
        $role = Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager'])) {
            $this->requirePermission('cancellation_rules', 'create');
        }
        header('Content-Type: application/json');
        $input = $this->parseJsonInput();

        $ruleName = strtolower(trim($input['rule_name'] ?? ''));
        if (!$ruleName) {
            $this->json(['success' => false, 'message' => 'Rule Name (Order Status) is required.'], 422);
        }
        
        $paymentMethod = in_array(strtolower($input['payment_method'] ?? ''), ['cod', 'prepaid', 'online', 'both', 'all']) ? strtolower($input['payment_method']) : 'prepaid';

        $model = new CancellationRule();
        
        // Prevent duplicate rules for the same order status
        $existing = $model->findByStatus($ruleName);
        if ($existing) {
            $this->json([
                'success' => false, 
                'message' => 'A cancellation rule for "' . ucfirst(str_replace('_', ' ', $ruleName)) . '" already exists. You can edit the existing rule.'
            ], 422);
            return;
        }

        try {
            $id = $model->create([
                'rule_name'         => $ruleName,
                'payment_method'    => $paymentMethod,
                'cancellation_fee'  => max(0, (float)($input['cancellation_fee'] ?? 0)),
                'refund_percentage' => max(0, min(100, (int)($input['refund_percentage'] ?? 100))),
                'sla_days'          => max(0, (int)($input['sla_days'] ?? 0)),
                'is_active'         => isset($input['is_active']) ? (int)$input['is_active'] : 1,
                'created_by'        => (int)(Session::get('user_id') ?: 1),
            ]);
            $this->json(['success' => true, 'message' => 'Rule created successfully.', 'id' => $id]);
        } catch (\PDOException $e) {
            // Check for duplicate key
            if ($e->getCode() === '23000') {
                $this->json(['success' => false, 'message' => 'A rule for this status already exists.'], 422);
            }
            $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/rules/update
    // -------------------------------------------------------------------------

    public function update(): void
    {
        $role = Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager'])) {
            $this->requirePermission('cancellation_rules', 'edit');
        }
        header('Content-Type: application/json');
        $input = $this->parseJsonInput();
        $id    = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid rule ID.'], 422);
        }

        $ruleName = strtolower(trim($input['rule_name'] ?? ''));
        if (!$ruleName) {
            $this->json(['success' => false, 'message' => 'Rule Name (Order Status) is required.'], 422);
        }

        $paymentMethod = in_array(strtolower($input['payment_method'] ?? ''), ['cod', 'prepaid', 'online', 'both', 'all']) ? strtolower($input['payment_method']) : 'prepaid';

        $model = new CancellationRule();
        if (!$model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Rule not found.'], 404);
        }

        // Check if another rule already has this status
        $existing = $model->findByStatus($ruleName);
        if ($existing && (int)$existing['id'] !== $id) {
            $this->json([
                'success' => false, 
                'message' => 'Another rule for status "' . ucfirst(str_replace('_', ' ', $ruleName)) . '" already exists.'
            ], 422);
            return;
        }

        try {
            $model->update($id, [
                'rule_name'         => $ruleName,
                'payment_method'    => $paymentMethod,
                'cancellation_fee'  => max(0, (float)($input['cancellation_fee'] ?? 0)),
                'refund_percentage' => max(0, min(100, (int)($input['refund_percentage'] ?? 100))),
                'sla_days'          => max(0, (int)($input['sla_days'] ?? 0)),
                'is_active'         => isset($input['is_active']) ? (int)$input['is_active'] : 1,
            ]);
            $this->json(['success' => true, 'message' => 'Rule updated successfully.']);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json(['success' => false, 'message' => 'A rule for this status already exists.'], 422);
            }
            $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/rules/toggle
    // -------------------------------------------------------------------------

    public function toggle(): void
    {
        $role = Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager'])) {
            $this->requirePermission('cancellation_rules', 'edit');
        }
        header('Content-Type: application/json');
        $input = $this->parseJsonInput();
        $id    = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid rule ID.'], 422);
        }

        $model = new CancellationRule();
        $model->toggleActive($id);

        $rule = $model->findById($id);
        $state = $rule && $rule['is_active'] ? 'enabled' : 'disabled';

        $this->json(['success' => true, 'message' => "Rule {$state}.", 'is_active' => (int)($rule['is_active'] ?? 0)]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/rules/delete
    // -------------------------------------------------------------------------

    public function delete(): void
    {
        $role = Session::get('user_role');
        if (!in_array($role, ['super_admin', 'admin', 'manager'])) {
            $this->requirePermission('cancellation_rules', 'delete');
        }
        header('Content-Type: application/json');
        $input = $this->parseJsonInput();
        $id    = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid rule ID.'], 422);
        }

        (new CancellationRule())->delete($id);
        $this->json(['success' => true, 'message' => 'Rule deleted successfully.']);
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/rules/toggle-global
    // -------------------------------------------------------------------------

    public function toggleGlobal(): void
    {
        $this->requirePermission('cancellation_rules', 'edit');
        header('Content-Type: application/json');
        $input = $this->parseJsonInput();
        $enabled = isset($input['enabled']) ? (bool)$input['enabled'] : true;

        $settingModel = new \App\Models\Setting();
        $settingModel->set('cancellations_enabled', $enabled ? '1' : '0');

        $this->json([
            'success' => true,
            'message' => 'Cancellations ' . ($enabled ? 'enabled' : 'disabled') . ' successfully.',
            'cancellations_enabled' => $enabled,
        ]);
    }

    // -------------------------------------------------------------------------
    private function parseJsonInput(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($ct, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }
}
