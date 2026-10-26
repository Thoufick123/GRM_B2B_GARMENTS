<div class="bg-gray-50 min-h-screen pb-6 md:pb-8">

    <!-- ── Breadcrumb Section (Matches Product Details & Catalog Bar Style) ── -->
    <div class="bg-white border-b border-gray-200/80 mb-5 md:mb-6">
        <div class="w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
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
                <a href="<?= BASE_URL ?>/customize" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0">Customize</a>
                <svg class="w-3.5 h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-[#F25996] font-bold shrink-0 truncate" aria-current="page"><?= htmlspecialchars($customization['fabric_name']) ?></span>
            </nav>
        </div>
    </div>

    <div class="w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8">

        <?php 
            $rawImages = !empty($customization['images']) && is_array($customization['images']) ? array_values(array_filter($customization['images'])) : [];
            $fallbackImg = 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=800&auto=format&fit=crop&q=80';
            $galleryList = !empty($rawImages) ? array_slice($rawImages, 0, 6) : [$fallbackImg];
            $mainImgUrl = get_image_url($galleryList[0]);

            $baseP = (float)($customization['fabric_base_price'] ?? 0);
            $discP = (float)($customization['fabric_discount_price'] ?? 0);
            if ($discP <= 0 && !empty($customization['fabric_price'])) {
                $discP = (float)$customization['fabric_price'];
            }
            $discPercent = ($baseP > 0 && $discP > 0 && $discP < $baseP) 
                ? round((($baseP - $discP) / $baseP) * 100) 
                : 0;

            $isWishlisted = !empty($wishlistIds) && in_array((int)$customization['fabric_id'], $wishlistIds);

            $savedSizes = !empty($savedSizes) && is_array($savedSizes) ? $savedSizes : [];
            $savedSets = !empty($savedSets) && is_array($savedSets) ? $savedSets : [];
            $savedQty = !empty($savedQty) && (int)$savedQty > 0 ? (int)$savedQty : 1;
            $savedPkg = !empty($savedPkg) ? $savedPkg : 'with_cover';
            $reqPcsForOneQty = isset($customization['required_pieces']) && (int)$customization['required_pieces'] > 0 
                ? (int)$customization['required_pieces'] 
                : (int)($customization['minimum_pieces'] ?? 35);
            $maxPcsForOneQty = isset($customization['maximum_pieces']) && (int)$customization['maximum_pieces'] > 0 
                ? (int)$customization['maximum_pieces'] 
                : (int)($reqPcsForOneQty * 10);
        ?>

        <!-- ── Top Fabric Product Showcase Hero (Matches Screenshot Layout) ── -->
        <div class="bg-white rounded-3xl border border-gray-100 p-5 sm:p-7 lg:p-8 shadow-xs mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                
                <!-- Left Column: Image Gallery (Vertical Thumbnails on Left + 3:4 Main Image on Right) -->
                <div class="lg:col-span-6 xl:col-span-6 w-full min-w-0 space-y-6">
                    <div class="flex flex-col-reverse md:flex-row gap-3 sm:gap-4 items-start w-full">
                        
                        <!-- Left Vertical Thumbnails Strip -->
                        <?php if (count($galleryList) > 1): ?>
                        <div id="fabricThumbnails" class="flex md:flex-col gap-2.5 sm:gap-3 overflow-x-auto md:overflow-y-auto max-h-[660px] xl:max-h-[720px] w-full md:w-20 shrink-0 no-scrollbar order-2 md:order-1 pb-1 md:pb-0">
                            <?php foreach ($galleryList as $idx => $gImg): 
                                $tUrl = get_image_url($gImg, 200, 200);
                            ?>
                            <button type="button" 
                                    onclick="setFabricActiveImage(<?= $idx ?>)"
                                    onmouseenter="setFabricActiveImage(<?= $idx ?>)"
                                    id="fabric_thumb_btn_<?= $idx ?>"
                                    class="fabric-thumb-btn w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 aspect-square rounded-2xl bg-white overflow-hidden border-2 transition-all cursor-pointer shrink-0 <?= $idx === 0 ? 'border-[#F25996] ring-2 ring-[#F25996]/30 shadow-xs' : 'border-gray-200 hover:border-[#F25996]/50 opacity-80 hover:opacity-100' ?>">
                                <img src="<?= htmlspecialchars($tUrl) ?>" 
                                     alt="" 
                                     onerror="this.src='<?= $fallbackImg ?>';" 
                                     class="w-full h-full object-cover">
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Main Showcase Image Frame (3:4 aspect ratio) -->
                        <div class="relative flex-1 w-full bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm group cursor-pointer order-1 md:order-2" onclick="openFabricModal(fabricActiveImgIndex)" title="Click to view full image">
                            
                            <!-- Top-Left Scallop Starburst Discount Rosette Badge (Aqua Blue) -->
                            <?php if ($discPercent > 0 && function_exists('render_discount_starburst')): ?>
                            <div class="absolute top-3.5 left-3.5 z-20 w-16 h-16 sm:w-20 sm:h-20 drop-shadow-md pointer-events-none transition-transform duration-300 group-hover:scale-105">
                                <?= render_discount_starburst($discPercent, 'w-full h-full') ?>
                            </div>
                            <?php else: ?>
                            <span class="absolute top-3.5 left-3.5 z-20 px-3 py-1 bg-[#F25996] text-white font-black text-[10px] uppercase tracking-wider rounded-lg shadow-sm">
                                ✂️ Custom Ready
                            </span>
                            <?php endif; ?>

                            <!-- Bottom-Right Click to Zoom Pill -->
                            <div class="absolute bottom-3.5 right-3.5 z-20 bg-black/60 text-white text-xs px-3.5 py-1.5 rounded-full backdrop-blur-sm flex items-center gap-1.5 shadow-md pointer-events-none select-none transition-all duration-200 group-hover:bg-black/80">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Click to zoom</span>
                            </div>

                            <!-- 3:4 Aspect Ratio Portrait Container -->
                            <div class="w-full overflow-hidden flex items-center justify-center bg-gray-50/40 rounded-3xl" style="aspect-ratio: 3 / 4; min-height: 540px; max-height: 720px; width: 100%;">
                                <img id="fabricMainImage" 
                                     src="<?= htmlspecialchars($mainImgUrl) ?>" 
                                     alt="<?= htmlspecialchars($customization['fabric_name']) ?>" 
                                     onerror="this.onerror=null; this.src='<?= $fallbackImg ?>';" 
                                     class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105"
                                     style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </div>
                        </div>

                    </div>

                    <!-- Fabric Description & Details Card (Admin Added Description) -->
                    <div class="bg-gradient-to-br from-pink-50/30 via-white to-gray-50/50 rounded-3xl p-5 sm:p-6 border border-gray-200/90 shadow-2xs space-y-4 w-full min-w-0 max-w-full overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                <span>📄 Fabric Description &amp; Details</span>
                            </h3>
                            <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full bg-pink-50 text-[#F25996] border border-pink-200">
                                Fabric Details
                            </span>
                        </div>

                        <!-- Admin Added Description -->
                        <div class="text-xs sm:text-sm text-gray-700 leading-relaxed w-full min-w-0 max-w-full overflow-hidden">
                            <?php 
                            $rawDesc = trim($customization['fabric_description'] ?? '');
                            if (!empty($rawDesc)): 
                                $paragraphs = explode("\n", $rawDesc);
                            ?>
                                <div class="text-gray-700 leading-relaxed bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-2xs text-xs sm:text-sm space-y-2.5 break-words overflow-hidden" style="word-break: break-word; overflow-wrap: anywhere; overflow: hidden; max-width: 100%;">
                                    <?php foreach ($paragraphs as $p): 
                                        $p = trim($p);
                                        if ($p !== ''):
                                    ?>
                                        <p class="leading-relaxed break-words m-0" style="word-break: break-word; overflow-wrap: anywhere; margin-bottom: 0.5rem;"><?= htmlspecialchars($p) ?></p>
                                    <?php 
                                        endif;
                                    endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-gray-400 italic bg-white p-4 rounded-2xl border border-gray-100">No description provided for this fabric.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Details & Step 1 Sizing Matrix -->
                <div class="lg:col-span-6 xl:col-span-6 flex flex-col justify-start space-y-4">
                    
                    <!-- Category / Program Pill Badge -->
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-pink-50 text-[#F25996] text-xs font-bold border border-pink-100 self-start">
                        <span><?= htmlspecialchars($customization['fabric_name']) ?> | Customization</span>
                        <span>•</span>
                        <span><?= htmlspecialchars($customization['garment_type'] ?? 'Custom Garment') ?></span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 font-display tracking-tight leading-tight">
                        <?= htmlspecialchars($customization['fabric_name']) ?>
                    </h1>

                    <!-- Meta: SKU -->
                    <div class="flex items-center gap-3 text-xs text-gray-500 flex-wrap">
                        <span>SKU: <strong class="text-gray-800 font-mono" id="fabricSkuDisplay"><?= htmlspecialchars(!empty($customization['fabric_sku']) ? $customization['fabric_sku'] : ($customization['sizes'][0]['variant_sku'] ?? 'N/A')) ?></strong></span>
                    </div>

                    <!-- Star Rating Row -->
                    <?php
                        $avgCustomRating = !empty($customizationStats['avg_rating']) ? (float)$customizationStats['avg_rating'] : 0.0;
                        $totalCustomRevs = !empty($customizationStats['total_reviews']) ? (int)$customizationStats['total_reviews'] : 0;
                    ?>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <svg class="w-4 h-4 <?= ($totalCustomRevs > 0 && $i <= round($avgCustomRating)) ? 'text-amber-400 fill-current' : 'text-gray-200 fill-current' ?>" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <?php endfor; ?>
                        </div>
                        <?php if ($totalCustomRevs > 0): ?>
                            <span class="text-xs font-bold text-gray-800"><?= number_format($avgCustomRating, 1) ?></span>
                            <a href="#customReviewsSection" class="text-xs text-[#F25996] hover:underline font-semibold">
                                (<?= $totalCustomRevs ?> <?= $totalCustomRevs === 1 ? 'Review' : 'Reviews' ?>)
                            </a>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-gray-400">Not rated yet</span>
                            <a href="#customReviewsSection" class="text-xs text-[#F25996] hover:underline font-semibold">
                                (No reviews yet)
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Available Stock (in Emerald Green bold) -->
                    <div class="text-sm font-bold text-emerald-600 flex items-center gap-1.5">
                        <span>Available Stock:</span>
                        <span class="text-emerald-700 font-extrabold"><?= number_format((int)$customization['fabric_stock']) ?> Qty</span>
                    </div>

                    <!-- Step 1: Custom Stitching & Sizing Workshop -->
                    <div class="pt-4 border-t border-gray-100 space-y-5">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 font-display flex items-center gap-2">
                                    <span>📏 Select Sizes & Stitch Pieces</span>
                                </h3>
                                <span class="text-xs font-semibold px-2.5 py-1 bg-pink-50 text-[#F25996] rounded-full border border-pink-200">
                                    Step 1 of 2
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Choose how many pieces you would like to stitch for each available size. Fabric consumption is automatically computed per piece based on admin engineering rules.
                            </p>
                        </div>

                        <!-- Fabric Quantity (Multiplier) Section -->
                        <div class="bg-gradient-to-r from-pink-50/80 via-rose-50/50 to-pink-50/80 p-3.5 sm:p-4 rounded-2xl border border-pink-200 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#F25996] text-white font-black text-lg flex items-center justify-center shadow-xs shrink-0">
                                    📦
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2 flex-wrap">
                                        <span>Fabric Quantity / Sets</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-pink-100 text-[#db2777] border border-pink-300/70" id="fabricQtyBadge">
                                            <?= $savedQty ?> Set<?= $savedQty > 1 ? 's' : '' ?> (<?= $savedQty ?>x)
                                        </span>
                                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-white text-[#F25996] border border-pink-300 shadow-2xs" id="fabricRequiredBadge">
                                            Required: <strong id="fabricRequiredPcs"><?= (int)($reqPcsForOneQty * $savedQty) ?> Pcs</strong> (Max: <strong id="fabricMaxPcs"><?= (int)($maxPcsForOneQty * $savedQty) ?> Pcs</strong>)
                                        </span>
                                    </h4>
                                    <p class="text-[11px] text-gray-500">Limits scale automatically for the chosen quantity.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="inline-flex items-center border-2 border-[#F25996] rounded-full bg-white px-2 py-0.5 shadow-xs">
                                    <button type="button" onclick="adjustFabricMultiplier(-1)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center font-black text-lg text-[#F25996] hover:bg-pink-50 transition-colors select-none cursor-pointer">
                                        -
                                    </button>
                                    <div class="h-4 w-px bg-pink-200 mx-1"></div>
                                    <input type="number" id="fabric_multiplier" value="<?= $savedQty ?>" min="1" max="999" oninput="onFabricMultiplierChange(this)" class="w-12 h-7 sm:h-8 text-center font-black text-sm sm:text-base text-[#F25996] bg-transparent focus:outline-none">
                                    <div class="h-4 w-px bg-pink-200 mx-1"></div>
                                    <button type="button" onclick="adjustFabricMultiplier(1)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center font-black text-lg text-[#F25996] hover:bg-pink-50 transition-colors select-none cursor-pointer">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Size Variant Buttons Grid -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                    <span>1. Choose Size Variants:</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-pink-50 text-[#F25996] font-bold border border-pink-200" id="currentConfigSetLabel">Roll / Set #1</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="selectAllVariants()" class="text-xs font-bold text-[#F25996] hover:underline cursor-pointer">Select All</button>
                                    <span class="text-gray-300">•</span>
                                    <button type="button" onclick="clearAllVariants()" class="text-xs font-bold text-gray-500 hover:text-red-500 cursor-pointer">Clear</button>
                                </div>
                            </div>

                            <!-- Roll / Set Selector Tabs (when multiple sets exist) -->
                            <div id="rollTabsContainer" class="flex items-center gap-1.5 overflow-x-auto pb-1 mb-2.5 hide-scrollbar">
                                <!-- Populated dynamically by renderRollTabs() -->
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3" id="variantButtonsGrid">
                                <?php if (!empty($customization['sizes'])): ?>
                                    <?php foreach ($customization['sizes'] as $sz): 
                                        $baseQty = isset($sz['base_quantity']) ? (int)$sz['base_quantity'] : 2;
                                        $maxQty = isset($sz['max_quantity']) && (int)$sz['max_quantity'] > 0 ? (int)$sz['max_quantity'] : 4;
                                        $fabricPricePerMeter = (float)($customization['fabric_price'] ?? 0);
                                        $consumptionRate = (float)($sz['fabric_consumption'] ?? 1);
                                        $pricePerPiece = round($fabricPricePerMeter * $consumptionRate, 2);
                                    ?>
                                        <button type="button" 
                                                id="btn_size_<?= htmlspecialchars($sz['size_name']) ?>"
                                                onclick="toggleSizeVariant('<?= htmlspecialchars($sz['size_name']) ?>')"
                                                data-size="<?= htmlspecialchars($sz['size_name']) ?>"
                                                data-sku="<?= htmlspecialchars($sz['variant_sku'] ?? '') ?>"
                                                data-base-qty="<?= $baseQty ?>"
                                                data-base-max="<?= $maxQty ?>"
                                                data-price-per-pc="<?= $pricePerPiece ?>"
                                                data-consumption="<?= $consumptionRate ?>"
                                                class="variant-btn relative p-3 rounded-2xl border-2 transition-all text-left flex flex-col justify-between group cursor-pointer border-gray-200 bg-white hover:border-pink-300 hover:shadow-xs">
                                            
                                            <!-- Top: Size Badge & Piece Count & Check Icon -->
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="w-7 h-7 rounded-xl bg-pink-50 text-[#F25996] font-black text-xs flex items-center justify-center border border-pink-200 group-hover:scale-105 transition-transform">
                                                    <?= htmlspecialchars($sz['size_name']) ?>
                                                </span>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-gray-400 group-hover:text-[#F25996]" id="btn_count_badge_<?= htmlspecialchars($sz['size_name']) ?>">0</span>
                                                    <span class="check-icon w-4.5 h-4.5 rounded-full border border-gray-300 flex items-center justify-center text-[9px] text-transparent group-hover:border-pink-400 font-bold transition-all">
                                                        ✓
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Price Per Piece -->
                                            <div class="text-xs font-bold text-gray-900 mb-0.5">
                                                ₹<?= number_format($pricePerPiece, 2) ?><span class="text-[10px] text-gray-500 font-normal">/pc</span>
                                            </div>

                                            <!-- Base & Max Info -->
                                            <div class="text-[10px] text-gray-500 space-y-0.5 border-t border-gray-100 pt-1 mt-1">
                                                <div class="flex items-center justify-between">
                                                    <span>Required:</span>
                                                    <strong class="text-[#F25996] font-bold" id="btn_range_label_<?= htmlspecialchars($sz['size_name']) ?>"><?= $baseQty ?> – <?= $maxQty ?> pcs</strong>
                                                </div>
                                            </div>
                                        </button>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Active Size Quantity Adjusters Section (Dynamic Set Containers) -->
                        <div id="activeSizesSection" class="space-y-3 pt-2 border-t border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs font-bold text-gray-700">
                                <span class="uppercase tracking-wider">2. Selected Size Quantities (By Fabric Set):</span>
                                <span class="text-[#F25996] font-medium text-[11px]" id="activeVariantNotice"></span>
                            </div>

                            <!-- Empty State Placeholder when no variant selected -->
                            <div id="emptyVariantsPlaceholder" class="p-6 text-center bg-white rounded-2xl border border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 hidden">
                                <div class="w-10 h-10 rounded-full bg-pink-50 text-[#F25996] flex items-center justify-center text-lg">
                                    👆
                                </div>
                                <p class="text-xs font-bold text-gray-700">No Size Variants Selected</p>
                                <p class="text-[11px] text-gray-500 max-w-sm">Click on any size variant buttons above to choose the sizes and pieces you want to customize.</p>
                            </div>

                            <!-- Dynamic Fabric Set Containers List -->
                            <div id="fabricSetsContainer" class="space-y-4">
                                <!-- Generated dynamically via renderFabricSetContainers() -->
                            </div>
                        </div>

                    <!-- Packaging Cover Option Selection -->
                    <div class="mt-5 bg-gradient-to-r from-pink-50/60 via-purple-50/30 to-pink-50/60 p-4 rounded-2xl border border-pink-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-1.5">
                                <span>Packaging Type:</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-pink-100 text-[#F25996]">Choose Cover</span>
                            </label>
                            <?php if ((float)($customization['without_cover_discount_percentage'] ?? 0) > 0): ?>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    Save <?= number_format($customization['without_cover_discount_percentage'], 1) ?>% without cover
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <!-- With Cover Option -->
                            <button type="button" 
                                    id="optWithCover" 
                                    onclick="selectPackagingOption('with_cover')"
                                    class="packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-[#F25996] bg-white ring-2 ring-[#F25996]/20 shadow-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-gray-900 flex items-center gap-1">
                                        <span>🛍️ With Cover</span>
                                    </span>
                                    <span class="pkg-check w-4 h-4 rounded-full bg-[#F25996] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                </div>
                                <p class="text-[10px] text-gray-500 leading-tight">Individual protective garment poly cover packing.</p>
                                <span class="inline-block mt-1 text-[9px] font-bold text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded">Standard Rate</span>
                            </button>

                            <!-- Without Cover Option -->
                            <button type="button" 
                                    id="optWithoutCover" 
                                    onclick="selectPackagingOption('without_cover')"
                                    class="packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-gray-200 bg-white hover:border-[#F25996]/50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black text-gray-900 flex items-center gap-1">
                                        <span>📦 Without Cover</span>
                                    </span>
                                    <span class="pkg-check w-4 h-4 rounded-full border border-gray-300 text-transparent flex items-center justify-center text-[10px] font-bold">✓</span>
                                </div>
                                <p class="text-[10px] text-gray-500 leading-tight">Bulk loose packing directly in carton.</p>
                                <?php if ((float)($customization['without_cover_discount_percentage'] ?? 0) > 0): ?>
                                    <span class="inline-block mt-1 text-[9px] font-black text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-200">
                                        -<?= number_format($customization['without_cover_discount_percentage'], 1) ?>% Off
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block mt-1 text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">Standard</span>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>

                    <!-- Piece Count & Constraint Tracker (Matches Image 3) -->
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-2">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="font-bold text-gray-800">Total Stitch Pieces:</span>
                            <span class="font-black text-sm sm:text-base text-gray-900" id="summaryTotalPieces">0 / <?= (int)($reqPcsForOneQty * $savedQty) ?>–<?= (int)($maxPcsForOneQty * $savedQty) ?> Pcs</span>
                        </div>
                        
                        <!-- Visual Progress Meter -->
                        <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                            <div id="pieceProgressBar" class="h-full bg-[#F25996] transition-all duration-300" style="width: 0%;"></div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] sm:text-xs text-gray-500 font-medium" id="pieceConstraintText">
                            <span>Min / Roll: <strong class="text-gray-900 font-bold" id="summaryMinPcsDisplay"><?= (int)$reqPcsForOneQty ?> Pcs</strong></span>
                            <span>Max / Roll: <strong class="text-gray-900 font-bold" id="summaryMaxPcsDisplay"><?= (int)$maxPcsForOneQty ?> Pcs</strong></span>
                        </div>
                    </div>

                    <!-- Live Summary Highlights (Compact Card) -->
                    <div class="bg-gradient-to-br from-pink-50/60 to-purple-50/30 p-4 rounded-2xl border border-pink-100 space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 font-medium">Calculation Status:</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0 whitespace-nowrap" id="liveStatusBadge">
                                Live Ready
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            <span>Total Required Fabric:</span>
                            <span class="font-bold text-gray-900" id="summaryRequiredFabric">0.00 <?= htmlspecialchars($customization['unit'] ?? 'Meters') ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            <span>Fabric Stock Available:</span>
                            <span class="font-bold text-emerald-700" id="summaryStockDisplay"><?= (int)$customization['fabric_stock'] ?> Qty</span>
                        </div>
                        
                        <!-- Hidden elements to preserve ID bindings for JS calculations -->
                        <span id="summaryBaseConsumption" class="hidden">0.00</span>
                        <span id="summaryWastageAmount" class="hidden">+0.00</span>
                        <span id="summaryGstAmount" class="hidden">₹0.00</span>
                        <!-- Without Cover Discount Row -->
                        <div id="packagingDiscountRow" class="hidden items-center justify-between text-xs bg-emerald-50 text-emerald-800 px-2.5 py-1.5 rounded-xl border border-emerald-200">
                            <span class="flex items-center gap-1 font-bold">
                                <span>📦 Without Cover Discount</span>
                                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full bg-emerald-200/70 text-emerald-900" id="summaryCoverDiscountPct">(-0%)</span>
                            </span>
                            <span class="font-black text-emerald-700" id="summaryCoverDiscount">-₹0.00</span>
                        </div>
                        <span id="deliveryDaysBadge" class="hidden"></span>
                        <span id="deliveryExpectedDate" class="hidden"></span>
                        <span id="deliveryScaleNote" class="hidden"></span>
                        <input type="hidden" id="shippingAddressInput" value="">
                        <input type="hidden" id="orderNotesInput" value="">

                        <div class="flex items-center justify-between pt-2.5 border-t border-pink-200/60">
                            <div>
                                <span class="text-xs font-bold text-gray-800 block">Estimated Subtotal:</span>
                                <span class="text-[10px] text-gray-400">Includes 5% GST &amp; discounts</span>
                            </div>
                            <span class="text-2xl font-black text-[#F25996]" id="summaryGrandTotal">₹0.00</span>
                        </div>
                    </div>

                    <!-- Error Alert Box (if validation fails) -->
                    <div id="errorAlertBox" class="hidden p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold"></div>

                    <!-- Step 1 to Step 2 Proceed to Checkout CTA -->
                    <div class="space-y-2.5 pt-2">
                        <button type="button" 
                                id="btnProceedCheckout" 
                                onclick="proceedToCheckout()" 
                                class="w-full py-4 px-6 bg-gradient-to-r from-[#F25996] via-[#ea3c85] to-[#db2777] hover:opacity-95 text-white font-black text-base rounded-2xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2.5 cursor-pointer group">
                            <span>Proceed to Checkout</span>
                            <svg class="w-5 h-5 text-white transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                        <!-- Keep a hidden button for any legacy bindings -->
                        <button type="button" id="btnPayRazorpay" class="hidden"></button>

                        <div class="flex items-center justify-center gap-2 text-[11px] text-gray-400 font-medium">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                            </svg>
                            <span>Next: Review sizes, delivery address, subtotals &amp; secure Razorpay payment</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        </div>

        <!-- ── You May Also Like Section (Matches Product Details Page) ── -->
        <?php 
        $relatedProducts = $relatedProducts ?? [];
        if (!empty($relatedProducts)): 
        ?>
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
        <div class="fbt-section mt-8 md:mt-12 border-t border-gray-200/80 pt-6 md:pt-10 mb-8 w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-3 md:mb-6 px-1 md:px-0">
                <div>
                    <h2 class="text-lg md:text-2xl font-black text-gray-900 tracking-tight">You May Also Like</h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5 hidden sm:block">Recommended customizable garments &amp; matching collections</p>
                </div>
                <a href="<?= BASE_URL ?>/customize" class="text-xs md:text-sm font-bold text-[#F25996] hover:underline flex items-center gap-1">
                    <span>View all</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="fbt-track flex overflow-x-auto pb-4 px-1 md:px-0 gap-2.5 sm:gap-4 snap-x snap-mandatory sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 sm:overflow-visible style-scrollbars items-stretch">
                <?php foreach($relatedProducts as $relProduct): ?>
                    <?php
                    $relPrimaryImage = !empty($relProduct['primary_image']) ? get_image_url($relProduct['primary_image']) : 'https://placehold.co/400x400/f3f4f6/9ca3af?text=No+Image';
                    $relMoq = $relProduct['custom_moq'] ?? $relProduct['minimum_pieces'] ?? $relProduct['moq_override'] ?? $relProduct['category_moq'] ?? 35;
                    $relCustomizeUrl = BASE_URL . '/fabrics/customize?fabric_id=' . (int)$relProduct['id'];
                    $relCatName = !empty($relProduct['category_name']) ? $relProduct['category_name'] : (!empty($relProduct['subcategory_name']) ? $relProduct['subcategory_name'] : 'MATERNITY');
                    $relGarmentType = !empty($relProduct['garment_type']) ? $relProduct['garment_type'] : 'CUSTOM GARMENT';
                    $relStock = isset($relProduct['stock_quantity']) ? (int)$relProduct['stock_quantity'] : 100;
                    $relImages = !empty($relProduct['images']) && is_array($relProduct['images']) ? array_values(array_filter($relProduct['images'])) : (!empty($relProduct['primary_image']) ? [$relProduct['primary_image']] : []);
                    if ((empty($relImages) || count($relImages) <= 1) && !empty($relProduct['id'])) {
                        try {
                            $db = \Core\Database::getInstance();
                            $relFallbackImgs = $db->query("SELECT image_path FROM product_images WHERE product_id = " . (int)$relProduct['id'] . " ORDER BY is_primary DESC, sort_order ASC, id ASC")->fetchAll(\PDO::FETCH_COLUMN);
                            if (!empty($relFallbackImgs) && count($relFallbackImgs) > 1) {
                                $relImages = $relFallbackImgs;
                            }
                        } catch (\Throwable $e) {}
                    }
                    $relSizes = !empty($relProduct['sizes']) && is_array($relProduct['sizes']) ? $relProduct['sizes'] : ['3XL', '4XL', 'L', 'M', 'XL', 'XXL'];
                    
                    $cleanDesc = !empty($relProduct['description']) 
                        ? trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r", "\n"], ' ', strip_tags($relProduct['description'])))) 
                        : 'Soft, breathable cotton fabric perfect for maternity wear.';
                    ?>
                    <div class="product-card h-full w-[calc(50%-0.25rem)] min-w-[calc(50%-0.25rem)] sm:w-full sm:min-w-0 snap-start bg-white rounded-2xl sm:rounded-[22px] border border-gray-100 overflow-hidden shadow-xs hover:shadow-xl hover:border-pink-200 transition-all duration-300 flex flex-col group transform hover:-translate-y-1 relative shrink-0 sm:shrink">
                        
                        <!-- Top Image Area -->
                        <div class="relative h-44 sm:h-64 md:h-72 w-full bg-gradient-to-br from-pink-50 to-rose-50 overflow-hidden">
                            <img id="rel-main-img-<?= $relProduct['id'] ?>" 
                                 src="<?= htmlspecialchars($relPrimaryImage) ?>" 
                                 alt="<?= htmlspecialchars($relProduct['name']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Top Left Badges: CUSTOM & Garment Type -->
                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 pointer-events-none z-10 flex flex-col gap-1 sm:gap-1.5 items-start">
                                <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-[#F25996] text-white font-extrabold text-[8.5px] sm:text-[10px] uppercase tracking-wider rounded sm:rounded-lg shadow-sm flex items-center gap-0.5 sm:gap-1">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/></svg>
                                    <span>CUSTOM</span>
                                </span>
                                <?php if (!empty($relGarmentType)): ?>
                                    <span class="px-1.5 sm:px-2 py-0.5 bg-black/65 backdrop-blur-xs text-white font-bold text-[8.5px] sm:text-[10px] uppercase tracking-wider rounded sm:rounded-md shadow-xs">
                                        <?= htmlspecialchars($relGarmentType) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Top Right: Stock Counter -->
                            <div class="absolute top-2 right-2 sm:top-3.5 sm:right-3.5 z-10 flex flex-col items-end gap-1 sm:gap-2">
                                <?php if ($relStock > 0): ?>
                                    <span class="px-1.5 sm:px-2.5 py-0.5 bg-emerald-600/95 backdrop-blur-xs text-white font-bold text-[8px] sm:text-[10px] tracking-wide rounded-full shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                        <?= $relStock ?> Qty
                                    </span>
                                <?php else: ?>
                                    <span class="px-1.5 sm:px-2.5 py-0.5 bg-amber-600/95 backdrop-blur-xs text-white font-bold text-[8px] sm:text-[10px] tracking-wide rounded-full shadow-sm flex items-center gap-1">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Circular Variant Swatches Overlapping Border (when multiple images exist) -->
                        <?php if (count($relImages) > 1): ?>
                        <div class="-mt-4 sm:-mt-6 mb-1.5 sm:mb-2 px-2.5 sm:px-5 flex items-center gap-1.5 sm:gap-2 relative z-20 overflow-x-auto scrollbar-none pb-0.5">
                            <?php 
                                foreach (array_slice($relImages, 0, 5) as $idx => $swImg):
                                    $swUrl = get_image_url($swImg, 100, 100);
                                    $fullSwUrl = get_image_url($swImg);
                            ?>
                                <img src="<?= htmlspecialchars($swUrl) ?>" 
                                     alt="" 
                                     onerror="if(!this.dataset.retried){this.dataset.retried='1';this.src='<?= htmlspecialchars($fullSwUrl) ?>';}else{this.style.display='none';}"
                                     onclick="document.getElementById('rel-main-img-<?= $relProduct['id'] ?>').src='<?= htmlspecialchars($fullSwUrl) ?>'; this.parentElement.querySelectorAll('img').forEach(function(i){i.classList.remove('ring-2', 'ring-[#F25996]')}); this.classList.add('ring-2', 'ring-[#F25996]');"
                                     class="w-7 h-7 sm:w-11 sm:h-11 rounded-full border-2 border-white shadow-sm sm:shadow-md object-cover cursor-pointer hover:scale-110 transition-transform flex-shrink-0 <?= $idx === 0 ? 'ring-2 ring-[#F25996]' : '' ?>">
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="h-2 sm:h-3"></div>
                        <?php endif; ?>

                        <!-- Card Content Body -->
                        <div class="px-2.5 sm:px-5 pt-0 pb-3 sm:pb-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Category Label in Pink -->
                                <div class="text-[#F25996] font-bold text-[9px] sm:text-[11px] uppercase tracking-wide mb-0.5 sm:mb-1">
                                    <?= htmlspecialchars($relCatName) ?>
                                </div>

                                <!-- Product Title -->
                                <h2 class="font-bold text-[#1e293b] text-xs sm:text-base leading-snug line-clamp-1 mb-0.5 sm:mb-1 hover:text-[#F25996] transition-colors" title="<?= htmlspecialchars($relProduct['name']) ?>">
                                    <a href="<?= $relCustomizeUrl ?>"><?= htmlspecialchars($relProduct['name']) ?></a>
                                </h2>

                                <!-- Description -->
                                <p class="text-[10px] sm:text-xs text-[#64748b] line-clamp-1 sm:line-clamp-2 mb-2 sm:mb-3 leading-tight sm:leading-relaxed">
                                    <?= htmlspecialchars($cleanDesc) ?>
                                </p>

                                <!-- Custom Stitching & Min Stitch (Row 1 & 2 on mobile/tablet, same row on desktop) -->
                                <div class="mb-2 sm:mb-3">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-1.5 lg:gap-1">
                                        <div class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg bg-pink-50/80 border border-pink-100/90 text-[#F25996] text-[9.5px] sm:text-xs font-bold shrink-0 w-fit">
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-[#F25996] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/>
                                            </svg>
                                            <span>Custom Stitching</span>
                                        </div>

                                        <!-- Min Stitch & Count Badge: Next row on mobile & tablet, right on desktop -->
                                        <div class="inline-flex items-center gap-1 shrink-0 whitespace-nowrap self-start lg:self-auto">
                                            <span class="uppercase font-semibold text-gray-400 tracking-wider" style="font-size: 7.5px; line-height: 1;">Min Stitch</span>
                                            <span class="px-1.5 py-0.5 bg-pink-50 text-[#F25996] border border-pink-200/80 font-extrabold text-[9px] sm:text-[10.5px] rounded-md leading-none">
                                                <?= (int)$relMoq ?> Pcs
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sizes Matrix -->
                                <div class="mb-2.5 sm:mb-4">
                                    <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
                                        <?php foreach ($relSizes as $sIndex => $szName): ?>
                                            <?php if ($sIndex === 0): ?>
                                                <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-[#F25996] text-white font-bold text-[9px] sm:text-[11px] rounded sm:rounded-md shadow-xs flex items-center justify-center min-w-[20px] sm:min-w-[28px]">
                                                    <?= htmlspecialchars($szName) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-[#fff0f6] text-[#db2777] font-bold text-[9px] sm:text-[11px] rounded sm:rounded-md border border-pink-100/80 flex items-center justify-center min-w-[20px] sm:min-w-[28px]">
                                                    <?= htmlspecialchars($szName) ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button: Customize & Stitch → -->
                            <div>
                                <a href="<?= $relCustomizeUrl ?>" 
                                   class="w-full py-2 sm:py-3 px-2 sm:px-4 bg-gradient-to-r from-[#F25996] via-[#ea3c85] to-[#db2777] hover:opacity-95 text-white font-bold text-xs sm:text-sm rounded-xl sm:rounded-2xl shadow-sm sm:shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1 sm:gap-2 group/btn active:scale-[0.98]">
                                    <span class="truncate">Customize & Stitch</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Customer Reviews Section (Matches Product Details Page Pink Container Style) ── -->
        <?php
            $crList = !empty($customizationReviews) ? $customizationReviews : [];
            $crTotal = count($crList);
            $crCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($crList as $crItem) {
                $r = (int)($crItem['rating'] ?? 5);
                if ($r < 1) $r = 1;
                if ($r > 5) $r = 5;
                $crCounts[$r]++;
            }
            $crAvg = $crTotal > 0 ? round(array_sum(array_column($crList, 'rating')) / $crTotal, 1) : (!empty($customizationStats['avg_rating']) ? (float)$customizationStats['avg_rating'] : 0.0);
        ?>
        <div id="customReviewsSection" class="w-full max-w-full mx-auto py-2 sm:py-4 mb-4 sm:mb-6">
            <div class="rounded-2xl sm:rounded-3xl shadow-sm overflow-hidden" style="background-color: #fce7f1; border: 1.5px solid #fbcfe8;">

                <!-- Mobile View Header -->
                <div class="sm:hidden p-4 pb-2 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Customer Reviews</h2>
                        <p class="text-xs text-gray-500 font-normal mt-0.5"><?= $crTotal ?> verified review<?= $crTotal !== 1 ? 's' : '' ?></p>
                    </div>
                    <?php if ($crTotal > 1): ?>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="slideCustomReviewPrev()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition shadow-sm active:scale-95" title="Previous">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" onclick="slideCustomReviewNext()" class="w-8 h-8 flex items-center justify-center rounded-full bg-[#F25996] hover:bg-[#d8407d] text-white transition shadow-sm active:scale-95" title="Next">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Single Row Reviews Section (Left: Rating Summary, Right: Review Cards) -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 p-3 sm:p-8">
                    
                    <?php if (!empty($crList)): ?>
                    <!-- 1. Customer Reviews Rating Summary Card (Left Side) -->
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
                                        <span class="text-3xl font-black text-white leading-none"><?= number_format($crAvg, 1) ?></span>
                                        <span class="text-[10px] font-bold text-white/90 tracking-tight mt-1">Out of 5</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 font-medium mt-2 mb-5">Based on <?= $crTotal ?> review<?= $crTotal !== 1 ? 's' : '' ?></p>
                            </div>

                            <!-- 5-Star Breakdown Bars -->
                            <div class="space-y-2 w-full text-left px-2">
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <?php 
                                        $count = $crCounts[$star] ?? 0;
                                        $pct = $crTotal > 0 ? round(($count / $crTotal) * 100) : 0;
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

                    <!-- RIGHT COLUMN: Reviews Slider / Grid -->
                    <div class="flex-1 min-w-0">
                        <!-- Desktop Header Controls -->
                        <div class="hidden sm:flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900">Buyer Reviews</h3>
                                <?php if ($crTotal > 0): ?>
                                    <span class="bg-gray-100 text-gray-600 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                        <span id="custom-review-slide-num">1</span> / <?= $crTotal ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($crTotal > 1): ?>
                            <div class="flex items-center gap-2">
                                <button type="button" id="customReviewPrevBtn" onclick="slideCustomReviewPrev()" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition shadow-sm active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none" title="Previous Review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" id="customReviewNextBtn" onclick="slideCustomReviewNext()" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-[#F25996] hover:bg-[#d8407d] text-white transition shadow-sm active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none" title="Next Review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($crList)): ?>
                            <div class="text-center py-8 sm:py-10 px-4 bg-white/70 rounded-2xl border border-pink-100">
                                <svg class="w-10 h-10 text-pink-300 mx-auto mb-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <p class="text-gray-700 font-bold text-sm">No reviews yet for this custom program</p>
                                <p class="text-gray-500 font-medium text-xs mt-1 max-w-md mx-auto">Be the first to order and customize this fabric! Once your order status is marked as delivered, you can rate and review your finished garments.</p>
                            </div>
                        <?php else: ?>
                            <!-- Slider Track -->
                            <div id="custom-review-slider-track" class="flex overflow-x-auto gap-3 sm:gap-4 pb-3 pt-1 snap-x snap-mandatory scroll-smooth touch-pan-x hide-scrollbar select-none cursor-grab">
                                
                                <!-- Mobile-only Rating Summary Slide -->
                                <div class="custom-review-slide-card sm:hidden flex-none w-[85vw] max-w-[320px]">
                                    <div class="bg-white rounded-2xl p-4 border border-pink-100 h-full flex flex-col justify-between text-center shadow-xs">
                                        <div>
                                            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Customer Reviews</div>
                                            <div class="relative w-24 h-24 mx-auto flex items-center justify-center my-1 flex-shrink-0">
                                                <svg class="w-full h-full text-[#F25996] drop-shadow-xs" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.784 1.399 8.163L12 18.896l-7.333 3.861 1.399-8.163-5.934-5.784 8.2-1.192zm0 0"/>
                                                </svg>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pt-1.5">
                                                    <span class="text-2xl font-black text-white leading-none"><?= number_format($crAvg, 1) ?></span>
                                                    <span class="text-[9px] font-bold text-white/90 tracking-tight mt-0.5">Out of 5</span>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium mt-1 mb-3">Based on <?= $crTotal ?> review<?= $crTotal !== 1 ? 's' : '' ?></p>
                                        </div>

                                        <!-- 5-Star Breakdown Bars -->
                                        <div class="space-y-1.5 w-full text-left">
                                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                                <?php 
                                                    $count = $crCounts[$star] ?? 0;
                                                    $pct = $crTotal > 0 ? round(($count / $crTotal) * 100) : 0;
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

                                <!-- Buyer Review Cards -->
                                <?php foreach ($crList as $idx => $rev): ?>
                                    <?php
                                        $initials = strtoupper(substr($rev['reviewer_name'] ?? 'U', 0, 1));
                                        $avatarColors = ['bg-amber-100 text-amber-800', 'bg-violet-100 text-violet-800', 'bg-blue-100 text-blue-800', 'bg-emerald-100 text-emerald-800', 'bg-rose-100 text-rose-800'];
                                        $avatarClass  = $avatarColors[ord($initials) % count($avatarColors)];
                                        $photos = !empty($rev['photos']) ? (is_array($rev['photos']) ? $rev['photos'] : json_decode($rev['photos'], true)) : [];
                                        
                                        $lines = explode("\n", trim($rev['review_text'] ?? ''));
                                        $hasHeadline = count($lines) > 1 && strlen($lines[0]) < 50;
                                        $headline = $hasHeadline ? $lines[0] : '';
                                        $bodyContent = $hasHeadline ? implode("\n", array_slice($lines, 1)) : ($rev['review_text'] ?? '');
                                    ?>
                                    <div class="custom-review-slide-card flex-none w-[85vw] max-w-[320px] sm:w-[300px] lg:w-[calc((100%-32px)/3)]">
                                        <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5 h-full flex flex-col shadow-xs hover:shadow-md transition-shadow">
                                            <!-- Top Row: Avatar + Name + Verified Purchase Badge -->
                                            <div class="flex items-start gap-3 mb-2.5">
                                                <div class="w-10 h-10 rounded-full <?= $avatarClass ?> flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                                                    <?= $initials ?>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-bold text-gray-900 text-sm leading-snug truncate"><?= htmlspecialchars($rev['reviewer_name'] ?? 'Customer') ?></span>
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                            <svg class="w-2.5 h-2.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            Verified Purchase
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <div class="flex text-amber-400 text-xs gap-0.5">
                                                            <?php for($i=1; $i<=5; $i++): ?>
                                                                <svg class="w-3.5 h-3.5 <?= $i <= (int)($rev['rating'] ?? 5) ? 'fill-current text-amber-400' : 'fill-current text-gray-200' ?>" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span class="text-xs text-gray-400"><?= !empty($rev['created_at']) ? date('d M Y', strtotime($rev['created_at'])) : '' ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Headline if present -->
                                            <?php if ($headline): ?>
                                                <h4 class="font-bold text-gray-900 text-sm mt-1 leading-snug break-words"><?= htmlspecialchars($headline) ?></h4>
                                            <?php endif; ?>

                                            <!-- Body text -->
                                            <?php $textToDisplay = $bodyContent ?: ($rev['review_text'] ?? ''); ?>
                                            <?php if (!empty($textToDisplay)): ?>
                                                <p class="text-gray-600 text-xs sm:text-sm leading-relaxed flex-1 mt-1.5 mb-3 break-words"><?= nl2br(htmlspecialchars($textToDisplay)) ?></p>
                                            <?php else: ?>
                                                <div class="flex-1"></div>
                                            <?php endif; ?>

                                            <!-- Attached Photos Row -->
                                            <?php if (!empty($photos)): ?>
                                                <div class="flex gap-2 mt-auto flex-wrap pt-1 mb-2">
                                                    <?php foreach (array_slice($photos, 0, 4) as $photo): ?>
                                                        <a href="<?= get_image_url($photo) ?>" target="_blank" class="block group">
                                                            <img src="<?= get_image_url($photo) ?>" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-gray-200 group-hover:border-emerald-400 group-hover:scale-105 transition-all duration-200 shadow-2xs" alt="Review photo">
                                                        </a>
                                                    <?php endforeach; ?>
                                                    <?php if (count($photos) > 4): ?>
                                                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-xl flex items-center justify-center text-xs text-gray-500 font-bold border border-gray-200">+<?= count($photos) - 4 ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Card Footer: Helpful Feedback -->
                                            <?php 
                                                $votedReviewsSession = \Core\Session::get('voted_reviews') ?: [];
                                                $votedChoice = $votedReviewsSession[$rev['id']] ?? null;
                                            ?>
                                            <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between mt-auto text-xs">
                                                <span class="text-gray-400 font-medium text-[10px] sm:text-[11px]">Helpful?</span>
                                                <div class="flex items-center gap-1.5" data-review-id="<?= $rev['id'] ?>" data-voted="<?= htmlspecialchars($votedChoice ?? '') ?>">
                                                    <button type="button" data-vote-type="yes" onclick="voteReviewHelpful(<?= $rev['id'] ?>, 'yes', this)" 
                                                        class="btn-vote-yes inline-flex items-center gap-1 px-2.5 py-1 <?= ($votedChoice === 'yes') ? 'bg-emerald-100 text-emerald-950 border-2 border-emerald-500 font-black shadow-xs' : 'bg-gray-50 hover:bg-emerald-50 text-gray-700 border border-gray-200 font-bold' ?> rounded-full text-[10px] sm:text-[11px] transition-all active:scale-95 cursor-pointer">
                                                        <span>👍</span> <span class="vote-count-yes"><?= (int)($rev['helpful_yes'] ?? 0) ?></span>
                                                    </button>
                                                    <button type="button" data-vote-type="no" onclick="voteReviewHelpful(<?= $rev['id'] ?>, 'no', this)" 
                                                        class="btn-vote-no inline-flex items-center gap-1 px-2.5 py-1 <?= ($votedChoice === 'no') ? 'bg-rose-100 text-rose-950 border-2 border-rose-500 font-black shadow-xs' : 'bg-gray-50 hover:bg-rose-50 text-gray-700 border border-gray-200 font-bold' ?> rounded-full text-[10px] sm:text-[11px] transition-all active:scale-95 cursor-pointer">
                                                        <span>👎</span> <span class="vote-count-no"><?= (int)($rev['helpful_no'] ?? 0) ?></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let activeCustomReviewIndex = 0;
        const totalCustomReviewSlides = <?= $crTotal ?>;

        function getCustomReviewTrack() {
            return document.getElementById('custom-review-slider-track');
        }

        function getCustomVisibleCards() {
            const track = getCustomReviewTrack();
            if (!track) return [];
            return Array.from(track.querySelectorAll('.custom-review-slide-card')).filter(c => c.offsetWidth > 0);
        }

        function slideCustomReviewPrev() {
            const track = getCustomReviewTrack();
            if (!track) return;
            const cards = getCustomVisibleCards();
            if (activeCustomReviewIndex > 0) {
                goToCustomReviewSlide(activeCustomReviewIndex - 1);
            } else {
                const cardWidth = cards[0]?.offsetWidth || 300;
                track.scrollBy({ left: -(cardWidth + 16), behavior: 'smooth' });
            }
        }

        function slideCustomReviewNext() {
            const track = getCustomReviewTrack();
            if (!track) return;
            const cards = getCustomVisibleCards();
            if (activeCustomReviewIndex < cards.length - 1) {
                goToCustomReviewSlide(activeCustomReviewIndex + 1);
            } else {
                const cardWidth = cards[0]?.offsetWidth || 300;
                track.scrollBy({ left: (cardWidth + 16), behavior: 'smooth' });
            }
        }

        function goToCustomReviewSlide(index) {
            const track = getCustomReviewTrack();
            if (!track) return;
            const cards = getCustomVisibleCards();
            if (cards[index]) {
                cards[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
                activeCustomReviewIndex = index;
                const counter = document.getElementById('custom-review-slide-num');
                if (counter) counter.innerText = (index + 1);
                const prevBtn = document.getElementById('customReviewPrevBtn');
                const nextBtn = document.getElementById('customReviewNextBtn');
                if (prevBtn) prevBtn.disabled = (index <= 0);
                if (nextBtn) nextBtn.disabled = (index >= cards.length - 1);
            }
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
</div>

<!-- Fabric Gallery Lightbox Modal -->
<div id="fabricLightboxModal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm hidden flex items-center justify-center p-3 sm:p-6" onclick="if(event.target === this) closeFabricModal()">
    <div class="relative max-w-4xl w-full flex flex-col items-center">
        <!-- Close Button -->
        <button type="button" onclick="closeFabricModal()" class="absolute -top-12 right-0 text-white hover:text-pink-300 p-2 text-sm font-bold flex items-center gap-1.5 transition-colors">
            <span>Close (Esc)</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- Main Lightbox Image Frame -->
        <div class="relative max-h-[75vh] w-auto max-w-full overflow-hidden rounded-2xl bg-white shadow-2xl flex items-center justify-center">
            <img id="fabricModalImg" src="" alt="Fabric Full Preview" class="max-h-[75vh] max-w-full object-contain">
            
            <!-- Prev / Next Controls -->
            <button type="button" onclick="navFabricModal(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/55 hover:bg-black/75 text-white flex items-center justify-center shadow-lg transition-transform hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" onclick="navFabricModal(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/55 hover:bg-black/75 text-white flex items-center justify-center shadow-lg transition-transform hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Counter Badge -->
            <div id="fabricModalCounter" class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/70 text-white text-xs font-bold px-3 py-1 rounded-full backdrop-blur-xs">
                1 / 1
            </div>
        </div>

        <!-- Lightbox Thumbnails -->
        <div id="fabricModalThumbs" class="flex items-center gap-2 mt-4 overflow-x-auto max-w-full pb-1 scrollbar-none justify-center"></div>
    </div>
</div>

<!-- Razorpay Official Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
const FABRIC_GALLERY_IMAGES = <?= json_encode(array_values(array_map(fn($p) => get_image_url($p), $galleryList))) ?>;
let fabricActiveImgIndex = 0;

function setFabricActiveImage(idx) {
    if (!FABRIC_GALLERY_IMAGES || !FABRIC_GALLERY_IMAGES[idx]) return;
    fabricActiveImgIndex = idx;
    const mainImg = document.getElementById('fabricMainImage');
    if (mainImg) {
        mainImg.style.opacity = '0.6';
        mainImg.src = FABRIC_GALLERY_IMAGES[idx];
        setTimeout(() => { mainImg.style.opacity = '1'; }, 100);
    }
    document.querySelectorAll('.fabric-thumb-btn').forEach((btn, i) => {
        if (i === idx) {
            btn.className = 'fabric-thumb-btn w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 aspect-square rounded-2xl bg-white overflow-hidden border-2 transition-all cursor-pointer shrink-0 border-[#F25996] ring-2 ring-[#F25996]/30 shadow-xs';
        } else {
            btn.className = 'fabric-thumb-btn w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 aspect-square rounded-2xl bg-white overflow-hidden border-2 transition-all cursor-pointer shrink-0 border-gray-200 hover:border-[#F25996]/50 opacity-80 hover:opacity-100';
        }
    });
}

function shareFabricProduct() {
    const url = window.location.href;
    const title = document.title;
    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Link copied to clipboard!',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                alert('Product link copied to clipboard!');
            }
        });
    }
}

function handleAddToCartClick() {
    proceedToCheckout();
}

function onTopFabricMultiplierChange(inp) {
    let mult = parseInt(inp.value) || 1;
    if (mult < 1) mult = 1;
    const bottomInp = document.getElementById('fabric_multiplier');
    if (bottomInp) bottomInp.value = mult;
    onFabricMultiplierChange(inp);
}

function openFabricModal(idx) {
    if (idx !== undefined) fabricActiveImgIndex = idx;
    const modal = document.getElementById('fabricLightboxModal');
    const modalImg = document.getElementById('fabricModalImg');
    const counter = document.getElementById('fabricModalCounter');
    if (!modal || !modalImg) return;

    modalImg.src = FABRIC_GALLERY_IMAGES[fabricActiveImgIndex] || '';
    if (counter) counter.innerText = `${fabricActiveImgIndex + 1} / ${FABRIC_GALLERY_IMAGES.length}`;
    modal.classList.remove('hidden');
    renderModalThumbs();
}

function closeFabricModal() {
    const modal = document.getElementById('fabricLightboxModal');
    if (modal) modal.classList.add('hidden');
}

function navFabricModal(direction) {
    if (!FABRIC_GALLERY_IMAGES || FABRIC_GALLERY_IMAGES.length <= 1) return;
    fabricActiveImgIndex = (fabricActiveImgIndex + direction + FABRIC_GALLERY_IMAGES.length) % FABRIC_GALLERY_IMAGES.length;
    const modalImg = document.getElementById('fabricModalImg');
    const counter = document.getElementById('fabricModalCounter');
    if (modalImg) modalImg.src = FABRIC_GALLERY_IMAGES[fabricActiveImgIndex];
    if (counter) counter.innerText = `${fabricActiveImgIndex + 1} / ${FABRIC_GALLERY_IMAGES.length}`;
    setFabricActiveImage(fabricActiveImgIndex);
    renderModalThumbs();
}

function renderModalThumbs() {
    const wrap = document.getElementById('fabricModalThumbs');
    if (!wrap) return;
    if (FABRIC_GALLERY_IMAGES.length <= 1) {
        wrap.innerHTML = '';
        return;
    }
    wrap.innerHTML = FABRIC_GALLERY_IMAGES.map((url, i) => `
        <button type="button" onclick="setFabricActiveImage(${i}); document.getElementById('fabricModalImg').src='${url}'; document.getElementById('fabricModalCounter').innerText='${i + 1} / ${FABRIC_GALLERY_IMAGES.length}'; renderModalThumbs();" class="w-12 h-12 rounded-xl overflow-hidden border-2 transition-all flex-shrink-0 ${i === fabricActiveImgIndex ? 'border-[#F25996] ring-2 ring-[#F25996]/50' : 'border-white/40 opacity-70 hover:opacity-100'}">
            <img src="${url}" class="w-full h-full object-cover">
        </button>
    `).join('');
}

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('fabricLightboxModal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeFabricModal();
    if (e.key === 'ArrowLeft') navFabricModal(-1);
    if (e.key === 'ArrowRight') navFabricModal(1);
});

const FABRIC_ID = <?= (int)$customization['fabric_id'] ?>;
const CUSTOMIZATION_ID = <?= (int)$customization['id'] ?>;
const MIN_PIECES = <?= (int)$reqPcsForOneQty ?>;
const MAX_PIECES = <?= (int)$maxPcsForOneQty ?>;
const FABRIC_STOCK = <?= (float)$customization['fabric_stock'] ?>;
const UNIT = <?= json_encode($customization['unit'] ?? 'Qty') ?>;
const BASE_URL = <?= json_encode(BASE_URL) ?>;
const IS_LOGGED_IN = <?= \Core\Session::get('user_id') ? 'true' : 'false' ?>;
const FABRIC_PRICE_PER_UNIT = <?= (float)($customization['fabric_price'] ?? 0) ?>;
const SIZES_CONFIG = <?= json_encode($customization['sizes'] ?? []) ?>;
const WITHOUT_COVER_DISCOUNT_PCT = <?= (float)($customization['without_cover_discount_percentage'] ?? 0) ?>;
const DELIVERY_DAYS = <?= (int)($customization['delivery_days'] ?? 7) ?>;
const DELIVERY_BASE_PIECES = <?= (int)($customization['delivery_base_pieces'] ?? 100) ?>;

const SAVED_SIZES = <?= json_encode($savedSizes) ?>;
const SAVED_SETS = <?= json_encode($savedSets) ?>;
const SAVED_QTY = <?= (int)$savedQty ?>;
const SAVED_PKG = <?= json_encode($savedPkg) ?>;
const CUSTOMIZATION_LABELS = <?= json_encode($customization['labels'] ?? []) ?>;

let fabricSetsData = [];
let activeConfigSet = 1; // 1, 2, ... or 'all'
let selectedPackagingOption = SAVED_PKG || 'with_cover';
let selectedSizeNames = []; // Preserves selection sequence
let currentCalc = null;

function selectPackagingOption(option) {
    selectedPackagingOption = option;
    const btnWith = document.getElementById('optWithCover');
    const btnWithout = document.getElementById('optWithoutCover');
    
    if (btnWith && btnWithout) {
        const checkWith = btnWith.querySelector('.pkg-check');
        const checkWithout = btnWithout.querySelector('.pkg-check');
        
        if (option === 'with_cover') {
            btnWith.className = 'packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-[#F25996] bg-white ring-2 ring-[#F25996]/20 shadow-xs';
            btnWithout.className = 'packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-gray-200 bg-white hover:border-[#F25996]/50';
            if (checkWith) checkWith.className = 'pkg-check w-4 h-4 rounded-full bg-[#F25996] text-white flex items-center justify-center text-[10px] font-bold';
            if (checkWithout) checkWithout.className = 'pkg-check w-4 h-4 rounded-full border border-gray-300 text-transparent flex items-center justify-center text-[10px] font-bold';
        } else {
            btnWithout.className = 'packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-emerald-500 bg-emerald-50/20 ring-2 ring-emerald-500/20 shadow-xs';
            btnWith.className = 'packaging-opt-btn p-3 rounded-xl border-2 text-left transition-all cursor-pointer border-gray-200 bg-white hover:border-[#F25996]/50';
            if (checkWithout) checkWithout.className = 'pkg-check w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold';
            if (checkWith) checkWith.className = 'pkg-check w-4 h-4 rounded-full border border-gray-300 text-transparent flex items-center justify-center text-[10px] font-bold';
        }
    }
    triggerRecalc();
}

function getFabricQuantity() {
    const inp = document.getElementById('fabric_multiplier');
    return inp ? Math.max(1, parseInt(inp.value) || 1) : 1;
}

function adjustFabricMultiplier(delta) {
    const inp = document.getElementById('fabric_multiplier');
    if (!inp) return;
    const maxStock = Math.max(1, Math.floor(FABRIC_STOCK));
    let curVal = parseInt(inp.value) || 1;
    let val = curVal + delta;
    if (val < 1) val = 1;
    if (val > maxStock) {
        val = maxStock;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Stock Limit Reached',
                text: `Quantity cannot exceed available stock (${maxStock} Qty).`,
                confirmButtonColor: '#F25996'
            });
        }
    }


    inp.value = val;
    onFabricMultiplierChange(inp);
}

function getSizeLimits(sizeName, mult) {
    if (!mult) mult = 1;
    const sObj = SIZES_CONFIG.find(s => s.size_name === sizeName);
    const rawMin = sObj && parseInt(sObj.base_quantity) > 0 ? parseInt(sObj.base_quantity) : 2;
    const baseMin = Math.max(1, Math.min(rawMin, MIN_PIECES));
    const baseMax = sObj && parseInt(sObj.max_quantity) > 0 ? parseInt(sObj.max_quantity) : 35;
    return {
        min: baseMin * mult,
        max: baseMax * mult,
        baseMin: baseMin,
        baseMax: baseMax
    };
}

// Synchronize fabricSetsData array to match targetCount sets
function syncFabricSets(targetCount) {
    const mult = Math.max(1, targetCount);

    // Expand if more sets needed
    while (fabricSetsData.length < mult) {
        const setIdx = fabricSetsData.length + 1;
        let defaultLabel = null;
        if (CUSTOMIZATION_LABELS && CUSTOMIZATION_LABELS.length > 0) {
            defaultLabel = CUSTOMIZATION_LABELS[(setIdx - 1) % CUSTOMIZATION_LABELS.length];
        }

        // Initialize with sizes respecting MAX_PIECES
        const defaultSizes = [];
        const setSizes = {};
        let currentSum = 0;
        SIZES_CONFIG.forEach(sz => {
            const limits = getSizeLimits(sz.size_name, 1);
            if (currentSum + limits.baseMin <= MAX_PIECES) {
                defaultSizes.push(sz.size_name);
                setSizes[sz.size_name] = limits.baseMin;
                currentSum += limits.baseMin;
            } else if (defaultSizes.length === 0) {
                const q = Math.max(1, Math.min(limits.baseMin, MAX_PIECES));
                defaultSizes.push(sz.size_name);
                setSizes[sz.size_name] = q;
                currentSum += q;
            }
        });

        fabricSetsData.push({
            set_id: setIdx,
            label_id: defaultLabel ? defaultLabel.id : '',
            label_name: defaultLabel ? (defaultLabel.display_label || defaultLabel.name) : 'Default Custom Garment',
            selected_sizes: defaultSizes.length > 0 ? [...defaultSizes] : SIZES_CONFIG.map(s => s.size_name),
            sizes: setSizes
        });
    }

    // Shrink if fewer sets needed
    if (fabricSetsData.length > mult) {
        fabricSetsData = fabricSetsData.slice(0, mult);
    }

    if (mult <= 1 || (activeConfigSet !== 'all' && activeConfigSet > mult)) {
        activeConfigSet = 1;
    }
}

// Render Roll / Set Selector Tabs in Section 1
function renderRollTabs() {
    const container = document.getElementById('rollTabsContainer');
    if (!container) return;

    const mult = getFabricQuantity();
    if (mult <= 1) {
        container.innerHTML = '';
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');

    let tabsHtml = `
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
            <span>Roll:</span>
        </span>
    `;

    fabricSetsData.forEach((st, sIdx) => {
        const setNum = sIdx + 1;
        const isActive = activeConfigSet === setNum;
        const sizeCount = (st.selected_sizes || []).length;
        const btnClass = isActive 
            ? 'px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs bg-[#F25996] text-white border-2 border-[#F25996] cursor-pointer'
            : 'px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5 border border-pink-200 text-gray-700 bg-white hover:bg-pink-50 cursor-pointer';
        const badgeClass = isActive 
            ? 'text-[10px] font-extrabold px-1.5 py-0.2 rounded-full bg-white/25 text-white'
            : 'text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-pink-50 text-[#F25996] border border-pink-100';

        tabsHtml += `
            <button type="button" onclick="setActiveConfigSet(${setNum})" class="${btnClass}">
                <span>🧵 Roll #${setNum}</span>
                <span class="${badgeClass}">${sizeCount} sizes</span>
            </button>
        `;
    });

    const isAllActive = activeConfigSet === 'all';
    const allBtnClass = isAllActive
        ? 'px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1 shadow-xs bg-gray-900 text-white border-2 border-gray-900 cursor-pointer ml-auto shrink-0'
        : 'px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1 border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 cursor-pointer ml-auto shrink-0';

    tabsHtml += `
        <button type="button" onclick="setActiveConfigSet('all')" class="${allBtnClass}">
            <span>⚡ All Rolls</span>
        </button>
    `;

    container.innerHTML = tabsHtml;
}

function setActiveConfigSet(setKey) {
    activeConfigSet = setKey;
    applyVariantSelectionFlow();
}

// Render dynamic Set Containers under Section 2
function renderFabricSetContainers() {
    const container = document.getElementById('fabricSetsContainer');
    if (!container) return;

    const mult = getFabricQuantity();
    const placeholder = document.getElementById('emptyVariantsPlaceholder');

    // Check if ANY set has sizes selected
    const totalSetSizesCount = fabricSetsData.reduce((acc, st) => acc + (st.selected_sizes || []).length, 0);

    if (totalSetSizesCount === 0) {
        if (placeholder) placeholder.classList.remove('hidden');
        container.innerHTML = '';
        return;
    }

    if (placeholder) placeholder.classList.add('hidden');

    let html = '';

    fabricSetsData.forEach((st, sIdx) => {
        const setIdx = sIdx + 1;
        const setSizes = st.selected_sizes || [];
        let setTotalPcs = 0;
        if (st.sizes) {
            setSizes.forEach(sz => {
                setTotalPcs += (parseInt(st.sizes[sz]) || 0);
            });
        }

        // Generate label options (both native select and custom dropdown matching register.php state dropdown style)
        let labelOptionsHtml = '';
        let customOptionsListHtml = '';
        let selectedLabelText = 'Select Style / Label';

        if (CUSTOMIZATION_LABELS && CUSTOMIZATION_LABELS.length > 0) {
            let matched = false;
            CUSTOMIZATION_LABELS.forEach((lbl, lIdx) => {
                const isSel = (!matched && (lbl.id == st.label_id || lbl.display_label === st.label_name || lbl.name === st.label_name || (!st.label_id && lIdx === (setIdx - 1) % CUSTOMIZATION_LABELS.length)));
                if (isSel) {
                    matched = true;
                    st.label_id = lbl.id;
                    st.label_name = lbl.display_label || lbl.name;
                    selectedLabelText = lbl.display_label || lbl.name;
                }
                const txt = lbl.display_label || lbl.name;
                labelOptionsHtml += `<option value="${lbl.id}" ${isSel ? 'selected' : ''}>${txt}</option>`;
                customOptionsListHtml += `
                    <div class="reg-cs-option ${isSel ? 'selected' : ''}" 
                         data-value="${lbl.id}" 
                         onclick="onCustomSetLabelSelect(${setIdx}, this, event)">
                        ${txt}
                    </div>
                `;
            });
        } else {
            labelOptionsHtml = `<option value="">Standard Custom Style</option>`;
            selectedLabelText = 'Standard Custom Style';
            customOptionsListHtml = `
                <div class="reg-cs-option selected" 
                     data-value="" 
                     onclick="onCustomSetLabelSelect(${setIdx}, this, event)">
                    Standard Custom Style
                </div>
            `;
        }

        const isMet = setTotalPcs >= MIN_PIECES;
        const badgeClass = isMet 
            ? 'text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shadow-2xs'
            : 'text-xs font-bold px-2.5 py-1 rounded-xl bg-pink-50 text-[#F25996] border border-pink-200 whitespace-nowrap shadow-2xs';
        const badgeText = isMet 
            ? `✅ ${setTotalPcs} Pcs` 
            : `${setTotalPcs} / ${MIN_PIECES} Pcs`;

        // Generate size pills for this set container
        let sizePillsHtml = '';
        SIZES_CONFIG.forEach(sz => {
            const isSizeInSet = setSizes.includes(sz.size_name);
            if (isSizeInSet) {
                sizePillsHtml += `
                    <button type="button" onclick="toggleSetSize(${setIdx}, '${sz.size_name}')" class="px-2.5 py-1 rounded-lg text-[11px] font-black bg-[#F25996] text-white border border-[#F25996] hover:bg-[#d8407d] transition-all flex items-center gap-1 shadow-2xs cursor-pointer" title="Remove ${sz.size_name} from Roll #${setIdx}">
                        <span>${sz.size_name}</span>
                        <span class="text-[9px]">✓</span>
                    </button>
                `;
            } else {
                sizePillsHtml += `
                    <button type="button" onclick="toggleSetSize(${setIdx}, '${sz.size_name}')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white text-gray-600 border border-gray-200 hover:border-pink-300 hover:text-[#F25996] hover:bg-pink-50/50 transition-all flex items-center gap-1 cursor-pointer" title="Add ${sz.size_name} to Roll #${setIdx}">
                        <span>${sz.size_name}</span>
                        <span class="text-[9px] text-gray-400 font-bold">+</span>
                    </button>
                `;
            }
        });

        html += `
        <div class="fabric-set-card bg-white rounded-2xl border-2 border-pink-100 shadow-xs p-3.5 sm:p-4 mb-3.5 transition-all hover:border-[#F25996]/40 relative" id="set_card_${setIdx}">
            <!-- Header of Set Container -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 pb-3 border-b border-pink-100/80 mb-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#F25996] to-[#db2777] text-white font-black text-xs flex items-center justify-center shadow-xs shrink-0">#${setIdx}</span>
                    <div class="min-w-0">
                        <h4 class="font-extrabold text-sm text-gray-900 flex items-center gap-2 flex-wrap">
                            <span>Roll / Set #${setIdx}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-pink-50 text-[#F25996] border border-pink-100 whitespace-nowrap">${setSizes.length} variant(s)</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 truncate">Configure label & piece allocation for this roll</p>
                    </div>
                </div>

                <!-- Label Selector & Set Piece Badge -->
                <div class="flex items-center gap-2 flex-wrap justify-between md:justify-end min-w-0">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <label class="text-xs font-bold text-gray-700 flex items-center gap-1 shrink-0">
                            <span>🏷️ Style / Label:</span>
                        </label>
                        <!-- Preserved native select for function/form compatibility -->
                        <select id="set_label_select_${setIdx}" class="set-label-select hidden" onchange="onSetLabelChange(${setIdx}, this)">
                            ${labelOptionsHtml}
                        </select>
                        <!-- Custom Select Dropdown Styled Exactly Like Register Page State Dropdown -->
                        <div class="reg-cs-wrapper" id="set_label_wrapper_${setIdx}">
                            <button type="button" 
                                    id="set_label_trigger_${setIdx}" 
                                    class="reg-cs-trigger" 
                                    onclick="toggleCustomSetLabel(${setIdx}, event)">
                                <span class="reg-cs-label" id="set_label_display_${setIdx}">${selectedLabelText}</span>
                                <svg class="reg-cs-arrow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 7l5 5 5-5"></path>
                                </svg>
                            </button>
                            <div class="reg-cs-list" id="set_label_list_${setIdx}">
                                ${customOptionsListHtml}
                            </div>
                        </div>
                    </div>
                    <span class="${badgeClass} shrink-0" id="set_pieces_badge_${setIdx}">
                        ${badgeText}
                    </span>
                </div>
            </div>

            <!-- Per-Set Size Variant Selector Bar -->
            <div class="flex items-center justify-between gap-2 flex-wrap bg-pink-50/40 p-2.5 rounded-xl border border-pink-100/80 mb-2.5">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-gray-700 mr-1 flex items-center gap-1">
                        <span>Sizes in Roll #${setIdx}:</span>
                    </span>
                    ${sizePillsHtml}
                </div>
            </div>

            <!-- Variant Steppers for this Set -->
            <div class="divide-y divide-gray-100" id="set_variants_list_${setIdx}">
        `;

        if (setSizes.length === 0) {
            html += `
                <div class="p-4 text-center text-xs text-gray-400 font-medium">
                    No size variants selected for Roll #${setIdx}. Click any size pill above to add sizes to this roll.
                </div>
            `;
        }

        // Generate rows ONLY for sizes selected in this set!
        setSizes.forEach(sizeName => {
            const sz = SIZES_CONFIG.find(s => s.size_name === sizeName) || { size_name: sizeName, fabric_consumption: 1.0 };
            const limits = getSizeLimits(sizeName, 1);
            const qty = (st.sizes && st.sizes[sizeName] !== undefined && st.sizes[sizeName] > 0) ? parseInt(st.sizes[sizeName]) : limits.baseMin;
            if (st.sizes) st.sizes[sizeName] = qty;
            const fabricPricePerMeter = FABRIC_PRICE_PER_UNIT;
            const consumptionRate = parseFloat(sz.fabric_consumption) || 1.0;
            const pricePerPiece = Math.round(fabricPricePerMeter * consumptionRate * 100) / 100;
            const subtotal = Math.round(pricePerPiece * qty * 100) / 100;

            html += `
                <div class="size-row p-3 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-pink-50/20 transition-colors" id="row_set_${setIdx}_${sizeName}">
                    <!-- Size Info -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-pink-100 text-[#F25996] font-black text-xs flex items-center justify-center border border-pink-200 shrink-0">
                            ${sizeName}
                        </div>
                        <div>
                            <div class="font-bold text-xs sm:text-sm text-gray-900 flex items-center gap-2 flex-wrap">
                                <span>Size ${sizeName}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-pink-50 text-[#db2777] border border-pink-200">
                                    Required: ${limits.baseMin} pcs (Max: ${limits.baseMax * Math.max(1, getFabricQuantity())} pcs)
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                Rate: <strong class="text-green-700">₹${pricePerPiece.toFixed(2)}/pc</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Stepper & Subtotal -->
                    <div class="flex items-center gap-3 self-end sm:self-auto">
                        <div class="inline-flex items-center border-2 border-[#F25996] rounded-full bg-white px-1.5 py-0.5 shadow-xs">
                            <button type="button" onclick="adjustSetQty(${setIdx}, '${sizeName}', -1)" class="w-6 h-6 rounded-full flex items-center justify-center font-black text-base text-[#F25996] hover:bg-pink-50 transition-colors select-none cursor-pointer">
                                -
                            </button>
                            <div class="h-3.5 w-px bg-pink-200 mx-1"></div>
                            <input type="number" 
                                   id="qty_set_${setIdx}_${sizeName}" 
                                   data-set="${setIdx}" 
                                   data-size="${sizeName}" 
                                   value="${qty}" 
                                   min="0" 
                                   max="${limits.baseMax * Math.max(1, Math.floor(FABRIC_STOCK))}" 
                                   oninput="onSetQtyInput(${setIdx}, '${sizeName}', this)" 
                                   class="set-qty-input w-10 h-6 text-center font-black text-xs sm:text-sm text-[#F25996] bg-transparent focus:outline-none">
                            <div class="h-3.5 w-px bg-pink-200 mx-1"></div>
                            <button type="button" onclick="adjustSetQty(${setIdx}, '${sizeName}', 1)" class="w-6 h-6 rounded-full flex items-center justify-center font-black text-base text-[#F25996] hover:bg-pink-50 transition-colors select-none cursor-pointer">
                                +
                            </button>
                        </div>

                        <!-- Subtotal & Remove/Trash Action -->
                        <div class="flex items-center gap-2">
                            <div class="text-right min-w-[65px]">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Subtotal</span>
                                <span class="text-xs font-bold text-gray-800" id="sub_set_${setIdx}_${sizeName}">
                                    ${qty} Pcs
                                </span>
                                <span class="text-[10px] font-bold text-green-700 block" id="price_sub_set_${setIdx}_${sizeName}">
                                    ₹${subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                </span>
                            </div>
                            <button type="button" onclick="removeSetSize(${setIdx}, '${sizeName}')" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer" title="Remove ${sizeName} from Roll #${setIdx}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
}

function onSetLabelChange(setIdx, sel) {
    const labelId = sel.value;
    const labelTxt = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
    if (fabricSetsData[setIdx - 1]) {
        fabricSetsData[setIdx - 1].label_id = labelId;
        fabricSetsData[setIdx - 1].label_name = labelTxt;
    }
    triggerRecalc();
}

function toggleCustomSetLabel(setIdx, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const trigger = document.getElementById('set_label_trigger_' + setIdx);
    const list = document.getElementById('set_label_list_' + setIdx);
    const card = document.getElementById('set_card_' + setIdx);
    if (!trigger || !list) return;

    const isOpen = list.classList.contains('open');
    closeAllSetLabelDropdowns();

    if (!isOpen) {
        trigger.classList.add('open');
        list.classList.add('open');
        if (card) card.classList.add('has-open-dropdown');
    }
}

function onCustomSetLabelSelect(setIdx, optEl, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const val = optEl.getAttribute('data-value') || '';
    const sel = document.getElementById('set_label_select_' + setIdx);
    if (sel) {
        sel.value = val;
        onSetLabelChange(setIdx, sel);
    }
    const displayEl = document.getElementById('set_label_display_' + setIdx);
    if (displayEl && optEl) {
        displayEl.textContent = optEl.textContent.trim();
    }
    const list = document.getElementById('set_label_list_' + setIdx);
    if (list) {
        list.querySelectorAll('.reg-cs-option').forEach(o => o.classList.remove('selected'));
        optEl.classList.add('selected');
    }
    closeAllSetLabelDropdowns();
}

function closeAllSetLabelDropdowns() {
    document.querySelectorAll('.reg-cs-trigger.open').forEach(t => t.classList.remove('open'));
    document.querySelectorAll('.reg-cs-list.open').forEach(l => l.classList.remove('open'));
    document.querySelectorAll('.fabric-set-card.has-open-dropdown').forEach(c => c.classList.remove('has-open-dropdown'));
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.reg-cs-wrapper')) {
        closeAllSetLabelDropdowns();
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllSetLabelDropdowns();
    }
});

function updateFabricMultiplierDisplay(mult) {
    const topInp = document.getElementById('fabric_multiplier_top');
    if (topInp) topInp.value = mult;
    const bottomInp = document.getElementById('fabric_multiplier');
    if (bottomInp) bottomInp.value = mult;

    const badge = document.getElementById('fabricQtyBadge');
    if (badge) {
        badge.textContent = mult === 1 ? '1 Set (1x)' : `${mult} Sets (${mult}x)`;
    }

    const reqMinPcs = MIN_PIECES * mult;
    const reqMaxPcs = MAX_PIECES * mult;
    const reqBadge = document.getElementById('fabricRequiredPcs');
    if (reqBadge) reqBadge.textContent = `${reqMinPcs} Pcs`;
    const maxBadge = document.getElementById('fabricMaxPcs');
    if (maxBadge) maxBadge.textContent = `${reqMaxPcs} Pcs`;
}

function adjustSetQty(setIndex, sizeName, delta) {
    if (!fabricSetsData[setIndex - 1]) return;
    const setData = fabricSetsData[setIndex - 1];
    if (!setData.sizes) setData.sizes = {};

    let curVal = parseInt(setData.sizes[sizeName]) || 0;
    let newVal = curVal + delta;
    if (newVal < 0) newVal = 0;

    const currentMult = getFabricQuantity();
    const currentMaxPieces = currentMult * MAX_PIECES;

    // Calculate current pieces inside THIS specific container/set
    const setSizes = setData.selected_sizes || [];
    let otherSizesInThisSet = 0;
    setSizes.forEach(s => {
        if (s !== sizeName) {
            otherSizesInThisSet += (parseInt(setData.sizes ? setData.sizes[s] : 0) || 0);
        }
    });

    const projectedSetTotal = otherSizesInThisSet + newVal;

    if (delta > 0 && projectedSetTotal > MAX_PIECES) {
        if (typeof Swal !== 'undefined') {
            const nextRollNotice = currentMult > 1 
                ? `Please add more pieces to Roll #${setIndex === 1 ? 2 : 1} or the other rolls.` 
                : `To order more pieces, please increase the Fabric Quantity.`;
            Swal.fire({
                icon: 'warning',
                title: `Roll #${setIndex} is Full (${MAX_PIECES} Pcs)`,
                text: `Roll #${setIndex} already has the maximum of ${MAX_PIECES} pieces. ${nextRollNotice}`,
                confirmButtonColor: '#F25996'
            });
        }
        return;
    }

    // Calculate projected total across all sets
    let projectedTotal = 0;
    fabricSetsData.forEach((st, sIdx) => {
        const sSizes = st.selected_sizes || [];
        sSizes.forEach(s => {
            let q = parseInt(st.sizes ? st.sizes[s] : 0) || 0;
            if (sIdx === setIndex - 1 && s === sizeName) {
                q = newVal;
            }
            projectedTotal += q;
        });
    });

    if (delta > 0 && projectedTotal > currentMaxPieces) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Total Pieces Limit Reached',
                text: `Maximum ${currentMaxPieces} pieces allowed for ${currentMult} Set(s). To add more pieces, please increase the Fabric Quantity.`,
                confirmButtonColor: '#F25996'
            });
        }
        return;
    }

    setData.sizes[sizeName] = newVal;

    const input = document.getElementById(`qty_set_${setIndex}_${sizeName}`);
    if (input) {
        input.value = newVal;
        input.max = MAX_PIECES;
    }

    const sub = document.getElementById(`sub_set_${setIndex}_${sizeName}`);
    if (sub) sub.textContent = `${newVal} Pcs`;

    const sObj = SIZES_CONFIG.find(s => s.size_name === sizeName);
    const rate = sObj ? parseFloat(sObj.fabric_consumption) || 1.0 : 1.0;
    const pricePerPc = FABRIC_PRICE_PER_UNIT * rate;
    const priceSub = document.getElementById(`price_sub_set_${setIndex}_${sizeName}`);
    if (priceSub) priceSub.textContent = `₹${(pricePerPc * newVal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

    updateSetBadge(setIndex);
    triggerRecalc();
}

function onSetQtyInput(setIndex, sizeName, inp) {
    if (!fabricSetsData[setIndex - 1]) return;
    const setData = fabricSetsData[setIndex - 1];
    if (!setData.sizes) setData.sizes = {};

    let val = parseInt(inp.value) || 0;
    if (val < 0) val = 0;

    const currentMult = getFabricQuantity();
    const currentMaxPieces = currentMult * MAX_PIECES;

    // Per-container capacity check
    const setSizes = setData.selected_sizes || [];
    let otherSizesInThisSet = 0;
    setSizes.forEach(s => {
        if (s !== sizeName) {
            otherSizesInThisSet += (parseInt(setData.sizes ? setData.sizes[s] : 0) || 0);
        }
    });

    if (otherSizesInThisSet + val > MAX_PIECES) {
        val = Math.max(0, MAX_PIECES - otherSizesInThisSet);
        inp.value = val;
        if (typeof Swal !== 'undefined') {
            const nextRollNotice = currentMult > 1 
                ? `Please add more pieces to the other roll(s).` 
                : `To order more pieces, please increase the Fabric Quantity.`;
            Swal.fire({
                icon: 'warning',
                title: `Roll #${setIndex} is Full (${MAX_PIECES} Pcs)`,
                text: `Roll #${setIndex} already has the maximum of ${MAX_PIECES} pieces. ${nextRollNotice}`,
                confirmButtonColor: '#F25996'
            });
        }
    }

    // Calculate projected total across all sets
    let projectedTotal = 0;
    fabricSetsData.forEach((st, sIdx) => {
        const sSizes = st.selected_sizes || [];
        sSizes.forEach(s => {
            let q = parseInt(st.sizes ? st.sizes[s] : 0) || 0;
            if (sIdx === setIndex - 1 && s === sizeName) {
                q = val;
            }
            projectedTotal += q;
        });
    });

    if (projectedTotal > currentMaxPieces) {
        val = Math.max(0, val - (projectedTotal - currentMaxPieces));
        inp.value = val;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Maximum Pieces Limit Reached',
                text: `Maximum ${currentMaxPieces} pieces allowed for Fabric Quantity ${currentMult}.`,
                confirmButtonColor: '#F25996'
            });
        }
    }

    setData.sizes[sizeName] = val;

    const sub = document.getElementById(`sub_set_${setIndex}_${sizeName}`);
    if (sub) sub.textContent = `${val} Pcs`;

    const sObj = SIZES_CONFIG.find(s => s.size_name === sizeName);
    const rate = sObj ? parseFloat(sObj.fabric_consumption) || 1.0 : 1.0;
    const pricePerPc = FABRIC_PRICE_PER_UNIT * rate;
    const priceSub = document.getElementById(`price_sub_set_${setIndex}_${sizeName}`);
    if (priceSub) priceSub.textContent = `₹${(pricePerPc * val).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

    updateSetBadge(setIndex);
    triggerRecalc();
}

function updateSetBadge(setIndex) {
    if (!fabricSetsData[setIndex - 1]) return;
    const setData = fabricSetsData[setIndex - 1];
    const setSizes = setData.selected_sizes || [];
    let setTotalPcs = 0;
    if (setData.sizes) {
        setSizes.forEach(sz => {
            setTotalPcs += (parseInt(setData.sizes[sz]) || 0);
        });
    }

    const badge = document.getElementById(`set_pieces_badge_${setIndex}`);
    if (badge) {
        const isMet = setTotalPcs >= MIN_PIECES;
        badge.className = isMet 
            ? 'text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shadow-2xs'
            : 'text-xs font-bold px-2.5 py-1 rounded-xl bg-pink-50 text-[#F25996] border border-pink-200 whitespace-nowrap shadow-2xs';
        badge.textContent = isMet ? `✅ ${setTotalPcs} / ${MIN_PIECES} Pcs` : `⏳ ${setTotalPcs} / ${MIN_PIECES} Pcs`;
    }
}

// Toggle a size variant directly in a specific set container
function toggleSetSize(setIdx, sizeName) {
    const targetSet = fabricSetsData[setIdx - 1];
    if (!targetSet) return;
    if (!targetSet.selected_sizes) targetSet.selected_sizes = [];
    if (!targetSet.sizes) targetSet.sizes = {};

    const limits = getSizeLimits(sizeName, 1);
    const isSel = targetSet.selected_sizes.includes(sizeName);
    if (isSel) {
        targetSet.selected_sizes = targetSet.selected_sizes.filter(s => s !== sizeName);
        targetSet.sizes[sizeName] = 0;
    } else {
        const currentSetTotal = (targetSet.selected_sizes || []).reduce((a, s) => a + (parseInt(targetSet.sizes ? targetSet.sizes[s] : 0) || 0), 0);
        if (currentSetTotal + limits.baseMin > MAX_PIECES) {
            if (typeof Swal !== 'undefined') {
                const currentMult = getFabricQuantity();
                const nextRollNotice = currentMult > 1 
                    ? `Please add this size to Roll #${setIdx === 1 ? 2 : 1} or the other rolls.` 
                    : `To order more sizes, please increase the Fabric Quantity.`;
                Swal.fire({
                    icon: 'warning',
                    title: `Roll #${setIdx} is Full (${MAX_PIECES} Pcs)`,
                    text: `Adding Size ${sizeName} (${limits.baseMin} pcs) exceeds the ${MAX_PIECES} pcs limit for Roll #${setIdx}. ${nextRollNotice}`,
                    confirmButtonColor: '#F25996'
                });
            }
            return;
        }

        const currentMult = getFabricQuantity();
        const currentMaxPieces = currentMult * MAX_PIECES;
        const currentTotal = Object.values(getSelectedSizes()).reduce((a, b) => a + b, 0);
        if (currentTotal + limits.baseMin > currentMaxPieces) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maximum Pieces Limit Reached',
                    text: `Adding this variant requires ${limits.baseMin} pcs, but maximum allowed for Fabric Quantity ${currentMult} is ${currentMaxPieces} pcs (currently ${currentTotal} pcs). Please increase the Fabric Quantity first.`,
                    confirmButtonColor: '#F25996'
                });
            }
            return;
        }
        targetSet.selected_sizes.push(sizeName);
        if (!targetSet.sizes[sizeName] || targetSet.sizes[sizeName] === 0) {
            targetSet.sizes[sizeName] = limits.baseMin;
        }
    }

    activeConfigSet = setIdx;
    applyVariantSelectionFlow();
}

// Remove a size variant from a specific set container
function removeSetSize(setIdx, sizeName) {
    const targetSet = fabricSetsData[setIdx - 1];
    if (!targetSet) return;
    if (!targetSet.selected_sizes) targetSet.selected_sizes = [];
    if (!targetSet.sizes) targetSet.sizes = {};

    targetSet.selected_sizes = targetSet.selected_sizes.filter(s => s !== sizeName);
    targetSet.sizes[sizeName] = 0;

    applyVariantSelectionFlow();
}

function onFabricMultiplierChange(inp) {
    let mult = parseInt(inp.value) || 1;
    const maxStock = Math.max(1, Math.floor(FABRIC_STOCK));
    if (mult < 1) {
        mult = 1;
        inp.value = 1;
    }
    if (mult > maxStock) {
        mult = maxStock;
        inp.value = maxStock;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Stock Limit Reached',
                text: `Quantity cannot exceed available stock (${maxStock} Qty).`,
                confirmButtonColor: '#F25996'
            });
        }
    }


    const topInp = document.getElementById('fabric_multiplier_top');
    if (topInp && topInp !== inp) topInp.value = mult;
    const bottomInp = document.getElementById('fabric_multiplier');
    if (bottomInp && bottomInp !== inp) bottomInp.value = mult;

    // Update multiplier badge
    const badge = document.getElementById('fabricQtyBadge');
    if (badge) {
        badge.textContent = mult === 1 ? '1 Set (1x)' : `${mult} Sets (${mult}x)`;
    }

    // Update required pieces Min & Max labels dynamically
    const reqMinPcs = MIN_PIECES * mult;
    const reqMaxPcs = MAX_PIECES * mult;
    const reqBadge = document.getElementById('fabricRequiredPcs');
    if (reqBadge) reqBadge.textContent = `${reqMinPcs} Pcs`;
    const maxBadge = document.getElementById('fabricMaxPcs');
    if (maxBadge) maxBadge.textContent = `${reqMaxPcs} Pcs`;

    // Also update tracker badges (Min: reqMinPcs, Max: reqMaxPcs)
    const summaryMin = document.getElementById('summaryMinPcsDisplay');
    if (summaryMin) summaryMin.textContent = `${reqMinPcs} Pcs`;
    const summaryMax = document.getElementById('summaryMaxPcsDisplay');
    if (summaryMax) summaryMax.textContent = `${reqMaxPcs} Pcs`;

    // Sync fabric sets to match new mult
    syncFabricSets(mult);

    // Apply variant flow and re-render set containers
    applyVariantSelectionFlow();
}

function toggleSizeVariant(sizeName) {
    const limits = getSizeLimits(sizeName, 1);
    const currentMult = getFabricQuantity();
    const currentMaxPieces = currentMult * MAX_PIECES;

    if (activeConfigSet === 'all') {
        // Toggle across all sets
        const anyHas = fabricSetsData.some(st => (st.selected_sizes || []).includes(sizeName));
        if (!anyHas) {
            // Adding size to sets: check capacity
            const currentTotal = Object.values(getSelectedSizes()).reduce((a, b) => a + b, 0);
            const neededToAdd = fabricSetsData.length * limits.baseMin;
            if (currentTotal + neededToAdd > currentMaxPieces) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maximum Pieces Limit Reached',
                        text: `Adding this variant requires ${neededToAdd} pcs, but maximum allowed for Fabric Quantity ${currentMult} is ${currentMaxPieces} pcs (currently ${currentTotal} pcs). Please increase the Fabric Quantity first.`,
                        confirmButtonColor: '#F25996'
                    });
                }
                return;
            }
        }
        fabricSetsData.forEach(st => {
            if (!st.selected_sizes) st.selected_sizes = [];
            if (!st.sizes) st.sizes = {};
            if (anyHas) {
                st.selected_sizes = st.selected_sizes.filter(s => s !== sizeName);
                st.sizes[sizeName] = 0;
            } else {
                const currentSetTotal = (st.selected_sizes || []).reduce((a, s) => a + (parseInt(st.sizes ? st.sizes[s] : 0) || 0), 0);
                if (currentSetTotal + limits.baseMin <= MAX_PIECES) {
                    if (!st.selected_sizes.includes(sizeName)) {
                        st.selected_sizes.push(sizeName);
                    }
                    if (!st.sizes[sizeName] || st.sizes[sizeName] === 0) {
                        st.sizes[sizeName] = limits.baseMin;
                    }
                }
            }
        });
    } else {
        const setIdx = parseInt(activeConfigSet) || 1;
        const targetSet = fabricSetsData[setIdx - 1];
        if (targetSet) {
            if (!targetSet.selected_sizes) targetSet.selected_sizes = [];
            if (!targetSet.sizes) targetSet.sizes = {};

            const isSel = targetSet.selected_sizes.includes(sizeName);
            if (isSel) {
                targetSet.selected_sizes = targetSet.selected_sizes.filter(s => s !== sizeName);
                targetSet.sizes[sizeName] = 0;
            } else {
                const currentSetTotal = (targetSet.selected_sizes || []).reduce((a, s) => a + (parseInt(targetSet.sizes ? targetSet.sizes[s] : 0) || 0), 0);
                if (currentSetTotal + limits.baseMin > MAX_PIECES) {
                    if (typeof Swal !== 'undefined') {
                        const nextRollNotice = currentMult > 1 
                            ? `Please add this size to Roll #${setIdx === 1 ? 2 : 1} or the other rolls.` 
                            : `To order more sizes, please increase the Fabric Quantity.`;
                        Swal.fire({
                            icon: 'warning',
                            title: `Roll #${setIdx} is Full (${MAX_PIECES} Pcs)`,
                            text: `Adding Size ${sizeName} (${limits.baseMin} pcs) exceeds the ${MAX_PIECES} pcs limit for Roll #${setIdx}. ${nextRollNotice}`,
                            confirmButtonColor: '#F25996'
                        });
                    }
                    return;
                }

                const currentTotal = Object.values(getSelectedSizes()).reduce((a, b) => a + b, 0);
                if (currentTotal + limits.baseMin > currentMaxPieces) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Maximum Pieces Limit Reached',
                            text: `Adding this variant requires ${limits.baseMin} pcs, but maximum allowed for Fabric Quantity ${currentMult} is ${currentMaxPieces} pcs (currently ${currentTotal} pcs). Please increase the Fabric Quantity first.`,
                            confirmButtonColor: '#F25996'
                        });
                    }
                    return;
                }
                targetSet.selected_sizes.push(sizeName);
                if (!targetSet.sizes[sizeName] || targetSet.sizes[sizeName] === 0) {
                    targetSet.sizes[sizeName] = limits.baseMin;
                }
            }
        }
    }

    applyVariantSelectionFlow();
}

function selectAllVariants() {
    const mult = getFabricQuantity();
    syncFabricSets(mult);
    const maxPiecesAllowed = MAX_PIECES * mult;

    if (activeConfigSet === 'all') {
        let runningTotal = 0;
        fabricSetsData.forEach(st => {
            st.selected_sizes = [];
            if (!st.sizes) st.sizes = {};
            SIZES_CONFIG.forEach(sz => {
                const limits = getSizeLimits(sz.size_name, 1);
                if (runningTotal + limits.baseMin <= maxPiecesAllowed) {
                    st.selected_sizes.push(sz.size_name);
                    st.sizes[sz.size_name] = limits.baseMin;
                    runningTotal += limits.baseMin;
                }
            });
        });
    } else {
        const setIdx = parseInt(activeConfigSet) || 1;
        const targetSet = fabricSetsData[setIdx - 1];
        if (targetSet) {
            let otherSetsTotal = 0;
            fabricSetsData.forEach((st, sIdx) => {
                if (sIdx !== setIdx - 1) {
                    (st.selected_sizes || []).forEach(sz => {
                        otherSetsTotal += (parseInt(st.sizes ? st.sizes[sz] : 0) || 0);
                    });
                }
            });
            let runningTotal = otherSetsTotal;
            targetSet.selected_sizes = [];
            if (!targetSet.sizes) targetSet.sizes = {};
            SIZES_CONFIG.forEach(sz => {
                const limits = getSizeLimits(sz.size_name, 1);
                if (runningTotal + limits.baseMin <= maxPiecesAllowed) {
                    targetSet.selected_sizes.push(sz.size_name);
                    targetSet.sizes[sz.size_name] = limits.baseMin;
                    runningTotal += limits.baseMin;
                }
            });
        }
    }

    applyVariantSelectionFlow();
}

function clearAllVariants() {
    if (activeConfigSet === 'all') {
        fabricSetsData.forEach(st => {
            st.selected_sizes = [];
            if (st.sizes) {
                Object.keys(st.sizes).forEach(sz => { st.sizes[sz] = 0; });
            }
        });
    } else {
        const setIdx = parseInt(activeConfigSet) || 1;
        const targetSet = fabricSetsData[setIdx - 1];
        if (targetSet) {
            targetSet.selected_sizes = [];
            if (targetSet.sizes) {
                Object.keys(targetSet.sizes).forEach(sz => { targetSet.sizes[sz] = 0; });
            }
        }
    }

    applyVariantSelectionFlow();
}

function applyVariantSelectionFlow() {
    const mult = getFabricQuantity();

    renderRollTabs();

    // Determine which sizes are active for the currently selected config tab in Section 1
    let activeSizesForTab = [];
    if (activeConfigSet === 'all') {
        fabricSetsData.forEach(st => {
            (st.selected_sizes || []).forEach(sz => {
                if (!activeSizesForTab.includes(sz)) activeSizesForTab.push(sz);
            });
        });
    } else {
        const setIdx = parseInt(activeConfigSet) || 1;
        const targetSet = fabricSetsData[setIdx - 1];
        activeSizesForTab = targetSet ? (targetSet.selected_sizes || []) : [];
    }

    // Update Section 1 header pill
    const cfgLabel = document.getElementById('currentConfigSetLabel');
    if (cfgLabel) {
        if (mult === 1) {
            cfgLabel.textContent = 'Roll #1 Variants';
        } else if (activeConfigSet === 'all') {
            cfgLabel.textContent = 'Configuring All Rolls';
        } else {
            cfgLabel.textContent = `Configuring Roll #${activeConfigSet}`;
        }
    }

    // Update Section 1 button visual states
    SIZES_CONFIG.forEach(sz => {
        const sizeName = sz.size_name;
        const btn = document.getElementById('btn_size_' + sizeName);
        const isSelected = activeSizesForTab.includes(sizeName);
        const limits = getSizeLimits(sizeName, mult);

        // Piece count for this size in current tab
        let sizePiecesInTab = 0;
        if (activeConfigSet === 'all') {
            fabricSetsData.forEach(st => {
                if ((st.selected_sizes || []).includes(sizeName)) {
                    sizePiecesInTab += (parseInt(st.sizes ? st.sizes[sizeName] : 0) || 0);
                }
            });
        } else {
            const setIdx = parseInt(activeConfigSet) || 1;
            const targetSet = fabricSetsData[setIdx - 1];
            if (targetSet && (targetSet.selected_sizes || []).includes(sizeName)) {
                sizePiecesInTab = parseInt(targetSet.sizes ? targetSet.sizes[sizeName] : 0) || 0;
            }
        }

        const countBadge = document.getElementById('btn_count_badge_' + sizeName);
        if (countBadge) {
            countBadge.textContent = sizePiecesInTab;
            countBadge.className = isSelected 
                ? 'text-xs font-black text-[#F25996]' 
                : 'text-xs font-bold text-gray-300';
        }

        if (btn) {
            const checkIcon = btn.querySelector('.check-icon');
            if (isSelected) {
                btn.classList.add('border-[#F25996]', 'bg-pink-50/40', 'shadow-xs', 'ring-2', 'ring-[#F25996]/20');
                btn.classList.remove('border-gray-200', 'bg-white');
                if (checkIcon) {
                    checkIcon.classList.add('bg-[#F25996]', 'border-[#F25996]', 'text-white');
                    checkIcon.classList.remove('border-gray-300', 'text-transparent');
                }
            } else {
                btn.classList.remove('border-[#F25996]', 'bg-pink-50/40', 'shadow-xs', 'ring-2', 'ring-[#F25996]/20');
                btn.classList.add('border-gray-200', 'bg-white');
                if (checkIcon) {
                    checkIcon.classList.remove('bg-[#F25996]', 'border-[#F25996]', 'text-white');
                    checkIcon.classList.add('border-gray-300', 'text-transparent');
                }
            }
        }

        const btnRange = document.getElementById('btn_range_label_' + sizeName);
        if (btnRange) {
            btnRange.textContent = `${limits.baseMin} – ${limits.max} pcs`;
        }
    });

    const notice = document.getElementById('activeVariantNotice');
    if (notice) {
        const totalDistinctActive = new Set();
        fabricSetsData.forEach(st => {
            (st.selected_sizes || []).forEach(s => totalDistinctActive.add(s));
        });
        if (totalDistinctActive.size > 0) {
            if (mult > 1) {
                const breakdown = fabricSetsData.map((st, i) => `#${i + 1}: ${(st.selected_sizes || []).length} sizes`).join(', ');
                notice.textContent = `• Independent sizes per roll (${breakdown})`;
            } else {
                notice.textContent = `• ${totalDistinctActive.size} size variant(s) selected`;
            }
        } else {
            notice.textContent = '• Please click one or more size buttons above to select variants';
        }
    }

    renderFabricSetContainers();
    triggerRecalc();

    // Update SKU dynamically based on selected variant
    const skuDisplay = document.getElementById('fabricSkuDisplay');
    if (skuDisplay) {
        let currentSku = <?= json_encode(!empty($customization['fabric_sku']) ? $customization['fabric_sku'] : '') ?>;
        const allSelectedSizes = fabricSetsData.flatMap(st => st.selected_sizes || []);
        if (allSelectedSizes.length > 0) {
            const lastSelected = allSelectedSizes[allSelectedSizes.length - 1];
            const szObj = SIZES_CONFIG.find(s => s.size_name === lastSelected);
            if (szObj && szObj.variant_sku) {
                currentSku = szObj.variant_sku;
            }
        }
        if (!currentSku && SIZES_CONFIG.length > 0 && SIZES_CONFIG[0].variant_sku) {
            currentSku = SIZES_CONFIG[0].variant_sku;
        }
        skuDisplay.textContent = currentSku || 'N/A';
    }
}

function getSelectedSizes() {
    const totalSizes = {};
    fabricSetsData.forEach(st => {
        const setSizes = st.selected_sizes || [];
        setSizes.forEach(sz => {
            const q = parseInt(st.sizes ? st.sizes[sz] : 0) || 0;
            if (q > 0) {
                totalSizes[sz] = (totalSizes[sz] || 0) + q;
            }
        });
    });
    return totalSizes;
}

function onAddressSelectChange(sel) {
    const input = document.getElementById('shippingAddressInput');
    if (input) {
        input.value = sel.value;
        if (!sel.value) {
            input.focus();
        }
    }
}

function triggerRecalc() {
    const mult = getFabricQuantity();
    const sizes = getSelectedSizes();

    // Update Section 1 count badges
    SIZES_CONFIG.forEach(sz => {
        const countBadge = document.getElementById('btn_count_badge_' + sz.size_name);
        if (countBadge) {
            countBadge.textContent = sizes[sz.size_name] || 0;
        }
    });

    // Update set badges
    fabricSetsData.forEach((st, idx) => {
        updateSetBadge(idx + 1);
    });

    // Build cleaned sets data containing only active sizes for accurate server validation
    const cleanedSets = fabricSetsData.map(st => {
        const cleanSizes = {};
        const setSizes = st.selected_sizes || [];
        if (st.sizes) {
            setSizes.forEach(sz => {
                const q = parseInt(st.sizes[sz]) || 0;
                if (q > 0) cleanSizes[sz] = q;
            });
        }
        return {
            set_id: st.set_id,
            label_id: st.label_id || '',
            label_name: st.label_name || '',
            selected_sizes: [...setSizes],
            sizes: cleanSizes
        };
    });

    // Call calculation API with sets
    fetch(`${BASE_URL}/fabrics/customization/calculate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            fabric_id: FABRIC_ID,
            customization_id: CUSTOMIZATION_ID,
            fabric_quantity: mult,
            sizes: sizes,
            sets: cleanedSets,
            packaging_option: selectedPackagingOption,
            without_cover: (selectedPackagingOption === 'without_cover')
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentCalc = data;
            renderCalculation(data.consumption, data.pricing, data.delivery);
        }
    })
    .catch(err => {
        console.error("Calc error", err);
    });
}

function renderCalculation(c, p, delivery) {
    const mult = getFabricQuantity();
    const effectiveMin = c.minimum_pieces || (MIN_PIECES * mult);
    const effectiveMax = c.maximum_pieces || (MAX_PIECES * mult);
    const totalPieces = c.total_pieces || 0;

    // Piece Tracker Text & Progress (Matches Image 3)
    const summaryTotal = document.getElementById('summaryTotalPieces');
    if (summaryTotal) {
        summaryTotal.textContent = `${totalPieces} / ${effectiveMin}–${effectiveMax} Pcs`;
    }
    
    const constraintText = document.getElementById('pieceConstraintText');
    if (constraintText) {
        constraintText.innerHTML = `
            <span>Min to Enable Payment: <strong class="text-gray-900 font-bold" id="summaryMinPcsDisplay">${effectiveMin} Pcs</strong></span>
            <span>Max Allowed: <strong class="text-gray-900 font-bold" id="summaryMaxPcsDisplay">${effectiveMax} Pcs</strong></span>
        `;
    }

    const progressBar = document.getElementById('pieceProgressBar');
    if (progressBar) {
        let pct = effectiveMax > 0 ? Math.min(100, Math.round((totalPieces / effectiveMax) * 100)) : 0;
        progressBar.style.width = pct + '%';
        if (totalPieces < effectiveMin) {
            progressBar.className = 'h-full bg-amber-400 transition-all duration-300';
        } else {
            progressBar.className = 'h-full bg-[#F25996] transition-all duration-300';
        }
    }

    document.getElementById('summaryBaseConsumption').textContent = `${c.base_consumption} ${c.unit}`;
    document.getElementById('summaryWastageAmount').textContent = `+${c.wastage_amount} ${c.unit}`;
    document.getElementById('summaryRequiredFabric').textContent = `${c.required_fabric} ${c.unit}`;

    // Fabric cost display with original cost line-through if without cover discount applied
    const fabricCostElem = document.getElementById('summaryFabricCost');
    if (p.without_cover && p.without_cover_discount > 0) {
        if (fabricCostElem) {
            fabricCostElem.innerHTML = `<span class="line-through text-xs text-gray-400 mr-1.5">₹${p.fabric_cost_original.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span> ₹${p.fabric_cost.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        }
    } else {
        if (fabricCostElem) {
            fabricCostElem.textContent = `₹${p.fabric_cost.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        }
    }

    // Without Cover Discount row toggle in bill
    const pkgRow = document.getElementById('packagingDiscountRow');
    const pkgDiscElem = document.getElementById('summaryCoverDiscount');
    const pkgDiscPct = document.getElementById('summaryCoverDiscountPct');
    if (pkgRow && pkgDiscElem) {
        if (p.without_cover && p.without_cover_discount > 0) {
            pkgRow.classList.remove('hidden');
            pkgRow.classList.add('flex');
            pkgDiscElem.textContent = `-₹${p.without_cover_discount.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            if (pkgDiscPct && p.discount_percentage > 0) {
                pkgDiscPct.textContent = `(-${p.discount_percentage}%)`;
            }
        } else {
            pkgRow.classList.remove('flex');
            pkgRow.classList.add('hidden');
        }
    }

    // Dynamic Delivery Schedule update
    const delBadge = document.getElementById('deliveryDaysBadge');
    const delDateElem = document.getElementById('deliveryExpectedDate');
    const delNoteElem = document.getElementById('deliveryScaleNote');
    if (delivery && delBadge && delDateElem) {
        delBadge.textContent = `${delivery.total_days} Days`;
        delDateElem.textContent = delivery.estimated_date_formatted || delivery.estimated_date;
        if (delNoteElem) {
            delNoteElem.textContent = `Dynamically scales: ${delivery.total_days} days for ${delivery.total_pieces} pcs (${delivery.base_days} days per ${delivery.base_pieces} pcs batch)`;
        }
    } else if (delBadge && delDateElem) {
        const baseDays = DELIVERY_DAYS || 7;
        const basePcs = DELIVERY_BASE_PIECES || 100;
        const multBatch = Math.max(1, Math.ceil(totalPieces / basePcs));
        const calcDays = multBatch * baseDays;
        delBadge.textContent = `${calcDays} Days`;
        const est = new Date();
        est.setDate(est.getDate() + calcDays);
        delDateElem.textContent = est.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        if (delNoteElem) {
            delNoteElem.textContent = `Dynamically scales: ${calcDays} days for ${totalPieces} pcs (${baseDays} days per ${basePcs} pcs batch)`;
        }
    }

    const gstElem = document.getElementById('summaryGstAmount');
    if (gstElem && p && p.gst_amount !== undefined) {
        gstElem.textContent = `₹${Number(p.gst_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
    }
    const grandElem = document.getElementById('summaryGrandTotal');
    if (grandElem && p && p.total !== undefined) {
        grandElem.textContent = `₹${Number(p.total).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
    }

    const isOverStock = (mult > FABRIC_STOCK) || !c.is_stock_sufficient;
    const reqFabricElem = document.getElementById('summaryRequiredFabric');
    const reqFabricContainer = reqFabricElem ? reqFabricElem.parentElement : null;
    const statusBadge = document.getElementById('liveStatusBadge');
    const btnPay = document.getElementById('btnPayRazorpay');
    const btnProceed = document.getElementById('btnProceedCheckout');
    const errorBox = document.getElementById('errorAlertBox');
    const bar = document.getElementById('pieceProgressBar');

    const setBtnState = (enabled) => {
        [btnPay, btnProceed].forEach(btn => {
            if (!btn) return;
            btn.disabled = !enabled;
            if (enabled) {
                btn.classList.remove('opacity-40', 'cursor-not-allowed');
                btn.classList.add('hover:opacity-95', 'cursor-pointer');
            } else {
                btn.classList.add('opacity-40', 'cursor-not-allowed');
                btn.classList.remove('hover:opacity-95', 'cursor-pointer');
            }
        });
    };

    // Check individual container status
    let emptySet = null;
    let incompleteSet = null;
    let excessSet = null;
    fabricSetsData.forEach((st, idx) => {
        const sSizes = st.selected_sizes || [];
        const setPcs = sSizes.reduce((a, sz) => a + (parseInt(st.sizes ? st.sizes[sz] : 0) || 0), 0);
        if (setPcs === 0 && !emptySet) {
            emptySet = { setIndex: idx + 1, setPcs: 0, needed: MIN_PIECES };
        } else if (setPcs < MIN_PIECES && !incompleteSet) {
            incompleteSet = { setIndex: idx + 1, setPcs: setPcs, needed: MIN_PIECES - setPcs };
        } else if (setPcs > MAX_PIECES && !excessSet) {
            excessSet = { setIndex: idx + 1, setPcs: setPcs, excess: setPcs - MAX_PIECES };
        }
    });

    if (isOverStock) {
        // Exceeded Fabric stock
        if (bar) bar.className = 'h-full bg-rose-600 transition-all duration-300';
        if (reqFabricContainer) reqFabricContainer.className = 'flex items-center justify-between font-bold bg-rose-50 p-2.5 rounded-xl border border-rose-200 text-rose-900';
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300 shrink-0 whitespace-nowrap';
            statusBadge.textContent = '❌ Exceeds Fabric Stock';
        }

        if (errorBox) {
            errorBox.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <span class="text-base">⚠️</span>
                    <div>
                        <strong class="font-bold block mb-1">Fabric Stock Exceeded!</strong>
                        <span>You selected <strong>${mult} Sets</strong>, but only <strong>${FABRIC_STOCK} Qty</strong> are available in inventory. Please reduce the fabric set quantity.</span>
                    </div>
                </div>
            `;
            errorBox.classList.remove('hidden');
        }

        setBtnState(false);

    } else if (totalPieces > effectiveMax) {
        // Exceeds Maximum allowed pieces for chosen sets
        const excess = totalPieces - effectiveMax;
        if (bar) bar.className = 'h-full bg-rose-600 transition-all duration-300';
        if (reqFabricContainer) reqFabricContainer.className = 'flex items-center justify-between font-bold bg-rose-50 p-2.5 rounded-xl border border-rose-200 text-rose-900';
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300 shrink-0 whitespace-nowrap';
            statusBadge.textContent = `❌ Exceeds Max (${totalPieces}/${effectiveMax} Pcs)`;
        }

        if (errorBox) {
            errorBox.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <span class="text-base">⚠️</span>
                    <div>
                        <strong class="font-bold block mb-0.5 text-rose-900">Maximum ${effectiveMax} Pieces Allowed</strong>
                        <span class="text-rose-800 text-xs leading-relaxed">
                            You currently have <strong>${totalPieces} pcs</strong> selected (${excess} pcs in excess). Maximum allowed is <strong>${effectiveMax} pcs</strong> for ${mult} Set(s). Please reduce variant quantities to enable checkout.
                        </span>
                    </div>
                </div>
            `;
            errorBox.classList.remove('hidden');
        }

        setBtnState(false);

    } else if (excessSet) {
        // A single container has exceeded its capacity
        if (bar) bar.className = 'h-full bg-rose-600 transition-all duration-300';
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300 shrink-0 whitespace-nowrap';
            statusBadge.textContent = `❌ Roll #${excessSet.setIndex} Exceeds Limit`;
        }

        if (errorBox) {
            errorBox.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <span class="text-base">⚠️</span>
                    <div>
                        <strong class="font-bold block mb-0.5 text-rose-900">Roll #${excessSet.setIndex} is Full (${MAX_PIECES} Pieces Max)</strong>
                        <span class="text-rose-800 text-xs leading-relaxed">
                            Roll #${excessSet.setIndex} has <strong>${excessSet.setPcs} pieces</strong>, but each roll can only hold up to <strong>${MAX_PIECES} pieces</strong> (${excessSet.excess} pcs extra). Please reduce pieces in Roll #${excessSet.setIndex}.
                        </span>
                    </div>
                </div>
            `;
            errorBox.classList.remove('hidden');
        }

        setBtnState(false);

    } else if (emptySet || incompleteSet || totalPieces < effectiveMin || !c.is_valid) {
        // Under minimum pieces or an incomplete container
        const targetProblem = emptySet || incompleteSet;
        const remainingPcs = effectiveMin - totalPieces;
        if (bar) bar.className = 'h-full bg-amber-400 transition-all duration-300';
        if (reqFabricContainer) reqFabricContainer.className = 'flex items-center justify-between font-bold bg-amber-50/70 p-2.5 rounded-xl border border-amber-200 text-amber-900';
        
        let badgeMsg = `⏳ Need ${remainingPcs > 0 ? remainingPcs : 'More'} Pcs`;
        if (targetProblem && targetProblem.needed > 0) {
            badgeMsg = `⏳ Roll #${targetProblem.setIndex} Needs ${targetProblem.needed} More Pcs`;
        }
        
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 shrink-0 whitespace-nowrap';
            statusBadge.textContent = badgeMsg;
        }

        if (errorBox) {
            let detailMsg = `Minimum <strong>${effectiveMin} pieces</strong> required across all ${mult} roll containers (${MIN_PIECES} pieces in each container). You currently have <strong>${totalPieces} pieces</strong> selected.`;
            if (targetProblem) {
                detailMsg += ` Roll #${targetProblem.setIndex} currently has ${targetProblem.setPcs} pcs and needs <strong>${targetProblem.needed} more pcs</strong> to meet its required ${MIN_PIECES} pcs.`;
            } else if (remainingPcs > 0) {
                detailMsg += ` Please add <strong>${remainingPcs} more pcs</strong> from the size variants to proceed to checkout.`;
            }

            errorBox.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <span class="text-base">⏳</span>
                    <div>
                        <strong class="font-bold block mb-0.5 text-amber-900">Total ${effectiveMin} Pieces Required to Enable Checkout</strong>
                        <span class="text-amber-800 text-xs leading-relaxed">
                            ${detailMsg}
                        </span>
                    </div>
                </div>
            `;
            errorBox.classList.remove('hidden');
        }

        setBtnState(false);

    } else if (c.is_valid && totalPieces >= effectiveMin && totalPieces <= effectiveMax) {
        // Valid & Ready
        if (bar) bar.className = 'h-full bg-emerald-500 transition-all duration-300';
        if (reqFabricContainer) reqFabricContainer.className = 'flex items-center justify-between font-bold bg-emerald-50 p-2.5 rounded-xl border border-emerald-200 text-emerald-950';
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0 whitespace-nowrap';
            statusBadge.textContent = `✅ Ready for Checkout (${totalPieces} Pcs)`;
        }

        if (errorBox) errorBox.classList.add('hidden');
        setBtnState(true);

    } else {
        // Default / Empty
        if (bar) bar.className = 'h-full bg-gray-300 transition-all duration-300';
        if (reqFabricContainer) reqFabricContainer.className = 'flex items-center justify-between font-bold bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-gray-800';
        if (statusBadge) {
            statusBadge.className = 'text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 shrink-0 whitespace-nowrap';
            statusBadge.textContent = `Min ${effectiveMin} Pcs Required`;
        }

        if (errorBox) errorBox.classList.add('hidden');
        setBtnState(false);
    }
}

function checkLoginOrPrompt() {
    if (!IS_LOGGED_IN) {
        Swal.fire({
            title: 'Please Login',
            text: 'You need to be logged in to place custom wholesale orders.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#F25996',
            confirmButtonText: 'Go to Login',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = `${BASE_URL}/login`;
            }
        });
        return false;
    }
    return true;
}

function getPayload() {
    const sizes = getSelectedSizes();
    const fabricQuantity = getFabricQuantity();
    const address = document.getElementById('shippingAddressInput') ? document.getElementById('shippingAddressInput').value.trim() : '';
    const notes = document.getElementById('orderNotesInput') ? document.getElementById('orderNotesInput').value.trim() : '';

    const cleanSets = fabricSetsData.map(st => {
        const cleanSizes = {};
        const setSizes = st.selected_sizes || [];
        if (st.sizes) {
            setSizes.forEach(sz => {
                const q = parseInt(st.sizes[sz]) || 0;
                if (q > 0) cleanSizes[sz] = q;
            });
        }
        return {
            set_id: st.set_id,
            label_id: st.label_id || '',
            label_name: st.label_name || '',
            selected_sizes: [...setSizes],
            sizes: cleanSizes
        };
    });

    return {
        fabric_id: FABRIC_ID,
        customization_id: CUSTOMIZATION_ID,
        fabric_quantity: fabricQuantity,
        sizes: sizes,
        sets: cleanSets,
        shipping_address: address,
        notes: notes,
        packaging_option: selectedPackagingOption,
        without_cover: (selectedPackagingOption === 'without_cover')
    };
}

// -------------------------------------------------------------
// Proceed to Step 2: Dedicated Checkout & Razorpay Page
// -------------------------------------------------------------
function proceedToCheckout() {
    if (!checkLoginOrPrompt()) return;

    const sizes = getSelectedSizes();
    let fabricQuantity = getFabricQuantity();
    const totalPcs = Object.values(sizes).reduce((a, b) => a + b, 0);

    if (totalPcs === 0) {
        Swal.fire({
            title: 'Select Size Quantities',
            text: 'Please select quantities for at least one size variant to proceed to checkout.',
            icon: 'info',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    const mult = fabricQuantity;
    const effectiveMin = MIN_PIECES * mult;
    const effectiveMax = MAX_PIECES * mult;

    // Check each container individually
    for (let i = 0; i < fabricSetsData.length; i++) {
        const st = fabricSetsData[i];
        const setNum = st.set_id || (i + 1);
        const setSizes = st.selected_sizes || [];
        const setPcs = setSizes.reduce((a, sz) => a + (parseInt(st.sizes ? st.sizes[sz] : 0) || 0), 0);
        
        if (setPcs === 0) {
            Swal.fire({
                title: `Roll #${setNum} is Empty`,
                text: `Please select ${MIN_PIECES} pieces for Roll #${setNum} before proceeding to checkout.`,
                icon: 'warning',
                confirmButtonColor: '#F25996'
            });
            return;
        }

        if (setPcs < MIN_PIECES) {
            const need = MIN_PIECES - setPcs;
            Swal.fire({
                title: `Roll #${setNum} Needs More Pieces`,
                text: `Roll #${setNum} currently has ${setPcs} pieces, but needs ${MIN_PIECES} pieces (please add ${need} more pcs to this roll).`,
                icon: 'warning',
                confirmButtonColor: '#F25996'
            });
            return;
        }

        if (setPcs > MAX_PIECES) {
            const excess = setPcs - MAX_PIECES;
            Swal.fire({
                title: `Roll #${setNum} is Over Limit`,
                text: `Roll #${setNum} has ${setPcs} pieces (${excess} pcs extra). Maximum allowed is ${MAX_PIECES} pieces per roll.`,
                icon: 'warning',
                confirmButtonColor: '#F25996'
            });
            return;
        }
    }

    if (totalPcs < effectiveMin) {
        const remaining = effectiveMin - totalPcs;
        Swal.fire({
            title: 'Minimum Pieces Required',
            text: `Please select at least ${effectiveMin} total pieces (${MIN_PIECES} pcs per roll × ${mult} rolls) to continue (currently ${totalPcs} pcs, need ${remaining} more pcs).`,
            icon: 'warning',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    if (totalPcs > effectiveMax) {
        Swal.fire({
            title: 'Maximum Limit Exceeded',
            text: `Maximum allowed pieces for ${mult} set(s) is ${effectiveMax} pcs (currently ${totalPcs} pcs).`,
            icon: 'warning',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    if (fabricQuantity > FABRIC_STOCK) {
        Swal.fire({
            title: 'Fabric Stock Shortage',
            text: `You selected ${fabricQuantity} Sets, but only ${FABRIC_STOCK} Qty are available in stock.`,
            icon: 'warning',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    // Clean sets for checkout submission
    const cleanSets = fabricSetsData.map(st => {
        const cleanSizes = {};
        const setSizes = st.selected_sizes || [];
        if (st.sizes) {
            setSizes.forEach(sz => {
                const q = parseInt(st.sizes[sz]) || 0;
                if (q > 0) cleanSizes[sz] = q;
            });
        }
        return {
            set_id: st.set_id,
            label_id: st.label_id || '',
            label_name: st.label_name || '',
            selected_sizes: [...setSizes],
            sizes: cleanSizes
        };
    });

    // Submit via dynamic POST form to checkout page
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${BASE_URL}/fabrics/customize/checkout`;

    const addField = (name, val) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = name;
        inp.value = val;
        form.appendChild(inp);
    };

    addField('fabric_id', FABRIC_ID);
    addField('customization_id', CUSTOMIZATION_ID);
    addField('fabric_quantity', fabricQuantity);
    addField('packaging_option', selectedPackagingOption);
    addField('without_cover', selectedPackagingOption === 'without_cover' ? '1' : '0');

    // Pass aggregated sizes
    Object.keys(sizes).forEach(sz => {
        addField(`sizes[${sz}]`, sizes[sz]);
    });

    // Pass detailed sets array & sets JSON
    addField('sets_json', JSON.stringify(cleanSets));
    cleanSets.forEach((st, sIdx) => {
        addField(`sets[${sIdx}][set_id]`, st.set_id);
        addField(`sets[${sIdx}][label_id]`, st.label_id || '');
        addField(`sets[${sIdx}][label_name]`, st.label_name || '');
        if (st.sizes) {
            Object.keys(st.sizes).forEach(sz => {
                addField(`sets[${sIdx}][sizes][${sz}]`, st.sizes[sz]);
            });
        }
    });

    document.body.appendChild(form);
    form.submit();
}

// -------------------------------------------------------------
// Online Payment via Razorpay
// -------------------------------------------------------------
function payViaRazorpay() {
    if (!checkLoginOrPrompt()) return;

    const payload = getPayload();
    if (Object.keys(payload.sizes).length === 0) {
        Swal.fire({ title: 'Select Sizes', text: 'Please add quantities for at least one size.', icon: 'warning', confirmButtonColor: '#F25996' });
        return;
    }

    if (!currentCalc || !currentCalc.consumption.is_valid || (getFabricQuantity() > FABRIC_STOCK)) {
        let msg = 'Please satisfy the required piece requirements.';
        if (getFabricQuantity() > FABRIC_STOCK || (currentCalc && !currentCalc.consumption.is_stock_sufficient)) {
            msg = `<strong>Fabric Inventory Shortage:</strong> You selected <strong>${getFabricQuantity()} Sets</strong>, but only <strong>${FABRIC_STOCK} Qty</strong> are available in stock.`;
        } else if (currentCalc && currentCalc.consumption.errors) {
            msg = currentCalc.consumption.errors.join('<br>');
        }
        Swal.fire({ title: 'Validation Notice', html: msg, icon: 'warning', confirmButtonColor: '#F25996' });
        return;
    }

    Swal.fire({ title: 'Connecting to Razorpay...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    // Step 1: Initialize Razorpay Order
    fetch(`${BASE_URL}/fabrics/customization/razorpay-init`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (!data.success) {
            Swal.fire({ title: 'Payment Init Failed', text: data.message || 'Unable to start online payment', icon: 'error', confirmButtonColor: '#F25996' });
            return;
        }

        // Razorpay Options
        const options = {
            "key": data.razorpay_key,
            "amount": data.amount_in_paise,
            "currency": data.currency || "INR",
            "name": "GRM B2B Garment Customizer",
            "description": `Custom Order: ${data.program_name}`,
            "order_id": data.razorpay_order_id,
            "prefill": {
                "name": data.user.name,
                "email": data.user.email,
                "contact": data.user.phone
            },
            "theme": {
                "color": "#F25996"
            },
            "handler": function (response) {
                // Step 2: Verify Razorpay Payment Signature
                Swal.fire({ title: 'Verifying Payment & Confirming Order...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const verifyPayload = Object.assign({}, payload, {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id || data.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature || ''
                });

                fetch(`${BASE_URL}/fabrics/customization/razorpay-verify`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(verifyPayload)
                })
                .then(r => r.json())
                .then(verifyRes => {
                    if (verifyRes.success) {
                        Swal.fire({
                            title: 'Order Confirmed! 🧵',
                            text: verifyRes.message,
                            icon: 'success',
                            confirmButtonColor: '#F25996',
                            confirmButtonText: 'View Invoice & Order'
                        }).then(() => {
                            window.location.href = verifyRes.redirect_url || `${BASE_URL}/dashboard/orders`;
                        });
                    } else {
                        Swal.fire({ title: 'Verification Error', text: verifyRes.message || 'Payment verification failed', icon: 'error', confirmButtonColor: '#F25996' });
                    }
                })
                .catch(err => {
                    Swal.fire({ title: 'Network Error', text: 'Error verifying payment with server.', icon: 'error', confirmButtonColor: '#F25996' });
                });
            },
            "modal": {
                "ondismiss": function() {
                    console.log('Razorpay modal closed by user');
                }
            }
        };

        try {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (resp){
                Swal.fire({ title: 'Payment Failed', text: resp.error.description || 'Payment was declined or cancelled.', icon: 'error', confirmButtonColor: '#F25996' });
            });
            rzp.open();
        } catch (e) {
            console.error("Razorpay error", e);
            Swal.fire({ title: 'Razorpay Error', text: 'Unable to open Razorpay modal: ' + e.message, icon: 'error', confirmButtonColor: '#F25996' });
        }
    })
    .catch(err => {
        Swal.fire({ title: 'Connection Error', text: 'Unable to connect to payment gateway.', icon: 'error', confirmButtonColor: '#F25996' });
    });
}

// Custom Pink Dropdown Component Handler
function initCustomPinkSelects() {
    const selects = document.querySelectorAll('select.custom-select-pink');
    
    selects.forEach(function (sel) {
        if (sel.dataset.customInit) return;
        sel.dataset.customInit = '1';

        // Hide the native select
        sel.style.display = 'none';

        // Create Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-pink-wrapper mb-2.5';
        sel.parentNode.insertBefore(wrapper, sel);
        wrapper.appendChild(sel);

        // Create Trigger Button
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-pink-trigger';

        const label = document.createElement('span');
        label.className = 'custom-select-pink-label';
        const selectedOption = sel.options[sel.selectedIndex] || sel.options[0];
        label.textContent = selectedOption ? selectedOption.text : 'Select Address';
        trigger.appendChild(label);

        // SVG Chevron Arrow (matches Image 2 style)
        const arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        arrow.setAttribute('class', 'custom-select-pink-arrow');
        arrow.setAttribute('viewBox', '0 0 20 20');
        arrow.setAttribute('fill', 'none');
        arrow.setAttribute('stroke', 'currentColor');
        arrow.setAttribute('stroke-width', '2.2');
        arrow.setAttribute('stroke-linecap', 'round');
        arrow.setAttribute('stroke-linejoin', 'round');
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M5 7.5l5 5 5-5');
        arrow.appendChild(path);
        trigger.appendChild(arrow);

        wrapper.appendChild(trigger);

        // Create Menu List
        const menu = document.createElement('div');
        menu.className = 'custom-select-pink-menu';

        Array.from(sel.options).forEach(function (opt) {
            const item = document.createElement('div');
            item.className = 'custom-select-pink-item' + (opt.selected ? ' selected' : '');
            item.textContent = opt.text;

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                sel.value = opt.value;
                label.textContent = opt.text;

                menu.querySelectorAll('.custom-select-pink-item').forEach(function (el) {
                    el.classList.remove('selected');
                });
                item.classList.add('selected');

                trigger.classList.remove('open');
                menu.classList.remove('open');

                // Trigger change event & handler
                sel.dispatchEvent(new Event('change'));
                if (typeof onAddressSelectChange === 'function') {
                    onAddressSelectChange(sel);
                }
            });

            menu.appendChild(item);
        });

        wrapper.appendChild(menu);

        // Toggle dropdown on click
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = trigger.classList.contains('open');

            // Close all other open dropdowns
            document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
                t.classList.remove('open');
                const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
                if (m) m.classList.remove('open');
            });

            if (!isOpen) {
                trigger.classList.add('open');
                menu.classList.add('open');
            }
        });
    });

    // Close on click outside
    document.addEventListener('click', function () {
        document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
            t.classList.remove('open');
            const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
            if (m) m.classList.remove('open');
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
                t.classList.remove('open');
                const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
                if (m) m.classList.remove('open');
            });
        }
    });
}

// Initial calculation & dropdown initialization on load
document.addEventListener('DOMContentLoaded', () => {
    initCustomPinkSelects();

    const mult = parseInt(SAVED_QTY) || 1;
    const topInp = document.getElementById('fabric_multiplier_top');
    const bottomInp = document.getElementById('fabric_multiplier');
    if (topInp) topInp.value = mult;
    if (bottomInp) bottomInp.value = mult;

    const badge = document.getElementById('fabricQtyBadge');
    if (badge) {
        badge.textContent = mult === 1 ? '1 Set (1x)' : `${mult} Sets (${mult}x)`;
    }

    if (SAVED_PKG) {
        selectPackagingOption(SAVED_PKG);
    }

    const hasSavedSets = SAVED_SETS && Array.isArray(SAVED_SETS) && SAVED_SETS.length > 0;
    const hasSavedSizes = SAVED_SIZES && typeof SAVED_SIZES === 'object' && Object.keys(SAVED_SIZES).some(k => parseInt(SAVED_SIZES[k]) > 0);

    if (hasSavedSets) {
        fabricSetsData = JSON.parse(JSON.stringify(SAVED_SETS));
        syncFabricSets(mult);
        fabricSetsData.forEach(s => {
            if (!s.selected_sizes || !Array.isArray(s.selected_sizes) || s.selected_sizes.length === 0) {
                s.selected_sizes = [];
                if (s.sizes) {
                    Object.keys(s.sizes).forEach(sz => {
                        if (parseInt(s.sizes[sz]) > 0 && !s.selected_sizes.includes(sz)) {
                            s.selected_sizes.push(sz);
                        }
                    });
                }
                if (s.selected_sizes.length === 0) {
                    s.selected_sizes = SIZES_CONFIG.map(sc => sc.size_name);
                }
            }
        });
        applyVariantSelectionFlow();
    } else if (hasSavedSizes) {
        const activeSizes = Object.keys(SAVED_SIZES).filter(k => parseInt(SAVED_SIZES[k]) > 0);
        const initSizes = activeSizes.length > 0 ? activeSizes : SIZES_CONFIG.map(s => s.size_name);
        syncFabricSets(mult);
        if (fabricSetsData[0]) {
            fabricSetsData[0].sizes = Object.assign({}, SAVED_SIZES);
            fabricSetsData[0].selected_sizes = [...initSizes];
        }
        applyVariantSelectionFlow();
    } else {
        // Automatically create and select variant containers based on current quantity
        selectAllVariants();
    }
});
</script>

<!-- Custom Pink Dropdown Component Styles (Matches Image 2 Style & Registration Page State Dropdown) -->
<style>
    /* ── Custom Select (Matches Registration Page State Dropdown) ── */
    .reg-cs-wrapper {
        position: relative;
        display: inline-block;
        width: auto;
        min-width: 170px;
        max-width: 220px;
    }
    @media (min-width: 640px) {
        .reg-cs-wrapper {
            min-width: 190px;
            max-width: 270px;
        }
    }
    .reg-cs-trigger {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 0.45rem 0.75rem;
        font-size: 0.8125rem;
        color: #111827;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s ease-in-out;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        user-select: none;
        font-weight: 500;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .reg-cs-trigger:hover {
        border-color: #d1d5db;
    }
    .reg-cs-trigger.placeholder {
        color: #9ca3af;
        font-weight: 400;
    }
    .reg-cs-trigger:focus,
    .reg-cs-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
    }
    .reg-cs-arrow {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: #9ca3af;
        transition: transform 0.2s ease, color 0.2s ease;
        pointer-events: none;
    }
    .reg-cs-trigger.open .reg-cs-arrow {
        transform: rotate(180deg);
        color: #F25996;
    }
    .reg-cs-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: left;
        flex: 1;
        min-width: 0;
    }
    .reg-cs-list {
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        left: auto;
        min-width: 220px;
        width: max-content;
        max-width: 330px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        z-index: 9999;
        max-height: 220px;
        overflow-y: auto;
        display: none;
        padding: 4px;
    }
    @media (max-width: 640px) {
        .reg-cs-list {
            right: 0;
            left: auto;
            max-width: calc(100vw - 40px);
        }
    }
    .reg-cs-list.open {
        display: block;
        animation: regCsFadeIn 0.15s ease-out;
    }
    @keyframes regCsFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .reg-cs-list::-webkit-scrollbar {
        width: 4px;
    }
    .reg-cs-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .reg-cs-list::-webkit-scrollbar-thumb {
        background: #F25996;
        border-radius: 4px;
    }
    .reg-cs-option {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        border-radius: 0.5rem;
        cursor: pointer;
        color: #374151;
        font-weight: 500;
        transition: background 0.15s, color 0.15s;
        line-height: 1.35;
        white-space: normal;
        word-break: break-word;
        text-align: left;
    }
    .reg-cs-option:hover {
        background: #fce7f3;
        color: #F25996;
    }
    .reg-cs-option.selected {
        background: #F25996 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    .reg-cs-option.disabled {
        color: #9ca3af;
        cursor: default;
        pointer-events: none;
    }
    .fabric-set-card {
        position: relative;
        overflow: visible !important;
    }
    .fabric-set-card.has-open-dropdown,
    .fabric-set-card:has(.reg-cs-trigger.open) {
        z-index: 50 !important;
    }

    .custom-select-pink-wrapper {
        position: relative;
        width: 100%;
    }
    .custom-select-pink-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.625rem 1.125rem;
        background-color: #ffffff;
        border: 1.5px solid #FDBFDD;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        outline: none;
        user-select: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .custom-select-pink-trigger:hover {
        border-color: #F25996;
    }
    .custom-select-pink-trigger:focus,
    .custom-select-pink-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
    }
    .custom-select-pink-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: left;
        flex: 1;
    }
    .custom-select-pink-arrow {
        width: 1.05rem;
        height: 1.05rem;
        color: #F25996;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
        pointer-events: none;
    }
    .custom-select-pink-trigger.open .custom-select-pink-arrow {
        transform: rotate(180deg);
    }
    .custom-select-pink-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        min-width: 100%;
        background: #ffffff;
        border: 1.5px solid #FDBFDD;
        border-radius: 1.125rem;
        box-shadow: 0 12px 30px rgba(242, 89, 150, 0.15), 0 4px 10px rgba(0,0,0,0.05);
        padding: 6px;
        z-index: 60;
        max-height: 240px;
        overflow-y: auto;
        display: none;
        animation: cfFadeIn 0.15s ease-out;
    }
    .custom-select-pink-menu.open {
        display: block;
    }
    .custom-select-pink-menu::-webkit-scrollbar {
        width: 4px;
    }
    .custom-select-pink-menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-select-pink-menu::-webkit-scrollbar-thumb {
        background: #F25996;
        border-radius: 4px;
    }
    .custom-select-pink-item {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        cursor: pointer;
        color: #374151;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
        line-height: 1.4;
    }
    .custom-select-pink-item:last-child {
        margin-bottom: 0;
    }
    .custom-select-pink-item:hover {
        background-color: #fdf2f7;
        color: #F25996;
        font-weight: 600;
    }
    .custom-select-pink-item.selected {
        background-color: #F25996 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    @keyframes cfFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ── SweetAlert Pink & Round Pill Modal Customization ── */
    .swal2-popup {
        border-radius: 1.5rem !important;
        padding: 2rem 1.5rem !important;
        font-family: inherit !important;
    }
    .swal2-title {
        font-weight: 800 !important;
        color: #111827 !important;
        letter-spacing: -0.02em !important;
    }
    .swal2-html-container {
        color: #4b5563 !important;
        font-size: 0.9375rem !important;
        line-height: 1.6 !important;
    }
    .swal2-icon {
        width: 3.25rem !important;
        height: 3.25rem !important;
        margin: 0.5rem auto 1rem !important;
        border-width: 2.5px !important;
    }
    .swal2-icon .swal2-icon-content {
        font-size: 1.75rem !important;
        line-height: 3.25rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .swal2-icon.swal2-question {
        border-color: #F25996 !important;
        color: #F25996 !important;
    }
    .swal2-icon.swal2-warning {
        border-color: #F25996 !important;
        color: #F25996 !important;
    }
    .swal2-icon.swal2-info {
        border-color: #F25996 !important;
        color: #F25996 !important;
    }
    .swal2-actions {
        gap: 0.75rem !important;
        margin-top: 1.5rem !important;
    }
    .swal2-styled.swal2-confirm {
        background: linear-gradient(135deg, #F25996 0%, #ea3c85 100%) !important;
        border-radius: 9999px !important;
        padding: 0.75rem 2rem !important;
        font-weight: 800 !important;
        font-size: 0.875rem !important;
        letter-spacing: 0.01em !important;
        box-shadow: 0 4px 14px rgba(242, 89, 150, 0.35) !important;
        transition: all 0.2s ease !important;
        border: none !important;
    }
    .swal2-styled.swal2-confirm:hover {
        background: linear-gradient(135deg, #e04885 0%, #db2777 100%) !important;
        box-shadow: 0 6px 20px rgba(242, 89, 150, 0.5) !important;
        transform: translateY(-1px) !important;
    }
    .swal2-styled.swal2-cancel {
        background-color: #f3f4f6 !important;
        color: #4b5563 !important;
        border-radius: 9999px !important;
        padding: 0.75rem 1.75rem !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        border: 1px solid #e5e7eb !important;
    }
    .swal2-styled.swal2-cancel:hover {
        background-color: #e5e7eb !important;
        color: #111827 !important;
        transform: translateY(-1px) !important;
    }
</style>
