<?php
// Define basic constants for paths and application config

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('PUBLIC_PATH', BASE_PATH);
define('CONFIG_PATH', BASE_PATH . '/config');

// Load Environment Variables
$envPath = BASE_PATH . '/.env';
if (!file_exists($envPath) && file_exists(BASE_PATH . '/env')) {
    $envPath = BASE_PATH . '/env';
}
$envVars = [];
if (file_exists($envPath)) {
    $parsed = @parse_ini_file($envPath);
    if ($parsed !== false && is_array($parsed) && !empty($parsed)) {
        $envVars = $parsed;
    } else {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';') || str_starts_with($line, '//')) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($k, $v) = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim(trim($v), "'\"");
                    $envVars[$k] = $v;
                }
            }
        }
    }
}

// Base URL for the application (useful for linking CSS, JS, email links, etc.)
if (!empty($envVars['APP_URL'])) {
    define('BASE_URL', rtrim($envVars['APP_URL'], '/'));
} else {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    define('BASE_URL', $protocol . '://' . $host);
}

// Application Configuration
define('APP_NAME', $envVars['APP_NAME'] ?? 'GRM B2B');
define('APP_VERSION', $envVars['APP_VERSION'] ?? '1.0.0');
define('APP_ENV', $envVars['APP_ENV'] ?? 'development');
define('APP_TIMEZONE', $envVars['APP_TIMEZONE'] ?? 'Asia/Kolkata');
date_default_timezone_set(APP_TIMEZONE);


// SMTP Configuration
define('SMTP_HOST', $envVars['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $envVars['SMTP_PORT'] ?? 587);
define('SMTP_USER', $envVars['SMTP_USER'] ?? '');
define('SMTP_PASS', $envVars['SMTP_PASS'] ?? '');
define('SMTP_FROM', $envVars['SMTP_FROM'] ?? '');

// Cloudinary Configuration
define('CLOUDINARY_CLOUD_NAME', $envVars['CLOUDINARY_CLOUD_NAME'] ?? '');
define('CLOUDINARY_API_KEY', $envVars['CLOUDINARY_API_KEY'] ?? '');
define('CLOUDINARY_API_SECRET', $envVars['CLOUDINARY_API_SECRET'] ?? '');

/*
// Cashfree Configuration (Archived / Preserved for future use)
define('CASHFREE_APP_ID', $envVars['CASHFREE_APP_ID'] ?? 'YOUR_DEFAULT_APP_ID');
define('CASHFREE_SECRET_KEY', $envVars['CASHFREE_SECRET_KEY'] ?? 'YOUR_DEFAULT_SECRET_KEY');
define('CASHFREE_ENV', $envVars['CASHFREE_ENV'] ?? 'sandbox'); // sandbox or production
*/

// Razorpay Configuration (Active Payment Gateway)
define('RAZORPAY_KEY_ID', $envVars['RAZORPAY_KEY_ID'] ?? 'rzp_test_YourKeyIdHere');
define('RAZORPAY_KEY_SECRET', $envVars['RAZORPAY_KEY_SECRET'] ?? 'YourKeySecretHere');
define('RAZORPAY_CURRENCY', $envVars['RAZORPAY_CURRENCY'] ?? 'INR');

// SMSQuicker Configuration (Active SMS & WhatsApp Gateway)
define('SMSQUICKER_API_URL', $envVars['SMSQUICKER_API_URL'] ?? 'https://www.smsquicker.com/api/send/sms');
define('SMSQUICKER_API_KEY', $envVars['SMSQUICKER_API_KEY'] ?? '');
define('SMSQUICKER_MODE', $envVars['SMSQUICKER_MODE'] ?? 'devices');
define('SMSQUICKER_DEVICE_ID', $envVars['SMSQUICKER_DEVICE_ID'] ?? '');
define('SMSQUICKER_SIM_SLOT', $envVars['SMSQUICKER_SIM_SLOT'] ?? '1');
define('SMSQUICKER_SENDER_ID', $envVars['SMSQUICKER_SENDER_ID'] ?? '');
define('SMSQUICKER_WHATSAPP_ACCOUNT', $envVars['SMSQUICKER_WHATSAPP_ACCOUNT'] ?? '');


// ─── Image Helpers ───────────────────────────────────────────────────────────

if (!function_exists('get_image_url')) {
    /**
     * Returns an optimised image URL.
     * For Cloudinary URLs it injects q_auto,f_auto (WebP delivery) and optional resize.
     */
    function get_image_url($path, $width = null, $height = null) {
        if (empty($path)) return '';

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Cloudinary: auto quality + format, optional resize
            if (strpos($path, 'res.cloudinary.com') !== false && strpos($path, 'q_auto') === false) {
                $transform = [];
                if ($width)  $transform[] = 'w_' . (int)$width;
                if ($height) $transform[] = 'h_' . (int)$height;
                $transform[] = 'c_limit,q_auto,f_auto';
                $transformStr = implode(',', $transform);

                // Insert right after /upload/
                $path = preg_replace('#(/upload/)#', '$1' . $transformStr . '/', $path, 1);
            }
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (strpos($cleanPath, 'uploads/') === 0 && !file_exists(BASE_PATH . '/' . $cleanPath) && file_exists(BASE_PATH . '/public/' . $cleanPath)) {
            $cleanPath = 'public/' . $cleanPath;
        }

        return rtrim(BASE_URL, '/') . '/' . $cleanPath;
    }
}

if (!function_exists('lazy_img')) {
    /**
     * Outputs an optimised <img> tag with lazy loading and Cloudinary transforms.
     * Use $eager = true for above-the-fold / LCP images (hero banners, etc.).
     */
    function lazy_img($path, $alt = '', $class = '', $width = null, $height = null, $eager = false) {
        $src     = get_image_url($path, $width, $height);
        $loading = $eager ? 'eager' : 'lazy';
        $altE    = htmlspecialchars($alt,   ENT_QUOTES, 'UTF-8');
        $classE  = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $srcE    = htmlspecialchars($src,   ENT_QUOTES, 'UTF-8');
        $wAttr   = $width  ? " width=\"{$width}\""  : '';
        $hAttr   = $height ? " height=\"{$height}\"" : '';
        return "<img src=\"{$srcE}\" alt=\"{$altE}\" class=\"{$classE}\" loading=\"{$loading}\" decoding=\"async\"{$wAttr}{$hAttr}>";
    }
}

if (!function_exists('human_time_ago')) {
    /**
     * Converts a datetime string to human readable time ago (e.g. 5 mins ago, 2 hours ago)
     */
    function human_time_ago($datetime) {
        if (empty($datetime)) return '—';
        $time = strtotime($datetime);
        if (!$time) return '—';
        $diff = time() - $time;
        if ($diff < 0) return 'Just now';
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' mins ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('d M Y', $time);
    }
}

if (!function_exists('format_price')) {
    /**
     * Formats price without trailing .00 (e.g. 100.00 -> 100, 399.50 -> 399.50, 1000.00 -> 1,000)
     */
    function format_price($price) {
        if ($price === null || $price === '') return '0';
        $floatVal = (float)$price;
        if (floor($floatVal) == $floatVal) {
            return number_format($floatVal, 0);
        }
        return rtrim(rtrim(number_format($floatVal, 2, '.', ','), '0'), '.');
    }
}

if (!function_exists('render_discount_starburst')) {
    function render_discount_starburst($pct = 0, $extraClass = '') {
        $pctVal = (int)$pct;

        // 10-lobed scalloped flower rosette path (viewBox 0 0 200 200)
        $scallopPath = "M 100 22 A 26.5 26.5 0 0 1 145.85 36.9 A 26.5 26.5 0 0 1 174.18 75.9 A 26.5 26.5 0 0 1 174.18 124.1 A 26.5 26.5 0 0 1 145.85 163.1 A 26.5 26.5 0 0 1 100 178 A 26.5 26.5 0 0 1 54.15 163.1 A 26.5 26.5 0 0 1 25.82 124.1 A 26.5 26.5 0 0 1 25.82 75.9 A 26.5 26.5 0 0 1 54.15 36.9 A 26.5 26.5 0 0 1 100 22 Z";

        $pctDisplay = ($pctVal > 0) ? $pctVal . '%' : '';

        if ($pctVal > 0) {
            $textMarkup = '<text x="100" y="98" class="badge-pct-text" font-family="\'Montserrat\', \'Arial Black\', \'Impact\', \'Inter\', sans-serif" font-weight="900" font-size="56" letter-spacing="0.5">' . $pctDisplay . '</text>'
                        . '<text x="100" y="142" font-family="\'Montserrat\', \'Arial Black\', \'Impact\', \'Inter\', sans-serif" font-weight="900" font-size="28" letter-spacing="2">OFF</text>';
        } else {
            $textMarkup = '<text x="100" y="102" class="badge-pct-text" font-family="\'Montserrat\', \'Arial Black\', \'Impact\', \'Inter\', sans-serif" font-weight="900" font-size="44" letter-spacing="1">DEAL</text>'
                        . '<text x="100" y="140" font-family="\'Montserrat\', \'Arial Black\', \'Impact\', \'Inter\', sans-serif" font-weight="900" font-size="24" letter-spacing="2">OFF</text>';
        }

        return '<div class="scallop-discount-badge ' . htmlspecialchars($extraClass) . ' pointer-events-none select-none" style="aspect-ratio: 1/1; filter: drop-shadow(0 4px 8px rgba(0, 168, 204, 0.35));">
            <svg viewBox="0 0 200 200" class="w-full h-full block" xmlns="http://www.w3.org/2000/svg" style="overflow: visible;">
                <!-- Scalloped Rosette Flower Background in Aqua Blue -->
                <path d="' . $scallopPath . '" fill="#00A8CC" />
                
                <!-- White Typography -->
                <g fill="#ffffff" text-anchor="middle">
                    ' . $textMarkup . '
                </g>
            </svg>
        </div>';
    }
}

if (!function_exists('is_vendor_module_enabled')) {
    /**
     * Checks if the dynamic Multi-Vendor Module is enabled by Super Admin.
     * Default: true ('1')
     */
    function is_vendor_module_enabled(bool $forceRefresh = false): bool {
        static $requestMemo = null;
        if (!$forceRefresh && $requestMemo !== null) {
            return $requestMemo;
        }

        try {
            if (!class_exists('Core\Cache')) {
                $cacheFile = defined('CORE_PATH') ? (CORE_PATH . '/Cache.php') : (__DIR__ . '/../core/Cache.php');
                if (file_exists($cacheFile)) {
                    require_once $cacheFile;
                }
            }
            if (!class_exists('Core\Database')) {
                $dbFile = defined('CORE_PATH') ? (CORE_PATH . '/Database.php') : (__DIR__ . '/../core/Database.php');
                if (file_exists($dbFile)) {
                    require_once $dbFile;
                }
            }

            $val = ($forceRefresh || !class_exists('Core\Cache')) ? null : \Core\Cache::get('setting_vendor_module_enabled');
            if ($val === null) {
                $db = \Core\Database::getInstance();
                $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'vendor_module_enabled' LIMIT 1");
                $stmt->execute();
                $res = $stmt->fetchColumn();
                $val = ($res !== false && $res !== null) ? (string)$res : '1';
                if (class_exists('Core\Cache')) {
                    \Core\Cache::set('setting_vendor_module_enabled', $val, 3600);
                }
            }
            $requestMemo = ($val === '1' || $val === 1 || $val === true || $val === 'true');
            return $requestMemo;
        } catch (\Throwable $e) {
            return true;
        }
    }
}



