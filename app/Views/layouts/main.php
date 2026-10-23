<?php
$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
$cacheKey = $isPreview ? null : 'layout_main_components';

$db = \Core\Database::getInstance();
$activeCond = $isPreview ? "" : " AND is_active = 1";

$components = $cacheKey ? \Core\Cache::get($cacheKey) : null;
if (!$components) {
    $allCmpStmt = $db->query("SELECT * FROM cms_components WHERE section_type IN ('top_bar', 'navbar', 'footer') $activeCond ORDER BY id DESC");
    $allCmpRows = $allCmpStmt->fetchAll();

    $globalTopBar = null;
    $globalNavbar = null;
    $globalFooter = null;

    foreach ($allCmpRows as $row) {
        if ($row['section_type'] === 'top_bar' && !$globalTopBar) $globalTopBar = $row;
        elseif ($row['section_type'] === 'navbar' && !$globalNavbar) $globalNavbar = $row;
        elseif ($row['section_type'] === 'footer' && !$globalFooter) $globalFooter = $row;
    }

    $components = [
        'top_bar' => $globalTopBar ?: null,
        'navbar'  => $globalNavbar ?: null,
        'footer'  => $globalFooter ?: null,
    ];
    if ($cacheKey) {
        \Core\Cache::set($cacheKey, $components, 3600);
    }
}

$globalTopBar = $components['top_bar'] ?? null;
$hasDynamicTopBar = !empty($globalTopBar);
$globalNavbar = $components['navbar'] ?? null;
$hasDynamicNavbar = !empty($globalNavbar);
$globalFooter = $components['footer'] ?? null;

$faviconUrl = '';
if ($hasDynamicNavbar) {
    $navData = json_decode($globalNavbar['content_data'], true) ?? [];
    $faviconUrl = $navData['global_settings']['favicon'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (!empty($title)) ? htmlspecialchars($title) . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <?php if (!empty($faviconUrl)): ?>
    <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/favicon.svg">
    <link rel="alternate icon" href="<?= BASE_URL ?>/favicon.ico">
    <?php endif; ?>

    <!-- Google Fonts (Optimized Weights & display=swap) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Cloudinary & jsDelivr CDN preconnect (speeds up all images & scripts) -->
    <link rel="preconnect" href="https://res.cloudinary.com" crossorigin>
    <link rel="dns-prefetch" href="https://res.cloudinary.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

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

    <!-- Compiled Tailwind CSS (Fast, Minified, Fully Pre-Rendered) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?>">
    
    <?php if (!empty($isPreview)): ?>
    <!-- Tailwind CDN only for CMS Live Preview Editor -->
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
    <?php endif; ?>
    
    <!-- Global Font & Layout Override -->
    <style>
        html { font-size: 112.5%; } /* 18px base instead of 16px */
        html, body, input, select, textarea, button {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            font-size: 1rem;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .font-display {
            font-family: 'Outfit', 'Inter', system-ui, -apple-system, sans-serif !important;
        }
        
        /* Hide scrollbar for horizontal scroll containers */
        .hide-scrollbar::-webkit-scrollbar,
        .no-scrollbar::-webkit-scrollbar,
        .scrollbar-none::-webkit-scrollbar,
        .grm-breadcrumb-nav::-webkit-scrollbar,
        nav[aria-label="Breadcrumb"]::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
            -webkit-appearance: none !important;
        }
        .hide-scrollbar::-webkit-scrollbar-thumb,
        .no-scrollbar::-webkit-scrollbar-thumb,
        .scrollbar-none::-webkit-scrollbar-thumb,
        .hide-scrollbar::-webkit-scrollbar-track,
        .no-scrollbar::-webkit-scrollbar-track,
        .scrollbar-none::-webkit-scrollbar-track,
        .grm-breadcrumb-nav::-webkit-scrollbar-thumb,
        .grm-breadcrumb-nav::-webkit-scrollbar-track,
        nav[aria-label="Breadcrumb"]::-webkit-scrollbar-thumb,
        nav[aria-label="Breadcrumb"]::-webkit-scrollbar-track {
            display: none !important;
            background: transparent !important;
        }
        .hide-scrollbar,
        .no-scrollbar,
        .scrollbar-none,
        .grm-breadcrumb-nav,
        nav[aria-label="Breadcrumb"] {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        
        /* Ensure object-fit classes exist even if Tailwind JIT misses them due to dynamic PHP variables */
        .object-contain { object-fit: contain !important; }
        .object-fill { object-fit: fill !important; }
        .object-cover { object-fit: cover !important; }

        /* ===== MOBILE OVERFLOW / LAYOUT PROTECTION ===== */
        @media (max-width: 767px) {
            /* Prevent images from overflowing viewport width — but don't force height:auto as it breaks h-full containers */
            img, video, canvas, iframe {
                max-width: 100% !important;
            }
            /* Product cards inside grids take full column width */
            .grid > .product-card,
            #product-grid > .product-card {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }
            /* Drawer & Cart related slider: strictly 2 cards per view on mobile */
            #drawer-related-track .product-card,
            div[id="drawer-related-track"] .product-card,
            #cart-related-track .product-card,
            div[id="cart-related-track"] .product-card {
                width: calc(50% - 6px) !important;
                min-width: calc(50% - 6px) !important;
                max-width: calc(50% - 6px) !important;
                flex: 0 0 calc(50% - 6px) !important;
                flex-shrink: 0 !important;
                box-sizing: border-box !important;
            }
            /* Prevent flex children from overflowing */
            .product-card > *,
            .product-card-body > * {
                min-width: 0;
            }
            /* Catalog & home grid containers */
            #product-grid {
                overflow-x: hidden !important;
                width: 100% !important;
            }
            /* Ensure bottom sticky bars don't cause layout shift */
            .safe-area-pb {
                padding-bottom: env(safe-area-inset-bottom, 0px) !important;
            }
        }
    </style>

    <!-- Global Product Card CSS Variables -->
    <?php include __DIR__ . '/../storefront/components/product_card_settings.php'; ?>

    <!-- SweetAlert2 (deferred so it doesn't block page render) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- Swiper CSS & Deferred JS (Eliminates render blocking across storefront) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen overflow-x-hidden">

    <?php 
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $isCartPage = ($currentPath === '/cart' || $currentPath === '/cart/' || strpos($currentPath, '/cart') === 0);
    ?>

    <?php if ($hasDynamicTopBar): ?>
        <div class="cms-section transition-all duration-300 relative z-30 <?= $isCartPage ? 'hidden md:block' : '' ?>" data-cms-id="<?= htmlspecialchars($globalTopBar['id']) ?>" style="display: <?= $globalTopBar['is_active'] ? 'block' : 'none' ?>;">
        <?php 
            $contentData = json_decode($globalTopBar['content_data'], true) ?? [];
            include __DIR__ . '/../storefront/components/topbar.php'; 
        ?>
        </div>
    <?php endif; ?>

    <?php if ($hasDynamicNavbar): ?>
        <div class="cms-section transition-all duration-300 relative z-50 <?= $isCartPage ? 'hidden md:block' : '' ?>" data-cms-id="<?= htmlspecialchars($globalNavbar['id']) ?>" style="display: <?= $globalNavbar['is_active'] ? 'block' : 'none' ?>;">
        <?php 
            $contentData = json_decode($globalNavbar['content_data'], true) ?? [];
            include __DIR__ . '/../storefront/components/navbar.php'; 
        ?>
        </div>
    <?php else: ?>
    <!-- Header Navigation -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 <?= $isCartPage ? 'hidden md:block' : '' ?>">
        <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                
                <!-- Logo Left -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2 text-xl md:text-2xl font-bold text-gray-900 font-display">
                        <svg class="w-7 h-7 md:w-8 md:h-8 text-black" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        <span>GRM <span class="text-brand-600">B2B</span></span>
                    </a>
                </div>
                
                <!-- Center Nav (desktop only) -->
                <nav class="hidden md:flex space-x-10">
                    <?php
                    // Load categories for mega-menu (cached to avoid redundant DB roundtrips)
                    $_megaMenuData = class_exists('\Core\Cache') ? \Core\Cache::get('layout_mega_categories_data') : null;
                    if (!$_megaMenuData) {
                        $_megaDb = \Core\Database::getInstance();
                        $_megaStmt = $_megaDb->query("SELECT id, name, slug FROM categories WHERE status='active' ORDER BY name ASC");
                        $_megaCategories = $_megaStmt->fetchAll(\PDO::FETCH_ASSOC);
                        $_megaSubStmt = $_megaDb->query("SELECT id, category_id, name, slug FROM sub_categories WHERE status='active' ORDER BY name ASC");
                        $_megaSubCategories = $_megaSubStmt->fetchAll(\PDO::FETCH_ASSOC);
                        $_megaMenuData = [
                            'categories' => $_megaCategories,
                            'subCategories' => $_megaSubCategories
                        ];
                        if (class_exists('\Core\Cache')) {
                            \Core\Cache::set('layout_mega_categories_data', $_megaMenuData, 3600);
                        }
                    }
                    $_megaCategories = $_megaMenuData['categories'] ?? [];
                    $_megaSubCategories = $_megaMenuData['subCategories'] ?? [];
                    // Group subs by category_id
                    $_megaSubMap = [];
                    foreach ($_megaSubCategories as $sc) {
                        $_megaSubMap[$sc['category_id']][] = $sc;
                    }
                    ?>
                    <!-- Products Mega Menu -->
                    <div class="relative group">
                        <a href="<?= BASE_URL ?>/catalog" class="text-sm font-semibold text-gray-900 hover:text-brand-600 transition-colors flex items-center gap-1">
                            Categories
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                        <?php if (!empty($_megaCategories)): ?>
                        <div class="absolute left-1/2 -translate-x-1/2 top-full pt-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50" style="width: max(720px, 60vw);">
                            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden" style="border: 1px solid #f0f0f0;">
                                
                                <!-- Top bar -->
                                <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100" style="background: linear-gradient(135deg, #fdf4f3 0%, #fff 100%);">
                                    <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Browse Categories</span>
                                    <a href="<?= BASE_URL ?>/catalog" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition-colors">
                                        View All Products
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>

                                <!-- 3-column grid -->
                                <div class="grid grid-cols-3 gap-0 p-0">
                                    <?php foreach ($_megaCategories as $_idx => $_cat): ?>
                                    <div class="group/col border-r border-gray-50 last:border-r-0 p-5 hover:bg-gray-50 transition-colors duration-150">
                                        <!-- Category Header -->
                                        <a href="<?= BASE_URL ?>/catalog?category_id=<?= $_cat['id'] ?>" class="flex items-center gap-2.5 mb-3 group/link">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #fde8e6, #fbd5d2);">
                                                <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                            </div>
                                            <span class="text-xs font-black uppercase tracking-wider text-gray-800 group-hover/link:text-brand-600 transition-colors">
                                                <?= htmlspecialchars($_cat['name']) ?>
                                            </span>
                                        </a>
                                        <div class="w-full h-px bg-gradient-to-r from-brand-100 to-transparent mb-3"></div>
                                        
                                        <!-- Subcategories -->
                                        <?php if (!empty($_megaSubMap[$_cat['id']])): ?>
                                        <ul class="space-y-1.5">
                                            <?php foreach ($_megaSubMap[$_cat['id']] as $_sub): ?>
                                            <li>
                                                <a href="<?= BASE_URL ?>/catalog?category_id=<?= $_cat['id'] ?>&sub_category_id=<?= $_sub['id'] ?>" 
                                                   class="flex items-center gap-2 text-xs text-gray-500 hover:text-brand-600 transition-colors group/sub py-0.5">
                                                    <span class="w-1 h-1 rounded-full bg-gray-300 group-hover/sub:bg-brand-500 transition-colors flex-shrink-0"></span>
                                                    <?= htmlspecialchars($_sub['name']) ?>
                                                </a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php else: ?>
                                        <p class="text-xs text-gray-400 italic">All products</p>
                                        <?php endif; ?>
                                        
                                        <!-- View all link for this category -->
                                        <a href="<?= BASE_URL ?>/catalog?category_id=<?= $_cat['id'] ?>" class="mt-3 flex items-center gap-1 text-[10px] font-bold text-brand-500 hover:text-brand-700 transition-colors opacity-0 group-hover/col:opacity-100">
                                            Browse all
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Footer bar -->
                                <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between" style="background: #fafafa;">
                                    <span class="text-xs text-gray-400 font-medium"><?= count($_megaCategories) ?> Categories Available</span>
                                    <a href="<?= BASE_URL ?>/catalog" class="inline-flex items-center gap-1.5 text-xs font-bold text-white px-4 py-1.5 rounded-full transition-all hover:opacity-90" style="background: linear-gradient(135deg, #c73e25, #a02d1a);">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Shop All
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>/customize" class="text-sm font-bold text-amber-600 hover:text-amber-700 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L11.758 4.758a3 3 0 114.242 4.242L12 12z"/></svg>
                        Customize
                    </a>
                    <a href="<?= BASE_URL ?>/about" class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">About Us</a>
                    <a href="<?= BASE_URL ?>/catalog" class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">Our Store</a>
                </nav>

                <!-- Icons Right -->
                <div class="flex items-center space-x-3 md:space-x-5">
                    <!-- Search Icon -->
                    <button class="text-gray-900 hover:text-brand-600 transition-colors" onclick="openSearchModal(event)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>

                    <!-- Wishlist Icon (hidden on mobile — shown in bottom nav) -->
                    <a href="javascript:void(0)" onclick="openWishlistDrawer()" class="hidden sm:inline-flex text-gray-900 hover:text-red-500 transition-colors relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        <?php if (\Core\Session::get('user_id')): ?>
                            <span id="wishlist-badge" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center hidden">0</span>
                        <?php endif; ?>
                    </a>

                    <!-- Cart Icon -->
                    <a href="javascript:void(0)" onclick="openCartDrawer()" class="text-gray-900 hover:text-brand-600 transition-colors relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <?php if (\Core\Session::get('user_id')): ?>
                            <span id="cart-badge" class="absolute -top-1.5 -right-1.5 bg-brand-600 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center hidden">0</span>
                        <?php endif; ?>
                    </a>

                    <!-- User Account (desktop) -->
                    <?php if (\Core\Session::get('user_id')): ?>
                        <?php 
                            $userId = \Core\Session::get('user_id');
                            $userName = \Core\Session::get('user_name');
                            
                            if (empty($userName) && $userId) {
                                $db = \Core\Database::getInstance();
                                $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
                                $stmt->execute([$userId]);
                                $userRow = $stmt->fetch();
                                if ($userRow && !empty($userRow['name'])) {
                                    $userName = $userRow['name'];
                                    \Core\Session::set('user_name', $userName);
                                } else {
                                    $userName = 'User';
                                }
                            }
                            $initial = strtoupper(substr($userName, 0, 1));
                        ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 hover:opacity-80 transition-opacity focus:outline-none">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-white/60 bg-white text-[#F25996]">
                                    <?= $initial ?>
                                </div>
                                <span class="font-semibold text-[15px] hidden md:block" style="color: #5a6448;">
                                    <?= htmlspecialchars($userName) ?>
                                </span>
                            </button>
                            <!-- Dropdown -->
                            <div class="absolute right-0 w-48 mt-2 origin-top-right bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="py-1">
                                    <?php if (\Core\Session::get('user_role') === 'vendor'): ?>
                                        <a href="<?= BASE_URL ?>/admin/dashboard" class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50 font-bold flex items-center justify-between">
                                            <span>Vendor Portal</span>
                                            <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full font-semibold">Vendor</span>
                                        </a>
                                    <?php elseif (in_array(\Core\Session::get('user_role'), ['super_admin', 'manager', 'staff'])): ?>
                                        <a href="<?= BASE_URL ?>/admin/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-semibold text-brand-600">Admin Dashboard</a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Account</a>
                                    <?php endif; ?>
                                    <form action="<?= BASE_URL ?>/logout" method="POST" class="w-full text-left">
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="hidden sm:flex items-center space-x-3 ml-2">
                            <?php if (is_vendor_module_enabled()): ?>
                            <a href="<?= BASE_URL ?>/vendor-register" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-full border border-indigo-200 transition-colors flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Become a Seller
                            </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/login" class="text-sm font-semibold text-gray-900 hover:text-brand-600">Log In</a>
                            <a href="<?= BASE_URL ?>/register" class="bg-gray-900 text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-800 transition-colors">Sign Up</a>
                        </div>
                    <?php endif; ?>

                    <!-- Hamburger (mobile only) -->
                    <button id="mobile-menu-btn" class="md:hidden p-1.5 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Open menu">
                        <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white shadow-lg">
            <nav class="px-4 py-4 space-y-1">
                <a href="<?= BASE_URL ?>/catalog" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Products
                </a>
                <a href="<?= BASE_URL ?>/customize" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L11.758 4.758a3 3 0 114.242 4.242L12 12z"/></svg>
                    Customize Workshop
                </a>
                <a href="<?= BASE_URL ?>/about" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    About Us
                </a>
                <a href="<?= BASE_URL ?>/catalog" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Our Store
                </a>

                <?php if (\Core\Session::get('user_id')): ?>
                <!-- Logged-in user links -->
                <div class="border-t border-gray-100 mt-2 pt-2 space-y-1">
                    <button onclick="closeMobileMenuAndRun(openWishlistDrawer)" class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        Wishlist
                    </button>
                    <button onclick="closeMobileMenuAndRun(openCartDrawer)" class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Cart
                    </button>
                    <?php if (\Core\Session::get('user_role') === 'vendor'): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Vendor Portal
                            </div>
                            <span class="bg-indigo-200 text-indigo-800 text-xs px-2 py-0.5 rounded-full font-semibold">Vendor</span>
                        </a>
                    <?php elseif (in_array(\Core\Session::get('user_role'), ['super_admin', 'manager', 'staff'])): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            Admin Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            My Account
                        </a>
                    <?php endif; ?>
                </div>
                <!-- Logout -->
                <div class="border-t border-gray-100 mt-1 pt-1">
                    <form action="<?= BASE_URL ?>/logout" method="POST">
                        <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </nav>
            <?php if (!\Core\Session::get('user_id')): ?>
            <div class="px-4 pb-4 flex flex-col gap-2">
                <div class="flex gap-2">
                    <a href="<?= BASE_URL ?>/login" class="flex-1 text-center py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors">Log In</a>
                    <a href="<?= BASE_URL ?>/register" class="flex-1 text-center py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">Buyer Sign Up</a>
                </div>
                <?php if (is_vendor_module_enabled()): ?>
                <a href="<?= BASE_URL ?>/vendor-register" class="w-full text-center py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-colors flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Become a Vendor / Seller
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>

    <script>
    // Close mobile menu and then execute a callback (used for drawer openers)
    function closeMobileMenuAndRun(callback) {
        var menu = document.getElementById('mobile-menu');
        var hIcon = document.getElementById('hamburger-icon');
        var cIcon = document.getElementById('close-icon');
        if (menu) menu.classList.add('hidden');
        if (hIcon) hIcon.classList.remove('hidden');
        if (cIcon) cIcon.classList.add('hidden');
        if (typeof callback === 'function') callback();
    }
    // Mobile hamburger toggle
    (function(){
        var btn = document.getElementById('mobile-menu-btn');
        var menu = document.getElementById('mobile-menu');
        var hIcon = document.getElementById('hamburger-icon');
        var cIcon = document.getElementById('close-icon');
        if (!btn) return;
        btn.addEventListener('click', function(){
            var open = !menu.classList.contains('hidden');
            if (open) {
                menu.classList.add('hidden');
                hIcon.classList.remove('hidden');
                cIcon.classList.add('hidden');
            } else {
                menu.classList.remove('hidden');
                hIcon.classList.add('hidden');
                cIcon.classList.remove('hidden');
            }
        });
        // Close on resize to desktop
        window.addEventListener('resize', function(){
            if (window.innerWidth >= 768) {
                menu.classList.add('hidden');
                hIcon.classList.remove('hidden');
                cIcon.classList.add('hidden');
            }
        });
    })();
    </script>

    <style>
        .grm-login-terms-modal {
            border-radius: 20px !important;
            padding: 22px 18px 18px 18px !important;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.25) !important;
            max-width: 440px !important;
            width: min(92vw, 440px) !important;
            margin: auto !important;
            font-family: inherit !important;
        }
        .grm-login-terms-modal .swal2-title {
            padding: 0 !important;
            margin: 0 !important;
        }
        .grm-login-terms-modal .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .grm-login-terms-modal .swal2-actions {
            margin-top: 14px !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .grm-login-terms-modal .swal2-confirm {
            width: 100% !important;
            border-radius: 12px !important;
            padding: 12px 20px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            background: #F25996 !important;
            box-shadow: 0 4px 12px rgba(242, 89, 150, 0.25) !important;
            margin: 0 !important;
            transition: all 0.2s ease !important;
        }
        .grm-login-terms-modal .swal2-confirm:hover {
            background: #d94883 !important;
            transform: translateY(-1px);
        }
        .grm-terms-scroll-box,
        .scrollbar-hide {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .grm-terms-scroll-box::-webkit-scrollbar,
        .scrollbar-hide::-webkit-scrollbar {
            display: none !important;
            width: 0px !important;
            height: 0px !important;
        }
    </style>

    <!-- Flash Messages -->
    <?php if ($flashError = \Core\Session::getFlash('error')): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: <?= json_encode($flashError) ?>,
                    confirmButtonColor: '#059669' // brand-600
                });
            });
        </script>
    <?php endif; ?>

    <?php if ($flashSuccess = \Core\Session::getFlash('success')): ?>
        <?php 
            $isWelcomeBack = (stripos($flashSuccess, 'Welcome') !== false || stripos($flashSuccess, 'signed in') !== false || stripos($flashSuccess, 'logged in') !== false);
        ?>
        <style>
            .swal-login-popup {
                border-radius: 1.25rem !important;
                padding: 1.5rem 1.25rem 1.25rem !important;
                max-width: calc(100vw - 2rem) !important;
                box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.25) !important;
            }
            .swal-login-popup .swal-login-html {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .swal-login-popup .swal2-actions {
                margin-top: 1.25rem !important;
                margin-bottom: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
            .swal-login-popup .swal2-confirm {
                width: 100% !important;
                border-radius: 0.75rem !important;
                font-weight: 700 !important;
                font-size: 0.925rem !important;
                padding: 0.75rem 1.75rem !important;
                background: #F25996 !important;
                box-shadow: 0 4px 14px rgba(242, 89, 150, 0.35) !important;
                border: none !important;
                transition: all 0.2s ease !important;
            }
            .swal-login-popup .swal2-confirm:hover {
                background: #d94883 !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 6px 18px rgba(242, 89, 150, 0.45) !important;
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                <?php if ($isWelcomeBack): ?>
                Swal.fire({
                    html: '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;margin-bottom:14px;">'
                        + '<div style="width:58px;height:58px;background:#f0fdf4;border:2px solid #86efac;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px auto;box-shadow:0 4px 14px rgba(34,197,94,0.18);flex-shrink:0;">'
                        + '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">'
                        + '<polyline points="20 6 9 17 4 12"/>'
                        + '</svg>'
                        + '</div>'
                        + '<h3 style="font-size:22px;font-weight:800;color:#111827;margin:0 0 6px 0;line-height:1.2;font-family:inherit;">Success</h3>'
                        + '<p style="margin:0;font-size:14px;color:#4b5563;font-weight:600;"><?= htmlspecialchars($flashSuccess) ?></p>'
                        + '</div>'
                        + '<div class="grm-terms-scroll-box" style="background:#fff5f8;border:1px solid #fce7f3;border-radius:14px;padding:14px 16px;text-align:left;max-height:220px;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">'
                        + '<div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">'
                        + '<svg style="width:15px;height:15px;color:#db2777;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>'
                        + '<p style="margin:0;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#db2777;">Terms &amp; Conditions</p>'
                        + '</div>'
                        + '<ul style="margin:0;padding:0;list-style:none;font-size:11.5px;color:#374151;line-height:1.5;display:flex;flex-direction:column;gap:6px;">'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>Wholesale orders only.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>Products are supplied as per the quantity ordered.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>For products with assorted/random designs, pieces are selected randomly and may differ from the images shown on the website.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>Images are for reference only; actual design, print, color, or pattern may vary.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#e11d48;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(225,29,72,0.25);"></span>'
                        + '<span><strong style="color:#e11d48;">NO RETURN, NO EXCHANGE, NO CASH REFUND</strong> is accepted once the order is confirmed/delivered.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>Customers are requested to check the quantity and products at the time of delivery.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>Once the order is confirmed, cancellation is not allowed.</span>'
                        + '</li>'
                        + '<li style="display:flex;align-items:flex-start;gap:7px;">'
                        + '<span style="width:6px;height:6px;background:#F25996;border-radius:50%;margin-top:5.5px;flex-shrink:0;box-shadow:0 0 0 2px rgba(242,89,150,0.25);"></span>'
                        + '<span>By placing the order, the customer agrees to the above terms.</span>'
                        + '</li>'
                        + '</ul>'
                        + '</div>',
                    confirmButtonColor: '#F25996',
                    confirmButtonText: 'OK',
                    width: 460,
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'swal-login-popup',
                        htmlContainer: 'swal-login-html'
                    },
                    willOpen: function() {
                        document.documentElement.style.overflow = 'hidden';
                        document.body.style.overflow = 'hidden';
                        document.body.style.touchAction = 'none';
                    },
                    willClose: function() {
                        document.documentElement.style.overflow = '';
                        document.body.style.overflow = '';
                        document.body.style.touchAction = '';
                    },
                    didClose: function() {
                        document.documentElement.style.overflow = '';
                        document.body.style.overflow = '';
                        document.body.style.touchAction = '';
                    }
                });
                <?php else: ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: <?= json_encode($flashSuccess) ?>,
                    confirmButtonColor: '#F25996',
                    allowOutsideClick: false,
                    customClass: { popup: 'grm-login-terms-modal' },
                    willOpen: function() {
                        document.documentElement.style.overflow = 'hidden';
                        document.body.style.overflow = 'hidden';
                        document.body.style.touchAction = 'none';
                    },
                    willClose: function() {
                        document.documentElement.style.overflow = '';
                        document.body.style.overflow = '';
                        document.body.style.touchAction = '';
                    },
                    didClose: function() {
                        document.documentElement.style.overflow = '';
                        document.body.style.overflow = '';
                        document.body.style.touchAction = '';
                    }
                });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-grow w-full min-w-0">
        <?php echo $content; ?>
    </main>



    <!-- Vanilla JS Main File (Deferred to avoid render blocking) -->
    <script src="<?= BASE_URL ?>/js/app.js" defer></script>

    <!-- Cart Offcanvas Drawer -->
    <?php if (\Core\Session::get('user_id')): ?>
    <div id="cart-drawer-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[998] hidden transition-opacity duration-300 opacity-0" onclick="closeCartDrawer()"></div>
    
    <div id="cart-drawer" class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white z-[999] transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col">
        <!-- Header (Exact Match to Wishlist Header) -->
        <div class="px-3.5 sm:px-4 md:px-5 py-2.5 sm:py-3.5 md:py-4 border-b border-pink-100 flex items-center justify-between flex-shrink-0 bg-white">
            <h2 class="text-sm sm:text-base md:text-base font-bold text-[#F25996] flex items-center space-x-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#F25996]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span id="cart-drawer-title">Shopping Cart (0)</span>
            </h2>
            <button onclick="closeCartDrawer()" class="text-gray-400 hover:text-[#F25996] transition-colors p-1 rounded-full hover:bg-pink-50" aria-label="Close cart">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto px-4 md:px-6 py-4 md:py-6 bg-gray-50/50">
            <!-- Active Category Offers injected via JS -->
            <div id="cart-drawer-offers" class="space-y-2.5 mb-3"></div>

            <div id="cart-drawer-items" class="space-y-3">
                <!-- Items injected via JS -->
                <div class="text-center text-gray-500 py-8">Loading cart...</div>
            </div>
            
            <div id="cart-drawer-errors" class="mt-4 space-y-2"></div>
            
            <!-- Related Products injected via JS -->
            <div id="cart-drawer-related" class="hidden"></div>
        </div>

        <!-- Footer -->
        <div class="px-4 md:px-6 py-4 md:py-5 bg-[#F25996] border-t border-pink-200 flex-shrink-0 shadow-lg">
            <div class="flex items-center justify-between mb-1">
                <span class="text-base md:text-lg font-black text-[#ffffff]">Subtotal</span>
                <span id="cart-drawer-subtotal" class="text-lg md:text-xl font-black text-[#ffffff] font-mono">₹0</span>
            </div>
            <p class="text-xs md:text-sm text-pink-100 mb-3">Shipping and taxes calculated at checkout.</p>
            
            <a href="<?= BASE_URL ?>/cart" id="cart-drawer-checkout-btn" class="w-full bg-white text-[#F25996] hover:bg-pink-50 rounded-xl py-3.5 md:py-4 font-bold tracking-widest transition-all flex justify-center items-center shadow-md text-sm md:text-base active:scale-[0.99]">
                BUY NOW
            </a>
            <button onclick="closeCartDrawer()" class="w-full mt-3 text-sm font-semibold text-white hover:text-white/90 transition-colors py-1">
                or Continue Shopping &rarr;
            </button>
        </div>
    </div>

    <script>
        const baseUrl = '<?= BASE_URL ?>';
        window.formatJsPrice = function(num) {
            if (num === null || num === undefined) return '';
            num = parseFloat(num);
            if (isNaN(num)) return '';
            return Number.isInteger(num) ? num.toLocaleString('en-IN') : num.toFixed(2).replace(/\.00$/, '');
        };
        const formatJsPrice = window.formatJsPrice;
        window.renderDiscountStarburstJs = function(pct, extraClass = '', uid = '') {
            pct = parseInt(pct) || 0;
            if (pct <= 0) return '';
            const scallopPath = "M 100 22 A 26.5 26.5 0 0 1 145.85 36.9 A 26.5 26.5 0 0 1 174.18 75.9 A 26.5 26.5 0 0 1 174.18 124.1 A 26.5 26.5 0 0 1 145.85 163.1 A 26.5 26.5 0 0 1 100 178 A 26.5 26.5 0 0 1 54.15 163.1 A 26.5 26.5 0 0 1 25.82 124.1 A 26.5 26.5 0 0 1 25.82 75.9 A 26.5 26.5 0 0 1 54.15 36.9 A 26.5 26.5 0 0 1 100 22 Z";
            return `<div class="scallop-discount-badge ${extraClass} pointer-events-none select-none" style="aspect-ratio: 1/1; filter: drop-shadow(0 4px 8px rgba(0, 168, 204, 0.35));">
                <svg viewBox="0 0 200 200" class="w-full h-full block" xmlns="http://www.w3.org/2000/svg" style="overflow: visible;">
                    <path d="${scallopPath}" fill="#00A8CC" />
                    <g fill="#ffffff" text-anchor="middle">
                        <text x="100" y="98" class="badge-pct-text" font-family="'Montserrat', 'Arial Black', 'Impact', 'Inter', sans-serif" font-weight="900" font-size="56" letter-spacing="0.5">${pct}%</text>
                        <text x="100" y="142" font-family="'Montserrat', 'Arial Black', 'Impact', 'Inter', sans-serif" font-weight="900" font-size="28" letter-spacing="2">OFF</text>
                    </g>
                </svg>
            </div>`;
        };
        const renderDiscountStarburstJs = window.renderDiscountStarburstJs;
        const drawer = document.getElementById('cart-drawer');
        const backdrop = document.getElementById('cart-drawer-backdrop');
        const itemsContainer = document.getElementById('cart-drawer-items');
        const subtotalEl = document.getElementById('cart-drawer-subtotal');
        const errorsContainer = document.getElementById('cart-drawer-errors');
        const badge = document.getElementById('cart-badge');
        const wishlistBadge = document.getElementById('wishlist-badge');

        // Auto-fetch counts on page load
        window.scrollRelated = function(direction, trackId) {
            const track = document.getElementById(trackId);
            if (!track) return;
            const amount = track.clientWidth * 0.8;
            track.scrollBy({
                left: direction === 'left' ? -amount : amount,
                behavior: 'smooth'
            });
        };

        window.initTrackScroll = function(trackId) {
            const track = typeof trackId === 'string' ? document.getElementById(trackId) : trackId;
            if (!track || track.dataset.scrollInited) return;
            track.dataset.scrollInited = "true";

            // Click and drag scrolling for Desktop mouse
            let isDown = false, startX, scrollLeft, isDragging = false;
            track.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;
                isDown = true;
                isDragging = false;
                startX = e.pageX - track.offsetLeft;
                scrollLeft = track.scrollLeft;
                track.style.scrollSnapType = 'none';
            });
            track.addEventListener('mouseleave', () => {
                isDown = false;
                track.style.scrollSnapType = '';
            });
            track.addEventListener('mouseup', () => {
                isDown = false;
                track.style.scrollSnapType = '';
            });
            track.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                const x = e.pageX - track.offsetLeft;
                const walk = (x - startX) * 1.5;
                if (Math.abs(walk) > 4) {
                    isDragging = true;
                    track.scrollLeft = scrollLeft - walk;
                }
            });
            track.addEventListener('click', (e) => {
                if (isDragging) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        };

        document.addEventListener('DOMContentLoaded', () => {
            fetchCart();
            <?php if (\Core\Session::get('user_id') && \Core\Session::get('user_status') === 'active'): ?>
            fetchWishlistCount();
            <?php endif; ?>
            if (document.getElementById('cart-related-track')) {
                initTrackScroll('cart-related-track');
            }
        });

        function fetchWishlistCount() {
            fetch(baseUrl + '/wishlist/ajax-get')
            .then(res => res.json())
            .then(data => {
                if (data.success && wishlistBadge) {
                    const count = data.items.length;
                    if (count > 0) {
                        wishlistBadge.innerText = count;
                        wishlistBadge.classList.remove('hidden');
                    } else {
                        wishlistBadge.classList.add('hidden');
                    }
                }
            })
            .catch(() => {});
        }

        function openCartDrawer(skipFetch = false) {
            backdrop.classList.remove('hidden');
            const waBtn = document.getElementById('floating-whatsapp-btn');
            if (waBtn) waBtn.classList.add('hidden');
            // Small delay to allow display:block to take effect before opacity transition
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                drawer.classList.remove('translate-x-full');
            }, 10);
            if (!skipFetch) fetchCart();
        }

        function closeCartDrawer() {
            backdrop.classList.add('opacity-0');
            drawer.classList.add('translate-x-full');
            const waBtn = document.getElementById('floating-whatsapp-btn');
            if (waBtn && !window.location.pathname.endsWith('/cart')) {
                waBtn.classList.remove('hidden');
            }
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }

                function addWishlistItemToCart(productId, moq = 1, btnElement = null, event = null) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            let origContent = '';
            let origClasses = '';
            if (btnElement) {
                origContent = btnElement.innerHTML;
                origClasses = btnElement.className;
                btnElement.disabled = true;
                btnElement.className = "h-7 sm:h-8 px-2.5 bg-[#F25996] text-white rounded-full flex items-center justify-center gap-1 shadow-xs shrink-0 text-[10px] font-bold";
                btnElement.innerHTML = `
                    <svg class="animate-spin w-3 h-3 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Adding...</span>
                `;
            }

            fetch(baseUrl + '/cart/ajax-add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: moq, from_wishlist: 1 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (btnElement) {
                        btnElement.innerHTML = `
                            <svg class="w-3 h-3 text-white inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Added!</span>
                        `;
                    }

                    // Dynamically animate & remove the item from wishlist page and drawer
                    const pageItem = document.getElementById('wishlist-item-' + productId);
                    if (pageItem) {
                        pageItem.style.transition = 'all 0.35s ease';
                        pageItem.style.opacity = '0';
                        pageItem.style.transform = 'scale(0.92)';
                        setTimeout(() => {
                            pageItem.remove();
                            const container = document.getElementById('wishlist-container');
                            if (container && container.querySelectorAll('[id^="wishlist-item-"]').length === 0) {
                                container.innerHTML = `
                                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                                    <svg class="w-16 h-16 mx-auto text-[#F25996] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                    <p class="text-base sm:text-lg font-bold text-gray-800">Your wishlist is empty.</p>
                                    <p class="text-xs text-gray-400 mt-1 mb-4">Explore our catalog and save items you want to buy later.</p>
                                    <a href="${baseUrl}/catalog" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-text-gray hover:bg-[#F25996] px-5 py-2.5 rounded-xl transition-all shadow-sm shadow-pink-500/20">Browse Catalog</a>
                                </div>`;
                            }
                            if (typeof updatePageWishlistSelection === 'function') updatePageWishlistSelection();
                        }, 350);
                    }

                    const drawerItem = document.getElementById('wishlist-drawer-item-' + productId);
                    if (drawerItem) {
                        drawerItem.style.transition = 'all 0.35s ease';
                        drawerItem.style.opacity = '0';
                        drawerItem.style.transform = 'scale(0.92)';
                        setTimeout(() => {
                            drawerItem.remove();
                            const dItems = document.querySelectorAll('[id^="wishlist-drawer-item-"]');
                            if (dItems.length === 0 && typeof fetchWishlist === 'function') {
                                fetchWishlist();
                            }
                            if (typeof updateWishlistSelection === 'function') updateWishlistSelection();
                        }, 350);
                    }

                    if (typeof fetchWishlistCount === 'function') fetchWishlistCount();

                    if (data.cart_data) {
                        renderCart(data.cart_data);
                        if (typeof openCartDrawer === 'function') openCartDrawer(true);
                    } else {
                        fetchCart();
                        if (typeof openCartDrawer === 'function') openCartDrawer();
                    }
                } else {
                    if (btnElement) {
                        btnElement.disabled = false;
                        btnElement.className = origClasses;
                        btnElement.innerHTML = origContent;
                    }
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(err => {
                console.error('Error adding to cart:', err);
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.className = origClasses;
                    btnElement.innerHTML = origContent;
                }
            });
        }
        window.addWishlistItemToCart = addWishlistItemToCart;

        function ajaxAddToCart(event, formElement) {
            event.preventDefault();
            event.stopPropagation();
            
            const formData = new FormData(formElement);
            const productId = formData.get('product_id');
            const quantity = parseInt(formData.get('quantity')) || 1;
            const variantId = formData.get('variant_id') || null;
            const fromWishlist = formData.get('from_wishlist') || null;

            fetch(baseUrl + '/cart/ajax-add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity, variant_id: variantId, from_wishlist: fromWishlist })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if (fromWishlist) {
                        const pageItem = document.getElementById('wishlist-item-' + productId);
                        if (pageItem) pageItem.remove();
                        const drawerItem = document.getElementById('wishlist-drawer-item-' + productId);
                        if (drawerItem) drawerItem.remove();
                        const fallbackItem = formElement.closest('.border-\\[\\#eaf0d8\\]') || formElement.closest('.relative');
                        if (fallbackItem && fallbackItem !== pageItem && fallbackItem !== drawerItem) fallbackItem.remove();
                        
                        if (typeof fetchWishlistCount === 'function') fetchWishlistCount();
                        if (typeof closeWishlistDrawer === 'function') closeWishlistDrawer();
                    }
                    if (data.cart_data) {
                        renderCart(data.cart_data);
                        if(typeof openCartDrawer === 'function') openCartDrawer(true);
                    } else {
                        fetchCart();
                        if(typeof openCartDrawer === 'function') openCartDrawer();
                    }
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(err => console.error('Error adding to cart:', err));
        }

        function addToCart(event, productId, quantity = 1) {
            event.stopPropagation();
            event.preventDefault();

            fetch(baseUrl + '/cart/ajax-add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if (data.cart_data) {
                        renderCart(data.cart_data);
                        if(typeof openCartDrawer === 'function') openCartDrawer(true);
                    } else {
                        fetchCart();
                        if(typeof openCartDrawer === 'function') openCartDrawer();
                    }
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(err => console.error('Error adding to cart:', err));
        }

        function fetchCart(closeIfEmpty = false) {
            fetch(baseUrl + '/cart/ajax-get')
            .then(res => res.json())
            .then(data => {
                const cartData = data.cart_data || data;
                renderCart(cartData);
                if (closeIfEmpty && (!cartData.items || cartData.items.length === 0)) closeCartDrawer();
            })
            .catch(err => {
                console.error('Error fetching cart:', err);
                if (itemsContainer) itemsContainer.innerHTML = '<div class="text-center text-gray-500 py-6 text-xs sm:text-sm">Your cart is empty</div>';
            });
        }

        let cartUpdateTimers = {};
        let cartDrawerReqSeq = {};
        // Track pending (unsaved) quantities so background syncs don't overwrite faster taps
        let cartPendingQty = {}; // { cartItemId: { qty, moq, maxVal } }
        window.cartDrawerItemsMap = {}; // { [cartItemId]: { price, qty, moq, maxVal } }

        function calculateDrawerSubtotalInstant() {
            if (!subtotalEl) return;
            let sum = 0;
            for (const id in window.cartDrawerItemsMap) {
                const it = window.cartDrawerItemsMap[id];
                sum += (it.price * it.qty);
            }
            subtotalEl.innerText = '₹' + formatJsPrice(sum);
        }

        function updateCartItem(cartItemId, delta, moq, btnElement = null, maxVal = null) {
            let container = null;
            let span = null;
            
            if (btnElement) {
                container = btnElement.parentElement;
                span = container ? container.querySelector('span') : null;
            } else {
                span = document.getElementById('cart-drawer-qty-' + cartItemId);
                if (span) container = span.parentElement;
            }
            if (!span) return;

            if (!window.cartDrawerItemsMap[cartItemId]) {
                const itemEl = document.getElementById('cart-drawer-item-' + cartItemId);
                const price = itemEl ? parseFloat(itemEl.dataset.unitPrice || 0) : 0;
                window.cartDrawerItemsMap[cartItemId] = {
                    price: price,
                    qty: parseInt(span.innerText) || moq,
                    moq: moq,
                    maxVal: maxVal
                };
            }

            const item = window.cartDrawerItemsMap[cartItemId];
            let currentQty = item.qty;
            let newQty = currentQty + delta;
            
            if (newQty < moq) return; 
            
            if (maxVal !== null && maxVal > 0 && newQty > maxVal) {
                newQty = maxVal;
                item.qty = newQty;
                span.innerText = newQty;
                if (container) {
                    const plusBtn = container.querySelector('button:last-child');
                    if (plusBtn) plusBtn.disabled = true;
                }
                
                const errContainer = document.getElementById('cart-drawer-errors');
                if (errContainer) {
                    errContainer.innerHTML = `<div class="flex items-start gap-2 p-3 bg-pink-50 text-[#F25996] text-xs rounded-xl border border-pink-200 shadow-2xs font-semibold"><svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-[#F25996]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span>Maximum available inventory reached (${maxVal} units).</span></div>`;
                    setTimeout(() => { if (errContainer) errContainer.innerHTML = ''; }, 3500);
                }
                cartPendingQty[cartItemId] = { qty: newQty, moq: moq, maxVal: maxVal };
                calculateDrawerSubtotalInstant();
                return;
            }
            
            // 0ms Instant update in pure memory and instant DOM write
            item.qty = newQty;
            span.innerText = newQty;
            
            if (container) {
                const minusBtn = container.querySelector('button:first-child');
                if (minusBtn) {
                    minusBtn.disabled = (newQty <= moq);
                }
                const plusBtn = container.querySelector('button:last-child');
                if (plusBtn && maxVal !== null && maxVal > 0) {
                    plusBtn.disabled = (newQty >= maxVal);
                }
            }

            // Save the pending quantity
            cartPendingQty[cartItemId] = { qty: newQty, moq: moq, maxVal: maxVal };

            // 0ms Instant subtotal calculation in drawer from memory
            calculateDrawerSubtotalInstant();

            if (cartUpdateTimers[cartItemId]) {
                clearTimeout(cartUpdateTimers[cartItemId]);
            }

            // Debounce the network request by 75ms so rapid consecutive taps are bundled into an ultra-fast server call
            cartUpdateTimers[cartItemId] = setTimeout(() => {
                const sendQty = cartPendingQty[cartItemId] ? cartPendingQty[cartItemId].qty : newQty;
                const seq = (cartDrawerReqSeq[cartItemId] || 0) + 1;
                cartDrawerReqSeq[cartItemId] = seq;

                flushCartUpdate(cartItemId, sendQty)
                .then(res => res.json())
                .then(data => {
                    // Stale response check: If user clicked more since this request was sent, discard stale server response
                    if (seq !== cartDrawerReqSeq[cartItemId]) {
                        return;
                    }

                    // Only clear pending if user hasn't clicked newer taps while this was in flight
                    if (cartPendingQty[cartItemId] && cartPendingQty[cartItemId].qty === sendQty) {
                        delete cartPendingQty[cartItemId];
                    }
                    delete cartUpdateTimers[cartItemId];
                    const cData = data.cart_data || data;
                    if (data.success && cData && cData.items) {
                        renderCart(cData, true); // update subtotals/errors in place without re-creating DOM
                        if (cData.items.length === 0) closeCartDrawer();
                    } else if (data.success) {
                        fetchCart(true);
                    }
                })
                .catch(err => {
                    if (seq === cartDrawerReqSeq[cartItemId]) {
                        if (cartPendingQty[cartItemId] && cartPendingQty[cartItemId].qty === sendQty) {
                            delete cartPendingQty[cartItemId];
                        }
                        delete cartUpdateTimers[cartItemId];
                    }
                    console.error('Error updating cart:', err);
                });
            }, 75);
        }

        // Send a cart update to the server (keepalive: request survives page navigation/refresh)
        function flushCartUpdate(itemId, qty) {
            return fetch(baseUrl + '/cart/ajax-update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: itemId, quantity: qty }),
                keepalive: true
            });
        }

        // When user hides the tab or refreshes, flush any pending quantity saves immediately
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                Object.entries(cartPendingQty).forEach(([id, pending]) => {
                    flushCartUpdate(parseInt(id), pending.qty);
                });
            }
        });
        window.addEventListener('beforeunload', () => {
            Object.entries(cartPendingQty).forEach(([id, pending]) => {
                flushCartUpdate(parseInt(id), pending.qty);
            });
        });

        function removeCartItem(cartItemId, btnElement = null) {
            if (btnElement) {
                const container = btnElement.closest('.bg-white') || btnElement.closest('.flex.items-start');
                if (container) {
                    container.style.opacity = '0.5';
                    container.style.pointerEvents = 'none';
                }
            }
            if (cartUpdateTimers[cartItemId]) {
                clearTimeout(cartUpdateTimers[cartItemId]);
                delete cartUpdateTimers[cartItemId];
            }
            delete cartPendingQty[cartItemId];
            delete cartDrawerReqSeq[cartItemId];
            if (window.cartDrawerItemsMap) {
                delete window.cartDrawerItemsMap[cartItemId];
                calculateDrawerSubtotalInstant();
            }
            fetch(baseUrl + '/cart/ajax-remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: cartItemId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success && data.cart_data) {
                    renderCart(data.cart_data);
                    if (data.cart_data.items.length === 0) closeCartDrawer();
                } else if(data.success) {
                    fetchCart(true);
                }
            });
        }

        function quickAddToCart(productId, moq = 1, btnElement = null, event = null) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            let origContent = '';
            if (btnElement) {
                origContent = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = `
                    <svg class="animate-spin h-3.5 w-3.5 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Adding...</span>
                `;
            }

            fetch(baseUrl + '/cart/ajax-add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: moq })
            })
            .then(res => res.json())
            .then(data => {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-white inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <span>Added!</span>
                    `;
                    btnElement.classList.add('bg-[#c9080d]', 'text-white');
                    setTimeout(() => {
                        if (btnElement) {
                            btnElement.innerHTML = origContent;
                            btnElement.classList.remove('bg-[#c9080d]', 'text-white');
                        }
                    }, 1400);
                }
                if (data.success) {
                    if (data.cart_data) {
                        renderCart(data.cart_data);
                    } else {
                        fetchCart();
                    }
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(err => {
                console.error('Error adding to cart:', err);
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origContent;
                }
            });
        }

        function quickAddVariantToCart(productId, variantId, quantity, btnElement = null) {
            let origText = '';
            if (btnElement) {
                origText = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = `<span>Adding...</span>`;
            }
            fetch(baseUrl + '/cart/ajax-add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: quantity })
            })
            .then(res => res.json())
            .then(data => {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origText;
                }
                if (data.success) {
                    const cData = data.cart_data || data;
                    if (cData && cData.items) {
                        renderCart(cData);
                    } else {
                        fetchCart();
                    }
                    if (window.location.pathname.endsWith('/cart')) {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to add variant to cart');
                }
            })
            .catch(err => {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origText;
                }
                console.error('Error adding variant:', err);
            });
        }

        function renderCart(data, skipItemsRerender = false) {
            const hasPendingClicks = Object.keys(cartPendingQty).length > 0;
            
            // Sync in-memory drawer map
            if (data.items) {
                if (!skipItemsRerender) window.cartDrawerItemsMap = {};
                data.items.forEach(item => {
                    const itemBasePrice = parseFloat(item.base_price || 0);
                    const itemUnitPrice = parseFloat(item.unit_price || 0);
                    const itemDiscPrice = item.discount_price ? parseFloat(item.discount_price) : (itemUnitPrice < itemBasePrice ? itemUnitPrice : 0);
                    const effectivePrice = itemDiscPrice > 0 ? itemDiscPrice : itemUnitPrice;
                    const availStock = parseInt(item.available_stock || 0);
                    const currentQty = (cartPendingQty[item.cart_item_id] !== undefined)
                        ? cartPendingQty[item.cart_item_id].qty
                        : (parseInt(item.quantity) || 1);

                    window.cartDrawerItemsMap[item.cart_item_id] = {
                        price: effectivePrice,
                        qty: currentQty,
                        moq: parseInt(item.moq) || 1,
                        maxVal: availStock
                    };
                });
            }

            // If there are pending clicks, calculate instantly rather than taking older server subtotal
            if (hasPendingClicks) {
                calculateDrawerSubtotalInstant();
            } else if (subtotalEl && data.subtotal) {
                subtotalEl.innerText = data.subtotal;
            }


            const cartTitle = document.getElementById('cart-drawer-title');
            if (cartTitle && data.items) {
                cartTitle.innerText = `Shopping Cart (${data.items.length})`;
            }

            if (badge && data.items) {
                badge.innerText = data.items.length;
                if (data.items.length > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            if (!data.items || data.items.length === 0) {
                window.cartDrawerItemsMap = {};
                if (itemsContainer) itemsContainer.innerHTML = '<div class="text-center text-gray-500 py-8">Your cart is empty</div>';
            } else if (skipItemsRerender && itemsContainer && itemsContainer.children.length > 0) {
                // In-place updates: Do NOT wipe or recreate DOM nodes while user might be tapping
                data.items.forEach(item => {
                    const span = document.getElementById('cart-drawer-qty-' + item.cart_item_id);
                    if (span && cartPendingQty[item.cart_item_id] === undefined) {
                        span.innerText = item.quantity;
                        const stepperDiv = span.parentElement;
                        if (stepperDiv) {
                            const minusBtn = stepperDiv.querySelector('button:first-child');
                            const plusBtn = stepperDiv.querySelector('button:last-child');
                            const availStock = parseInt(item.available_stock || 0);
                            if (minusBtn) minusBtn.disabled = (item.quantity <= (item.moq || 1));
                            if (plusBtn && availStock > 0) plusBtn.disabled = (item.quantity >= availStock);
                        }
                    }
                });
            } else if (itemsContainer) {
                let html = '';

                data.items.forEach(item => {
                    let imgSrc = item.primary_image ? item.primary_image : 'https://placehold.co/100x100?text=No+Img';
                    const availStock = parseInt(item.available_stock || 0);
                    const isMaxStock = (availStock > 0 && item.quantity >= availStock);

                    const itemErrorHtml = item.item_error
                        ? `<div class="mt-2 flex items-start gap-1.5 text-[10px] sm:text-xs text-[#F25996] bg-pink-50 border border-pink-200 rounded-xl px-2.5 sm:px-3 py-1.5 sm:py-2 font-medium">
                               <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-[#F25996]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                               <span>${item.item_error}</span>
                           </div>`
                        : (item.item_notice ? `
                            <div class="mt-2 flex items-start gap-1.5 text-[10px] sm:text-xs text-[#F25996] bg-pink-50 border border-pink-100 rounded-xl px-2.5 sm:px-3 py-1 sm:py-1.5 font-medium">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>${item.item_notice}</span>
                            </div>
                        ` : '');

                    const itemBasePrice = parseFloat(item.base_price || 0);
                    const itemUnitPrice = parseFloat(item.unit_price || 0);
                    const itemDiscPrice = item.discount_price ? parseFloat(item.discount_price) : (itemUnitPrice < itemBasePrice ? itemUnitPrice : 0);
                    const hasDiscount = (itemBasePrice > 0 && itemDiscPrice > 0 && itemBasePrice > itemDiscPrice);
                    const discPct = hasDiscount ? Math.round(((itemBasePrice - itemDiscPrice) / itemBasePrice) * 100) : 0;

                    // Suggestions shown when out of stock OR when max inventory reached
                    const altVariantsHtml = ((item.is_out_of_stock || isMaxStock) && item.alternative_variants && item.alternative_variants.length > 0) ? `
                        <div class="mt-2 pt-2 border-t border-pink-100/80 flex flex-wrap items-center gap-1.5 w-full">
                            <span class="text-[10px] font-bold text-[#F25996] flex items-center gap-1">
                                <svg class="w-3 h-3 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                ${item.is_out_of_stock ? 'In Stock Alternatives:' : 'Need more? Other Available Variants:'}
                            </span>
                            ${item.alternative_variants.map(alt => `
                                <button type="button" onclick="quickAddVariantToCart(${item.product_id}, ${alt.id}, ${alt.moq || item.moq || 1}, this)" class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-pink-50 hover:bg-[#F25996] text-[#F25996] hover:text-white rounded-md text-[10px] font-bold border border-pink-200 transition-all active:scale-95 shadow-2xs cursor-pointer select-none" style="touch-action: manipulation;" title="Add this variant as a separate item below">
                                    <span>${alt.name}</span>
                                    <span class="font-normal">(₹${alt.formatted_price})</span>
                                    <span class="font-black">+ Add</span>
                                </button>
                            `).join('')}
                        </div>
                    ` : '';

                    html += `
                        <div id="cart-drawer-item-${item.cart_item_id}" data-cart-item-id="${item.cart_item_id}" data-unit-price="${itemDiscPrice || itemUnitPrice}" class="bg-white border border-gray-100/90 rounded-[22px] p-2.5 sm:p-3 hover:shadow-md transition-all duration-300 group relative shadow-xs mb-2 sm:mb-2.5">
                            <div class="flex items-start gap-2.5 sm:gap-3">
                                <!-- Product Thumbnail Image (88px x 88px rounded-[16px]) -->
                                <div class="rounded-[16px] overflow-hidden flex-shrink-0 relative block" style="width: 88px; height: 88px; min-width: 88px; min-height: 88px;">
                                    <img src="${imgSrc}" alt="${item.name}" class="rounded-[16px] block" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                
                                <!-- Center Details & Right Actions (Exact Wishlist Page Size & Layout) -->
                                <div class="flex-1 min-w-0 flex items-start justify-between gap-2 self-stretch">
                                    <!-- Center Info Column -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
                                        <div>
                                            <!-- Title -->
                                            <h4 class="text-xs sm:text-[13px] font-bold text-gray-900 leading-tight line-clamp-1" title="${item.name}">${item.name}</h4>
                                            
                                            <!-- Variant Name / Attributes -->
                                            ${item.variant_name ? `<div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5 truncate">${item.variant_name}</div>` : ''}

                                            <!-- Price Row with discount percentage -->
                                            <div class="flex items-baseline gap-1 mt-0.5 flex-wrap">
                                                ${hasDiscount ? `
                                                    <span class="text-xs sm:text-sm font-bold text-[#F25996]">₹${formatJsPrice(itemDiscPrice)}</span>
                                                    <span class="text-[10px] sm:text-[11px] text-gray-400 line-through font-normal">₹${formatJsPrice(itemBasePrice)}</span>
                                                    <span class="text-[8px] sm:text-[9px] font-bold text-[#F25996] bg-pink-50 border border-pink-100 px-1 py-0.2 rounded">${discPct}% OFF</span>
                                                ` : `
                                                    <span class="text-xs sm:text-sm font-bold text-[#F25996]">₹${formatJsPrice(itemUnitPrice)}</span>
                                                `}
                                            </div>

                                            <!-- MOQ Line -->
                                            <div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5">
                                                Min. Order: ${item.moq || 1} Pcs
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Action Column: Red Trash Icon on Top, Stepper Below (NO border line) -->
                                    <div class="flex flex-col justify-between items-end self-stretch shrink-0 pl-1">
                                        <!-- Top: Red Delete Button -->
                                        <button type="button" onclick="removeCartItem(${item.cart_item_id}, this)" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-0.5 -mr-1 rounded-lg transition-colors flex-shrink-0 cursor-pointer select-none" style="color: #ef4444; touch-action: manipulation;" title="Remove item">
                                            <svg class="w-4 h-4 text-red-500" style="color: #ef4444; stroke: #ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>

                                        <!-- Bottom (Below Trash): Quantity Stepper with NO dividing line -->
                                        <div class="inline-flex items-center border border-pink-200 rounded-xl bg-white p-0.5 shadow-2xs mt-auto select-none" style="touch-action: manipulation;">
                                            <button type="button" onclick="updateCartItem(${item.cart_item_id}, -${(item.moq && parseInt(item.moq) > 0) ? parseInt(item.moq) : 1}, ${item.moq || 1}, this, ${item.available_stock})" class="w-6 h-6 flex items-center justify-center text-[#F25996] hover:bg-pink-50 active:scale-90 active:bg-pink-100 rounded transition-all disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold cursor-pointer select-none" style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;" ${item.quantity <= (item.moq || 1) ? 'disabled' : ''}>&minus;</button>
                                            <div class="h-3 w-[1px] bg-pink-100"></div>
                                            <span id="cart-drawer-qty-${item.cart_item_id}" class="text-xs font-bold w-6 text-center text-[#F25996] select-none pointer-events-none">${item.quantity}</span>
                                            <div class="h-3 w-[1px] bg-pink-100"></div>
                                            <button type="button" onclick="updateCartItem(${item.cart_item_id}, ${(item.moq && parseInt(item.moq) > 0) ? parseInt(item.moq) : 1}, ${item.moq || 1}, this, ${item.available_stock})" class="w-6 h-6 flex items-center justify-center text-[#F25996] hover:bg-pink-50 active:scale-90 active:bg-pink-100 rounded transition-all disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold cursor-pointer select-none" style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;" ${isMaxStock ? 'disabled' : ''}>&plus;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${altVariantsHtml}
                            ${itemErrorHtml}
                        </div>
                    `;
                });
                itemsContainer.innerHTML = html;

                // Restore any pending (unsaved) quantities so the user's input isn't wiped out
                Object.keys(cartPendingQty).forEach(id => {
                    const pending = cartPendingQty[id];
                    const span = document.getElementById('cart-drawer-qty-' + id);
                    if (span) {
                        span.innerText = pending.qty;
                        const stepperDiv = span.parentElement;
                        if (stepperDiv) {
                            const minusBtn = stepperDiv.querySelector('button:first-child');
                            const plusBtn = stepperDiv.querySelector('button:last-child');
                            if (minusBtn) minusBtn.disabled = (pending.qty <= (pending.moq || 1));
                            if (plusBtn && pending.maxVal !== null && pending.maxVal > 0) {
                                plusBtn.disabled = (pending.qty >= pending.maxVal);
                            }
                        }
                    }
                });
            }

            // Render Active Category Offers & Progress (Cart Brand Palette: Pink theme)
            const offerContainer = document.getElementById('cart-drawer-offers');
            if (offerContainer) {
                if (data.activeOffers && data.activeOffers.length > 0 && data.items && data.items.length > 0) {
                    let offerHtml = '';
                    data.activeOffers.forEach(offer => {
                        if (offer.is_unlocked) {
                            let secondListHtml = '';
                            if (offer.secondary_offers && offer.secondary_offers.length > 0) {
                                secondListHtml = '<div class="mt-2.5 flex flex-wrap gap-1.5">';
                                offer.secondary_offers.forEach(so => {
                                    const inCartBadge = so.in_cart 
                                        ? (so.is_met 
                                            ? '<span class="text-[9px] bg-[#F25996] text-white px-1.5 py-0.5 rounded font-black shadow-2xs">In Cart ✓</span>'
                                            : `<span class="text-[9px] bg-pink-100 text-[#F25996] px-1.5 py-0.5 rounded font-bold border border-[#F25996]/40">Add ₹${so.remaining.toLocaleString()} more</span>`)
                                        : '';
                                    secondListHtml += `
                                        <a href="${baseUrl}/catalog?category_id=${so.id}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-pink-50 text-[#F25996] rounded-lg text-[11px] font-bold border border-pink-200 shadow-2xs transition-all hover:scale-105">
                                            <span>✨ ${so.name}</span>
                                            <span class="text-[10px] text-[#F25996] font-semibold">(Min ₹${so.special_min_amount.toLocaleString()})</span>
                                            ${inCartBadge}
                                        </a>`;
                                });
                                secondListHtml += '</div>';
                            }
                            offerHtml += `
                                <div class="bg-gradient-to-br from-pink-50/80 via-pink-50/50 to-white border border-pink-200 rounded-2xl p-3.5 shadow-sm">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-[#F25996] text-white flex items-center justify-center text-xs flex-shrink-0 font-bold shadow-xs mt-0.5">✓</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-black text-[#F25996] uppercase tracking-wider">🎉 SPECIAL OFFER UNLOCKED!</h4>
                                                <span class="text-[10px] font-bold text-white bg-[#F25996] border border-[#F25996] px-2 py-0.5 rounded-full shadow-2xs">Active</span>
                                            </div>
                                            <p class="text-[11px] text-gray-800 leading-snug mt-1">
                                                Your <strong>${offer.main_category_name}</strong> order reached the ₹${offer.min_required.toLocaleString()} tier. You unlocked special low minimums on these secondary categories:
                                            </p>
                                        </div>
                                    </div>
                                    ${secondListHtml}
                                </div>
                            `;
                        } else {
                            let secondNames = offer.secondary_offers ? offer.secondary_offers.map(s => s.name).join(', ') : '';
                            offerHtml += `
                                <div class="bg-gradient-to-br from-pink-50/60 via-pink-50/40 to-white border border-pink-200 rounded-2xl p-3.5 shadow-sm">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <span class="text-xs font-black text-[#F25996] flex items-center gap-1.5">
                                            <span>🎁</span>
                                            <span>Unlock ${offer.main_category_name} Combo Offer</span>
                                        </span>
                                        <span class="text-[11px] font-bold text-[#F25996] font-mono">₹${offer.main_spent.toLocaleString()} / ₹${offer.min_required.toLocaleString()}</span>
                                    </div>
                                    <div class="w-full bg-white h-2 rounded-full overflow-hidden p-0.5 border border-pink-200 shadow-2xs">
                                        <div class="bg-gradient-to-r from-[#F25996] to-pink-500 h-full rounded-full transition-all duration-500" style="width: ${offer.progress_percent}%"></div>
                                    </div>
                                    <p class="text-[11px] text-gray-800 mt-1.5 leading-snug">
                                        Add <strong>₹${offer.remaining.toLocaleString()}</strong> more of <em>${offer.main_category_name}</em> to unlock special low combo rates on <strong>${secondNames || 'subcategories'}</strong>!
                                    </p>
                                </div>
                            `;
                        }
                    });
                    offerContainer.innerHTML = offerHtml;
                } else {
                    offerContainer.innerHTML = '';
                }
            }

            // Render global MOQ/rule Errors (category-level only)
            const checkoutBtn = document.getElementById('cart-drawer-checkout-btn');
            const hasAnyError = (data.moqErrors && data.moqErrors.length > 0) || data.hasItemErrors;
            
            if (data.moqErrors && data.moqErrors.length > 0) {
                let errHtml = '';
                data.moqErrors.forEach(err => {
                    errHtml += `<div class="flex items-start gap-2 p-3 bg-pink-50 text-[#F25996] text-xs rounded-xl border border-pink-200 shadow-2xs font-medium">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-[#F25996]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <div class="flex-1 text-[#F25996] leading-relaxed [&>strong]:text-[#d8407d] [&>strong]:font-bold">${err}</div>
                    </div>`;
                });
                errorsContainer.innerHTML = errHtml;
            } else {
                errorsContainer.innerHTML = '';
            }

            const relatedContainer = document.getElementById('cart-drawer-related');
            if (relatedContainer && data.relatedProducts && data.relatedProducts.length > 0) {
                const suggestionUrl = data.suggestionUrl ? data.suggestionUrl : `${baseUrl}/catalog`;
                const hasUnlockedOffers = data.relatedProducts.some(rp => rp.is_unlocked_offer);
                let relHtml = `
                    <div class="mt-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span>You may also like</span>
                                ${hasUnlockedOffers ? '<span class="text-[10px] font-bold text-white bg-[#F25996] px-2 py-0.5 rounded-full">Unlocked Offers</span>' : ''}
                            </span>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="scrollRelated('left', 'drawer-related-track')" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-[#F25996] hover:text-white text-gray-700 flex items-center justify-center transition-colors shadow-2xs" title="Previous">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button type="button" onclick="scrollRelated('right', 'drawer-related-track')" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-[#F25996] hover:text-white text-gray-700 flex items-center justify-center transition-colors shadow-2xs" title="Next">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                                <a href="${suggestionUrl}" class="text-xs font-semibold text-[#F25996] hover:underline">View all</a>
                            </div>
                        </h3>
                        <div id="drawer-related-track" class="flex overflow-x-auto pb-4 gap-2.5 sm:gap-3 snap-x snap-mandatory hide-scrollbar touch-pan-x cursor-grab active:cursor-grabbing">
                `;
                data.relatedProducts.forEach(rp => {
                    const rpImg = rp.primary_image || `https://placehold.co/400?text=${encodeURIComponent(rp.name)}`;
                    const rpDisplay = parseFloat(rp.price || 0);
                    const rpBase = parseFloat(rp.base_price || 0);
                    const rpMoq = parseInt(rp.moq) || 1;
                    const rpDisplayStr = formatJsPrice(rpDisplay);
                    const rpBaseStr = formatJsPrice(rpBase);
                    const discPct = (rpBase && rpBase > rpDisplay) ? Math.round(((rpBase - rpDisplay) / rpBase) * 100) : 0;
                    const discHtml = (discPct > 0) ? `
                        <span class="product-orig-price text-[0.65rem] md:text-xs font-bold text-gray-400 line-through">₹${rpBaseStr}</span>
                    ` : '';
                    const moqHtml = rpMoq >= 1 ? `
                        <p class="product-moq-row text-[10px] sm:text-xs font-semibold mb-2 flex items-center gap-1 text-[#F25996]">
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Min. Order: ${rpMoq} Pcs</span>
                        </p>` : '';
                    relHtml += `
                        <div class="product-card shrink-0 snap-start rounded-2xl overflow-hidden border border-gray-100 flex flex-col relative group bg-white shadow-xs h-full" style="width: calc(50% - 6px) !important; min-width: calc(50% - 6px) !important; max-width: calc(50% - 6px) !important; flex: 0 0 calc(50% - 6px) !important;">
                            <button onclick="toggleWishlist(${rp.id}, this); event.stopPropagation();" data-wishlist-btn-id="${rp.id}" class="wishlist-btn absolute top-1.5 right-1.5 w-6 h-6 hover:opacity-80 transition-opacity rounded-full flex items-center justify-center z-10 shadow-sm bg-white" title="Wishlist">
                                <svg class="w-3 h-3 text-gray-400 fill-none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </button>
                            <a href="${baseUrl}/product?id=${rp.id}" class="product-image-wrap block overflow-hidden shrink-0 bg-gray-100 h-28 sm:h-32 relative">
                                ${discPct > 0 ? renderDiscountStarburstJs(discPct, 'transition-transform duration-300 group-hover:scale-105', 'dw_' + rp.id) : ''}
                                <img src="${rpImg}" alt="${rp.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                            <div class="product-card-body p-2 sm:p-2.5 flex flex-col flex-1 bg-white relative z-10">
                                <a href="${baseUrl}/product?id=${rp.id}" class="product-link block mb-1">
                                    <h4 class="product-name text-[11px] sm:text-xs font-bold line-clamp-2 leading-tight text-gray-900 hover:text-[#F25996] transition-colors">${rp.name}</h4>
                                </a>
                                <div class="mt-auto flex flex-col justify-end">
                                    <div class="product-price-row flex items-baseline gap-1 flex-wrap mb-1">
                                        <span class="product-price text-xs sm:text-sm font-black tracking-tight text-[#F25996]">₹${rpDisplayStr}</span>
                                        ${discHtml}
                                    </div>
                                    ${moqHtml}
                                    <button type="button" onclick="quickAddToCart(${rp.id}, ${rpMoq}, this, event)" class="product-btn w-full py-1.5 px-2 font-bold text-[10px] sm:text-xs transition-all flex items-center justify-center gap-1 cursor-pointer rounded-lg text-white bg-[#F25996] hover:bg-[#db3e7c] active:scale-[0.98]">
                                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                relHtml += `</div></div>`;
                relatedContainer.innerHTML = relHtml;
                relatedContainer.classList.remove('hidden');
                if (window.initTrackScroll) window.initTrackScroll('drawer-related-track');
            } else if (relatedContainer) {
                relatedContainer.innerHTML = '';
                relatedContainer.classList.add('hidden');
            }

            if (checkoutBtn) {
                if (hasAnyError || data.items.length === 0) {
                    checkoutBtn.classList.add('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
                } else {
                    checkoutBtn.classList.remove('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
                }
            }
        }
    </script>

    <!-- Wishlist Offcanvas Drawer -->
    <div id="wishlist-drawer-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300 opacity-0" onclick="closeWishlistDrawer()"></div>
    
    <div id="wishlist-drawer" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-[100] transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col">
        <!-- Header (Exact Match to Shopping Cart Header) -->
        <div class="px-3.5 sm:px-4 md:px-5 py-2.5 sm:py-3.5 md:py-4 border-b border-pink-100 flex items-center justify-between flex-shrink-0 bg-white">
            <h2 class="text-sm sm:text-base md:text-base font-bold text-[#F25996] flex items-center space-x-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#F25996]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                <span id="wishlist-drawer-title">Your Wishlist</span>
            </h2>
            <button onclick="closeWishlistDrawer()" class="text-gray-400 hover:text-[#F25996] transition-colors p-1 rounded-full hover:bg-pink-50" aria-label="Close wishlist">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto px-6 py-6 bg-gray-50/50">
            <div id="wishlist-drawer-items" class="space-y-4 h-full">
                <!-- Items injected via JS -->
                <div class="flex flex-col items-center justify-center h-full text-center space-y-4 py-16">
                    <svg class="w-16 h-16 text-[#F25996]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <p class="text-lg font-bold text-[#F25996]">Your wishlist is empty.</p>
                    <a href="<?= BASE_URL ?>/catalog" class="text-xs font-bold text-[#F25996] bg-pink-50 hover:bg-pink-100 px-4 py-2 rounded-xl transition-colors">Browse Products</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const wishlistDrawer = document.getElementById('wishlist-drawer');
        const wishlistBackdrop = document.getElementById('wishlist-drawer-backdrop');
        const wishlistItemsContainer = document.getElementById('wishlist-drawer-items');

        function openWishlistDrawer() {
            wishlistBackdrop.classList.remove('hidden');
            const waBtn = document.getElementById('floating-whatsapp-btn');
            if (waBtn) waBtn.classList.add('hidden');
            setTimeout(() => {
                wishlistBackdrop.classList.remove('opacity-0');
                wishlistDrawer.classList.remove('translate-x-full');
            }, 10);
            fetchWishlist();
        }

        function closeWishlistDrawer() {
            wishlistBackdrop.classList.add('opacity-0');
            wishlistDrawer.classList.add('translate-x-full');
            const waBtn = document.getElementById('floating-whatsapp-btn');
            if (waBtn && !window.location.pathname.endsWith('/cart')) {
                waBtn.classList.remove('hidden');
            }
            setTimeout(() => {
                wishlistBackdrop.classList.add('hidden');
            }, 300);
        }

        function fetchWishlist() {
            fetch(baseUrl + '/wishlist/ajax-get')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    renderWishlist(data);
                    syncWishlistIcons(data.items);
                }
            });
        }

        function syncWishlistIcons(wishlistItems) {
            const wishlistIds = wishlistItems.map(item => String(item.product_id));
            const btns = document.querySelectorAll('.wishlist-btn');
            btns.forEach(btn => {
                const pid = String(btn.dataset.wishlistBtnId);
                const svg = btn.querySelector('svg');
                if (!svg) return;
                
                if (wishlistIds.includes(pid)) {
                    svg.classList.remove('text-gray-400', 'fill-transparent');
                    svg.classList.add('text-red-500', 'fill-current');
                } else {
                    svg.classList.remove('text-red-500', 'fill-current');
                    svg.classList.add('text-gray-400', 'fill-transparent');
                }
            });
        }

        function removeWishlistItem(productId) {
            fetch(baseUrl + '/wishlist/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    fetchWishlist();
                    fetchWishlistCount();
                }
            });
        }

        function toggleAllWishlistItems(checkbox) {
            const checkboxes = document.querySelectorAll('.wishlist-item-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateWishlistSelection();
        }

        function updateWishlistSelection() {
            const checkboxes = document.querySelectorAll('.wishlist-item-checkbox');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            
            const selectAllBtn = document.getElementById('wishlist-select-all');
            if (selectAllBtn) {
                selectAllBtn.checked = checked.length > 0 && checked.length === checkboxes.length;
            }

            const delBtn = document.getElementById('wishlist-del-selected');
            const addBtn = document.getElementById('wishlist-add-selected');
            if (delBtn) delBtn.disabled = checked.length === 0;
            if (addBtn) addBtn.disabled = checked.length === 0;
        }
        
        async function deleteSelectedWishlistItems() {
            const checked = Array.from(document.querySelectorAll('.wishlist-item-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) return;
            
            for (const productId of checked) {
                await fetch(baseUrl + '/wishlist/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                });
            }
            fetchWishlist();
            fetchWishlistCount();
        }

        async function addSelectedWishlistToCart() {
            const checked = Array.from(document.querySelectorAll('.wishlist-item-checkbox:checked'));
            if (checked.length === 0) return;
            
            for (const cb of checked) {
                const productId = cb.value;
                const moq = cb.dataset.moq || 1;
                
                await fetch(baseUrl + '/cart/ajax-add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: moq, from_wishlist: 1 })
                });
            }
            fetchCart();
            openCartDrawer(true);
            closeWishlistDrawer();
        }

        function renderWishlist(data) {
            // Update wishlist badge
            if (wishlistBadge) {
                const count = data.items.length;
                wishlistBadge.innerText = count;
                if (count > 0) {
                    wishlistBadge.classList.remove('hidden');
                } else {
                    wishlistBadge.classList.add('hidden');
                }
            }
            if (data.items.length === 0) {
                wishlistItemsContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-center space-y-4 py-16">
                        <svg class="w-16 h-16 text-[#F25996]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <p class="text-lg font-bold text-[#F25996]">Your wishlist is empty.</p>
                        <a href="${baseUrl}/catalog" class="text-xs font-bold text-[#F25996] bg-pink-50 hover:bg-pink-100 px-4 py-2 rounded-xl transition-colors">Browse Products</a>
                    </div>`;
            } else {
                let html = `
                    <div class="flex items-center justify-between gap-2 mb-3 bg-white px-3 py-2 rounded-xl shadow-sm border border-pink-100/60 sticky top-0 z-10 whitespace-nowrap flex-nowrap">
                        <label class="flex items-center gap-1.5 cursor-pointer select-none shrink-0 whitespace-nowrap">
                            <input type="checkbox" id="wishlist-select-all" onchange="toggleAllWishlistItems(this)" class="w-3.5 h-3.5 text-[#F25996] rounded-[4px] border-gray-300 focus:ring-0 cursor-pointer accent-[#F25996]">
                            <span class="text-xs font-bold text-gray-700 whitespace-nowrap">Select All</span>
                        </label>
                        <div class="flex items-center gap-2 shrink-0 whitespace-nowrap">
                            <button onclick="deleteSelectedWishlistItems()" class="text-xs font-bold text-red-500 hover:text-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors whitespace-nowrap" id="wishlist-del-selected" disabled>Remove Selected</button>
                            <span class="text-gray-200">|</span>
                            <button onclick="addSelectedWishlistToCart()" class="text-xs font-bold text-[#F25996] hover:text-[#d8407d] disabled:opacity-40 disabled:cursor-not-allowed transition-colors flex items-center gap-1 whitespace-nowrap" id="wishlist-add-selected" disabled>Add to Cart</button>
                        </div>
                    </div>
                `;
                data.items.forEach(item => {
                    let imgSrc = item.primary_image ? item.primary_image : 'https://placehold.co/100x100?text=No+Img';
                    const priceFormatted = item.price || ('₹' + (item.price_raw ? formatJsPrice(item.price_raw) : '0'));
                    const origPriceHtml = item.original_price ? `<span class="text-[10px] sm:text-[11px] text-gray-400 line-through font-normal">${item.original_price}</span>` : '';
                    const discPctHtml = (item.discount_pct && item.discount_pct > 0) ? `<span class="text-[8px] sm:text-[9px] font-bold text-[#F25996] bg-pink-50 border border-pink-100 px-1 py-0.2 rounded">${item.discount_pct}% OFF</span>` : '';

                    html += `
                        <div class="bg-white border border-gray-100/90 rounded-[22px] p-2.5 sm:p-3 hover:shadow-md transition-all duration-300 group flex items-start gap-2.5 sm:gap-3 relative shadow-xs mb-2 sm:mb-2.5" id="wishlist-drawer-item-${item.product_id}">
                            <!-- Left Selection Checkbox -->
                            <div class="pt-1 flex-shrink-0">
                                <input type="checkbox" class="wishlist-item-checkbox w-3.5 h-3.5 text-[#F25996] rounded-[4px] border border-gray-300 focus:ring-0 cursor-pointer accent-[#F25996]" value="${item.product_id}" onchange="updateWishlistSelection()" data-moq="${item.moq || 1}">
                            </div>

                            <!-- Product Thumbnail Image (88px x 88px rounded-[16px]) -->
                            <a href="${baseUrl}/product?id=${item.product_id}" class="rounded-[16px] overflow-hidden flex-shrink-0 relative group/img block" style="width: 88px; height: 88px; min-width: 88px; min-height: 88px;">
                                <img src="${imgSrc}" alt="${item.name}" class="rounded-[16px] group-hover/img:scale-105 transition-transform duration-300 block" style="width: 100%; height: 100%; object-fit: cover;">
                            </a>

                            <!-- Center Details & Right Actions (Exact Wishlist Layout) -->
                            <div class="flex-1 min-w-0 flex items-start justify-between gap-2 self-stretch">
                                <!-- Center Info Column -->
                                <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
                                    <div>
                                        <!-- Title -->
                                        <a href="${baseUrl}/product?id=${item.product_id}" class="text-xs sm:text-[13px] font-bold text-gray-900 leading-tight hover:text-[#F25996] transition-colors line-clamp-1" title="${item.name}">
                                            ${item.name}
                                        </a>

                                        <!-- Variant Text -->
                                        <div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5 truncate">
                                            ${item.variant_text || ''}
                                        </div>

                                        <!-- Price Row -->
                                        <div class="flex items-baseline gap-1 mt-0.5 flex-wrap">
                                            <span class="text-xs sm:text-sm font-bold text-[#F25996]">${priceFormatted}</span>
                                            ${origPriceHtml}
                                            <span class="text-[10px] sm:text-[11px] text-gray-400 font-normal">/ Unit</span>
                                            ${discPctHtml}
                                        </div>

                                        <!-- MOQ Line -->
                                        <div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5">
                                            Min. Order: ${item.moq || 1} Pcs
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Action Column: Heart Remove on Top, Pink Add to Cart on Bottom -->
                                <div class="flex flex-col justify-between items-end self-stretch shrink-0 pl-1">
                                    <!-- Top: Heart Remove Button -->
                                    <button onclick="removeWishlistItem(${item.product_id})" class="text-[#f43f5e] hover:scale-110 active:scale-95 transition-transform p-0.5 -mr-1" title="Remove from Wishlist">
                                        <svg class="w-4 h-4 text-[#f43f5e] fill-[#f43f5e]" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                        </svg>
                                    </button>

                                    <!-- Bottom: Pink Add to Cart Icon Button -->
                                    <button type="button" onclick="addWishlistItemToCart(${item.product_id}, ${item.moq || 1}, this, event)" class="w-7 h-7 sm:w-8 sm:h-8 bg-[#F25996] hover:bg-[#d8407d] text-white rounded-full transition-all shadow-xs shadow-pink-500/25 flex items-center justify-center active:scale-90 mt-auto" title="Add to Cart">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                wishlistItemsContainer.innerHTML = html;
            }
        }
    </script>
    <?php else: ?>
    <script>
        function openCartDrawer() {
            window.location.href = '<?= BASE_URL ?>/login';
        }
        function openWishlistDrawer() {
            window.location.href = '<?= BASE_URL ?>/login';
        }
    </script>
    <?php endif; ?>

    <!-- Live Search Modal -->
    <div id="searchDrawer" class="fixed inset-0 z-[100] hidden items-start justify-center pt-24 pb-4 px-4 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0 duration-300" onclick="closeSearchModal(event)">
        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh] transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            <!-- Header / Search Bar -->
            <form id="searchDrawerForm" action="<?= BASE_URL ?>/catalog" method="GET" class="flex items-center w-full px-4 py-3 sm:py-4 border-b border-gray-100 shrink-0">
                <button type="submit" class="text-gray-400 hover:text-[#F25996] focus:outline-none transition-colors shrink-0 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
                <input type="text" id="liveSearchInput" name="q" class="w-full bg-transparent border-0 focus:ring-0 text-lg sm:text-xl text-gray-900 placeholder-gray-400 pl-4 py-2 outline-none" placeholder="Search by product name, category, or SKU..." autocomplete="off" autofocus>
                <button type="button" class="shrink-0 p-2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" onclick="closeSearchModal(event)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </form>

            <!-- Results Container -->
            <div class="overflow-y-auto p-4 md:p-6 bg-gray-50/40">
                <!-- Loading Indicator -->
                <div id="searchLoading" class="hidden py-8 text-center text-gray-500">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-t-[#F25996] border-r-transparent border-b-[#F25996] border-l-transparent mb-2"></div>
                    <p class="text-sm font-medium text-gray-500">Searching...</p>
                </div>

                <!-- Categories Section -->
                <div id="searchCategoriesSection" class="hidden mb-6">
                    <h3 class="text-xs font-bold text-gray-400 tracking-wider mb-3 uppercase">Categories</h3>
                    <ul id="searchCategoriesList" class="space-y-3">
                        <!-- Populated via JS -->
                    </ul>
                </div>

                <!-- Products Section -->
                <div id="searchProductsSection" class="hidden border-t border-gray-100 pt-6">
                    <h3 class="text-xs font-bold text-gray-400 tracking-wider mb-3 uppercase">Products</h3>
                    <ul id="searchProductsList" class="space-y-3">
                        <!-- Populated via JS -->
                    </ul>
                </div>

                <!-- Empty State -->
                <div id="searchEmptyState" class="hidden py-8 text-center text-gray-500">
                    No results found. Try a different search term.
                </div>

                <!-- View All Link -->
                <div id="searchViewAll" class="hidden text-center border-t border-gray-100 pt-4 mt-6">
                    <a href="#" id="searchViewAllLink" class="text-sm font-bold text-[#F25996] hover:text-[#d8407d] hover:underline">View All Results</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const searchDrawer = document.getElementById('searchDrawer');
        const searchDrawerInner = searchDrawer.querySelector('div.relative');
        const liveSearchInput = document.getElementById('liveSearchInput');
        
        const searchLoading = document.getElementById('searchLoading');
        const searchCategoriesSection = document.getElementById('searchCategoriesSection');
        const searchCategoriesList = document.getElementById('searchCategoriesList');
        const searchProductsSection = document.getElementById('searchProductsSection');
        const searchProductsList = document.getElementById('searchProductsList');
        const searchEmptyState = document.getElementById('searchEmptyState');
        const searchViewAll = document.getElementById('searchViewAll');
        const searchViewAllLink = document.getElementById('searchViewAllLink');

        let searchTimeout = null;

        function openSearchModal(e) {
            if (e) e.preventDefault();
            searchDrawer.classList.remove('hidden');
            searchDrawer.classList.add('flex');
            
            requestAnimationFrame(() => {
                searchDrawer.classList.remove('opacity-0');
                searchDrawerInner.classList.remove('scale-95');
                liveSearchInput.focus();
            });
        }

        function closeSearchModal(e) {
            if (e) e.preventDefault();
            searchDrawer.classList.add('opacity-0');
            searchDrawerInner.classList.add('scale-95');
            
            setTimeout(() => {
                searchDrawer.classList.add('hidden');
                searchDrawer.classList.remove('flex');
                liveSearchInput.value = '';
                resetSearchUI();
            }, 300);
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !searchDrawer.classList.contains('hidden')) {
                closeSearchModal();
            }
        });

        function resetSearchUI() {
            searchCategoriesSection.classList.add('hidden');
            searchProductsSection.classList.add('hidden');
            searchEmptyState.classList.add('hidden');
            searchViewAll.classList.add('hidden');
            searchCategoriesList.innerHTML = '';
            searchProductsList.innerHTML = '';
        }

        liveSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                resetSearchUI();
                searchLoading.classList.add('hidden');
                return;
            }

            searchLoading.classList.remove('hidden');
            resetSearchUI();

            searchTimeout = setTimeout(() => {
                fetch(`<?= BASE_URL ?>/api/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchLoading.classList.add('hidden');
                        renderSearchResults(query, data);
                    })
                    .catch(err => {
                        console.error("Search error:", err);
                        searchLoading.classList.add('hidden');
                    });
            }, 300); // 300ms debounce
        });

        function renderSearchResults(query, data) {
            let hasResults = false;

            // Render Categories
            if (data.categories && data.categories.length > 0) {
                hasResults = true;
                searchCategoriesSection.classList.remove('hidden');
                let html = '';
                data.categories.forEach(cat => {
                    html += `
                        <li>
                            <a href="<?= BASE_URL ?>/catalog?q=${encodeURIComponent(cat.name)}" class="flex items-center p-2 -mx-2 rounded-xl group hover:bg-pink-50/50 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center mr-4 group-hover:bg-pink-100/70 transition-colors">
                                    <svg class="w-5 h-5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <span class="text-base font-semibold text-gray-900 group-hover:text-[#F25996] transition-colors">${cat.name}</span>
                            </a>
                        </li>
                    `;
                });
                searchCategoriesList.innerHTML = html;
            }

            // Render Products
            if (data.products && data.products.length > 0) {
                hasResults = true;
                searchProductsSection.classList.remove('hidden');
                let html = '';
                data.products.forEach(prod => {
                    let priceHtml = '';
                    const formatNum = (v) => {
                        let n = parseFloat(v || 0);
                        return Number.isInteger(n) ? n.toLocaleString() : parseFloat(n.toFixed(2)).toLocaleString();
                    };
                    if (prod.base_price) {
                        if (prod.discount_price && parseFloat(prod.discount_price) > 0 && parseFloat(prod.discount_price) < parseFloat(prod.base_price)) {
                            priceHtml = `
                                <div class="text-right shrink-0">
                                    <span class="text-[11px] text-gray-400 line-through block">₹${formatNum(prod.base_price)}</span>
                                    <span class="font-extrabold text-[#F25996] text-xs sm:text-sm block">₹${formatNum(prod.discount_price)}</span>
                                </div>`;
                        } else {
                            priceHtml = `<div class="font-extrabold text-[#F25996] text-xs sm:text-sm shrink-0">₹${formatNum(prod.base_price)}</div>`;
                        }
                    }
                    const imageSrc = prod.primary_image ? prod.primary_image : 'https://placehold.co/100x100?text=No+Image';
                    
                    html += `
                        <li>
                            <a href="<?= BASE_URL ?>/product?id=${prod.id}" class="flex items-center group p-2.5 -mx-2 rounded-xl hover:bg-pink-50/50 transition-colors">
                                <img src="${imageSrc}" alt="${prod.name}" class="w-14 h-14 object-cover rounded-lg mr-3.5 shadow-xs border border-gray-100" loading="lazy" decoding="async" width="56" height="56">
                                <div class="flex-1 min-w-0 pr-2">
                                    <h4 class="font-bold text-gray-900 text-sm sm:text-base group-hover:text-[#F25996] transition-colors truncate">${prod.name}</h4>
                                    <p class="text-gray-400 text-xs truncate">${prod.category_name || ''}</p>
                                </div>
                                ${priceHtml}
                            </a>
                        </li>
                    `;
                });
                searchProductsList.innerHTML = html;
            }

            if (!hasResults) {
                searchEmptyState.classList.remove('hidden');
            } else {
                searchViewAll.classList.remove('hidden');
                searchViewAllLink.href = `<?= BASE_URL ?>/catalog?q=${encodeURIComponent(query)}`;
            }
        }

        // Global Wishlist Toggle Handler
        function toggleWishlist(productId, btnElement) {
            if (window.event) {
                window.event.stopPropagation();
            }
            fetch('<?= BASE_URL ?>/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isAdded = data.action === 'added';
                    const targetBtns = document.querySelectorAll(`[data-wishlist-btn-id="${productId}"], button[onclick*="toggleWishlist(${productId}"]`);
                    
                    const btnList = btnElement ? [btnElement, ...Array.from(targetBtns)] : Array.from(targetBtns);
                    
                    btnList.forEach(btn => {
                        if (!btn) return;
                        if (isAdded) {
                            btn.classList.add('is-active');
                        } else {
                            btn.classList.remove('is-active');
                        }
                        const svg = btn.querySelector('svg');
                        if (svg) {
                            if (isAdded) {
                                svg.classList.remove('text-gray-400', 'text-gray-300', 'text-white', 'fill-transparent', 'fill-none');
                                svg.classList.add('text-[#F25996]', 'fill-[#F25996]', 'is-active');
                                svg.setAttribute('fill', '#F25996');
                                svg.style.setProperty('fill', '#F25996', 'important');
                                svg.style.setProperty('color', '#F25996', 'important');
                                svg.style.setProperty('stroke', '#F25996', 'important');
                            } else {
                                svg.classList.remove('text-[#F25996]', 'fill-[#F25996]', 'text-red-500', 'fill-current', 'is-active');
                                svg.classList.add('text-gray-400', 'fill-none');
                                svg.setAttribute('fill', 'none');
                                svg.style.removeProperty('fill');
                                svg.style.removeProperty('color');
                                svg.style.removeProperty('stroke');
                            }
                        }
                    });

                    if (isAdded && typeof openWishlistDrawer === 'function') {
                        openWishlistDrawer();
                    }
                    if (typeof fetchWishlistCount === 'function') {
                        fetchWishlistCount();
                    }
                    if (typeof fetchWishlist === 'function') {
                        fetchWishlist();
                    }
                } else {
                    if (data.message && data.message.toLowerCase().includes('login')) {
                        window.location.href = '<?= BASE_URL ?>/login';
                    } else {
                        alert(data.message || 'Error updating wishlist');
                    }
                }
            })
            .catch(error => {
                console.error('Wishlist toggle error:', error);
            });
        }
    </script>
    
    <?php
    if ($globalFooter):
        $contentData = json_decode($globalFooter['content_data'], true) ?? [];
        echo '<div class="cms-section transition-all duration-300" data-cms-id="' . htmlspecialchars($globalFooter['id']) . '" style="display: ' . ($globalFooter['is_active'] ? 'block' : 'none') . ';">';
        include __DIR__ . '/../storefront/components/footer.php';
        echo '</div>';
    else:
    ?>
    <footer class="bg-gray-900 border-t mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> GRM Garments B2B. All rights reserved.</p>
            <div class="mt-4 md:mt-0 flex space-x-6">
                <a href="#" class="text-sm text-gray-400 hover:text-white">Privacy Policy</a>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Terms of Service</a>
            </div>
        </div>
    </footer>
    <?php endif; ?>


    <?php if ($isPreview): ?>
    <script>
        // Preserve preview state across navigation within the iframe
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && link.href.startsWith(window.location.origin) && !link.href.includes('preview=1')) {
                const url = new URL(link.href);
                url.searchParams.set('preview', '1');
                link.href = url.toString();
            }
        });
    </script>
    <?php endif; ?>
    <?php
    $whatsappNumber = '';
    if (isset($globalFooter) && $globalFooter) {
        $footerData = json_decode($globalFooter['content_data'], true) ?? [];
        $whatsappNumber = $footerData['contact']['phone'] ?? '';
    }
    // Clean up number
    $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
    if (empty($whatsappNumber)) {
        $whatsappNumber = '919000000000'; // Default placeholder
    }
    $isCartPage = (isset($_SERVER['REQUEST_URI']) && preg_match('#/cart(\?|/|$)#', $_SERVER['REQUEST_URI']));
    ?>
    <!-- Floating WhatsApp Button (Hidden on Shopping Cart page to avoid blocking checkout bar; active on all other pages) -->
    <style>
        /* Floating WhatsApp Button - Responsive Sizing & Positioning */
        /* Mobile View (<768px): A little bigger and positioned lower down */
        @media (max-width: 767px) {
            #floating-whatsapp-btn {
                width: 46px !important;
                height: 46px !important;
                bottom: 16px !important;
                right: 16px !important;
                box-shadow: 0 4px 14px rgba(37, 211, 102, 0.38) !important;
            }
            #floating-whatsapp-btn svg {
                width: 25px !important;
                height: 25px !important;
            }
        }
        /* Tablet View (768px - 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
            #floating-whatsapp-btn {
                width: 52px !important;
                height: 52px !important;
                box-shadow: 0 4px 14px rgba(37, 211, 102, 0.38) !important;
            }
            #floating-whatsapp-btn svg {
                width: 28px !important;
                height: 28px !important;
            }
        }
        /* Desktop View (>= 1024px) */
        @media (min-width: 1024px) {
            #floating-whatsapp-btn {
                width: 58px !important;
                height: 58px !important;
                box-shadow: 0 6px 20px rgba(37, 211, 102, 0.42) !important;
            }
            #floating-whatsapp-btn svg {
                width: 32px !important;
                height: 32px !important;
            }
        }

        /* Bouncing Scroll-to-Top Button - Same Style as WhatsApp Button */
        #scroll-to-top-btn {
            position: fixed;
            z-index: 998;
            background-color: #e8015b !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            border: none;
            outline: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
        }
        #scroll-to-top-btn.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            animation: bounceButtonUp 1.6s infinite ease-in-out !important;
        }
        #scroll-to-top-btn.is-visible:hover {
            animation-play-state: paused;
            transform: translateY(-4px) scale(1.1);
        }
        @keyframes bounceButtonUp {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        /* Mobile View (<768px): position above WhatsApp button */
        @media (max-width: 767px) {
            #scroll-to-top-btn {
                width: 42px !important;
                height: 42px !important;
                bottom: 70px !important;
                right: 18px !important;
                box-shadow: 0 4px 14px rgba(232, 1, 91, 0.38) !important;
            }
            #scroll-to-top-btn svg {
                width: 22px !important;
                height: 22px !important;
            }
        }
        /* Tablet View (768px - 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
            #scroll-to-top-btn {
                width: 46px !important;
                height: 46px !important;
                bottom: 76px !important;
                right: 19px !important;
                box-shadow: 0 4px 14px rgba(232, 1, 91, 0.38) !important;
            }
            #scroll-to-top-btn svg {
                width: 24px !important;
                height: 24px !important;
            }
        }
        /* Desktop View (>= 1024px) */
        @media (min-width: 1024px) {
            #scroll-to-top-btn {
                width: 50px !important;
                height: 50px !important;
                bottom: 88px !important;
                right: 24px !important;
                box-shadow: 0 6px 20px rgba(232, 1, 91, 0.42) !important;
            }
            #scroll-to-top-btn svg {
                width: 26px !important;
                height: 26px !important;
            }
        }
    </style>
    <a id="floating-whatsapp-btn" href="https://api.whatsapp.com/send/?phone=<?= $whatsappNumber ?>&text=Hello%20GRM%20B2B,%20I%20have%20an%20inquiry%20from%20<?= urlencode(BASE_URL) ?>" target="_blank" rel="noopener noreferrer" class="<?= $isCartPage ? 'hidden' : '' ?> fixed bottom-4 right-4 md:bottom-4 md:right-4 lg:bottom-5 lg:right-5 z-[999] bg-[#25D366] text-white rounded-full shadow-md hover:scale-110 transition-transform duration-300 flex items-center justify-center group" aria-label="Chat on WhatsApp">
        <svg class="w-5 h-5 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <!-- Optional Tooltip -->
        <div class="absolute right-full mr-3 bg-white text-gray-800 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 whitespace-nowrap">
            Chat with us
        </div>
    </a>

    <!-- Bouncing Scroll to Top Button (Same style as WhatsApp Button) -->
    <button id="scroll-to-top-btn" type="button" class="fixed bg-[#e8015b] text-white rounded-full shadow-md hover:scale-110 transition-transform duration-300 flex items-center justify-center group" aria-label="Scroll to top" title="Scroll to top">
        <svg class="scroll-arrow-icon" fill="currentColor" viewBox="200 175 240 285" xmlns="http://www.w3.org/2000/svg">
            <path d="M337 199L417 279C426.4 288.4 426.4 303.6 417 312.9C407.6 322.2 392.4 322.3 383.1 312.9L344.1 273.9L344.1 424C344.1 437.3 333.4 448 320.1 448C306.8 448 296.1 437.3 296.1 424L296.1 273.9L257.1 312.9C247.7 322.3 232.5 322.3 223.2 312.9C213.9 303.5 213.8 288.3 223.2 279L303.2 199C312.6 189.6 327.8 189.6 337.1 199z"/>
        </svg>
        <!-- Optional Tooltip -->
        <div class="absolute right-full mr-3 bg-white text-gray-800 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 whitespace-nowrap pointer-events-none">
            Scroll to top
        </div>
    </button>

    <script>
        (function() {
            var scrollBtn = document.getElementById('scroll-to-top-btn');
            if (!scrollBtn) return;

            var handleScroll = function() {
                if (window.scrollY > 250) {
                    scrollBtn.classList.add('is-visible');
                } else {
                    scrollBtn.classList.remove('is-visible');
                }
            };

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            scrollBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        })();
    </script>
</body>
</html>
