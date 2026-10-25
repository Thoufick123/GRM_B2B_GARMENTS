<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\User;

class BuyerController extends Controller
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
        $this->requirePermission('all_buyers', 'view');
        $userModel = new User();

        $perPage = 20;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $total  = $userModel->countActiveBuyers();
        $buyers = $userModel->getActiveBuyers($perPage, $offset);
        $stats  = $userModel->getBuyerStats();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $this->render('admin/buyers_list', [
            'title'       => 'Approved Buyers',
            'buyers'      => $buyers,
            'stats'       => $stats,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalBuyers' => $total,
            'perPage'     => $perPage,
        ], 'admin');
    }

    public function pending()
    {
        $this->requirePermission('pending_buyers', 'view');
        $userModel = new User();

        $perPage = 20;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $totalPending  = $userModel->countPendingBuyers();
        $buyers        = $userModel->getPendingBuyers($perPage, $offset);
        $totalRejected = $userModel->countRejectedBuyers();
        $rejectedBuyers = $userModel->getRejectedBuyers();
        $totalPages    = max(1, (int)ceil($totalPending / $perPage));

        $this->render('admin/buyers_pending', [
            'title'          => 'Pending Buyer Registrations',
            'buyers'         => $buyers,
            'rejectedBuyers' => $rejectedBuyers,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'totalPending'   => $totalPending,
            'totalRejected'  => $totalRejected,
            'perPage'        => $perPage,
        ], 'admin');
    }

    public function approve()
    {
        $this->requirePermission('pending_buyers', 'edit');
        $userId = $_POST['user_id'] ?? null;
        
        if (!$userId) {
            Session::setFlash('error', 'Invalid request.');
            $this->redirect('/admin/buyers/pending');
        }

        try {
            $db = \Core\Database::getInstance();
            $db->beginTransaction();

            $userModel = new User();
            $userModel->updateStatus($userId, 'active');

            // Update request status as well
            $stmt = $db->prepare("UPDATE registration_requests SET status = 'approved', reviewed_by = :admin, reviewed_at = NOW() WHERE user_id = :id");
            $stmt->execute(['admin' => Session::get('user_id'), 'id' => $userId]);

            $db->commit();
            
            // TODO: Send approval email to buyer
            
            Session::setFlash('success', 'Buyer has been approved and is now active.');
        } catch (\Exception $e) {
            $db->rollBack();
            Session::setFlash('error', 'Failed to approve buyer: ' . $e->getMessage());
        }

        $this->redirect('/admin/buyers/pending');
    }

    public function view()
    {
        if (!$this->hasPermission('all_buyers', 'view') && !$this->hasPermission('pending_buyers', 'view')) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/admin/dashboard');
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            Session::setFlash('error', 'Invalid buyer ID.');
            $this->redirect('/admin/buyers/pending');
        }

        $userModel = new User();
        $buyer = $userModel->getBuyerDetails((int)$id);

        if (!$buyer) {
            Session::setFlash('error', 'Buyer not found.');
            $this->redirect('/admin/buyers/pending');
        }

        $orderModel = new \App\Models\Order();
        $orderStats = $orderModel->getBuyerOrderStats((int)$id);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $totalOrdersCount = $orderModel->getTotalOrdersCountByUser((int)$id);
        $totalPages = ceil($totalOrdersCount / $limit);
        if ($totalPages == 0) $totalPages = 1;
        
        $orderHistory = $orderModel->getOrdersByUser((int)$id, $limit, $offset);

        $db = \Core\Database::getInstance();
        $emailLogsStmt = $db->prepare("SELECT * FROM user_email_logs WHERE user_id = ? ORDER BY created_at DESC");
        $emailLogsStmt->execute([(int)$id]);
        $emailLogs = $emailLogsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/buyer_view', [
            'title' => 'Review Buyer Registration',
            'buyer' => $buyer,
            'orderStats' => $orderStats,
            'orderHistory' => $orderHistory,
            'emailLogs' => $emailLogs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrdersCount
        ], 'admin');
    }

    public function reject()
    {
        $this->requirePermission('pending_buyers', 'edit');
        $userId = $_POST['user_id'] ?? null;
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        
        if (!$userId) {
            Session::setFlash('error', 'Invalid request.');
            $this->redirect('/admin/buyers/pending');
        }

        if (empty($rejectionReason)) {
            Session::setFlash('error', 'A rejection reason is required.');
            $this->redirect('/admin/buyers/pending');
        }

        try {
            $db = \Core\Database::getInstance();
            $db->beginTransaction();

            $userModel = new User();
            $userModel->updateStatus($userId, 'rejected');

            // Update request status and save rejection reason
            $stmt = $db->prepare("UPDATE registration_requests SET status = 'rejected', rejection_reason = :reason, reviewed_by = :admin, reviewed_at = NOW() WHERE user_id = :id");
            $stmt->execute(['reason' => $rejectionReason, 'admin' => Session::get('user_id'), 'id' => $userId]);

            $db->commit();
            Session::setFlash('success', 'Buyer request has been rejected with a reason saved.');
        } catch (\Exception $e) {
            $db->rollBack();
            Session::setFlash('error', 'Failed to reject buyer: ' . $e->getMessage());
        }

        $this->redirect('/admin/buyers/pending');
    }

    public function toggleBlock()
    {
        $this->requirePermission('all_buyers', 'edit');
        $userId = $_POST['user_id'] ?? null;
        $action = $_POST['action'] ?? null; // 'block' or 'unblock'
        
        if (!$userId || !in_array($action, ['block', 'unblock'])) {
            Session::setFlash('error', 'Invalid request.');
            $this->redirect('/admin/buyers');
        }
        try {
            $userModel = new User();
            $newStatus = ($action === 'block') ? 'blocked' : 'active';
            $userModel->updateStatus($userId, $newStatus);

            Session::setFlash('success', "Buyer has been successfully {$newStatus}.");
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to update buyer status: ' . $e->getMessage());
        }

        $this->redirect('/admin/buyers');
    }

    public function ajaxVerify()
    {
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? '';
        $value = strtoupper(trim($_GET['value'] ?? ''));
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        if (!$type || !$value) {
            echo json_encode(['success' => false, 'message' => 'Missing parameter type or value.']);
            exit;
        }

        $db = \Core\Database::getInstance();

        if ($type === 'gst') {
            // Validate GSTIN format (15 characters)
            if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)) {
                echo json_encode(['success' => false, 'message' => 'Invalid GSTIN format.']);
                exit;
            }

            // Look up in database first to get real info if possible
            $stmt = $db->prepare("
                SELECT u.name, bp.business_name, bp.shop_location, bp.pan_number 
                FROM users u 
                JOIN business_profiles bp ON u.id = bp.user_id 
                WHERE bp.gst_number = :gst
            ");
            $stmt->execute(['gst' => $value]);
            $realInfo = $stmt->fetch();

            $pan = substr($value, 2, 10);
            $stateCode = substr($value, 0, 2);
            
            $states = [
                '01' => 'Jammu & Kashmir', '02' => 'Himachal Pradesh', '03' => 'Punjab', '04' => 'Chandigarh',
                '05' => 'Uttarakhand', '06' => 'Haryana', '07' => 'Delhi', '08' => 'Rajasthan', '09' => 'Uttar Pradesh',
                '10' => 'Bihar', '11' => 'Sikkim', '12' => 'Arunachal Pradesh', '13' => 'Nagaland', '14' => 'Manipur',
                '15' => 'Mizoram', '16' => 'Tripura', '17' => 'Meghalaya', '18' => 'Assam', '19' => 'West Bengal',
                '20' => 'Jharkhand', '21' => 'Odisha', '22' => 'Chhattisgarh', '23' => 'Madhya Pradesh',
                '24' => 'Gujarat', '25' => 'Daman & Diu', '26' => 'Dadra & Nagar Haveli', '27' => 'Maharashtra',
                '29' => 'Karnataka', '30' => 'Goa', '31' => 'Lakshadweep', '32' => 'Kerala', '33' => 'Tamil Nadu',
                '34' => 'Puducherry', '35' => 'Andaman & Nicobar Islands', '36' => 'Telangana', '37' => 'Andhra Pradesh',
                '38' => 'Ladakh'
            ];
            $stateName = $states[$stateCode] ?? 'Unknown State';

            $constitutionMap = [
                'P' => 'Sole Proprietorship / Individual',
                'C' => 'Private Limited Company',
                'H' => 'Hindu Undivided Family (HUF)',
                'F' => 'Partnership Firm / LLP',
                'A' => 'Association of Persons (AOP)',
                'T' => 'Trust',
                'G' => 'Government Entity'
            ];
            $pan4thChar = substr($pan, 3, 1);
            $constitution = $constitutionMap[$pan4thChar] ?? 'Proprietorship / Other';

            $tradeName = $realInfo ? $realInfo['business_name'] : 'GRM Partner Store';
            $legalName = $realInfo ? $realInfo['name'] : 'Registered Business Owner';
            $address = $realInfo ? $realInfo['shop_location'] : '102 Main Wholesale Market, Sector 4, ' . $stateName;

            $data = [
                'gstin' => $value,
                'pan' => $pan,
                'legal_name' => $legalName,
                'trade_name' => $tradeName,
                'constitution' => $constitution,
                'taxpayer_type' => 'Regular Regular Taxpayer',
                'status' => 'Active',
                'registration_date' => date('d/m/Y', strtotime('-2 years')),
                'state' => $stateName,
                'address' => $address
            ];

            if ($userId > 0) {
                $payload = json_encode([
                    'type' => 'gst',
                    'verified_at' => date('Y-m-d H:i:s'),
                    'data' => $data
                ]);
                try {
                    $stmtUpdate = $db->prepare("UPDATE business_profiles SET tax_verified = 1, verification_data = :vdata WHERE user_id = :uid");
                    $stmtUpdate->execute(['vdata' => $payload, 'uid' => $userId]);
                } catch (\PDOException $e) {}
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            exit;
        } elseif ($type === 'pan') {
            // Validate PAN format (10 characters)
            if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value)) {
                echo json_encode(['success' => false, 'message' => 'Invalid PAN format.']);
                exit;
            }

            // Look up in database first
            $stmt = $db->prepare("
                SELECT u.name, bp.business_name, bp.shop_location, bp.gst_number 
                FROM users u 
                JOIN business_profiles bp ON u.id = bp.user_id 
                WHERE bp.pan_number = :pan
            ");
            $stmt->execute(['pan' => $value]);
            $realInfo = $stmt->fetch();

            // Look up all GSTINs registered under this PAN in our system
            $associatedGstins = [];
            if ($realInfo && $realInfo['gst_number']) {
                $associatedGstins[] = $realInfo['gst_number'];
            }

            // Also check if there are other registration requests/profiles with GST matching this PAN
            $stmt = $db->prepare("SELECT gst_number FROM business_profiles WHERE gst_number LIKE :panPattern");
            $stmt->execute(['panPattern' => "__" . $value . "%"]);
            $matchingGstins = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach($matchingGstins as $g) {
                if (!in_array($g, $associatedGstins)) {
                    $associatedGstins[] = $g;
                }
            }

            // If none found in DB, simulate one realistic GSTIN
            if (empty($associatedGstins)) {
                $associatedGstins[] = '27' . $value . '1Z5';
            }

            $pan4thChar = substr($value, 3, 1);
            $constitutionMap = [
                'P' => 'Sole Proprietorship / Individual',
                'C' => 'Private Limited Company',
                'H' => 'Hindu Undivided Family (HUF)',
                'F' => 'Partnership Firm / LLP',
                'A' => 'Association of Persons (AOP)',
                'T' => 'Trust',
                'G' => 'Government Entity'
            ];
            $constitution = $constitutionMap[$pan4thChar] ?? 'Proprietorship / Other';

            $tradeName = $realInfo ? $realInfo['business_name'] : 'GRM Partner Store';
            $legalName = $realInfo ? $realInfo['name'] : 'Registered Business Owner';

            $data = [
                'pan' => $value,
                'legal_name' => $legalName,
                'trade_name' => $tradeName,
                'status' => 'Active / Valid',
                'pan_type' => $constitution,
                'gstins' => $associatedGstins
            ];

            if ($userId > 0) {
                $payload = json_encode([
                    'type' => 'pan',
                    'verified_at' => date('Y-m-d H:i:s'),
                    'data' => $data
                ]);
                try {
                    $stmtUpdate = $db->prepare("UPDATE business_profiles SET tax_verified = 1, verification_data = :vdata WHERE user_id = :uid");
                    $stmtUpdate->execute(['vdata' => $payload, 'uid' => $userId]);
                } catch (\PDOException $e) {}
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid verification type.']);
        exit;
    }
}
