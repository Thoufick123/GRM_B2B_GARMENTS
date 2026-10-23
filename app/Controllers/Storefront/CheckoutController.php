<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use App\Models\Cart;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function __construct()
    {
        if (!Session::get('user_id') || Session::get('user_status') !== 'active') {
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $userId = Session::get('user_id');
        $cartModel = new Cart();
        $cartItems = $cartModel->getItems($userId);

        if (empty($cartItems)) {
            Session::setFlash('error', 'Your cart is empty.');
            $this->redirect('/cart');
        }

        $subtotal = 0;
        $categoryTotals = [];

        foreach ($cartItems as &$item) {
            $gst = (float)($item['variant_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['product_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['category_sgst'] ?? 0) + (float)($item['category_cgst'] ?? 0);

            if (!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) {
                $rawEffective = (float)$item['variant_discount_price'];
            } else {
                $rawEffective = (!empty($item['variant_price']) && (float)$item['variant_price'] > 0) 
                    ? (float)$item['variant_price'] 
                    : (float)($item['wholesale_price'] ?? 0);
            }

            $unitPrice = $rawEffective;

            $item['unit_price'] = $unitPrice;
            $item['gst_rate'] = $gst;
            $itemTotal = round($unitPrice * $item['quantity'], 2);
            $subtotal += $itemTotal;
            
            $requiredMoq = $item['moq_override'] ?? $item['category_moq'] ?? 1;
            if ($item['quantity'] < $requiredMoq) {
                Session::setFlash('error', 'Please resolve minimum order requirements in your cart before checkout.');
                $this->redirect('/cart');
            }
            if ($item['quantity'] > (int)$item['available_stock']) {
                Session::setFlash('error', 'Some items in your cart are no longer available in the requested quantity. Please review your cart.');
                $this->redirect('/cart');
            }
            
            // Accumulate category totals for Order Rules
            $catId = $item['category_id'];
            
            if (!isset($categoryTotals[$catId])) {
                $categoryTotals[$catId] = ['name' => $item['category_name'], 'total' => 0];
            }
            $categoryTotals[$catId]['total'] += $itemTotal;
        }
        
        // Validate against dynamic Order Rules, Category MOV and Order Cart Value
        $ruleValidation = \App\Models\OrderRule::validateCartRules($categoryTotals, $subtotal);
        if (!empty($ruleValidation['errors'])) {
            Session::setFlash('error', $ruleValidation['errors'][0]);
            $this->redirect('/cart');
        }

        $userModel = new \App\Models\User();
        $user = $userModel->findById($userId);
        
        $profileModel = new \App\Models\BusinessProfile();
        $profile = $profileModel->findByUserId($userId);

        // Load saved addresses (auto-seed from shop_location if none exist)
        $db2 = \Core\Database::getInstance();
        $addrCount = $db2->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
        $addrCount->execute([$userId]);
        if ((int)$addrCount->fetchColumn() === 0 && !empty($profile['shop_location'])) {
            $shopLocation = $profile['shop_location'];
            $line1 = $shopLocation;
            $city = 'Unknown';
            $state = 'Unknown';
            $postal = '000000';
            
            // Try to parse format: Street Address, District, State - Pincode
            if (preg_match('/^(.*?),\s*(.*?),\s*(.*?)\s*-\s*(\d{6})$/', $shopLocation, $matches)) {
                $line1 = trim($matches[1]);
                $city = trim($matches[2]);
                $state = trim($matches[3]);
                $postal = $matches[4];
            }
            
            $db2->prepare("INSERT INTO addresses (user_id, label, line1, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")
                ->execute([$userId, 'Shop Address', $line1, $city, $state, $postal, 'India']);
        }
        $addrStmt = $db2->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $addrStmt->execute([$userId]);
        $addresses = $addrStmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('storefront/checkout', [
            'title'     => 'Checkout',
            'cartItems' => $cartItems,
            'subtotal'  => $subtotal,
            'user'      => $user,
            'profile'   => $profile,
            'addresses' => $addresses
        ]);
    }


    public function placeOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->redirect('/checkout');
            return;
        }

        $db = \Core\Database::getInstance();
        // Virtual Queue / Rate Limiting (Max 30 checkouts per minute globally)
        if (!\Core\RateLimiter::attempt('checkout_global_limit', 30, 60)) {
            Session::setFlash('error', 'The server is currently experiencing extremely high checkout volume. You have been placed in a virtual queue. Please wait a few seconds and try again.');
            $this->redirect('/checkout');
        }

        $userId = Session::get('user_id');
        $address = $_POST['shipping_address'] ?? '';
        $paymentMethodInput = $_POST['payment_method'] ?? 'Online';
        $state = trim($_POST['shipping_state'] ?? '');

        if (strtolower($paymentMethodInput) === 'cod') {
            $paymentMethod = 'COD';
        } else {
            $paymentMethod = 'Online';
        }

        if (empty($address)) {
            Session::setFlash('error', 'Shipping address is required.');
            $this->redirect('/checkout');
        }

        $cartModel = new Cart();
        $cartItems = $cartModel->getItems($userId);

        if (empty($cartItems)) {
            $this->redirect('/catalog');
        }

        $totalAmount = 0;
        foreach ($cartItems as &$item) {
            $gst = (float)($item['variant_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['product_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['category_sgst'] ?? 0) + (float)($item['category_cgst'] ?? 0);

            if (!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) {
                $rawEffective = (float)$item['variant_discount_price'];
            } else {
                $rawEffective = (!empty($item['variant_price']) && (float)$item['variant_price'] > 0) 
                    ? (float)$item['variant_price'] 
                    : (float)($item['wholesale_price'] ?? 0);
            }

            $unitPrice = $rawEffective;

            $item['unit_price'] = $unitPrice;
            $item['gst_rate'] = $gst;
            $itemTotal = round($unitPrice * $item['quantity'], 2);
            $totalAmount += $itemTotal;
            
            $catId = $item['category_id'];
            if (!isset($categoryTotals[$catId])) {
                $categoryTotals[$catId] = ['name' => $item['category_name'], 'total' => 0];
            }
            $categoryTotals[$catId]['total'] += $itemTotal;
        }

        // Validate against dynamic Order Rules, Category MOV and Order Cart Value
        $ruleValidation = \App\Models\OrderRule::validateCartRules($categoryTotals, $totalAmount);
        if (!empty($ruleValidation['errors'])) {
            Session::setFlash('error', $ruleValidation['errors'][0]);
            $this->redirect('/cart');
        }

        // Calculate checkout fees dynamically
        $feeDetails = $this->calculateCheckoutFees($userId, $state, $paymentMethod, $cartItems);

        try {
            $orderModel = new Order();
            $orderNumber = $orderModel->createOrder($userId, $cartItems, $totalAmount, $address, $feeDetails);
            
            // Get user details for Razorpay
            $db = \Core\Database::getInstance();
            $stmt = $db->prepare("SELECT phone, name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch() ?: [];
            
            // Handle saving new address
            if (($_POST['is_new_address'] ?? '0') === '1') {
                $nl1 = trim($_POST['new_addr_line1'] ?? '');
                $nl2 = trim($_POST['new_addr_line2'] ?? '');
                $nct = trim($_POST['new_addr_city'] ?? '');
                $nst = trim($_POST['shipping_state'] ?? '');
                $npn = trim($_POST['new_addr_pincode'] ?? '');
                $nco = trim($_POST['new_addr_country'] ?? 'India');
                
                if (!empty($nl1) && !empty($nct)) {
                    // Check if already exists
                    $chkStmt = $db->prepare("SELECT id FROM addresses WHERE user_id = ? AND line1 = ? AND city = ?");
                    $chkStmt->execute([$userId, $nl1, $nct]);
                    if (!$chkStmt->fetch()) {
                        // Mark others as not default
                        $db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
                        
                        $insStmt = $db->prepare("INSERT INTO addresses (user_id, label, line1, line2, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
                        $insStmt->execute([$userId, 'Saved Address', $nl1, $nl2, $nct, $nst, $npn, $nco]);
                    }
                }
            }
            $rawPhone = preg_replace('/[^0-9]/', '', $user['phone'] ?? '');
            if (strlen($rawPhone) > 10) {
                $rawPhone = substr($rawPhone, -10);
            }
            $phone = (strlen($rawPhone) === 10) ? $rawPhone : '9999999999';
            $name = !empty(trim($user['name'] ?? '')) ? trim($user['name']) : 'Customer';
            $email = filter_var($user['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $user['email'] : 'customer@example.com';
            
            // =========================================================================
            // [ARCHIVED] Cashfree Integration — Commented out for future reference
            // =========================================================================
            /*
            $appId = CASHFREE_APP_ID;
            $secret = CASHFREE_SECRET_KEY;
            $env = CASHFREE_ENV;
            $baseUrl = $env === 'sandbox' ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg';
            
            $cfOrderId = $orderNumber . '_' . uniqid();

            $orderPayload = [
                'order_amount' => round($feeDetails['paid_amount'], 2),
                'order_currency' => 'INR',
                'order_id' => $cfOrderId,
                'customer_details' => [
                    'customer_id' => (string)$userId,
                    'customer_phone' => $phone,
                    'customer_name' => $name,
                    'customer_email' => $email
                ],
                'order_meta' => [
                    'return_url' => BASE_URL . '/checkout/verify?order_id=' . $orderNumber
                ]
            ];

            $ch = curl_init($baseUrl . '/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-client-id: ' . $appId,
                'x-client-secret: ' . $secret,
                'x-api-version: 2023-08-01'
            ]);

            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
            
            $resData = json_decode($response, true);
            
            if (isset($resData['payment_session_id'])) {
                $paymentSessionId = $resData['payment_session_id'];
                
                // Update cashfree order ID in DB
                $stmt = $db->prepare("UPDATE orders SET cashfree_order_id = ? WHERE order_number = ?");
                $stmt->execute([$cfOrderId, $orderNumber]);

                // Render payment page
                $this->render('storefront/payment', [
                    'title' => 'Processing Payment',
                    'paymentSessionId' => $paymentSessionId,
                    'env' => $env
                ]);
                return;
            } else {
                $errorMsg = $resData['message'] ?? 'Payment initiation failed.';
                Session::setFlash('error', 'Payment Error: ' . $errorMsg);
                $this->redirect('/cart');
            }
            */

            // =========================================================================
            // [ACTIVE] Razorpay Integration
            // =========================================================================
            $payableAmount = round($feeDetails['paid_amount'], 2);
            $razorpayService = new \App\Services\RazorpayService();
            $rzpRes = $razorpayService->createOrder($payableAmount, $orderNumber, [
                'order_number' => $orderNumber,
                'user_id' => (string)$userId,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone
            ]);

            if (!$rzpRes['success']) {
                $errorMsg = $rzpRes['error'] ?? 'Payment gateway initialization failed.';
                Session::setFlash('error', 'Razorpay Error: ' . $errorMsg);
                $this->redirect('/cart');
                return;
            }

            $razorpayOrderId = $rzpRes['order']['id'];

            // Store Razorpay Order ID in DB (also populate cashfree_order_id for backward compatibility)
            try {
                $stmt = $db->prepare("UPDATE orders SET razorpay_order_id = ?, cashfree_order_id = ? WHERE order_number = ?");
                $stmt->execute([$razorpayOrderId, $razorpayOrderId, $orderNumber]);
            } catch (\PDOException $e) {
                $stmt = $db->prepare("UPDATE orders SET cashfree_order_id = ? WHERE order_number = ?");
                $stmt->execute([$razorpayOrderId, $orderNumber]);
            }

            // Render Razorpay payment page
            $this->render('storefront/payment', [
                'title'           => 'Processing Payment',
                'orderNumber'     => $orderNumber,
                'razorpayOrderId' => $razorpayOrderId,
                'razorpayKeyId'   => RAZORPAY_KEY_ID,
                'amountInPaise'   => (int)round($payableAmount * 100),
                'amount'          => $payableAmount,
                'customerName'    => $name,
                'customerEmail'   => $email,
                'customerPhone'   => $phone,
                'currency'        => RAZORPAY_CURRENCY
            ]);
            return;
            
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to place order. ' . $e->getMessage());
            $this->redirect('/cart');
        }
    }

    public function retryPayment()
    {
        $orderNumber = $_GET['order_id'] ?? null;
        if (!$orderNumber) {
            $this->redirect('/dashboard/orders');
        }

        $userId = Session::get('user_id');
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
        $stmt->execute([$orderNumber, $userId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'pending') {
            Session::setFlash('error', 'Invalid order or already paid.');
            $this->redirect('/dashboard/orders');
        }

        // =========================================================================
        // [ARCHIVED] Cashfree Retry Logic — Commented out for future reference
        // =========================================================================
        /*
        $appId = CASHFREE_APP_ID;
        $secret = CASHFREE_SECRET_KEY;
        $env = CASHFREE_ENV;
        $baseUrl = $env === 'sandbox' ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg';

        $cfOrderId = $order['cashfree_order_id'] ?? $orderNumber;

        $ch = curl_init($baseUrl . '/orders/' . $cfOrderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-client-id: ' . $appId,
            'x-client-secret: ' . $secret,
            'x-api-version: 2023-08-01'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $resData = json_decode($response, true);

        if (isset($resData['payment_session_id'])) {
            $this->render('storefront/payment', [
                'title' => 'Processing Payment',
                'paymentSessionId' => $resData['payment_session_id'],
                'env' => $env
            ]);
        } else {
            Session::setFlash('error', 'Could not fetch payment session for this order.');
            $this->redirect('/dashboard/orders');
        }
        */

        // =========================================================================
        // [ACTIVE] Razorpay Retry Logic
        // =========================================================================
        $stmtUser = $db->prepare("SELECT phone, name, email FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch();

        $rawPhone = preg_replace('/[^0-9]/', '', $user['phone'] ?? '');
        if (strlen($rawPhone) > 10) {
            $rawPhone = substr($rawPhone, -10);
        }
        $phone = (strlen($rawPhone) === 10) ? $rawPhone : '9999999999';
        $name = !empty(trim($user['name'] ?? '')) ? trim($user['name']) : 'Customer';
        $email = filter_var($user['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $user['email'] : 'customer@example.com';

        $payableAmount = ($order['balance_amount'] > 0 || $order['cod_advance_paid'] > 0) ? (float)$order['cod_advance_paid'] : (float)$order['grand_total'];

        $razorpayService = new \App\Services\RazorpayService();
        $razorpayOrderId = $order['razorpay_order_id'] ?? null;

        if (empty($razorpayOrderId)) {
            $rzpRes = $razorpayService->createOrder($payableAmount, $orderNumber, [
                'order_number' => $orderNumber,
                'user_id' => (string)$userId,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone
            ]);

            if ($rzpRes['success']) {
                try {
                    $db->prepare("UPDATE orders SET razorpay_order_id = ? WHERE order_number = ?")->execute([$razorpayOrderId, $orderNumber]);
                } catch (\PDOException $e) {
                    $db->prepare("UPDATE orders SET cashfree_order_id = ? WHERE order_number = ?")->execute([$razorpayOrderId, $orderNumber]);
                }
            } else {
                Session::setFlash('error', 'Could not initialize payment for this order: ' . ($rzpRes['error'] ?? ''));
                $this->redirect('/dashboard/orders');
                return;
            }
        }

        $this->render('storefront/payment', [
            'title'           => 'Processing Payment',
            'orderNumber'     => $orderNumber,
            'razorpayOrderId' => $razorpayOrderId,
            'razorpayKeyId'   => RAZORPAY_KEY_ID,
            'amountInPaise'   => (int)round($payableAmount * 100),
            'amount'          => $payableAmount,
            'customerName'    => $name,
            'customerEmail'   => $email,
            'customerPhone'   => $phone,
            'currency'        => RAZORPAY_CURRENCY
        ]);
    }

    public function verifyPayment()
    {
        $orderNumber = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
        if (!$orderNumber) {
            $this->redirect('/dashboard/orders');
        }
        
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            $this->redirect('/cart');
            return;
        }

        // If order was already paid and assigned a GB number, redirect directly to success
        if ($order['payment_status'] === 'paid' && strpos($order['order_number'], 'PENDING-') !== 0) {
            $this->redirect('/order-success?order_id=' . $order['order_number']);
            return;
        }

        // =========================================================================
        // [ARCHIVED] Cashfree Payment Verification — Commented out for future reference
        // =========================================================================
        /*
        $appId = CASHFREE_APP_ID;
        $secret = CASHFREE_SECRET_KEY;
        $env = CASHFREE_ENV;
        $baseUrl = $env === 'sandbox' ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg';
        $cfOrderId = $order['cashfree_order_id'] ?? $orderNumber;

        $ch = curl_init($baseUrl . '/orders/' . $cfOrderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-client-id: ' . $appId,
            'x-client-secret: ' . $secret,
            'x-api-version: 2023-08-01'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $resData = json_decode($response, true);
        
        if (isset($resData['order_status']) && $resData['order_status'] === 'PAID') { ... }
        */

        // =========================================================================
        // [ACTIVE] Razorpay Payment Verification
        // =========================================================================
        $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? $_GET['razorpay_payment_id'] ?? null;
        $razorpayOrderId   = $_POST['razorpay_order_id'] ?? $_GET['razorpay_order_id'] ?? ($order['razorpay_order_id'] ?? null);
        $razorpaySignature = $_POST['razorpay_signature'] ?? $_GET['razorpay_signature'] ?? null;

        $razorpayService = new \App\Services\RazorpayService();
        $isVerified = false;

        if (!empty($razorpayOrderId) && !empty($razorpayPaymentId) && !empty($razorpaySignature)) {
            $isVerified = $razorpayService->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);
        } elseif (!empty($razorpayPaymentId)) {
            // Fallback: Verify directly from Razorpay API
            $paymentDetails = $razorpayService->fetchPayment($razorpayPaymentId);
            if (!empty($paymentDetails['status']) && in_array($paymentDetails['status'], ['captured', 'authorized'])) {
                $isVerified = true;
            }
        } elseif (!empty($order['razorpay_order_id'])) {
            // Direct Gateway Verification by Razorpay Order ID (e.g. if redirect didn't pass POST parameters)
            $rzpOrder = $razorpayService->fetchOrder($order['razorpay_order_id']);
            if (!empty($rzpOrder['status']) && $rzpOrder['status'] === 'paid') {
                $rzpPayments = $razorpayService->fetchOrderPayments($order['razorpay_order_id']);
                if (!empty($rzpPayments['items'])) {
                    foreach ($rzpPayments['items'] as $payItem) {
                        if (in_array($payItem['status'] ?? '', ['captured', 'authorized'])) {
                            $isVerified = true;
                            $razorpayPaymentId = $payItem['id'];
                            $paymentDetails = $payItem;
                            break;
                        }
                    }
                }
            }
        }

        if ($isVerified) {
            $expectedAmount = ($order['balance_amount'] > 0 || $order['cod_advance_paid'] > 0) ? (float)$order['cod_advance_paid'] : (float)$order['grand_total'];
            
            // Optional: If paymentDetails fetched, verify amount in paise
            if (!empty($paymentDetails['amount'])) {
                $paidRupees = (float)$paymentDetails['amount'] / 100;
                if (abs($paidRupees - $expectedAmount) > 0.01) {
                    Session::setFlash('error', 'Payment amount mismatch. Please contact support.');
                    $this->redirect('/dashboard/orders');
                    return;
                }
            }

            // Generate successful unique GB order number if not already assigned
            $finalOrderNumber = $orderNumber;
            if (strpos($orderNumber, 'PENDING-') === 0) {
                // Query strictly standard B2B orders matching 'GB' followed only by digits
                // Exclude 'GBC%' which are custom fabric orders
                $stmt = $db->query("
                    SELECT order_number 
                    FROM orders 
                    WHERE order_number REGEXP '^GB[0-9]+$' 
                    ORDER BY id DESC
                ");
                $gbOrders = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                $maxNum = 0;
                foreach ($gbOrders as $ord) {
                    if (preg_match('/^GB(\d+)$/', $ord, $matches)) {
                        $numVal = (int)$matches[1];
                        if ($numVal > $maxNum) {
                            $maxNum = $numVal;
                        }
                    }
                }

                // Increment and guarantee uniqueness
                do {
                    $maxNum++;
                    $candidateOrderNumber = 'GB' . str_pad($maxNum, 5, '0', STR_PAD_LEFT);
                    $chk = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
                    $chk->execute([$candidateOrderNumber]);
                    $exists = ((int)$chk->fetchColumn()) > 0;
                } while ($exists);

                $finalOrderNumber = $candidateOrderNumber;
            }

            try {
                $stmt = $db->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', 
                        status = 'placed', 
                        razorpay_payment_id = ?, 
                        razorpay_order_id = ?, 
                        order_number = ? 
                    WHERE order_number = ?
                ");
                $stmt->execute([$razorpayPaymentId, $razorpayOrderId, $finalOrderNumber, $orderNumber]);
            } catch (\PDOException $e) {
                $stmt = $db->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', 
                        status = 'placed', 
                        cashfree_order_id = ?, 
                        order_number = ? 
                    WHERE order_number = ?
                ");
                $stmt->execute([$razorpayOrderId, $finalOrderNumber, $orderNumber]);
            }
            
            // Reduce stock
            $orderModel = new Order();
            $orderModel->reduceStockForOrder($finalOrderNumber);
            
            // Clear the cart here upon successful payment
            $userId = Session::get('user_id');
            if ($userId) {
                $stmtClear = $db->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $stmtClear->execute([$userId]);
            }

            // Invalidate admin dashboard cached stats
            \Core\Cache::delete('admin_dashboard_stats_30');
            \Core\Cache::delete('admin_dashboard_stats_7');
            \Core\Cache::delete('admin_dashboard_stats_all');

            Session::setFlash('success', 'Payment successful!');
            $this->redirect('/order-success?order_id=' . $finalOrderNumber);
        } else {
            // Not paid or failed
            Session::setFlash('error', 'Payment verification failed or cancelled.');
            $this->redirect('/cart');
        }
    }

    public function success()
    {
        $orderNumber = $_GET['order_id'] ?? null;
        if (!$orderNumber) {
            $this->redirect('/dashboard/orders');
        }
        
        $userId = Session::get('user_id');
        
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
        $stmt->execute([$orderNumber, $userId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            $this->redirect('/dashboard/orders');
        }
        
        $stmtItems = $db->prepare("
            SELECT oi.*, p.name, pv.name as variant_name, c.hsn_code as category_hsn_code,
                   COALESCE(
                       (SELECT image_path FROM product_images WHERE variant_id = oi.variant_id ORDER BY is_primary DESC, id ASC LIMIT 1),
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1)
                   ) as primary_image
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$order['id']]);
        $items = $stmtItems->fetchAll();

        $this->render('storefront/order_success', [
            'title' => 'Order Success',
            'order' => $order,
            'items' => $items
        ]);
    }
    private function calculateCheckoutFees($userId, $state, $paymentMethod, $cartItems)
    {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $gst = (float)($item['variant_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['product_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($item['category_sgst'] ?? 0) + (float)($item['category_cgst'] ?? 0);

            if (!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) {
                $rawEffective = (float)$item['variant_discount_price'];
            } else {
                $rawEffective = (!empty($item['variant_price']) && (float)$item['variant_price'] > 0) 
                    ? (float)$item['variant_price'] 
                    : (float)($item['wholesale_price'] ?? 0);
            }

            $unitPrice = $rawEffective;

            $subtotal += $unitPrice * $item['quantity'];
        }

        $totalWeightKg = 0.0;
        foreach ($cartItems as $item) {
            $weight = (float)($item['weight'] ?? 0);
            $unit   = strtolower(trim($item['weight_unit'] ?? 'kg'));
            if (strpos($unit, 'g') !== false && strpos($unit, 'kg') === false) {
                $weight = $weight / 1000.0;
            }
            $totalWeightKg += $weight * $item['quantity'];
        }

        $totalAmount = $subtotal;

        $feeRuleModel = new \App\Models\FeeRule();
        $matchedRules = $feeRuleModel->findMatchingRules($state, $paymentMethod);

        $fees = [
            'shipping_fee'  => 0.0,
            'weight_fee'    => 0.0,
            'platform_fee'  => 0.0,
            'packaging_fee' => 0.0,
            'cod_advance'   => 0.0,
        ];

        $feeBreakdown = [];

        foreach ($matchedRules as $rule) {
            $maxOrderAmt = !empty($rule['maximum_order_amount']) ? (float)$rule['maximum_order_amount'] : null;
            $minOrderAmt = !empty($rule['minimum_order_amount']) ? (float)$rule['minimum_order_amount'] : null;
            $scalingMultiplier = 1;

            if ($minOrderAmt !== null && $subtotal < $minOrderAmt) {
                continue;
            }

            if ($maxOrderAmt !== null && $subtotal > $maxOrderAmt && $maxOrderAmt > 0) {
                $scalingMultiplier = (int)ceil($subtotal / $maxOrderAmt);
            }

            $categoryName = strtolower($rule['category_name'] ?? '');
            $slabs = json_decode($rule['weight_slabs'] ?? '[]', true) ?: [];

            if (!empty($slabs)) {
                $charge = $this->resolveWeightSlabCharge($slabs, $totalWeightKg);
                $key = $this->resolveFeeKey($categoryName);
                $fees[$key] += $charge;
                $feeBreakdown[] = [
                    'name'   => $rule['fee_name'],
                    'amount' => $charge,
                    'note'   => "Weight: " . round($totalWeightKg, 3) . " kg"
                ];
            } else {
                $baseCharge = $this->resolveFlatFee($rule, $subtotal);
                $charge = $baseCharge * $scalingMultiplier;
                $key = $this->resolveFeeKey($categoryName);
                $fees[$key] += $charge;
                if ($charge > 0) {
                    $note = $scalingMultiplier > 1
                        ? "Scaled ×{$scalingMultiplier} (order ₹" . number_format($subtotal, 0) . " ÷ ₹" . number_format($maxOrderAmt, 0) . " cap)"
                        : null;
                    $breakdown = ['name' => $rule['fee_name'], 'amount' => $charge];
                    if ($note) $breakdown['note'] = $note;
                    $feeBreakdown[] = $breakdown;
                }
            }
        }

        $billableFees = (float)$fees['shipping_fee'] + (float)$fees['weight_fee'] + (float)$fees['platform_fee'] + (float)$fees['packaging_fee'];
        $unroundedGrandTotal = (float)$subtotal + $billableFees;
        $grandTotal   = (float)round($unroundedGrandTotal);
        $roundOff     = round($grandTotal - $unroundedGrandTotal, 2);
        if (abs($roundOff) < 0.001) {
            $roundOff = 0.0;
        }

        $codAdvance   = (float)$fees['cod_advance'];
        if ($paymentMethod === 'COD') {
            $paidAmount    = $codAdvance;
            $balanceAmount = $grandTotal - $paidAmount;
        } else {
            $paidAmount    = $grandTotal;
            $balanceAmount = 0.0;
        }

        return [
            'subtotal'         => $subtotal,
            'shipping_fee'     => $fees['shipping_fee'],
            'weight_fee'       => $fees['weight_fee'],
            'platform_fee'     => $fees['platform_fee'],
            'packaging_fee'    => $fees['packaging_fee'],
            'unrounded_total'  => $unroundedGrandTotal,
            'round_off'        => $roundOff,
            'grand_total'      => $grandTotal,
            'cod_advance_paid' => $codAdvance,
            'balance_amount'   => $balanceAmount,
            'paid_amount'      => $paidAmount,
            'fee_breakdown'    => $feeBreakdown
        ];
    }

    private function resolveWeightSlabCharge(array $slabs, float $totalWeight): float
    {
        $activeSlabs = array_filter($slabs, fn($s) => !empty($s['status']));
        usort($activeSlabs, fn($a, $b) => $a['minWeight'] <=> $b['minWeight']);
        $activeSlabs = array_values($activeSlabs);

        if (empty($activeSlabs)) return 0.0;

        $lowestSlab  = $activeSlabs[0];
        $highestSlab = end($activeSlabs);

        if ($totalWeight < $lowestSlab['minWeight']) {
            return (float)$lowestSlab['charge'];
        }

        foreach ($activeSlabs as $slab) {
            if ($totalWeight >= $slab['minWeight'] && $totalWeight <= $slab['maxWeight']) {
                return (float)$slab['charge'];
            }
        }

        foreach ($activeSlabs as $slab) {
            if ($totalWeight < $slab['minWeight']) {
                return (float)$slab['charge'];
            }
        }

        $maxLimit  = (float)$highestSlab['maxWeight'];
        $maxCharge = (float)$highestSlab['charge'];
        if ($maxLimit <= 0) return $maxCharge;

        return ceil($totalWeight / $maxLimit) * $maxCharge;
    }

    private function resolveFlatFee(array $rule, float $subtotal): float
    {
        if ($rule['fee_type'] === 'Percentage') {
            return ($subtotal * (float)$rule['flat_fee_value']) / 100.0;
        }
        return (float)$rule['flat_fee_value'];
    }

    private function resolveFeeKey(string $categoryName): string
    {
        if (strpos($categoryName, 'weight') !== false) return 'weight_fee';
        if (strpos($categoryName, 'platform') !== false) return 'platform_fee';
        if (strpos($categoryName, 'packing') !== false) return 'packaging_fee';
        if (strpos($categoryName, 'cod') !== false) return 'cod_advance';
        return 'shipping_fee';
    }

    public function addAddress()
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $label = trim($_POST['label'] ?? 'Saved Address');
        if (empty($label)) $label = 'Saved Address';
        $line1 = trim($_POST['line1'] ?? '');
        $line2 = trim($_POST['line2'] ?? '');
        $city  = trim($_POST['city'] ?? $_POST['city_text'] ?? $_POST['district'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $otherState = trim($_POST['other_state_name'] ?? '');
        if ($state === 'Others' && !empty($otherState)) {
            $state = $otherState;
        }
        $postal= trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'India') ?: 'India';
        $isDefault = !empty($_POST['is_default']) ? 1 : 0;

        if (empty($line1) || empty($city) || empty($state) || empty($postal)) {
            echo json_encode(['error' => 'Please fill in all required fields (Address Line 1, State, District/City, Pincode).']);
            exit;
        }

        $db = \Core\Database::getInstance();
        if ($isDefault) {
            $db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }
        $stmt = $db->prepare("INSERT INTO addresses (user_id, label, line1, line2, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $label, $line1, $line2, $city, $state, $postal, $country, $isDefault]);
        $newId = $db->lastInsertId();

        echo json_encode(['success' => true, 'address_id' => $newId]);
        exit;
    }

    public function editAddress()
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $addressId = (int)($_POST['address_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $line1 = trim($_POST['line1'] ?? '');
        $line2 = trim($_POST['line2'] ?? '');
        $city  = trim($_POST['city'] ?? $_POST['city_text'] ?? $_POST['district'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $otherState = trim($_POST['other_state_name'] ?? '');
        if ($state === 'Others' && !empty($otherState)) {
            $state = $otherState;
        }
        $postal= trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'India') ?: 'India';
        $isDefault = !empty($_POST['is_default']) ? 1 : 0;

        if (!$addressId || empty($line1) || empty($city) || empty($state) || empty($postal)) {
            echo json_encode(['error' => 'Please fill in all required fields (Address Line 1, State, District/City, Pincode).']);
            exit;
        }

        $db = \Core\Database::getInstance();
        
        $check = $db->prepare("SELECT id, label FROM addresses WHERE id = ? AND user_id = ?");
        $check->execute([$addressId, $userId]);
        $existing = $check->fetch();
        if (!$existing) {
            echo json_encode(['error' => 'Address not found or unauthorized.']);
            exit;
        }

        if (empty($label)) {
            $label = $existing['label'] ?? 'Saved Address';
        }

        if ($isDefault) {
            $db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $stmt = $db->prepare("UPDATE addresses SET label = ?, line1 = ?, line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$label, $line1, $line2, $city, $state, $postal, $country, $isDefault, $addressId, $userId]);

        echo json_encode(['success' => true]);
        exit;
    }
}
