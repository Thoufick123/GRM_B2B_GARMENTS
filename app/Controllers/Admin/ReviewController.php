<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Review;

class ReviewController extends Controller
{
    private $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new Review();
        
        // Ensure user is logged in and is admin
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
        $this->requirePermission('reviews', 'view');

        $role = Session::get('user_role');
        $userId = (int)Session::get('user_id');

        if ($role === 'vendor') {
            $allReviews = $this->reviewModel->getVendorReviews($userId);
            $standardReviews = array_values(array_filter($allReviews, fn($r) => empty($r['customization_id'])));
            $customizedReviews = array_values(array_filter($allReviews, fn($r) => !empty($r['customization_id'])));
        } else {
            $standardReviews = $this->reviewModel->getAdminReviews('standard');
            $customizedReviews = $this->reviewModel->getAdminReviews('customized');
            $allReviews = $this->reviewModel->getAdminReviews();
        }

        $this->render('admin/reviews/index', [
            'title'             => ($role === 'vendor') ? 'Customer Reviews' : 'Reviews Management',
            'reviews'           => $allReviews,
            'standardReviews'   => $standardReviews,
            'customizedReviews' => $customizedReviews
        ], 'admin');
    }

    public function updateStatus()
    {
        header('Content-Type: application/json');
        
        if (!$this->hasPermission('reviews', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $reviewId = $input['review_id'] ?? null;
        $status = $input['status'] ?? null; // 'approve' or 'reject'

        if (!$reviewId || !in_array($status, ['approve', 'reject'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            return;
        }

        $role = Session::get('user_role');
        $userId = (int)Session::get('user_id');

        if ($role === 'vendor') {
            if (!$this->reviewModel->isReviewOwnedByVendor((int)$reviewId, $userId)) {
                echo json_encode(['success' => false, 'message' => 'You do not have permission to manage this review.']);
                return;
            }
        }

        $isApproved = ($status === 'approve');
        
        try {
            if ($this->reviewModel->updateStatus((int)$reviewId, $isApproved)) {
                echo json_encode(['success' => true, 'message' => 'Review status updated.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
            }
        } catch (\Throwable $e) {
            error_log("Review status update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to update review status: ' . $e->getMessage()]);
        }
    }
}
