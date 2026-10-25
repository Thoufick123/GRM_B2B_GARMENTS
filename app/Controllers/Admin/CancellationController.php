<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\RefundRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\EmailService;

/**
 * Admin\CancellationController
 *
 * Handles the FIRST step of the cancellation workflow:
 * Admin reviews cancellation requests, then either:
 *   - APPROVES  → status becomes 'processing' (moves to Refunds sub-module for payment)
 *   - REJECTS   → status becomes 'rejected', order goes to 'cancelled', user emailed
 *
 * Routes:
 *   GET  /admin/cancellations          → index()
 *   POST /admin/cancellations/approve  → approve()
 *   POST /admin/cancellations/reject   → reject()
 *
 * Security: super_admin, manager, staff (staff can view; only manager+ can approve/reject)
 */
class CancellationController extends Controller
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
    // GET /admin/cancellations
    // -------------------------------------------------------------------------

    public function index(): void
    {
        if (!$this->hasPermission('all_cancellations', 'view') && !$this->hasPermission('all_cancellations', 'edit')) {
            $this->requirePermission('all_cancellations', 'view');
        }
        $refundModel = new RefundRequest();
        $filter      = $_GET['filter'] ?? 'pending';   // pending | all | rejected
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $counts      = $refundModel->getCancellationStatusCounts();

        // Map tab filter → DB statuses shown on Cancellations page
        $statusMap = [
            'pending'  => ['pending_admin_approval'],
            'all'      => ['pending_admin_approval', 'processing', 'completed', 'rejected'],
            'rejected' => ['rejected'],
        ];
        $statuses = $statusMap[$filter] ?? ['pending_admin_approval'];

        $result = $refundModel->getPaginatedByStatuses($statuses, $page, 15);

        $this->render('admin/cancellations/index', [
            'title'    => 'all_cancellations',
            'requests' => $result['data'],
            'total'    => $result['total'],
            'pages'    => $result['pages'],
            'current'  => $result['current'],
            'filter'   => $filter,
            'counts'   => $counts,
        ], 'admin');
    }

    public function export(): void
    {
        if (!$this->hasPermission('all_cancellations', 'view') && !$this->hasPermission('all_cancellations', 'edit')) {
            $this->requirePermission('all_cancellations', 'view');
        }

        $statusMap = [
            'pending'  => ['pending_admin_approval'],
            'all'      => ['pending_admin_approval', 'processing', 'completed', 'rejected'],
            'rejected' => ['rejected'],
        ];
        $filter = $_GET['filter'] ?? 'pending';
        $requests = (new RefundRequest())->getAllByStatuses($statusMap[$filter] ?? $statusMap['pending']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cancellations_export_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \RuntimeException('Unable to open cancellation export output.');
        }

        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, [
            'Request ID', 'Order Number', 'Buyer Name', 'Email', 'Refund Amount',
            'Refund Method', 'Refund Details', 'Cancellation Type', 'Reason',
            'Status', 'Admin Notes', 'Requested At', 'Approved At'
        ]);
        foreach ($requests as $request) {
            $status = strtolower(trim($request['status']));
            $approvedAt = $request['approved_at'] ?? null;
            if (!$approvedAt && !in_array($status, ['pending_admin_approval', 'rejected'], true)) {
                $approvedAt = $request['processed_at'] ?? $request['updated_at'] ?? '';
            }
            fputcsv($output, [
                $request['id'],
                $request['order_number'],
                $request['user_name'],
                $request['user_email'],
                $request['amount'],
                $request['refund_method'] ?? '',
                $request['refund_details'] ?? '',
                $request['cancellation_type'],
                $request['reason'],
                $request['status'],
                $request['admin_notes'] ?? '',
                $request['created_at'],
                $approvedAt,
            ]);
        }

        fclose($output);
        exit;
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/approve
    // -------------------------------------------------------------------------

    /**
     * Approve the cancellation request.
     * Restores inventory immediately and queues refund.
     */
    public function approve(): void
    {
        $this->requirePermission('all_cancellations', 'edit');
        header('Content-Type: application/json');

        $input     = $this->parseJsonInput();
        $reqId     = (int)($input['refund_request_id'] ?? 0);
        $adminNote = trim($input['admin_notes'] ?? '');
        $adminId   = (int)Session::get('user_id');

        if (!$reqId) {
            $this->json(['success' => false, 'message' => 'Invalid request ID.'], 422);
            return;
        }

        $refundModel = new RefundRequest();
        $req         = $refundModel->findById($reqId);

        if (!$req) {
            $this->json(['success' => false, 'message' => 'Cancellation request not found.'], 404);
            return;
        }

        $currentStatus = strtolower(trim($req['status']));
        if ($currentStatus !== 'pending_admin_approval') {
            $this->json([
                'success' => false,
                'message' => "Cannot approve — current status is: " . str_replace('_', ' ', $req['status']),
            ], 409);
            return;
        }

        $orderModel = new Order();
        $orderId = (int)$req['order_id'];
        $amount = (float)($req['amount'] ?? 0);

        // Always restore inventory upon cancellation approval
        $orderModel->restoreStockForOrderId($orderId);

        if ($amount <= 0) {
            // ₹0 refund (e.g. COD without advance) → mark completed and cancel order immediately
            $refundModel->updateStatus($reqId, 'completed', [
                'admin_notes'  => $adminNote ?: 'Cancellation approved (₹0 refund). Order cancelled.',
                'processed_by' => $adminId,
                'approved_at'  => true,
            ]);
            $orderModel->updateOrderCancellationStatus($orderId, 'cancelled', $adminNote ?: 'Cancellation approved by admin', 'admin');

            $this->json([
                'success' => true,
                'message' => "Cancellation approved! Order #{$req['order_number']} has been cancelled and stock restored to inventory.",
            ]);
            return;
        }

        // Amount > 0 → move to 'processing' and order to 'refund_processing'
        $refundModel->updateStatus($reqId, 'processing', [
            'admin_notes'  => $adminNote ?: 'Cancellation approved. Pending payment refund.',
            'processed_by' => $adminId,
            'approved_at'  => true,
        ]);

        $orderModel->updateOrderCancellationStatus($orderId, 'refund_processing', $adminNote, 'admin');

        $this->json([
            'success' => true,
            'message' => "Cancellation approved! Inventory has been restored and refund is queued for confirmation.",
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/reject
    // -------------------------------------------------------------------------

    public function reject(): void
    {
        $this->requirePermission('all_cancellations', 'edit');
        header('Content-Type: application/json');

        $input     = $this->parseJsonInput();
        $reqId     = (int)($input['refund_request_id'] ?? 0);
        $adminNote = trim($input['admin_notes'] ?? '');
        $adminId   = (int)Session::get('user_id');

        if (!$reqId) {
            $this->json(['success' => false, 'message' => 'Invalid request ID.'], 422);
            return;
        }

        if (empty($adminNote) || strlen($adminNote) < 5) {
            $this->json(['success' => false, 'message' => 'A rejection reason (min 5 characters) is required.'], 422);
            return;
        }

        $refundModel = new RefundRequest();
        $req         = $refundModel->findById($reqId);

        if (!$req) {
            $this->json(['success' => false, 'message' => 'Request not found.'], 404);
            return;
        }

        $currentStatus = strtolower(trim($req['status']));
        if ($currentStatus !== 'pending_admin_approval') {
            $this->json([
                'success' => false,
                'message' => "Cannot reject — current status is: " . str_replace('_', ' ', $req['status']),
            ], 422);
            return;
        }

        $refundModel->updateStatus($reqId, 'rejected', [
            'admin_notes'  => $adminNote,
            'processed_by' => $adminId,
        ]);

        // Revert order back to active 'placed' status (order is NOT cancelled because cancellation request was rejected)
        $db = \Core\Database::getInstance();
        $db->prepare("UPDATE orders SET status = 'placed', updated_at = NOW() WHERE id = ? AND status = 'refund_pending'")->execute([(int)$req['order_id']]);

        // Email user
        try {
            $userModel = new User();
            $user      = $userModel->findById((int)$req['user_id']);
            if ($user) {
                (new EmailService())->sendRefundRejected($user, $req, $adminNote);
            }
        } catch (\Exception $e) {
            error_log('[Admin\CancellationController::reject] Email: ' . $e->getMessage());
        }

        $this->json(['success' => true, 'message' => 'Cancellation request rejected. Order remains active and buyer has been notified.']);
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
