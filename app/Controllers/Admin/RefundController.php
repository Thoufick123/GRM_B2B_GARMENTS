<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\RefundRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\CashfreeService;
use App\Services\EmailService;

/**
 * Admin\RefundController — STEP 2 of the cancellation workflow.
 *
 * Only deals with requests that have already been APPROVED by CancellationController
 * (i.e. status = 'processing').  Triggers the actual Cashfree payment refund.
 *
 * Routes:
 *   GET  /admin/cancellations/refunds         → index()
 *   POST /admin/cancellations/refunds/process → process()
 *
 * Security: super_admin and manager only.
 */
class RefundController extends Controller
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
    // GET /admin/cancellations/refunds
    // -------------------------------------------------------------------------

    public function index(): void
    {
        if (!$this->hasPermission('refunds', 'view') && !$this->hasPermission('refunds', 'edit')) {
            $this->requirePermission('refunds', 'view');
        }
        $refundModel = new RefundRequest();
        $tab         = $_GET['tab'] ?? 'pending';   // pending | completed | all
        $page        = max(1, (int)($_GET['page'] ?? 1));

        // Map tab → statuses shown
        $statusMap = [
            'pending'   => ['processing'],
            'completed' => ['completed'],
            'all'       => ['processing', 'completed'],
        ];
        $statuses = $statusMap[$tab] ?? ['processing'];

        $result = $refundModel->getPaginatedByStatuses($statuses, $page, 15);
        $counts  = $refundModel->getCancellationStatusCounts();
        $amounts = $refundModel->getCancellationStatusAmounts();

        $this->render('admin/refunds/index', [
            'title'    => 'Refunds',
            'requests' => $result['data'],
            'total'    => $result['total'],
            'pages'    => $result['pages'],
            'current'  => $result['current'],
            'tab'      => $tab,
            'counts'   => $counts,
            'amounts'  => $amounts,
        ], 'admin');
    }

    public function export(): void
    {
        if (!$this->hasPermission('refunds', 'view') && !$this->hasPermission('refunds', 'edit')) {
            $this->requirePermission('refunds', 'view');
        }

        $statusMap = [
            'pending'   => ['processing'],
            'completed' => ['completed'],
            'all'       => ['processing', 'completed'],
        ];
        $tab = $_GET['tab'] ?? 'pending';
        $requests = (new RefundRequest())->getAllByStatuses($statusMap[$tab] ?? $statusMap['pending']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=refunds_export_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \RuntimeException('Unable to open refund export output.');
        }

        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, [
            'Request ID', 'Order Number', 'Buyer Name', 'Email', 'Refund Amount',
            'Refund Method', 'Refund Details', 'Refund Reference', 'Status',
            'Requested At', 'Approved At', 'Refund Processed At'
        ]);
        foreach ($requests as $request) {
            $approvedAt = $request['approved_at'] ?? $request['processed_at'] ?? $request['updated_at'] ?? '';
            fputcsv($output, [
                $request['id'],
                $request['order_number'],
                $request['user_name'],
                $request['user_email'],
                $request['amount'],
                $request['refund_method'] ?? '',
                $request['refund_details'] ?? '',
                $request['razorpay_refund_id'] ?? $request['cashfree_refund_id'] ?? '',
                $request['status'],
                $request['created_at'],
                $approvedAt,
                strtolower(trim($request['status'])) === 'completed' ? ($request['processed_at'] ?? '') : '',
            ]);
        }

        fclose($output);
        exit;
    }

    // -------------------------------------------------------------------------
    // POST /admin/cancellations/refunds/process
    // -------------------------------------------------------------------------

    public function process(): void
    {
        $this->requirePermission('refunds', 'edit');
        header('Content-Type: application/json');

        $input       = $this->parseJsonInput();
        $refundReqId = (int)($input['refund_request_id'] ?? 0);
        $adminNote   = trim($input['admin_notes'] ?? '');
        $adminId     = (int)Session::get('user_id');

        if (!$refundReqId) {
            $this->json(['success' => false, 'message' => 'Invalid request ID.'], 422);
            return;
        }

        $refundModel = new RefundRequest();
        $req         = $refundModel->findById($refundReqId);

        if (!$req) {
            $this->json(['success' => false, 'message' => 'Refund request not found.'], 404);
            return;
        }

        if (strtolower(trim($req['status'])) !== 'processing') {
            $this->json([
                'success' => false,
                'message' => "Only approved (processing) requests can be refunded. Current status: {$req['status']}",
            ], 409);
            return;
        }

        // ---- Force Manual / Direct Refund ----
        $refundModel->updateStatus($refundReqId, 'completed', [
            'admin_notes'  => $adminNote ?: 'Manual refund processed.',
            'processed_by' => $adminId,
            'cashfree_refund_id' => 'MANUAL-' . time(),
            'razorpay_refund_id' => 'MANUAL-' . time()
        ]);
        
        $orderModel = new Order();
        $orderModel->updateOrderCancellationStatus((int)$req['order_id'], 'refund_completed');
        $orderModel->restoreStockForOrderId((int)$req['order_id']);

        try {
            $user = (new User())->findById((int)$req['user_id']);
            if ($user) {
                (new EmailService())->sendRefundProcessed($user, $req, (float)$req['amount']);
            }
        } catch (\Exception $e) {
            error_log('[RefundController::process] Email: ' . $e->getMessage());
        }

        $this->json(['success' => true, 'message' => 'Refund confirmed and marked as completed manually.']);
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
