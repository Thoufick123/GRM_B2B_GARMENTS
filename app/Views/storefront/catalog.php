<style>
    /* ── Custom Pink Select for Catalog Page (Image 1 UI format) ── */
    .cat-cs-wrapper {
        position: relative;
        width: 100%;
    }
    .cat-cs-trigger {
        width: 100%;
        border-radius: 0.75rem;
        border: 1.5px solid #F25996;
        background-color: #ffffff;
        padding: 0.5rem 0.875rem;
        font-size: 0.8125rem;
        color: #1f2937;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s ease-in-out;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
        font-weight: 600;
        gap: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    @media (min-width: 640px) {
        .cat-cs-trigger {
            font-size: 0.875rem;
            padding: 0.5625rem 1rem;
        }
    }
    .cat-cs-trigger:focus,
    .cat-cs-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.25) !important;
    }
    .cat-cs-arrow {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: #F25996;
        transition: transform 0.2s ease;
        pointer-events: none;
    }
    .cat-cs-trigger.open .cat-cs-arrow {
        transform: rotate(180deg);
    }
    .cat-cs-list {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 8px 10px -6px rgba(0,0,0,0.06);
        z-index: 9999;
        max-height: 240px;
        overflow-y: auto;
        display: none;
        padding: 4px;
        min-width: 100%;
    }
    .cat-cs-list.open {
        display: block;
    }
    .cat-cs-list::-webkit-scrollbar {
        width: 4px;
    }
    .cat-cs-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .cat-cs-list::-webkit-scrollbar-thumb {
        background: #F25996;
        border-radius: 4px;
    }
    .cat-cs-option {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        border-radius: 0.5rem;
        cursor: pointer;
        color: #374151;
        font-weight: 500;
        transition: background 0.15s, color 0.15s;
    }
    .cat-cs-option:hover {
        background: #fce7f3;
        color: #F25996;
    }
    .cat-cs-option.selected {
        background: #F25996;
        color: #ffffff;
        font-weight: 600;
    }
    @media (min-width: 1024px) {
        #filter-panel,
        #filter-panel > div {
            overflow: visible !important;
            max-height: none !important;
        }
    }
</style>

<div class="bg-[#fcfbf9] min-h-screen">
    <?php
    $currentCatName = null;
    if (!empty($selectedCategoryId) && !empty($categories)) {
        foreach ($categories as $cat) {
            if ($cat['id'] == $selectedCategoryId) {
                $currentCatName = $cat['name'];
                break;
            }
        }
    }
    ?>

    <!-- Ã¢â€â‚¬Ã¢â€â‚¬ Breadcrumb Section Ã¢â€â‚¬Ã¢â€â‚¬ -->
    <div class="bg-white border-b border-gray-200/80">
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
                <?php if ($currentCatName): ?>
                    <a href="<?= BASE_URL ?>/catalog" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors shrink-0">Catalog</a>
                    <svg class="w-4 h-4 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-[#F25996] font-bold truncate"><?= htmlspecialchars($currentCatName) ?></span>
                <?php else: ?>
                    <span class="text-[#F25996] font-bold shrink-0">Catalog</span>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="w-full max-w-full mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-16 flex flex-col lg:flex-row gap-6 lg:gap-8">
        
        <!-- Sidebar Filters -->
        <div class="w-full lg:w-64 lg:shrink-0">
            
            <!-- Mobile & Tablet: Filter Toggle Button -->
            <button id="filter-toggle-btn" class="lg:hidden w-full flex items-center justify-between bg-white border border-pink-200 rounded-xl px-4 py-3.5 mb-4 shadow-sm font-bold text-[#F25996] hover:bg-pink-50/50 transition-colors" onclick="toggleMobileFilter()">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Filters</span>
                    <?php if (!empty($attributeFilters)): ?>
                        <span class="ml-1 bg-[#fce7f3] text-[#F25996] text-[11px] px-2 py-0.5 rounded-full font-bold border border-[#f48fb1]"><?= count($attributeFilters) ?></span>
                    <?php endif; ?>
                </span>
                <span class="flex items-center gap-1.5 text-xs text-gray-500 font-normal">
                    <span>Refine Products</span>
                    <svg id="filter-toggle-icon" class="w-4 h-4 text-[#F25996] transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </button>

            <!-- Mobile & Tablet Filter Backdrop -->
            <div id="mobile-filter-backdrop" class="fixed inset-0 bg-black/50 z-[998] hidden lg:hidden" onclick="toggleMobileFilter()"></div>

            <!-- Filter Panel -->
            <div id="filter-panel" class="fixed inset-y-0 left-0 z-[999] w-[85%] max-w-sm sm:max-w-md bg-[#fce7f3] shadow-2xl transform -translate-x-full transition-transform duration-300 lg:relative lg:translate-x-0 lg:w-full lg:max-w-none lg:shadow-sm lg:z-0 flex flex-col h-[100dvh] lg:h-auto lg:bg-[#fce7f3] lg:rounded-2xl lg:p-5 lg:overflow-visible">

                <!-- Mobile & Tablet Header -->
                <div class="flex items-center justify-between p-4 border-b border-pink-200 lg:hidden shrink-0 bg-[#fce7f3]">
                    <h2 class="text-lg font-bold text-[#F25996] flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Filters
                    </h2>
                    <button onclick="toggleMobileFilter()" class="p-2 text-[#F25996] hover:text-[#7a1b1b] rounded-lg hover:bg-pink-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <!-- Filter Content Scrollable Area on Mobile, Visible on Desktop -->
                <div class="p-4 lg:p-0 flex-1 overflow-y-auto lg:overflow-visible">

                <!-- Desktop Header -->
                <div class="hidden lg:flex items-center gap-2 mb-5">
                    <svg class="w-5 h-5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <h2 class="text-xl font-bold text-[#F25996]">Filters</h2>
                </div>

                <div class="mb-4 lg:mb-5">
                    <label class="block text-xs lg:text-sm text-[#F25996] font-bold mb-1.5 pl-1">Main Category</label>
                    <select id="main-category-select" onchange="handleMainCategoryChange()" class="block w-full bg-white border border-[#f48fb1] rounded-lg px-3 py-2.5 text-sm text-gray-700 font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f48fb1]">
                        <option value="">-- All Categories --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            
                <?php 
                    $catSubCategories = [];
                    if ($selectedCategoryId) {
                        $catSubCategories = array_filter($subCategories, function($sub) use ($selectedCategoryId) {
                            return $sub['category_id'] == $selectedCategoryId;
                        });
                    }
                ?>
                <div id="sub-category-container" class="mb-4 lg:mb-5" style="<?= empty($catSubCategories) ? 'display: none;' : '' ?>">
                    <label class="block text-xs lg:text-sm text-[#F25996] font-bold mb-1.5 pl-1">Sub Category</label>
                    <select id="sub-category-select" onchange="handleSubCategoryChange()" class="block w-full bg-white border border-[#f48fb1] rounded-lg px-3 py-2.5 text-sm text-gray-700 font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f48fb1]">
                        <option value="">-- All Subcategories --</option>
                        <?php foreach ($catSubCategories as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= $selectedSubCategoryId == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            
                <div id="attributes-container">
                <?php if (!empty($filterableAttributes)): ?>
                    <hr class="border-pink-200 mb-4 lg:mb-5">
                    <div class="mb-4 lg:mb-5 flex justify-between items-center">
                        <h3 class="font-bold text-[#F25996] text-sm lg:text-base truncate mr-2">Refine By</h3>
                    </div>
                    
                    <?php foreach ($filterableAttributes as $attr): ?>
                        <?php if (isset($attributeValues[$attr['id']]) && !empty($attributeValues[$attr['id']])): ?>
                            <div class="mb-4 lg:mb-5">
                                <h4 class="font-bold text-[#F25996] mb-2 lg:mb-3 text-sm border-b border-[#f48fb1] pb-2 flex justify-between items-center cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                    <?= htmlspecialchars($attr['name']) ?>
                                    <svg class="w-4 h-4 text-[#f48fb1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </h4>
                                <div class="space-y-2 max-h-40 lg:max-h-48 overflow-y-auto pr-2">
                                    <?php foreach ($attributeValues[$attr['id']] as $val): ?>
                                        <?php 
                                             $isChecked = isset($attributeFilters[$attr['id']]) && in_array($val['id'], (array)$attributeFilters[$attr['id']]);
                                             $isColorAttr = ($attr['input_type'] ?? '') === 'colorpicker' || !empty($val['color_code']);
                                             $valColorHex = !empty($val['color_code']) ? $val['color_code'] : null;
                                        ?>
                                        <label class="flex items-center gap-2.5 cursor-pointer group">
                                            <div class="relative flex items-center justify-center">
                                                <input type="checkbox" class="attribute-filter appearance-none w-4 h-4 lg:w-5 lg:h-5 border-2 border-[#F25996] rounded bg-white checked:bg-[#f48fb1] checked:border-[#f48fb1] transition-colors cursor-pointer peer" name="attr[<?= $attr['id'] ?>][]" value="<?= $val['id'] ?>" <?= $isChecked ? 'checked' : '' ?> onchange="if(window.innerWidth >= 1024) applyFilters();">
                                                <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <?php if ($isColorAttr && $valColorHex): ?>
                                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shadow-xs shrink-0 inline-block" style="background-color: <?= htmlspecialchars($valColorHex) ?>"></span>
                                            <?php endif; ?>
                                            <span class="text-xs lg:text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors select-none"><?= htmlspecialchars($val['value']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>

                <hr class="border-pink-200 mb-4 lg:mb-5">
                
                <!-- Desktop Buttons -->
                <div class="hidden lg:flex flex-col gap-3">
                    <button onclick="applyFilters()" class="w-full text-white py-2.5 rounded-xl font-bold shadow-sm hover:opacity-90 transition-opacity" style="background:#F25996;">Apply Filters</button>
                    <a href="<?= BASE_URL ?>/catalog" class="w-full bg-transparent border border-[#F25996] text-[#F25996] py-2.5 rounded-xl font-bold hover:bg-pink-100/50 transition-colors text-center block">Reset Filters</a>
                </div>

                </div><!-- /filter content -->

                <!-- Mobile & Tablet Footer (Apply / Reset Buttons) -->
                <div class="p-4 border-t border-pink-200 lg:hidden shrink-0 flex gap-3">
                    <a href="<?= BASE_URL ?>/catalog" class="flex-1 bg-transparent border border-[#F25996] text-[#F25996] py-3 rounded-xl font-bold transition-colors text-center">Reset</a>
                    <button onclick="applyFilters()" class="flex-1 text-white py-3 rounded-xl font-bold shadow-sm hover:opacity-90 transition-opacity" style="background:#F25996;">Apply</button>
                </div>

            </div><!-- /filter-panel -->
        </div>

        <!-- Main Product Grid & Header -->
        <div class="w-full lg:flex-1 min-w-0">
            
            <!-- Top Controls -->
            <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                
                <!-- Results Count -->
                <div id="catalog-results-count" class="text-gray-600 text-xs sm:text-sm font-medium whitespace-nowrap hidden sm:block">
                    <?php
                    $pgFrom = min($totalProducts ?? count($products), (($currentPage ?? 1) - 1) * ($perPage ?? 24) + 1);
                    $pgTo   = min($totalProducts ?? count($products), ($currentPage ?? 1) * ($perPage ?? 24));
                    ?>
                    Showing <span class="font-bold text-gray-900"><?= $pgFrom ?>–<?= $pgTo ?></span>
                    of <span class="font-bold text-gray-900"><?= $totalProducts ?? count($products) ?></span> products
                </div>
                
                <!-- Search Box, Sort Dropdown & View Toggles -->
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto flex-wrap sm:flex-nowrap justify-between sm:justify-end">
                    
                    <!-- Search Box -->
                    <div class="relative flex-1 sm:w-56 md:w-72" id="search-box-container">
                        <form id="search-form" action="<?= BASE_URL ?>/catalog" method="GET" onsubmit="handleSearchSubmit(event)" class="relative m-0" autocomplete="off">
                            <?php if ($selectedCategoryId): ?><input type="hidden" name="category_id" value="<?= $selectedCategoryId ?>"><?php endif; ?>
                            <?php if ($selectedSubCategoryId): ?><input type="hidden" name="sub_category_id" value="<?= $selectedSubCategoryId ?>"><?php endif; ?>
                            <input id="search-input" type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($searchQuery ?? '') ?>" class="w-full pr-8 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-[#F25996] transition-all placeholder-gray-400" style="padding-left: 2.35rem !important;" autocomplete="off" spellcheck="false">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <!-- Clear Button -->
                            <button type="button" id="search-clear-btn" onclick="clearCatalogSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200/80 hover:bg-gray-300 text-gray-600 flex items-center justify-center text-[10px] transition-all cursor-pointer" title="Clear search">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>

                        <!-- Inline Search Options Dropdown -->
                        <div id="search-autocomplete-dropdown" class="hidden absolute right-0 w-[calc(100vw-2rem)] sm:w-80 md:w-96 max-w-[92vw] top-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 ring-1 ring-black/5 z-50 overflow-hidden text-left backdrop-blur-sm" style="max-height: 480px;">
                            <!-- Loading State -->
                            <div id="search-dropdown-loading" class="hidden py-4 px-3 text-center">
                                <div class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500">
                                    <svg class="w-4 h-4 animate-spin text-[#F25996]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Searching options...</span>
                                </div>
                            </div>

                            <!-- Scrollable Options List -->
                            <div id="search-dropdown-content" class="overflow-y-auto max-h-[360px] divide-y divide-gray-50">
                                <!-- Categories Section -->
                                <div id="search-dropdown-categories" class="p-2 hidden">
                                    <p class="px-2 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Categories</p>
                                    <div id="search-categories-items" class="space-y-0.5"></div>
                                </div>

                                <!-- Products Section -->
                                <div id="search-dropdown-products" class="p-2 hidden">
                                    <p class="px-2 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Products</p>
                                    <div id="search-products-items" class="space-y-1"></div>
                                </div>

                                <!-- Empty State -->
                                <div id="search-dropdown-empty" class="py-6 px-4 text-center hidden">
                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-xs text-gray-600 font-semibold">No matching options found</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Try searching with a different keyword</p>
                                </div>
                            </div>

                            <!-- Footer: Search All in Catalog -->
                            <div id="search-dropdown-footer" class="p-2.5 bg-gray-50/90 border-t border-gray-100 flex items-center justify-between text-xs cursor-pointer hover:bg-pink-50/60 transition-colors group" onclick="triggerFullSearchFromDropdown()">
                                <div class="flex items-center gap-2 text-gray-700 group-hover:text-[#F25996] font-medium truncate">
                                    <svg class="w-4 h-4 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <span class="truncate">Search catalog for "<strong id="search-query-preview" class="font-bold text-[#F25996]"></strong>"</span>
                                </div>
                                <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 bg-white border border-gray-200 rounded shadow-2xs shrink-0">↵ Enter</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Sort By Dropdown -->
                    <div class="relative shrink-0 min-w-[130px] sm:min-w-[145px]">
                        <select id="sort-select" onchange="applyFilters()" class="w-full bg-white border border-[#f48fb1] rounded-xl px-3 py-2 text-xs sm:text-sm text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-[#F25996] cursor-pointer shadow-xs">
                            <option value="newest" <?= ($currentSort ?? 'newest') === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
                            <option value="price_asc" <?= ($currentSort ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= ($currentSort ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>
                    
                    <!-- Grid / List View Toggles -->
                    <div class="flex items-center gap-1 border-l border-gray-200 pl-2 sm:pl-3 shrink-0">
                        <button id="btn-grid" onclick="setView('grid')" class="w-8 h-8 rounded-lg flex items-center justify-center text-white transition-colors shadow-xs" style="background:#F25996;" title="Grid View">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                        <button id="btn-list" onclick="setView('list')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors" title="List View">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div id="product-grid" class="w-full grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-4 lg:gap-5 items-start overflow-x-hidden">
                <?php if (empty($products)): ?>
                    <div class="col-span-full py-16 px-4 text-center bg-white rounded-2xl border border-gray-200 shadow-sm">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <h3 class="text-lg font-bold text-gray-900">No products found</h3>
                        <p class="text-gray-500 mt-1">Try adjusting your filters or search query.</p>
                        <a href="<?= BASE_URL ?>/catalog" class="inline-block mt-4 text-[#F25996] font-bold hover:underline">Clear all filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                        <?php
                            $img = !empty($product['primary_image']) ? (str_starts_with($product['primary_image'], 'http') ? $product['primary_image'] : (BASE_URL . '/' . ltrim($product['primary_image'], '/'))) : 'https://placehold.co/400?text=' . urlencode($product['name']);
                            $catGst = (float)($product['category_sgst'] ?? 0) + (float)($product['category_cgst'] ?? 0);
                            $productGst = $catGst > 0 ? $catGst : ((float)($product['variant_total_gst'] ?? 0) ?: (float)($product['total_gst'] ?? 0));

                            $basePrice = (isset($product['base_price']) && (float)$product['base_price'] > 0) ? (float)$product['base_price'] : (float)($product['wholesale_price'] ?? 0);
                            $discountPrice = !empty($product['discount_price']) && (float)$product['discount_price'] > 0 ? (float)$product['discount_price'] : null;
                            $effPrice = $discountPrice ?? $basePrice;

                            $displayPrice = $effPrice;

                            $displayOriginalPrice = null;
                            if ($discountPrice && $basePrice > $discountPrice) {
                                $displayOriginalPrice = $basePrice;
                            } elseif (!empty($product['wholesale_price']) && (float)$product['wholesale_price'] > $effPrice) {
                                $displayOriginalPrice = (float)$product['wholesale_price'];
                            }

                            $moq = !empty($product['moq_override']) ? $product['moq_override'] : ($product['category_moq'] ?? 1);
                        ?>
                        <div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-200 flex flex-col h-full relative group">

                            <!-- Floating Action Buttons (Wishlist) -->
                            <?php if (\Core\Session::get('user_id')): ?>
                            <?php 
                                $inWishlist = false;
                                if (!empty($wishlistIds) && is_array($wishlistIds)) {
                                    $inWishlist = in_array($product['id'], $wishlistIds);
                                } elseif (!empty($wishlistItems) && is_array($wishlistItems)) {
                                    $inWishlist = in_array($product['id'], array_column($wishlistItems, 'product_id')) || in_array($product['id'], array_column($wishlistItems, 'id'));
                                }
                            ?>
                            <div class="product-floating-actions absolute top-2 right-2 z-10 flex flex-col gap-1.5">
                                <button onclick="toggleWishlist(<?= $product['id'] ?>, this); event.preventDefault();" data-wishlist-btn-id="<?= $product['id'] ?>" class="wishlist-btn <?= $inWishlist ? 'is-active' : '' ?> w-6 h-6 sm:w-7 sm:h-7 hover:opacity-80 transition-opacity rounded-full flex items-center justify-center shadow-sm" style="background-color: <?= htmlspecialchars($wishlist_color) ?>;" title="Wishlist">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 <?= $inWishlist ? 'text-[#F25996] fill-[#F25996] is-active' : 'text-gray-400 fill-none' ?>" fill="<?= $inWishlist ? '#F25996' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                </button>
                            </div>
                            <?php endif; ?>

                            <!-- Product Image -->
                            <a href="<?= BASE_URL ?>/product?id=<?= $product['id'] ?>" class="product-image-wrap block w-full overflow-hidden shrink-0 relative">
                                <?php 
                                $discPercent = ($displayOriginalPrice && $displayOriginalPrice > $displayPrice) ? round((($displayOriginalPrice - $displayPrice) / $displayOriginalPrice) * 100) : 0;
                                if ($canAddToCart && $discPercent > 0 && function_exists('render_discount_starburst')) {
                                    echo render_discount_starburst($discPercent, 'transition-transform duration-300 group-hover:scale-105');
                                }
                                ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" decoding="async">
                            </a>

                            <!-- Product Details -->
                            <div class="product-card-body p-3.5 sm:p-4 md:p-5 flex flex-col flex-1 bg-white relative z-10">
                                <?php 
                                $cardBrand = !empty($product['vendor_store_name']) ? $product['vendor_store_name'] : (!empty($product['vendor_company_name']) ? $product['vendor_company_name'] : '');
                                ?>
                                <?php if (!empty($cardBrand)): ?>
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50/80 px-2 py-0.5 rounded border border-indigo-100/70 capitalize">
                                            <svg class="w-2.5 h-2.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <?= htmlspecialchars($cardBrand) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= BASE_URL ?>/product?id=<?= $product['id'] ?>" class="product-link block mb-1">
                                    <h3 class="product-name font-bold leading-snug line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                                </a>
                                
                                <?php if (!empty($product['sku'])): ?>
                                    <p class="font-medium mb-1.5 hidden md:block" style="font-size: 0.8125rem; color: <?= htmlspecialchars($subtitle_color) ?>;">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                                <?php endif; ?>

                                <?php if ($canAddToCart): ?>
                                    <div class="mt-auto">
                                        <!-- Selling Price, Strikethrough & Discount % Badge -->
                                        <div class="product-price-row flex items-baseline gap-1.5 flex-wrap mb-1">
                                            <span class="product-price font-extrabold tracking-tight">&#8377;<?= format_price($displayPrice) ?></span>
                                            <?php if ($displayOriginalPrice && $displayOriginalPrice > $displayPrice): ?>
                                                <span class="product-orig-price text-sm font-bold text-gray-400 line-through">&#8377;<?= format_price($displayOriginalPrice) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Minimum Order Count -->
                                        <p class="product-moq-row text-xs font-semibold mb-1.5 md:mb-4 flex items-center gap-1 whitespace-nowrap" style="color: var(--pc-price);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Min. Order: <?= htmlspecialchars($moq) ?> Pcs</span>
                                        </p>

                                        <!-- Rating Row (Shown in list view) -->
                                        <div class="product-rating-row flex items-center gap-1 mb-2 text-xs">
                                            <div class="flex items-center text-amber-500">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            </div>
                                            <span class="font-bold text-gray-800 text-[11px] md:text-xs">5.0</span>
                                            <span class="text-gray-400 text-[10px] md:text-xs">(1)</span>
                                        </div>

                                        <form action="<?= BASE_URL ?>/cart/add" method="POST" onsubmit="ajaxAddToCart(event, this)">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= htmlspecialchars($moq) ?>">
                                            <button type="submit" class="product-btn w-full font-bold transition-colors flex items-center justify-center gap-1.5 md:gap-2 hover:opacity-90 shadow-sm whitespace-nowrap" style="padding: clamp(0.45rem, 1.8vw, 1rem); font-size: clamp(0.75rem, 2.8vw, 1rem); border-radius: 0.75rem;">
                                                <svg class="w-3.5 h-3.5 md:w-6 md:h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span class="whitespace-nowrap">Add to Cart</span>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-auto pt-2 border-t border-gray-100">
                                        <a href="<?= BASE_URL ?>/login" class="flex items-center gap-1.5 text-[#F25996] font-semibold py-1 text-xs md:text-[11px] lg:text-xs hover:underline leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span class="truncate">Register to view price</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// ── Storefront Catalog Pagination ────────────────────────────────────────
$currentPage   = $currentPage   ?? 1;
$totalPages    = $totalPages    ?? 1;
$totalProducts = $totalProducts ?? count($products ?? []);
$perPage       = $perPage       ?? 24;

function catalogPageUrl(int $page): string {
    $base  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $qs    = $_GET;
    $qs['page'] = $page;
    return $base . '?' . http_build_query($qs);
}
?>
<div id="catalog-pagination-container" class="w-full">
<?php if ($totalPages > 1): ?>
<div class="w-full max-w-full mx-auto px-3 sm:px-6 lg:px-8 pb-10">
    <div class="flex items-center justify-center gap-2 flex-wrap pt-2">
        <?php if ($currentPage > 1): ?>
            <a href="<?= catalogPageUrl($currentPage - 1) ?>"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition shadow-xs">
                ← Prev
            </a>
        <?php endif; ?>
        <?php
            $cStart = max(1, $currentPage - 2);
            $cEnd   = min($totalPages, $currentPage + 2);
            if ($cStart > 1): ?>
                <a href="<?= catalogPageUrl(1) ?>" class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition text-sm font-bold shadow-xs">1</a>
                <?php if ($cStart > 2): ?><span class="text-gray-400 text-sm">…</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($pg = $cStart; $pg <= $cEnd; $pg++): ?>
            <a href="<?= catalogPageUrl($pg) ?>"
               class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-bold transition shadow-xs
                      <?= $pg === $currentPage
                          ? 'bg-[#F25996] text-white border border-[#F25996]'
                          : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50' ?>">
                <?= $pg ?>
            </a>
        <?php endfor; ?>
        <?php if ($cEnd < $totalPages): ?>
            <?php if ($cEnd < $totalPages - 1): ?><span class="text-gray-400 text-sm">…</span><?php endif; ?>
            <a href="<?= catalogPageUrl($totalPages) ?>" class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition text-sm font-bold shadow-xs"><?= $totalPages ?></a>
        <?php endif; ?>
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= catalogPageUrl($currentPage + 1) ?>"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition shadow-xs">
                Next →
            </a>
        <?php endif; ?>
    </div>
    <p class="text-center text-xs text-gray-400 mt-3 font-medium">
        Page <?= $currentPage ?> of <?= $totalPages ?> &nbsp;·&nbsp; <?= number_format($totalProducts) ?> total products
    </p>
</div>
<?php endif; ?>
</div>

<style>
/* ===== Hide List-specific elements in Grid View ===== */
#product-grid:not(.list-view) .product-rating-row {
    display: none !important;
}

/* ===== Desktop List View (min-width: 1024px) - 3 Products Per Row ===== */
@media (min-width: 1024px) {
    #product-grid.list-view { 
        display: grid !important; 
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important; 
        gap: 1.125rem !important; 
        width: 100% !important;
    }
    #product-grid.list-view .product-card { 
        flex-direction: row !important; 
        height: 100% !important; 
        min-height: 160px !important;
        align-items: stretch !important;
        border-radius: 1.25rem !important;
        width: 100% !important;
    }
    #product-grid.list-view .product-card .product-image-wrap { 
        width: 140px !important; 
        min-width: 140px !important; 
        max-width: 140px !important; 
        height: auto !important;
        min-height: 100% !important;
        aspect-ratio: auto !important; 
        flex-shrink: 0 !important; 
        align-self: stretch !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
        display: flex !important;
        overflow: hidden !important;
    }
    #product-grid.list-view .product-card .product-image-wrap img {
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
        object-fit: cover !important;
        object-position: center top !important;
        display: block !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
    }
    #product-grid.list-view .product-card .product-card-body { 
        flex: 1 1 auto !important; 
        padding: 0.875rem 1rem !important; 
        margin-top: 0 !important; 
        border-radius: 0 1.25rem 1.25rem 0 !important; 
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    #product-grid.list-view .product-card .product-floating-actions {
        top: 0.65rem !important;
        right: 0.65rem !important;
        left: auto !important;
    }
}

/* ===== Tablet List View (768px - 1023px) - 2 Products Per Row ===== */
@media (min-width: 768px) and (max-width: 1023px) {
    #product-grid.list-view { 
        display: grid !important; 
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important; 
        gap: 1rem !important; 
        width: 100% !important;
    }
    #product-grid.list-view .product-card { 
        flex-direction: row !important; 
        height: 100% !important; 
        min-height: 150px !important;
        align-items: stretch !important;
        border-radius: 1.25rem !important;
        width: 100% !important;
    }
    #product-grid.list-view .product-card .product-image-wrap { 
        width: 150px !important; 
        min-width: 150px !important; 
        max-width: 150px !important; 
        height: auto !important;
        min-height: 100% !important;
        aspect-ratio: auto !important; 
        flex-shrink: 0 !important; 
        align-self: stretch !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
        display: flex !important;
        overflow: hidden !important;
    }
    #product-grid.list-view .product-card .product-image-wrap img {
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
        object-fit: cover !important;
        object-position: center top !important;
        display: block !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
    }
    #product-grid.list-view .product-card .product-card-body { 
        flex: 1 1 auto !important; 
        padding: 1rem 1.125rem !important; 
        margin-top: 0 !important; 
        border-radius: 0 1.25rem 1.25rem 0 !important; 
    }
    #product-grid.list-view .product-card .product-floating-actions {
        top: 0.65rem !important;
        right: 0.65rem !important;
        left: auto !important;
    }
}

/* ===== Mobile List View (< 768px) matching vanaja git code ===== */
@media (max-width: 767px) {
    #product-grid.list-view {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
        width: 100% !important;
    }
    #product-grid.list-view .product-card {
        flex-direction: row !important;
        align-items: stretch !important;
        height: auto !important;
        min-height: 130px !important;
        max-height: none !important;
        border-radius: 1.25rem !important;
        background: #ffffff !important;
        border: 1px solid #eef0f4 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
        overflow: hidden !important;
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    #product-grid.list-view .product-card .product-image-wrap {
        width: 135px !important;
        min-width: 135px !important;
        max-width: 135px !important;
        align-self: stretch !important;
        height: auto !important;
        min-height: 100% !important;
        aspect-ratio: auto !important;
        flex-shrink: 0 !important;
        position: relative !important;
        overflow: hidden !important;
        background: transparent !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
        display: flex !important;
    }
    #product-grid.list-view .product-card .product-image-wrap img {
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
        object-fit: cover !important;
        object-position: center top !important;
        display: block !important;
        border-radius: 1.25rem 0 0 1.25rem !important;
    }
    /* Wishlist button placed on top-right of image thumbnail in mobile list view */
    #product-grid.list-view .product-card .product-floating-actions {
        top: 0.45rem !important;
        right: auto !important;
        left: calc(135px - 2.2rem) !important;
        z-index: 25 !important;
        display: flex !important;
    }
    #product-grid.list-view .product-card .wishlist-btn {
        width: 1.75rem !important;
        height: 1.75rem !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(4px) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
        border: 1px solid rgba(229, 231, 235, 0.8) !important;
        border-radius: 9999px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #product-grid.list-view .product-card .wishlist-btn svg {
        width: 0.85rem !important;
        height: 0.85rem !important;
    }
    /* Product card body in mobile list view */
    #product-grid.list-view .product-card .product-card-body {
        flex: 1 1 auto !important;
        padding: 0.75rem 0.85rem 0.75rem 0.75rem !important;
        margin-top: 0 !important;
        border-radius: 0 1.25rem 1.25rem 0 !important;
        background: #ffffff !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        min-width: 0 !important;
    }
    #product-grid.list-view .product-card .product-name {
        font-family: inherit !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #F25996 !important;
        line-height: 1.25 !important;
        margin-bottom: 0.35rem !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }
    #product-grid.list-view .product-card .product-price-row {
        display: flex !important;
        align-items: baseline !important;
        gap: 0.4rem !important;
        margin-bottom: 0.25rem !important;
        flex-wrap: wrap !important;
    }
    #product-grid.list-view .product-card .product-price {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #111827 !important;
        line-height: 1.2 !important;
    }
    #product-grid.list-view .product-card .product-orig-price {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #9ca3af !important;
        text-decoration: line-through !important;
    }
    #product-grid.list-view .product-card .product-badge-discount {
        display: inline-block !important;
        font-size: 0.65rem !important;
        font-weight: 800 !important;
        padding: 0.1rem 0.35rem !important;
        border-radius: 9999px !important;
        background-color: #fce7f3 !important;
        color: #F25996 !important;
        line-height: 1.2 !important;
    }
    #product-grid.list-view .product-card .product-moq-row {
        font-size: 0.65rem !important;
        font-weight: 600 !important;
        margin-bottom: 0.2rem !important;
        color: #6b7280 !important;
    }
    #product-grid.list-view .product-card .product-rating-row {
        display: flex !important;
        align-items: center !important;
        gap: 0.25rem !important;
        margin-bottom: 0.35rem !important;
    }
    #product-grid.list-view .product-card form {
        display: block !important;
        margin-top: 0.25rem !important;
        width: 100% !important;
    }
    #product-grid.list-view .product-card .product-btn {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.25rem !important;
        padding: 0.35rem 0.5rem !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        border-radius: 0.5rem !important;
        width: 100% !important;
        background-color: #F25996 !important;
        color: #ffffff !important;
        line-height: 1.2 !important;
    }
}
</style>

<script>
const ALL_SUBCATEGORIES = <?= json_encode($subCategories ?? []) ?>;

// On mobile/tablet: auto-open filter panel if open_filter flag is present
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('open_filter') && window.innerWidth < 1024) {
        toggleMobileFilter();
    }
    
    const saved = localStorage.getItem('catalogView');
    if (saved === 'list') setView('list');
});

function handleMainCategoryChange() {
    const mainCatId = document.getElementById('main-category-select').value;
    const subCatContainer = document.getElementById('sub-category-container');
    const subCatSelect = document.getElementById('sub-category-select');
    
    if (subCatSelect) {
        // Clear sub category dropdown
        subCatSelect.innerHTML = '<option value="">-- All Subcategories --</option>';
        
        // Clear attributes container
        const attrContainer = document.getElementById('attributes-container');
        if (attrContainer) {
            attrContainer.innerHTML = '';
        }

        if (mainCatId) {
            const relevantSubs = ALL_SUBCATEGORIES.filter(sub => sub.category_id == mainCatId);
            if (relevantSubs.length > 0) {
                relevantSubs.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    subCatSelect.appendChild(option);
                });
                if (subCatContainer) subCatContainer.style.display = 'block';
            } else {
                if (subCatContainer) subCatContainer.style.display = 'none';
            }
        } else {
            if (subCatContainer) subCatContainer.style.display = 'none';
        }
    }
    
    // Auto-reload on desktop only
    if (window.innerWidth >= 1024) {
        updateCategories();
    }
}

function handleSubCategoryChange() {
    const subCatId = document.getElementById('sub-category-select').value;
    const attrContainer = document.getElementById('attributes-container');
    
    // Auto-reload on desktop
    if (window.innerWidth >= 1024) {
        updateCategories();
        return;
    }
    
    // On mobile/tablet, fetch attributes dynamically
    if (subCatId && attrContainer) {
        attrContainer.innerHTML = '<div class="py-4 text-center text-sm text-gray-500">Loading attributes...</div>';
        fetch('<?= BASE_URL ?>/catalog/ajax-attributes?sub_category_id=' + subCatId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.attributes.length > 0) {
                    let html = '<hr class="border-gray-200 mb-4 md:mb-6"><div class="mb-4 md:mb-6 flex justify-between items-center"><h3 class="font-bold text-[#F25996] text-sm md:text-base truncate mr-2">Refine By</h3></div>';
                    
                    data.attributes.forEach(attr => {
                        if (data.values[attr.id] && data.values[attr.id].length > 0) {
                            html += '<div class="mb-4 md:mb-6">';
                            html += '<h4 class="font-bold text-gray-700 mb-2 md:mb-3 text-sm">' + escapeHtml(attr.name) + '</h4>';
                            html += '<div class="space-y-2 max-h-40 md:max-h-48 overflow-y-auto pr-2">';
                            
                            data.values[attr.id].forEach(val => {
                                html += `
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" class="attribute-filter appearance-none w-4 h-4 md:w-5 md:h-5 border-2 border-gray-300 rounded bg-white checked:bg-[#F25996] checked:border-[#F25996] transition-colors cursor-pointer peer" name="attr[${attr.id}][]" value="${val.id}" onchange="if(window.innerWidth >= 768) applyFilters();">
                                        <svg class="w-3 h-3 md:w-3.5 md:h-3.5 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-xs md:text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors select-none">${escapeHtml(val.value)}</span>
                                </label>`;
                            });
                            
                            html += '</div></div>';
                        }
                    });
                    
                    attrContainer.innerHTML = html;
                } else {
                    attrContainer.innerHTML = '';
                }
            })
            .catch(err => {
                console.error(err);
                attrContainer.innerHTML = '';
            });
    } else if (attrContainer) {
        attrContainer.innerHTML = '';
    }
}

function escapeHtml(unsafe) {
    return (unsafe || '').toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function applyFilters() {
    const url = new URL(window.location.href);
    
    // Clear existing attr and sort params
    const keysToDelete = [];
    for (let key of url.searchParams.keys()) {
        if (key.startsWith('attr[')) keysToDelete.push(key);
    }
    keysToDelete.forEach(k => url.searchParams.delete(k));
    
    // Set sort (Removed from UI, default to newest or keep existing)
    const sortEl = document.getElementById('sort-select');
    if (sortEl) {
        url.searchParams.set('sort', sortEl.value);
    } else {
        url.searchParams.set('sort', 'newest');
    }
    
    // Preserve search query
    const searchEl = document.getElementById('search-input');
    if (searchEl && searchEl.value.trim()) {
        url.searchParams.set('q', searchEl.value.trim());
    } else {
        url.searchParams.delete('q');
    }
    
    // Add category selects for mobile workflow
    const mainCatEl = document.getElementById('main-category-select');
    if (mainCatEl) {
        if (mainCatEl.value) {
            url.searchParams.set('category_id', mainCatEl.value);
        } else {
            url.searchParams.delete('category_id');
            url.searchParams.delete('sub_category_id'); // Clear sub if main is cleared
        }
    }

    const subCatEl = document.getElementById('sub-category-select');
    if (subCatEl && mainCatEl && mainCatEl.value) {
        if (subCatEl.value) {
            url.searchParams.set('sub_category_id', subCatEl.value);
        } else {
            url.searchParams.delete('sub_category_id');
        }
    }
    
    // Append checked boxes
    document.querySelectorAll('.attribute-filter:checked').forEach(cb => {
        url.searchParams.append(cb.name, cb.value);
    });
    
    url.searchParams.delete('open_filter'); // Remove the auto-open flag on actual apply
    
    window.location.href = url.toString();
}

function updateCategories() {
    const url = new URL(window.location.href);
    const mainCatEl = document.getElementById('main-category-select');
    const subCatEl = document.getElementById('sub-category-select');
    
    if (mainCatEl && mainCatEl.value) {
        url.searchParams.set('category_id', mainCatEl.value);
        if (subCatEl && subCatEl.value) {
            url.searchParams.set('sub_category_id', subCatEl.value);
        } else {
            url.searchParams.delete('sub_category_id');
        }
    } else {
        url.searchParams.delete('category_id');
        url.searchParams.delete('sub_category_id');
    }
    
    if (window.innerWidth < 1024) {
        url.searchParams.set('open_filter', '1');
    }
    
    window.location.href = url.toString();
}

/* ─────────────────────────────────────────────────────────
   Inline Live Search & Autocomplete Options Dropdown
───────────────────────────────────────────────────────── */
let searchDebounceTimer = null;
let apiSearchDebounceTimer = null;
let currentHighlightedIndex = -1;

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function highlightMatch(text, query) {
    if (!text) return '';
    if (!query) return escapeHtml(text);
    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedQuery})`, 'gi');
    return escapeHtml(text).replace(regex, '<mark class="bg-pink-100 text-[#F25996] font-bold p-0 rounded-xs">$1</mark>');
}

function hideSearchDropdown() {
    const dropdown = document.getElementById('search-autocomplete-dropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    currentHighlightedIndex = -1;
}

function clearCatalogSearch() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    if (clearBtn) clearBtn.classList.add('hidden');
    hideSearchDropdown();
    filterProductCardsClientSide('');
    fetchCatalogGrid('');
}

function triggerFullSearchFromDropdown() {
    const searchInput = document.getElementById('search-input');
    hideSearchDropdown();
    const q = searchInput ? searchInput.value.trim() : '';
    clearTimeout(searchDebounceTimer);
    clearTimeout(apiSearchDebounceTimer);
    fetchCatalogGrid(q);
}

function handleSearchSubmit(e) {
    if (e) e.preventDefault();
    hideSearchDropdown();
    const searchInput = document.getElementById('search-input');
    const q = searchInput ? searchInput.value.trim() : '';
    clearTimeout(searchDebounceTimer);
    clearTimeout(apiSearchDebounceTimer);
    fetchCatalogGrid(q);
}

function filterProductCardsClientSide(query) {
    const grid = document.getElementById('product-grid');
    if (!grid) return;

    const cards = grid.querySelectorAll('.product-card');
    const qLower = query.toLowerCase().trim();
    let visibleCount = 0;

    cards.forEach(card => {
        const nameEl = card.querySelector('.product-name');
        const nameText = nameEl ? nameEl.textContent.toLowerCase() : '';
        const bodyText = card.textContent.toLowerCase();

        if (!qLower || nameText.includes(qLower) || bodyText.includes(qLower)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Handle empty state message for client-side search
    let emptyMsg = document.getElementById('client-search-empty-msg');
    if (visibleCount === 0 && qLower && cards.length > 0) {
        if (!emptyMsg) {
            emptyMsg = document.createElement('div');
            emptyMsg.id = 'client-search-empty-msg';
            emptyMsg.className = 'col-span-full py-12 px-4 text-center bg-white rounded-2xl border border-gray-200 shadow-sm';
            grid.appendChild(emptyMsg);
        }
        emptyMsg.innerHTML = `
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <h3 class="text-base font-bold text-gray-900">No matching products found</h3>
            <p class="text-xs text-gray-500 mt-1">No products on this page match "<strong class="text-gray-700">${escapeHtml(query)}</strong>"</p>
            <button type="button" onclick="clearCatalogSearch()" class="inline-block mt-3 px-3 py-1.5 text-xs text-[#F25996] font-bold hover:underline bg-pink-50 rounded-lg">Clear search</button>
        `;
        emptyMsg.style.display = '';
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }

    // Update count display
    const countEl = document.getElementById('catalog-results-count');
    if (countEl && qLower) {
        countEl.innerHTML = `Showing <span class="font-bold text-gray-900">${visibleCount}</span> matching products`;
    }
}

function fetchCatalogGrid(query) {
    const url = new URL(window.location.href);
    if (query) {
        url.searchParams.set('q', query);
        url.searchParams.delete('page'); // Reset to page 1 on search
    } else {
        url.searchParams.delete('q');
    }

    // Update browser URL history without reloading
    window.history.replaceState({}, '', url.toString());

    fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Update product grid
        const newGrid = doc.getElementById('product-grid');
        const currentGrid = document.getElementById('product-grid');
        if (newGrid && currentGrid) {
            currentGrid.innerHTML = newGrid.innerHTML;
            if (localStorage.getItem('catalogView') === 'list') {
                setView('list');
            }
        }

        // Update count display
        const newCount = doc.getElementById('catalog-results-count');
        const currentCount = document.getElementById('catalog-results-count');
        if (newCount && currentCount) {
            currentCount.innerHTML = newCount.innerHTML;
        }

        // Update pagination
        const newPagination = doc.getElementById('catalog-pagination-container');
        const currentPagination = document.getElementById('catalog-pagination-container');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        } else if (currentPagination && !newPagination) {
            currentPagination.innerHTML = '';
        }
    })
    .catch(err => {
        console.error("Catalog live search error:", err);
    });
}

function fetchSearchDropdownOptions(query) {
    const dropdown = document.getElementById('search-autocomplete-dropdown');
    const loading = document.getElementById('search-dropdown-loading');
    const catSection = document.getElementById('search-dropdown-categories');
    const catItems = document.getElementById('search-categories-items');
    const prodSection = document.getElementById('search-dropdown-products');
    const prodItems = document.getElementById('search-products-items');
    const emptyState = document.getElementById('search-dropdown-empty');
    const queryPreview = document.getElementById('search-query-preview');

    if (!dropdown) return;

    dropdown.classList.remove('hidden');
    if (queryPreview) queryPreview.textContent = query;
    if (loading) loading.classList.remove('hidden');
    if (emptyState) emptyState.classList.add('hidden');
    if (catSection) catSection.classList.add('hidden');
    if (prodSection) prodSection.classList.add('hidden');

    fetch(`<?= BASE_URL ?>/api/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (loading) loading.classList.add('hidden');
            let hasResults = false;
            currentHighlightedIndex = -1;

            // Categories
            if (data.categories && data.categories.length > 0) {
                hasResults = true;
                catSection.classList.remove('hidden');
                catItems.innerHTML = data.categories.map(cat => `
                    <a href="<?= BASE_URL ?>/catalog?category_id=${cat.id}" class="search-option-item flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:bg-pink-50 hover:text-[#F25996] transition-colors group">
                        <span class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-pink-50 flex items-center justify-center text-[#F25996] group-hover:bg-pink-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            </span>
                            <span>${highlightMatch(cat.name, query)}</span>
                        </span>
                        <span class="text-[10px] text-gray-400 group-hover:text-pink-400">Category</span>
                    </a>
                `).join('');
            }

            // Products
            if (data.products && data.products.length > 0) {
                hasResults = true;
                prodSection.classList.remove('hidden');
                prodItems.innerHTML = data.products.slice(0, 6).map(p => {
                    const img = p.primary_image || ('https://placehold.co/80?text=' + encodeURIComponent(p.name));
                    const priceHtml = p.display_price ? `<span class="text-xs font-bold text-[#F25996]">₹${parseFloat(p.display_price).toLocaleString()}</span>` : '';
                    return `
                        <a href="<?= BASE_URL ?>/product?id=${p.id}" class="search-option-item flex items-center gap-3 p-2 rounded-xl hover:bg-pink-50/70 transition-colors group">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-100 shrink-0">
                                <img src="${img}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-gray-900 group-hover:text-[#F25996] transition-colors truncate">
                                    ${highlightMatch(p.name, query)}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    ${p.category_name ? `<span class="text-[10px] text-gray-400 font-medium truncate">${escapeHtml(p.category_name)}</span>` : ''}
                                    ${priceHtml}
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-[#F25996] group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    `;
                }).join('');
            }

            if (!hasResults) {
                emptyState.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error("API search error:", err);
            if (loading) loading.classList.add('hidden');
        });
}

function highlightDropdownItem(items, index) {
    const footer = document.getElementById('search-dropdown-footer');
    items.forEach((item, idx) => {
        if (idx === index) {
            item.classList.add('bg-pink-50', 'ring-1', 'ring-[#F25996]/30');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('bg-pink-50', 'ring-1', 'ring-[#F25996]/30');
        }
    });

    if (footer) {
        if (index === items.length) {
            footer.classList.add('bg-pink-100/70');
            footer.scrollIntoView({ block: 'nearest' });
        } else {
            footer.classList.remove('bg-pink-100/70');
        }
    }
}

function initCatalogInlineSearch() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    if (!searchInput) return;

    if (searchInput.value.trim()) {
        if (clearBtn) clearBtn.classList.remove('hidden');
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        // Toggle clear button
        if (clearBtn) {
            if (query) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        // 1. Instant client-side DOM filter on current product cards
        filterProductCardsClientSide(query);

        // 2. Debounced API search for autocomplete dropdown options
        clearTimeout(apiSearchDebounceTimer);
        if (query.length >= 1) {
            apiSearchDebounceTimer = setTimeout(() => {
                fetchSearchDropdownOptions(query);
            }, 200);
        } else {
            hideSearchDropdown();
        }

        // 3. Debounced AJAX fetch for complete database catalog grid sync
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchCatalogGrid(query);
        }, 400);
    });

    searchInput.addEventListener('keydown', function(e) {
        const dropdown = document.getElementById('search-autocomplete-dropdown');
        const isDropdownOpen = dropdown && !dropdown.classList.contains('hidden');

        if (!isDropdownOpen) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearchSubmit(e);
            }
            return;
        }

        const items = dropdown.querySelectorAll('.search-option-item');
        const totalNavItems = items.length + 1; // items + footer

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentHighlightedIndex = (currentHighlightedIndex + 1) % totalNavItems;
            highlightDropdownItem(items, currentHighlightedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentHighlightedIndex = (currentHighlightedIndex - 1 + totalNavItems) % totalNavItems;
            highlightDropdownItem(items, currentHighlightedIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentHighlightedIndex >= 0 && currentHighlightedIndex < items.length) {
                items[currentHighlightedIndex].click();
            } else {
                hideSearchDropdown();
                handleSearchSubmit(e);
            }
        } else if (e.key === 'Escape') {
            hideSearchDropdown();
        }
    });

    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 1) {
            fetchSearchDropdownOptions(query);
        }
    });

    document.addEventListener('click', function(e) {
        const container = document.getElementById('search-box-container');
        if (container && !container.contains(e.target)) {
            hideSearchDropdown();
        }
    });

    window.addEventListener('popstate', function() {
        const url = new URL(window.location.href);
        const q = url.searchParams.get('q') || '';
        if (searchInput) {
            searchInput.value = q;
            if (clearBtn) {
                if (q) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }
        }
        fetchCatalogGrid(q);
    });
}

function setView(mode) {
    const grid = document.getElementById('product-grid');
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');
    const gridClasses = ['grid', 'grid-cols-2', 'sm:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-3', 'xl:grid-cols-4'];
    if (mode === 'list') {
        grid.classList.add('list-view');
        grid.classList.remove(...gridClasses);
        btnList.style.background = '#F25996';
        btnList.classList.add('text-white');
        btnList.classList.remove('text-gray-400', 'hover:bg-gray-100');
        btnGrid.style.background = '';
        btnGrid.classList.remove('text-white');
        btnGrid.classList.add('text-gray-400', 'hover:bg-gray-100');
        localStorage.setItem('catalogView', 'list');
    } else {
        grid.classList.remove('list-view');
        grid.classList.add(...gridClasses);
        btnGrid.style.background = '#F25996';
        btnGrid.classList.add('text-white');
        btnGrid.classList.remove('text-gray-400', 'hover:bg-gray-100');
        btnList.style.background = '';
        btnList.classList.remove('text-white');
        btnList.classList.add('text-gray-400', 'hover:bg-gray-100');
        localStorage.setItem('catalogView', 'grid');
    }
}




function toggleMobileFilter() {
    const panel = document.getElementById('filter-panel');
    const backdrop = document.getElementById('mobile-filter-backdrop');
    
    // Toggle classes for sliding drawer effect
    if (panel.classList.contains('-translate-x-full')) {
        panel.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    } else {
        panel.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/* ─────────────────────────────────────────────────────────
   Custom Pink Select Component for Catalog Page (Image 1 UI)
   Applies to #sort-select, #main-category-select, #sub-category-select
───────────────────────────────────────────────────────── */
function initCatCustomSelect(sel) {
    if (!sel || sel.dataset.catCsInit) return;
    sel.dataset.catCsInit = '1';
    sel.style.display = 'none';

    var wrapper = document.createElement('div');
    wrapper.className = 'cat-cs-wrapper';
    if (sel.classList.contains('hidden') || (sel.parentElement && sel.parentElement.style.display === 'none')) {
        wrapper.classList.add('hidden');
    }
    sel.parentNode.insertBefore(wrapper, sel);
    wrapper.appendChild(sel);

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'cat-cs-trigger';

    var label = document.createElement('span');
    label.className = 'cat-cs-label truncate';
    var selectedOpt = sel.options[sel.selectedIndex] || sel.options[0];
    label.textContent = selectedOpt ? selectedOpt.text : 'Select';
    trigger.appendChild(label);

    var arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    arrow.setAttribute('class', 'cat-cs-arrow');
    arrow.setAttribute('viewBox', '0 0 20 20');
    arrow.setAttribute('fill', 'none');
    arrow.setAttribute('stroke', 'currentColor');
    arrow.setAttribute('stroke-width', '2');
    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('stroke-linecap', 'round');
    path.setAttribute('stroke-linejoin', 'round');
    path.setAttribute('d', 'M5 7l5 5 5-5');
    arrow.appendChild(path);
    trigger.appendChild(arrow);
    wrapper.appendChild(trigger);

    var list = document.createElement('div');
    list.className = 'cat-cs-list';
    wrapper.appendChild(list);

    function buildOptions() {
        list.innerHTML = '';
        Array.from(sel.options).forEach(function(opt) {
            var item = document.createElement('div');
            item.className = 'cat-cs-option' + (opt.selected ? ' selected' : '');
            item.textContent = opt.text;
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                sel.value = opt.value;
                label.textContent = opt.text;
                list.querySelectorAll('.cat-cs-option').forEach(function(o) { o.classList.remove('selected'); });
                item.classList.add('selected');
                closeList();
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            });
            list.appendChild(item);
        });
    }

    function syncTrigger() {
        var cur = sel.options[sel.selectedIndex] || sel.options[0];
        if (cur) {
            label.textContent = cur.text;
        }
    }
    syncTrigger();
    buildOptions();

    function openList() {
        document.querySelectorAll('.cat-cs-trigger.open').forEach(function(t) {
            if (t !== trigger) {
                t.classList.remove('open');
                var sib = t.parentElement && t.parentElement.querySelector('.cat-cs-list');
                if (sib) sib.classList.remove('open');
            }
        });
        trigger.classList.add('open');
        list.classList.add('open');
    }
    function closeList() {
        trigger.classList.remove('open');
        list.classList.remove('open');
    }

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        list.classList.contains('open') ? closeList() : openList();
    });

    // Observe changes to options or style/class
    new MutationObserver(function() {
        if (sel.classList.contains('hidden') || (sel.parentElement && sel.parentElement.style.display === 'none')) {
            wrapper.classList.add('hidden');
        } else {
            wrapper.classList.remove('hidden');
        }
        buildOptions();
        syncTrigger();
    }).observe(sel, { attributes: true, attributeFilter: ['class', 'style'], childList: true, subtree: true });

    sel.addEventListener('change', function() {
        syncTrigger();
        buildOptions();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCatalogInlineSearch();
    initCatCustomSelect(document.getElementById('sort-select'));
    initCatCustomSelect(document.getElementById('main-category-select'));
    initCatCustomSelect(document.getElementById('sub-category-select'));

    document.addEventListener('click', function() {
        document.querySelectorAll('.cat-cs-trigger.open').forEach(function(t) { t.classList.remove('open'); });
        document.querySelectorAll('.cat-cs-list.open').forEach(function(l) { l.classList.remove('open'); });
    });
});
</script>
