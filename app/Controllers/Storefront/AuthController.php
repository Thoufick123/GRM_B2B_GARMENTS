<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\RegistrationRequest;
use App\Models\Media;
use App\Models\MediaUpload;
use App\Core\CloudinaryUploader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller
{
    public function showLogin()
    {
        $redirect = $_GET['redirect'] ?? Session::get('return_to');
        if (!empty($redirect) && is_string($redirect)) {
            $decoded = rawurldecode($redirect);
            if (str_starts_with($decoded, '/') && !str_starts_with($decoded, '//')) {
                Session::set('return_to', $decoded);
                $redirect = $decoded;
            } elseif (str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
                Session::set('return_to', $redirect);
            } else {
                $redirect = null;
            }
        } else {
            $redirect = null;
        }

        if (Session::get('user_id')) {
            if (!empty($redirect)) {
                Session::remove('return_to');
                $this->redirect($redirect);
                return;
            }
            $this->redirect('/');
            return;
        }

        $this->render('storefront/login', [
            'title' => 'Login',
            'redirect' => $redirect
        ]);
    }

    public function login()
    {
        $loginInput = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? Session::get('return_to') ?? '');
        $redirectParam = !empty($redirect) ? '?redirect=' . urlencode($redirect) : '';

        if (empty($loginInput) || empty($password)) {
            Session::setFlash('error', 'Please fill in all fields.');
            $this->redirect('/login' . $redirectParam);
            return;
        }

        $userModel = new User();
        // Check by lowercased email, exact email, or phone number
        $user = $userModel->findByEmail(strtolower($loginInput));
        if (!$user) {
            $user = $userModel->findByEmail($loginInput);
        }
        if (!$user) {
            $user = $userModel->findByPhone($loginInput);
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'blocked' || $user['status'] === 'inactive' || $user['status'] === 'deactivated') {
                Session::setFlash('error', 'Your account has been deactivated or blocked by the administrator.');
                $this->redirect('/login' . $redirectParam);
                return;
            }

            if ($user['status'] === 'rejected') {
                // Fetch rejection reason
                $db = \Core\Database::getInstance();
                $stmt = $db->prepare("SELECT rejection_reason FROM registration_requests WHERE user_id = :uid ORDER BY reviewed_at DESC LIMIT 1");
                $stmt->execute(['uid' => $user['id']]);
                $reason = $stmt->fetchColumn();

                $msg = 'Your registration has been rejected by the admin. ';
                if ($reason) {
                    $msg .= 'Reason: ' . htmlspecialchars($reason) . ' ';
                }
                $msg .= 'Please re-register with corrected details.';
                Session::setFlash('rejected_reason', $msg);
                $this->redirect('/register' . $redirectParam);
                return;
            }

            if ($user['status'] === 'pending') {
                Session::setFlash('error', 'Your registration is pending admin approval. Please check back later.');
                $this->redirect('/login' . $redirectParam);
                return;
            }

            if ($user['role'] === 'vendor' && !is_vendor_module_enabled()) {
                Session::setFlash('error', 'Vendor access and operations are currently disabled by platform administration.');
                $this->redirect('/login' . $redirectParam);
                return;
            }
            
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            Session::set('user_status', $user['status']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email'] ?? '');
            
            // If they are admin/manager/vendor, redirect to admin panel
            if (in_array($user['role'], ['super_admin', 'manager', 'staff', 'vendor'])) {
                if ($user['role'] === 'staff') {
                    // Check if staff has dashboard permission, else redirect directly to first authorized module
                    $db = \Core\Database::getInstance();
                    $stmt = $db->prepare("
                        SELECT u.permissions AS custom_permissions, r.permissions AS role_permissions
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$user['id']]);
                    $pRow = $stmt->fetch();
                    $permsJson = !empty($pRow['role_permissions']) ? $pRow['role_permissions'] : ($pRow['custom_permissions'] ?? null);
                    $perms = is_array($permsJson) ? $permsJson : (json_decode($permsJson ?? '', true) ?? []);
                    
                    if (empty($perms['dashboard']['view'])) {
                        $fallbackRoutes = [
                            'all_buyers'            => '/admin/buyers',
                            'pending_buyers'        => '/admin/buyers/pending',
                            'all_orders'            => '/admin/orders',
                            'pending_payments'      => '/admin/orders/pending',
                            'bill_modifications'    => '/admin/order-modifications',
                            'reviews'               => '/admin/reviews',
                            'all_cancellations'     => '/admin/cancellations',
                            'refunds'               => '/admin/cancellations/refunds',
                            'cancellation_rules'    => '/admin/cancellations/rules',
                            'categories'            => '/admin/catalog/categories',
                            'subcategories'         => '/admin/catalog/subcategories',
                            'attributes'            => '/admin/catalog/attributes',
                            'products'              => '/admin/catalog/products',
                            'fabric_customizations' => '/admin/fabric-customizations',
                            'custom_orders'         => '/admin/customize/orders',
                            'inventory'             => '/admin/inventory',
                            'order_rules'           => '/admin/order-rules',
                            'fee_rules'             => '/admin/fee-rules',
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
                            if (!empty($perms[$mod])) {
                                $hasAny = false;
                                foreach (['view', 'create', 'edit', 'delete'] as $act) {
                                    if (!empty($perms[$mod][$act])) { $hasAny = true; break; }
                                }
                                if ($hasAny) {
                                    $this->redirect($route);
                                    return;
                                }
                            }
                        }
                    }
                }
                $this->redirect('/admin/dashboard');
                return;
            }

            Session::setFlash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

            // Buyer dynamic redirection (e.g. returning to product details after Instagram referral or direct link)
            Session::remove('return_to');
            $targetUrl = '/';
            if (!empty($redirect)) {
                $decodedRedirect = rawurldecode($redirect);
                $checkUrl = (str_starts_with($decodedRedirect, '/') && !str_starts_with($decodedRedirect, '//')) ? $decodedRedirect : $redirect;
                if (str_starts_with($checkUrl, '/') && !str_starts_with($checkUrl, '//')) {
                    $parsedPath = parse_url($checkUrl, PHP_URL_PATH);
                    if (!in_array($parsedPath, ['/login', '/logout', '/register', '/register/verify'])) {
                        $targetUrl = $checkUrl;
                    }
                }
            }

            $this->redirect($targetUrl);
            return;
        } else {
            Session::setFlash('error', 'Invalid email/phone or password. Please try again.');
            $this->redirect('/login' . $redirectParam);
            return;
        }
    }

    public function showRegister()
    {
        $redirect = $_GET['redirect'] ?? Session::get('return_to');
        if (!empty($redirect) && is_string($redirect)) {
            $decoded = rawurldecode($redirect);
            if (str_starts_with($decoded, '/') && !str_starts_with($decoded, '//')) {
                Session::set('return_to', $decoded);
            }
        }

        if (Session::get('user_id')) {
            if (!empty($redirect)) {
                Session::remove('return_to');
                $this->redirect($redirect);
                return;
            }
            $this->redirect('/');
            return;
        }
        $this->render('storefront/register', [
            'title' => 'Register as Buyer',
            'redirect' => $redirect
        ]);
    }

    public function register()
    {
        // 1. Gather Data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $businessName = strtoupper(trim($_POST['business_name'] ?? ''));
        
        $shopLocationRaw = trim($_POST['shop_location'] ?? '');
        $state = trim($_POST['state'] ?? '');
        if ($state === 'Others') {
            $state = trim($_POST['other_state_name'] ?? '');
        }
        $district = trim($_POST['district'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        
        $shopLocation = $shopLocationRaw;
        if ($district) $shopLocation .= ', ' . $district;
        if ($state) $shopLocation .= ', ' . $state;
        if ($pincode) $shopLocation .= ' - ' . $pincode;

        $hasGst = isset($_POST['has_gst']) ? (bool)$_POST['has_gst'] : false;
        $gstNumber = trim($_POST['gst_number'] ?? '');
        $panNumber = trim($_POST['pan_number'] ?? '');
        $noGstReason = trim($_POST['no_gst_reason'] ?? '');
        if ($noGstReason === 'Other') {
            $otherGstReason = trim($_POST['other_gst_reason'] ?? '');
            if (!empty($otherGstReason)) {
                $noGstReason = $otherGstReason;
            }
        }
        $instagramLink = trim($_POST['instagram_link'] ?? '');
        
        // 2. Validation
        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($businessName) || empty($shopLocationRaw) || empty($state) || empty($district) || empty($pincode) || empty($instagramLink)) {
            Session::setFlash('error', 'All core fields including full address and Instagram link are required.');
            $this->redirect('/register');
        }

        if (strlen($businessName) < 4) {
            Session::setFlash('error', 'Business Name must be at least 4 characters long.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Please provide a valid email address.');
            $this->redirect('/register');
        }

        // Prevent common email typos like .kom
        if (preg_match('/\.kom$/i', $email)) {
            Session::setFlash('error', 'Invalid email domain format (e.g. .kom is not allowed).');
            $this->redirect('/register');
        }

        // Password validation: minimum 8 characters, at least one uppercase, one lowercase, and one number
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            Session::setFlash('error', 'Password must be at least 8 characters long and contain an uppercase letter, a lowercase letter, and a number.');
            $this->redirect('/register');
        }

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            Session::setFlash('error', 'Please provide a valid 10-digit phone number.');
            $this->redirect('/register');
        }

        if ($hasGst) {
            if (empty($gstNumber)) {
                Session::setFlash('error', 'GST Number is required if you select that you have one.');
                $this->redirect('/register');
            }
            if (!preg_match('/^(?=.*[0-9])(?=.*[A-Z])[A-Z0-9]{15}$/i', $gstNumber)) {
                Session::setFlash('error', 'Please enter a valid 15-character GST number containing both letters and numbers.');
                $this->redirect('/register');
            }
        }

        if (!$hasGst) {
            if (empty($panNumber)) {
                Session::setFlash('error', 'PAN Number is required for Non-GST verification.');
                $this->redirect('/register');
            }
            if (!preg_match('/^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/', $panNumber)) {
                Session::setFlash('error', 'Please enter a valid PAN format (e.g., ABCDE1234F).');
                $this->redirect('/register');
            }
            if (empty($noGstReason)) {
                Session::setFlash('error', 'Please provide a reason for not having GST.');
                $this->redirect('/register');
            }
            if (strpos($noGstReason, 'Other: ') === 0) {
                $otherReasonText = substr($noGstReason, 7);
                if (strlen($otherReasonText) < 5 || !preg_match('/^[A-Za-z\s]+$/', $otherReasonText)) {
                    Session::setFlash('error', 'The specific other reason must be at least 5 characters long and contain only letters.');
                    $this->redirect('/register');
                }
            }
        }

        // Validate photos (at least 2 required, maximum 4 allowed)
        if (!isset($_FILES['shop_photos']) || count($_FILES['shop_photos']['name']) < 2 || $_FILES['shop_photos']['error'][0] === UPLOAD_ERR_NO_FILE) {
            Session::setFlash('error', 'Please upload at least 2 shop photos for verification.');
            $this->redirect('/register');
        }

        if (count($_FILES['shop_photos']['name']) > 4) {
            Session::setFlash('error', 'You can upload a maximum of 4 shop photos.');
            $this->redirect('/register');
        }

        // Check uniqueness — but allow re-registration for rejected accounts
        $userModel = new User();
        $existingByEmail = $userModel->findByEmail($email);
        if ($existingByEmail) {
            if ($existingByEmail['status'] === 'rejected') {
                // Delete old rejected account so they can re-register
                $db = \Core\Database::getInstance();
                $db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $existingByEmail['id']]);
            } else {
                Session::setFlash('error', 'Email address is already registered.');
                $this->redirect('/register');
            }
        }
        
        $existingByPhone = $userModel->findByPhone($phone);
        if ($existingByPhone) {
            if ($existingByPhone['status'] === 'rejected') {
                // Delete old rejected account so they can re-register
                $db = $db ?? \Core\Database::getInstance();
                $db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $existingByPhone['id']]);
            } else {
                Session::setFlash('error', 'Phone number is already registered.');
                $this->redirect('/register');
            }
        }

        $tempFiles = [];
        $uploader = new CloudinaryUploader();
        $uploadedCount = 0;

        foreach ($_FILES['shop_photos']['tmp_name'] as $key => $tmpName) {
            if ($uploadedCount >= 4) break; // Enforce maximum 4 shop photos
            if ($_FILES['shop_photos']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['shop_photos']['name'][$key], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

                $cloudinaryResponse = $uploader->uploadMedia($tmpName);
                
                if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                    $tempFiles[] = [
                        'path' => $cloudinaryResponse['secure_url'],
                        'type' => 'image/webp'
                    ];
                    $uploadedCount++;
                }
            }
        }

        // Handle GST / PAN document upload (max 3MB)
        $gstDocUrl  = null;
        $panDocUrl  = null;
        $allowedDocExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $maxDocBytes    = 3 * 1024 * 1024; // 3MB

        if ($hasGst && isset($_FILES['gst_document']) && $_FILES['gst_document']['error'] === UPLOAD_ERR_OK) {
            $docSize = $_FILES['gst_document']['size'];
            $docExt  = strtolower(pathinfo($_FILES['gst_document']['name'], PATHINFO_EXTENSION));
            if ($docSize > $maxDocBytes) {
                Session::setFlash('error', 'GST document must be under 3MB.');
                $this->redirect('/register');
            }
            if (!in_array($docExt, $allowedDocExts)) {
                Session::setFlash('error', 'GST document must be a PDF, JPG, or PNG file.');
                $this->redirect('/register');
            }
            $cloudinaryResponse = $uploader->uploadMedia($_FILES['gst_document']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $gstDocUrl = $cloudinaryResponse['secure_url'];
            }
        }

        if (!$hasGst && isset($_FILES['pan_document']) && $_FILES['pan_document']['error'] === UPLOAD_ERR_OK) {
            $docSize = $_FILES['pan_document']['size'];
            $docExt  = strtolower(pathinfo($_FILES['pan_document']['name'], PATHINFO_EXTENSION));
            if ($docSize > $maxDocBytes) {
                Session::setFlash('error', 'PAN document must be under 3MB.');
                $this->redirect('/register');
            }
            if (!in_array($docExt, $allowedDocExts)) {
                Session::setFlash('error', 'PAN document must be a PDF, JPG, or PNG file.');
                $this->redirect('/register');
            }
            $cloudinaryResponse = $uploader->uploadMedia($_FILES['pan_document']['tmp_name']);
            if ($cloudinaryResponse && isset($cloudinaryResponse['secure_url'])) {
                $panDocUrl = $cloudinaryResponse['secure_url'];
            }
        }

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Store all data in session for the next step
        Session::set('pending_registration', [
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => $password, // Plaintext, will be hashed upon final save
            'business_name' => $businessName,
            'shop_location' => $shopLocation,
            'shop_address_line1' => $shopLocationRaw,
            'shop_city' => $district,
            'shop_state' => $state,
            'shop_postal_code' => $pincode,
            'has_gst'       => $hasGst,
            'gst_number'    => $gstNumber,
            'gst_doc_url'   => $gstDocUrl,
            'pan_number'    => $panNumber,
            'pan_doc_url'   => $panDocUrl,
            'no_gst_reason' => $noGstReason,
            'instagram_link'=> $instagramLink,
            'temp_files'    => $tempFiles
        ]);
        Session::set('registration_otp', $otp);
        Session::set('otp_generated_at', time());

        // Send OTP via EmailService
        try {
            $emailService = new \App\Services\EmailService();
            $emailService->sendOtpEmail($email, $otp, $name);
        } catch (\Throwable $e) {
            error_log("[Register OTP Email Error] " . $e->getMessage());
        }

        Session::setFlash('success', "OTP has been sent to your email address!");
        $this->redirect('/register/verify');
    }

    public function checkUserExists()
    {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $businessName = trim($data['business_name'] ?? '');
        $gstNumber = strtoupper(trim($data['gst_number'] ?? ''));
        $panNumber = strtoupper(trim($data['pan_number'] ?? ''));
        $instagramLink = trim($data['instagram_link'] ?? '');

        if (empty($email) && empty($phone) && empty($businessName) && empty($gstNumber) && empty($panNumber) && empty($instagramLink)) {
            echo json_encode(['exists' => false]);
            return;
        }

        $userModel = new User();
        
        if (!empty($phone) && $userModel->findByPhone($phone)) {
            echo json_encode([
                'exists' => true,
                'field' => 'phone',
                'message' => 'This phone number is already registered.'
            ]);
            return;
        }

        if (!empty($email) && $userModel->findByEmail($email)) {
            echo json_encode([
                'exists' => true,
                'field' => 'email',
                'message' => 'This email address is already registered.'
            ]);
            return;
        }

        $db = \Core\Database::getInstance();

        if (!empty($businessName)) {
            $stmt = $db->prepare("SELECT id FROM business_profiles WHERE LOWER(business_name) = LOWER(?)");
            $stmt->execute([$businessName]);
            if ($stmt->fetch()) {
                echo json_encode([
                    'exists' => true,
                    'field' => 'business',
                    'message' => 'This business name is already registered.'
                ]);
                return;
            }
        }

        if (!empty($gstNumber)) {
            $stmt = $db->prepare("SELECT id FROM business_profiles WHERE UPPER(gst_number) = ?");
            $stmt->execute([$gstNumber]);
            if ($stmt->fetch()) {
                echo json_encode([
                    'exists' => true,
                    'field' => 'gst_number',
                    'message' => 'This GST number is already registered.'
                ]);
                return;
            }
        }

        if (!empty($panNumber)) {
            $stmt = $db->prepare("SELECT id FROM business_profiles WHERE UPPER(pan_number) = ?");
            $stmt->execute([$panNumber]);
            if ($stmt->fetch()) {
                echo json_encode([
                    'exists' => true,
                    'field' => 'pan_number',
                    'message' => 'This PAN number is already registered.'
                ]);
                return;
            }
        }

        if (!empty($instagramLink)) {
            // Clean/normalize instagram link for comparison
            $cleanInsta = rtrim(preg_replace('/^https?:\/\/(www\.)?instagram\.com\//i', '', $instagramLink), '/');
            $cleanInsta = explode('?', $cleanInsta)[0]; // strip query string if any
            
            if (!empty($cleanInsta)) {
                $stmt = $db->prepare("SELECT id, instagram_link FROM business_profiles WHERE instagram_link IS NOT NULL AND instagram_link != ''");
                $stmt->execute();
                $allLinks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($allLinks as $row) {
                    $existingClean = rtrim(preg_replace('/^https?:\/\/(www\.)?instagram\.com\//i', '', $row['instagram_link'] ?? ''), '/');
                    $existingClean = explode('?', $existingClean)[0];
                    if (strcasecmp($cleanInsta, $existingClean) === 0) {
                        echo json_encode([
                            'exists' => true,
                            'field' => 'instagram',
                            'message' => 'This Instagram shop link is already registered.'
                        ]);
                        return;
                    }
                }
            }
        }

        echo json_encode(['exists' => false]);
        return;
    }

    public function showVerifyOtp()
    {
        if (!Session::get('pending_registration')) {
            $this->redirect('/register');
        }

        $lastWrong = Session::get('otp_last_wrong_time') ?? 0;
        $lockoutRemaining = 0;
        if (time() - $lastWrong < 60) {
            $lockoutRemaining = 60 - (time() - $lastWrong);
        }

        $otpGeneratedAt = Session::get('otp_generated_at') ?? 0;
        $otpExpirationRemaining = 120 - (time() - $otpGeneratedAt);
        if ($otpExpirationRemaining < 0) {
            $otpExpirationRemaining = 0;
        }

        $devOtp = (defined('APP_ENV') && APP_ENV === 'development') ? (Session::get('registration_otp') ?? Session::get('reset_otp') ?? '') : '';

        $this->render('storefront/verify_otp', [
            'title' => 'Verify Email',
            'lockoutRemaining' => $lockoutRemaining,
            'otpExpirationRemaining' => $otpExpirationRemaining,
            'formAction' => '/register/verify',
            'resendAction' => '/register/resend-otp',
            'isRegistration' => true,
            'devOtp' => $devOtp
        ]);
    }

    public function resendOtp()
    {
        $regData = Session::get('pending_registration');
        if (!$regData) {
            Session::setFlash('error', 'Session expired. Please register again.');
            $this->redirect('/register');
        }

        // Check 8-hour lockout
        $resendLockoutTime = Session::get('otp_resend_lockout_time') ?? 0;
        if ($resendLockoutTime > 0) {
            if (time() - $resendLockoutTime < (8 * 3600)) {
                $hoursLeft = ceil(((8 * 3600) - (time() - $resendLockoutTime)) / 3600);
                Session::setFlash('error', "Maximum OTP resend attempts reached. Please try again after {$hoursLeft} hours.");
                $this->redirect('/register/verify');
            } else {
                // Lockout expired, reset the count
                Session::remove('otp_resend_lockout_time');
                Session::set('otp_resend_count', 0);
            }
        }

        // Rate limit: Max 5 resends
        $resendCount = Session::get('otp_resend_count') ?? 0;
        if ($resendCount >= 5) {
            Session::set('otp_resend_lockout_time', time());
            Session::setFlash('error', 'Maximum OTP resend attempts reached. Please try again after 8 hours.');
            $this->redirect('/register/verify');
        }

        // Rate limit: 1-minute cooldown between resends
        $lastResend = Session::get('otp_last_resend_time') ?? 0;
        if (time() - $lastResend < 60) {
            $wait = 60 - (time() - $lastResend);
            Session::setFlash('error', "Please wait {$wait} seconds before requesting a new OTP.");
            $this->redirect('/register/verify');
        }

        // Generate a NEW OTP
        $newOtp = sprintf("%06d", mt_rand(1, 999999));
        Session::set('registration_otp', $newOtp);
        Session::set('otp_generated_at', time());

        $name = $regData['name'] ?? 'User';
        $email = $regData['email'] ?? '';

        // Send new OTP via EmailService
        try {
            $emailService = new \App\Services\EmailService();
            $emailService->sendOtpEmail($email, $newOtp, $name);
        } catch (\Throwable $e) {
            error_log("[Resend OTP Email Error] " . $e->getMessage());
        }

        Session::set('otp_resend_count', $resendCount + 1);
        Session::set('otp_last_resend_time', time());
        Session::setFlash('success', "A new OTP has been sent to your email!");
        $this->redirect('/register/verify');
    }

    public function verifyOtp()
    {
        $submittedOtp = $_POST['otp'] ?? '';
        $sessionOtp = Session::get('registration_otp');
        $regData = Session::get('pending_registration');

        if (!$sessionOtp || !$regData) {
            Session::setFlash('error', 'Session expired. Please register again.');
            $this->redirect('/register');
        }

        // Lockout removed based on user request

        // Expiration check (2 minutes)
        $otpGeneratedAt = Session::get('otp_generated_at') ?? 0;
        if (time() - $otpGeneratedAt > 120) {
            Session::setFlash('error', 'This OTP has expired. Please request a new one.');
            $this->redirect('/register/verify');
        }

        if ($submittedOtp !== $sessionOtp) {
            $wrongCount = (Session::get('otp_wrong_count') ?? 0) + 1;
            Session::set('otp_wrong_count', $wrongCount);
            
            if ($wrongCount >= 5) {
                Session::remove('pending_registration');
                Session::remove('registration_otp');
                Session::remove('otp_wrong_count');
                Session::setFlash('error', 'Maximum invalid attempts reached. For your security, please register again.');
                $this->redirect('/register');
            } else {
                Session::setFlash('Invalid OTP', 'Invalid OTP. Please enter the correct OTP.');
                $this->redirect('/register/verify');
            }
        }

        // OTP IS CORRECT! Now do the DB inserts.
        try {
            $db = \Core\Database::getInstance();
            $db->beginTransaction();

            $uniqueBuyerId = '';
            $isUnique = false;
            while (!$isUnique) {
                $uniqueBuyerId = 'BUY-' . mt_rand(100000, 999999);
                $stmt = $db->prepare("SELECT id FROM users WHERE unique_buyer_id = ?");
                $stmt->execute([$uniqueBuyerId]);
                if (!$stmt->fetch()) {
                    $isUnique = true;
                }
            }

            $userModel = new User();
            $userId = $userModel->create([
                'unique_buyer_id' => $uniqueBuyerId,
                'name' => $regData['name'],
                'email' => $regData['email'],
                'phone' => $regData['phone'],
                'password' => $regData['password'],
                'role' => 'buyer',
                'status' => 'pending'
            ]);

            $profileModel = new BusinessProfile();
            $profileModel->create([
                'user_id' => $userId,
                'business_name' => $regData['business_name'],
                'shop_location' => $regData['shop_location'],
                'gst_number' => $regData['has_gst'] ? $regData['gst_number'] : null,
                'gst_document' => $regData['has_gst'] ? ($regData['gst_doc_url'] ?? null) : null,
                'pan_number' => !$regData['has_gst'] ? $regData['pan_number'] : null,
                'pan_document' => !$regData['has_gst'] ? ($regData['pan_doc_url'] ?? null) : null,
                'no_gst_reason' => !$regData['has_gst'] ? $regData['no_gst_reason'] : null,
                'instagram_link' => $regData['instagram_link'] ?? null,
            ]);

            if (!empty($regData['shop_address_line1'])
                && !empty($regData['shop_city'])
                && !empty($regData['shop_state'])
                && !empty($regData['shop_postal_code'])) {
                $db->prepare("
                    INSERT INTO addresses (user_id, label, line1, city, state, postal_code, country, is_default)
                    VALUES (?, 'Shop Address', ?, ?, ?, ?, 'India', 1)
                ")->execute([
                    $userId,
                    $regData['shop_address_line1'],
                    $regData['shop_city'],
                    $regData['shop_state'],
                    $regData['shop_postal_code'],
                ]);
            }

            $requestModel = new RegistrationRequest();
            $requestModel->create([
                'user_id' => $userId,
                'gst_flag' => $regData['has_gst'] ? 1 : 0
            ]);

            // Handle saving files from session
            // Handle saving files from session in a single batch query
            $mediaModel = new Media();
            $batchMedia = [];

            if (!empty($regData['temp_files']) && is_array($regData['temp_files'])) {
                foreach ($regData['temp_files'] as $tempFile) {
                    $batchMedia[] = [
                        'user_id' => $userId,
                        'file_path' => $tempFile['path'],
                        'file_type' => $tempFile['type'],
                        'purpose' => 'shop_verification'
                    ];
                }
            }

            if (!empty($regData['gst_doc_url'])) {
                $batchMedia[] = [
                    'user_id' => $userId,
                    'file_path' => $regData['gst_doc_url'],
                    'file_type' => 'document',
                    'purpose' => 'gst_document'
                ];
            }

            if (!empty($regData['pan_doc_url'])) {
                $batchMedia[] = [
                    'user_id' => $userId,
                    'file_path' => $regData['pan_doc_url'],
                    'file_type' => 'document',
                    'purpose' => 'pan_document'
                ];
            }

            $mediaModel->createBatch($batchMedia);

            $db->commit();

            // Clear session data
            Session::remove('pending_registration');
            Session::remove('registration_otp');

            Session::setFlash('success', 'Email verified and registration submitted successfully! Your account is pending admin approval.');
            $this->redirect('/');

        } catch (\Exception $e) {
            $db->rollBack();
            Session::setFlash('error', 'An error occurred during final registration: ' . $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function logout()
    {
        Session::destroy();
        $this->redirect('/login');
    }
}
