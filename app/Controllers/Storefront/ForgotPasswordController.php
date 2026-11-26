<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        if (Session::get('user_id')) {
            $this->redirect('/');
        }
        $this->render('storefront/forgot_password', ['title' => 'Forgot Password']);
    }

    public function checkCustomer()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $channel = trim($input['channel'] ?? 'whatsapp');
        $identifier = trim($input['identifier'] ?? $input['phone'] ?? $input['email'] ?? '');

        if (empty($identifier)) {
            echo json_encode(['exists' => false, 'message' => 'Please enter a value to check.']);
            exit;
        }

        $userModel = new User();
        $user = null;

        if ($channel === 'whatsapp') {
            $clean = preg_replace('/[^0-9]/', '', $identifier);
            $last10 = substr($clean, -10);

            if (strlen($last10) !== 10) {
                echo json_encode(['exists' => false, 'message' => 'Please enter a valid 10-digit mobile number.']);
                exit;
            }

            $user = $userModel->findCustomerByPhone($last10);
            if (!$user) {
                $user = $userModel->findByPhone($last10) ?: $userModel->findByPhone('91' . $last10);
            }

            if ($user) {
                $name = $user['name'] ?? 'Customer';
                echo json_encode([
                    'exists' => true,
                    'name' => htmlspecialchars($name),
                    'message' => "Registered customer account found ({$name})."
                ]);
                exit;
            } else {
                echo json_encode([
                    'exists' => false,
                    'message' => "Mobile number (+91 {$last10}) is not registered with any customer account."
                ]);
                exit;
            }
        } else {
            // Email check
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['exists' => false, 'message' => 'Please enter a valid email address.']);
                exit;
            }

            $user = $userModel->findCustomerByEmail($identifier) ?: $userModel->findByEmail($identifier);
            if ($user) {
                $name = $user['name'] ?? 'Customer';
                echo json_encode([
                    'exists' => true,
                    'name' => htmlspecialchars($name),
                    'message' => "Registered customer account found ({$name})."
                ]);
                exit;
            } else {
                echo json_encode([
                    'exists' => false,
                    'message' => "Email address ({$identifier}) is not registered with any customer account."
                ]);
                exit;
            }
        }
    }

    public function sendOtp()
    {
        $isJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
        }

        if (!\Core\RateLimiter::attempt('forgot_password_otp', 5, 60)) {
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait a minute and try again.']);
                exit;
            }
            Session::setFlash('error', 'Too many requests. Please wait a minute and try again.');
            $this->redirect('/forgot-password');
            return;
        }

        $input = [];
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $channel   = trim($_POST['channel'] ?? $input['channel'] ?? 'whatsapp');
        $userModel = new User();
        $user      = null;
        $email     = '';

        if ($channel === 'whatsapp') {
            $phoneInput = trim($_POST['phone'] ?? $input['phone'] ?? $_POST['email'] ?? '');
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);
            
            // Normalize Indian 10-digit number
            if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
                $lookupPhone = substr($cleanPhone, 2);
            } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
                $lookupPhone = substr($cleanPhone, 1);
            } else {
                $lookupPhone = substr($cleanPhone, -10);
            }

            if (empty($lookupPhone) || strlen($lookupPhone) !== 10) {
                $err = 'Please enter a valid 10-digit mobile number.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }

            // Strictly find registered customer
            $user = $userModel->findCustomerByPhone($lookupPhone);
            if (!$user) {
                $user = $userModel->findByPhone($lookupPhone) ?: $userModel->findByPhone('91' . $lookupPhone);
            }

            // STRICT VALIDATION: If not registered customer, DO NOT SEND OTP!
            if (!$user) {
                $err = 'No registered customer account found with mobile number +91 ' . htmlspecialchars($lookupPhone) . '. Only registered customer numbers can receive OTP.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }

            if (($user['status'] ?? '') === 'blocked') {
                $err = 'Your customer account is currently suspended. Please contact support.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }

            $email = $user['email'];
        } else {
            // Email Channel
            $email = trim($_POST['email'] ?? $input['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $err = 'Please enter a valid registered email address.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }

            // Strictly find registered customer
            $user = $userModel->findCustomerByEmail($email) ?: $userModel->findByEmail($email);

            // STRICT VALIDATION: If not registered customer, DO NOT SEND OTP!
            if (!$user) {
                $err = 'No registered customer account found with email ' . htmlspecialchars($email) . '. Only registered customer emails can receive OTP.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }

            if (($user['status'] ?? '') === 'blocked') {
                $err = 'Your customer account is currently suspended. Please contact support.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Session::setFlash('error', $err);
                $this->redirect('/forgot-password');
                return;
            }
        }

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));

        $db = Database::getInstance();
        // Delete any existing OTPs for this email to prevent spam
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        // Insert new OTP
        $db->prepare("INSERT INTO password_resets (email, otp, created_at) VALUES (?, ?, NOW())")->execute([$email, $otp]);

        $appName = defined('APP_NAME') ? APP_NAME : 'GRM B2B';

        if ($channel === 'whatsapp') {
            $userPhone = $user['phone'] ?? $lookupPhone;
            try {
                $smsService = new \App\Services\SmsQuickerService();
                $smsService->sendWhatsApp($userPhone, "Your {$appName} password reset code is {$otp}. Valid for 60 seconds.");
            } catch (\Throwable $e) {
                error_log("[Reset OTP WhatsApp Error] " . $e->getMessage());
            }

            Session::set('reset_email', $email);
            Session::set('reset_channel', 'whatsapp');
            Session::set('reset_phone', $userPhone);
            Session::setFlash('success', "OTP has been sent to your WhatsApp number (+91 {$userPhone})!");
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => BASE_URL . '/verify-otp']);
                exit;
            }
            $this->redirect('/verify-otp');
            return;
        }

        // Send Email via PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM, $appName);
            $mail->addAddress($email, $user['name'] ?? 'User');

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - ' . $appName;
            $mail->Body    = "
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 540px; margin: 0 auto; padding: 28px; background: #ffffff; border: 1px solid #FDBFDD; border-radius: 16px; box-shadow: 0 4px 12px rgba(242, 89, 150, 0.08);'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <span style='display: inline-block; background: #fdf2f7; color: #F25996; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 9999px; border: 1px solid #FDBFDD;'>Security Verification</span>
                        <h2 style='color: #111827; font-size: 22px; font-weight: 800; margin: 12px 0 6px;'>Password Reset Request</h2>
                    </div>
                    <p style='color: #374151; font-size: 14px; line-height: 1.6; margin: 0 0 12px;'>Hello <strong>" . htmlspecialchars($user['name'] ?? 'User') . "</strong>,</p>
                    <p style='color: #4b5563; font-size: 14px; line-height: 1.6; margin: 0 0 20px;'>You have requested to reset your password. Use the following 6-digit OTP to proceed:</p>
                    <div style='background: linear-gradient(135deg, #fdf2f7 0%, #fff 100%); border: 2px dashed #FDBFDD; padding: 20px; text-align: center; border-radius: 12px; margin: 20px 0;'>
                        <span style='font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #F25996; font-family: monospace;'>$otp</span>
                    </div>
                    <p style='color: #F25996; font-size: 13px; font-weight: bold; text-align: center; margin: 12px 0 0;'>This OTP will expire in 60 seconds.</p>
                    <p style='color: #6b7280; font-size: 12px; text-align: center; margin: 6px 0 0;'>If you did not request this, please ignore this email.</p>
                    <hr style='border: none; border-top: 1px solid #f3f4f6; margin: 24px 0;' />
                    <p style='color: #9ca3af; font-size: 11px; text-align: center; margin: 0;'>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
                </div>
            ";

            $mail->send();
        } catch (\Throwable $e) {
            error_log("[Reset OTP Email Error] " . $e->getMessage());
        }

        Session::set('reset_email', $email);
        Session::set('reset_channel', 'email');
        Session::setFlash('success', "OTP has been sent to your email address ({$email})!");
        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => BASE_URL . '/verify-otp']);
            exit;
        }
        $this->redirect('/verify-otp');
    }

    public function showOtpForm()
    {
        $email = Session::get('reset_email');
        if (!$email) {
            $this->redirect('/forgot-password');
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_passed FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        $resetRecord = $stmt->fetch();
        
        $otpExpirationRemaining = 0;
        if ($resetRecord && $resetRecord['seconds_passed'] < 60) {
            $otpExpirationRemaining = 60 - $resetRecord['seconds_passed'];
        }

        $devOtp = '';
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $stmtOtp = $db->prepare("SELECT otp FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmtOtp->execute([$email]);
            $devOtp = $stmtOtp->fetchColumn() ?: '';
        }

        $this->render('storefront/verify_otp', [
            'title' => 'Verify OTP',
            'formAction' => '/verify-otp',
            'resendAction' => '/forgot-password',
            'isRegistration' => false,
            'otpExpirationRemaining' => $otpExpirationRemaining,
            'devOtp' => $devOtp
        ]);
    }

    public function verifyOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/verify-otp');
        }

        $email = Session::get('reset_email');
        $otp = trim($_POST['otp'] ?? '');

        if (!$email || empty($otp)) {
            Session::setFlash('error', 'Invalid request.');
            $this->redirect('/forgot-password');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT *, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_passed FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            Session::setFlash('error', 'Invalid OTP or session expired.');
            $this->redirect('/verify-otp');
        }

        // Check if 60 seconds have passed using MySQL's internal clock to prevent timezone bugs
        if ($resetRecord['seconds_passed'] > 60) {
            Session::setFlash('error', 'This OTP has expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        if ($resetRecord['otp'] !== $otp) {
            Session::setFlash('error', 'Invalid OTP. Please try again.');
            $this->redirect('/verify-otp');
        }

        // Valid OTP
        Session::set('reset_verified', true);
        $this->redirect('/reset-password');
    }

    public function showResetForm()
    {
        if (!Session::get('reset_verified') || !Session::get('reset_email')) {
            $this->redirect('/forgot-password');
        }
        $this->render('storefront/reset_password', ['title' => 'Reset Password']);
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/reset-password');
        }

        if (!Session::get('reset_verified') || !Session::get('reset_email')) {
            $this->redirect('/forgot-password');
        }

        $email = Session::get('reset_email');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || empty($confirmPassword)) {
            Session::setFlash('error', 'All fields are required.');
            $this->redirect('/reset-password');
        }

        if ($newPassword !== $confirmPassword) {
            Session::setFlash('error', 'Passwords do not match.');
            $this->redirect('/reset-password');
        }

        if (strlen($newPassword) < 8) {
            Session::setFlash('error', 'Password must be at least 8 characters long.');
            $this->redirect('/reset-password');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Session::setFlash('error', 'User not found.');
            $this->redirect('/forgot-password');
        }

        // Hash and update
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $db = Database::getInstance();
        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $user['id']]);
        
        // Clean up reset tokens
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Clear session variables
        Session::remove('reset_email');
        Session::remove('reset_verified');

        Session::setFlash('success', "Your password for account ({$email}) has been successfully reset! You can now login with this email or mobile number.");
        $this->redirect('/login');
    }
}
