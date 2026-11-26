<?php
namespace App\Services;

class SmsQuickerService
{
    private string $apiUrl;
    private string $apiKey;
    private string $mode;
    private string $deviceId;
    private string $simSlot;
    private string $senderId;
    private string $whatsappAccount;

    public function __construct()
    {
        $this->apiUrl          = defined('SMSQUICKER_API_URL') && !empty(SMSQUICKER_API_URL) 
            ? SMSQUICKER_API_URL 
            : 'https://www.smsquicker.com/api/send/sms';
        $this->apiKey          = defined('SMSQUICKER_API_KEY') ? SMSQUICKER_API_KEY : '';
        $this->mode            = defined('SMSQUICKER_MODE') ? SMSQUICKER_MODE : 'devices';
        $this->deviceId        = defined('SMSQUICKER_DEVICE_ID') ? SMSQUICKER_DEVICE_ID : '';
        $this->simSlot         = defined('SMSQUICKER_SIM_SLOT') && SMSQUICKER_SIM_SLOT !== '' ? (string)SMSQUICKER_SIM_SLOT : '1';
        $this->senderId        = defined('SMSQUICKER_SENDER_ID') ? SMSQUICKER_SENDER_ID : '';
        $this->whatsappAccount = defined('SMSQUICKER_WHATSAPP_ACCOUNT') ? SMSQUICKER_WHATSAPP_ACCOUNT : '';
    }

    /**
     * Send OTP for Registration (WhatsApp & SMS)
     */
    public function sendRegisterOtp(string $phone, string $otp, string $name = ''): bool
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'GRM B2B';
        $message = "Your {$appName} code is {$otp}";
        
        $waSent = false;
        if (!empty($this->whatsappAccount)) {
            $waSent = $this->sendWhatsApp($phone, $message);
        }
        $smsSent = $this->sendSms($phone, $message);
        return $waSent || $smsSent;
    }

    /**
     * Send Resent OTP for Registration / Verification (WhatsApp & SMS)
     */
    public function sendResendOtp(string $phone, string $otp, string $name = ''): bool
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'GRM B2B';
        $message = "Your new {$appName} code is {$otp}";
        
        $waSent = false;
        if (!empty($this->whatsappAccount)) {
            $waSent = $this->sendWhatsApp($phone, $message);
        }
        $smsSent = $this->sendSms($phone, $message);
        return $waSent || $smsSent;
    }

    /**
     * Send OTP for Password Reset (WhatsApp & SMS)
     */
    public function sendResetPasswordOtp(string $phone, string $otp, string $name = ''): bool
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'GRM B2B';
        $message = "Your {$appName} reset code is {$otp}";
        
        $waSent = false;
        if (!empty($this->whatsappAccount)) {
            $waSent = $this->sendWhatsApp($phone, $message);
        }
        $smsSent = $this->sendSms($phone, $message);
        return $waSent || $smsSent;
    }

    /**
     * Send WhatsApp Message via SMSQuicker WhatsApp API
     */
    public function sendWhatsApp(string $phone, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->whatsappAccount)) {
            return false;
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', trim($phone));
        if (empty($cleanPhone)) {
            return false;
        }

        if (preg_match('/^[6-9]\d{9}$/', $cleanPhone)) {
            $formattedPhone = '+91' . $cleanPhone;
        } else {
            $formattedPhone = $cleanPhone;
        }

        $payload = [
            'secret'    => $this->apiKey,
            'account'   => $this->whatsappAccount,
            'recipient' => $formattedPhone,
            'type'      => 'text',
            'message'   => $message,
        ];

        $ch = curl_init('https://www.smsquicker.com/api/send/whatsapp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log("[SmsQuicker WhatsApp cURL Error] To: {$formattedPhone} | Error: {$curlError}");
            return false;
        }

        $decoded = json_decode($response, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300) && (!isset($decoded['status']) || $decoded['status'] == 200 || $decoded['status'] === true);

        if (!$isSuccess) {
            error_log("[SmsQuicker WhatsApp API Warning] HTTP {$httpCode} | To: {$formattedPhone} | Response: {$response}");
        } else {
            error_log("[SmsQuicker WhatsApp Success] To: {$formattedPhone} | HTTP {$httpCode}");
        }

        return $isSuccess;
    }

    /**
     * Core SMS sender method using cURL
     */
    public function sendSms(string $phone, string $message): bool
    {
        // Sanitize phone number (remove spaces, hyphens, brackets)
        $cleanPhone = preg_replace('/[^0-9+]/', '', trim($phone));
        if (empty($cleanPhone)) {
            error_log("[SmsQuicker] Empty phone number provided.");
            return false;
        }

        // If 10-digit Indian number without country code, prefix +91 or 91 if standard
        if (preg_match('/^[6-9]\d{9}$/', $cleanPhone)) {
            $formattedPhone = '+91' . $cleanPhone;
        } else {
            $formattedPhone = $cleanPhone;
        }

        if (empty($this->apiKey)) {
            error_log("[SmsQuicker Warning] API Key is not configured in .env (SMSQUICKER_API_KEY). Message to {$formattedPhone}: {$message}");
            return false;
        }

        // Prepare payload according to SMSQuicker / Zender REST API schema
        $payload = [
            'secret'   => $this->apiKey,
            'mode'     => $this->mode,
            'device'   => $this->deviceId,
            'sim'      => (int)$this->simSlot,
            'priority' => 1, // High Priority / Instant Dispatch for OTP
            'phone'    => $formattedPhone,
            'message'  => $message,
        ];

        if (empty($this->deviceId)) {
            error_log("[SmsQuicker Notice] SMSQUICKER_DEVICE_ID is empty in .env. SMSQuicker requires an Android device linked to your account to dispatch SMS.");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log("[SmsQuicker cURL Error] To: {$formattedPhone} | Error: {$curlError}");
            return false;
        }

        $decoded = json_decode($response, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300) && (!isset($decoded['status']) || $decoded['status'] == 200 || $decoded['status'] === true || $decoded['status'] === 'success');

        if (!$isSuccess) {
            error_log("[SmsQuicker API Warning] HTTP {$httpCode} | To: {$formattedPhone} | Response: {$response}");
        } else {
            error_log("[SmsQuicker Success] To: {$formattedPhone} | HTTP {$httpCode}");
        }

        return $isSuccess;
    }
}
