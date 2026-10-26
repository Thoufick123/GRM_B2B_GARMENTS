<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <?= !empty($isCustomize) ? 'Customization Management' : 'Catalog Management' ?> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700"><?= !empty($isCustomize) ? 'Customize Categories' : 'Categories' ?></span>
        </div>
        
        <div class="flex justify-between items-center mb-8 w-full">
            <div>
                <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight"><?= !empty($isCustomize) ? 'Customize Categories' : 'Categories' ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= !empty($isCustomize) ? 'Manage separate categories dedicated for custom fabrics and workshop products.' : 'Manage general ready-made product categories.' ?></p>
            </div>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button class="bg-brand-600/90 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest" onclick="exportCategories()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
                <?php if (empty($isCustomize) && is_vendor_module_enabled()): ?>
                <?php
                $reqModel = new \App\Models\CategoryRequest();
                $pendingCatReqCount = $reqModel->getPendingCount();
                ?>
                <a href="<?= BASE_URL ?>/admin/catalog/category-requests" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 px-5 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span>Requests</span>
                    <?php if ($pendingCatReqCount > 0): ?>
                        <span class="bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full font-black"><?= $pendingCatReqCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <?php 
                $permCatMod = !empty($isCustomize) ? 'customize_categories' : 'categories';
                ?>
                <?php if ($this->hasPermission($permCatMod, 'create')): ?>
                <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold text-sm shadow-md transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <?= !empty($isCustomize) ? 'Add Customize Category' : 'Add Category' ?>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Global Cart Value / Overall Order Limit Card -->
        <?php if (empty($isCustomize)): ?>
        <div class="mb-6 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #4a044e 50%, #831843 100%) !important; border: 1px solid rgba(242, 89, 150, 0.35) !important;">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full blur-2xl pointer-events-none" style="background: rgba(242, 89, 150, 0.2);"></div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="max-w-xl">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider" style="background: rgba(242, 89, 150, 0.25) !important; color: #fce7f3 !important; border: 1px solid rgba(242, 89, 150, 0.4) !important;">
                            Global Cart Requirement
                        </span>
                        <?php if (!empty($globalMinCartValue) && $globalMinCartValue > 0): ?>
                            <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full" style="background: rgba(16, 185, 129, 0.25) !important; color: #6ee7b7 !important; border: 1px solid rgba(52, 211, 153, 0.4) !important;">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse" style="background: #34d399;"></span>
                                Active: ₹<?= number_format($globalMinCartValue, 2) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full" style="background: rgba(255, 255, 255, 0.15) !important; color: #e5e7eb !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                No Global Limit (₹0.00)
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-display font-extrabold tracking-tight flex items-center gap-2" style="color: #ffffff !important;">
                        <svg class="w-6 h-6 shrink-0" style="color: #f472b6 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Overall Order Cart Value
                    </h3>
                    <p class="text-xs mt-1.5 leading-relaxed" style="color: rgba(255, 255, 255, 0.88) !important;">
                        This amount applies to the overall cart limit across <strong style="color: #ffffff;">all categories combined</strong>. If a category order rule is not mentioned or if category amounts are reached, the customer's total cart amount must reach at least this value to place an order.
                    </p>
                </div>

                <!-- Saved Display View (Default) -->
                <div id="global-cart-limit-view" class="flex items-center gap-4 shrink-0 p-3 sm:px-5 sm:py-3 rounded-2xl backdrop-blur-md" style="background: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important;">
                    <div class="text-left mr-1">
                        <div class="text-[10px] uppercase font-bold tracking-wider" style="color: #fbcfe8 !important;">Current Limit</div>
                        <div class="text-xl sm:text-2xl font-display font-black" style="color: #ffffff !important;">
                            <?php if (!empty($globalMinCartValue) && $globalMinCartValue > 0): ?>
                                ₹<?= number_format($globalMinCartValue, 2) ?>
                            <?php else: ?>
                                <span class="text-sm font-semibold" style="color: rgba(255, 255, 255, 0.75) !important;">₹0.00 (No Limit)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($this->hasPermission($permCatMod, 'edit')): ?>
                    <button type="button" onclick="toggleCartLimitEdit(true)" class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-md transition-all flex items-center gap-1.5 transform hover:-translate-y-0.5 cursor-pointer" style="background: #ffffff !important; color: #831843 !important;">
                        <svg class="w-3.5 h-3.5" style="color: #831843 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>Edit</span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Editable Form View (Hidden by default, shown on Edit) -->
                <form id="global-cart-limit-edit" action="<?= BASE_URL ?>/admin/catalog/categories/global-cart-limit" method="POST" class="hidden flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0 p-2 sm:p-2.5 rounded-2xl backdrop-blur-md" style="background: rgba(255, 255, 255, 0.16) !important; border: 1px solid rgba(255, 255, 255, 0.28) !important;">
                    <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
                    <div class="relative flex-1 sm:w-56">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="font-bold text-sm" style="color: #f472b6 !important;">₹</span>
                        </div>
                        <input type="number" step="0.01" min="0" id="min_order_cart_value_input" name="min_order_cart_value" value="<?= !empty($globalMinCartValue) && $globalMinCartValue > 0 ? (float)$globalMinCartValue : '' ?>" placeholder="0.00 (No Limit)" class="w-full pl-8 pr-4 py-2.5 rounded-xl text-sm font-bold focus:outline-none transition-all" style="background: rgba(0, 0, 0, 0.4) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; color: #ffffff !important;">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="submit" class="text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0 whitespace-nowrap cursor-pointer" style="background: linear-gradient(135deg, #F25996 0%, #d94480 100%) !important;">
                            Save
                        </button>
                        <button type="button" onclick="toggleCartLimitEdit(false)" class="text-white px-3.5 py-2.5 rounded-xl font-semibold text-xs transition-all cursor-pointer" style="background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="bg-white rounded-full shadow-sm border border-gray-100 p-1.5 flex items-center">
            <div class="flex-grow flex items-center pl-5">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="catSearchInput" onkeyup="filterCategories()" placeholder="Search categories..." class="w-full pl-3 pr-4 py-2.5 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 outline-none font-medium">
            </div>
            <div class="border-l border-gray-200 pl-2 pr-2">
                <select id="catStatusFilter" onchange="filterCategories()" class="bg-transparent border-none text-gray-600 focus:ring-0 py-2.5 pr-8 pl-4 font-semibold outline-none cursor-pointer">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-brand-50/40 to-transparent pointer-events-none -z-10"></div>

    <?php 
    $flashSuccess = \Core\Session::getFlash('success');
    $flashError = \Core\Session::getFlash('error');
    ?>
    <?php if ($flashSuccess): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <span>✅ <?= htmlspecialchars($flashSuccess) ?></span>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg">&times;</button>
    </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <span>❌ <?= htmlspecialchars($flashError) ?></span>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold text-lg">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Table Container -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-brand-900/5 border border-white/60 overflow-hidden ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50/90 to-white/90 backdrop-blur text-gray-500 text-xs uppercase tracking-widest border-b border-gray-100">
                        <th class="px-6 py-5 font-semibold text-gray-500 w-16">ID</th>
                        <th class="px-6 py-5 font-semibold text-gray-500 w-24">Image</th>
                        <th class="px-6 py-5 font-semibold text-gray-500">Details</th>
                        <?php if (empty($isCustomize)): ?>
                        <th class="px-6 py-5 font-semibold text-gray-500">MOV</th>
                        <?php endif; ?>
                        <th class="px-6 py-5 font-semibold text-gray-500">Status</th>
                        <th class="px-6 py-5 font-semibold text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    <?php if(empty($categories)): ?>
                    <tr>
                        <td colspan="<?= empty($isCustomize) ? '6' : '5' ?>" class="px-6 py-16 text-center text-gray-400 font-medium">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                No categories found. Let's create your first one.
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($categories as $index => $cat): ?>
                        <tr class="group hover:bg-brand-50/40 transition-colors duration-300">
                            <td class="px-6 py-5 text-gray-400 font-medium">
                                <span class="group-hover:text-brand-600 transition-colors duration-300">#<?= $index + 1 ?></span>
                            </td>
                            <td class="px-6 py-5">
                                <?php if ($cat['image_path']): ?>
                                    <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow-sm border border-gray-100/50 group-hover:shadow-md group-hover:scale-105 transition-all duration-300">
                                        <img src="<?= htmlspecialchars(get_image_url($cat['image_path'])) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 border border-gray-100 group-hover:bg-white group-hover:shadow-sm transition-all duration-300">
                                        <svg class="w-6 h-6 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-semibold text-gray-900 text-base group-hover:text-brand-700 transition-colors"><?= htmlspecialchars($cat['name']) ?></div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-100"><?= htmlspecialchars($cat['slug']) ?></span>
                                    <?php if(!empty($cat['hsn_code'])): ?>
                                        <span class="text-xs font-mono font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">HSN: <?= htmlspecialchars($cat['hsn_code']) ?></span>
                                    <?php endif; ?>
                                    <?php if($cat['description']): ?>
                                        <span class="text-xs text-gray-500 truncate max-w-[200px]" title="<?= htmlspecialchars($cat['description']) ?>"><?= htmlspecialchars($cat['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if (empty($isCustomize)): ?>
                            <td class="px-6 py-5">
                                <div class="flex items-center">
                                    <?php if(!empty($cat['min_order_value'])): ?>
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100 shadow-sm">MOV: ₹<?= number_format($cat['min_order_value'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs font-medium italic">No MOV</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-5">
                                <?php if($cat['status'] === 'active'): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end space-x-2">
                                    <?php if ($this->hasPermission($permCatMod, 'edit')): ?>
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($cat)) ?>)" class="p-1 text-blue-600 hover:text-blue-800 transition-colors group/btn inline-flex items-center justify-center" title="Edit">
                                        <i class="bi bi-pencil-square text-lg group-hover/btn:scale-110 transition-transform"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($this->hasPermission($permCatMod, 'delete')): ?>
                                    <button onclick="openDeleteModal(<?= $cat['id'] ?>)" class="p-1 text-red-600 hover:text-red-800 transition-colors group/btn inline-flex items-center justify-center" title="Delete">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="add-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden border border-white transform transition-all my-auto">
        <div class="px-6 py-3.5 border-b border-gray-100 flex justify-between items-center bg-gray-50/70 shrink-0">
            <div>
                <h3 class="text-lg font-display font-bold text-gray-900"><?= !empty($isCustomize) ? 'Add Customize Category' : 'Add Category' ?></h3>
                <p class="text-xs text-gray-500 font-medium"><?= !empty($isCustomize) ? 'Create a separate category dedicated for customizable fabrics.' : 'Create a new product category.' ?></p>
            </div>
            <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900 bg-white hover:bg-gray-100 p-1.5 rounded-full transition-colors shadow-sm border border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="<?= BASE_URL ?>/admin/catalog/categories/store" method="POST" enctype="multipart/form-data" class="p-5 overflow-y-auto space-y-3.5 flex-1 text-sm">
            <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
            <input type="hidden" name="is_customizable" value="<?= !empty($isCustomize) ? 1 : 0 ?>">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 text-sm transition-all bg-white" oninput="this.form.slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '')" placeholder="e.g. Electronics">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" required class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-gray-50 px-3 py-2 transition-all text-gray-600 font-mono text-xs" placeholder="electronics">
                </div>
            </div>

            <div class="<?= empty($isCustomize) ? 'grid grid-cols-2 gap-3' : '' ?>">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">HSN Code <span class="text-[10px] text-gray-400 font-normal">(digits only)</span></label>
                    <input type="text" name="hsn_code" maxlength="8" pattern="[0-9]*" oninput="this.value=this.value.replace(/\D/g,'')" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 transition-all bg-white font-mono text-xs" placeholder="e.g. 610910">
                </div>
                <?php if (empty($isCustomize)): ?>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Min Order Value (₹) <span class="text-[10px] text-gray-400 font-normal">(Category MOV)</span></label>
                    <input type="text" name="min_order_value" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 text-sm transition-all" placeholder="0.00">
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-1.5 text-xs transition-all resize-none" placeholder="Optional category description..."></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-0.5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Category Image</label>
                    <div id="add-image-preview-container" class="mb-2 hidden items-center justify-between p-2 bg-gray-50/80 border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="relative w-11 h-11 rounded-lg overflow-hidden border border-gray-200 bg-white shadow-2xs shrink-0">
                                <img id="add-image-preview" src="#" class="w-full h-full object-cover">
                            </div>
                            <span class="text-xs font-medium text-gray-600 truncate">Selected Image</span>
                        </div>
                        <button type="button" onclick="document.getElementById('add-image-path').value=''; document.getElementById('add-image-preview-container').classList.add('hidden'); document.getElementById('add-image-preview-container').classList.remove('flex');" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-white border border-gray-200 text-gray-700 hover:text-red-600 hover:border-red-200 hover:bg-red-50 text-xs font-semibold rounded-full transition-all shadow-xs cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                    <input type="hidden" name="image_path" id="add-image-path">
                    <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => { document.getElementById('add-image-path').value = rawUrl; document.getElementById('add-image-preview').src = fullUrl; document.getElementById('add-image-preview-container').classList.remove('hidden'); document.getElementById('add-image-preview-container').classList.add('flex'); })" class="w-full inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Select Media</span>
                    </button>
                </div>
                
                <div class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-0.5">SGST (%)</label>
                            <input type="text" name="sgst" step="0.01" min="0" max="100" value="0.00" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-0.5">CGST (%)</label>
                            <input type="text" name="cgst" step="0.01" min="0" max="100" value="0.00" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-0.5">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all bg-white font-medium">
                            <option value="active">🟢 Active</option>
                            <option value="inactive">⚪ Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="pt-3.5 border-t border-gray-100 flex justify-end space-x-2.5 shrink-0 items-center">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-sm font-bold shadow-md shadow-brand-600/20 transform hover:-translate-y-0.5 transition-all inline-flex items-center justify-center">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden border border-white transform transition-all my-auto">
        <div class="px-6 py-3.5 border-b border-gray-100 flex justify-between items-center bg-gray-50/70 shrink-0">
            <div>
                <h3 class="text-lg font-display font-bold text-gray-900"><?= !empty($isCustomize) ? 'Edit Customize Category' : 'Edit Category' ?></h3>
                <p class="text-xs text-gray-500 font-medium"><?= !empty($isCustomize) ? 'Update customize category details and settings.' : 'Update category details and settings.' ?></p>
            </div>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900 bg-white hover:bg-gray-100 p-1.5 rounded-full transition-colors shadow-sm border border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="<?= BASE_URL ?>/admin/catalog/categories/update" method="POST" enctype="multipart/form-data" class="p-5 overflow-y-auto space-y-3.5 flex-1 text-sm">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
            <input type="hidden" name="is_customizable" id="edit-is-customizable" value="<?= !empty($isCustomize) ? 1 : 0 ?>">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit-name" required class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 text-sm transition-all bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" id="edit-slug" required class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-gray-50 px-3 py-2 transition-all text-gray-600 font-mono text-xs">
                </div>
            </div>

            <div class="<?= empty($isCustomize) ? 'grid grid-cols-2 gap-3' : '' ?>">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">HSN Code <span class="text-[10px] text-gray-400 font-normal">(digits only)</span></label>
                    <input type="text" name="hsn_code" id="edit-hsn-code" maxlength="8" pattern="[0-9]*" oninput="this.value=this.value.replace(/\D/g,'')" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 transition-all bg-white font-mono text-xs" placeholder="e.g. 610910">
                </div>
                <?php if (empty($isCustomize)): ?>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Min Order Value (₹) <span class="text-[10px] text-gray-400 font-normal">(Category MOV)</span></label>
                    <input type="text" name="min_order_value" id="edit-min-value" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-2 text-sm transition-all">
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Description</label>
                <textarea name="description" id="edit-description" rows="2" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-3 py-1.5 text-xs transition-all resize-none"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-0.5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Category Image</label>
                    <div id="edit-image-preview-container" class="mb-2 hidden items-center justify-between p-2 bg-gray-50/80 border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="relative w-11 h-11 rounded-lg overflow-hidden border border-gray-200 bg-white shadow-2xs shrink-0">
                                <img id="edit-image-preview" src="#" class="w-full h-full object-cover">
                            </div>
                            <span id="edit-image-preview-text" class="text-xs font-medium text-gray-600 truncate">Current Image</span>
                        </div>
                        <button type="button" onclick="document.getElementById('edit-image-path').value=''; document.getElementById('edit-image-preview-container').classList.add('hidden'); document.getElementById('edit-image-preview-container').classList.remove('flex');" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-white border border-gray-200 text-gray-700 hover:text-red-600 hover:border-red-200 hover:bg-red-50 text-xs font-semibold rounded-full transition-all shadow-xs cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                    <input type="hidden" name="image_path" id="edit-image-path">
                    <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => { document.getElementById('edit-image-path').value = rawUrl; document.getElementById('edit-image-preview').src = fullUrl; document.getElementById('edit-image-preview-container').classList.remove('hidden'); document.getElementById('edit-image-preview-container').classList.add('flex'); document.getElementById('edit-image-preview-text').innerText = 'Selected Image'; })" class="w-full inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Change Media</span>
                    </button>
                </div>
                
                <div class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-0.5">SGST (%)</label>
                            <input type="text" name="sgst" id="edit-sgst" step="0.01" min="0" max="100" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-0.5">CGST (%)</label>
                            <input type="text" name="cgst" id="edit-cgst" step="0.01" min="0" max="100" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-0.5">Status</label>
                        <select name="status" id="edit-status" class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 px-2.5 py-1.5 text-xs transition-all bg-white font-medium">
                            <option value="active">🟢 Active</option>
                            <option value="inactive">⚪ Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="pt-3.5 border-t border-gray-100 flex justify-end space-x-2.5 shrink-0 items-center">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-sm font-bold shadow-md shadow-brand-600/20 transform hover:-translate-y-0.5 transition-all inline-flex items-center justify-center">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center p-8 border border-white transform transition-all">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 border-4 border-red-100/50 mb-5 relative">
            <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="absolute -right-1 -top-1 w-4 h-4 bg-red-500 rounded-full animate-ping opacity-75"></div>
        </div>
        <h3 class="text-xl font-display font-bold text-gray-900 mb-2">Delete Category?</h3>
        <p id="delete-modal-text" class="text-sm text-gray-500 mb-8 font-medium">Checking products...</p>
        <div id="delete-error-msg" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-xl text-left"></div>
        <form id="category-delete-form" action="<?= BASE_URL ?>/admin/catalog/categories/delete" method="POST" class="flex space-x-3 items-center">
            <input type="hidden" name="id" id="delete-id">
            <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
            <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="flex-1 px-6 py-2.5 bg-white border border-gray-200 rounded-full text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">Cancel</button>
            <button type="submit" id="btn-confirm-delete" class="flex-1 px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white rounded-full text-sm font-semibold shadow-lg shadow-red-500/30 transform hover:-translate-y-0.5 transition-all inline-flex items-center justify-center">Yes, Delete</button>
        </form>
    </div>
</div>

<script>
const allCategories = <?= json_encode($categories ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function toggleCartLimitEdit(showEdit) {
    const viewEl = document.getElementById('global-cart-limit-view');
    const editEl = document.getElementById('global-cart-limit-edit');
    if (!viewEl || !editEl) return;
    
    if (showEdit) {
        viewEl.classList.add('hidden');
        editEl.classList.remove('hidden');
        editEl.classList.add('flex');
        const input = document.getElementById('min_order_cart_value_input');
        if (input) {
            input.focus();
            input.select();
        }
    } else {
        editEl.classList.add('hidden');
        editEl.classList.remove('flex');
        viewEl.classList.remove('hidden');
    }
}

function exportCategories() {
    if (allCategories.length === 0) {
        alert('No categories to export.');
        return;
    }
    const isCustomizeCat = <?= !empty($isCustomize) ? 'true' : 'false' ?>;
    const headers = isCustomizeCat 
        ? ['ID', 'Name', 'Slug', 'HSN Code', 'Status', 'Product Count']
        : ['ID', 'Name', 'Slug', 'HSN Code', 'Min Order Value', 'Status', 'Product Count'];
        
    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n";
    allCategories.forEach(c => {
        const row = isCustomizeCat ? [
            c.id,
            `"${(c.name || '').replace(/"/g, '""')}"`,
            c.slug,
            c.hsn_code || '',
            c.status,
            c.product_count || 0
        ] : [
            c.id,
            `"${(c.name || '').replace(/"/g, '""')}"`,
            c.slug,
            c.hsn_code || '',
            c.min_order_value || '',
            c.status,
            c.product_count || 0
        ];
        csvContent += row.join(",") + "\n";
    });
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", isCustomizeCat ? "customize_categories_export.csv" : "categories_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function filterCategories() {
    const searchVal = document.getElementById('catSearchInput').value.toLowerCase();
    const statusVal = document.getElementById('catStatusFilter').value.toLowerCase();
    
    // Simple DOM filtering since categories are rendered via PHP
    const rows = document.querySelectorAll('tbody tr.group'); // Only the data rows
    let visibleCount = 0;
    
    rows.forEach(row => {
        const textContent = row.innerText.toLowerCase();
        let statusMatches = true;
        if (statusVal === 'active' && !textContent.includes('active')) statusMatches = false;
        if (statusVal === 'inactive' && !textContent.includes('inactive')) statusMatches = false;
        
        const matchesSearch = textContent.includes(searchVal);
        
        if (matchesSearch && statusMatches) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}

function openEditModal(cat) {
    document.getElementById('edit-id').value = cat.id;
    document.getElementById('edit-name').value = cat.name;
    document.getElementById('edit-slug').value = cat.slug;
    document.getElementById('edit-hsn-code').value = cat.hsn_code || '';
    document.getElementById('edit-description').value = cat.description || '';
    const minValEl = document.getElementById('edit-min-value');
    if (minValEl) {
        minValEl.value = cat.min_order_value || '';
    }
    document.getElementById('edit-sgst').value = cat.sgst !== null && cat.sgst !== undefined ? cat.sgst : '0.00';
    document.getElementById('edit-cgst').value = cat.cgst !== null && cat.cgst !== undefined ? cat.cgst : '0.00';
    const statusSelect = document.getElementById('edit-status');
    if (statusSelect) {
        statusSelect.value = (cat.status || 'active').toLowerCase();
        statusSelect.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof statusSelect._grmSyncSelect === 'function') statusSelect._grmSyncSelect();
    }
    
    // Handle image preview
    const previewContainer = document.getElementById('edit-image-preview-container');
    const previewImage = document.getElementById('edit-image-preview');
    const previewText = document.getElementById('edit-image-preview-text');
    const pathInput = document.getElementById('edit-image-path');
    pathInput.value = cat.image_path || ''; // keep existing path by default
    
    if (cat.image_path) {
        const isAbsolute = cat.image_path.startsWith('http');
        previewImage.src = isAbsolute ? cat.image_path : '<?= BASE_URL ?>/' + cat.image_path.replace(/^\/+/, '');
        previewContainer.classList.remove('hidden');
        previewContainer.classList.add('flex');
        previewText.innerText = 'Current Image';
    } else {
        previewContainer.classList.add('hidden');
        previewContainer.classList.remove('flex');
        previewImage.src = '#';
    }

    document.getElementById('edit-modal').classList.remove('hidden');
}

function openDeleteModal(id) {
    document.getElementById('delete-id').value = id;
    const modalText = document.getElementById('delete-modal-text');
    const errDiv = document.getElementById('delete-error-msg');
    if (errDiv) { errDiv.classList.add('hidden'); errDiv.innerText = ''; }
    
    const confirmBtn = document.getElementById('btn-confirm-delete');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.classList.remove('hidden');
        confirmBtn.innerHTML = 'Yes, Delete';
    }
    
    modalText.innerHTML = '<span class="inline-flex items-center gap-1.5 text-gray-500"><svg class="animate-spin h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Checking linked items...</span>';
    document.getElementById('delete-modal').classList.remove('hidden');
    
    const checkUrl = '<?= BASE_URL ?>/admin/catalog/categories/check-delete?id=' + id;
    fetch(checkUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const prodCount = data.product_count !== undefined ? data.product_count : (data.count || 0);
                const subCount = data.subcategory_count !== undefined ? data.subcategory_count : 0;
                const attrCount = data.attribute_count !== undefined ? data.attribute_count : 0;
                
                if (subCount > 0 || attrCount > 0 || prodCount > 0) {
                    if (confirmBtn) confirmBtn.classList.add('hidden');
                    modalText.innerHTML = `
                        <div class="text-left space-y-3">
                            <div class="p-3 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-semibold flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <strong class="font-bold text-red-900 block text-sm mb-1">Deletion Restricted</strong>
                                    This category cannot be deleted because it is currently linked to:
                                    <ul class="list-disc list-inside mt-1.5 space-y-1 text-red-800 font-medium text-xs">
                                        ${subCount > 0 ? `<li><strong>${subCount}</strong> sub-categor(y/ies)</li>` : ''}
                                        ${attrCount > 0 ? `<li><strong>${attrCount}</strong> attribute(s)</li>` : ''}
                                        ${prodCount > 0 ? `<li><strong>${prodCount}</strong> product(s)</li>` : ''}
                                    </ul>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed font-medium">
                                To prevent unassigned items and maintain catalog integrity, please delete or reassign all linked subcategories, attributes, and products before deleting this category.
                            </p>
                        </div>
                    `;
                } else {
                    if (confirmBtn) {
                        confirmBtn.classList.remove('hidden');
                        confirmBtn.disabled = false;
                    }
                    modalText.innerHTML = `Are you sure you want to delete this category? This action <strong class="text-gray-900 font-bold">cannot be undone</strong>.`;
                }
            } else {
                modalText.innerHTML = `Are you sure you want to delete this category?`;
                if (confirmBtn) confirmBtn.disabled = false;
            }
        })
        .catch(err => {
            modalText.innerHTML = `Are you sure you want to delete this category?`;
            if (confirmBtn) confirmBtn.disabled = false;
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const deleteForm = document.getElementById('category-delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-delete');
            const errDiv = document.getElementById('delete-error-msg');
            const catId = document.getElementById('delete-id').value;
            
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Deleting...';
            
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json();
                }
                // Fallback for full page reload
                window.location.reload();
                return { success: true };
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('delete-modal').classList.add('hidden');
                    // Reload to update stats and table cleanly
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Yes, Delete';
                    if (errDiv) {
                        errDiv.innerText = data.message || 'Error deleting category.';
                        errDiv.classList.remove('hidden');
                    } else {
                        alert(data.message || 'Error deleting category.');
                    }
                }
            })
            .catch(err => {
                // If anything went unexpected, submit form normally
                this.submit();
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../media/_selector_modal.php'; ?>
