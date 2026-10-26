<?php

$canAddToCart = \Core\Session::get('user_id') && \Core\Session::get('user_status') === 'active';
$moq = $product['moq_override'] ?? $product['category_moq'] ?? 1; 
$primaryImage = !empty($product['primary_image']) ? get_image_url($product['primary_image']) : 'https://placehold.co/800x800/f9fafb/9ca3af?text=No+Image';

// Build a variant-to-images map for JS-driven gallery
// Only include images that were explicitly uploaded for each variant.
// Do NOT fall back to the product primary image — if a variant has no images, show nothing.
$variantImageMap = [];
if (!empty($variants)) {
    foreach ($variants as $v) {
        $vImages = [];
        if (!empty($v['images'])) {
            foreach ($v['images'] as $img) {
                $vImages[] = $img; // already full URL from controller
            }
        }
        $variantImageMap[$v['id']] = $vImages; // empty array = no images for this variant
    }
}

// Determine the initial main image:
// Priority: first in-stock variant's first image → any variant's first image → product primary
$initialMainImage = $primaryImage;
if (!empty($variants)) {
    foreach ($variants as $v) {
        if (!empty($variantImageMap[$v['id']])) {
            $initialMainImage = $variantImageMap[$v['id']][0];
            break;
        }
    }
}

// Compute Initial Pricing & Discount Values for Badge & Header
$catGst = (float)($category['sgst'] ?? 0) + (float)($category['cgst'] ?? 0);
$productGst = $catGst > 0 
    ? $catGst 
    : ((isset($product['total_gst']) && $product['total_gst'] !== null && $product['total_gst'] !== '' && (float)$product['total_gst'] > 0) 
        ? (float)$product['total_gst'] 
        : ((float)($product['sgst'] ?? 0) + (float)($product['cgst'] ?? 0)));
$variantGst = $productGst;
$displayPrice = (float)($product['wholesale_price'] ?? 0);
$displayOriginalPrice = null;

$hasInStockVariant = false;
$firstInStockIndex = -1;
if (!empty($variants)) {
    foreach($variants as $index => $variant) {
        if ((int)($variant['current_stock'] ?? 0) >= $moq && $firstInStockIndex === -1) {
            $firstInStockIndex = $index;
            $hasInStockVariant = true;
        }
    }
}
$firstIndex = ($hasInStockVariant && $firstInStockIndex !== -1) ? $firstInStockIndex : 0;
$initialWeight = null;

if (!empty($variants)) {
    $firstVariant = $variants[$firstIndex];
    if (isset($firstVariant['weight']) && $firstVariant['weight'] !== null && $firstVariant['weight'] !== '' && (float)$firstVariant['weight'] > 0) {
        $initialWeight = (float)$firstVariant['weight'];
    } elseif (isset($product['weight']) && $product['weight'] !== null && $product['weight'] !== '' && (float)$product['weight'] > 0) {
        $initialWeight = (float)$product['weight'];
    }

    if (isset($firstVariant['base_price']) && (float)$firstVariant['base_price'] > 0) {
        $vGstVal = $catGst > 0 
            ? $catGst 
            : ((isset($firstVariant['total_gst']) && $firstVariant['total_gst'] !== null && $firstVariant['total_gst'] !== '' && (float)$firstVariant['total_gst'] > 0)
                ? (float)$firstVariant['total_gst']
                : (((float)($firstVariant['sgst'] ?? 0) + (float)($firstVariant['cgst'] ?? 0)) ?: $productGst));
        $variantGst = $vGstVal;
        $effPrice = (!empty($firstVariant['discount_price']) && (float)$firstVariant['discount_price'] > 0) ? (float)$firstVariant['discount_price'] : (float)$firstVariant['base_price'];
        
        $displayPrice = $effPrice;

        if (!empty($firstVariant['discount_price']) && (float)$firstVariant['discount_price'] > 0 && (float)$firstVariant['discount_price'] < (float)$firstVariant['base_price']) {
            $displayOriginalPrice = (float)$firstVariant['base_price'];
        }
    }
} else {
    $displayPrice = (float)$displayPrice;
    if (isset($product['weight']) && $product['weight'] !== null && $product['weight'] !== '' && (float)$product['weight'] > 0) {
        $initialWeight = (float)$product['weight'];
    }
}
?>

<style>
    .no-scrollbar::-webkit-scrollbar,
    .hide-scrollbar::-webkit-scrollbar,
    .scrollbar-none::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar,
    .hide-scrollbar,
    .scrollbar-none {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
</style>

<div class="bg-[#fafafa] min-h-screen pb-24 md:pb-12">

    <!-- ── Breadcrumb Section ── -->
    <div class="bg-white border-b border-gray-200/80 mb-4 md:mb-6">
        <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8 py-3.5">
            <nav class="grm-breadcrumb-nav hide-scrollbar no-scrollbar scrollbar-none flex flex-wrap items-center gap-1 sm:gap-1.5 text-xs sm:text-sm font-medium" aria-label="Breadcrumb">
                <a href="<?= BASE_URL ?>/" class="inline-flex items-center gap-1 text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-[#F25996]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    <span>Home</span>
                </a>
                <svg class="w-3.5 h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <a href="<?= BASE_URL ?>/catalog" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0">Catalog</a>
                <?php if (!empty($category['name'])): ?>
                    <svg class="w-3.5 h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="<?= BASE_URL ?>/catalog?category=<?= urlencode($category['name']) ?>" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0"><?= htmlspecialchars($category['name']) ?></a>
                <?php endif; ?>
                <?php if (!empty($subCategory['name'])): ?>
                    <svg class="w-3.5 h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="<?= BASE_URL ?>/catalog?category=<?= urlencode($category['name'] ?? '') ?>&subcategory=<?= urlencode($subCategory['name']) ?>" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0"><?= htmlspecialchars($subCategory['name']) ?></a>
                <?php endif; ?>
                <svg class="w-3.5 h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-[#F25996] font-bold" aria-current="page"><?= htmlspecialchars($product['name']) ?></span>
            </nav>
        </div>
    </div>

    <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 mb-10 items-start">
            
            <!-- ===== LEFT: Image Gallery ===== -->
            <div class="lg:col-span-5 lg:sticky lg:top-6 lg:self-start relative z-40 max-w-[540px] w-full mx-auto">
                <div class="flex flex-row gap-2.5 sm:gap-3 items-start">
                    <!-- Thumbnail Strip (Left Side) -->
                    <div id="thumbnailGallery" class="flex empty:hidden flex-col gap-2 sm:gap-2.5 overflow-y-auto max-h-[560px] scrollbar-thin flex-shrink-0 py-0.5">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Main Image (Right Side) -->
                    <div class="relative bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm group cursor-crosshair flex-1 min-w-0" id="mainImageWrap">
                        <?php 
                        $initialDiscPct = ($displayOriginalPrice && $displayOriginalPrice > $displayPrice) ? round((($displayOriginalPrice - $displayPrice) / $displayOriginalPrice) * 100) : 0;
                        ?>
                        <div id="mainImageDiscountBadge" class="<?= ($canAddToCart && $initialDiscPct > 0) ? '' : 'hidden' ?> absolute top-3 left-3 z-20 w-16 h-16 sm:w-20 sm:h-20 drop-shadow-lg pointer-events-none">
                            <?= render_discount_starburst($initialDiscPct > 0 ? $initialDiscPct : 1, 'w-full h-full') ?>
                        </div>
                        <!-- Zoom Lens -->
                        <div id="zoomLens" class="absolute hidden border border-gray-300 bg-white/40 pointer-events-none" style="width: 140px; height: 140px; z-index: 10;"></div>
                        
                        <div class="w-full overflow-hidden flex items-center justify-center bg-gray-50/40 rounded-xl" style="aspect-ratio: 3 / 4; max-height: 560px;">
                            <img id="mainImage" 
                                 src="<?= $initialMainImage ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="w-full h-full object-cover" 
                                 style="width: 100%; height: 100%; object-fit: cover; display: block; transition: opacity 0.2s ease;"
                                 loading="eager" decoding="async"
                                 onerror="this.src='https://placehold.co/800x800/f9fafb/9ca3af?text=No+Image'">
                        </div>
                        <!-- Zoom hint -->
                        <div class="absolute bottom-3 right-3 bg-black/30 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm hidden md:flex items-center gap-1 pointer-events-none" id="zoomHint">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                            Click to zoom
                        </div>
                    </div>
                </div>
                
                <!-- Zoom Result Panel (Hidden by default, shown on hover, desktop only) -->
                <div id="zoomResult" class="hidden absolute top-0 left-full ml-6 w-[500px] h-[560px] bg-white border border-gray-200 shadow-2xl z-50 rounded-2xl bg-no-repeat pointer-events-none" style="background-color: #fff; background-size: 200%;"></div>
            </div>

            <!-- ===== RIGHT: Details Panel ===== -->
            <div class="lg:col-span-7 flex flex-col">
                <?php 
                $vendorBrandName = !empty($product['vendor_store_name']) ? $product['vendor_store_name'] : (!empty($product['vendor_company_name']) ? $product['vendor_company_name'] : (!empty($product['vendor_name']) ? $product['vendor_name'] : ''));
                ?>

                <!-- Category + Brand + Status -->
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="<?= BASE_URL ?>/catalog?category=<?= urlencode($category['name'] ?? '') ?>" class="text-xs font-bold text-[#F25996] hover:text-[#d8407d] tracking-widest uppercase hover:underline">
                            <?= htmlspecialchars($category['name'] ?? 'Uncategorized') ?>
                        </a>
                        <?php if (!empty($subCategory['name'])): ?>
                            <span class="text-pink-300">•</span>
                            <a href="<?= BASE_URL ?>/catalog?category=<?= urlencode($category['name'] ?? '') ?>&subcategory=<?= urlencode($subCategory['name']) ?>" class="text-xs font-bold text-[#F25996] hover:text-[#d8407d] tracking-widest uppercase hover:underline">
                                <?= htmlspecialchars($subCategory['name']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($vendorBrandName)): ?>
                            <span class="text-gray-300">•</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-800 bg-indigo-50/90 px-2.5 py-0.5 rounded-full border border-indigo-100/90 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-gray-500 font-medium">Brand:</span> <span class="capitalize"><?= htmlspecialchars($vendorBrandName) ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span id="stockBadge" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border
                        <?php 
                        $totalCurrentStock = !empty($variants) 
                            ? array_sum(array_map(fn($v) => max(0, (int)($v['current_stock'] ?? 0)), $variants))
                            : max(0, (int)($product['stock_quantity'] ?? 0));
                        $isAvailable = $totalCurrentStock >= ($moq ?? 1);
                        echo $isAvailable ? 'bg-teal-50 text-teal-700 border-teal-100' : 'bg-red-50 text-red-600 border-red-100';
                        ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $isAvailable ? 'bg-teal-500' : 'bg-red-500' ?>"></span>
                        <?= $isAvailable ? 'In Stock' : 'Out of Stock' ?>
                    </span>
                </div>

                <!-- Product Name and Wishlist -->
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h1 class="text-2xl md:text-4xl font-black text-gray-900 leading-tight tracking-tight">
                        <?= htmlspecialchars($product['name']) ?>
                    </h1>
                    <?php if ($canAddToCart): ?>
                    <?php 
                        $inWish = false;
                        if (!empty($wishlistIds) && is_array($wishlistIds)) {
                            $inWish = in_array($product['id'], $wishlistIds);
                        } elseif (!empty($wishlistItems) && is_array($wishlistItems)) {
                            $inWish = in_array($product['id'], array_column($wishlistItems, 'product_id')) || in_array($product['id'], array_column($wishlistItems, 'id'));
                        }
                    ?>
                    <button type="button" onclick="toggleWishlist(<?= $product['id'] ?>, this)" class="wishlist-btn <?= $inWish ? 'is-active' : '' ?> group p-2 rounded-full hover:bg-pink-50 transition-colors flex-shrink-0" data-wishlist-btn-id="<?= $product['id'] ?>" title="<?= $inWish ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                        <svg class="w-7 h-7 <?= $inWish ? 'text-[#F25996] fill-[#F25996] is-active' : 'text-gray-400 fill-none' ?> group-hover:text-[#F25996] transition-colors" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Rating -->
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex text-yellow-400">
                        <?php 
                        $avgRating = round($product['average_rating'] ?? 0);
                        for($i=1; $i<=5; $i++): ?>
                            <svg class="w-4 h-4 <?= $i <= $avgRating ? 'fill-current' : 'text-gray-200 fill-current' ?>" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <span class="text-sm font-semibold text-gray-700"><?= number_format($product['average_rating'] ?? 0, 1) ?></span>
                    <span class="text-xs text-gray-400">(<?= $product['num_reviews'] ?? 0 ?> reviews)</span>
                    <a href="#customer-reviews" class="text-xs text-[#d64026] hover:underline font-semibold ml-1">Read all</a>
                </div>

                <!-- SKU, HSN & Brand Meta -->
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <?php if (!empty($vendorBrandName)): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold tracking-widest text-[#9c6c4c] uppercase">Brand:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-xs text-indigo-900 bg-indigo-50/90 px-2.5 py-0.5 rounded border border-indigo-200 capitalize shadow-2xs">
                            <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <?= htmlspecialchars($vendorBrandName) ?>
                            <?php if (!empty($product['unique_vendor_id'])): ?>
                                <span class="text-[10px] text-indigo-500 font-mono font-normal">(<?= htmlspecialchars($product['unique_vendor_id']) ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold tracking-widest text-[#F25996] uppercase">SKU:</span>
                        <span id="productSkuDisplay" class="font-mono text-sm font-semibold text-[#F25996] bg-pink-50 px-3 py-0.5 rounded-full border border-pink-200">
                            <?php 
                            $displaySku = $product['base_sku'];
                            if (empty($displaySku) && !empty($variants)) {
                                $firstIndex = isset($firstInStockIndex) && $firstInStockIndex !== -1 ? $firstInStockIndex : 0;
                                $displaySku = $variants[$firstIndex]['sku'] ?? 'N/A';
                            }
                            echo htmlspecialchars($displaySku ?: 'N/A');
                            ?>
                        </span>
                    </div>
                    <?php if (!empty($category['hsn_code'])): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold tracking-widest text-[#F25996] uppercase">HSN:</span>
                        <span class="font-mono text-xs font-bold text-[#F25996] bg-pink-50 px-3 py-0.5 rounded-full border border-pink-200">
                            <?= htmlspecialchars($category['hsn_code']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Price Box -->
                <?php if ($canAddToCart): ?>
                <div class="bg-gradient-to-br from-pink-50/60 to-white rounded-2xl p-5 border border-pink-100 mb-5 shadow-sm">
                    <div class="flex items-baseline gap-3 flex-wrap" id="priceDisplayContainer">
                        <span class="text-3xl md:text-4xl font-black text-[#F25996]" id="mainPriceDisplay">₹<?= format_price($displayPrice) ?></span>
                        <span class="text-sm text-gray-400 font-semibold" id="priceUnitLabel">/ unit</span>
                        <?php if ($displayOriginalPrice): ?>
                            <span class="text-base font-bold text-gray-400 line-through" id="originalPriceDisplay">₹<?= format_price($displayOriginalPrice) ?></span>
                        <?php else: ?>
                            <span class="text-base font-bold text-gray-400 line-through hidden" id="originalPriceDisplay"></span>
                        <?php endif; ?>
                        <?php if ($variantGst > 0): ?>
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200 shadow-sm" id="gstBadge">
                                Incl. <?= (float)$variantGst ?>% GST
                            </span>
                        <?php else: ?>
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200 shadow-sm hidden" id="gstBadge"></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[#F25996] text-xs font-semibold mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Minimum order: <?= $moq ?> units
                    </p>
                </div>

                <!-- Quick Specs -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Min. Order</p>
                        <p class="text-sm font-black text-gray-900"><?= $moq ?> units</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1" id="productWeightLabel">Weight / Unit</p>
                        <p class="text-sm font-black text-gray-900" id="productWeightDisplay"><?= ($initialWeight !== null && $initialWeight > 0) ? number_format($initialWeight, 3) . ' kg' : (!empty($product['weight']) ? htmlspecialchars($product['weight']) . ' kg' : '-') ?></p>
                    </div>
                </div>
                <?php else: ?>
                <!-- Not logged in -->
                <div class="bg-gradient-to-br from-[#faf6f8] to-white border border-[#ebd7e3] rounded-2xl p-6 mb-5 shadow-sm relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <!-- Decorative subtle blur in background -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#e0b0ce] rounded-full mix-blend-multiply filter blur-3xl opacity-30"></div>
                    
                    <div class="flex items-start gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-[0.95rem] mb-1">Wholesale Pricing Locked</p>
                            <p class="text-xs text-gray-500 font-medium">Create a free B2B account or log in to view our exclusive wholesale rates.</p>
                        </div>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/login?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? ('/product?id=' . $product['id'])) ?>" class="relative z-10 flex items-center justify-center gap-2 bg-[#F25996] hover:bg-[#d8407d] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm shrink-0 w-full md:w-auto">
                        Login / Register
                    </a>
                </div>
                <?php endif; ?>

                <!-- Mobile Description -->
                <?php if (!empty($product['description'])): ?>
                <div class="lg:hidden mb-5 bg-gray-50 rounded-xl p-4 border border-gray-100 overflow-hidden min-w-0 max-w-full">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-gray-600 leading-relaxed text-sm break-words overflow-hidden" style="word-break: break-word; overflow-wrap: anywhere; max-width: 100%;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- B2B Category-to-Subcategory Offer Callout -->
                <?php if (!empty($categoryOfferRule)): ?>
                <div class="mb-5 bg-gradient-to-br from-pink-500/10 via-rose-500/5 to-pink-50/80 border border-pink-200/90 rounded-2xl p-4 shadow-xs">
                    <div class="flex items-start gap-2.5">
                        <span class="text-xl flex-shrink-0">🎁</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-black text-[#8a184e] uppercase tracking-wider">B2B Combo Offer Available</h4>
                                <span class="text-[10px] font-bold text-[#F25996] bg-pink-100/90 border border-pink-200 px-2.5 py-0.5 rounded-full shadow-2xs">Tier ₹<?= number_format($categoryOfferRule['min_amount']) ?></span>
                            </div>
                            <p class="text-xs text-[#521330] leading-snug mt-1">
                                Order <strong>₹<?= number_format($categoryOfferRule['min_amount']) ?></strong> or more from <em><?= htmlspecialchars($categoryOfferRule['main_category_name']) ?></em> to unlock special low order minimums on:
                            </p>
                            <?php if (!empty($categoryOfferRule['secondary_offers'])): ?>
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <?php foreach($categoryOfferRule['secondary_offers'] as $so): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white text-[#8a184e] rounded-xl text-[11px] font-bold border border-pink-200 shadow-2xs hover:border-pink-300 transition-colors">
                                        <span>✨ <?= htmlspecialchars($so['name']) ?></span>
                                        <span class="text-[#F25996] font-semibold">(Min ₹<?= number_format($so['min_amount']) ?>)</span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form action="<?= BASE_URL ?>/cart/add" method="POST" id="productAddToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <!-- Variant Selection -->
                    <?php if (!empty($variants) && count($variants) > 0): ?>
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900">Select Variant</span>
                                <span id="selectedVariantName" class="text-xs font-bold text-[#F25996] bg-pink-50 border border-pink-200 px-2.5 py-0.5 rounded-full transition-all">1 selected</span>
                            </div>
                            <button type="button" onclick="openProductSizeChartModal(<?= (int)($product['category_id'] ?? ($category['id'] ?? 0)) ?>, <?= (int)($product['size_chart_id'] ?? 0) ?>, <?= (int)($product['id'] ?? 0) ?>, <?= (int)($product['sub_category_id'] ?? ($subCategory['id'] ?? 0)) ?>)" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f25996] hover:text-[#d94883] bg-[#fdf2f7] hover:bg-[#fce7f1] border border-[#fbaed2] px-3 py-1 rounded-full transition-all shadow-2xs hover:shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#f25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18M7 6v3m4-3v2m4-2v3m4-3v2M7 12v3m4-3v2m4-3v3m4-3v2"></path></svg>
                                <span>Size Guide</span>
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2.5" id="variantCheckboxGroup">
                            <?php 
                            $hasInStockVariant = false;
                            $firstInStockIndex = -1;
                            if (!empty($variants)) {
                                foreach($variants as $index => $variant) {
                                    if ((int)($variant['current_stock'] ?? 0) >= $moq && $firstInStockIndex === -1) {
                                        $firstInStockIndex = $index;
                                        $hasInStockVariant = true;
                                    }
                                }
                            }
                            ?>
                            <?php foreach($variants as $index => $variant): ?>
                                <?php 
                                $stock = (int)($variant['current_stock'] ?? 0); 
                                $isOutOfStock = $stock < $moq;
                                $isChecked = ($hasInStockVariant) ? ($index === $firstInStockIndex) : ($index === 0);
                                $colorCode = !empty($variant['color_code']) ? $variant['color_code'] : null;
                                ?>
                                <label class="<?= $isOutOfStock ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' ?>" <?= $isOutOfStock ? 'title="Out of stock"' : '' ?>>
                                    <input type="checkbox" name="variant_ids[]" value="<?= $variant['id'] ?>" class="hidden peer variant-checkbox" <?= $isChecked ? 'checked' : '' ?> <?= $isOutOfStock ? 'disabled' : '' ?> onchange="updateVariantSelection()" data-name="<?= htmlspecialchars($variant['name']) ?>" data-stock="<?= $stock ?>" data-moq="<?= $moq ?>" data-weight="<?= !empty($variant['weight']) ? (float)$variant['weight'] : '' ?>" data-color="<?= htmlspecialchars($colorCode ?? '') ?>">
                                    <div class="min-w-[72px] sm:min-w-[85px] px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border-2 border-gray-200 text-gray-800 bg-white transition-all flex flex-col items-center justify-center
                                        peer-checked:border-[#F25996] peer-checked:bg-pink-50/70 peer-checked:text-[#F25996] peer-checked:shadow-xs
                                        <?= !$isOutOfStock ? 'hover:border-pink-300 hover:bg-pink-50/20' : '' ?>
                                        peer-disabled:bg-gray-50 peer-disabled:text-gray-400 peer-disabled:border-gray-100">
                                        <div class="flex items-center gap-1.5 sm:gap-2">
                                            <?php if ($colorCode): ?>
                                                <span class="w-3 h-3 rounded-full border border-gray-300 shadow-2xs shrink-0" style="background-color: <?= htmlspecialchars($colorCode) ?>"></span>
                                            <?php endif; ?>
                                            <span class="font-bold text-xs sm:text-sm leading-tight"><?= htmlspecialchars($variant['name']) ?></span>
                                        </div>
                                        <?php 
                                        $badgeText = !$isOutOfStock ? $stock . ' in stock' : 'Out of stock';
                                        $badgeColor = !$isOutOfStock ? 'text-emerald-600 peer-checked:text-[#F25996]' : 'text-red-400';
                                        ?>
                                        <span class="text-[9px] sm:text-[10px] font-semibold <?= $badgeColor ?> mt-0.5"><?= $badgeText ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p id="variantWarning" class="text-xs text-red-500 mt-2 hidden font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            Please select at least one variant.
                        </p>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="variant_ids[]" value="">
                    <?php endif; ?>



                <!-- Quantity + Add to Cart Row -->
                <?php 
                $isProductOutOfStock = empty($variants) && (int)($product['stock_quantity'] ?? 0) < $moq;
                $disableAddToCart = !$canAddToCart || $isProductOutOfStock || (!empty($variants) && !isset($hasInStockVariant) ? false : (isset($hasInStockVariant) && !$hasInStockVariant));
                $initialTotalAmt = round(((float)($displayPrice ?? ($product['wholesale_price'] ?? 0))) * $moq, 2);
                ?>
                <div class="hidden md:flex items-center gap-3 mb-3 flex-wrap sm:flex-nowrap">
                    <!-- Quantity Control -->
                    <div class="flex items-center border-2 border-[#F25996] rounded-full bg-white overflow-hidden shrink-0 <?= $disableAddToCart ? 'opacity-50 pointer-events-none' : '' ?>">
                        <button type="button" id="qtyMinusBtn" onclick="updateQty(-<?= $moq ?>)" class="w-11 sm:w-12 h-11 sm:h-12 flex items-center justify-center text-[#F25996] text-xl hover:bg-pink-50 focus:outline-none font-black transition-colors" <?= $disableAddToCart ? 'disabled' : '' ?>>−</button>
                        <div class="h-5 w-[1.5px] bg-[#F25996] opacity-30"></div>
                        <input type="text" id="qtyInput" name="quantity" value="<?= $moq ?>" readonly class="w-12 sm:w-14 text-center text-base sm:text-lg font-black text-[#F25996] h-11 sm:h-12 focus:outline-none bg-white p-0">
                        <div class="h-5 w-[1.5px] bg-[#F25996] opacity-30"></div>
                        <button type="button" id="qtyPlusBtn" onclick="updateQty(<?= $moq ?>)" class="w-11 sm:w-12 h-11 sm:h-12 flex items-center justify-center text-[#F25996] text-xl hover:bg-pink-50 focus:outline-none font-black transition-colors" <?= $disableAddToCart ? 'disabled' : '' ?>>+</button>
                    </div>

                    <!-- Right Side Add to Cart Button with Quantity-based Dynamic Total Amount -->
                    <button type="submit" <?= $disableAddToCart ? 'disabled' : '' ?> id="addToCartBtn"
                        class="flex-1 min-w-[200px] text-white py-3 sm:py-3.5 px-4 sm:px-6 rounded-2xl font-black text-sm sm:text-base flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#F25996]/30 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#d8407d] active:scale-[0.99] bg-[#F25996]"
                        style="background: #F25996;">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Add to Cart</span>
                        <?php if ($canAddToCart): ?>
                        <span class="text-white/60 font-normal">|</span>
                        <span id="addToCartTotalAmount" class="font-black">₹<?= format_price($initialTotalAmt) ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <div class="hidden md:flex items-center gap-2 mb-3">
                    <?php if (empty($variants)): ?>
                        <?php 
                        $pStock = (int)($product['stock_quantity'] ?? 0);
                        $pBadgeClass = $pStock >= $moq ? 'bg-teal-50 text-teal-700 border border-teal-100' : 'bg-red-50 text-red-600 border border-red-100';
                        ?>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full <?= $pBadgeClass ?>"><?= $pStock >= $moq ? $pStock . ' in stock' : 'Out of stock' ?></span>
                    <?php endif; ?>
                    <?php if (!$canAddToCart): ?>
                        <span class="text-xs text-red-500 font-bold">Login required</span>
                    <?php endif; ?>
                </div>

                <p id="maxStockWarning" class="text-xs text-orange-500 font-semibold mb-3 hidden">⚠ Maximum available stock reached.</p>
            </form>

            <!-- Trust Badges -->
            <div class="hidden md:flex gap-3 mt-4 text-xs text-gray-500">
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Secure Checkout</span></div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>B2B Pricing</span></div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Fast Dispatch</span></div>
            </div>

            <?php if (!empty($vendorBrandName)): ?>
            <!-- Seller / Brand Information Card -->
            <div class="mt-4 sm:mt-5 p-3.5 sm:p-4 bg-gradient-to-r from-gray-50 to-indigo-50/30 rounded-2xl border border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0">
                        <?= strtoupper(substr($vendorBrandName, 0, 2)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sold by / Brand</div>
                        <div class="text-sm font-bold text-gray-900 capitalize flex items-center gap-1.5 flex-wrap">
                            <span><?= htmlspecialchars($vendorBrandName) ?></span>
                            <?php if (!empty($product['unique_vendor_id'])): ?>
                                <span class="text-[10px] font-mono font-semibold bg-gray-200/80 text-gray-700 px-1.5 py-0.5 rounded"><?= htmlspecialchars($product['unique_vendor_id']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="flex items-center sm:self-auto self-start">
                    <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Gold Supplier (Fast SLA Dispatch)
                    </span>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>

        <?php
        $rawCustomFields = $product['custom_fields'] ?? [];
        if (is_string($rawCustomFields)) {
            $productCustomTabs = json_decode($rawCustomFields, true) ?: [];
        } elseif (is_array($rawCustomFields)) {
            $productCustomTabs = $rawCustomFields;
        } else {
            $productCustomTabs = [];
        }

        // Determine title for Tab 2 ("How to play" for toys/games/kids/wooden products or "How to use")
        $catName = strtolower($category['name'] ?? $product['category_name'] ?? '');
        $subCatName = strtolower($subCategory['name'] ?? $product['sub_category_name'] ?? '');
        $prodName = strtolower($product['name'] ?? '');
        
        $isToyContext = (
            strpos($catName, 'toy') !== false ||
            strpos($catName, 'play') !== false ||
            strpos($catName, 'kid') !== false ||
            strpos($catName, 'game') !== false ||
            strpos($catName, 'wood') !== false ||
            strpos($subCatName, 'toy') !== false ||
            strpos($subCatName, 'play') !== false ||
            strpos($subCatName, 'kid') !== false ||
            strpos($prodName, 'toy') !== false ||
            strpos($prodName, 'play') !== false ||
            strpos($prodName, 'puzzle') !== false ||
            strpos($prodName, 'alphabet') !== false ||
            strpos($prodName, 'board') !== false ||
            strpos($prodName, 'game') !== false
        );

        $howToUseTabTitle = $isToyContext ? 'How to play' : 'How to use';

        // Filter custom fields to avoid duplicating standard tab names
        $filteredCustomTabs = [];
        if (!empty($productCustomTabs)) {
            foreach ($productCustomTabs as $cf) {
                $cfName = trim($cf['name'] ?? '');
                $cfNameLower = strtolower($cfName);
                if (empty($cfName)) continue;
                if (in_array($cfNameLower, ['description', 'how to use', 'how to play', 'why choose', 'why choose us'])) {
                    continue;
                }
                $filteredCustomTabs[] = $cf;
            }
        }
        ?>
        <!-- ===== PRODUCT INFORMATION TABS (Description, How to play, Why Choose, etc.) ===== -->
        <div class="mt-8 mb-12 bg-white rounded-3xl border border-gray-200/80 p-5 sm:p-8 md:p-10 shadow-sm">
            
            <!-- Tab Navigation Header (Smooth Horizontal Scrollable, Left Aligned) -->
            <div class="border-b border-gray-200 pb-0 mb-8 overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar hide-scrollbar scrollbar-none flex justify-start" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                <nav class="flex items-center space-x-4 sm:space-x-8 min-w-max px-1" aria-label="Product Information Tabs">
                    <!-- 1. Description Tab (Default Active) -->
                    <button type="button" 
                            onclick="switchProductInfoTab('tab-description', this)" 
                            class="product-info-tab-btn group shrink-0 snap-start pb-3.5 px-3 border-b-2 border-gray-900 text-gray-900 font-bold text-sm sm:text-base flex items-center gap-2 transition-all duration-200 outline-none cursor-pointer">
                        <span class="w-4 h-4 rounded-full border border-current flex items-center justify-center text-[10px] font-bold italic shrink-0">i</span>
                        <span class="whitespace-nowrap">Description</span>
                    </button>


                    <!-- 4. Dynamic Additional Custom Field Tabs -->
                    <?php if (!empty($filteredCustomTabs)): ?>
                        <?php foreach ($filteredCustomTabs as $idx => $tab): ?>
                            <button type="button" 
                                    onclick="switchProductInfoTab('tab-custom-<?= $idx ?>', this)" 
                                    class="product-info-tab-btn group shrink-0 snap-start pb-3.5 px-3 border-b-2 border-transparent text-gray-500 hover:text-gray-900 font-medium text-sm sm:text-base flex items-center gap-2 transition-all duration-200 outline-none cursor-pointer">
                                <span class="w-4 h-4 rounded-full border border-gray-400 group-hover:border-gray-900 flex items-center justify-center text-[10px] font-bold italic shrink-0">i</span>
                                <span class="whitespace-nowrap"><?= htmlspecialchars($tab['name']) ?></span>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Tab Content Panels -->
            <div class="product-info-panels min-h-[140px]">
                
                <!-- 1. Description Panel (Default Active) -->
                <div id="tab-description" class="product-info-panel block transition-opacity duration-300">
                    <div class="max-w-4xl mx-auto text-left">
                        <h3 class="text-lg sm:text-2xl font-black text-gray-900 mb-5 tracking-tight text-left">
                            About <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        <div class="text-gray-700 text-sm sm:text-base leading-relaxed space-y-3.5 text-left overflow-hidden min-w-0 max-w-full">
                            <?php 
                            $descText = trim($product['description'] ?? '');
                            if (!empty($descText)) {
                                $paragraphs = explode("\n", $descText);
                                foreach ($paragraphs as $p) {
                                    $p = trim($p);
                                    if ($p !== '') {
                                        echo '<p class="leading-relaxed text-gray-700 break-words" style="word-break: break-word; overflow-wrap: anywhere; max-width: 100%;">' . htmlspecialchars($p) . '</p>';
                                    }
                                }
                            } else {
                                echo '<p class="text-gray-400 italic">No description provided for this product.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>


                <!-- 4. Dynamic Custom Field Panels -->
                <?php if (!empty($filteredCustomTabs)): ?>
                    <?php foreach ($filteredCustomTabs as $idx => $tab): ?>
                        <div id="tab-custom-<?= $idx ?>" class="product-info-panel hidden transition-opacity duration-300">
                            <div class="max-w-4xl mx-auto text-left">
                                <h3 class="text-lg sm:text-2xl font-black text-gray-900 mb-5 tracking-tight text-left">
                                    <?= htmlspecialchars($tab['name']) ?>
                                </h3>
                                <div class="text-gray-700 text-sm sm:text-base leading-relaxed space-y-3.5 text-left">
                                    <?php 
                                    $valText = trim($tab['value'] ?? '');
                                    if (!empty($valText)) {
                                        $valParagraphs = explode("\n", $valText);
                                        foreach ($valParagraphs as $vp) {
                                            $vp = trim($vp);
                                            if ($vp !== '') {
                                                echo '<p class="leading-relaxed text-gray-700">' . htmlspecialchars($vp) . '</p>';
                                            }
                                        }
                                    } else {
                                        echo '<p class="text-gray-400 italic">No content available for this section.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

        <!-- ===== MOBILE STICKY ADD TO CART BAR ===== -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-xl px-3 py-2.5 safe-area-pb">
            <div class="flex items-center justify-between gap-2.5">
                <!-- Qty control -->
                <div class="flex items-center border-2 border-[#F25996] rounded-full bg-white overflow-hidden <?= $disableAddToCart ? 'opacity-50 pointer-events-none' : '' ?>">
                    <button type="button" onclick="updateQty(-<?= $moq ?>)" class="w-8 h-8 flex items-center justify-center text-[#F25996] text-base hover:bg-pink-50 active:bg-pink-100 font-black">&minus;</button>
                    <div class="h-3.5 w-[1.5px] bg-[#F25996] opacity-30"></div>
                    <span id="mobileQtyDisplay" class="w-8 text-center text-xs font-black text-[#F25996] h-8 flex items-center justify-center"><?= $moq ?></span>
                    <div class="h-3.5 w-[1.5px] bg-[#F25996] opacity-30"></div>
                    <button type="button" onclick="updateQty(<?= $moq ?>)" class="w-8 h-8 flex items-center justify-center text-[#F25996] text-base hover:bg-pink-50 active:bg-pink-100 font-black">+</button>
                </div>
                <!-- Submit with dynamic calculated amount -->
                <button type="button" <?= $disableAddToCart ? 'disabled' : '' ?> 
                    onclick="document.getElementById('productAddToCartForm').requestSubmit()" 
                    id="mobileAddToCartBtn"
                    class="flex-1 h-9 px-3 text-white rounded-lg font-bold text-xs flex items-center <?= $canAddToCart ? 'justify-between' : 'justify-center' ?> transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed active:opacity-80"
                    style="background: #F25996;">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Add to Cart</span>
                    </div>
                    <?php if ($canAddToCart): ?>
                    <span id="mobileBtnTotal" class="font-extrabold text-xs tracking-tight bg-black/20 px-2 py-0.5 rounded ml-1 whitespace-nowrap">₹<?= number_format($displayPrice * $moq, 2) ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Frequently Bought Together -->
        <?php if (!empty($relatedProducts)): ?>
        <style>

        @media (max-width: 767px) {
            .fbt-section .fbt-track {
                gap: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }
            .fbt-section .product-card {
                width: calc(50% - 0.25rem) !important;
                min-width: calc(50% - 0.25rem) !important;
                max-width: calc(50% - 0.25rem) !important;
                flex-shrink: 0 !important;
            }
        }
        </style>
        <div class="fbt-section mt-10 md:mt-16 border-t border-gray-200 pt-8 md:pt-16">
            <h2 class="text-lg md:text-2xl font-black text-gray-900 mb-3 md:mb-6 px-1 md:px-0">Frequently Bought Together</h2>
            <div class="fbt-track flex overflow-x-auto pb-4 px-1 md:px-0 gap-2.5 sm:gap-4 snap-x snap-mandatory sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 sm:overflow-visible style-scrollbars items-stretch">
                <?php foreach($relatedProducts as $relProduct): ?>
                    <?php
                    $relPrimaryImage = !empty($relProduct['primary_image']) ? get_image_url($relProduct['primary_image']) : 'https://placehold.co/300x300/f3f4f6/9ca3af?text=No+Image';
                    $relMoq = $relProduct['moq_override'] ?? $relProduct['category_moq'] ?? 1;
                    
                    $relBasePrice = (isset($relProduct['base_price']) && $relProduct['base_price'] > 0) ? (float)$relProduct['base_price'] : (float)($relProduct['wholesale_price'] ?? 0);
                    $relDiscountPrice = !empty($relProduct['discount_price']) && (float)$relProduct['discount_price'] > 0 ? (float)$relProduct['discount_price'] : null;
                    $relDisplayPrice = $relDiscountPrice ?? $relBasePrice;
                    ?>
                    <div class="product-card h-full w-[calc(50%-0.25rem)] min-w-[calc(50%-0.25rem)] sm:w-full sm:min-w-0 snap-start bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow group flex flex-col relative shrink-0 sm:shrink">
                        <!-- Wishlist Button -->
                        <?php if ($canAddToCart || \Core\Session::get('user_id')): ?>
                        <?php 
                            $inWishlist = false;
                            if (!empty($wishlistIds) && is_array($wishlistIds)) {
                                $inWishlist = in_array($relProduct['id'], $wishlistIds);
                            } elseif (!empty($wishlistItems) && is_array($wishlistItems)) {
                                $inWishlist = in_array($relProduct['id'], array_column($wishlistItems, 'product_id')) || in_array($relProduct['id'], array_column($wishlistItems, 'id'));
                            }
                        ?>
                        <button type="button" onclick="toggleWishlist(<?= $relProduct['id'] ?>, this); event.stopPropagation();" data-wishlist-btn-id="<?= $relProduct['id'] ?>" class="wishlist-btn <?= $inWishlist ? 'is-active' : '' ?>" title="Wishlist">
                            <svg class="<?= $inWishlist ? 'text-[#F25996] fill-[#F25996] is-active' : 'text-gray-400 fill-none' ?>" fill="<?= $inWishlist ? '#F25996' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <a href="<?= BASE_URL ?>/product?id=<?= $relProduct['id'] ?>" class="product-image-wrap block w-full bg-gray-100 overflow-hidden shrink-0 aspect-square relative">
                            <?php 
                            $relDiscPct = ($relDiscountPrice && $relBasePrice > $relDiscountPrice) ? round((($relBasePrice - $relDiscountPrice) / $relBasePrice) * 100) : 0;
                            if ($canAddToCart && $relDiscPct > 0) {
                                echo render_discount_starburst($relDiscPct, 'transition-transform duration-300 group-hover:scale-105');
                            }
                            ?>
                            <img src="<?= htmlspecialchars($relPrimaryImage) ?>" alt="<?= htmlspecialchars($relProduct['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" decoding="async" style="object-fit: cover; width: 100%; height: 100%; display: block;">
                        </a>
                        
                        <!-- Product Details -->
                        <div class="product-card-body p-3 md:p-4 flex flex-col flex-1 bg-white relative z-10">
                            <a href="<?= BASE_URL ?>/product?id=<?= $relProduct['id'] ?>" class="product-link block mb-1">
                                <h3 class="product-name font-bold leading-snug line-clamp-2"><?= htmlspecialchars($relProduct['name']) ?></h3>
                            </a>
                            
                            <?php if ($canAddToCart): ?>
                                <div class="mt-auto flex flex-col justify-end">
                                    <!-- Selling Price, Strikethrough (Same Row) -->
                                    <div class="product-price-row flex items-baseline gap-1.5 flex-wrap mb-1">
                                        <span class="product-price font-extrabold tracking-tight">₹<?= format_price($relDisplayPrice) ?></span>
                                        <?php if ($relDiscountPrice && $relBasePrice > $relDiscountPrice): ?>
                                            <span class="product-orig-price font-bold text-gray-400 line-through">₹<?= format_price($relBasePrice) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Minimum Order Count -->
                                    <p class="product-moq-row font-semibold flex items-center gap-1 whitespace-nowrap overflow-hidden text-ellipsis">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span>Min. Order: <?= htmlspecialchars($relMoq) ?> Pcs</span>
                                    </p>

                                    <!-- CTA Button -->
                                    <form action="<?= BASE_URL ?>/cart/add" method="POST" onsubmit="ajaxAddToCart(event, this)">
                                        <input type="hidden" name="product_id" value="<?= $relProduct['id'] ?>">
                                        <input type="hidden" name="quantity" value="<?= htmlspecialchars($relMoq) ?>">
                                        <button type="submit" class="product-btn w-full font-bold transition-colors flex items-center justify-center gap-1 hover:opacity-90 shadow-sm whitespace-nowrap">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span class="whitespace-nowrap">Add to Cart</span>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="mt-auto pt-2 md:pt-3 border-t border-gray-100">
                                    <a href="<?= BASE_URL ?>/login?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? ('/product?id=' . $product['id'])) ?>" class="flex items-center gap-1.5 text-[#F25996] font-semibold hover:underline text-xs md:text-sm">
                                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Register to view price
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div>
    
    <!-- Customer Reviews Section -->
    <?php 
    $reviews = $reviews ?? [];
    $totalReviews = count($reviews);
    $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($reviews as $rev) {
        $r = (int)round($rev['rating'] ?? 5);
        if ($r < 1) $r = 1;
        if ($r > 5) $r = 5;
        $ratingCounts[$r]++;
    }
    $avgRating = $totalReviews > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;
    ?>
    <div id="customer-reviews" class="w-full max-w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 mb-8 sm:mb-12">
        <div class="rounded-2xl sm:rounded-3xl shadow-sm overflow-hidden" style="background-color: #fce7f1; border: 1.5px solid #fbcfe8;">

            <!-- Mobile View Header -->
            <div class="sm:hidden p-4 pb-2 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Customer Reviews</h2>
                    <p class="text-xs text-gray-400 font-normal mt-0.5"><?= $totalReviews ?> verified review<?= $totalReviews !== 1 ? 's' : '' ?></p>
                </div>
                <?php if ($totalReviews > 1): ?>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="slideReviewPrev()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition shadow-sm active:scale-95" title="Previous">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" onclick="slideReviewNext()" class="w-8 h-8 flex items-center justify-center rounded-full bg-[#F25996] hover:bg-[#d8407d] text-white transition shadow-sm active:scale-95" title="Next">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>



            <!-- Single Row Reviews Slider Section (Left: Rating Summary, Right: Review Cards) -->
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 p-3 sm:p-8">
                
                <?php if (!empty($reviews)): ?>
                <!-- 1. Customer Reviews Rating Summary Card (Fixed on Desktop/Tablet Left Side) -->
                <div class="hidden sm:block w-[320px] flex-shrink-0">
                    <div class="bg-white rounded-2xl p-5 border border-pink-100 h-full flex flex-col justify-center text-center shadow-xs">
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Customer Reviews</div>
                            <!-- Star Emblem with Rating -->
                            <div class="relative w-28 h-28 mx-auto flex items-center justify-center my-2 flex-shrink-0">
                                <svg class="w-full h-full text-[#F25996] drop-shadow-xs" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.784 1.399 8.163L12 18.896l-7.333 3.861 1.399-8.163-5.934-5.784 8.2-1.192zm0 0"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pt-2">
                                    <span class="text-3xl font-black text-white leading-none"><?= number_format($avgRating, 1) ?></span>
                                    <span class="text-[10px] font-bold text-white/90 tracking-tight mt-1">Out of 5</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 font-medium mt-2 mb-5">Based on <?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?></p>
                        </div>

                        <!-- 5-Star Breakdown Bars -->
                        <div class="space-y-2 w-full text-left px-2">
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                <?php 
                                    $count = $ratingCounts[$star] ?? 0;
                                    $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                ?>
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="flex text-amber-400 w-16 justify-end text-xs gap-0.5 flex-shrink-0">
                                        <?php for ($s = 1; $s <= $star; $s++): ?>★<?php endfor; ?>
                                    </div>
                                    <div class="flex-1 h-2.5 bg-pink-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#F25996] rounded-full transition-all duration-500" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span class="w-4 text-right text-xs font-semibold text-gray-500 flex-shrink-0"><?= $count ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- RIGHT COLUMN: Reviews Slider -->
                <div class="flex-1 min-w-0">
                    <!-- Desktop Slider Header Controls -->
                    <div class="hidden sm:flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900">Buyer Reviews</h3>
                            <?php if ($totalReviews > 0): ?>
                                <span class="bg-gray-100 text-gray-600 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                    <span id="review-current-slide-num">1</span> / <?= $totalReviews ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($totalReviews > 1): ?>
                        <div class="flex items-center gap-2">
                            <button type="button" id="reviewSliderPrevBtn" onclick="slideReviewPrev()" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition shadow-sm active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none" title="Previous Review">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" id="reviewSliderNextBtn" onclick="slideReviewNext()" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-[#F25996] hover:bg-[#d8407d] text-white transition shadow-sm active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none" title="Next Review">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <div class="text-center py-12 px-4 bg-[#fafafa] rounded-2xl border border-gray-100">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <p class="text-gray-500 font-medium text-xs sm:text-sm">No customer reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php else: ?>
                        <!-- Slider Track -->
                        <div id="review-slider-track" class="flex overflow-x-auto gap-3 sm:gap-4 pb-3 pt-1 snap-x snap-mandatory scroll-smooth touch-pan-x hide-scrollbar select-none cursor-grab">
                            
                            <div class="review-slide-card sm:hidden flex-none w-[85vw] max-w-[320px]">
                            <div class="bg-white rounded-2xl p-4 border border-pink-100 h-full flex flex-col justify-between text-center shadow-xs">
                                <div>
                                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Customer Reviews</div>
                                    <!-- Star Emblem with Rating -->
                                    <div class="relative w-24 h-24 mx-auto flex items-center justify-center my-1 flex-shrink-0">
                                        <svg class="w-full h-full text-[#F25996] drop-shadow-xs" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.784 1.399 8.163L12 18.896l-7.333 3.861 1.399-8.163-5.934-5.784 8.2-1.192zm0 0"/>
                                        </svg>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pt-1.5">
                                            <span class="text-2xl font-black text-white leading-none"><?= number_format($avgRating, 1) ?></span>
                                            <span class="text-[9px] font-bold text-white/90 tracking-tight mt-0.5">Out of 5</span>
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-500 font-medium mt-1 mb-3">Based on <?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?></p>
                                </div>

                                <!-- 5-Star Breakdown Bars -->
                                <div class="space-y-1.5 w-full text-left">
                                    <?php for ($star = 5; $star >= 1; $star--): ?>
                                        <?php 
                                            $count = $ratingCounts[$star] ?? 0;
                                            $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                        ?>
                                        <div class="flex items-center gap-2 text-xs">
                                            <div class="flex text-amber-400 w-16 justify-end text-[11px] gap-0.5 flex-shrink-0">
                                                <?php for ($s = 1; $s <= $star; $s++): ?>★<?php endfor; ?>
                                            </div>
                                            <div class="flex-1 h-2 bg-pink-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#F25996] rounded-full transition-all duration-500" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                            <span class="w-4 text-right text-xs font-semibold text-gray-500 flex-shrink-0"><?= $count ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Buyer Review Cards -->
                        <?php foreach ($reviews as $idx => $review): ?>
                            <?php
                                $initials = strtoupper(substr($review['reviewer_name'] ?? 'U', 0, 1));
                                $avatarColors = ['bg-amber-100 text-amber-800', 'bg-violet-100 text-violet-800', 'bg-blue-100 text-blue-800', 'bg-emerald-100 text-emerald-800', 'bg-rose-100 text-rose-800'];
                                $avatarClass  = $avatarColors[ord($initials) % count($avatarColors)];
                                $photos = !empty($review['photos']) ? (is_array($review['photos']) ? $review['photos'] : json_decode($review['photos'], true)) : [];
                                
                                $lines = explode("\n", trim($review['review_text'] ?? ''));
                                $hasHeadline = count($lines) > 1 && strlen($lines[0]) < 50;
                                $headline = $hasHeadline ? $lines[0] : '';
                                $bodyContent = $hasHeadline ? implode("\n", array_slice($lines, 1)) : ($review['review_text'] ?? '');
                            ?>
                            <!-- Review Card (Marakathai Mobile View Style) -->
                            <div class="review-slide-card flex-none w-[85vw] max-w-[320px] sm:w-[300px] lg:w-[calc((100%-32px)/3)]">
                                <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5 h-full flex flex-col shadow-xs hover:shadow-md transition-shadow">
                                    <!-- Top Row: Avatar + Name + Verified Purchase Badge + Stars + Date -->
                                    <div class="flex items-start gap-3 mb-2.5">
                                        <div class="w-10 h-10 rounded-full <?= $avatarClass ?> flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                                            <?= $initials ?>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-gray-900 text-sm leading-snug truncate"><?= htmlspecialchars($review['reviewer_name']) ?></span>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                    <svg class="w-2.5 h-2.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Verified Purchase
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="flex text-amber-400 text-xs gap-0.5">
                                                    <?php for($i=1; $i<=5; $i++): ?>
                                                        <svg class="w-3.5 h-3.5 <?= $i <= $review['rating'] ? 'fill-current text-amber-400' : 'fill-current text-gray-200' ?>" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="text-xs text-gray-400"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Headline if present -->
                                    <?php if ($headline): ?>
                                        <h4 class="font-bold text-gray-900 text-sm mt-1 leading-snug break-words"><?= htmlspecialchars($headline) ?></h4>
                                    <?php endif; ?>

                                    <!-- Body text -->
                                    <?php $textToDisplay = $bodyContent ?: ($review['review_text'] ?? ''); ?>
                                    <?php if (!empty($textToDisplay)): ?>
                                        <p class="text-gray-600 text-xs sm:text-sm leading-relaxed flex-1 mt-1.5 mb-3 break-words"><?= nl2br(htmlspecialchars($textToDisplay)) ?></p>
                                    <?php else: ?>
                                        <div class="flex-1"></div>
                                    <?php endif; ?>

                                    <!-- Attached Photos Row -->
                                    <?php if (!empty($photos)): ?>
                                        <div class="flex gap-2 mt-auto flex-wrap pt-1 mb-2">
                                            <?php foreach (array_slice($photos, 0, 4) as $photo): ?>
                                                <button type="button" onclick="openReviewPhoto('<?= get_image_url($photo) ?>')" class="block group">
                                                    <img src="<?= get_image_url($photo) ?>" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-gray-200 group-hover:border-emerald-400 group-hover:scale-105 transition-all duration-200 shadow-2xs" alt="Review photo">
                                                </button>
                                            <?php endforeach; ?>
                                            <?php if (count($photos) > 4): ?>
                                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-xl flex items-center justify-center text-xs text-gray-500 font-bold border border-gray-200">+<?= count($photos) - 4 ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Card Footer: Helpfulness Voting -->
                                    <?php 
                                        $votedReviewsSession = \Core\Session::get('voted_reviews') ?: [];
                                        $votedChoice = $votedReviewsSession[$review['id']] ?? null;
                                    ?>
                                    <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between mt-auto text-xs">
                                        <span class="text-gray-400 font-medium text-[10px] sm:text-[11px]">Helpful?</span>
                                        <div class="flex items-center gap-1.5" data-review-id="<?= $review['id'] ?>" data-voted="<?= htmlspecialchars($votedChoice ?? '') ?>">
                                            <button type="button" data-vote-type="yes" onclick="voteReviewHelpful(<?= $review['id'] ?>, 'yes', this)" 
                                                class="btn-vote-yes inline-flex items-center gap-1 px-2.5 py-1 <?= ($votedChoice === 'yes') ? 'bg-emerald-100 text-emerald-950 border-2 border-emerald-500 font-black shadow-xs' : 'bg-gray-50 hover:bg-emerald-50 text-gray-700 border border-gray-200 font-bold' ?> rounded-full text-[10px] sm:text-[11px] transition-all active:scale-95 cursor-pointer">
                                                <span>👍</span> <span class="vote-count-yes"><?= (int)($review['helpful_yes'] ?? 0) ?></span>
                                            </button>
                                            <button type="button" data-vote-type="no" onclick="voteReviewHelpful(<?= $review['id'] ?>, 'no', this)" 
                                                class="btn-vote-no inline-flex items-center gap-1 px-2.5 py-1 <?= ($votedChoice === 'no') ? 'bg-rose-100 text-rose-950 border-2 border-rose-500 font-black shadow-xs' : 'bg-gray-50 hover:bg-rose-50 text-gray-700 border border-gray-200 font-bold' ?> rounded-full text-[10px] sm:text-[11px] transition-all active:scale-95 cursor-pointer">
                                                <span>👎</span> <span class="vote-count-no"><?= (int)($review['helpful_no'] ?? 0) ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Dot indicators -->
                    <div id="review-dots" class="flex justify-center gap-1.5 pb-2 pt-2"></div>
                </div> <!-- Close right column -->
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Photo Lightbox -->
    <div id="review-photo-lightbox" class="fixed inset-0 z-[500] hidden items-center justify-center bg-black/80 backdrop-blur-sm" onclick="closeReviewPhoto()">
        <img id="review-photo-img" src="" class="max-h-[90vh] max-w-[90vw] rounded-2xl shadow-2xl object-contain" alt="Review photo">
        <button onclick="closeReviewPhoto()" class="absolute top-4 right-4 text-white bg-white/20 hover:bg-white/30 rounded-full p-2 backdrop-blur-sm transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <script>
    let activeReviewIndex = 0;
    const totalReviewSlides = <?= $totalReviews ?>;

    function getReviewTrack() {
        return document.getElementById('review-slider-track');
    }

    function getVisibleCards() {
        const track = getReviewTrack();
        if (!track) return [];
        return Array.from(track.querySelectorAll('.review-slide-card')).filter(c => c.offsetWidth > 0);
    }

    function slideReviewPrev() {
        const track = getReviewTrack();
        if (!track) return;
        const cards = getVisibleCards();
        if (activeReviewIndex > 0) {
            goToReviewSlide(activeReviewIndex - 1);
        } else {
            const cardWidth = cards[0]?.offsetWidth || 300;
            track.scrollBy({ left: -(cardWidth + 16), behavior: 'smooth' });
        }
    }

    function slideReviewNext() {
        const track = getReviewTrack();
        if (!track) return;
        const cards = getVisibleCards();
        if (activeReviewIndex < cards.length - 1) {
            goToReviewSlide(activeReviewIndex + 1);
        } else {
            const cardWidth = cards[0]?.offsetWidth || 300;
            track.scrollBy({ left: (cardWidth + 16), behavior: 'smooth' });
        }
    }

    function goToReviewSlide(index) {
        const track = getReviewTrack();
        if (!track) return;
        const cards = getVisibleCards();
        if (cards[index]) {
            cards[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            updateSliderDots(index, cards.length);
        }
    }

    function updateSliderDots(activeIndex, totalSlides) {
        activeReviewIndex = activeIndex;
        
        const counter = document.getElementById('review-current-slide-num');
        if (counter) counter.innerText = (activeIndex + 1);

        const dots = document.querySelectorAll('.review-slider-dot');
        dots.forEach((dot, idx) => {
            if (idx === activeIndex) {
                dot.className = 'review-slider-dot h-2 rounded-full focus:outline-none transition-all review-slider-active-dot';
            } else {
                dot.className = 'review-slider-dot h-2 w-2 rounded-full bg-gray-200 hover:bg-gray-300 focus:outline-none transition-all';
            }
        });

        const prevBtn = document.getElementById('reviewSliderPrevBtn');
        const nextBtn = document.getElementById('reviewSliderNextBtn');
        if (prevBtn) prevBtn.disabled = (activeIndex <= 0);
        if (nextBtn) nextBtn.disabled = (activeIndex >= (totalSlides || <?= $totalReviews ?>) - 1);
    }

    // Touch and Scroll Listener to Sync Active Slide
    document.addEventListener('DOMContentLoaded', function() {
        const track = getReviewTrack();
        if (track) {
            let isScrolling = null;
            track.addEventListener('scroll', function() {
                clearTimeout(isScrolling);
                isScrolling = setTimeout(function() {
                    const cards = getVisibleCards();
                    const trackLeft = track.getBoundingClientRect().left;
                    let closestIdx = 0;
                    let minDiff = Infinity;

                    cards.forEach((card, idx) => {
                        const cardLeft = card.getBoundingClientRect().left;
                        const diff = Math.abs(cardLeft - trackLeft);
                        if (diff < minDiff) {
                            minDiff = diff;
                            closestIdx = idx;
                        }
                    });

                    updateSliderDots(closestIdx, cards.length);
                }, 60);
            }, { passive: true });

            // Mouse Drag to Scroll (for desktop slider interaction)
            let isDown = false;
            let startX;
            let scrollLeft;

            track.addEventListener('mousedown', (e) => {
                isDown = true;
                track.classList.add('cursor-grabbing');
                track.classList.remove('cursor-grab');
                startX = e.pageX - track.offsetLeft;
                scrollLeft = track.scrollLeft;
            });

            track.addEventListener('mouseleave', () => {
                isDown = false;
                track.classList.remove('cursor-grabbing');
                track.classList.add('cursor-grab');
            });

            track.addEventListener('mouseup', () => {
                isDown = false;
                track.classList.remove('cursor-grabbing');
                track.classList.add('cursor-grab');
            });

            track.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - track.offsetLeft;
                const walk = (x - startX) * 1.5;
                track.scrollLeft = scrollLeft - walk;
            });
            // Update prev/next buttons
            const prev = document.getElementById('rev-prev');
            const next = document.getElementById('rev-next');
            if (prev) prev.disabled = current === 0;
            if (next) next.disabled = current >= maxIndex();

            const prevM = document.getElementById('rev-prev-mobile');
            const nextM = document.getElementById('rev-next-mobile');
            if (prevM) {
                prevM.disabled = current === 0;
                if (current === 0) {
                    prevM.classList.add('opacity-40');
                    prevM.classList.remove('bg-[#0f172a]', 'text-white');
                    prevM.classList.add('bg-slate-100', 'text-gray-400');
                } else {
                    prevM.classList.remove('opacity-40');
                    prevM.classList.remove('bg-slate-100', 'text-gray-400');
                    prevM.classList.add('bg-[#0f172a]', 'text-white');
                }
            }
            if (nextM) {
                nextM.disabled = current >= maxIndex();
                if (current >= maxIndex()) {
                    nextM.classList.add('opacity-40');
                    nextM.classList.remove('bg-[#0f172a]', 'text-white');
                    nextM.classList.add('bg-slate-100', 'text-gray-400');
                } else {
                    nextM.classList.remove('opacity-40');
                    nextM.classList.remove('bg-slate-100', 'text-gray-400');
                    nextM.classList.add('bg-[#0f172a]', 'text-white');
                }
            }
        }
    });

    function openReviewPhoto(url) {
        const lb = document.getElementById('review-photo-lightbox');
        document.getElementById('review-photo-img').src = url;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeReviewPhoto() {
        const lb = document.getElementById('review-photo-lightbox');
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function applyVotedStyle(container, type) {
        if (!container) return;
        const btnYes = container.querySelector('.btn-vote-yes');
        const btnNo = container.querySelector('.btn-vote-no');
        
        const defaultYesClass = 'btn-vote-yes inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 hover:bg-emerald-50 text-gray-700 rounded-full border border-gray-200 text-[10px] sm:text-[11px] font-bold transition-all active:scale-95 cursor-pointer';
        const activeYesClass  = 'btn-vote-yes inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-950 border-2 border-emerald-500 rounded-full text-[10px] sm:text-[11px] font-black shadow-xs transition-all active:scale-95 cursor-pointer';
        
        const defaultNoClass  = 'btn-vote-no inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 hover:bg-rose-50 text-gray-700 rounded-full border border-gray-200 text-[10px] sm:text-[11px] font-bold transition-all active:scale-95 cursor-pointer';
        const activeNoClass   = 'btn-vote-no inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-950 border-2 border-rose-500 rounded-full text-[10px] sm:text-[11px] font-black shadow-xs transition-all active:scale-95 cursor-pointer';

        if (btnYes) {
            btnYes.disabled = false;
            btnYes.style.pointerEvents = 'auto';
            btnYes.className = (type === 'yes') ? activeYesClass : defaultYesClass;
        }
        
        if (btnNo) {
            btnNo.disabled = false;
            btnNo.style.pointerEvents = 'auto';
            btnNo.className = (type === 'no') ? activeNoClass : defaultNoClass;
        }
    }

    function voteReviewHelpful(reviewId, type, btnElem) {
        const container = btnElem.closest('[data-review-id]');
        if (!container) return;

        // Prevent rapid duplicate clicks
        if (container.dataset.loading === 'true') return;
        container.dataset.loading = 'true';

        const currentVoted = container.getAttribute('data-voted') || '';

        const params = new URLSearchParams();
        params.append('review_id', reviewId);
        params.append('type', type);
        params.append('current_vote', currentVoted);

        fetch('<?= BASE_URL ?>/reviews/vote-helpful', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(r => r.json())
        .then(data => {
            container.dataset.loading = 'false';
            if (data.success) {
                const voteType = data.voted_type || '';

                // Update all matching review containers on the page
                document.querySelectorAll(`[data-review-id="${reviewId}"]`).forEach(c => {
                    const yesElem = c.querySelector('.vote-count-yes');
                    const noElem = c.querySelector('.vote-count-no');
                    if (yesElem && data.yes !== undefined) yesElem.textContent = data.yes;
                    if (noElem && data.no !== undefined) noElem.textContent = data.no;
                    c.setAttribute('data-voted', voteType);
                    applyVotedStyle(c, voteType);
                });

                if (voteType) {
                    try { localStorage.setItem('voted_review_' + reviewId, voteType); } catch(e){}
                } else {
                    try { localStorage.removeItem('voted_review_' + reviewId); } catch(e){}
                }
            }
        })
        .catch(err => {
            container.dataset.loading = 'false';
            console.error(err);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-review-id]').forEach(container => {
            const reviewId = container.getAttribute('data-review-id');
            const serverVoted = container.getAttribute('data-voted');
            let localVoted = null;
            try { localVoted = localStorage.getItem('voted_review_' + reviewId); } catch(e){}
            
            const activeVote = localVoted || serverVoted;
            if (activeVote) {
                container.setAttribute('data-voted', activeVote);
                applyVotedStyle(container, activeVote);
            }
        });
    });
    </script>
</div>

<script>
    const variants = <?= json_encode($variants ?? []) ?>;
    const variantImageMap = <?= json_encode($variantImageMap ?? (object)[]) ?>;
    const volumeTiers = <?= json_encode($volumeTiers ?? []) ?>;
    const categoryGst = <?= (float)(($category['sgst'] ?? 0) + ($category['cgst'] ?? 0)) ?>;
    const productGst = categoryGst > 0 
        ? categoryGst 
        : <?= (isset($product['total_gst']) && $product['total_gst'] !== null && $product['total_gst'] !== '' && (float)$product['total_gst'] > 0) 
            ? (float)$product['total_gst'] 
            : (((float)($product['sgst'] ?? 0) + (float)($product['cgst'] ?? 0)) ?: 0) ?>;
    
    const defaultMoq = <?= $moq ?>;
    const productStock = <?= (int)($product['stock_quantity'] ?? 0) ?>;
    const hasVariants = <?= !empty($variants) ? 'true' : 'false' ?>;
    const baseProductPrice = <?= (float)($displayPrice ?? 0) ?>;
    const canAddToCart = <?= $canAddToCart ? 'true' : 'false' ?>;
    const productWeight = <?= (!empty($product['weight']) && (float)$product['weight'] > 0) ? (float)$product['weight'] : 'null' ?>;

    function getSelectedUnitPrice(qty) {
        if (!hasVariants) {
            let p = baseProductPrice;
            if (volumeTiers && volumeTiers.length > 0) {
                const currentQty = qty || defaultMoq;
                for (let t of volumeTiers) {
                    let minQ = parseInt(t.min_qty) || 0;
                    let maxQ = t.max_qty ? parseInt(t.max_qty) : Infinity;
                    if (currentQty >= minQ && currentQty <= maxQ && parseFloat(t.tier_price) > 0) {
                        let gst = productGst || 0;
                        p = Math.round(parseFloat(t.tier_price) * 100) / 100;
                        break;
                    }
                }
            }
            return p;
        }
        const checkboxes = document.querySelectorAll('.variant-checkbox:checked');
        if (checkboxes.length === 0) return 0;

        let unitTotal = 0;
        checkboxes.forEach(c => {
            const vid = c.value;
            const sv = variants.find(v => v.id == vid);
            if (sv && parseFloat(sv.base_price) > 0) {
                let bp = parseFloat(sv.base_price);
                let dp = (sv.discount_price !== null && sv.discount_price !== undefined && sv.discount_price !== '' && parseFloat(sv.discount_price) > 0) ? parseFloat(sv.discount_price) : null;
                let gst = categoryGst > 0 
                    ? categoryGst 
                    : ((sv.total_gst !== null && sv.total_gst !== undefined && sv.total_gst !== '' && parseFloat(sv.total_gst) > 0) 
                        ? parseFloat(sv.total_gst) 
                        : (((parseFloat(sv.sgst) || 0) + (parseFloat(sv.cgst) || 0)) || productGst || 0));

                let effPrice = (dp !== null && dp > 0) ? dp : bp;
                let maxAmt = Math.round(effPrice * 100) / 100;
                unitTotal += maxAmt;
            }
        });
        return unitTotal > 0 ? unitTotal : baseProductPrice;
    }

    function updateCartButtonAmount() {
        if (!canAddToCart) {
            updateMobileButtonTotal();
            return;
        }
        const desktopBtnTotal = document.getElementById('addToCartTotalAmount');
        const qtyInput = document.getElementById('qtyInput');
        const qty = parseInt(qtyInput ? qtyInput.value : defaultMoq) || defaultMoq;
        const unitPrice = getSelectedUnitPrice(qty);
        const totalAmount = unitPrice * qty;

        if (desktopBtnTotal) {
            desktopBtnTotal.innerText = '₹' + totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
        }
        updateMobileButtonTotal();
    }

    function updateMobileButtonTotal() {
        const mobileBtnTotal = document.getElementById('mobileBtnTotal');
        if (!mobileBtnTotal) return;

        if (!canAddToCart) {
            mobileBtnTotal.style.display = 'none';
            return;
        }

        const qtyInput = document.getElementById('qtyInput');
        const qty = parseInt(qtyInput ? qtyInput.value : defaultMoq) || defaultMoq;
        const unitPrice = getSelectedUnitPrice(qty);
        const totalAmount = unitPrice * qty;

        if (totalAmount > 0) {
            mobileBtnTotal.innerText = '₹' + totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
            mobileBtnTotal.style.display = 'inline-block';
        } else {
            mobileBtnTotal.style.display = 'none';
        }
    }

    function getMaxAllowedQty() {
        if (!hasVariants) {
            return productStock;
        }
        const checkboxes = document.querySelectorAll('.variant-checkbox:checked');
        if (checkboxes.length === 0) return 0;
        
        let maxAllowed = Infinity;
        checkboxes.forEach(c => {
            const stock = parseInt(c.dataset.stock) || 0;
            if (stock < maxAllowed) maxAllowed = stock;
        });
        return maxAllowed === Infinity ? 0 : maxAllowed;
    }

    function updateQty(delta) {
        const i = document.getElementById('qtyInput');
        if (!i) return;
        let current = parseInt(i.value) || defaultMoq;
        let maxAllowed = getMaxAllowedQty();
        
        let newValue = current + delta;
        if (newValue < defaultMoq) newValue = defaultMoq;
        
        const warning = document.getElementById('maxStockWarning');
        if (newValue > maxAllowed) {
            newValue = maxAllowed;
            if (newValue < defaultMoq) {
                newValue = defaultMoq;
            }
            if (warning) {
                warning.classList.remove('hidden');
                setTimeout(() => warning.classList.add('hidden'), 3000);
            }
        } else {
            if (warning) {
                warning.classList.add('hidden');
            }
        }
        
        i.value = newValue;
        // Keep mobile sticky bar qty display in sync
        const mobileDisplay = document.getElementById('mobileQtyDisplay');
        if (mobileDisplay) mobileDisplay.textContent = newValue;
        updateButtonStates();
        updateCartButtonAmount();
    }
    
    function updateButtonStates() {
        const i = document.getElementById('qtyInput');
        const minusBtn = document.getElementById('qtyMinusBtn');
        const plusBtn = document.getElementById('qtyPlusBtn');
        if (!i || !minusBtn || !plusBtn) return;
        
        let current = parseInt(i.value) || defaultMoq;
        let maxAllowed = getMaxAllowedQty();
        
        minusBtn.disabled = current <= defaultMoq;
        plusBtn.disabled = current >= maxAllowed;
    }
    
    function updateVariantSelection() {
        const checkboxes = document.querySelectorAll('.variant-checkbox');
        const checked = Array.from(checkboxes).filter(c => c.checked);
        const nameEl = document.getElementById('selectedVariantName');
        const warning = document.getElementById('variantWarning');
        
        const canAddToCart = <?= $canAddToCart ? 'true' : 'false' ?>;
        const addToCartBtn = document.getElementById('addToCartBtn');
        
        if (checked.length === 0) {
            nameEl.innerText = 'None selected';
            if (warning) warning.classList.remove('hidden');
            if (addToCartBtn) addToCartBtn.disabled = true;
        } else if (checked.length === 1) {
            nameEl.innerText = checked[0].dataset.name;
            if (warning) warning.classList.add('hidden');
            if (addToCartBtn && canAddToCart) addToCartBtn.disabled = false;
        } else {
            nameEl.innerText = checked.length + ' selected';
            if (warning) warning.classList.add('hidden');
            if (addToCartBtn && canAddToCart) addToCartBtn.disabled = false;
        }

        // Collect all checked variant IDs
        const checkedIds = checked.map(c => c.value);

        // Update SKU display
        const skuDisplay = document.getElementById('productSkuDisplay');
        if (skuDisplay && hasVariants) {
            let skus = [];
            checkedIds.forEach(id => {
                let v = variants.find(v => String(v.id) === String(id));
                if (v && v.sku) skus.push(v.sku);
            });
            skuDisplay.innerText = skus.length > 0 ? skus.join(', ') : 'N/A';
        }

        // Update Weight display for selected variant(s)
        const weightDisplay = document.getElementById('productWeightDisplay');
        const weightLabel = document.getElementById('productWeightLabel');
        if (weightDisplay) {
            if (hasVariants && checkedIds.length > 0) {
                let totalWeight = 0;
                let hasValidWeight = false;
                checkedIds.forEach(id => {
                    let v = variants.find(v => String(v.id) === String(id));
                    let w = (v && v.weight !== null && v.weight !== undefined && v.weight !== '') 
                        ? parseFloat(v.weight) 
                        : (productWeight ? parseFloat(productWeight) : 0);
                    if (!isNaN(w) && w > 0) {
                        totalWeight += w;
                        hasValidWeight = true;
                    }
                });
                
                if (hasValidWeight && totalWeight > 0) {
                    weightDisplay.innerText = totalWeight.toFixed(3) + ' kg';
                } else if (productWeight && parseFloat(productWeight) > 0) {
                    weightDisplay.innerText = parseFloat(productWeight).toFixed(3) + ' kg';
                } else {
                    weightDisplay.innerText = '-';
                }

                if (weightLabel) {
                    weightLabel.innerText = checkedIds.length > 1 ? 'Weight / Set' : 'Weight / Unit';
                }
            } else {
                if (productWeight && parseFloat(productWeight) > 0) {
                    weightDisplay.innerText = parseFloat(productWeight).toFixed(3) + ' kg';
                } else {
                    weightDisplay.innerText = '-';
                }
                if (weightLabel) {
                    weightLabel.innerText = 'Weight / Unit';
                }
            }
        }

        // Update pricing display for ALL selected variants (summed)
        updatePriceDisplay(checkedIds);

        // Update image gallery with ALL selected variants' images (auto-rotate)
        updateVariantImages(checkedIds);

        // Update button dynamic total amounts
        updateCartButtonAmount();

        // Re-validate quantity when variant selection changes
        const qtyInput = document.getElementById('qtyInput');
        if (qtyInput) {
            let maxAllowed = getMaxAllowedQty();
            let currentQty = parseInt(qtyInput.value) || defaultMoq;
            if (currentQty > maxAllowed && maxAllowed >= defaultMoq) {
                qtyInput.value = maxAllowed;
            }
            updateButtonStates();
            updateCartButtonAmount();
        }
    }

    function updatePriceDisplay(variantIds) {
        const mainPriceDisplay = document.getElementById('mainPriceDisplay');
        const originalPriceDisplay = document.getElementById('originalPriceDisplay');
        const discountBadge = document.getElementById('discountBadge');
        const gstBadge = document.getElementById('gstBadge');
        const unitLabel = document.getElementById('priceUnitLabel');
        
        if (!mainPriceDisplay || !variantIds || variantIds.length === 0) return;

        let totalDisplay = 0;
        let totalOriginal = 0;
        let hasDiscount = false;
        let highestGst = 0;

        variantIds.forEach(vid => {
            const sv = variants.find(v => v.id == vid);
            if (sv && parseFloat(sv.base_price) > 0) {
                let bp = parseFloat(sv.base_price);
                let dp = (sv.discount_price !== null && sv.discount_price !== undefined && sv.discount_price !== '' && parseFloat(sv.discount_price) > 0) ? parseFloat(sv.discount_price) : null;
                let gst = categoryGst > 0 
                    ? categoryGst 
                    : ((sv.total_gst !== null && sv.total_gst !== undefined && sv.total_gst !== '' && parseFloat(sv.total_gst) > 0) 
                        ? parseFloat(sv.total_gst) 
                        : (((parseFloat(sv.sgst) || 0) + (parseFloat(sv.cgst) || 0)) || productGst || 0));
                
                if (gst > highestGst) highestGst = gst;

                let effPrice = (dp !== null && dp > 0) ? dp : bp;
                let maxAmt = Math.round(effPrice * 100) / 100;
                let origMaxAmt = Math.round(bp * 100) / 100;

                totalDisplay += maxAmt;
                totalOriginal += origMaxAmt;

                if (dp !== null && dp > 0 && dp < bp) {
                    hasDiscount = true;
                }
            }
        });

        if (totalDisplay > 0) {
            mainPriceDisplay.innerText = '\u20b9' + totalDisplay.toLocaleString('en-IN', {maximumFractionDigits: 2});
            
            // Show "/ unit" for single, "total" for multi
            if (unitLabel) {
                unitLabel.innerText = variantIds.length === 1 ? '/ unit' : '/ ' + variantIds.length + ' variants';
            }
            
            if (hasDiscount && totalOriginal > totalDisplay) {
                originalPriceDisplay.innerText = '\u20b9' + totalOriginal.toLocaleString('en-IN', {maximumFractionDigits: 2});
                originalPriceDisplay.classList.remove('hidden');
                const pct = Math.round(((totalOriginal - totalDisplay) / totalOriginal) * 100);
                if (discountBadge) {
                    discountBadge.innerText = pct + '% OFF';
                    discountBadge.classList.remove('hidden');
                }
                const mainBadge = document.getElementById('mainImageDiscountBadge');
                if (mainBadge) {
                    const mainBadgePct = mainBadge.querySelector('.badge-pct-text') || document.getElementById('mainBadgePct');
                    if (mainBadgePct) {
                        mainBadgePct.textContent = pct + '%';
                    }
                    mainBadge.classList.remove('hidden');
                }
            } else {
                originalPriceDisplay.classList.add('hidden');
                if (discountBadge) discountBadge.classList.add('hidden');
                const mainBadge = document.getElementById('mainImageDiscountBadge');
                if (mainBadge) mainBadge.classList.add('hidden');
            }

            if (gstBadge) {
                if (highestGst > 0) {
                    gstBadge.innerText = 'Incl. ' + highestGst + '% GST';
                    gstBadge.classList.remove('hidden');
                } else {
                    gstBadge.classList.add('hidden');
                }
            }
        }
    }

    // Auto-rotate timer reference
    let imageRotateInterval = null;
    let currentRotateIndex = 0;

    function updateVariantImages(variantIds) {
        const mainImg = document.getElementById('mainImage');
        const gallery = document.getElementById('thumbnailGallery');
        if (!gallery || !mainImg) return;

        // Stop any existing auto-rotate
        if (imageRotateInterval) {
            clearInterval(imageRotateInterval);
            imageRotateInterval = null;
        }

        // Collect all images from all selected variants
        let allImages = [];
        if (variantIds && variantIds.length > 0) {
            variantIds.forEach(vid => {
                if (variantImageMap[vid] && variantImageMap[vid].length > 0) {
                    variantImageMap[vid].forEach(url => {
                        if (!allImages.includes(url)) {
                            allImages.push(url);
                        }
                    });
                }
            });
        }

        if (allImages.length > 0) {
            mainImg.src = allImages[0];
            mainImg.classList.remove('opacity-30');
        } else {
            // No variant-specific images set — fall back to product primary image
            const productPrimaryImg = '<?= htmlspecialchars($primaryImage) ?>';
            mainImg.src = productPrimaryImg || 'https://placehold.co/800x800/f9fafb/9ca3af?text=No+Image';
            mainImg.classList.remove('opacity-30');
        }

        // Build thumbnail buttons (horizontal scrollable strip)
        gallery.innerHTML = '';
        if (allImages.length > 1) {
            allImages.forEach(function(url, idx) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl bg-white overflow-hidden border-2 transition-all cursor-pointer ' + (idx === 0 ? 'border-[#f25996] shadow-xs' : 'border-gray-200 hover:border-[#f25996]/50');
                btn.setAttribute('data-img-idx', idx);
                btn.onclick = function() {
                    mainImg.src = url;
                    currentRotateIndex = idx;
                    highlightThumb(gallery, idx);
                };
                const img = document.createElement('img');
                img.src = url;
                img.className = 'w-full h-full object-contain p-1';
                img.alt = 'Thumbnail ' + (idx + 1);
                btn.appendChild(img);
                gallery.appendChild(btn);
            });

            // Start auto-rotate if multiple images from multiple variants
            currentRotateIndex = 0;
            imageRotateInterval = setInterval(function() {
                currentRotateIndex = (currentRotateIndex + 1) % allImages.length;
                mainImg.style.opacity = '0';
                setTimeout(function() {
                    mainImg.src = allImages[currentRotateIndex];
                    mainImg.style.opacity = '1';
                    highlightThumb(gallery, currentRotateIndex);
                }, 200);
            }, 3000);
        }
    }

    function highlightThumb(gallery, activeIdx) {
        gallery.querySelectorAll('button').forEach(b => {
            if (parseInt(b.getAttribute('data-img-idx')) === activeIdx) {
                b.classList.remove('border-gray-200');
                b.classList.add('border-[#f25996]', 'shadow-xs');
            } else {
                b.classList.remove('border-[#f25996]', 'shadow-xs');
                b.classList.add('border-gray-200');
            }
        });
    }

    // Initialize states on load
    document.addEventListener('DOMContentLoaded', () => {
        updateButtonStates();
        updateMobileButtonTotal();
        const productForm = document.getElementById('productAddToCartForm');
        let isSubmitting = false; // Guard against double-click / race conditions

        if (productForm) {
            productForm.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();

                if (isSubmitting) return; // Block duplicate submissions

                const checkboxes = productForm.querySelectorAll('.variant-checkbox:checked');
                const warning = document.getElementById('variantWarning');

                // If product has variants but none are selected, warn user
                const allCheckboxes = productForm.querySelectorAll('.variant-checkbox');
                if (allCheckboxes.length > 0 && checkboxes.length === 0) {
                    if (warning) warning.classList.remove('hidden');
                    return;
                }

                const productId = productForm.querySelector('[name="product_id"]').value;
                const quantity = parseInt(productForm.querySelector('[name="quantity"]').value) || 1;
                const btn = document.getElementById('addToCartBtn');

                isSubmitting = true; // Lock to prevent double-submit
                
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Adding...';
                }

                // If no variant checkboxes (no variants on product), send single request with no variant_id
                let requests;
                if (checkboxes.length === 0) {
                    requests = [fetch(baseUrl + '/cart/ajax-add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId, quantity: quantity, variant_id: null })
                    }).then(r => r.json())];
                } else {
                    // Send one request per selected variant
                    requests = Array.from(checkboxes).map(cb => {
                        return fetch(baseUrl + '/cart/ajax-add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ product_id: productId, quantity: quantity, variant_id: cb.value })
                        }).then(r => r.json());
                    });
                }

                Promise.all(requests).then(results => {
                    isSubmitting = false; // Release lock
                    const failed = results.filter(r => !r.success);
                    const lastSuccess = results.filter(r => r.success).pop();

                    if (lastSuccess && lastSuccess.cart_data) {
                        renderCart(lastSuccess.cart_data);
                        if (typeof openCartDrawer === 'function') openCartDrawer(true);
                    } else if (lastSuccess) {
                        fetchCart();
                        if (typeof openCartDrawer === 'function') openCartDrawer();
                    }

                    if (failed.length > 0) {
                        alert(failed[0].message || 'Some variants could not be added to cart.');
                    }

                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Add to Cart</span>' + (canAddToCart ? '<span class="text-white/60 font-normal">|</span><span id="addToCartTotalAmount" class="font-black"></span>' : '');
                        updateCartButtonAmount();
                    }
                }).catch(err => {
                    isSubmitting = false; // Release lock on error too
                    console.error('Cart error:', err);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Add to Cart</span>' + (canAddToCart ? '<span class="text-white/60 font-normal">|</span><span id="addToCartTotalAmount" class="font-black"></span>' : '');
                        updateCartButtonAmount();
                    }
                });

            });
        }

        // Initialize variant selection label, images, and button amount
        updateVariantSelection();
        updateCartButtonAmount();

        // Image Zoom Logic
        const wrap = document.getElementById('mainImageWrap');
        const img = document.getElementById('mainImage');
        const lens = document.getElementById('zoomLens');
        const result = document.getElementById('zoomResult');
        
        if (wrap && img && lens && result && window.innerWidth >= 1024) {
            wrap.addEventListener('mouseenter', function() {
                // Ensure image is loaded before zooming
                if (img.src.includes('placehold.co') || img.classList.contains('opacity-30')) return;
                
                lens.classList.remove('hidden');
                result.classList.remove('hidden');
                result.style.backgroundImage = `url('${img.src}')`;
            });
            
            wrap.addEventListener('mouseleave', function() {
                lens.classList.add('hidden');
                result.classList.add('hidden');
            });
            
            wrap.addEventListener('mousemove', function(e) {
                if (lens.classList.contains('hidden')) return;
                
                const rect = wrap.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Calculate lens position
                let lensX = x - (lens.offsetWidth / 2);
                let lensY = y - (lens.offsetHeight / 2);
                
                // Keep lens inside the wrapper bounds
                if (lensX < 0) lensX = 0;
                if (lensY < 0) lensY = 0;
                if (lensX > rect.width - lens.offsetWidth) lensX = rect.width - lens.offsetWidth;
                if (lensY > rect.height - lens.offsetHeight) lensY = rect.height - lens.offsetHeight;
                
                lens.style.left = lensX + 'px';
                lens.style.top = lensY + 'px';
                
                // Calculate ratio for background image position in the result pane
                const ratioX = result.offsetWidth / lens.offsetWidth;
                const ratioY = result.offsetHeight / lens.offsetHeight;
                
                // The background image in the result pane needs to be scaled relative to the main image
                result.style.backgroundSize = (rect.width * ratioX) + 'px ' + (rect.height * ratioY) + 'px';
                result.style.backgroundPosition = `-${lensX * ratioX}px -${lensY * ratioY}px`;
            });
        }
    });

    // Product Size Guide Modal Logic
    let productSizeChartsCache = null;

    function openProductSizeChartModal(categoryId, chartId = 0, productId = 0, subCategoryId = 0) {
        const modal = document.getElementById('productSizeGuideModal');
        const container = document.getElementById('productSizeGuideContent');
        const loader = document.getElementById('productSizeGuideLoader');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const cacheKey = `${categoryId}_${chartId}_${productId}_${subCategoryId}`;
        if (productSizeChartsCache && productSizeChartsCache._key === cacheKey) {
            renderProductSizeGuide(productSizeChartsCache.data);
            return;
        }

        loader.classList.remove('hidden');
        container.innerHTML = '';

        let apiUrl = `<?= BASE_URL ?>/api/size-chart?category_id=${categoryId || 0}`;
        if (chartId) apiUrl += `&chart_id=${chartId}`;
        if (productId) apiUrl += `&product_id=${productId}`;
        if (subCategoryId) apiUrl += `&sub_category_id=${subCategoryId}`;

        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                if (data.success && data.charts && data.charts.length > 0) {
                    productSizeChartsCache = { _key: cacheKey, data: data.charts };
                    renderProductSizeGuide(data.charts);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3 text-amber-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h4 class="font-bold text-gray-800 text-base mb-1">Standard Sizing Applies</h4>
                            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-4">Please refer to our general apparel size guide or contact support for customized dimensions.</p>
                            <a href="<?= BASE_URL ?>/size-chart" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#f25996] text-white text-xs font-bold rounded-lg hover:bg-[#d94883] transition-all">
                                <span>Browse All Size Charts</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    `;
                }
            })
            .catch(err => {
                loader.classList.add('hidden');
                container.innerHTML = '<div class="text-center py-8 text-sm text-red-500">Failed to load size guide. Please try again.</div>';
            });
    }

    // Interactive Product Information Tab Switcher (Description, How to play/use, Why choose, Custom fields)
    window.switchProductInfoTab = function(targetPanelId, btn) {
        // Hide all tab panels
        const panels = document.querySelectorAll('.product-info-panel');
        panels.forEach(panel => {
            panel.classList.add('hidden');
            panel.classList.remove('block');
        });

        // Reset all tab buttons
        const tabBtns = document.querySelectorAll('.product-info-tab-btn');
        tabBtns.forEach(tabBtn => {
            tabBtn.classList.remove('border-gray-900', 'text-gray-900', 'border-[#F25996]', 'text-[#F25996]', 'font-bold');
            tabBtn.classList.add('border-transparent', 'text-gray-500', 'font-medium');
            const icon = tabBtn.querySelector('span:first-child');
            if (icon) {
                icon.classList.remove('border-current');
                icon.classList.add('border-gray-400');
            }
        });

        // Activate clicked tab button
        if (btn) {
            btn.classList.add('border-gray-900', 'text-gray-900', 'font-bold');
            btn.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
            const activeIcon = btn.querySelector('span:first-child');
            if (activeIcon) {
                activeIcon.classList.add('border-current');
                activeIcon.classList.remove('border-gray-400');
            }
            try {
                btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } catch(e) {}
        }

        // Show target panel
        const targetPanel = document.getElementById(targetPanelId);
        if (targetPanel) {
            targetPanel.classList.remove('hidden');
            targetPanel.classList.add('block');
        }
    };

    function closeProductSizeChartModal() {
        const modal = document.getElementById('productSizeGuideModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function renderProductSizeGuide(charts) {
        const container = document.getElementById('productSizeGuideContent');
        let html = '';

        if (charts.length > 1) {
            html += `
                <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-5 border-b border-gray-100 no-scrollbar">
                    ${charts.map((c, idx) => `
                        <button type="button" onclick="switchProductChartTab(${idx})" class="product-chart-tab px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap ${idx === 0 ? 'bg-[#f25996] text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}" data-tab-idx="${idx}">
                            ${escapeHtml(c.title)}
                        </button>
                    `).join('')}
                </div>
            `;
        }

        charts.forEach((chart, idx) => {
            const cols = chart.columns || [];
            const rows = chart.rows || [];

            html += `
                <div class="product-chart-pane ${idx === 0 ? '' : 'hidden'}" id="productChartPane_${idx}">
                    <!-- Header with Title & Pink Line -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-6 pb-2">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight uppercase">${escapeHtml(chart.title)}</h3>
                            <div class="h-1 w-28 bg-[#f25996] rounded-full mt-1.5"></div>
                            ${chart.category_name ? `<p class="text-xs text-gray-500 font-medium mt-1">Category: ${escapeHtml(chart.category_name)} ${chart.dress_type ? '• ' + escapeHtml(chart.dress_type) : ''}</p>` : ''}
                        </div>
                        ${chart.tolerance_note ? `
                            <span class="inline-flex items-center self-start px-3.5 py-1 rounded-full border border-gray-300 text-[11px] font-semibold text-gray-600 bg-gray-50 shadow-2xs whitespace-nowrap">
                                ${escapeHtml(chart.tolerance_note)}
                            </span>
                        ` : ''}
                    </div>

                    <!-- Size Table and Optional Diagram -->
                    <div class="grid grid-cols-1 ${chart.image_url ? 'lg:grid-cols-12 gap-5' : ''} items-start mb-4">
                        <div class="${chart.image_url ? 'lg:col-span-8' : 'w-full'}">
                            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-xs">
                                <table class="w-full text-center text-xs sm:text-sm border-collapse min-w-[360px]">
                                    <thead>
                                        <tr class="border-b-2 border-gray-200 bg-[#faf8f5]">
                                            ${cols.map(col => `
                                                <th class="py-3 px-3.5 font-bold text-[#3b281c] uppercase tracking-wider text-[11px] sm:text-xs whitespace-nowrap">${escapeHtml(col)}</th>
                                            `).join('')}
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 font-medium text-gray-700">
                                        ${rows.map(row => `
                                            <tr class="hover:bg-[#fbf9f6] transition-colors">
                                                ${cols.map((col, cIdx) => {
                                                    const val = row[col] !== undefined ? row[col] : (row['Size'] || row['size'] || '');
                                                    const isSizeCol = (cIdx === 0 || col.toLowerCase() === 'size');
                                                    return `
                                                        <td class="py-3 px-3.5 ${isSizeCol ? 'font-black text-[#2a1c13] text-sm bg-gray-50/50' : 'text-gray-600'}">
                                                            ${escapeHtml(val || '-')}
                                                        </td>
                                                    `;
                                                }).join('')}
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        ${chart.image_url ? `
                            <div class="lg:col-span-4 mt-3 lg:mt-0">
                                <div class="bg-gray-50/90 p-3.5 rounded-xl border border-gray-200 text-center">
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Guide Illustration</span>
                                    <a href="${escapeHtml(chart.image_url)}" target="_blank" rel="noopener noreferrer" class="block group" title="Click to enlarge diagram">
                                        <img src="${escapeHtml(chart.image_url)}" alt="${escapeHtml(chart.title)} Diagram" class="w-full h-auto max-h-48 object-contain rounded-lg border border-gray-200 bg-white p-1.5 shadow-2xs group-hover:scale-[1.02] transition-transform">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-[#f25996] font-bold mt-1.5 group-hover:underline">
                                            🔍 View Full Diagram
                                        </span>
                                    </a>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function switchProductChartTab(targetIdx) {
        document.querySelectorAll('.product-chart-tab').forEach((tab, idx) => {
            if (idx === targetIdx) {
                tab.classList.remove('bg-gray-100', 'text-gray-700');
                tab.classList.add('bg-[#f25996]', 'text-white', 'shadow-sm');
            } else {
                tab.classList.remove('bg-[#f25996]', 'text-white', 'shadow-sm');
                tab.classList.add('bg-gray-100', 'text-gray-700');
            }
        });

        document.querySelectorAll('.product-chart-pane').forEach((pane, idx) => {
            pane.classList.toggle('hidden', idx !== targetIdx);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>

<!-- Size Guide Modal -->
<div id="productSizeGuideModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4 transition-all duration-200">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-[#fdfcfb]">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-[#fdf2f7] text-[#f25996] flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18M7 6v3m4-3v2m4-2v3m4-3v2M7 12v3m4-3v2m4-3v3m4-3v2"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Garment Measurement Guide</h4>
                    <p class="text-[11px] text-gray-400">Accurate sizing specifications</p>
                </div>
            </div>
            <button type="button" onclick="closeProductSizeChartModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Modal Body Content -->
        <div class="p-6 overflow-y-auto flex-1">
            <div id="productSizeGuideLoader" class="text-center py-12 hidden">
                <div class="inline-block w-8 h-8 border-3 border-[#f25996] border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs text-gray-500 mt-2 font-medium">Loading size measurements...</p>
            </div>
            <div id="productSizeGuideContent"></div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center text-xs">
            <span class="text-gray-400 font-medium flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#f25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Accurate sizing specifications for this garment
            </span>
        </div>
    </div>
</div>
