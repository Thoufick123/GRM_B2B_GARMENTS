<div class="bg-gray-50 min-h-screen py-4 md:py-10 pb-28 md:pb-12">
    <div class="max-w-6xl mx-auto px-3.5 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-center justify-center relative mb-4 sm:mb-6">
            <div class="flex items-center justify-center gap-2 text-center">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900">Review Your Order</h1>
                <?php if (!empty($cartItems)): ?>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-pink-50 text-[#F25996] border border-pink-200"><?= count($cartItems) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($cartItems)): ?>
                <a href="<?= BASE_URL ?>/catalog" class="hidden lg:inline-flex absolute right-0 items-center gap-1 text-xs sm:text-sm font-bold text-[#F25996] hover:underline">
                    <span>Continue Shopping</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-pink-50 text-[#F25996] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-1">Your cart is empty</h3>
                <p class="text-gray-500 text-xs sm:text-sm mb-6">You haven't added any products to your cart yet.</p>
                <a href="<?= BASE_URL ?>/catalog" class="inline-flex items-center gap-2 bg-[#F25996] text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-[#db3e7c] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Browse Catalog
                </a>
            </div>

        <?php else: ?>
        
        <?php if (!empty($activeOffers)): ?>
        <!-- Active Category Offers & Tier Progress (Cart Brand Palette: Pink theme) -->
        <div class="mb-5 space-y-3">
            <?php foreach($activeOffers as $offer): ?>
                <?php if ($offer['is_unlocked']): ?>
                    <div class="bg-gradient-to-br from-pink-50/80 via-pink-50/50 to-white border border-pink-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#F25996] text-white flex items-center justify-center text-xs sm:text-sm font-black shadow-xs flex-shrink-0 mt-0.5">✓</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs sm:text-sm font-black text-[#F25996] uppercase tracking-wider">🎉 SPECIAL OFFER UNLOCKED: <?= htmlspecialchars($offer['main_category_name']) ?></h3>
                                    <span class="text-[10px] sm:text-xs font-bold text-white bg-[#F25996] border border-[#F25996] px-2.5 py-0.5 rounded-full shadow-2xs">Active</span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-800 leading-snug mt-1">
                                    Your <strong><?= htmlspecialchars($offer['main_category_name']) ?></strong> order reached the ₹<?= number_format($offer['min_required']) ?> tier. You unlocked exclusive low minimum order quantities for:
                                </p>
                                <?php if (!empty($offer['secondary_offers'])): ?>
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <?php foreach($offer['secondary_offers'] as $so): ?>
                                        <a href="<?= BASE_URL ?>/catalog?category_id=<?= $so['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-pink-50 text-[#F25996] rounded-xl text-xs font-bold border border-pink-200 shadow-2xs hover:shadow-sm hover:scale-[1.02] transition-all">
                                            <span>✨ <?= htmlspecialchars($so['name']) ?></span>
                                            <span class="text-[#F25996] font-semibold">(Min ₹<?= number_format($so['special_min_amount']) ?>)</span>
                                            <?php if ($so['in_cart']): ?>
                                                <?php if ($so['is_met']): ?>
                                                    <span class="text-[10px] bg-[#F25996] text-white px-1.5 py-0.5 rounded font-bold shadow-2xs">In Cart ✓</span>
                                                <?php else: ?>
                                                    <span class="text-[10px] bg-pink-100 text-[#F25996] px-1.5 py-0.5 rounded font-bold border border-[#F25996]/40">Add ₹<?= number_format($so['remaining']) ?> more</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                        $secondNames = !empty($offer['secondary_offers']) ? implode(', ', array_map(fn($s) => $s['name'], $offer['secondary_offers'])) : 'subcategories';
                    ?>
                    <div class="bg-gradient-to-br from-pink-50/60 via-pink-50/40 to-white border border-pink-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="text-xs sm:text-sm font-black text-[#F25996] flex items-center gap-1.5">
                                <span>🎁</span>
                                <span>Unlock <?= htmlspecialchars($offer['main_category_name']) ?> Combo Offer</span>
                            </span>
                            <span class="text-xs font-bold text-[#F25996] font-mono">₹<?= number_format($offer['main_spent']) ?> / ₹<?= number_format($offer['min_required']) ?></span>
                        </div>
                        <div class="w-full bg-white h-2 sm:h-2.5 rounded-full overflow-hidden p-0.5 border border-pink-200 shadow-2xs">
                            <div class="bg-gradient-to-r from-[#F25996] to-pink-500 h-full rounded-full transition-all duration-500" style="width: <?= $offer['progress_percent'] ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-800 mt-2 leading-relaxed">
                            Add <strong>₹<?= number_format($offer['remaining']) ?></strong> more of <em><?= htmlspecialchars($offer['main_category_name']) ?></em> to unlock special low combo rates on <strong><?= htmlspecialchars($secondNames) ?></strong>!
                        </p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Main layout: stacks on mobile (1. Cart Items -> 2. Order Summary -> 3. You May Also Like), side-by-side on lg+ -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

            <!-- ── Left Column: Cart Items + You May Also Like (lg:col-span-8) ── -->
            <div class="lg:col-span-8 min-w-0 space-y-5 sm:space-y-6 flex flex-col">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/40">
                        <h2 class="font-black text-gray-900 text-sm sm:text-base"><?= count($cartItems) ?> Item<?= count($cartItems) !== 1 ? 's' : '' ?></h2>
                        <a href="<?= BASE_URL ?>/catalog" class="text-xs font-bold text-[#F25996] hover:underline flex items-center gap-1">
                            <span>Continue Shopping</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        <?php foreach($cartItems as $item): ?>
                        <?php
                            $requiredMoq = (int)($item['moq_override'] ?? (!empty($item['category_moq']) ? $item['category_moq'] : 1));
                            $hasMoqError = $item['quantity'] < $requiredMoq;
                            $basePrice = (!empty($item['base_price']) && (float)$item['base_price'] > 0) ? (float)$item['base_price'] : ((!empty($item['variant_price']) && (float)$item['variant_price'] > 0) ? (float)$item['variant_price'] : (float)($item['wholesale_price'] ?? 0));
                            $discountPrice = (!empty($item['discount_price']) && (float)$item['discount_price'] > 0) ? (float)$item['discount_price'] : ((!empty($item['variant_discount_price']) && (float)$item['variant_discount_price'] > 0) ? (float)$item['variant_discount_price'] : null);
                            $unitPrice = $discountPrice ?? $basePrice;
                            $lineTotal = $unitPrice * $item['quantity'];
                            $hasDiscount = $discountPrice && $basePrice > $discountPrice;
                            $discPct = $hasDiscount ? round((($basePrice - $discountPrice) / $basePrice) * 100) : 0;
                            $isOutOfStock = !empty($item['is_out_of_stock']);
                        ?>
                        <li id="cart-page-item-<?= $item['cart_item_id'] ?>" data-cart-item-id="<?= $item['cart_item_id'] ?>" data-unit-price="<?= $unitPrice ?>" class="p-3 sm:p-3.5 transition-colors hover:bg-gray-50/30 <?= ($hasMoqError || $isOutOfStock) ? 'bg-pink-50/40' : '' ?>">
                            <div class="bg-white border border-gray-100/90 rounded-[22px] p-2.5 sm:p-3 hover:shadow-md transition-all duration-300 group relative shadow-xs">
                                <div class="flex items-start gap-2.5 sm:gap-3">
                                    <!-- Product Image (88px x 88px rounded-[16px]) -->
                                    <div class="rounded-[16px] overflow-hidden flex-shrink-0 relative block" style="width: 88px; height: 88px; min-width: 88px; min-height: 88px;">
                                        <?php if (!empty($item['primary_image'])): ?>
                                            <img src="<?= get_image_url($item['primary_image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-[16px] block" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center bg-gray-50 rounded-[16px]">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Center Details & Right Actions (Wishlist layout & font sizing) -->
                                    <div class="flex-1 min-w-0 flex items-start justify-between gap-2 self-stretch">
                                        <!-- Center Info Column -->
                                        <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
                                            <div>
                                                <!-- Title -->
                                                <h3 class="font-bold text-gray-900 text-xs sm:text-[13px] leading-tight line-clamp-1" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></h3>
                                                
                                                <!-- Variant Name / Attributes -->
                                                <?php if (!empty($item['variant_name'])): ?>
                                                    <div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5 truncate">
                                                        <?= htmlspecialchars($item['variant_name']) ?>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Base Price & Discount Price Row -->
                                                <div class="flex items-baseline gap-1 mt-0.5 flex-wrap">
                                                    <?php if ($hasDiscount): ?>
                                                        <span class="text-xs sm:text-sm font-bold text-[#F25996]">₹<?= format_price($discountPrice) ?></span>
                                                        <span class="text-[10px] sm:text-[11px] text-gray-400 line-through font-normal">₹<?= format_price($basePrice) ?></span>
                                                        <span class="text-[8px] sm:text-[9px] font-bold text-[#F25996] bg-pink-50 border border-pink-100 px-1 py-0.2 rounded"><?= $discPct ?>% OFF</span>
                                                    <?php else: ?>
                                                        <span class="text-xs sm:text-sm font-bold text-[#F25996]">₹<?= format_price($unitPrice) ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Min. Order & Warnings -->
                                                <div class="text-[10px] sm:text-[11px] text-gray-400 font-normal mt-0.5">
                                                    Min. Order: <?= $requiredMoq ?> Pcs
                                                    <?php if ($hasMoqError): ?>
                                                        <span class="inline-flex items-center ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-pink-100 text-[#F25996] border border-pink-200">
                                                             Min. <?= $requiredMoq ?> required
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Action Column: Red Trash Icon on Top, Stepper Below (AJAX - no page reload) -->
                                        <div class="flex flex-col justify-between items-end self-stretch shrink-0 pl-1">
                                            <!-- Top: Red Delete Button (AJAX) -->
                                            <button type="button"
                                                onclick="cartPageRemoveItem(<?= $item['cart_item_id'] ?>, this)"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50 p-0.5 -mr-1 rounded-lg transition-colors cursor-pointer" style="color: #ef4444;" title="Remove item">
                                                <svg class="w-4 h-4 text-red-500" style="color: #ef4444; stroke: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>

                                            <?php 
                                                $availableStock = (int)($item['available_stock'] ?? 0);
                                                $isMaxStock = ($availableStock > 0 && $item['quantity'] >= $availableStock);
                                                $moqStep = ($requiredMoq > 0) ? (int)$requiredMoq : 1;
                                            ?>
                                            <!-- Bottom (Below Trash): AJAX Stepper - qty saved without page reload -->
                                            <div id="cart-stepper-<?= $item['cart_item_id'] ?>" class="inline-flex items-center border border-pink-200 rounded-xl bg-white p-0.5 shadow-2xs mt-auto select-none" style="touch-action: manipulation;">
                                                <button type="button"
                                                    onclick="cartPageUpdateQty(<?= $item['cart_item_id'] ?>, -<?= $moqStep ?>, <?= $requiredMoq ?>, <?= $availableStock ?>, this)"
                                                    class="w-6 h-6 flex items-center justify-center text-[#F25996] hover:bg-pink-50 active:scale-90 active:bg-pink-100 rounded transition-all text-sm font-bold disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer select-none"
                                                    style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;"
                                                    <?= $item['quantity'] <= $requiredMoq ? 'disabled' : '' ?>>&minus;</button>
                                                <div class="h-3 w-[1px] bg-pink-100"></div>
                                                <span id="cart-qty-<?= $item['cart_item_id'] ?>" class="w-6 text-center text-xs font-bold text-[#F25996] select-none pointer-events-none"><?= $item['quantity'] ?></span>
                                                <div class="h-3 w-[1px] bg-pink-100"></div>
                                                <button type="button"
                                                    onclick="cartPageUpdateQty(<?= $item['cart_item_id'] ?>, <?= $moqStep ?>, <?= $requiredMoq ?>, <?= $availableStock ?>, this)"
                                                    class="w-6 h-6 flex items-center justify-center text-[#F25996] hover:bg-pink-50 active:scale-90 active:bg-pink-100 rounded transition-all text-sm font-bold disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer select-none"
                                                    style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;"
                                                    <?= $isMaxStock ? 'disabled' : '' ?>>&plus;</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- In-Stock Variant Suggestions (shown when out of stock OR when max inventory reached) -->
                                <?php if (($isOutOfStock || $isMaxStock) && !empty($item['alternative_variants'])): ?>
                                    <div class="mt-2 pt-2 border-t border-pink-100/80 flex flex-wrap items-center gap-1.5 w-full">
                                        <span class="text-[10px] font-bold text-[#F25996] flex items-center gap-1">
                                            <svg class="w-3 h-3 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <?= $isOutOfStock ? 'In Stock Alternatives:' : 'Need more? Other Available Variants:' ?>
                                        </span>
                                        <?php foreach ($item['alternative_variants'] as $alt): ?>
                                            <button type="button"
                                                onclick="cartPageAddVariant(<?= $item['product_id'] ?>, <?= $alt['id'] ?>, <?= $alt['moq'] ?? $requiredMoq ?>, this)"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-pink-50 hover:bg-[#F25996] text-[#F25996] hover:text-white rounded-md text-[10px] font-bold border border-pink-200 transition-all active:scale-95 shadow-2xs cursor-pointer" title="Add this variant as a new item below">
                                                <span><?= htmlspecialchars($alt['name']) ?></span>
                                                <span class="font-normal">(₹<?= $alt['formatted_price'] ?>)</span>
                                                <span class="font-black">+ Add</span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Stock Error / Notice Alert -->
                                <?php if (!empty($item['item_error'])): ?>
                                    <div class="mt-2 flex items-start gap-1.5 text-[10px] sm:text-xs text-[#F25996] bg-pink-50 border border-pink-200 rounded-xl px-2.5 sm:px-3 py-1.5 sm:py-2 font-medium">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-[#F25996]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span><?= htmlspecialchars($item['item_error']) ?></span>
                                    </div>
                                <?php elseif (!empty($item['item_notice'])): ?>
                                    <div class="mt-2 flex items-start gap-1.5 text-[10px] sm:text-xs text-[#F25996] bg-pink-50 border border-pink-100 rounded-xl px-2.5 sm:px-3 py-1 sm:py-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span><?= htmlspecialchars($item['item_notice']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div><!-- /cart items card -->

                <!-- ── You May Also Like (Inside Left Column directly below cart items) ── -->
                <?php if (!empty($relatedProducts)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/40">
                        <div class="flex items-center gap-2">
                            <h2 class="font-black text-gray-900 text-sm sm:text-base">You may also like</h2>
                            <?php if (array_reduce($relatedProducts, fn($carry, $p) => $carry || !empty($p['is_unlocked_offer']), false)): ?>
                                <span class="text-[10px] font-bold text-[#F25996] bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">Unlocked Offers</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <!-- Arrow Controls -->
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="scrollRelated('left', 'cart-related-track')" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-[#F25996] hover:text-white text-gray-700 flex items-center justify-center transition-colors shadow-2xs" title="Previous">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" onclick="scrollRelated('right', 'cart-related-track')" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-[#F25996] hover:text-white text-gray-700 flex items-center justify-center transition-colors shadow-2xs" title="Next">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            <a href="<?= isset($suggestionUrl) ? $suggestionUrl : BASE_URL . '/catalog' ?>" class="text-xs font-bold text-[#F25996] hover:underline">View all</a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="cart-related-track" class="flex items-stretch overflow-x-auto gap-2.5 sm:gap-4 pb-3 pt-0.5 snap-x snap-proximity md:snap-mandatory hide-scrollbar touch-auto overscroll-x-contain cursor-grab active:cursor-grabbing">
                            <?php foreach($relatedProducts as $rp): ?>
                            <?php
                                $rpImg = !empty($rp['primary_image'])
                                    ? (str_starts_with($rp['primary_image'], 'http') ? $rp['primary_image'] : (BASE_URL . '/' . ltrim($rp['primary_image'], '/')))
                                    : 'https://placehold.co/400?text=' . urlencode($rp['name']);
                                $basePrice = (isset($rp['base_price']) && (float)$rp['base_price'] > 0) ? (float)$rp['base_price'] : (float)($rp['wholesale_price'] ?? 0);
                                $discountPrice = !empty($rp['discount_price']) && (float)$rp['discount_price'] > 0 ? (float)$rp['discount_price'] : (!empty($rp['price']) ? (float)$rp['price'] : null);
                                $effPrice = $discountPrice ?? $basePrice;

                                $displayPrice = $effPrice;
                                $displayOriginalPrice = null;
                                if ($discountPrice && $basePrice > $discountPrice) {
                                    $displayOriginalPrice = $basePrice;
                                }
                                $rpMoq = !empty($rp['moq']) ? (int)$rp['moq'] : (!empty($rp['moq_override']) ? (int)$rp['moq_override'] : ($rp['category_moq'] ?? 1));
                            ?>
                            <div class="product-card h-full shrink-0 w-[calc(50%-0.25rem)] sm:w-[260px] md:w-[280px] snap-start bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-200 flex flex-col relative group">
                                <!-- Wishlist Button -->
                                <?php if (\Core\Session::get('user_id')): ?>
                                <button type="button" onclick="toggleWishlist(<?= $rp['id'] ?>, this); event.stopPropagation();" data-wishlist-btn-id="<?= $rp['id'] ?>" class="wishlist-btn" title="Wishlist">
                                    <svg class="text-gray-400 fill-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                </button>
                                <?php endif; ?>

                                <!-- Product Image -->
                                <a href="<?= BASE_URL ?>/product?id=<?= $rp['id'] ?>" class="product-image-wrap block w-full bg-gray-50 overflow-hidden shrink-0 aspect-square relative">
                                    <?php 
                                        $discPercent = ($displayOriginalPrice && $displayOriginalPrice > $displayPrice) ? round((($displayOriginalPrice - $displayPrice) / $displayOriginalPrice) * 100) : 0;
                                        if ($discPercent > 0) {
                                            echo render_discount_starburst($discPercent, 'transition-transform duration-300 group-hover:scale-105');
                                        }
                                    ?>
                                    <img src="<?= htmlspecialchars($rpImg) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </a>

                                <!-- Product Body -->
                                <div class="product-card-body p-3 md:p-4 flex flex-col flex-1 bg-white relative z-10">
                                    <a href="<?= BASE_URL ?>/product?id=<?= $rp['id'] ?>" class="product-link block mb-1">
                                        <h3 class="product-name font-bold leading-snug line-clamp-2"><?= htmlspecialchars($rp['name']) ?></h3>
                                    </a>

                                    <!-- Dynamic Rating & Review Row -->
                                    <?php
                                        $rpRatingRaw = (float)($rp['average_rating'] ?? 0);
                                        $rpReviews   = (int)($rp['num_reviews'] ?? 0);
                                        $rpHasRev    = ($rpReviews > 0 || $rpRatingRaw > 0);
                                        $rpStars     = max(0, min(5, (int)round($rpRatingRaw)));
                                        $rpRatingFmt = number_format($rpRatingRaw, 1);
                                    ?>
                                    <div class="product-rating-row flex items-center gap-1.5 my-1 text-xs">
                                        <div class="flex items-center text-amber-400" style="color: var(--pc-star, #f59e0b);">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 <?= ($rpHasRev && $s <= $rpStars) ? 'fill-current' : 'text-gray-200 fill-current' ?>" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="font-bold text-gray-800 text-[11px]"><?= $rpHasRev ? $rpRatingFmt : '0.0' ?></span>
                                        <span class="text-gray-400 text-[10px] font-medium">(<?= $rpReviews ?>)</span>
                                    </div>

                                    <div class="mt-auto">
                                        <!-- Selling Price & Strikethrough (Same Row) -->
                                        <div class="product-price-row flex items-baseline gap-1.5 flex-wrap mb-1">
                                            <span class="product-price font-black tracking-tight">₹<?= format_price($displayPrice) ?></span>
                                            <?php if ($displayOriginalPrice && $displayOriginalPrice > $displayPrice): ?>
                                                <span class="product-orig-price font-bold text-gray-400 line-through">₹<?= format_price($displayOriginalPrice) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- MOQ -->
                                        <?php if ($rpMoq >= 1): ?>
                                         <p class="product-moq-row font-semibold flex items-center gap-1 whitespace-nowrap overflow-hidden text-ellipsis">
                                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Min. Order: <?= htmlspecialchars($rpMoq) ?> Pcs</span>
                                        </p>
                                        <?php endif; ?>

                                        <!-- Add to Cart Button -->
                                        <button type="button" onclick="quickAddToCart(<?= $rp['id'] ?>, <?= $rpMoq ?>, this, event)" class="product-btn w-full font-bold transition-all flex items-center justify-center gap-1.5 hover:opacity-90 shadow-sm cursor-pointer">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <span>Add to Cart</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /Left Column (lg:col-span-8) -->

            <!-- ── Right Column: Order Summary (lg:col-span-4) ── -->
            <div class="w-full lg:col-span-4 min-w-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden lg:sticky lg:top-24">

                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/40">
                        <h2 class="font-black text-gray-900 text-base sm:text-lg">Order Summary</h2>
                    </div>

                    <!-- Line items -->
                    <div class="px-5 py-4 space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span class="font-medium">Subtotal (<span id="cart-page-count"><?= count($cartItems) ?></span> items)</span>
                            <span id="cart-page-subtotal" class="font-bold text-gray-900">₹<?= format_price($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600">
                            <span class="font-medium">Shipping</span>
                            <span class="text-[11px] font-bold text-[#F25996] bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">Calculated at checkout</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="px-5 py-4 bg-gray-50/80 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="font-black text-gray-900 text-base">Total (Est.)</span>
                            <span id="cart-page-total" class="font-black text-gray-900 text-xl">₹<?= format_price(round($subtotal)) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Inclusive of GST. Shipping and fees calculated at checkout</p>
                    </div>

                    <!-- MOQ / Rule Errors -->
                    <div id="cart-page-moq-errors-container" class="px-5 py-3 space-y-2 <?= empty($moqErrors) ? 'hidden' : '' ?>">
                        <?php if (!empty($moqErrors)): ?>
                            <?php foreach($moqErrors as $err): ?>
                            <div class="flex items-start gap-2 p-3 bg-pink-50 text-[#F25996] text-xs rounded-xl border border-pink-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span><?= $err ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="px-5 pb-5 pt-3 space-y-2">
                        <form action="<?= BASE_URL ?>/checkout" method="GET">
                            <button type="submit"
                                    id="cart-page-checkout-btn"
                                    <?= !empty($moqErrors) ? 'disabled' : '' ?>
                                    class="w-full flex items-center justify-center gap-2 bg-[#F25996] text-white py-3.5 px-4 rounded-xl font-black text-sm hover:bg-[#db3e7c] transition-all shadow-md shadow-[#F25996]/20 active:scale-[0.99] <?= !empty($moqErrors) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Proceed to Checkout
                            </button>
                        </form>
                        <a href="<?= BASE_URL ?>/catalog"
                           class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-[#F25996] bg-pink-50 border border-pink-200 hover:bg-pink-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Continue Shopping
                        </a>
                    </div>

                </div><!-- /sticky card -->
            </div><!-- /Right Column -->

        </div><!-- /grid layout -->

        <!-- ── Mobile Sticky Checkout Footer ── -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-2xl px-4 py-2.5 safe-area-pb">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider block leading-none mb-0.5">Total (Est.)</span>
                    <span id="cart-page-mobile-total" class="text-base sm:text-lg font-black text-gray-900">₹<?= format_price(round($subtotal)) ?></span>
                </div>
                <a href="<?= BASE_URL ?>/checkout" id="cart-page-mobile-checkout-btn" class="flex-1 max-w-[200px] bg-[#F25996] hover:bg-[#d8407d] text-white py-2.5 px-4 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-md shadow-[#F25996]/30 active:scale-[0.99] transition-all <?= !empty($moqErrors) ? 'opacity-50 pointer-events-none' : '' ?>">
                    <span>Checkout</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>

        <?php endif; ?>

    </div><!-- /container -->
</div>


<script>
const cartBaseUrl = '<?= BASE_URL ?>';

// Track pending quantities per item (same logic as drawer)
const cartPagePendingQty = {};

document.addEventListener('DOMContentLoaded', function() {
    if (window.initTrackScroll) {
        window.initTrackScroll('cart-related-track');
    }
});

// ── AJAX Add Variant Suggestion (adds as new separate cart line item) ───
function cartPageAddVariant(productId, variantId, quantity, btnEl) {
    if (btnEl) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<span>Adding...</span>';
    }
    fetch(cartBaseUrl + '/cart/ajax-add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: quantity })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Reload to show both variants as separate items one below the other
            window.location.reload();
        } else {
            if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<span>+ Add</span>'; }
            alert(data.message || 'Could not add variant.');
        }
    })
    .catch(() => {
        if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<span>+ Add</span>'; }
    });
}

// ── AJAX Stepper for Cart Page (Ultra Fast & Debounced) ───────────────────
function formatCartPrice(num) {
    if (num === null || num === undefined) return '0';
    num = parseFloat(num);
    if (isNaN(num)) return '0';
    return Number.isInteger(num) ? num.toLocaleString('en-IN') : num.toFixed(2).replace(/\.00$/, '');
}

window.cartPageItemsMap = {
<?php foreach ($cartItems as $ci): ?>
    <?php
        $basePrice = (!empty($ci['base_price']) && (float)$ci['base_price'] > 0) ? (float)$ci['base_price'] : ((!empty($ci['variant_price']) && (float)$ci['variant_price'] > 0) ? (float)$ci['variant_price'] : (float)($ci['wholesale_price'] ?? 0));
        $discountPrice = (!empty($ci['discount_price']) && (float)$ci['discount_price'] > 0) ? (float)$ci['discount_price'] : ((!empty($ci['variant_discount_price']) && (float)$ci['variant_discount_price'] > 0) ? (float)$ci['variant_discount_price'] : null);
        $effPrice = (float)($discountPrice ?? $basePrice);
    ?>
    <?= (int)$ci['cart_item_id'] ?>: {
        price: <?= $effPrice ?>,
        qty: <?= (int)$ci['quantity'] ?>,
        moq: <?= (int)($ci['moq_override'] ?? (!empty($ci['category_moq']) ? $ci['category_moq'] : 1)) ?>,
        maxStock: <?= (int)($ci['available_stock'] ?? 0) ?>
    },
<?php endforeach; ?>
};
const cartPageUpdateTimers = {};
const cartPageRequestSeq = {};

function calculateCartPageSubtotalInstant() {
    let sum = 0;
    for (const id in window.cartPageItemsMap) {
        const it = window.cartPageItemsMap[id];
        sum += ((parseFloat(it.price) || 0) * (parseInt(it.qty) || 0));
    }
    const formatted = '₹' + formatCartPrice(sum);
    const subtotalEl = document.getElementById('cart-page-subtotal');
    const totalEl = document.getElementById('cart-page-total');
    const mobileTotalEl = document.getElementById('cart-page-mobile-total');
    if (subtotalEl) subtotalEl.textContent = formatted;
    if (totalEl) totalEl.textContent = formatted;
    if (mobileTotalEl) mobileTotalEl.textContent = formatted;
}

function cartPageUpdateQty(cartItemId, delta, moq, maxStock, btnEl) {
    const span = document.getElementById('cart-qty-' + cartItemId);
    const stepper = document.getElementById('cart-stepper-' + cartItemId);
    if (!span) return;

    if (!window.cartPageItemsMap[cartItemId]) {
        const itemLi = document.getElementById('cart-page-item-' + cartItemId);
        const price = itemLi ? parseFloat(itemLi.dataset.unitPrice || 0) : 0;
        window.cartPageItemsMap[cartItemId] = {
            price: price,
            qty: parseInt(span.textContent) || moq,
            moq: moq,
            maxStock: maxStock
        };
    }

    const item = window.cartPageItemsMap[cartItemId];
    let newQty = item.qty + delta;
    if (newQty < moq) return;
    if (maxStock > 0 && newQty > maxStock) newQty = maxStock;

    // 0ms in-memory update & instant DOM write
    item.qty = newQty;
    span.textContent = newQty;
    cartPagePendingQty[cartItemId] = newQty;

    // Update stepper button disabled states immediately
    if (stepper) {
        const minusBtn = stepper.querySelector('button:first-child');
        const plusBtn  = stepper.querySelector('button:last-child');
        if (minusBtn) minusBtn.disabled = (newQty <= moq);
        if (plusBtn)  plusBtn.disabled  = (maxStock > 0 && newQty >= maxStock);
    }

    // 0ms Instant subtotal calculation on page (no waiting for server)
    calculateCartPageSubtotalInstant();

    if (cartPageUpdateTimers[cartItemId]) {
        clearTimeout(cartPageUpdateTimers[cartItemId]);
    }

    // Debounce server call by 75ms so rapid clicks are bundled together
    cartPageUpdateTimers[cartItemId] = setTimeout(() => {
        const sendQty = cartPagePendingQty[cartItemId] !== undefined ? cartPagePendingQty[cartItemId] : newQty;
        const seq = (cartPageRequestSeq[cartItemId] || 0) + 1;
        cartPageRequestSeq[cartItemId] = seq;

        fetch(cartBaseUrl + '/cart/ajax-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_id: cartItemId, quantity: sendQty }),
            keepalive: true
        })
        .then(r => r.json())
        .then(data => {
            // If user clicked more since this request was sent, discard stale server response
            if (seq !== cartPageRequestSeq[cartItemId]) {
                return;
            }

            if (cartPagePendingQty[cartItemId] === sendQty) {
                delete cartPagePendingQty[cartItemId];
            }
            delete cartPageUpdateTimers[cartItemId];

            if (data.success && data.cart_data) {
                cartPageUpdateSummary(data.cart_data);
            }
        })
        .catch(() => {
            if (cartPagePendingQty[cartItemId] === sendQty) {
                delete cartPagePendingQty[cartItemId];
            }
            delete cartPageUpdateTimers[cartItemId];
        });
    }, 150);
}

// ── AJAX Remove for Cart Page ────────────────────────────────────────────
function cartPageRemoveItem(cartItemId, btnEl) {
    // Fade out the card
    const card = btnEl ? btnEl.closest('li') : null;
    if (card) { card.style.opacity = '0.4'; card.style.pointerEvents = 'none'; }

    if (window.cartPageItemsMap) {
        delete window.cartPageItemsMap[cartItemId];
        delete cartPagePendingQty[cartItemId];
        calculateCartPageSubtotalInstant();
    }

    fetch(cartBaseUrl + '/cart/ajax-remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart_item_id: cartItemId }),
        keepalive: true
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (card) card.remove();
            if (data.cart_data) cartPageUpdateSummary(data.cart_data);
            // If cart is now empty, reload to show empty state
            if (!data.cart_data || !data.cart_data.items || data.cart_data.items.length === 0) {
                window.location.reload();
            }
        } else {
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
        }
    })
    .catch(() => {
        if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
    });
}

// ── Update subtotal, errors, and button states on the cart page ──────────
function cartPageUpdateSummary(cartData) {
    const subtotalEl     = document.getElementById('cart-page-subtotal');
    const totalEl        = document.getElementById('cart-page-total');
    const mobileTotalEl  = document.getElementById('cart-page-mobile-total');
    const countEl        = document.getElementById('cart-page-count');
    const errContainer   = document.getElementById('cart-page-moq-errors-container');
    const checkoutBtn    = document.getElementById('cart-page-checkout-btn');
    const mobileBtn      = document.getElementById('cart-page-mobile-checkout-btn');

    const hasPendingClicks = Object.keys(cartPagePendingQty).length > 0;

    if (cartData.items && window.cartPageItemsMap) {
        cartData.items.forEach(it => {
            if (window.cartPageItemsMap[it.cart_item_id]) {
                if (cartPagePendingQty[it.cart_item_id] === undefined) {
                    window.cartPageItemsMap[it.cart_item_id].qty = parseInt(it.quantity) || 1;
                }
            }
        });
    }

    // If there are pending clicks, calculate instantly rather than taking older server subtotal
    if (hasPendingClicks) {
        calculateCartPageSubtotalInstant();
    } else {
        if (subtotalEl && cartData.subtotal)    subtotalEl.textContent    = cartData.subtotal;
        if (totalEl && cartData.subtotal)       totalEl.textContent       = cartData.subtotal;
        if (mobileTotalEl && cartData.subtotal) mobileTotalEl.textContent = cartData.subtotal;
    }

    if (countEl && cartData.count !== undefined) countEl.textContent = cartData.count;

    const hasErrors = (cartData.moqErrors && cartData.moqErrors.length > 0) || cartData.hasItemErrors || cartData.count === 0;

    if (errContainer) {
        if (cartData.moqErrors && cartData.moqErrors.length > 0) {
            let html = '';
            cartData.moqErrors.forEach(err => {
                html += `<div class="flex items-start gap-2 p-3 bg-pink-50 text-[#F25996] text-xs rounded-xl border border-pink-200">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>${err}</span>
                </div>`;
            });
            errContainer.innerHTML = html;
            errContainer.classList.remove('hidden');
        } else {
            errContainer.innerHTML = '';
            errContainer.classList.add('hidden');
        }
    }

    if (checkoutBtn) {
        if (hasErrors) {
            checkoutBtn.setAttribute('disabled', 'disabled');
            checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            checkoutBtn.removeAttribute('disabled');
            checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    if (mobileBtn) {
        if (hasErrors) {
            mobileBtn.classList.add('opacity-50', 'pointer-events-none');
        } else {
            mobileBtn.classList.remove('opacity-50', 'pointer-events-none');
        }
    }
}

// Flush any pending qty on page hide/unload
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
        Object.entries(cartPagePendingQty).forEach(([id, qty]) => {
            fetch(cartBaseUrl + '/cart/ajax-update', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: parseInt(id), quantity: qty }),
                keepalive: true
            });
        });
    }
});
window.addEventListener('beforeunload', () => {
    Object.entries(cartPagePendingQty).forEach(([id, qty]) => {
        fetch(cartBaseUrl + '/cart/ajax-update', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_id: parseInt(id), quantity: qty }),
            keepalive: true
        });
    });
});
</script>
