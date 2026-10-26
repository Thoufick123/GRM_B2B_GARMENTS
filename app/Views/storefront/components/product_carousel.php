<?php
// Extracted from $contentData which is passed in via home.php
$id = uniqid('carousel_');
$title = $contentData['title'] ?? '';
$sort_order = $contentData['sort_order'] ?? 0;
$mobile_count = max(1, (int)($contentData['mobile_count'] ?? 2));
$desktop_count = (int)($contentData['desktop_count'] ?? 4);
if ($desktop_count < 2) {
    $desktop_count = 4;
}
$cta_text = $contentData['cta_text'] ?? '';
$cta_url = $contentData['cta_url'] ?? '';
$cta_position = $contentData['cta_position'] ?? 'Right';
$show_arrows = $contentData['show_arrows'] ?? true;
$show_dots = $contentData['show_dots'] ?? true;
$product_ids = $contentData['product_ids'] ?? [];
$theme = $contentData['theme'] ?? ['type' => 'solid', 'value' => '#ffffff'];

$title_color = $contentData['title_color'] ?? '#111827';
$text_color = $contentData['text_color'] ?? '#111827';
$price_color = $contentData['price_color'] ?? '#111827';
$button_bg_color = $contentData['button_bg_color'] ?? '#4a3628';
$button_text_color = $contentData['button_text_color'] ?? '#ffffff';
$arrow_color = !empty($contentData['arrow_color']) && $contentData['arrow_color'] !== '#ffffff' && $contentData['arrow_color'] !== '#111827' && $contentData['arrow_color'] !== '#1f2937' ? $contentData['arrow_color'] : '#F25996';
$arrow_bg_color = $contentData['arrow_bg_color'] ?? '#ffffff';
$dot_color = $contentData['dot_color'] ?? '#8c6a4f';
$wishlist_color = $contentData['wishlist_color'] ?? 'rgba(17, 24, 39, 0.8)';
$image_fit = $contentData['image_fit'] ?? 'cover';

$objectFitClass = 'object-cover';
if ($image_fit === 'contain') $objectFitClass = 'object-contain';
elseif ($image_fit === 'stretch') $objectFitClass = 'object-fill';

// Background styling
$bgStyle = '';
if (!empty($theme['value'])) {
    $bgStyle = 'background: ' . $theme['value'] . ';';
}

// Find actual product data from the globally injected $products array
$carouselVendorId = $contentData['vendor_id'] ?? 'all';
$product_ids_int = array_map('intval', (array)($product_ids ?? []));

$carouselProducts = [];
$carouselProdCacheKey = 'cms_p_carousel_' . md5(json_encode($product_ids_int) . '_' . $carouselVendorId);
$cachedCarouselProds = (class_exists('\Core\Cache') && empty($_GET['preview'])) ? \Core\Cache::get($carouselProdCacheKey) : null;

if ($cachedCarouselProds !== null) {
    $carouselProducts = $cachedCarouselProds;
} else {
    if (isset($products) && is_array($products)) {
        if (!empty($product_ids_int)) {
            // If specific products were checked/selected, map them in exact selected order
            $productMap = [];
            foreach ($products as $p) {
                $productMap[(int)$p['id']] = $p;
            }

            // Fetch any missing products (such as customizable products or specific IDs)
            $missingIds = array_diff($product_ids_int, array_keys($productMap));
            if (!empty($missingIds)) {
                $db = \Core\Database::getInstance();
                $inClause = implode(',', array_map('intval', $missingIds));
                $stmt = $db->query("
                    SELECT p.*, c.name AS category_name, c.moq AS category_moq, c.sgst AS category_sgst, c.cgst AS category_cgst,
                           vp.store_name AS vendor_store_name, vp.company_name AS vendor_company_name,
                           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
                    FROM products p
                    JOIN categories c ON p.category_id = c.id AND c.status = 'active'
                    LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
                    WHERE p.id IN ($inClause) AND p.status = 'active' AND p.category_id IS NOT NULL
                ");
                if ($stmt) {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($fetched as $fp) {
                        $productMap[(int)$fp['id']] = $fp;
                    }
                }
            }

            foreach ($product_ids_int as $pId) {
                if (isset($productMap[$pId])) {
                    $carouselProducts[] = $productMap[$pId];
                }
            }
        } elseif ($carouselVendorId !== 'all' && $carouselVendorId !== '') {
            // If vendor filter was chosen, show all vendor products
            foreach ($products as $p) {
                $pVendorId = (int)($p['vendor_id'] ?? $p['user_id'] ?? 0);
                if ($carouselVendorId === 'in_house' && $pVendorId === 0) {
                    $carouselProducts[] = $p;
                } elseif ((int)$carouselVendorId === $pVendorId) {
                    $carouselProducts[] = $p;
                }
            }
        }
    }

    // Enrich customizable products with images, sizes, and fabric_customizations info
    $customProdIds = [];
    foreach ($carouselProducts as $cp) {
        if (!empty($cp['is_customizable'])) {
            $customProdIds[] = (int)$cp['id'];
        }
    }
    if (!empty($customProdIds)) {
        try {
            $db = \Core\Database::getInstance();
            $cpidList = implode(',', array_unique($customProdIds));
            
            $fcStmt = $db->query("SELECT fabric_id, id as customization_id, garment_type, minimum_pieces as custom_moq FROM fabric_customizations WHERE fabric_id IN ($cpidList) AND customization_enabled = 1 AND status = 'active'");
            $fcData = [];
            $customProgIds = [];
            if ($fcStmt) {
                while ($fcr = $fcStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $fcData[(int)$fcr['fabric_id']] = $fcr;
                    $customProgIds[] = (int)$fcr['customization_id'];
                }
            }

            $cimgStmt = $db->query("SELECT product_id, image_path FROM product_images WHERE product_id IN ($cpidList) ORDER BY is_primary DESC, sort_order ASC, id ASC");
            $customImgs = [];
            if ($cimgStmt) {
                while ($cir = $cimgStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $customImgs[$cir['product_id']][] = $cir['image_path'];
                }
            }

            $customSizes = [];
            if (!empty($customProgIds)) {
                $cprogList = implode(',', array_unique($customProgIds));
                $cszStmt = $db->query("SELECT customization_id, size_name FROM fabric_customization_sizes WHERE customization_id IN ($cprogList) AND enabled = 1 ORDER BY sort_order ASC, id ASC");
                if ($cszStmt) {
                    while ($csz = $cszStmt->fetch(\PDO::FETCH_ASSOC)) {
                        $customSizes[$csz['customization_id']][] = $csz['size_name'];
                    }
                }
            }

            foreach ($carouselProducts as &$cp) {
                if (!empty($cp['is_customizable'])) {
                    $pid = (int)$cp['id'];
                    if (isset($fcData[$pid])) {
                        $cp['garment_type'] = $fcData[$pid]['garment_type'] ?? null;
                        $cp['custom_moq'] = $fcData[$pid]['custom_moq'] ?? null;
                        $custProgId = (int)$fcData[$pid]['customization_id'];
                        $cp['sizes'] = $customSizes[$custProgId] ?? ['3XL', '4XL', 'L', 'M', 'XL', 'XXL'];
                    } else {
                        $cp['sizes'] = ['3XL', '4XL', 'L', 'M', 'XL', 'XXL'];
                    }
                    if (isset($customImgs[$pid])) {
                        $cp['images'] = $customImgs[$pid];
                    }
                }
            }
            unset($cp);
        } catch (\Exception $e) {
            // Fallback gracefully
        }
    }

    // Sync live approved review stats for carousel products
    $allCpIds = array_column($carouselProducts, 'id');
    if (!empty($allCpIds)) {
        try {
            $db = \Core\Database::getInstance();
            $pIdList = implode(',', array_map('intval', $allCpIds));
            $revStats = $db->query("
                SELECT product_id, COUNT(*) as cnt, AVG(rating) as avg_rating 
                FROM reviews 
                WHERE product_id IN ($pIdList) AND is_approved = 1 
                GROUP BY product_id
            ")->fetchAll(\PDO::FETCH_ASSOC);
            $revMap = [];
            foreach ($revStats as $rs) {
                $revMap[$rs['product_id']] = [
                    'num_reviews' => (int)$rs['cnt'],
                    'average_rating' => round((float)$rs['avg_rating'], 2)
                ];
            }
            foreach ($carouselProducts as &$cp) {
                if (isset($revMap[$cp['id']])) {
                    $cp['num_reviews'] = $revMap[$cp['id']]['num_reviews'];
                    $cp['average_rating'] = $revMap[$cp['id']]['average_rating'];
                }
            }
            unset($cp);
        } catch (\Throwable $e) {
            error_log("Carousel review sync error: " . $e->getMessage());
        }
    }

    if (class_exists('\Core\Cache') && !empty($carouselProducts) && empty($_GET['preview'])) {
        \Core\Cache::set($carouselProdCacheKey, $carouselProducts, 3600);
    }
}

$mobileWidth = 100 / max(1, $mobile_count);
$desktopWidth = 100 / max(1, $desktop_count);
$tablet_count = min(3, max(2, (int)floor($desktop_count * 0.75)));
$tabletWidth = 100 / max(1, $tablet_count);
?>

<style>
    #<?= $id ?> .slide-item {
        width: calc(<?= $mobileWidth ?>% - <?= (($mobile_count - 1) * 0.5) / $mobile_count ?>rem);
    }
    #<?= $id ?> .carousel-btn {
        top: 30%; /* Center of product image on mobile so it never covers product titles or prices */
        width: 1.5rem;
        height: 1.5rem;
        color: #F25996 !important;
    }
    #<?= $id ?> .carousel-btn svg {
        width: 0.75rem;
        height: 0.75rem;
        stroke: #F25996 !important;
    }
    #<?= $id ?> .carousel-btn:hover {
        color: #e04481 !important;
    }
    #<?= $id ?> .carousel-btn:hover svg {
        stroke: #e04481 !important;
    }
    #prev_<?= $id ?> { left: -0.25rem; }
    #next_<?= $id ?> { right: -0.25rem; }
    /* Mobile view alignment: minimal gutters so product cards maximize display width */
    #track_container_<?= $id ?> { padding-left: 0.15rem; padding-right: 0.15rem; }
    #inner_<?= $id ?> { gap: 0.5rem !important; }
    #<?= $id ?> .product-rating-row {
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
    }
    
    #<?= $id ?> .slider-dot {
        width: 6px;
        height: 6px;
        background-color: <?= htmlspecialchars($dot_color) ?> !important;
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        padding: 0;
        transition: all 0.3s ease;
        opacity: 0.5;
        display: inline-block;
        vertical-align: middle;
    }
    #<?= $id ?> .slider-dot.active {
        width: 8px;
        height: 8px;
        background-color: <?= htmlspecialchars($dot_color) ?> !important;
        opacity: 1;
        outline: 2px solid <?= htmlspecialchars($dot_color) ?> !important;
        outline-offset: 2px;
    }

    /* Tablet layout (768px to 1023px) */
    @media (min-width: 768px) and (max-width: 1023px) {
        #<?= $id ?> .slide-item {
            width: calc(<?= $tabletWidth ?>% - <?= (($tablet_count - 1) * 0.85) / $tablet_count ?>rem) !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #inner_<?= $id ?> {
            gap: 0.85rem !important;
        }
        #track_container_<?= $id ?> {
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }
        #<?= $id ?> .carousel-btn {
            top: 35% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
        }
        #<?= $id ?> .carousel-btn svg {
            width: 0.85rem !important;
            height: 0.85rem !important;
        }
        #prev_<?= $id ?> { left: -0.25rem !important; }
        #next_<?= $id ?> { right: -0.25rem !important; }
        #<?= $id ?> .product-card {
            border-radius: 1.25rem !important;
            height: 100% !important;
        }
        #<?= $id ?> .product-image-wrap {
            aspect-ratio: 1 / 1 !important;
        }
        #<?= $id ?> .product-card-body {
            padding: 0.75rem 0.65rem 0.85rem 0.65rem !important;
            margin-top: -1.25rem !important;
            border-radius: 1.25rem 1.25rem 0 0 !important;
        }
        #<?= $id ?> .product-name {
            font-size: 0.9375rem !important;
            font-weight: 700 !important;
            line-height: 1.25 !important;
            margin-bottom: 0.35rem !important;
        }
        #<?= $id ?> .product-price {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
        }
        #<?= $id ?> .product-orig-price {
            font-size: 0.75rem !important;
        }
        #<?= $id ?> .product-badge-discount {
            font-size: 0.625rem !important;
            padding: 0.15rem 0.35rem !important;
        }
        #<?= $id ?> .product-moq-row {
            font-size: 0.725rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.25rem !important;
        }
        #<?= $id ?> .product-moq-row svg {
            width: 0.85rem !important;
            height: 0.85rem !important;
            flex-shrink: 0 !important;
        }
        #<?= $id ?> .product-btn {
            padding: 0.55rem 0.5rem !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            border-radius: 0.65rem !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.35rem !important;
            white-space: nowrap !important;
            line-height: 1.2 !important;
        }
        #<?= $id ?> .product-btn svg {
            width: 1rem !important;
            height: 1rem !important;
            flex-shrink: 0 !important;
        }
        #<?= $id ?> .product-btn span {
            font-size: inherit !important;
            white-space: nowrap !important;
        }
        #<?= $id ?> .carousel-header-cta {
            min-height: 2.25rem !important;
            padding: 0.4rem 1.25rem !important;
            font-size: 0.8125rem !important;
            border-width: 1.5px !important;
        }
        #<?= $id ?> .slider-dot {
            width: 8px;
            height: 8px;
        }
        #<?= $id ?> .slider-dot.active {
            width: 10px;
            height: 10px;
            outline: 2px solid <?= htmlspecialchars($dot_color) ?> !important;
            outline-offset: 2px;
        }
    }
    
    /* Desktop layout (1024px and up) */
    @media (min-width: 1024px) {
        #<?= $id ?> .slide-item {
            width: calc(<?= $desktopWidth ?>% - <?= (($desktop_count - 1) * 1.5) / $desktop_count ?>rem);
        }
        #<?= $id ?> .carousel-btn {
            top: 50%; /* center of entire card on desktop */
            width: 1.85rem;
            height: 1.85rem;
        }
        #<?= $id ?> .carousel-btn svg {
            width: 0.95rem;
            height: 0.95rem;
        }
        #prev_<?= $id ?> { left: 0.25rem; }
        #next_<?= $id ?> { right: 0.25rem; }
        #track_container_<?= $id ?> { padding-left: 2rem; padding-right: 2rem; }
        #inner_<?= $id ?> { gap: 1.5rem !important; }
        #<?= $id ?> .slider-dot {
            width: 10px;
            height: 10px;
        }
        #<?= $id ?> .slider-dot.active {
            width: 12px;
            height: 12px;
            outline: 2px solid <?= htmlspecialchars($dot_color) ?> !important;
            outline-offset: 2px;
        }
    }
    @media (max-width: 767px) {
        #<?= $id ?> {
            min-height: 0 !important;
            height: auto !important;
            padding-top: 0.35rem !important;
            padding-bottom: 0.35rem !important;
        }
        #<?= $id ?> .carousel-outer-wrap {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        #<?= $id ?> .carousel-bg-wrap {
            padding: 0.65rem 0.35rem 0.65rem 0.35rem !important;
            border-radius: 1.25rem !important;
        }
        #track_container_<?= $id ?> {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        #<?= $id ?> .carousel-header-wrap {
            margin-bottom: 0.5rem !important;
            gap: 0.5rem !important;
        }
        #<?= $id ?> .slide-item {
            display: flex !important;
            flex-direction: column !important;
            height: auto !important;
        }
        #<?= $id ?> .product-card {
            height: 100% !important;
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            border-radius: 1.25rem !important;
            overflow: hidden !important;
        }
        #<?= $id ?> .product-image-wrap {
            aspect-ratio: 4 / 5 !important;
            width: 100% !important;
            height: auto !important;
            overflow: hidden !important;
            background: #f3f4f6 !important;
            display: block !important;
            line-height: 0 !important;
            flex-shrink: 0 !important;
        }
        #<?= $id ?> .product-image-wrap img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
        }
        #<?= $id ?> .product-card-body {
            padding: 0.55rem 0.45rem 0.65rem 0.45rem !important;
            margin-top: -1rem !important;
            border-radius: 1.25rem 1.25rem 0 0 !important;
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
        }
        #<?= $id ?> .product-card-body .mt-auto {
            margin-top: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
        }
        #<?= $id ?> .product-card-body form {
            margin-top: auto !important;
        }
        #<?= $id ?> .product-name {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            margin-bottom: 0.2rem !important;
            min-height: auto !important;
        }
        #<?= $id ?> .product-price {
            font-size: 0.85rem !important;
            font-weight: 800 !important;
        }
        #<?= $id ?> .product-moq-row {
            font-size: 0.525rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.25rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.15rem !important;
            letter-spacing: -0.01em !important;
        }
        #<?= $id ?> .product-moq-row svg {
            width: 0.5875rem !important;
            height: 0.5875rem !important;
            flex-shrink: 0 !important;
        }
        #<?= $id ?> .product-btn {
            white-space: nowrap !important;
            flex-wrap: nowrap !important;
            padding: 0.35rem 0.2rem !important;
            font-size: 0.625rem !important;
            border-radius: 0.5rem !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.15rem !important;
            line-height: 1.2 !important;
        }
        #<?= $id ?> .product-btn svg {
            width: 0.6875rem !important;
            height: 0.6875rem !important;
            flex-shrink: 0 !important;
        }
        #<?= $id ?> .product-btn span {
            font-size: inherit !important;
            white-space: nowrap !important;
        }
        #<?= $id ?> .carousel-header-cta {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 1.5rem !important;
            height: auto !important;
            padding: 0.25rem 0.65rem !important;
            font-size: 0.6875rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            border-radius: 9999px !important;
            border: 1px solid <?= htmlspecialchars($title_color ?? '#ffffff') ?> !important;
            background: transparent !important;
            color: <?= htmlspecialchars($title_color ?? '#ffffff') ?> !important;
            white-space: nowrap !important;
            line-height: 1 !important;
        }
    }
    @media (min-width: 1024px) {
        #<?= $id ?> .carousel-header-cta {
            min-height: 2.5rem !important;
            padding: 0.5rem 1.5rem !important;
            font-size: 0.875rem !important;
            border-width: 1.5px !important;
        }
    }
</style>

<div class="w-full relative py-3 sm:py-5 md:py-6" id="<?= $id ?>">
    <div class="carousel-outer-wrap w-full max-w-full mx-auto px-1 sm:px-6 lg:px-8">
        <div class="carousel-bg-wrap rounded-2xl md:rounded-[2rem] px-3 sm:px-6 md:px-8 py-5 sm:py-7 md:py-8 relative" style="<?= $bgStyle ?>">
        <div class="w-full relative">
        
        <!-- Header -->
        <div class="carousel-header-wrap flex flex-col md:flex-row items-start md:items-center justify-between gap-2 md:gap-3 mb-3 sm:mb-4 md:mb-5">
            <?php if ($cta_position === 'Center'): ?>
                <div class="flex-1"></div>
            <?php endif; ?>
            
            <h2 class="text-lg sm:text-2xl md:text-3xl font-serif font-bold leading-tight min-w-0 <?= $cta_position === 'Center' ? 'text-center flex-1' : 'flex-1' ?>" style="color: <?= htmlspecialchars($title_color) ?>;">
                <?= htmlspecialchars($title) ?>
            </h2>
            
            <?php if ($cta_text && $cta_url): ?>
                <div class="self-end md:self-auto <?= $cta_position === 'Center' ? 'flex-1 text-right' : 'shrink-0' ?>">
                    <a href="<?= htmlspecialchars($cta_url) ?>" class="carousel-header-cta text-xs sm:text-sm font-bold tracking-wider uppercase hover:opacity-80 px-3 sm:px-6 py-1.5 sm:py-2 rounded-full transition-all whitespace-nowrap inline-flex items-center justify-center" style="border: 1.5px solid <?= htmlspecialchars($title_color ?? '#ffffff') ?>; color: <?= htmlspecialchars($title_color ?? '#ffffff') ?>; background-color: transparent;">
                        <?= htmlspecialchars($cta_text) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Slider Container -->
        <div class="relative group/carousel" id="track_container_<?= $id ?>">
            
            <!-- Track with touch-action pan-y -->
            <div class="overflow-hidden touch-pan-y" id="track_<?= $id ?>" style="touch-action: pan-y; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain; overscroll-behavior-y: auto;">
                <div class="flex transition-transform duration-500 ease-out items-stretch" id="inner_<?= $id ?>" style="transform: translateX(0%);">
                    <?php if (empty($carouselProducts)): ?>
                        <div class="w-full py-20 text-center text-gray-500 bg-white/50 rounded-2xl">
                            No products selected for this carousel.
                        </div>
                    <?php else: ?>
                        <?php foreach ($carouselProducts as $prod): 
                            $rawImg = !empty($prod['primary_image']) ? (str_starts_with($prod['primary_image'], 'http') ? $prod['primary_image'] : (BASE_URL . '/' . ltrim($prod['primary_image'], '/'))) : 'https://placehold.co/400?text=' . urlencode($prod['name']);
                            $img = get_image_url($rawImg, 400, 500);
                            $catGst = (float)($prod['category_sgst'] ?? 0) + (float)($prod['category_cgst'] ?? 0);
                            $prodGst = $catGst > 0 ? $catGst : ((float)($prod['variant_total_gst'] ?? 0) ?: (float)($prod['total_gst'] ?? 0));

                            $basePrice = (isset($prod['base_price']) && (float)$prod['base_price'] > 0) ? (float)$prod['base_price'] : (float)($prod['wholesale_price'] ?? 0);
                            $discountPrice = !empty($prod['discount_price']) && (float)$prod['discount_price'] > 0 ? (float)$prod['discount_price'] : null;
                            $effPrice = $discountPrice ?? $basePrice;

                            $displayPrice = $effPrice;

                            $displayOriginalPrice = null;
                            if ($discountPrice && $basePrice > $discountPrice) {
                                $displayOriginalPrice = $basePrice;
                            }

                            $moq = !empty($prod['moq_override']) ? $prod['moq_override'] : ($prod['category_moq'] ?? 1);
                            $isLoggedIn = \Core\Session::get('user_id') !== null;
                            $isCustomProduct = !empty($prod['is_customizable']);
                            $prodDetailUrl = $isCustomProduct ? (BASE_URL . '/fabrics/customize?fabric_id=' . (int)$prod['id']) : (BASE_URL . '/product?id=' . (int)$prod['id']);
                        ?>
                            <!-- Slide Item -->
                            <div class="slide-item flex-none">
                                <?php if ($isCustomProduct): ?>
                                    <?php
                                    $cGarmentType = !empty($prod['garment_type']) ? $prod['garment_type'] : 'CUSTOM GARMENT';
                                    $cStock = isset($prod['stock_quantity']) ? (int)$prod['stock_quantity'] : 100;
                                    $cImages = !empty($prod['images']) && is_array($prod['images']) ? array_values(array_filter($prod['images'])) : (!empty($img) ? [$img] : []);
                                    if ((empty($cImages) || count($cImages) <= 1) && !empty($prod['id'])) {
                                        try {
                                            $cFallbackImgs = $db->query("SELECT image_path FROM product_images WHERE product_id = " . (int)$prod['id'] . " ORDER BY is_primary DESC, sort_order ASC, id ASC")->fetchAll(\PDO::FETCH_COLUMN);
                                            if (!empty($cFallbackImgs) && count($cFallbackImgs) > 1) {
                                                $cImages = $cFallbackImgs;
                                            }
                                        } catch (\Throwable $e) {}
                                    }
                                    $cSizes = !empty($prod['sizes']) && is_array($prod['sizes']) ? $prod['sizes'] : ['3XL', '4XL', 'L', 'M', 'XL', 'XXL'];
                                    $cCatName = !empty($prod['category_name']) ? $prod['category_name'] : $cGarmentType;
                                    $cMoq = !empty($prod['custom_moq']) ? $prod['custom_moq'] : $moq;
                                    $cCleanDesc = !empty($prod['description']) 
                                        ? trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r", "\n"], ' ', strip_tags($prod['description'])))) 
                                        : 'Soft, breathable fabric perfect for custom tailoring.';
                                    ?>
                                    <div class="product-card bg-white rounded-2xl sm:rounded-[22px] border border-gray-100 overflow-hidden shadow-xs hover:shadow-xl hover:border-pink-200 transition-all duration-300 flex flex-col h-full group transform hover:-translate-y-1 relative">
                                        
                                        <!-- Top Image Area -->
                                        <a href="<?= $prodDetailUrl ?>" class="block relative h-48 sm:h-56 w-full bg-gradient-to-br from-pink-50 to-rose-50 overflow-hidden group/img">
                                            <img id="pc-main-img-<?= $prod['id'] ?>_<?= $id ?>" 
                                                 src="<?= htmlspecialchars($img) ?>" 
                                                 alt="<?= htmlspecialchars($prod['name']) ?>" 
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="300"
                                                 height="375"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                                            <!-- Top Left Badges: CUSTOM & Garment Type -->
                                            <div class="absolute top-2 left-2 pointer-events-none z-10 flex flex-col gap-1 items-start">
                                                <span class="px-2 py-0.5 bg-[#F25996] text-white font-extrabold text-[8.5px] uppercase tracking-wider rounded-md shadow-sm flex items-center gap-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/></svg>
                                                    <span>CUSTOM</span>
                                                </span>
                                                <?php if (!empty($cGarmentType)): ?>
                                                    <span class="px-1.5 py-0.5 bg-black/65 backdrop-blur-xs text-white font-bold text-[8px] uppercase tracking-wider rounded shadow-xs">
                                                        <?= htmlspecialchars($cGarmentType) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Top Right: Stock Counter (Wishlist removed for customize card) -->
                                            <?php if ($cStock > 0): ?>
                                                <div class="absolute top-2 right-2 z-10 flex flex-col items-end pointer-events-none">
                                                    <span class="px-2 py-0.5 bg-emerald-600/95 backdrop-blur-xs text-white font-bold text-[8px] tracking-wide rounded-full shadow-sm flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                                        <?= $cStock ?> Qty
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <!-- Circular Variant Swatches (when multiple images exist) -->
                                        <?php if (count($cImages) > 1): ?>
                                        <div class="-mt-4 mb-1.5 px-3 flex items-center gap-1.5 relative z-20 overflow-x-auto scrollbar-none pb-0.5">
                                            <?php 
                                                foreach (array_slice($cImages, 0, 5) as $idx => $swImg):
                                                    $swUrl = get_image_url($swImg, 100, 100);
                                                    $fullSwUrl = get_image_url($swImg);
                                            ?>
                                                <img src="<?= htmlspecialchars($swUrl) ?>" 
                                                     alt="" 
                                                     loading="lazy"
                                                     decoding="async"
                                                     width="32"
                                                     height="32"
                                                     onerror="if(!this.dataset.retried){this.dataset.retried='1';this.src='<?= htmlspecialchars($fullSwUrl) ?>';}else{this.style.display='none';}"
                                                     onclick="event.stopPropagation(); document.getElementById('pc-main-img-<?= $prod['id'] ?>_<?= $id ?>').src='<?= htmlspecialchars($fullSwUrl) ?>'; this.parentElement.querySelectorAll('img').forEach(function(i){i.classList.remove('ring-2', 'ring-[#F25996]')}); this.classList.add('ring-2', 'ring-[#F25996]');"
                                                     class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover cursor-pointer hover:scale-110 transition-transform flex-shrink-0 <?= $idx === 0 ? 'ring-2 ring-[#F25996]' : '' ?>">
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="h-2"></div>
                                        <?php endif; ?>

                                        <!-- Card Content Body -->
                                        <div class="px-3 pt-0 pb-3 flex-1 flex flex-col justify-between">
                                            <div>
                                                <!-- Category Label in Pink -->
                                                <div class="text-[#F25996] font-bold text-[9.5px] uppercase tracking-wide mb-0.5 truncate">
                                                    <?= htmlspecialchars($cCatName) ?>
                                                </div>

                                                <!-- Product Title -->
                                                <h3 class="font-bold text-[#1e293b] text-xs sm:text-sm leading-snug line-clamp-1 mb-1 hover:text-[#F25996] transition-colors" title="<?= htmlspecialchars($prod['name']) ?>">
                                                    <a href="<?= $prodDetailUrl ?>"><?= htmlspecialchars($prod['name']) ?></a>
                                                </h3>

                                                <!-- Dynamic Rating & Review Row -->
                                                <?php
                                                    $cardRatingRaw = (float)($prod['average_rating'] ?? 0);
                                                    $cardReviews   = (int)($prod['num_reviews'] ?? 0);
                                                    $hasReviews    = ($cardReviews > 0 || $cardRatingRaw > 0);
                                                    $cardStars     = max(0, min(5, (int)round($cardRatingRaw)));
                                                    $cardRatingFmt = number_format($cardRatingRaw, 1);
                                                ?>
                                                <div class="product-rating-row flex items-center gap-1.5 my-1 text-xs">
                                                    <div class="product-stars flex items-center text-amber-400" style="color: var(--pc-star, #f59e0b);">
                                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 <?= ($hasReviews && $s <= $cardStars) ? 'fill-current' : 'text-gray-200 fill-current' ?>" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="font-bold text-gray-800 text-[11px] sm:text-xs"><?= $hasReviews ? $cardRatingFmt : '0.0' ?></span>
                                                    <span class="text-gray-400 text-[10px] sm:text-[11px] font-medium">(<?= $cardReviews ?>)</span>
                                                </div>

                                                <!-- Description -->
                                                <p class="text-[10px] text-[#64748b] line-clamp-2 mb-2 leading-tight">
                                                    <?= htmlspecialchars($cCleanDesc) ?>
                                                </p>

                                                <?php if ($isLoggedIn): ?>
                                                    <!-- Custom Stitching & Min Stitch (Row 1 & 2 on mobile/tablet, same row on desktop) -->
                                                    <div class="mb-2">
                                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-1.5 lg:gap-1">
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-50/80 border border-pink-100/90 text-[#F25996] text-[10px] font-bold shrink-0 w-fit">
                                                                <svg class="w-3 h-3 text-[#F25996] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/>
                                                                </svg>
                                                                <span>Custom Stitching</span>
                                                            </div>

                                                            <!-- Min Stitch & Count Badge: Next row on mobile & tablet, right on desktop -->
                                                            <div class="inline-flex items-center gap-1 shrink-0 whitespace-nowrap self-start lg:self-auto">
                                                                <span class="uppercase font-semibold text-gray-400 tracking-wider" style="font-size: 7.5px; line-height: 1;">Min Stitch</span>
                                                                <span class="px-1.5 py-0.5 bg-pink-50 text-[#F25996] border border-pink-200/80 font-extrabold text-[9.5px] rounded-md leading-none">
                                                                    <?= (int)$cMoq ?> Pcs
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sizes Matrix -->
                                                    <div class="mb-2.5">
                                                        <div class="flex items-center gap-1 flex-wrap">
                                                            <?php foreach ($cSizes as $sIndex => $szName): ?>
                                                                <?php if ($sIndex === 0): ?>
                                                                    <span class="px-1.5 py-0.5 bg-[#F25996] text-white font-bold text-[9px] rounded shadow-xs flex items-center justify-center min-w-[20px]">
                                                                        <?= htmlspecialchars($szName) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="px-1.5 py-0.5 bg-[#fff0f6] text-[#db2777] font-bold text-[9px] rounded border border-pink-100/80 flex items-center justify-center min-w-[20px]">
                                                                        <?= htmlspecialchars($szName) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                            </div>

                                            <!-- Action Button: Customize & Stitch → -->
                                            <div>
                                                <a href="<?= $prodDetailUrl ?>" 
                                                   class="w-full py-2 px-3 bg-gradient-to-r from-[#F25996] via-[#ea3c85] to-[#db2777] hover:opacity-95 text-white font-bold text-xs rounded-xl shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-1.5 group/btn active:scale-[0.98]">
                                                    <span class="truncate">Customize & Stitch</span>
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                                <?php else: ?>
                                            </div>
                                            <div class="mt-auto pt-2 md:pt-3 lg:pt-4 md:border-t md:border-gray-100 flex flex-col gap-2">
                                                <a href="<?= BASE_URL ?>/login" class="flex items-center gap-1.5 text-[#F25996] font-semibold hover:underline whitespace-nowrap overflow-hidden text-ellipsis" style="font-size: clamp(0.65rem, 2.2vw, 0.8125rem);">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    <span class="truncate">Register to view price</span>
                                                </a>
                                            </div>
                                                <?php endif; ?>

                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Normal Product Card: 100% UNTOUCHED -->
                                    <div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-200 flex flex-col h-full relative group">
                                        
                                        <!-- Wishlist Icon -->
                                        <?php if ($isLoggedIn): ?>
                                        <?php 
                                            $inWishlist = false;
                                            if (!empty($wishlistIds) && is_array($wishlistIds)) {
                                                $inWishlist = in_array($prod['id'], $wishlistIds);
                                            } elseif (!empty($wishlistItems) && is_array($wishlistItems)) {
                                                $inWishlist = in_array($prod['id'], array_column($wishlistItems, 'product_id')) || in_array($prod['id'], array_column($wishlistItems, 'id'));
                                            }
                                        ?>
                                        <button onclick="toggleWishlist(<?= $prod['id'] ?>, this)" data-wishlist-btn-id="<?= $prod['id'] ?>" class="wishlist-btn <?= $inWishlist ? 'is-active' : '' ?> absolute top-2 right-2 w-6 h-6 sm:w-7 sm:h-7 hover:opacity-80 transition-opacity rounded-full flex items-center justify-center z-10 shadow-sm" style="background-color: <?= htmlspecialchars($wishlist_color) ?>;">
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 <?= $inWishlist ? 'text-[#F25996] fill-[#F25996] is-active' : 'text-gray-400 fill-none' ?>" fill="<?= $inWishlist ? '#F25996' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                        </button>
                                        <?php endif; ?>

                                        <!-- Product Image -->
                                        <a href="<?= $prodDetailUrl ?>" class="product-image-wrap block w-full bg-gray-100 overflow-hidden shrink-0 relative">
                                            <?php 
                                            $discPercent = ($displayOriginalPrice && $displayOriginalPrice > $displayPrice) ? round((($displayOriginalPrice - $displayPrice) / $displayOriginalPrice) * 100) : 0;
                                            if ($isLoggedIn && $discPercent > 0) {
                                                echo render_discount_starburst($discPercent, 'transition-transform duration-300 group-hover:scale-105');
                                            }
                                            ?>
                                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" width="300" height="375" class="w-full h-full <?= $objectFitClass ?> group-hover:scale-105 transition-transform duration-500" loading="lazy" decoding="async" style="object-fit: <?= htmlspecialchars($image_fit ?: 'cover') ?>; width: 100%; height: 100%; display: block;">
                                        </a>

                                        <!-- Product Details -->
                                        <div class="product-card-body p-3 md:p-4 flex flex-col bg-white relative z-10 flex-1">
                                            <?php 
                                            $cardBrand = !empty($prod['vendor_store_name']) ? $prod['vendor_store_name'] : (!empty($prod['vendor_company_name']) ? $prod['vendor_company_name'] : '');
                                            ?>
                                            <?php if (!empty($cardBrand)): ?>
                                                <div class="mb-1">
                                                    <span class="inline-flex items-center gap-1 text-[10px] md:text-[11px] font-bold text-indigo-700 bg-indigo-50/80 px-2 py-0.5 rounded border border-indigo-100/70 capitalize">
                                                        <svg class="w-2.5 h-2.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        <?= htmlspecialchars($cardBrand) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?= $prodDetailUrl ?>" class="product-link block mb-1">
                                                <h3 class="product-name font-bold leading-snug line-clamp-2"><?= htmlspecialchars($prod['name']) ?></h3>
                                            </a>
                                            
                                            <?php if (!empty($prod['sku'])): ?>
                                                <p class="font-medium mb-1 hidden md:block" style="font-size: 0.9375rem; color: <?= htmlspecialchars($subtitle_color) ?>;">SKU: <?= htmlspecialchars($prod['sku']) ?></p>
                                            <?php endif; ?>

                                            <!-- Dynamic Rating & Review Row -->
                                            <?php
                                                $cardRatingRaw = (float)($prod['average_rating'] ?? 0);
                                                $cardReviews   = (int)($prod['num_reviews'] ?? 0);
                                                $hasReviews    = ($cardReviews > 0 || $cardRatingRaw > 0);
                                                $cardStars     = max(0, min(5, (int)round($cardRatingRaw)));
                                                $cardRatingFmt = number_format($cardRatingRaw, 1);
                                            ?>
                                            <div class="product-rating-row flex items-center gap-1.5 my-1 text-xs">
                                                <div class="product-stars flex items-center text-amber-400" style="color: var(--pc-star, #f59e0b);">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 <?= ($hasReviews && $s <= $cardStars) ? 'fill-current' : 'text-gray-200 fill-current' ?>" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="font-bold text-gray-800 text-[11px] sm:text-xs"><?= $hasReviews ? $cardRatingFmt : '0.0' ?></span>
                                                <span class="text-gray-400 text-[10px] sm:text-[11px] font-medium">(<?= $cardReviews ?>)</span>
                                            </div>

                                            <?php if ($isLoggedIn): ?>
                                                <div class="mt-auto flex flex-col justify-end">
                                                    <!-- Selling Price, Strikethrough & Discount Badge (Same Row) -->
                                                    <div class="product-price-row flex items-baseline gap-1.5 flex-wrap mb-1">
                                                        <span class="product-price font-extrabold tracking-tight">₹<?= format_price($displayPrice) ?></span>
                                                        <?php if ($displayOriginalPrice && $displayOriginalPrice > $displayPrice): ?>
                                                            <span class="product-orig-price text-[0.6875rem] md:text-sm font-bold text-gray-400 line-through">₹<?= format_price($displayOriginalPrice) ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Row 4: Minimum Order Count -->
                                                    <p class="product-moq-row font-semibold flex items-center gap-1 whitespace-nowrap overflow-hidden text-ellipsis">
                                                        <svg class="w-3 h-3 md:w-3.5 md:h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                        <span>Min. Order: <?= htmlspecialchars($moq) ?> Pcs</span>
                                                    </p>

                                                    <!-- Row 6: CTA Button -->
                                                    <form action="<?= BASE_URL ?>/cart/add" method="POST" onsubmit="ajaxAddToCart(event, this)">
                                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                        <input type="hidden" name="quantity" value="<?= htmlspecialchars($moq) ?>">
                                                        <button type="submit" class="product-btn w-full font-bold transition-colors flex items-center justify-center gap-1.5 md:gap-1.5 lg:gap-2 hover:opacity-90 shadow-sm whitespace-nowrap">
                                                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                            <span class="whitespace-nowrap">Add to Cart</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-auto pt-2 md:pt-3 lg:pt-4 md:border-t md:border-gray-100 flex flex-col gap-2">
                                                    <a href="<?= BASE_URL ?>/login" class="flex items-center gap-1.5 text-[#F25996] font-semibold hover:underline whitespace-nowrap overflow-hidden text-ellipsis" style="font-size: clamp(0.65rem, 2.2vw, 0.8125rem);">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        <span class="truncate">Register to view price</span>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Arrows -->
            <?php if ($show_arrows && !empty($carouselProducts)): ?>
                <button id="prev_<?= $id ?>" class="carousel-btn absolute -translate-y-1/2 rounded-full flex items-center justify-center shadow-md border border-gray-200 hover:shadow-lg hover:scale-105 active:scale-95 transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none z-20" style="color: <?= htmlspecialchars($arrow_color) ?>; background-color: <?= htmlspecialchars($arrow_bg_color) ?>;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button id="next_<?= $id ?>" class="carousel-btn absolute -translate-y-1/2 rounded-full flex items-center justify-center shadow-md border border-gray-200 hover:shadow-lg hover:scale-105 active:scale-95 transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none z-20" style="color: <?= htmlspecialchars($arrow_color) ?>; background-color: <?= htmlspecialchars($arrow_bg_color) ?>;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </button>
            <?php endif; ?>
        </div>

        <!-- Dots -->
        <?php if ($show_dots && !empty($carouselProducts)): ?>
            <div class="mt-8 flex justify-center items-center gap-2" id="dots_<?= $id ?>">
                <!-- Dots generated by JS -->
            </div>
        <?php endif; ?>

        </div>
    </div>
</div>
     </div>

<script>
function initProductCarousel_<?= $id ?>() {
    const track = document.getElementById('track_<?= $id ?>');
    const inner = document.getElementById('inner_<?= $id ?>');
    if (!inner || !track) return;

    const prevBtn = document.getElementById('prev_<?= $id ?>');
    const nextBtn = document.getElementById('next_<?= $id ?>');
    const dotsContainer = document.getElementById('dots_<?= $id ?>');
    const slides = inner.querySelectorAll('.slide-item');
    
    if (slides.length === 0) return;

    // Desktop/Tablet/Mobile items count configuration
    const mobileCount = <?= (int)$mobile_count ?>;
    const tabletCount = <?= (int)$tablet_count ?>;
    const desktopCount = <?= (int)$desktop_count ?>;
    
    function getItemsPerView() {
        if (window.innerWidth < 768) {
            return mobileCount;
        } else if (window.innerWidth < 1024) {
            return tabletCount;
        } else {
            return desktopCount;
        }
    }

    let currentIndex = 0;
    let itemsPerView = getItemsPerView();
    let maxIndex = Math.max(0, slides.length - itemsPerView);

    function updateSlider() {
        let slideWidth = 0;
        if (slides.length > 1) {
            slideWidth = slides[1].getBoundingClientRect().left - slides[0].getBoundingClientRect().left;
        } else {
            slideWidth = slides[0].getBoundingClientRect().width;
        }
        
        inner.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        
        // Buttons active with no scroll lock (continuous wrap-around loop)
        if (prevBtn) {
            prevBtn.disabled = false;
            prevBtn.style.opacity = '1';
            prevBtn.style.cursor = 'pointer';
            prevBtn.style.pointerEvents = 'auto';
            prevBtn.style.visibility = 'visible';
            prevBtn.style.display = 'flex';
        }
        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
            nextBtn.style.pointerEvents = 'auto';
            nextBtn.style.visibility = 'visible';
            nextBtn.style.display = 'flex';
        }
        
        // Update Dots
        if (dotsContainer) {
            const dots = dotsContainer.querySelectorAll('.slider-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
    }

    function initDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        if (maxIndex > 0) {
            dotsContainer.style.display = 'flex';
            for (let i = 0; i <= maxIndex; i++) {
                const dot = document.createElement('button');
                dot.className = 'slider-dot' + (i === currentIndex ? ' active' : '');
                dot.setAttribute('aria-label', `Slide ${i + 1}`);
                dot.onclick = () => {
                    currentIndex = i;
                    updateSlider();
                };
                dotsContainer.appendChild(dot);
            }
        } else {
            dotsContainer.style.display = 'none';
        }
    }

    initDots();

    // Navigation buttons without scroll lock (infinite wrap-around)
    if (prevBtn) {
        prevBtn.onclick = () => {
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = maxIndex;
            }
            updateSlider();
        };
    }

    if (nextBtn) {
        nextBtn.onclick = () => {
            if (currentIndex < maxIndex) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            updateSlider();
        };
    }

    // Handle Resize
    window.addEventListener('resize', () => {
        const newItemsPerView = getItemsPerView();
        if (newItemsPerView !== itemsPerView) {
            itemsPerView = newItemsPerView;
            maxIndex = Math.max(0, slides.length - itemsPerView);
            currentIndex = Math.min(currentIndex, maxIndex);
            initDots();
            updateSlider();
        } else {
            updateSlider();
        }
    });

    updateSlider();

    // Cursor Vertical Scroll: converts vertical mouse wheel scroll to left-to-right carousel movement
    let wheelTimeout = null;
    let isWheeling = false;
    track.addEventListener('wheel', (e) => {
        const delta = Math.abs(e.deltaY) >= Math.abs(e.deltaX) ? e.deltaY : e.deltaX;
        if (Math.abs(delta) > 8) {
            if (isWheeling) return;
            isWheeling = true;
            if (delta > 0) {
                // Wheel down -> scroll left to right (next)
                if (currentIndex < maxIndex) {
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
            } else {
                // Wheel up -> scroll right to left (prev)
                if (currentIndex > 0) {
                    currentIndex--;
                } else {
                    currentIndex = maxIndex;
                }
            }
            updateSlider();
            clearTimeout(wheelTimeout);
            wheelTimeout = setTimeout(() => { isWheeling = false; }, 180);
        }
    }, { passive: true });

    // Cursor Drag Scroll (Click and Drag Left to Right with Cursor)
    let isDragging = false;
    let dragStartX = 0;
    let hasMoved = false;

    track.style.cursor = 'grab';

    track.addEventListener('mousedown', (e) => {
        if (e.target.closest('button, a, input, select, textarea, form, label')) return;
        isDragging = true;
        hasMoved = false;
        dragStartX = e.pageX;
        track.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        if (Math.abs(e.pageX - dragStartX) > 6) {
            hasMoved = true;
        }
    });

    window.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        track.style.cursor = 'grab';
        if (hasMoved) {
            const diffX = e.pageX - dragStartX;
            if (Math.abs(diffX) > 35) {
                if (diffX < 0) {
                    if (currentIndex < maxIndex) {
                        currentIndex++;
                    } else {
                        currentIndex = 0;
                    }
                } else {
                    if (currentIndex > 0) {
                        currentIndex--;
                    } else {
                        currentIndex = maxIndex;
                    }
                }
                updateSlider();
            }
        }
    });

    // Touch swipe with wrap-around
    let touchStartX = 0;
    let touchStartY = 0;
    track.addEventListener('touchstart', (e) => {
        if (e.changedTouches && e.changedTouches[0]) {
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
        }
    }, {passive: true});

    track.addEventListener('touchend', (e) => {
        if (e.changedTouches && e.changedTouches[0]) {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const diffX = touchStartX - touchEndX;
            const diffY = touchStartY - touchEndY;
            // If gesture is vertical, allow smooth vertical scroll without switching slides
            if (Math.abs(diffY) > Math.abs(diffX)) return;
            if (Math.abs(diffX) > 35) {
                if (diffX > 0) {
                    if (currentIndex < maxIndex) {
                        currentIndex++;
                    } else {
                        currentIndex = 0;
                    }
                } else {
                    if (currentIndex > 0) {
                        currentIndex--;
                    } else {
                        currentIndex = maxIndex;
                    }
                }
                updateSlider();
            }
        }
    }, {passive: true});
}

// Resilient initialization: execute immediately if DOM is ready, otherwise on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductCarousel_<?= $id ?>);
} else {
    initProductCarousel_<?= $id ?>();
}
window.addEventListener('load', initProductCarousel_<?= $id ?>);
</script>
