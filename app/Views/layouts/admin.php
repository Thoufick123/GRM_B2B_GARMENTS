<?php
$globalNavbar = \Core\Cache::get('layout_admin_navbar');
if ($globalNavbar === null) {
    $db = \Core\Database::getInstance();
    $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'navbar' AND is_active = 1 ORDER BY id DESC LIMIT 1");
    $globalNavbar = $stmt->fetch() ?: false;
    \Core\Cache::set('layout_admin_navbar', $globalNavbar, 3600);
}
$faviconUrl = '';
$cmsLogoUrl = '';
if ($globalNavbar) {
    $navData = json_decode($globalNavbar['content_data'], true) ?? [];
    $faviconUrl = $navData['global_settings']['favicon'] ?? $navData['favicon'] ?? '';
    $cmsLogoUrl = $navData['global_settings']['logo'] ?? $navData['logo'] ?? '';
}

// Logged In User Identity & Role Resolution
$currentUserId = (int)\Core\Session::get('user_id');
$currentUserRole = \Core\Session::get('user_role');
$currentUserName = \Core\Session::get('user_name') ?? 'Administrator';
$currentUserEmail = '';
$currentCustomRole = '';
$currentStoreName = '';

if ($currentUserId) {
    $uRow = \Core\Session::get('admin_profile_memo_' . $currentUserId);
    if ($uRow === null) {
        $dbUser = \Core\Database::getInstance();
        $stmtU = $dbUser->prepare("
            SELECT u.email, u.name, u.role, r.name as custom_role_name, vp.store_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN vendor_profiles vp ON u.id = vp.user_id 
            WHERE u.id = ? LIMIT 1
        ");
        $stmtU->execute([$currentUserId]);
        $uRow = $stmtU->fetch() ?: [];
        \Core\Session::set('admin_profile_memo_' . $currentUserId, $uRow);
    }
    if ($uRow) {
        $currentUserName = !empty($uRow['name']) ? $uRow['name'] : $currentUserName;
        $currentUserEmail = $uRow['email'] ?? '';
        $currentCustomRole = $uRow['custom_role_name'] ?? '';
        $currentStoreName = $uRow['store_name'] ?? '';
    }
}

// Role badge configuration
if ($currentUserRole === 'super_admin') {
    $roleBadgeText = 'Super Admin';
    $roleBadgeIcon = '🛡️';
    $roleBadgeClass = 'bg-pink-50 text-[#F25996] border-pink-200';
    $avatarGradient = 'bg-gradient-to-tr from-[#F25996] to-[#d94480]';
} elseif ($currentUserRole === 'staff') {
    $roleTitle = !empty($currentCustomRole) ? $currentCustomRole : 'Staff';
    $roleBadgeText = $roleTitle;
    $roleBadgeIcon = '👤';
    $roleBadgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
    $avatarGradient = 'bg-gradient-to-tr from-indigo-500 to-purple-600';
} elseif ($currentUserRole === 'vendor') {
    $roleBadgeText = 'Vendor' . (!empty($currentStoreName) ? " • {$currentStoreName}" : '');
    $roleBadgeIcon = '🏪';
    $roleBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
    $avatarGradient = 'bg-gradient-to-tr from-amber-500 to-orange-500';
} else {
    $roleBadgeText = ucfirst(str_replace('_', ' ', $currentUserRole ?? 'Admin'));
    $roleBadgeIcon = '💼';
    $roleBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
    $avatarGradient = 'bg-gradient-to-tr from-blue-500 to-cyan-600';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - Admin | ' . APP_NAME : 'Admin | ' . APP_NAME ?></title>
    
    <?php if (!empty($faviconUrl)): ?>
    <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/favicon.svg">
    <link rel="alternate icon" href="<?= BASE_URL ?>/favicon.ico">
    <?php endif; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Cloudinary CDN preconnect -->
    <link rel="preconnect" href="https://res.cloudinary.com" crossorigin>
    <link rel="dns-prefetch" href="https://res.cloudinary.com">
    
    <!-- Global HTML Sanitization Helpers -->
    <script>
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        function escapeHtmlStr(str) {
            return escapeHtml(str);
        }
        window.escapeHtml = escapeHtml;
        window.escapeHtmlStr = escapeHtmlStr;
    </script>
    
    <?php 
    $adminCssFile = defined('BASE_PATH') ? BASE_PATH . '/css/app.css' : __DIR__ . '/../../../css/app.css';
    $adminAssetVersion = file_exists($adminCssFile) ? filemtime($adminCssFile) : (defined('APP_VERSION') ? APP_VERSION : time());
    ?>
    <!-- Compiled Tailwind CSS with Auto Cache-Busting -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= $adminAssetVersion ?>">
    
    <!-- Tailwind CDN for JIT & dynamic classes -->
    <script>
        (function() {
            var _w = console.warn;
            console.warn = function() {
                if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].indexOf('cdn.tailwindcss.com') !== -1) return;
                _w.apply(console, arguments);
            };
        })();
        window.tailwind = {
            config: {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                50:  '#fdf2f7',
                                100: '#fce7f1',
                                200: '#fbaed2',
                                300: '#f784b7',
                                400: '#f56b9c',
                                500: '#F25996',
                                600: '#e04481',
                                700: '#c72e6b',
                                800: '#a32457',
                                900: '#831e46',
                            }
                        },
                        fontFamily: {
                            sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                            display: ['Outfit', 'Inter', 'sans-serif'],
                            body: ['Inter', 'sans-serif']
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com" defer></script>

    <style>
        html, body, input, select, textarea, button {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
        }
        .font-display {
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }

        /* Global SVG Sizing Guard to prevent unconstrained SVGs */
        svg {
            display: inline-block;
            vertical-align: middle;
        }
        svg.w-3\.5 { width: 14px !important; height: 14px !important; }
        svg.w-4 { width: 16px !important; height: 16px !important; }
        svg.w-5 { width: 20px !important; height: 20px !important; }
        svg.w-6 { width: 24px !important; height: 24px !important; }
        svg.w-7 { width: 28px !important; height: 28px !important; }
        svg.w-8 { width: 32px !important; height: 32px !important; }
        svg.w-9 { width: 36px !important; height: 36px !important; }
        svg.w-10 { width: 40px !important; height: 40px !important; }
        svg.w-12 { width: 48px !important; height: 48px !important; }
        svg.w-16 { width: 64px !important; height: 64px !important; }

        /* ===== ADMIN SIDEBAR: Soft Pink #fdbfdd Theme ===== */
        body > aside {
            background: #fdbfdd !important;
            background-color: #fdbfdd !important;
            border-right: 1px solid #f9a8d4 !important;
            box-shadow: 2px 0 15px rgba(253, 191, 221, 0.25) !important;
        }
        /* Section labels (System, Sales, Management, Catalog) */
        body > aside p {
            color: rgb(232, 0, 89) !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
        }
        /* All nav links & dropdown triggers – default state */
        body > aside nav a,
        body > aside nav > div > div:first-child {
            color: rgb(232, 0, 89) !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
        }
        /* All icons – default state */
        body > aside nav svg {
            color: rgb(232, 0, 89) !important;
        }
        /* Hover state for nav items */
        body > aside nav a:hover,
        body > aside nav > div > div:first-child:hover {
            background-color: rgba(255,255,255,0.6) !important;
            color: rgb(232, 0, 89) !important;
        }
        body > aside nav a:hover svg,
        body > aside nav > div > div:first-child:hover svg {
            color: rgb(232, 0, 89) !important;
        }
        /* Active / current page state */
        body > aside nav a.bg-brand-50,
        body > aside nav a[class*="bg-brand-50"] {
            background-color: #ffffff !important;
            color: rgb(232, 0, 89) !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(232, 0, 89, 0.12) !important;
        }
        body > aside nav a.text-brand-700,
        body > aside nav a[class*="text-brand-700"] {
            color: rgb(232, 0, 89) !important;
        }
        body > aside nav a.text-brand-600,
        body > aside nav a[class*="text-brand-600"],
        body > aside nav svg.text-brand-600,
        body > aside nav div svg.text-brand-600 {
            color: rgb(232, 0, 89) !important;
        }
        /* Active icon */
        body > aside nav a.bg-brand-50 svg,
        body > aside nav a[class*="bg-brand-50"] svg {
            color: rgb(232, 0, 89) !important;
        }
        /* Dropdown sub-border lines */
        body > aside nav .border-l {
            border-left-color: rgba(232, 0, 89, 0.25) !important;
        }
        /* Bottom section (Storefront + Logout) */
        body > aside > div:last-of-type {
            border-top-color: rgba(232, 0, 89, 0.15) !important;
        }
        body > aside > div:last-of-type a,
        body > aside > div:last-of-type button {
            color: rgb(232, 0, 89) !important;
            border-radius: 0.75rem !important;
        }
        body > aside > div:last-of-type a svg,
        body > aside > div:last-of-type button svg {
            color: rgb(232, 0, 89) !important;
        }
        body > aside > div:last-of-type a:hover,
        body > aside > div:last-of-type button:hover {
            background-color: rgba(255,255,255,0.6) !important;
            color: rgb(232, 0, 89) !important;
        }
        body > aside > div:last-of-type a:hover svg,
        body > aside > div:last-of-type button:hover svg {
            color: rgb(232, 0, 89) !important;
        }
        /* Logo area text */
        body > aside .text-gray-900 { color: rgb(232, 0, 89) !important; }
        /* =========================================================
           GLOBAL PILL BUTTON DESIGN (MATCHING PRINT / BRAND PILL)
           Enforces uniform #F25996 Pill Style for Form Submit Buttons
           ========================================================= */

        /* 1. Universal Primary Pill Buttons (Save, Add, Create, Submit) */
        button[type="submit"]:not(.btn-delete):not(.text-red-500):not(.text-red-600):not(.text-rose-600):not(.text-gray-400):not(.p-1):not(.p-2):not([title*="Delete"]):not([title*="delete"]):not([title*="Clear"]),
        button.btn-primary, a.btn-primary,
        button.btn-save, a.btn-save,
        button.btn-add, a.btn-add,
        button.btn-pill, a.btn-pill, .btn-pill,
        a[href*="/create"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([title*="Edit"]):not([class*="inset-0"]),
        a[href*="/add"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([class*="inset-0"]),
        a[href*="/new"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([class*="inset-0"]),
        button[id*="Submit"], button[id*="submit"],
        button[id*="save"], button[id*="Save"],
        button[id*="create"], button[id*="Create"],
        button.bg-brand-600, a.bg-brand-600,
        button.bg-blue-600,
        button.bg-indigo-600,
        button.bg-purple-600 {
            background-color: #F25996 !important;
            border-color: #F25996 !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 6px rgba(242, 89, 150, 0.28) !important;
            transition: all 0.2s ease-in-out !important;
        }

        /* Hover states */
        button[type="submit"]:not(.btn-delete):not(.text-red-500):not(.text-red-600):not(.text-rose-600):not(.text-gray-400):not(.p-1):not(.p-2):not([title*="Delete"]):not([title*="delete"]):not([title*="Clear"]):hover,
        button.btn-primary:hover, a.btn-primary:hover,
        button.btn-save:hover, a.btn-save:hover,
        button.btn-add:hover, a.btn-add:hover,
        button.btn-pill:hover, a.btn-pill:hover, .btn-pill:hover,
        a[href*="/create"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([class*="inset-0"]):hover,
        a[href*="/add"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([class*="inset-0"]):hover,
        a[href*="/new"]:not(.text-blue-500):not(.text-gray-500):not([class*="text-indigo"]):not([class*="inset-0"]):hover,
        button[id*="Submit"]:hover, button[id*="submit"]:hover,
        button[id*="save"]:hover, button[id*="Save"]:hover,
        button[id*="create"]:hover, button[id*="Create"]:hover,
        button.bg-brand-600:hover, a.bg-brand-600:hover,
        button.bg-blue-600:hover,
        button.bg-indigo-600:hover,
        button.bg-purple-600:hover {
            background-color: #e04481 !important;
            border-color: #e04481 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(242, 89, 150, 0.38) !important;
            transform: translateY(-1px) !important;
        }

        /* =========================================================
           GLOBAL TABLE & ACTION EDIT BUTTONS (PURE BLUE ICON)
           Only the blue pencil-square / edit icon is displayed.
           Removed background pad, border, and box-shadow.
           ========================================================= */
        a[href*="/edit"]:not(.btn-pill):not([class*="px-5"]):not([class*="py-2.5"]):not([class*="rounded-full"]):not([class*="group-hover:text-indigo-600"]),
        a[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]),
        a[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[onclick*="openEdit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[onclick*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        .btn-action-edit,
        .bi-pencil-square {
            color: #2563eb !important;
        }

        a[href*="/edit"]:not(.btn-pill):not([class*="px-5"]):not([class*="py-2.5"]):not([class*="rounded-full"]):not([class*="group-hover:text-indigo-600"]),
        a[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]),
        a[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[onclick*="openEdit"]:not(.btn-pill):not([class*="rounded-full"]),
        button[onclick*="edit"]:not(.btn-pill):not([class*="rounded-full"]),
        .btn-action-edit {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            padding: 0.25rem !important;
            border-radius: 0.25rem !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            transition: color 0.15s ease-in-out, transform 0.15s ease-in-out !important;
            cursor: pointer !important;
        }

        a[href*="/edit"]:not(.btn-pill):not([class*="px-5"]):not([class*="py-2.5"]):not([class*="rounded-full"]):not([class*="group-hover:text-indigo-600"]):hover,
        a[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        a[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        button[title*="Edit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        button[title*="edit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        button[onclick*="openEdit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        button[onclick*="edit"]:not(.btn-pill):not([class*="rounded-full"]):hover,
        .btn-action-edit:hover,
        .bi-pencil-square:hover {
            color: #1d4ed8 !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            transform: scale(1.15) !important;
        }

        a[href*="/edit"]:not(.btn-pill) svg,
        a[title*="Edit"] svg,
        a[title*="edit"] svg,
        button[title*="Edit"] svg,
        button[title*="edit"] svg,
        button[onclick*="openEdit"] svg,
        button[onclick*="edit"] svg,
        .btn-action-edit svg,
        a[href*="/edit"]:not(.btn-pill) i,
        a[title*="Edit"] i,
        a[title*="edit"] i,
        button[title*="Edit"] i,
        button[title*="edit"] i,
        button[onclick*="openEdit"] i,
        button[onclick*="edit"] i,
        .btn-action-edit i {
            color: inherit !important;
            display: inline-block !important;
            line-height: 1 !important;
        }
        button[id*="create"]:hover, button[id*="Create"]:hover,
        button.bg-brand-600:hover, a.bg-brand-600:hover,
        button.bg-blue-600:hover, a.bg-blue-600:hover,
        button.bg-indigo-600:hover, a.bg-indigo-600:hover,
        button.bg-purple-600:hover, a.bg-purple-600:hover,
        button.bg-gray-900:not(.px-3):hover, a.bg-gray-900:not(.px-3):hover,
        button.bg-black:hover, a.bg-black:hover,
        .bg-\[\#F25996\]:hover, .bg-\[\#8e6c51\]:hover, .bg-\[\#1a50a8\]:hover, .bg-\[\#163366\]:hover,
        .hover\:bg-\[\#e04481\]:hover, .hover\:bg-\[\#d94480\]:hover, .hover\:bg-\[\#7a5c43\]:hover, .hover\:bg-\[\#153e83\]:hover {
            background-color: #e04481 !important;
            border-color: #e04481 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(242, 89, 150, 0.38) !important;
            transform: translateY(-1px) !important;
        }

        /* Active Click Effect */
        button[type="submit"]:active,
        button.btn-primary:active,
        a.btn-primary:active,
        .btn-pill:active {
            transform: scale(0.96) !important;
        }

        /* 2. Secondary & Modal Pill Buttons */
        button[onclick*="close"]:not(.text-gray-400):not(.p-1),
        button[onclick*="Close"]:not(.text-gray-400):not(.p-1),
        a[href*="/admin/"][class*="border-gray"],
        a.btn-secondary, button.btn-secondary {
            border-radius: 9999px !important;
            font-weight: 700 !important;
        }

        .text-\[\#8e6c51\], .text-\[\#1a50a8\],
        .hover\:text-\[\#8e6c51\]:hover, .hover\:text-\[\#1a50a8\]:hover {
            color: #F25996 !important;
        }

        .border-\[\#8e6c51\], .border-\[\#1a50a8\] {
            border-color: #F25996 !important;
        }

        .bg-\[\#fdf8f4\], .bg-\[\#fef6ee\] {
            background-color: #fdf2f7 !important;
        }
        .border-\[\#f3e6d8\], .border-\[\#f9dbaf\] {
            border-color: #FDBFDD !important;
        }

        /* =========================================================
           GLOBAL ADMIN SELECT & DROPDOWN STYLING
           Matches reference design:
           1. Single pink chevron (removes double native browser icon)
           2. Rounded card popup with pink header item
           3. White background options with dark text & pink hover/active state
           ========================================================= */
        select,
        select.form-select,
        select.filter-select,
        .admin-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #ffffff !important;
            color: #1f2937 !important;
            border: 1.5px solid #fbcfe8 !important;
            border-radius: 0.75rem !important;
            accent-color: #F25996 !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            outline: none !important;
            transition: all 0.2s ease-in-out !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23F25996' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.1em 1.1em !important;
            padding-right: 2.25rem !important;
        }

        select::-ms-expand {
            display: none !important;
        }

        select:focus,
        select.form-select:focus,
        select.filter-select:focus,
        .admin-select:focus {
            border-color: #F25996 !important;
            box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
            outline: none !important;
        }

        /* Native Dropdown Options */
        select option {
            background-color: #ffffff !important;
            color: #1f2937 !important;
            padding: 10px 14px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }

        select option:first-child,
        select option[value=""],
        select option[disabled],
        select option:disabled {
            background-color: #f9fafb !important;
            color: #6b7280 !important;
            font-weight: 600 !important;
        }

        select option:hover,
        select option:focus,
        select option:checked,
        select option:active {
            background-color: #fdf2f7 !important;
            background: #fdf2f7 !important;
            color: #be185d !important;
            font-weight: 600 !important;
        }

        /* Custom Dropdown Component Styling */
        .grm-select-wrapper {
            position: relative !important;
            display: inline-block;
            width: 100%;
        }

        .grm-select-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
            border: 1.5px solid #fbcfe8;
            border-radius: 0.75rem;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            user-select: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .grm-select-trigger:hover {
            border-color: #F25996;
        }

        .grm-select-trigger.is-active {
            border-color: #F25996;
            box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2);
        }

        .grm-select-trigger svg {
            width: 1.1rem;
            height: 1.1rem;
            color: #F25996;
            transition: transform 0.2s ease-in-out;
            flex-shrink: 0;
            margin-left: 0.5rem;
        }

        .grm-select-trigger.is-active svg {
            transform: rotate(180deg);
        }

        .grm-select-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1.5px solid #fbcfe8;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            z-index: 99999;
            max-height: 260px;
            overflow-y: auto;
            display: none;
            scrollbar-width: thin;
            scrollbar-color: #fbcfe8 transparent;
        }

        .grm-select-menu.is-open {
            display: block;
            animation: grmDropdownFade 0.15s ease-out;
        }

        @keyframes grmDropdownFade {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Top / Header Option - Clean Soft Style */
        .grm-select-option:first-child,
        .grm-select-option.is-header {
            background-color: #f9fafb !important;
            color: #6b7280 !important;
            font-weight: 600 !important;
            border-top-left-radius: 0.85rem;
            border-top-right-radius: 0.85rem;
            border-bottom: 1px solid #f3f4f6;
            padding: 10px 14px;
        }

        .grm-select-option {
            padding: 9px 14px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
        }

        .grm-select-option:hover:not(.is-header) {
            background-color: #fdf2f7 !important;
            color: #db2777 !important;
            font-weight: 600;
        }

        .grm-select-option.is-selected:not(:first-child) {
            background-color: #fce7f1;
            color: #be185d;
            font-weight: 700;
        }

        .grm-select-option.is-selected:hover:not(:first-child) {
            background-color: #fce7f1 !important;
            color: #be185d !important;
        }

        /* Custom Dropdown Menus & Popups in Admin */
        .dropdown-menu,
        [data-dropdown-menu],
        .custom-dropdown-content {
            background-color: #ffffff !important;
            border: 1px solid #fbcfe8 !important;
            border-radius: 1rem !important;
            box-shadow: 0 10px 25px -5px rgba(242, 89, 150, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</head>
<body class="bg-gray-50 flex min-h-screen text-gray-800 font-sans">

    <!-- Sidebar (Hover Expandable) -->
    <aside class="w-20 hover:w-64 transition-all duration-300 ease-in-out group bg-white flex-shrink-0 flex flex-col border-r border-gray-100 hidden md:flex absolute md:relative z-20 h-full">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-4 overflow-hidden">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center min-w-max">
                <?php if (!empty($cmsLogoUrl)): ?>
                    <img src="<?= htmlspecialchars($cmsLogoUrl) ?>" alt="Logo" class="h-9 w-9 object-contain flex-shrink-0">
                    <div class="ml-3 opacity-0 w-0 group-hover:w-auto group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center">
                        <img src="<?= htmlspecialchars($cmsLogoUrl) ?>" alt="Logo" class="h-10 max-h-12 max-w-[170px] object-contain">
                    </div>
                <?php else: ?>
                    <svg class="w-8 h-8 text-brand-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="ml-3 opacity-0 w-0 group-hover:w-auto group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex flex-col justify-center">
                        <span class="text-xl font-bold font-display tracking-tight text-gray-900 leading-none">GRM <span class="text-brand-600">B2B</span></span>
                    </div>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto overflow-x-hidden">
            <?php 
                $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
                $base = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
                if ($base !== '/' && $base !== '' && strpos($currentUri, $base) === 0) {
                    $currentUri = substr($currentUri, strlen($base));
                }
            ?>
            
            <?php if (\Core\Session::get('user_role') === 'vendor' && is_vendor_module_enabled()): ?>
                <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Vendor Portal</p>
                
                <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/dashboard' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>" title="Dashboard">
                    <svg class="w-6 h-6 flex-shrink-0 <?= $currentUri === '/admin/dashboard' ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Dashboard</span>
                </a>

                <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Modules</p>

                <style>
                    .vendor-mgmt-dropdown {
                        display: none;
                    }
                    .vendor-mgmt-menu:hover .vendor-mgmt-dropdown {
                        display: block;
                        animation: fadeIn 0.2s ease-in-out;
                    }
                    .vendor-mgmt-menu:hover .vendor-mgmt-chevron {
                        transform: rotate(180deg);
                    }
                </style>
                <div class="relative vendor-mgmt-menu">
                    <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= (strpos($currentUri, '/admin/catalog/products') === 0 || strpos($currentUri, '/admin/inventory') === 0 || strpos($currentUri, '/admin/orders') === 0 || strpos($currentUri, '/admin/reviews') === 0 || strpos($currentUri, '/admin/media') === 0) ? 'text-brand-600' : '' ?>">
                        <svg class="w-6 h-6 flex-shrink-0 <?= (strpos($currentUri, '/admin/catalog/products') === 0 || strpos($currentUri, '/admin/inventory') === 0 || strpos($currentUri, '/admin/orders') === 0 || strpos($currentUri, '/admin/reviews') === 0 || strpos($currentUri, '/admin/media') === 0) ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                            Management
                            <svg class="w-4 h-4 ml-2 transition-transform duration-200 vendor-mgmt-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                    
                    <div class="vendor-mgmt-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                        <?php $vendorProdPendingCount = (new \App\Models\Product())->getPendingApprovalCount((int)\Core\Session::get('user_id')); ?>
                        <a href="<?= BASE_URL ?>/admin/catalog/products" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/catalog/products') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center justify-between w-full">
                                <span>📦 Products</span>
                                <?php if ($vendorProdPendingCount > 0): ?>
                                    <span class="bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $vendorProdPendingCount ?> products pending review"><?= $vendorProdPendingCount ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/inventory" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/inventory') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">📈 Inventory</span>
                        </a>
                        <?php $vendorPendingOrderCount = (new \App\Models\Order())->getVendorPendingOrdersCount((int)\Core\Session::get('user_id')); ?>
                        <a href="<?= BASE_URL ?>/admin/orders" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/orders') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center justify-between w-full">
                                <span>🛒 Orders</span>
                                <?php if ($vendorPendingOrderCount > 0): ?>
                                    <span class="bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $vendorPendingOrderCount ?> pending orders to fulfill"><?= $vendorPendingOrderCount ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/reviews" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/reviews') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">⭐ Reviews</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/media" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/media') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">🖼️ Media Library</span>
                        </a>
                    </div>
                </div>

                <style>
                    .vendor-finance-dropdown {
                        display: none;
                    }
                    .vendor-finance-menu:hover .vendor-finance-dropdown {
                        display: block;
                        animation: fadeIn 0.2s ease-in-out;
                    }
                    .vendor-finance-menu:hover .vendor-finance-chevron {
                        transform: rotate(180deg);
                    }
                </style>
                <div class="relative vendor-finance-menu mt-1">
                    <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/finance') === 0 ? 'text-brand-600' : '' ?>">
                        <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/finance') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                            Finance & Wallet
                            <svg class="w-4 h-4 ml-2 transition-transform duration-200 vendor-finance-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                    
                    <div class="vendor-finance-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                        <a href="<?= BASE_URL ?>/admin/finance/payments" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/finance/payments' || $currentUri === '/admin/finance' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">💳 Payments & Wallet</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/finance/withdrawals" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/finance/withdrawals') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">🏦 Payout Requests</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/finance/transactions" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/finance/transactions') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">📜 Financial Ledger</span>
                        </a>
                    </div>
                </div>

                <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Account & Help</p>

                <?php $vendorOpenTicketsCount = (new \App\Models\SupportTicket())->getCounts((int)\Core\Session::get('user_id'))['open']; ?>
                <a href="<?= BASE_URL ?>/admin/support" class="flex items-center justify-between px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/support') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/support') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Support</span>
                    </div>
                    <?php if ($vendorOpenTicketsCount > 0): ?>
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $vendorOpenTicketsCount ?> open support tickets"><?= $vendorOpenTicketsCount ?></span>
                    <?php endif; ?>
                </a>

                <a href="<?= BASE_URL ?>/admin/vendor/settings" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/vendor/settings') === 0 || strpos($currentUri, '/admin/settings') === 0) ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>" title="Settings & Bank Accounts">
                    <svg class="w-6 h-6 flex-shrink-0 <?= (strpos($currentUri, '/admin/vendor/settings') === 0 || strpos($currentUri, '/admin/settings') === 0) ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Settings</span>
                </a>
            <?php else: ?>
            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('dashboard', 'view')): ?>
            <!-- ================= SYSTEM MODULE ================= -->
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">System</p>
            
            <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/dashboard' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>" title="Dashboard">
                <svg class="w-6 h-6 flex-shrink-0 <?= $currentUri === '/admin/dashboard' ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('all_buyers') || $this->hasAnyPermission('pending_buyers')): ?>
            <!-- ================= BUYERS MODULE ================= -->
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Buyers</p>

            <style>
                .buyers-dropdown {
                    display: none;
                }
                .buyers-menu:hover .buyers-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .buyers-menu:hover .buyers-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative buyers-menu">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/buyers') === 0 ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/buyers') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Buyers
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 buyers-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="buyers-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('all_buyers', 'view') || $this->hasAnyPermission('all_buyers')): ?>
                    <a href="<?= BASE_URL ?>/admin/buyers" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/buyers' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">All Buyers</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('pending_buyers', 'view') || $this->hasAnyPermission('pending_buyers')): ?>
                    <a href="<?= BASE_URL ?>/admin/buyers/pending" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/buyers/pending' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Pending Buyers</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('all_orders') || $this->hasAnyPermission('pending_payments') || $this->hasAnyPermission('bill_modifications') || $this->hasAnyPermission('packing_videos') || $this->hasAnyPermission('packing_slip_config')): ?>
            <style>
                .sales-dropdown {
                    display: none;
                }
                .sales-menu:hover .sales-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .sales-menu:hover .sales-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative sales-menu mt-1">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= (strpos($currentUri, '/admin/orders') === 0 || strpos($currentUri, '/admin/order-modifications') === 0) ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= (strpos($currentUri, '/admin/orders') === 0 || strpos($currentUri, '/admin/order-modifications') === 0) ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Orders
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 sales-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="sales-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('all_orders', 'view') || $this->hasAnyPermission('all_orders')): ?>
                    <a href="<?= BASE_URL ?>/admin/orders" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/orders' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">All Orders</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('pending_payments', 'view') || $this->hasAnyPermission('pending_payments')): ?>
                    <a href="<?= BASE_URL ?>/admin/orders/pending" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/orders/pending' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Pending Payments</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('bill_modifications', 'view') || $this->hasAnyPermission('bill_modifications')): ?>
                    <a href="<?= BASE_URL ?>/admin/order-modifications" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/order-modifications') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Bill Modifications</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('packing_videos', 'view') || $this->hasAnyPermission('packing_videos')): ?>
                    <a href="<?= BASE_URL ?>/admin/orders/packing-videos" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/orders/packing-videos' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">📹 Packing Videos & Backup</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('packing_slip_config', 'view') || $this->hasAnyPermission('packing_slip_config')): ?>
                    <a href="<?= BASE_URL ?>/admin/orders/packing-slip-config" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/orders/packing-slip-config') === 0 ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">🏷️ Packing Slip Config</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('reviews', 'view') || $this->hasAnyPermission('reviews')): ?>
            <a href="<?= BASE_URL ?>/admin/reviews" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/reviews') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/reviews') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Reviews</span>
            </a>
            <?php endif; ?>

            <style>
                .cancellations-dropdown {
                    display: none;
                }
                .cancellations-menu:hover .cancellations-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .cancellations-menu:hover .cancellations-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('all_cancellations') || $this->hasAnyPermission('cancellation_rules') || $this->hasAnyPermission('refunds')): ?>
            <div class="relative cancellations-menu mt-1">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= (strpos($currentUri, '/admin/cancellations') === 0) ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= (strpos($currentUri, '/admin/cancellations') === 0) ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Cancellations
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 cancellations-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="cancellations-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('all_cancellations', 'view')): ?>
                    <a href="<?= BASE_URL ?>/admin/cancellations" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/cancellations' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">All Cancellations</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('refunds', 'view')): ?>
                    <a href="<?= BASE_URL ?>/admin/cancellations/refunds" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/cancellations/refunds' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Refunds</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cancellation_rules', 'view') || $this->hasAnyPermission('cancellation_rules')): ?>
                    <a href="<?= BASE_URL ?>/admin/cancellations/rules" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/cancellations/rules' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Cancellation Rules</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('categories') || $this->hasAnyPermission('subcategories') || $this->hasAnyPermission('attributes') || $this->hasAnyPermission('products') || $this->hasAnyPermission('inventory') || $this->hasAnyPermission('fabric_customizations') || $this->hasAnyPermission('custom_orders') || $this->hasAnyPermission('order_rules') || $this->hasAnyPermission('fee_rules') || $this->hasAnyPermission('staff_management') || $this->hasAnyPermission('roles') || $this->hasAnyPermission('media_manager') || $this->hasAnyPermission('settings')): ?>
            <!-- ================= ADMIN MODULE ================= -->
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Admin</p>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('categories') || $this->hasAnyPermission('subcategories') || $this->hasAnyPermission('attributes') || $this->hasAnyPermission('products')): ?>
            <style>
                .catalog-dropdown {
                    display: none;
                }
                .catalog-menu:hover .catalog-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .catalog-menu:hover .catalog-chevron {
                    transform: rotate(180deg);
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-5px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>
            <div class="relative catalog-menu">
                <!-- Parent Menu Item -->
                <?php $isCatalogActive = strpos($currentUri, '/admin/catalog/') === 0 && (!isset($_GET['module']) || $_GET['module'] !== 'customize'); ?>
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= $isCatalogActive ? 'text-brand-600 font-bold' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= $isCatalogActive ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        <span>Catalog</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 catalog-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <!-- Dropdown Options -->
                <div class="catalog-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php
                    $sidebarCatReqCount = (new \App\Models\CategoryRequest())->getPendingCount();
                    $sidebarProdPendingCount = is_vendor_module_enabled() ? (new \App\Models\Product())->getPendingApprovalCount() : 0;
                    $isCustParam = (isset($_GET['module']) && $_GET['module'] === 'customize');
                    ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('categories', 'view') || $this->hasAnyPermission('categories')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/categories" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/categories') === 0 && !$isCustParam) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Categories</span>
                    </a>
                    <?php if (is_vendor_module_enabled()): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/category-requests" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/catalog/category-requests') === 0 ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center justify-between w-full">
                            Category Requests
                            <?php if ($sidebarCatReqCount > 0): ?>
                                <span class="bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1"><?= $sidebarCatReqCount ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('subcategories', 'view') || $this->hasAnyPermission('subcategories')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/subcategories" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/subcategories') === 0 && !$isCustParam) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Sub Categories</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('attributes', 'view') || $this->hasAnyPermission('attributes')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/attributes" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/attributes') === 0 && !$isCustParam) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Attributes</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('products', 'view') || $this->hasAnyPermission('products')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/products" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/products') === 0 && !$isCustParam) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center justify-between w-full">
                            Products
                            <?php if (is_vendor_module_enabled() && $sidebarProdPendingCount > 0): ?>
                                <span class="bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $sidebarProdPendingCount ?> products pending approval"><?= $sidebarProdPendingCount ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            $canAccessCustomize = \Core\Session::get('user_role') === 'super_admin' 
                || $this->hasAnyPermission('customize_categories')
                || $this->hasAnyPermission('customize_subcategories')
                || $this->hasAnyPermission('customize_attributes')
                || $this->hasAnyPermission('customize_products')
                || $this->hasAnyPermission('customization_labels')
                || $this->hasAnyPermission('custom_orders')
                || $this->hasAnyPermission('fabric_customizations');
            ?>
            <?php if ($canAccessCustomize): ?>
            <style>
                .customize-dropdown {
                    display: none;
                }
                .customize-menu:hover .customize-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .customize-menu:hover .customize-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <?php 
            $isCustomizeActive = (isset($_GET['module']) && $_GET['module'] === 'customize') || strpos($currentUri, '/admin/customize') === 0 || strpos($currentUri, '/admin/fabric-customizations') === 0 || strpos($currentUri, '/admin/customization-labels') === 0;
            ?>
            <div class="relative customize-menu mt-1">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= $isCustomizeActive ? 'text-amber-600 font-bold' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= $isCustomizeActive ? 'text-amber-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L11.758 4.758a3 3 0 114.242 4.242L12 12z"/></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        <span>Customize</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 customize-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="customize-dropdown ml-4 pl-4 border-l border-pink-200 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('customize_categories', 'view') || $this->hasAnyPermission('customize_categories')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/categories?module=customize" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/categories') === 0 && (isset($_GET['module']) && $_GET['module'] === 'customize')) ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            Category
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('customize_subcategories', 'view') || $this->hasAnyPermission('customize_subcategories')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/subcategories?module=customize" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/subcategories') === 0 && (isset($_GET['module']) && $_GET['module'] === 'customize')) ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Sub Category
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('customize_attributes', 'view') || $this->hasAnyPermission('customize_attributes')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/attributes?module=customize" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/attributes') === 0 && (isset($_GET['module']) && $_GET['module'] === 'customize')) ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            Attributes
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('customize_products', 'view') || $this->hasAnyPermission('customize_products')): ?>
                    <a href="<?= BASE_URL ?>/admin/catalog/products?module=customize" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= (strpos($currentUri, '/admin/catalog/products') === 0 && (isset($_GET['module']) && $_GET['module'] === 'customize')) ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Products / Fabrics
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('fabric_customizations', 'view') || $this->hasAnyPermission('fabric_customizations')): ?>
                    <a href="<?= BASE_URL ?>/admin/fabric-customizations" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/fabric-customizations') === 0 ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L11.758 4.758a3 3 0 114.242 4.242L12 12z"/></svg>
                            Fabric Customization Rules
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('customization_labels', 'view') || $this->hasAnyPermission('customization_labels')): ?>
                    <a href="<?= BASE_URL ?>/admin/customization-labels" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/customization-labels') === 0 ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Customization Labels
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('custom_orders', 'view') || $this->hasAnyPermission('custom_orders')): ?>
                    <a href="<?= BASE_URL ?>/admin/customize/orders" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/customize/orders') === 0 ? 'bg-pink-100 text-pink-900 font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Custom Orders
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('inventory', 'view') || $this->hasAnyPermission('inventory')): ?>
            <a href="<?= BASE_URL ?>/admin/inventory" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/inventory') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/inventory') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Inventory</span>
            </a>
            <?php endif; ?>
            
            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('order_rules', 'view') || $this->hasAnyPermission('order_rules')): ?>
            <a href="<?= BASE_URL ?>/admin/order-rules" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/order-rules') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/order-rules') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Order Rules</span>
            </a>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('fee_rules', 'view') || $this->hasAnyPermission('fee_rules')): ?>
            <a href="<?= BASE_URL ?>/admin/fee-rules" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/fee-rules') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/fee-rules') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Fee Rules</span>
            </a>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('staff_management') || $this->hasAnyPermission('roles')): ?>
            <style>
                .staff-dropdown {
                    display: none;
                }
                .staff-menu:hover .staff-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .staff-menu:hover .staff-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative staff-menu mt-1">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/staff') === 0 ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/staff') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Staff
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 staff-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="staff-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('staff_management', 'view') || $this->hasAnyPermission('staff_management')): ?>
                    <a href="<?= BASE_URL ?>/admin/staff" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/staff' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">All Staff</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('staff_management', 'create')): ?>
                    <a href="<?= BASE_URL ?>/admin/staff/create" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/staff/create' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Add Staff</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('roles', 'view') || $this->hasAnyPermission('roles')): ?>
                    <a href="<?= BASE_URL ?>/admin/staff/roles" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/staff/roles' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Role Assign</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            $hasCmsAccess = \Core\Session::get('user_role') === 'super_admin' || 
                $this->hasAnyPermission('cms_builder') ||
                $this->hasAnyPermission('cms_navbars') ||
                $this->hasAnyPermission('cms_topbars') ||
                $this->hasAnyPermission('cms_hero_banners') ||
                $this->hasAnyPermission('cms_main_banners') ||
                $this->hasAnyPermission('cms_product_carousels') ||
                $this->hasAnyPermission('cms_category_carousels') ||
                $this->hasAnyPermission('cms_categories_grid') ||
                $this->hasAnyPermission('cms_footers') ||
                $this->hasAnyPermission('cms_about_us') ||
                $this->hasAnyPermission('cms_size_charts') ||
                $this->hasAnyPermission('cms_product_card') ||
                $this->hasAnyPermission('cms_reviews') ||
                $this->hasAnyPermission('cms_projects') ||
                $this->hasAnyPermission('cms_reels');
            if ($hasCmsAccess): 
            ?>
            <style>
                .cms-dropdown {
                    display: none;
                }
                .cms-menu:hover .cms-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .cms-menu:hover .cms-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative cms-menu mt-1">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/cms') === 0 ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/cms') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        CMS Builder
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 cms-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="cms-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_builder', 'view') || $this->hasAnyPermission('cms_builder')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/builder" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/cms/builder' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Layout Builder</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_navbars', 'view') || $this->hasAnyPermission('cms_navbars')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/navbars" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/navbars') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Navbars</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_topbars', 'view') || $this->hasAnyPermission('cms_topbars')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/topbars" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/topbars') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Top Bars</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_hero_banners', 'view') || $this->hasAnyPermission('cms_hero_banners')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/hero-banners" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/hero-banners') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Hero Banners</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_main_banners', 'view') || $this->hasAnyPermission('cms_main_banners')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/main-banners" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/main-banners') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Main Banners</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_product_carousels', 'view') || $this->hasAnyPermission('cms_product_carousels')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/product-carousels" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/product-carousels') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Product Carousels</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_category_carousels', 'view') || $this->hasAnyPermission('cms_category_carousels')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/category-carousels" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/category-carousels') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Category Carousels</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_categories_grid', 'view') || $this->hasAnyPermission('cms_categories_grid')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/categories-grid" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/categories-grid') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Categories Grid</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_footers', 'view') || $this->hasAnyPermission('cms_footers')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/footers" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/footers') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Footers</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_about_us', 'view') || $this->hasAnyPermission('cms_about_us')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/about-us" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/about-us') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">About Us</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_size_charts', 'view') || $this->hasAnyPermission('cms_size_charts')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/size-charts" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/size-charts') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Size Charts</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_product_card', 'view') || $this->hasAnyPermission('cms_product_card')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/product-card-settings" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/product-card-settings') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Product Card</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_reviews', 'view') || $this->hasAnyPermission('cms_reviews')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/reviews" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/reviews') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Review</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_projects', 'view') || $this->hasAnyPermission('cms_projects')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/projects" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/projects') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Our Project</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('cms_reels', 'view') || $this->hasAnyPermission('cms_reels')): ?>
                    <a href="<?= BASE_URL ?>/admin/cms/reels" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/cms/reels') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center gap-1.5">
                            <span>Reels Container</span>
                            <span class="px-1.5 py-0.2 text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-600 rounded">New</span>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('media_manager', 'view') || $this->hasAnyPermission('media_manager')): ?>
            <a href="<?= BASE_URL ?>/admin/media" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/media') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/media') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Media Manager</span>
            </a>
            <?php endif; ?>

            <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('settings', 'view') || $this->hasAnyPermission('settings')): ?>
            <a href="<?= BASE_URL ?>/admin/settings" class="flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/settings') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>" title="System & Backup Settings">
                <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/settings') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Settings</span>
            </a>
            <?php endif; ?>

            <?php if (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('all_vendors') || $this->hasAnyPermission('pending_vendors') || $this->hasAnyPermission('vendor_analytics'))): ?>
            <!-- ================= VENDOR MODULE ================= -->
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap h-0 group-hover:h-auto">Vendor</p>

            <style>
                .vendors-dropdown {
                    display: none;
                }
                .vendors-menu:hover .vendors-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .vendors-menu:hover .vendors-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative vendors-menu">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/vendors') === 0 ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/vendors') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Vendors
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 vendors-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="vendors-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('vendor_analytics', 'view') || $this->hasAnyPermission('vendor_analytics')): ?>
                    <a href="<?= BASE_URL ?>/admin/vendors/dashboard" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/vendors/dashboard' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">📊 Revenue Dashboard</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('all_vendors', 'view') || $this->hasAnyPermission('all_vendors')): ?>
                    <a href="<?= BASE_URL ?>/admin/vendors" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/vendors' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">All Vendors</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('pending_vendors', 'view') || $this->hasAnyPermission('pending_vendors')): ?>
                    <a href="<?= BASE_URL ?>/admin/vendors/pending" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/vendors/pending' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Pending Vendors</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('payments') || $this->hasAnyPermission('withdrawals') || $this->hasAnyPermission('commissions') || $this->hasAnyPermission('transactions'))): ?>
            <style>
                .finance-dropdown {
                    display: none;
                }
                .finance-menu:hover .finance-dropdown {
                    display: block;
                    animation: fadeIn 0.2s ease-in-out;
                }
                .finance-menu:hover .finance-chevron {
                    transform: rotate(180deg);
                }
            </style>
            <div class="relative finance-menu <?= (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasAnyPermission('all_vendors') || $this->hasAnyPermission('pending_vendors') || $this->hasAnyPermission('vendor_analytics'))) ? 'mt-1' : '' ?>">
                <div class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors min-w-max text-gray-600 hover:bg-gray-50 hover:text-gray-900 cursor-pointer <?= strpos($currentUri, '/admin/finance') === 0 ? 'text-brand-600' : '' ?>">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/finance') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex-1 flex items-center justify-between">
                        Finance
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 finance-chevron text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </div>
                
                <div class="finance-dropdown ml-4 pl-4 border-l border-gray-100 space-y-1 mt-1">
                    <?php
                    $pendingPayoutsCount = (new \App\Models\Finance())->getPendingPayoutCount();
                    ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('payments', 'view') || $this->hasAnyPermission('payments')): ?>
                    <a href="<?= BASE_URL ?>/admin/finance/payments" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= $currentUri === '/admin/finance/payments' || $currentUri === '/admin/finance' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">💳 Payments Overview</span>
                    </a>
                    <?php endif; ?>
                    <?php if (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('withdrawals', 'view') || $this->hasAnyPermission('withdrawals'))): ?>
                    <a href="<?= BASE_URL ?>/admin/finance/withdrawals" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/finance/withdrawals') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap flex items-center justify-between w-full">
                            <span>🏦 Withdrawal Requests</span>
                            <?php if ($pendingPayoutsCount > 0): ?>
                                <span class="bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $pendingPayoutsCount ?> pending payout requests"><?= $pendingPayoutsCount ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('commissions', 'view') || $this->hasAnyPermission('commissions'))): ?>
                    <a href="<?= BASE_URL ?>/admin/finance/commissions" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/finance/commissions') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">⚙️ Commission Settings</span>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('transactions', 'view') || $this->hasAnyPermission('transactions')): ?>
                    <a href="<?= BASE_URL ?>/admin/finance/transactions" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/finance/transactions') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">📜 Financial Ledger</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (is_vendor_module_enabled() && (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('support', 'view') || $this->hasAnyPermission('support'))): ?>
            <?php $adminOpenTicketsCount = (new \App\Models\SupportTicket())->getCounts()['open']; ?>
            <a href="<?= BASE_URL ?>/admin/support" class="flex items-center justify-between px-3 py-3 mt-1 text-sm font-medium rounded-lg transition-colors min-w-max <?= strpos($currentUri, '/admin/support') === 0 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <div class="flex items-center">
                    <svg class="w-6 h-6 flex-shrink-0 <?= strpos($currentUri, '/admin/support') === 0 ? 'text-brand-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Support Desk</span>
                </div>
                <?php if ($adminOpenTicketsCount > 0): ?>
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1" title="<?= $adminOpenTicketsCount ?> open support queries"><?= $adminOpenTicketsCount ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <?php endif; ?>

        </nav>
        
        <!-- Bottom / Profile & Logout -->
        <div class="p-3 border-t border-gray-100 overflow-hidden flex flex-col space-y-2">
            <!-- User Summary Pill in Sidebar -->
            <div class="flex items-center px-2.5 py-2 rounded-xl bg-white border border-pink-100 min-w-max shadow-sm">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white flex-shrink-0 <?= $avatarGradient ?> shadow-xs">
                    <?= strtoupper(substr($currentUserName, 0, 1)) ?>
                </div>
                <div class="ml-2.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col min-w-0">
                    <span class="text-xs font-bold text-gray-900 truncate max-w-[130px]"><?= htmlspecialchars($currentUserName) ?></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider <?= ($currentUserRole === 'super_admin') ? 'text-[#F25996]' : (($currentUserRole === 'staff') ? 'text-indigo-600' : 'text-amber-600') ?>">
                        <?= $roleBadgeIcon ?> <?= htmlspecialchars($roleBadgeText) ?>
                    </span>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/" class="flex w-full items-center px-3 py-2 text-xs font-semibold text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors min-w-max" title="View Storefront">
                <svg class="w-5 h-5 flex-shrink-0 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Storefront</span>
            </a>
            <form action="<?= BASE_URL ?>/logout" method="POST">
                <button type="submit" class="flex w-full items-center px-3 py-2 text-xs font-semibold text-gray-600 rounded-lg hover:bg-rose-50 hover:text-rose-700 transition-colors min-w-max" title="Log out">
                    <svg class="w-5 h-5 flex-shrink-0 text-gray-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="ml-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Log out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden ml-0 md:ml-0 transition-all duration-300">

        <!-- Top Navigation / User Identity & Role Header Bar -->
        <header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-8 sticky top-0 z-20 shadow-2xs flex-shrink-0">
            <!-- Left: System Context & Status -->
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Online
                </span>
                <span class="text-xs font-medium text-gray-400 font-mono hidden sm:inline">GRM B2B Portal</span>
            </div>

            <!-- Right: Logged In User Identity & Role Badge -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition active:scale-95 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Storefront</span>
                </a>

                <!-- User Identity Card -->
                <div class="flex items-center gap-2.5 bg-gray-50/80 border border-gray-200/80 pl-2 pr-3.5 py-1 rounded-full shadow-2xs">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white shadow-xs <?= $avatarGradient ?>">
                        <?= strtoupper(substr($currentUserName, 0, 1)) ?>
                    </div>
                    <div class="flex flex-col text-left">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-gray-900 leading-tight"><?= htmlspecialchars($currentUserName) ?></span>
                            <!-- Role Badge -->
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $roleBadgeClass ?>">
                                <span><?= $roleBadgeIcon ?></span>
                                <span><?= htmlspecialchars($roleBadgeText) ?></span>
                            </span>
                        </div>
                        <?php if (!empty($currentUserEmail)): ?>
                            <span class="text-[10.5px] text-gray-400 font-medium leading-none truncate max-w-[160px]"><?= htmlspecialchars($currentUserEmail) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sign out -->
                <form action="<?= BASE_URL ?>/logout" method="POST" class="inline">
                    <button type="submit" title="Sign Out" class="p-2 rounded-full text-gray-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages & Confirm Interceptor -->
        <?php if ($flashError = \Core\Session::getFlash('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        title: 'Error!',
                        html: <?= json_encode($flashError) ?>, // using html in case there are <br> tags
                        icon: 'error',
                        confirmButtonColor: '#059669'
                    });
                });
            </script>
        <?php endif; ?>
        <?php if ($flashSuccess = \Core\Session::getFlash('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: <?= json_encode($flashSuccess) ?>,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#fff',
                        iconColor: '#10b981'
                    });
                });
            </script>
        <?php endif; ?>

        <!-- Native Confirm Interceptor for SweetAlert2 -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Intercept forms with onsubmit="return confirm(...)"
                document.querySelectorAll('form').forEach(form => {
                    const onsubmitAttr = form.getAttribute('onsubmit');
                    if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                        const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                        if (match) {
                            const message = match[1];
                            form.removeAttribute('onsubmit');
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#059669',
                                    cancelButtonColor: '#ef4444',
                                    confirmButtonText: 'Yes, proceed!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit();
                                    }
                                });
                            });
                        }
                    }
                });

                // Intercept elements with onclick="return confirm(...)"
                document.querySelectorAll('[onclick*="confirm("]').forEach(el => {
                    const onclickAttr = el.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes('confirm(')) {
                        const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
                        if (match) {
                            const message = match[1];
                            el.removeAttribute('onclick');
                            el.addEventListener('click', function(e) {
                                e.preventDefault();
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#059669',
                                    cancelButtonColor: '#ef4444',
                                    confirmButtonText: 'Yes, proceed!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        if (el.tagName.toLowerCase() === 'a' && el.hasAttribute('href')) {
                                            window.location.href = el.getAttribute('href');
                                        } else if (el.closest('form')) {
                                            el.closest('form').submit();
                                        } else {
                                            // Fallback for custom logic if not a link/form button
                                            // You could theoretically eval() the original onclick without the confirm if needed, 
                                            // but typical usage is link or form.
                                        }
                                    }
                                });
                            });
                        }
                    }
                });
            });

            // Global Delete Form Event Delegation
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form && form.classList && (form.classList.contains('delete-form') || (form.getAttribute('action') && form.getAttribute('action').includes('/delete')))) {
                    if (form.dataset.confirmed === 'true') {
                        return; // proceed with submission
                    }
                    e.preventDefault();
                    showCustomConfirmModal(() => {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }, 'delete');
                }
            });

            // =========================================================
            // Universal Admin Custom Dropdown Engine
            // Transforms native <select> into styled Pink-Header Card dropdowns
            // =========================================================
            function initCustomSelects() {
                document.querySelectorAll('select:not([multiple])').forEach(select => {
                    if (select.dataset.customized === 'true' || select.classList.contains('no-custom-select') || select.offsetParent === null && select.style.display === 'none') {
                        return;
                    }

                    select.dataset.customized = 'true';

                    // Hide native select visually but keep in DOM for forms/validation
                    select.style.position = 'absolute';
                    select.style.opacity = '0';
                    select.style.pointerEvents = 'none';
                    select.style.width = '0';
                    select.style.height = '0';
                    select.style.margin = '0';
                    select.style.padding = '0';
                    select.style.border = '0';
                    select.style.zIndex = '-1';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'grm-select-wrapper';
                    
                    // Copy dimensional classes
                    const originalClasses = select.getAttribute('class') || '';
                    originalClasses.split(' ').forEach(cls => {
                        if (cls.startsWith('w-') || cls.startsWith('max-w-') || cls === 'flex-1' || cls.startsWith('min-w-')) {
                            wrapper.classList.add(cls);
                        }
                    });

                    const trigger = document.createElement('div');
                    trigger.className = 'grm-select-trigger';
                    trigger.setAttribute('tabindex', '0');

                    const triggerText = document.createElement('span');
                    triggerText.className = 'truncate select-none';
                    const selectedOpt = select.options[select.selectedIndex];
                    triggerText.textContent = selectedOpt ? selectedOpt.text : 'Select...';

                    const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    chevron.setAttribute('fill', 'none');
                    chevron.setAttribute('viewBox', '0 0 24 24');
                    chevron.setAttribute('stroke', 'currentColor');
                    chevron.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>';

                    trigger.appendChild(triggerText);
                    trigger.appendChild(chevron);

                    const menu = document.createElement('div');
                    menu.className = 'grm-select-menu';

                    function populateOptions() {
                        menu.innerHTML = '';
                        Array.from(select.options).forEach((opt, idx) => {
                            const optEl = document.createElement('div');
                            optEl.className = 'grm-select-option';
                            
                            const isHeader = (idx === 0 && (opt.value === '' || opt.disabled || opt.text.toLowerCase().includes('select') || opt.text.toLowerCase().includes('choose')));
                            if (isHeader) {
                                optEl.classList.add('is-header');
                            }
                            if (opt.selected && !isHeader) {
                                optEl.classList.add('is-selected');
                            }
                            optEl.textContent = opt.text;

                            optEl.addEventListener('click', (e) => {
                                e.stopPropagation();
                                select.value = opt.value;
                                triggerText.textContent = opt.text;
                                
                                menu.querySelectorAll('.grm-select-option').forEach(o => o.classList.remove('is-selected'));
                                if (!isHeader) optEl.classList.add('is-selected');
                                
                                menu.classList.remove('is-open');
                                trigger.classList.remove('is-active');

                                // Trigger native events for inline onchange & event listeners
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                                select.dispatchEvent(new Event('input', { bubbles: true }));
                            });

                            menu.appendChild(optEl);
                        });
                    }

                    populateOptions();

                    // Toggle menu
                    trigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (select.disabled) return;
                        
                        // Close any other open dropdowns
                        document.querySelectorAll('.grm-select-menu.is-open').forEach(m => {
                            if (m !== menu) {
                                m.classList.remove('is-open');
                                m.parentElement?.querySelector('.grm-select-trigger')?.classList.remove('is-active');
                            }
                        });

                        menu.classList.toggle('is-open');
                        trigger.classList.toggle('is-active');
                    });

                    // Sync when native select changes programmatically
                    select.addEventListener('change', () => {
                        const currentOpt = select.options[select.selectedIndex];
                        if (currentOpt) {
                            triggerText.textContent = currentOpt.text;
                            menu.querySelectorAll('.grm-select-option').forEach((o, i) => {
                                if (i === select.selectedIndex && !o.classList.contains('is-header')) {
                                    o.classList.add('is-selected');
                                } else {
                                    o.classList.remove('is-selected');
                                }
                            });
                        }
                    });

                    // Observe dynamic option mutations (e.g. AJAX or category chaining)
                    const observer = new MutationObserver(() => {
                        populateOptions();
                        const currentOpt = select.options[select.selectedIndex];
                        if (currentOpt) triggerText.textContent = currentOpt.text;
                        if (select.disabled) {
                            wrapper.classList.add('opacity-50', 'pointer-events-none');
                        } else {
                            wrapper.classList.remove('opacity-50', 'pointer-events-none');
                        }
                    });
                    observer.observe(select, { childList: true, attributes: true, attributeFilter: ['disabled'] });
                    if (select.disabled) {
                        wrapper.classList.add('opacity-50', 'pointer-events-none');
                    }

                    // Insert in DOM
                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);
                    wrapper.appendChild(trigger);
                    wrapper.appendChild(menu);
                });
            }

            // Close dropdowns on outside click
            document.addEventListener('click', () => {
                document.querySelectorAll('.grm-select-menu.is-open').forEach(m => {
                    m.classList.remove('is-open');
                    m.parentElement?.querySelector('.grm-select-trigger')?.classList.remove('is-active');
                });
            });

            // Auto-init on load and DOM changes
            document.addEventListener('DOMContentLoaded', initCustomSelects);
            window.addEventListener('load', initCustomSelects);
            setTimeout(initCustomSelects, 150);
            setTimeout(initCustomSelects, 500);
        </script>

        <!-- Page Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <?php echo $content; ?>
        </main>
    </div>

    <script src="<?= BASE_URL ?>/js/app.js"></script>
    <script>
        // Global SweetAlert for Delete Forms using Event Delegation
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('delete-form')) {
                e.preventDefault();
                const form = e.target;
                    Swal.fire({
                        html: `
                            <div class="flex flex-col items-center pt-2 pb-2">
                                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-5">
                                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </div>
                                <h2 class="text-2xl font-serif text-gray-900 font-bold mb-3">Delete Item</h2>
                                <p class="text-gray-500 text-sm">This action cannot be undone. Are you sure?</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'CONFIRM',
                        cancelButtonText: 'CANCEL',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'rounded-3xl px-8 py-6 w-full max-w-sm',
                            actions: 'w-full flex gap-4 mt-8',
                            confirmButton: 'flex-1 bg-red-600 hover:bg-red-700 text-white rounded-full py-3.5 font-bold tracking-widest text-sm transition-colors',
                            cancelButton: 'flex-1 bg-white border border-red-300 text-red-600 hover:bg-red-50 rounded-full py-3.5 font-bold tracking-widest text-sm transition-colors'
                        }
                    }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>
