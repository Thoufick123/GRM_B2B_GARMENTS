<div class="bg-gray-50 min-h-screen pb-12 md:pb-16">
    <!-- ── Breadcrumb Section (Home > Customize) ── -->
    <div class="bg-white border-b border-gray-200/80 mb-4 sm:mb-6">
        <div class="w-full max-w-full mx-auto px-3 sm:px-6 lg:px-8 py-3.5">
            <nav class="grm-breadcrumb-nav hide-scrollbar no-scrollbar flex flex-wrap items-center gap-1 sm:gap-1.5 text-xs sm:text-sm font-medium" aria-label="Breadcrumb">
                <a href="<?= BASE_URL ?>/" class="inline-flex items-center gap-1.5 text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0">
                    <svg class="w-4 h-4 shrink-0 text-[#F25996]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    <span>Home</span>
                </a>
                <svg class="w-4 h-4 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-[#F25996] font-bold shrink-0">Customize</span>
            </nav>
        </div>
    </div>

    <div class="w-full max-w-full mx-auto px-3 sm:px-6 lg:px-8">
        
        <!-- Hero Workshop Banner (Matches Image 2 Style) -->
        <div class="relative overflow-hidden rounded-[28px] p-6 sm:p-8 lg:px-10 lg:py-8 mb-8 border border-pink-200/80 shadow-md shadow-pink-200/30" style="background: linear-gradient(135deg, #fff2f8 0%, #fedde9 45%, #fedbe9 100%);">
            
            <!-- Soft Scissor Watermark Graphic in Background -->
            <div class="absolute right-1/3 top-4 w-28 h-28 text-pink-300/30 select-none pointer-events-none transform -rotate-12 z-0">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full stroke-[1.3]">
                    <circle cx="6" cy="6" r="3" stroke="currentColor" fill="rgba(253, 191, 221, 0.2)"></circle>
                    <circle cx="6" cy="18" r="3" stroke="currentColor" fill="rgba(253, 191, 221, 0.2)"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12"></path>
                </svg>
            </div>

            <!-- 2-Column Responsive Grid (Balanced Layout) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-center relative z-10">
                
                <!-- Left 7 Cols: Content & Stepper Flow -->
                <div class="md:col-span-7 lg:col-span-7 flex flex-col justify-center">
                    
                    <!-- White Pill Badge with Pink Scissor -->
                    <div class="inline-flex items-center self-start gap-2 px-4 py-1.5 rounded-full bg-white text-[#F25996] text-xs font-black uppercase tracking-wider border border-pink-100 mb-3 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#F25996]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/>
                        </svg>
                        <span>B2B CUSTOM TAILORING & SOURCING</span>
                    </div>
                    
                    <!-- Headline in Hot Pink with Doodle Heart -->
                    <div class="flex items-center gap-2.5 mb-3">
                        <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black font-display tracking-tight leading-[1.12] text-[#F25996]">
                            Wholesale Fabric <br class="hidden sm:inline">
                            Customization Hub
                        </h1>
                        <div class="text-[#F25996] self-start mt-1 transform rotate-12">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Description in Slate with Pink Underlines & Breathable Width -->
                    <p class="text-sm sm:text-[15px] lg:text-base text-gray-700 mb-6 sm:mb-7 leading-relaxed font-normal max-w-2xl">
                        Select any premium fabric below, configure your custom size quantities and pieces, and choose between instant <strong class="text-gray-900 font-bold underline decoration-[#F25996] decoration-2 underline-offset-4">Custom Order Requests</strong> or direct <strong class="text-gray-900 font-bold underline decoration-[#F25996] decoration-2 underline-offset-4">Online Razorpay Payment</strong>.
                    </p>

                    <!-- Workflow Stepper Flow with 4 Step Circles & Arrows -->
                    <div class="flex items-center gap-3 sm:gap-4 lg:gap-5 overflow-x-auto pt-3 pb-1 scrollbar-none">
                        
                        <!-- Step 1: Select Fabric -->
                        <div class="flex flex-col items-center flex-shrink-0 min-w-[78px] sm:min-w-[88px] text-center">
                            <div class="relative w-12 h-12 sm:w-13 sm:h-13 rounded-full bg-white border border-pink-200/90 shadow-xs flex items-center justify-center text-[#F25996]">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span class="absolute -top-1.5 -right-1.5 z-10 w-5 h-5 sm:w-5.5 sm:h-5.5 rounded-full bg-[#F25996] text-white text-[10.5px] sm:text-[11px] font-black flex items-center justify-center shadow-xs border-2 border-white">1</span>
                            </div>
                            <span class="text-xs sm:text-[13px] font-bold text-gray-800 mt-2 leading-tight">Select Fabric</span>
                        </div>

                        <!-- Step Arrow -->
                        <div class="text-pink-300 flex-shrink-0 mb-3.5 font-bold">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>

                        <!-- Step 2: Pick Sizes & Pcs -->
                        <div class="flex flex-col items-center flex-shrink-0 min-w-[78px] sm:min-w-[88px] text-center">
                            <div class="relative w-12 h-12 sm:w-13 sm:h-13 rounded-full bg-white border border-pink-200/90 shadow-xs flex items-center justify-center text-[#F25996]">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                <span class="absolute -top-1.5 -right-1.5 z-10 w-5 h-5 sm:w-5.5 sm:h-5.5 rounded-full bg-[#F25996] text-white text-[10.5px] sm:text-[11px] font-black flex items-center justify-center shadow-xs border-2 border-white">2</span>
                            </div>
                            <span class="text-xs sm:text-[13px] font-bold text-gray-800 mt-2 leading-tight">Pick Sizes & Pcs</span>
                        </div>

                        <!-- Step Arrow -->
                        <div class="text-pink-300 flex-shrink-0 mb-3.5 font-bold">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>

                        <!-- Step 3: Request / Razorpay -->
                        <div class="flex flex-col items-center flex-shrink-0 min-w-[78px] sm:min-w-[88px] text-center">
                            <div class="relative w-12 h-12 sm:w-13 sm:h-13 rounded-full bg-white border border-pink-200/90 shadow-xs flex items-center justify-center text-[#F25996]">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="absolute -top-1.5 -right-1.5 z-10 w-5 h-5 sm:w-5.5 sm:h-5.5 rounded-full bg-[#F25996] text-white text-[10.5px] sm:text-[11px] font-black flex items-center justify-center shadow-xs border-2 border-white">3</span>
                            </div>
                            <span class="text-xs sm:text-[13px] font-bold text-gray-800 mt-2 leading-tight">Request / Razorpay</span>
                        </div>

                        <!-- Step Arrow -->
                        <div class="text-pink-300 flex-shrink-0 mb-3.5 font-bold">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>

                        <!-- Step 4: Stitch & Dispatch -->
                        <div class="flex flex-col items-center flex-shrink-0 min-w-[78px] sm:min-w-[88px] text-center">
                            <div class="relative w-12 h-12 sm:w-13 sm:h-13 rounded-full bg-white border border-pink-200/90 shadow-xs flex items-center justify-center text-[#F25996]">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span class="absolute -top-1.5 -right-1.5 z-10 w-5 h-5 sm:w-5.5 sm:h-5.5 rounded-full bg-[#F25996] text-white text-[10.5px] sm:text-[11px] font-black flex items-center justify-center shadow-xs border-2 border-white">4</span>
                            </div>
                            <span class="text-xs sm:text-[13px] font-bold text-gray-800 mt-2 leading-tight">Stitch & Dispatch</span>
                        </div>

                    </div>
                </div>

                <!-- Right 5 Cols: Exact Artwork Illustration Fills Right Side Naturally -->
                <div class="md:col-span-5 lg:col-span-5 flex items-center justify-center md:justify-end">
                    <div class="relative w-full max-w-[420px] lg:max-w-[480px] xl:max-w-[500px]">
                        <img src="<?= get_image_url('https://res.cloudinary.com/typqb5pd/image/upload/v1791616562/customization_hub_hero_women_art.png') ?>" 
                             alt="Custom Maternity & Wholesale Fabric Production" 
                             class="w-full h-auto max-h-[320px] sm:max-h-[350px] lg:max-h-[380px] object-contain drop-shadow-sm mx-auto md:ml-auto">
                    </div>
                </div>

            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-8">
            <form id="customizeFilterForm" method="GET" action="<?= BASE_URL ?>/customize" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                
                <!-- Search -->
                <div class="relative flex-1 min-w-[260px]">
                    <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search customizable fabrics by name, SKU, or garment..." class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50/70 border border-gray-200 rounded-2xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#F25996]/20 focus:border-[#F25996] transition-all font-medium">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Category -->
                    <select name="category_id" class="custom-select-pink">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Garment Type -->
                    <?php if (!empty($garmentTypes)): ?>
                        <select name="garment_type" class="custom-select-pink">
                            <option value="">All Garment Types</option>
                            <?php foreach ($garmentTypes as $gt): ?>
                                <option value="<?= htmlspecialchars($gt) ?>" <?= ($filters['garment_type'] ?? '') == $gt ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- Sort -->
                    <select name="sort" class="custom-select-pink">
                        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                        <option value="stock" <?= ($filters['sort'] ?? '') === 'stock' ? 'selected' : '' ?>>Highest Fabric Stock</option>
                    </select>

                    <button type="submit" class="px-6 py-2.5 bg-[#F25996] hover:bg-[#d8407d] text-white text-sm font-bold rounded-2xl shadow-sm hover:shadow-md transition-all active:scale-95 cursor-pointer">
                        Search
                    </button>

                    <?php if (!empty($filters['search']) || !empty($filters['category_id']) || !empty($filters['garment_type']) || (!empty($filters['sort']) && $filters['sort'] !== 'newest')): ?>
                        <a href="<?= BASE_URL ?>/customize" class="px-3 py-2 text-xs font-bold text-gray-500 hover:text-[#F25996] transition-colors">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Fabrics Listing Grid -->
        <?php if (empty($fabrics)): ?>
            <div class="bg-white rounded-3xl border border-gray-100 p-16 text-center shadow-xs">
                <div class="w-20 h-20 bg-pink-50 text-[#F25996] rounded-3xl flex items-center justify-center mx-auto mb-5 text-4xl shadow-inner">
                    🧵
                </div>
                <h3 class="text-xl font-bold text-gray-900 font-display">No Customizable Fabrics Available</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mt-2">There are currently no active fabric programs configured by the admin matching your criteria. Please check back shortly.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6 lg:gap-7">
                <?php foreach ($fabrics as $f): ?>
                    <?php
                        $rawImg = !empty($f['primary_image']) ? get_image_url($f['primary_image']) : '';
                        $fallbackImg = 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=800&auto=format&fit=crop&q=80';
                        $imgSrc = !empty($rawImg) ? $rawImg : $fallbackImg;
                        $unitLabel = htmlspecialchars($f['unit'] ?? 'Qty');
                        $isLoggedIn = !empty(\Core\Session::get('user_id'));
                        $customizeDetailUrl = BASE_URL . '/fabrics/customize?fabric_id=' . (int)$f['fabric_id'];
                    ?>
                    <div class="bg-white rounded-2xl sm:rounded-[22px] border border-gray-100 overflow-hidden shadow-xs hover:shadow-xl hover:border-pink-200 transition-all duration-300 flex flex-col group transform hover:-translate-y-1">
                        
                        <!-- Top Image Area -->
                        <a href="<?= $customizeDetailUrl ?>" class="block relative h-44 sm:h-64 md:h-72 w-full bg-gradient-to-br from-pink-50 to-rose-50 overflow-hidden group/img">
                            <?php if (!empty($imgSrc)): ?>
                            <img id="fabric-main-img-<?= $f['id'] ?>" 
                                 src="<?= htmlspecialchars($imgSrc) ?>" 
                                 alt="<?= htmlspecialchars($f['fabric_name']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                <span class="text-4xl sm:text-5xl mb-2">🧵</span>
                                <span class="text-xs sm:text-sm font-bold text-gray-700"><?= htmlspecialchars($f['fabric_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Top Left Badges: CUSTOM & Garment Type (Price & Discount badges hidden for customize fabrics) -->
                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 pointer-events-none z-10 flex flex-col gap-1 sm:gap-1.5 items-start">
                                <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-[#F25996] text-white font-extrabold text-[8.5px] sm:text-[10px] uppercase tracking-wider rounded sm:rounded-lg shadow-sm flex items-center gap-0.5 sm:gap-1">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 10.5m0 0l2.879-2.879a3 3 0 114.242 4.242 3 3 0 01-4.242 0L12 10.5z"/></svg>
                                    <span>CUSTOM</span>
                                </span>
                                <?php if (!empty($f['garment_type'])): ?>
                                    <span class="px-1.5 sm:px-2 py-0.5 bg-black/65 backdrop-blur-xs text-white font-bold text-[8.5px] sm:text-[10px] uppercase tracking-wider rounded sm:rounded-md shadow-xs">
                                        <?= htmlspecialchars($f['garment_type']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Top Right: Stock Counter -->
                            <div class="absolute top-2 right-2 sm:top-3.5 sm:right-3.5 z-10 flex flex-col items-end gap-1 sm:gap-2 pointer-events-none">
                                <?php if (isset($f['fabric_stock']) && (float)$f['fabric_stock'] > 0): ?>
                                    <span class="px-1.5 sm:px-2.5 py-0.5 bg-emerald-600/95 backdrop-blur-xs text-white font-bold text-[8px] sm:text-[10px] tracking-wide rounded-full shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                        <?= (int)$f['fabric_stock'] ?> Qty
                                    </span>
                                <?php else: ?>
                                    <span class="px-1.5 sm:px-2.5 py-0.5 bg-amber-600/95 backdrop-blur-xs text-white font-bold text-[8px] sm:text-[10px] tracking-wide rounded-full shadow-sm flex items-center gap-1">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>

                        <!-- Circular Variant Swatches Overlapping Border (only when multiple images exist) -->
                        <?php 
                            $validImages = !empty($f['images']) && is_array($f['images']) ? array_values(array_filter($f['images'])) : [];
                            if ((empty($validImages) || count($validImages) <= 1) && !empty($f['id'])) {
                                try {
                                    $db = \Core\Database::getInstance();
                                    $cFallbackImgs = $db->query("SELECT image_path FROM product_images WHERE product_id = " . (int)$f['id'] . " ORDER BY is_primary DESC, sort_order ASC, id ASC")->fetchAll(\PDO::FETCH_COLUMN);
                                    if (!empty($cFallbackImgs) && count($cFallbackImgs) > 1) {
                                        $validImages = $cFallbackImgs;
                                    }
                                } catch (\Throwable $e) {}
                            }
                            if (count($validImages) > 1):
                        ?>
                        <div class="-mt-4 sm:-mt-6 mb-1.5 sm:mb-2 px-2.5 sm:px-5 flex items-center gap-1.5 sm:gap-2 relative z-20 overflow-x-auto scrollbar-none pb-0.5">
                            <?php 
                                foreach (array_slice($validImages, 0, 5) as $idx => $swImg):
                                    $swUrl = get_image_url($swImg, 100, 100);
                                    $fullSwUrl = get_image_url($swImg);
                            ?>
                                <img src="<?= htmlspecialchars($swUrl) ?>" 
                                     alt="" 
                                     onerror="if(!this.dataset.retried){this.dataset.retried='1';this.src='<?= htmlspecialchars($fullSwUrl) ?>';}else{this.style.display='none';}"
                                     onclick="event.stopPropagation(); document.getElementById('fabric-main-img-<?= $f['id'] ?>').src='<?= htmlspecialchars($fullSwUrl) ?>'; this.parentElement.querySelectorAll('img').forEach(function(i){i.classList.remove('ring-2', 'ring-[#F25996]')}); this.classList.add('ring-2', 'ring-[#F25996]');"
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
                                    <?= htmlspecialchars($f['category_name'] ?? $f['garment_type'] ?? 'MATERNITY') ?>
                                </div>

                                <!-- Product Title -->
                                <h2 class="font-bold text-[#1e293b] text-xs sm:text-base leading-snug line-clamp-1 mb-0.5 sm:mb-1 hover:text-[#F25996] transition-colors" title="<?= htmlspecialchars($f['fabric_name']) ?>">
                                    <a href="<?= $customizeDetailUrl ?>"><?= htmlspecialchars($f['fabric_name']) ?></a>
                                </h2>

                                <!-- Description -->
                                <p class="text-[10px] sm:text-xs text-[#64748b] line-clamp-1 sm:line-clamp-2 mb-2 sm:mb-3 leading-tight sm:leading-relaxed">
                                    <?= htmlspecialchars($f['fabric_description'] ?? 'Soft, breathable cotton fabric perfect for maternity wear.') ?>
                                </p>

                                <?php if ($isLoggedIn): ?>
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
                                                    <?= (int)($f['minimum_pieces'] ?? 10) ?> Pcs
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sizes Matrix -->
                                    <div class="mb-2.5 sm:mb-4">
                                        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
                                            <?php 
                                                $sizeList = !empty($f['sizes']) ? array_column($f['sizes'], 'size_name') : ['M', 'L', 'XL', 'XXL', '3XL', '4XL'];
                                            ?>
                                            <?php foreach ($sizeList as $sIndex => $szName): ?>
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
                                <a href="<?= $customizeDetailUrl ?>" 
                                   class="w-full py-2 sm:py-3 px-2 sm:px-4 bg-gradient-to-r from-[#F25996] via-[#ea3c85] to-[#db2777] hover:opacity-95 text-white font-bold text-xs sm:text-sm rounded-xl sm:rounded-2xl shadow-sm sm:shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1 sm:gap-2 group/btn active:scale-[0.98]">
                                    <span class="truncate">Customize & Stitch</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ── Custom Pink Dropdown Component (Matches Image 2 Style) ── -->
<style>
    .custom-select-pink-wrapper {
        position: relative;
        min-width: 175px;
        width: auto;
    }
    @media (max-width: 768px) {
        .custom-select-pink-wrapper {
            width: 100%;
        }
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
        min-width: 185px;
        background: #ffffff;
        border: 1.5px solid #FDBFDD;
        border-radius: 1.125rem;
        box-shadow: 0 12px 30px rgba(242, 89, 150, 0.12), 0 4px 10px rgba(0,0,0,0.05);
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('select.custom-select-pink');
    
    selects.forEach(function (sel) {
        if (sel.dataset.customInit) return;
        sel.dataset.customInit = '1';

        // Hide the native select
        sel.style.display = 'none';

        // Create Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-pink-wrapper';
        sel.parentNode.insertBefore(wrapper, sel);
        wrapper.appendChild(sel);

        // Create Trigger Button
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-pink-trigger';

        const label = document.createElement('span');
        const selectedOption = sel.options[sel.selectedIndex] || sel.options[0];
        label.textContent = selectedOption ? selectedOption.text : 'Select Option';
        trigger.appendChild(label);

        // SVG Chevron Arrow (matches Image 2)
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

                // Auto-submit the filter form
                const form = sel.closest('form');
                if (form) {
                    form.submit();
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
});
</script>
