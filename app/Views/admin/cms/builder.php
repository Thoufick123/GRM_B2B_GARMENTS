
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

.builder-container {
    font-family: 'Inter', sans-serif;
    width: 100%;
}
.font-serif {
    font-family: 'Playfair Display', serif;
}
.bg-brown { background-color: #8c6a4f; }
.hover\:bg-brown-dark:hover { background-color: #755740; }
.text-brown { color: #8c6a4f; }
.shadow-custom { box-shadow: 0 8px 30px rgba(0,0,0,0.04); }

/* Fixes for Tailwind arbitrary values missing in compiled css */
.builder-card {
    height: calc(100vh - 10rem);
    min-height: 600px;
}
.browser-mockup {
    border-radius: 1.5rem;
}

/* Custom Scrollbar for list */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af; 
}
</style>

<?php
$allItems = [];
if (!empty($activeComponents)) {
    foreach($activeComponents as $cmp) {
        $cmp['is_active'] = (bool)$cmp['is_active'];
        $allItems[] = $cmp;
    }
}
if (!empty($inactiveComponents)) {
    foreach($inactiveComponents as $cmp) {
        $cmp['is_active'] = (bool)$cmp['is_active'];
        $allItems[] = $cmp;
    }
}
?>

<div class="builder-container">
    <div class="max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 h-full">
        
        <!-- Sidebar: Layout Builder -->
        <div class="lg:col-span-5 xl:col-span-4">
            <div class="bg-white p-6 shadow-custom flex flex-col builder-card rounded-2xl border border-gray-100">
                <h2 class="text-2xl font-serif text-gray-800 mb-2">Layout Builder</h2>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed pr-4">Drag and drop to reorder. Toggle visibility for the customer website.</p>
                
                <!-- Sortable List -->
                <div class="flex-1 overflow-y-auto pr-2 -mr-2 space-y-4 custom-scrollbar" id="layoutList">
                    <?php if(empty($allItems)): ?>
                        <p class="text-center text-gray-400 py-8 text-sm italic">No sections available.</p>
                    <?php else: ?>
                        <?php foreach($allItems as $cmp): 
                            $label = str_replace('_', ' ', $cmp['section_type']);
                            $isHeroBanner = $cmp['section_type'] === 'hero_banner';
                            $isMainBanner = $cmp['section_type'] === 'main_banner';
                            $isProductCarousel = $cmp['section_type'] === 'product_carousel';
                            $isCategoryCarousel = $cmp['section_type'] === 'category_carousel';
                            $isCategoriesGrid = $cmp['section_type'] === 'categories_grid';
                            $isNavbar = $cmp['section_type'] === 'navbar';
                            $isTopBar = $cmp['section_type'] === 'top_bar';
                            $isFooter = $cmp['section_type'] === 'footer';
                            $isAboutUs = $cmp['section_type'] === 'about_us';
                            $isReviews = $cmp['section_type'] === 'reviews';
                            $isReelsContainer = $cmp['section_type'] === 'reels_container';

                            if ($isHeroBanner) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Banner';
                                $sublabel = 'Hero Banner';
                            } elseif ($isMainBanner) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Main Banner';
                                $sublabel = 'Main Banner';
                            } elseif ($isReelsContainer) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Reels Container';
                                $sublabel = 'Reels Container';
                            } elseif ($isProductCarousel) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Product Carousel';
                                $sublabel = 'Product Carousel';
                            } elseif ($isCategoryCarousel) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Category Carousel';
                                $sublabel = 'Category Carousel';
                            } elseif ($isCategoriesGrid) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Categories Grid';
                                $sublabel = 'Categories Grid';
                            } elseif ($isReviews) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Review Section';
                                $sublabel = 'Customer Reviews';
                            } elseif ($isNavbar) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Navbar';
                                $sublabel = 'Navbar Configuration';
                            } elseif ($isTopBar) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Top Bar';
                                $sublabel = 'Top Announcement Bar';
                            } elseif ($isFooter) {
                                $label = !empty($cmp['content']['title']) ? $cmp['content']['title'] : 'Untitled Footer';
                                $sublabel = 'Footer Configuration';
                            } elseif ($isAboutUs) {
                                $label = !empty($cmp['content']['internal_name']) ? $cmp['content']['internal_name'] : 'Untitled About Us';
                                $sublabel = 'About Us Configuration';
                            } elseif (!empty($cmp['content']['label'])) {
                                $sublabel = $cmp['content']['label'];
                            }
                        ?>
                            <div class="bg-white border <?= $cmp['is_active'] ? 'border-gray-200' : 'border-gray-100 opacity-60' ?> rounded-xl p-5 shadow-sm flex items-center justify-between group transition-all duration-200 layout-item" data-id="<?= htmlspecialchars($cmp['id']) ?>" data-active="<?= $cmp['is_active'] ? '1' : '0' ?>">
                                <div class="flex items-center gap-4 overflow-hidden">
                                    <!-- Drag Handle -->
                                    <div class="text-gray-300 group-hover:text-gray-500 cursor-grab drag-handle flex-shrink-0">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6-12a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
                                    </div>
                                    <!-- Icon & Title -->
                                    <div class="flex items-center gap-2.5 truncate pr-2">
                                        <?php
                                            $iconColor = 'text-gray-400';
                                            if ($isHeroBanner) $iconColor = 'text-blue-500';
                                            elseif ($isMainBanner) $iconColor = 'text-purple-500';
                                            elseif ($isProductCarousel) $iconColor = 'text-brand-600';
                                            elseif ($isCategoryCarousel) $iconColor = 'text-orange-500';
                                            elseif ($isCategoriesGrid) $iconColor = 'text-pink-500';
                                            elseif ($isReviews) $iconColor = 'text-amber-500';
                                            elseif ($isNavbar) $iconColor = 'text-gray-700';
                                            elseif ($isTopBar) $iconColor = 'text-emerald-600';
                                            elseif ($isFooter) $iconColor = 'text-gray-700';
                                            elseif ($isAboutUs) $iconColor = 'text-green-600';
                                            elseif ($isReelsContainer) $iconColor = 'text-[#F25996]';
                                        ?>
                                        <div class="<?= $iconColor ?> flex-shrink-0">
                                            <?php if($isReelsContainer): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <?php elseif($isHeroBanner): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <?php elseif($isMainBanner): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            <?php elseif($isProductCarousel): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                            <?php elseif($isCategoryCarousel): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                            <?php elseif($isCategoriesGrid): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                            <?php elseif($isNavbar): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                            <?php elseif($isTopBar): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                            <?php elseif($isFooter): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16h16M4 20h16"></path></svg>
                                            <?php elseif($isAboutUs): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <?php elseif($isReviews): ?>
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="truncate">
                                            <h4 class="font-semibold text-gray-800 text-[13px] capitalize truncate"><?= htmlspecialchars($label) ?></h4>
                                            <?php if($sublabel): ?>
                                                <p class="text-[10px] text-gray-400 truncate"><?= htmlspecialchars($sublabel) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Actions: Edit + Delete + Eye toggle -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <?php if ($isHeroBanner && $this->hasPermission('cms_hero_banners', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/hero-banners/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Hero Banner">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isMainBanner && $this->hasPermission('cms_main_banners', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/main-banners/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Main Banner">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isProductCarousel && $this->hasPermission('cms_product_carousels', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/product-carousels/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Product Carousel">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isCategoryCarousel && $this->hasPermission('cms_category_carousels', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/category-carousels/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Category Carousel">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isCategoriesGrid && $this->hasPermission('cms_categories_grid', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/categories-grid/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Categories Grid">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isNavbar && $this->hasPermission('cms_navbars', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/navbars/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Navbar">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isTopBar && $this->hasPermission('cms_topbars', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/topbars/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Top Bar">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isFooter && $this->hasPermission('cms_footers', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/footers/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Footer">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isReviews && $this->hasPermission('cms_reviews', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/reviews/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Review Section">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isAboutUs && $this->hasPermission('cms_about_us', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/about-us/edit?id=<?= $cmp['id'] ?>&ref=builder" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit About Us">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php elseif ($isReelsContainer && $this->hasPermission('cms_reels', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/reels/edit?id=<?= $cmp['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Reels Container">
                                            <i class="bi bi-pencil-square text-base"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($this->hasPermission('cms_builder', 'delete') || $this->hasPermission('cms_builder', 'edit')): ?>
                                    <!-- Delete button -->
                                    <button class="delete-component-btn p-1.5 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Delete Component" data-id="<?= htmlspecialchars($cmp['id']) ?>" data-label="<?= htmlspecialchars($label) ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($this->hasPermission('cms_builder', 'edit')): ?>
                                    <button class="toggle-visibility-btn p-1.5 rounded-md hover:bg-gray-50 transition-colors <?= $cmp['is_active'] ? 'text-green-500' : 'text-gray-400' ?>" title="Toggle Visibility">
                                        <?php if($cmp['is_active']): ?>
                                            <!-- Open Eye -->
                                            <svg class="w-5 h-5 eye-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <?php else: ?>
                                            <!-- Closed Eye -->
                                            <svg class="w-5 h-5 eye-icon-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.51-2.793M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 9.88a3 3 0 014.24 4.24M10.73 5.08A10.43 10.43 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411"></path></svg>
                                        <?php endif; ?>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($this->hasPermission('cms_builder', 'edit')): ?>
                <!-- Buttons -->
                <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col gap-3">
                    <div class="flex gap-3">
                        <button id="saveDraftBtn" class="flex-1 py-3 rounded-full font-bold text-white transition-all bg-[#E8437D] hover:bg-[#d8356f] text-sm shadow-sm active:scale-95">Save Draft</button>
                        <button id="saveLayoutBtn" class="flex-1 py-3 rounded-full font-bold text-white transition-all bg-[#E8437D] hover:bg-[#d8356f] text-sm flex items-center justify-center gap-2 shadow-sm active:scale-95">Publish</button>
                    </div>
                    <button id="resetBtn" class="w-full py-2.5 rounded-full border border-pink-200 font-bold text-[#E8437D] bg-white hover:bg-pink-50 transition-colors text-[11px] tracking-wider uppercase">RESET TO PUBLISHED</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Live Preview Area -->
        <div class="lg:col-span-7 xl:col-span-8">
            <div class="bg-white p-6 shadow-custom flex flex-col builder-card rounded-2xl border border-gray-100">
                <div class="flex justify-between items-center mb-5 px-2">
                    <h2 class="text-2xl font-serif text-gray-800">Live Preview</h2>
                    <!-- Device Toggles -->
                    <div class="flex items-center gap-1.5 bg-gray-50 p-1 rounded-xl border border-gray-100">
                        <button class="p-2 bg-white rounded-lg shadow-sm text-gray-800 transition-colors" id="viewDesktop" title="Desktop View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </button>
                        <button class="p-2 text-gray-400 hover:text-gray-800 transition-colors" id="viewTablet" title="Tablet View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </button>
                        <button class="p-2 text-gray-400 hover:text-gray-800 transition-colors" id="viewMobile" title="Mobile View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>
                
                <!-- Browser Mockup -->
                <div class="flex-1 border border-gray-200 overflow-hidden flex flex-col bg-gray-50 shadow-inner w-full mx-auto transition-all duration-300 browser-mockup" id="previewContainer">
                    <!-- Browser Header -->
                    <div class="bg-white border-b border-gray-100 px-5 py-3 flex items-center gap-4">
                        <div class="flex gap-2 w-16">
                            <div class="w-3 h-3 rounded-full bg-[#FF5F56]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#FFBD2E]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#27C93F]"></div>
                        </div>
                        <div class="flex-1 flex justify-center">
                            <div class="bg-gray-50 rounded-lg text-center text-[11px] text-gray-400 py-1.5 px-6 font-medium border border-gray-100 w-full max-w-md truncate">
                                preview.<?= parse_url(BASE_URL, PHP_URL_HOST) ?? 'yourstore.com' ?>
                            </div>
                        </div>
                        <div class="w-16"></div> <!-- Spacer for balance -->
                    </div>
                    <!-- Iframe -->
                    <div class="flex-1 relative bg-white overflow-hidden h-full">
                        <?php 
                            $allIds = array_column(array_merge($activeComponents ?? [], $inactiveComponents ?? []), 'id');
                            $layoutParam = implode(',', $allIds);
                        ?>
                        <iframe id="previewIframe" src="<?= BASE_URL ?>/?preview=1&layout=<?= htmlspecialchars($layoutParam) ?>" class="absolute inset-0 w-full h-full border-0" title="Live Preview"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include SortableJS for Drag and Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const layoutList = document.getElementById('layoutList');
    
    // Initialize Sortable
    const canEditLayout = <?= ($this->hasPermission('cms_builder', 'edit')) ? 'true' : 'false' ?>;
    if (layoutList && canEditLayout) {
        new Sortable(layoutList, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                // Send reorder message to live preview iframe
                const items = Array.from(layoutList.querySelectorAll('.layout-item'));
                const order = items.map(item => item.getAttribute('data-id'));
                const iframe = document.getElementById('previewIframe');
                if (iframe && iframe.contentWindow) {
                    iframe.contentWindow.postMessage({ action: 'reorder', order: order }, '*');
                }
            }
        });
    }

    // Handle Visibility Toggle
    const svgOpenEye = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    const svgClosedEye = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.51-2.793M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 9.88a3 3 0 014.24 4.24M10.73 5.08A10.43 10.43 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411"></path>';

    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.toggle-visibility-btn');
        if (toggleBtn) {
            const item = toggleBtn.closest('.layout-item');
            const isActive = item.getAttribute('data-active') === '1';
            const svg = toggleBtn.querySelector('svg');
            
            if (isActive) {
                // Set to inactive
                item.setAttribute('data-active', '0');
                toggleBtn.classList.remove('text-green-500');
                toggleBtn.classList.add('text-gray-400');
                svg.innerHTML = svgClosedEye;
                
                // Dim the item slightly
                item.classList.remove('border-gray-200');
                item.classList.add('border-gray-100', 'opacity-60');
            } else {
                // Set to active
                item.setAttribute('data-active', '1');
                toggleBtn.classList.remove('text-gray-400');
                toggleBtn.classList.add('text-green-500');
                svg.innerHTML = svgOpenEye;
                
                // Restore item brightness
                item.classList.remove('border-gray-100', 'opacity-60');
                item.classList.add('border-gray-200');
            }

            // Send toggle message to live preview iframe
            const iframe = document.getElementById('previewIframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage({
                    action: 'toggle',
                    id: item.getAttribute('data-id'),
                    active: !isActive
                }, '*');
            }
        }

        // Handle Delete Component
        const deleteBtn = e.target.closest('.delete-component-btn');
        if (deleteBtn) {
            const id = deleteBtn.getAttribute('data-id');
            
            showCustomDeleteModal(() => {
                const row = deleteBtn.closest('.layout-item');
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';

                const fd = new FormData();
                fd.append('id', id);
                fetch('<?= BASE_URL ?>/admin/cms/delete-component', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(() => row.remove(), 300);
                    } else {
                        row.style.opacity = '1';
                        row.style.transform = '';
                        showToast('Delete failed: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(() => {
                    row.style.opacity = '1';
                    row.style.transform = '';
                    showToast('Network error. Please try again.', 'error');
                });
            }, 'Delete Item', 'This action cannot be undone. Are you sure?');
        }
    });

    // Device Toggles
    const previewContainer = document.getElementById('previewContainer');
    const viewDesktop = document.getElementById('viewDesktop');
    const viewTablet = document.getElementById('viewTablet');
    const viewMobile = document.getElementById('viewMobile');
    
    function resetDeviceToggles() {
        [viewDesktop, viewTablet, viewMobile].forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm', 'text-gray-800');
            btn.classList.add('text-gray-400');
        });
    }
    
    viewDesktop.addEventListener('click', () => {
        resetDeviceToggles();
        viewDesktop.classList.add('bg-white', 'shadow-sm', 'text-gray-800');
        viewDesktop.classList.remove('text-gray-400');
        previewContainer.style.maxWidth = '100%';
    });
    
    viewTablet.addEventListener('click', () => {
        resetDeviceToggles();
        viewTablet.classList.add('bg-white', 'shadow-sm', 'text-gray-800');
        viewTablet.classList.remove('text-gray-400');
        previewContainer.style.maxWidth = '768px';
    });
    
    viewMobile.addEventListener('click', () => {
        resetDeviceToggles();
        viewMobile.classList.add('bg-white', 'shadow-sm', 'text-gray-800');
        viewMobile.classList.remove('text-gray-400');
        previewContainer.style.maxWidth = '375px';
    });

    // Save Layout (Publish)
    document.getElementById('saveLayoutBtn').addEventListener('click', function() {
        // Grab all items, active and inactive, passing their status as well
        const allItems = Array.from(layoutList.querySelectorAll('.layout-item'))
                                .map(item => ({
                                    id: item.getAttribute('data-id'),
                                    is_active: item.getAttribute('data-active') === '1'
                                }));
        
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Publishing...';
        btn.disabled = true;

        fetch('<?= BASE_URL ?>/api/cms/layout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ layout: allItems })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh iframe
                const iframe = document.getElementById('previewIframe');
                iframe.src = iframe.src;
                
                // Show success notification
                showToast('Layout published successfully!', 'success');
            } else {
                showToast('Error saving layout: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showToast('Error: ' + error, 'error');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-50 transition-all duration-300 transform translate-y-0 opacity-100 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Save Draft & Reset mock functionality
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        showToast('Draft saved successfully!', 'success');
    });
    
    document.getElementById('resetBtn').addEventListener('click', function() {
        if (typeof showCustomDeleteModal === 'function') {
            showCustomDeleteModal(() => {
                window.location.reload();
            }, 'Reset Changes', 'Are you sure you want to reset to the published version? Unsaved changes will be lost.');
        } else if (confirm('Are you sure you want to reset to the published version? Unsaved changes will be lost.')) {
            window.location.reload();
        }
    });
});
</script>
