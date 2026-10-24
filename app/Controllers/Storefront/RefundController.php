<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use App\Services\EmailService;

/**
 * RefundController (Storefront — User side)
 *
 * Route: POST /dashboard/orders/request-refund
 *
 * Handles the user's cancellation / refund request submission.
 * Security:
 *  - Must be logged in (not admin)
 *  - Order must belong to the logged-in user
 *  - Order must be in an eligible status (placed / packed)
 *  - Only one pending request per order
 */
class RefundController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!Session::get('user_id')) {
            Session::setFlash('error', 'Please login to continue.');
            $this->redirect('/login');
        }

        // Block admin/vendor roles from accessing user-facing refund routes
        if (in_array(Session::get('user_role'), ['super_admin', 'manager', 'staff', 'vendor'])) {
            $this->redirect('/admin/dashboard');
        }
    }

    // -------------------------------------------------------------------------
    // POST /dashboard/orders/request-refund
    // -------------------------------------------------------------------------

    public function submitCancellation(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        $userId = (int)Session::get('user_id');

        // ---- Input & validation ----
        $input            = $this->parseJsonOrPost();
        $orderId          = (int)($input['order_id'] ?? 0);
        $reason           = trim($input['reason'] ?? '');
        $cancellationType = trim($input['cancellation_type'] ?? 'full');
        $refundMethod     = trim($input['refund_method'] ?? '');
        $refundDetails    = trim($input['refund_details'] ?? '');

        if (!$orderId) {
            $this->json(['success' => false, 'message' => 'Invalid order.'], 422);
        }

        if (empty($reason) || strlen($reason) < 5) {
            $this->json(['success' => false, 'message' => 'Please provide a reason (min 5 characters).'], 422);
        }

        if (empty($refundMethod) || !in_array($refundMethod, ['UPI ID', 'Phone Number'])) {
            $this->json(['success' => false, 'message' => 'Please select a valid refund method.'], 422);
        }

        if (empty($refundDetails)) {
            $this->json(['success' => false, 'message' => 'Please provide your refund details.'], 422);
        }

        if ($refundMethod === 'Phone Number' && !preg_match('/^\d{10}$/', $refundDetails)) {
            $this->json(['success' => false, 'message' => 'Please provide a valid 10-digit phone number.'], 422);
        }

        if (!in_array($cancellationType, ['full', 'partial', 'Full', 'Partial'])) {
            $cancellationType = 'Full';
        }
        
        $cancellationType = ucfirst(strtolower($cancellationType));

        // ---- Load order (must belong to this user) ----
        $orderModel = new Order();
        $order      = $orderModel->getOrderById($orderId, $userId);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // ---- Check Cancellation Rules & Eligibility ----
        $ruleModel = new \App\Models\CancellationRule();
        $rule = $ruleModel->getRuleForOrder($order);

        if (!$rule['eligible']) {
            $msg = !empty($rule['reason']) ? $rule['reason'] : "This order cannot be cancelled under status '" . ucwords(str_replace('_', ' ', $order['status'])) . "' based on active cancellation policy.";
            $this->json([
                'success' => false,
                'message' => $msg
            ], 422);
        }

        // ---- Only one request per order ----
        $refundModel   = new RefundRequest();
        $existingRequest = $refundModel->findByOrderId($orderId);

        if ($existingRequest) {
            $this->json(['success' => false, 'message' => 'A cancellation request already exists for this order.'], 409);
        }

        // ---- Determine refund amount based on Cancellation Rules ----
        $totalAmount = (float)($order['grand_total'] ?? $order['total_amount']);
        $feeAmount = (float)$rule['cancellation_fee'];
        
        $refundAmount = max(0, $totalAmount - $feeAmount);
        // ---- Persist refund request ----
        try {
            $refundId = $refundModel->create([
                'order_id'          => $orderId,
                'user_id'           => $userId,
                'amount'            => $refundAmount,
                'reason'            => $reason,
                'cancellation_type' => $cancellationType,
                'cashfree_order_id' => $order['cashfree_order_id'] ?? null,
                'refund_method'     => $refundMethod,
                'refund_details'    => $refundDetails,
            ]);

            // Update order status → refund_pending
            $orderModel->updateOrderCancellationStatus($orderId, 'refund_pending', $reason, 'buyer');

        } catch (\Exception $e) {
            error_log('[RefundController::submitCancellation] DB error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Could not save your request. Please try again.'], 500);
        }

        // ---- Send confirmation email (non-blocking) ----
        try {
            $userModel = new User();
            $user      = $userModel->findById($userId);
            if ($user) {
                (new EmailService())->sendCancellationRequested($user, $order);
            }
        } catch (\Exception $e) {
            error_log('[RefundController::submitCancellation] Email error: ' . $e->getMessage());
            // Do NOT fail the request if email fails
        }

        $this->json([
            'success'   => true,
            'message'   => 'Your cancellation request has been submitted. Our team will review it shortly.',
            'refund_id' => $refundId,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Accept both JSON body and classic POST form data */
    private function parseJsonOrPost(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }
}
