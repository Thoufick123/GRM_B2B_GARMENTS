<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-display"><?= !empty($isCustomize) ? 'Add Fabric' : 'Add Product' ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= !empty($isCustomize) ? 'Create a new wholesale customizable fabric.' : 'Create a new product in the catalog.' ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/catalog/products<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="text-gray-500 hover:text-gray-900 font-medium text-sm flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <?= !empty($isCustomize) ? 'Back to Fabrics' : 'Back to Products' ?>
        </a>
    </div>

    <form id="product-create-form" action="<?= BASE_URL ?>/admin/catalog/products/store" method="POST" enctype="multipart/form-data" class="space-y-8 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <?php if (!empty($isCustomize)): ?>
        <input type="hidden" name="module" value="customize">
        <?php endif; ?>
        
        <!-- Basic Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Basic Information</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= !empty($isCustomize) ? 'Fabric Name' : 'Product Name' ?> <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="<?= !empty($isCustomize) ? 'Enter fabric name' : 'Enter product name' ?>" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2" oninput="this.form.slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" required placeholder="<?= !empty($isCustomize) ? 'fabric-slug' : 'product-slug' ?>" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 bg-gray-50 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2 bg-white">
                        <option value="active" selected>Active</option>
                        <option value="draft">Draft</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <?php if (empty($isCustomize)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MOQ <span class="text-red-500">*</span></label>
                    <input type="text" name="moq_override" required min="1" placeholder="Minimum Order Quantity (e.g. 10)" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                </div>
                <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fabric Quantity (Stock) <span class="text-red-500">*</span></label>
                    <input type="number" name="stock_quantity" required min="1" value="100" placeholder="e.g. 100 (meters/units)" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                </div>
                <?php endif; ?>
                <div class="sm:col-span-2 space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-bold text-gray-800"><?= !empty($isCustomize) ? 'Fabric Description' : 'Description' ?> <span class="text-red-500">*</span></label>
                        <button type="button" onclick="addCustomField()" class="text-xs font-semibold text-brand-700 bg-brand-50 border border-brand-200 px-3 py-1.5 rounded-lg flex items-center hover:bg-brand-100 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 mr-1 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Field
                        </button>
                    </div>
                    <textarea name="description" rows="4" required placeholder="<?= !empty($isCustomize) ? 'Enter detailed fabric description...' : 'Detailed description of the product features, benefits...' ?>" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2 text-sm text-gray-800"></textarea>

                    <!-- Dynamic Additional Custom Fields Container -->
                    <div id="custom-fields-container" class="space-y-3 pt-2"></div>
                </div>
            </div>
        </div>

        <!-- Categorization and Base Image -->
        <?php if (!empty($isCustomize)): ?>
        <div class="space-y-6">
            <!-- Categorization (Full Width for Customizable Fabric) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-2xs">
                <div class="border-b border-gray-200 pb-3 mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Categorization</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Organize your fabric hierarchy and assign size charts.</p>
                </div>

                <!-- Row 1: Category & Subcategory -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label class="block text-sm font-bold text-gray-800">Category <span class="text-red-500">*</span></label>
                            <?php if (function_exists('is_vendor_module_enabled') && is_vendor_module_enabled()): ?>
                            <button type="button" onclick="openCategoryRequestModal('category')" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg border border-brand-200 transition-colors shrink-0 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>Request New</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                            <option value="">Select Category...</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" data-sgst="<?= (float)($category['sgst'] ?? 0) ?>" data-cgst="<?= (float)($category['cgst'] ?? 0) ?>" data-hsn="<?= htmlspecialchars($category['hsn_code'] ?? '') ?>"><?= htmlspecialchars($category['name']) ?><?= !empty($category['hsn_code']) ? ' (HSN: ' . htmlspecialchars($category['hsn_code']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label class="block text-sm font-bold text-gray-800">Subcategory</label>
                            <?php if (function_exists('is_vendor_module_enabled') && is_vendor_module_enabled()): ?>
                            <button type="button" onclick="openCategoryRequestModal('subcategory')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg border border-indigo-200 transition-colors shrink-0 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>Request New</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <select name="sub_category_id" id="sub_category_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 disabled:bg-gray-100 bg-white text-sm text-gray-800 shadow-2xs" disabled>
                            <option value="">Select Category first...</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Size Chart -->
                <div class="mt-5 pt-4 border-t border-gray-200/80">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2">
                                    <label class="block text-sm font-bold text-gray-800">Size Chart</label>
                                    <span class="text-[11px] font-semibold text-gray-400 bg-gray-200/60 px-2 py-0.5 rounded-full">Optional</span>
                                </div>
                                <a href="<?= BASE_URL ?>/admin/cms/size-charts/create" target="_blank" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg border border-emerald-200 transition-colors shrink-0 flex items-center gap-1" title="Open Size Chart Creator in new tab">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span>Add New Chart</span>
                                </a>
                            </div>
                            <select name="size_chart_id" id="size_chart_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                                <option value="">Default (Auto from Category / Subcategory / General)</option>
                                <?php if (!empty($sizeCharts)): ?>
                                    <?php foreach($sizeCharts as $sc): ?>
                                        <option value="<?= $sc['id'] ?>" 
                                                data-category-id="<?= (int)($sc['category_id'] ?? 0) ?>"
                                                data-subcategory-id="<?= (int)($sc['sub_category_id'] ?? 0) ?>">
                                            <?= htmlspecialchars($sc['title']) ?><?= !empty($sc['sub_category_name']) ? ' [' . htmlspecialchars($sc['sub_category_name']) . ']' : (!empty($sc['category_name']) ? ' (' . htmlspecialchars($sc['category_name']) . ')' : '') ?><?= !empty($sc['dress_type']) ? ' - ' . htmlspecialchars($sc['dress_type']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1.5" id="size_chart_hint">Auto-filters to match selected Category and Subcategory.</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($vendors)): ?>
                <!-- Row 3: Assigned Vendor -->
                <div class="mt-5 pt-4 border-t border-gray-200/80">
                    <label class="block text-sm font-bold text-gray-800 mb-1.5">Assigned Vendor (Optional)</label>
                    <select name="vendor_id" id="vendor_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                        <option value="">In-House / Admin Product (Default)</option>
                        <?php foreach($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                <?= htmlspecialchars($v['store_name'] ?? $v['name']) ?> (ID: <?= htmlspecialchars($v['unique_vendor_id'] ?? 'VN'.$v['id']) ?> - <?= htmlspecialchars($v['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <!-- Fabric Images: 5-Slot Showcase Gallery (1 Default Base + 4 Detail Images) -->
            <div class="bg-white p-6 rounded-2xl border border-pink-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 font-serif flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#F25996]"></span>
                            Fabric Images (Up to 5 Images)
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Add up to 5 images for this fabric. <strong>Image 1 is the Default Base Image</strong> used across listings. Images 2–5 display as the product details gallery on the customer workshop.
                        </p>
                    </div>
                    <button type="button" onclick="uploadNextFabricImage()" class="px-3.5 py-2 bg-[#F25996] hover:bg-[#d8407d] text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Add Next Image</span>
                    </button>
                </div>

                <!-- 5 Slots Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 mt-5">
                    <!-- Slot 1: Default Base Image (Primary) -->
                    <div class="bg-gradient-to-b from-pink-50/70 to-white rounded-2xl border-2 border-pink-200/90 p-3.5 flex flex-col justify-between shadow-2xs relative">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-pink-100 text-[#F25996] border border-pink-200 uppercase tracking-wider">⭐ 1. Base Image</span>
                            </div>
                            <input type="hidden" name="primary_image_path" id="fabric_slot_input_0" value="">
                            
                            <!-- Preview State -->
                            <div id="fabric_slot_prev_wrap_0" class="hidden relative group">
                                <div class="aspect-square w-full rounded-xl overflow-hidden bg-gray-50 border border-pink-200 relative">
                                    <img id="fabric_slot_img_0" src="#" class="w-full h-full object-cover">
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded bg-black/60 text-white text-[9px] font-bold backdrop-blur-xs">Default Base</span>
                                </div>
                                <div class="flex items-center justify-between gap-1 mt-2.5">
                                    <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => setFabricSlot(0, rawUrl, fullUrl))" class="flex-1 py-1.5 px-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg text-center transition-colors">
                                        Change
                                    </button>
                                    <button type="button" onclick="removeFabricSlot(0, 0)" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors" title="Remove image">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Empty Placeholder State -->
                            <div id="fabric_slot_empty_wrap_0" onclick="openMediaSelector((rawUrl, fullUrl) => setFabricSlot(0, rawUrl, fullUrl))" class="flex aspect-square w-full rounded-xl border-2 border-dashed border-pink-300 bg-pink-50/30 hover:bg-pink-50/60 cursor-pointer flex-col items-center justify-center p-3 text-center transition-colors group">
                                <div class="w-10 h-10 rounded-full bg-pink-100 text-[#F25996] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <span class="text-xs font-bold text-[#F25996]">Select Base Image</span>
                                <span class="text-[10px] text-gray-400 mt-0.5">(Primary)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Slots 2 through 5: Additional Detail Angles -->
                    <?php for ($s = 1; $s <= 4; $s++): ?>
                    <div class="bg-white rounded-2xl border border-gray-200 p-3.5 flex flex-col justify-between shadow-2xs relative hover:border-gray-300 transition-colors">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200 uppercase tracking-wider">📷 <?= $s + 1 ?>. Detail Angle</span>
                            </div>
                            <input type="hidden" name="gallery_images[<?= $s - 1 ?>]" id="fabric_slot_input_<?= $s ?>" value="">
                            
                            <!-- Preview State -->
                            <div id="fabric_slot_prev_wrap_<?= $s ?>" class="hidden relative group">
                                <div class="aspect-square w-full rounded-xl overflow-hidden bg-gray-50 border border-gray-200 relative">
                                    <img id="fabric_slot_img_<?= $s ?>" src="#" class="w-full h-full object-cover">
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded bg-black/60 text-white text-[9px] font-bold backdrop-blur-xs">Image <?= $s + 1 ?></span>
                                </div>
                                <div class="flex items-center justify-between gap-1 mt-2.5">
                                    <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => setFabricSlot(<?= $s ?>, rawUrl, fullUrl))" class="flex-1 py-1.5 px-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg text-center transition-colors">
                                        Change
                                    </button>
                                    <button type="button" onclick="removeFabricSlot(<?= $s ?>, 0)" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors" title="Remove image">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Empty Placeholder State -->
                            <div id="fabric_slot_empty_wrap_<?= $s ?>" onclick="openMediaSelector((rawUrl, fullUrl) => setFabricSlot(<?= $s ?>, rawUrl, fullUrl))" class="flex aspect-square w-full rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 hover:bg-gray-100/80 cursor-pointer flex-col items-center justify-center p-3 text-center transition-colors group">
                                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 group-hover:text-gray-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span class="text-xs font-bold text-gray-600">+ Add Image <?= $s + 1 ?></span>
                                <span class="text-[10px] text-gray-400 mt-0.5">(Optional)</span>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Categorization -->
                <div class="sm:col-span-2 bg-gray-50 p-6 rounded-2xl border border-gray-100 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="border-b border-gray-200 pb-3 mb-5">
                            <h3 class="text-lg font-bold text-gray-900">Categorization</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Organize your product hierarchy and assign size charts.</p>
                        </div>

                        <!-- Row 1: Category & Subcategory -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <label class="block text-sm font-bold text-gray-800">Category <span class="text-red-500">*</span></label>
                                    <?php if (is_vendor_module_enabled()): ?>
                                    <button type="button" onclick="openCategoryRequestModal('category')" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg border border-brand-200 transition-colors shrink-0 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Request New</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                                    <option value="">Select Category...</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" data-sgst="<?= (float)($category['sgst'] ?? 0) ?>" data-cgst="<?= (float)($category['cgst'] ?? 0) ?>" data-hsn="<?= htmlspecialchars($category['hsn_code'] ?? '') ?>"><?= htmlspecialchars($category['name']) ?><?= !empty($category['hsn_code']) ? ' (HSN: ' . htmlspecialchars($category['hsn_code']) . ')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <label class="block text-sm font-bold text-gray-800">Subcategory</label>
                                    <?php if (is_vendor_module_enabled()): ?>
                                    <button type="button" onclick="openCategoryRequestModal('subcategory')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg border border-indigo-200 transition-colors shrink-0 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Request New</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <select name="sub_category_id" id="sub_category_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 disabled:bg-gray-100 bg-white text-sm text-gray-800 shadow-2xs" disabled>
                                    <option value="">Select Category first...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Size Chart -->
                        <div class="mt-5 pt-4 border-t border-gray-200/80">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="sm:col-span-2">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <label class="block text-sm font-bold text-gray-800">Size Chart</label>
                                            <span class="text-[11px] font-semibold text-gray-400 bg-gray-200/60 px-2 py-0.5 rounded-full">Optional</span>
                                        </div>
                                    </div>
                                    <select name="size_chart_id" id="size_chart_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                                        <option value="">Default (Auto from Category / Subcategory / General)</option>
                                        <?php if (!empty($sizeCharts)): ?>
                                            <?php foreach($sizeCharts as $sc): ?>
                                                <option value="<?= $sc['id'] ?>" 
                                                        data-category-id="<?= (int)($sc['category_id'] ?? 0) ?>"
                                                        data-subcategory-id="<?= (int)($sc['sub_category_id'] ?? 0) ?>">
                                                    <?= htmlspecialchars($sc['title']) ?><?= !empty($sc['sub_category_name']) ? ' [' . htmlspecialchars($sc['sub_category_name']) . ']' : (!empty($sc['category_name']) ? ' (' . htmlspecialchars($sc['category_name']) . ')' : '') ?><?= !empty($sc['dress_type']) ? ' - ' . htmlspecialchars($sc['dress_type']) : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1.5" id="size_chart_hint">Auto-filters to match selected Category and Subcategory.</p>
                                </div>
                            </div>
                        </div>

                        <?php if (is_vendor_module_enabled() && !empty($vendors)): ?>
                        <!-- Row 3: Assigned Vendor -->
                        <div class="mt-5 pt-4 border-t border-gray-200/80">
                            <label class="block text-sm font-bold text-gray-800 mb-1.5">Assigned Vendor (Optional)</label>
                            <select name="vendor_id" id="vendor_id" class="w-full border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 px-3.5 py-2.5 bg-white text-sm text-gray-800 shadow-2xs">
                                <option value="">In-House / Admin Product (Default)</option>
                                <?php foreach($vendors as $v): ?>
                                    <option value="<?= $v['id'] ?>">
                                        <?= htmlspecialchars($v['store_name'] ?? $v['name']) ?> (ID: <?= htmlspecialchars($v['unique_vendor_id'] ?? 'VN'.$v['id']) ?> - <?= htmlspecialchars($v['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Base Image -->
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Product Base Image</h3>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Primary Image</label>
                        <p class="text-xs text-gray-500 mb-3">This image will be used as the thumbnail on landing pages.</p>
                        
                        <input type="hidden" name="primary_image_path" id="primary_image_path">
                        <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => { 
                            const displayUrl = fullUrl || ((rawUrl.startsWith('http://') || rawUrl.startsWith('https://')) ? rawUrl : ('<?= BASE_URL ?>/' + rawUrl.replace(/^\/+/, '')));
                            document.getElementById('primary_image_path').value = rawUrl; 
                            document.getElementById('image_preview').src = displayUrl; 
                            document.getElementById('image_preview_container').classList.remove('hidden'); 
                        })" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs cursor-pointer">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Select Media</span>
                        </button>

                        <div id="image_preview_container" class="mt-4 hidden relative inline-block group">
                            <p class="text-xs text-gray-500 mb-2">Preview:</p>
                            <div class="relative inline-block group" id="primary_preview_wrapper">
                                <img id="image_preview" src="#" alt="Image Preview" class="w-32 h-32 object-cover rounded-xl border border-gray-200 shadow-sm">
                                <button type="button" onclick="document.getElementById('primary_image_path').value=''; document.getElementById('image_preview_container').classList.add('hidden');" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-md hover:scale-110 active:scale-95 transition-all cursor-pointer" title="Remove Image">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($isCustomize)): ?>
        <!-- Customization Style Labels / Templates -->
        <div class="bg-white p-6 rounded-2xl border border-pink-100 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-pink-50 text-pink-700 flex items-center justify-center text-sm font-black shadow-2xs">🏷️</span>
                        <span>Assign Customization Style Labels / Templates</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Select which template labels (e.g. <code>Maternity -&gt; Feeding -&gt; Full Sleeve</code>) apply to this fabric. On the storefront customization workshop, customers can allocate piece quantities to each chosen style.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="selectAllCustomLabels(true)" class="px-2.5 py-1 text-xs font-bold text-pink-700 bg-pink-50 hover:bg-pink-100 rounded-lg border border-pink-200 transition-colors cursor-pointer">Select All</button>
                    <button type="button" onclick="selectAllCustomLabels(false)" class="px-2.5 py-1 text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors cursor-pointer">Clear</button>
                    <a href="<?= BASE_URL ?>/admin/customization-labels/create" target="_blank" class="px-3 py-1 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        + New Label
                    </a>
                </div>
            </div>

            <?php if (empty($allLabels)): ?>
                <div class="p-6 bg-pink-50/50 rounded-xl border border-pink-100 text-center">
                    <p class="text-xs text-gray-600 mb-2">No customization labels created yet.</p>
                    <a href="<?= BASE_URL ?>/admin/customization-labels/create" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-pink-600 hover:underline">
                        + Create your first template label (e.g. Maternity -&gt; Feeding -&gt; Full Sleeve)
                    </a>
                </div>
            <?php else: ?>
                <?php
                    $groupedLabels = [];
                    foreach ($allLabels as $lbl) {
                        $grp = $lbl['group_name'] ?: 'General / Standard';
                        $groupedLabels[$grp][] = $lbl;
                    }
                ?>
                <div class="space-y-4">
                    <?php foreach ($groupedLabels as $grpName => $lblGroup): ?>
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200/70">
                            <div class="text-xs font-extrabold text-gray-900 uppercase tracking-wider mb-2.5 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                                <span><?= htmlspecialchars($grpName) ?></span>
                                <span class="text-[10px] font-bold text-gray-400 bg-white px-1.5 py-0.5 rounded border border-gray-200"><?= count($lblGroup) ?> styles</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                <?php foreach ($lblGroup as $lbl): ?>
                                    <label class="flex items-start gap-2.5 p-2.5 bg-white rounded-xl border border-gray-200 hover:border-pink-300 hover:bg-pink-50/20 transition-all cursor-pointer select-none">
                                        <input type="checkbox" name="label_ids[]" value="<?= $lbl['id'] ?>" class="fabric-label-checkbox rounded border-gray-300 text-pink-600 focus:ring-pink-500 mt-0.5">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-gray-900 flex items-center gap-1 truncate">
                                                <span>🏷️</span>
                                                <span class="truncate"><?= htmlspecialchars($lbl['display_label']) ?></span>
                                            </div>
                                            <?php if (!empty($lbl['description'])): ?>
                                                <p class="text-[10.5px] text-gray-400 truncate mt-0.5"><?= htmlspecialchars($lbl['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Attributes -->
        <div id="attributes-section">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-2">
                <div class="flex items-center space-x-3">
                    <h3 class="text-lg font-semibold text-gray-900">✨ Custom Specifications</h3>
                    <button type="button" onclick="openAttributeModal()" class="inline-flex items-center px-2.5 py-1 bg-pink-50 hover:bg-pink-100 text-[#F25996] border border-pink-200 rounded-lg text-xs font-bold transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 mr-1 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Attribute
                    </button>
                </div>
                <label class="flex items-center space-x-2 cursor-pointer bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                    <input type="checkbox" id="no_attributes_checkbox" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                    <span class="text-sm font-bold text-gray-700">No Attributes (Simple Product)</span>
                </label>
            </div>
            
            <div id="attributes-container">
                <p class="text-sm text-gray-500 italic">Select a category to view available attributes.</p>
            </div>
        </div>

        <!-- Variant Management -->
        <div id="variant-management-section" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">📑 Variant Management <span class="text-red-500">*</span></h3>
            
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center" id="variant-initial-state">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-sm text-gray-500 mb-4">Select custom specifications above, then generate variants.</p>
                <button type="button" id="btn-generate-variants" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 shadow-sm">GENERATE VARIANTS</button>
            </div>

            <div id="variant-grid-container" style="display: none;" class="mt-6">
                <!-- Variant cards will be injected here -->
            </div>
        </div>

        <?php if (!empty($isCustomize)): ?>
        <!-- ======================================================== -->
        <!-- FABRIC CUSTOMIZATION RULES & CUSTOMIZED LABELS (STUDIO)  -->
        <!-- ======================================================== -->
        <div class="space-y-6 pt-6 border-t border-gray-100">
            <!-- 1. FABRIC CUSTOMIZATION RULES -->
            <div class="bg-gradient-to-br from-amber-50/40 via-white to-pink-50/30 p-6 sm:p-7 rounded-2xl border border-amber-200/80 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-amber-200/60 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🎛️</span>
                            <h3 class="text-lg font-black text-gray-900 font-display">Fabric Customization Rules</h3>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">Customization Engine</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Configure minimum/maximum required order pieces, price reduction without cover, and dynamic delivery schedules for this fabric.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Required Pieces to Enable Payment (Min & Max Limits) -->
                    <div class="md:col-span-2 bg-white p-4 sm:p-5 rounded-xl border border-amber-200/70 shadow-2xs">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                                <span>Required Order Pieces (Min / Max Limits)</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold border border-amber-200">Dynamic Multiplier</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Minimum Required Pieces <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="minimum_pieces" value="<?= htmlspecialchars($fabricRule['minimum_pieces'] ?? 10) ?>" min="1" required placeholder="e.g. 10" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-gray-900 pr-14">
                                    <span class="absolute right-3.5 top-2.5 text-xs text-gray-400 font-semibold">Pcs (Min)</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Base pieces required before checkout is unlocked (scales with Sets).
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Maximum Allowed Pieces <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="maximum_pieces" value="<?= htmlspecialchars($fabricRule['maximum_pieces'] ?? 100) ?>" min="1" required placeholder="e.g. 100" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-gray-900 pr-14">
                                    <span class="absolute right-3.5 top-2.5 text-xs text-gray-400 font-semibold">Pcs (Max)</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Maximum total pieces buyer can order per set multiplier.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Without Cover Discount Percentage -->
                    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span>Without Cover Discount (%)</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Price Reduction</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="without_cover_discount_percentage" value="<?= htmlspecialchars(number_format((float)($fabricRule['without_cover_discount_percentage'] ?? 0), 2, '.', '')) ?>" step="0.01" min="0" max="100" placeholder="e.g. 5.00" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-bold text-emerald-700 pr-14">
                                <span class="absolute right-3.5 top-2.5 text-xs text-emerald-600 font-semibold">% OFF</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2">
                            When buyer selects <strong>Without Cover</strong> in the customization UI, the base product price is reduced by this percentage.
                        </p>
                    </div>

                    <!-- Dynamic Delivery Dates Setting -->
                    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>🚚 Dynamic Delivery Schedule</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Days</label>
                                    <div class="relative">
                                        <input type="number" name="delivery_days" value="<?= htmlspecialchars($fabricRule['delivery_days'] ?? 7) ?>" min="1" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-gray-900 pr-12">
                                        <span class="absolute right-2.5 top-2 text-xs text-gray-400 font-medium">Days</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Per Pieces</label>
                                    <div class="relative">
                                        <input type="number" name="delivery_base_pieces" value="<?= htmlspecialchars($fabricRule['delivery_base_pieces'] ?? 100) ?>" min="1" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-gray-900 pr-12">
                                        <span class="absolute right-2.5 top-2 text-xs text-gray-400 font-medium">Pcs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2">
                            e.g. 7 days for 100 pcs; delivery estimate calculates dynamically as buyer quantity increases.
                        </p>
                    </div>

                    <!-- Customization Rule Status -->
                    <div class="md:col-span-2 bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-2xs">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Customization Rule Status
                        </label>
                        <select name="rule_status" class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium">
                            <option value="active" <?= ($fabricRule['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible & Enabled for Customization)</option>
                            <option value="inactive" <?= ($fabricRule['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive (Draft / Hidden from Customization Workshop)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. CUSTOMIZED LABELS (STYLE OPTIONS) -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-gray-200 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🏷️</span>
                            <h3 class="text-lg font-black text-gray-900 font-display">Assigned Customized Labels</h3>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-pink-100 text-pink-800 border border-pink-200">Styles & Options</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Select the active customization labels (sleeves, necklines, feeding options, etc.) that buyers can choose for this fabric.</p>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button type="button" onclick="selectAllCustomizationLabels(true)" class="px-2.5 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Select All
                        </button>
                        <button type="button" onclick="selectAllCustomizationLabels(false)" class="px-2.5 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Deselect All
                        </button>
                        <button type="button" onclick="openCustomizationLabelModal()" class="px-3.5 py-1.5 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white text-xs font-bold rounded-lg shadow-2xs hover:shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Quick Add Label</span>
                        </button>
                    </div>
                </div>

                <!-- Labels Container / Grid -->
                <div id="customization-labels-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php if (!empty($customizationLabels)): ?>
                        <?php foreach ($customizationLabels as $lbl): 
                            $isLblChecked = in_array($lbl['id'], $selectedLabelIds ?? []);
                        ?>
                        <label class="relative cursor-pointer flex items-start gap-3 p-3.5 rounded-xl border border-gray-200 hover:border-gray-300 bg-white has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40 has-[:checked]:ring-1 has-[:checked]:ring-brand-500 transition-all shadow-2xs select-none">
                            <input type="checkbox" name="customization_labels[]" value="<?= $lbl['id'] ?>" <?= $isLblChecked ? 'checked' : '' ?> class="mt-0.5 w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 customization-label-checkbox">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <?php if (!empty($lbl['group_name'])): ?>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-700"><?= htmlspecialchars($lbl['group_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($lbl['sub_group'])): ?>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200"><?= htmlspecialchars($lbl['sub_group']) ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs font-bold text-gray-900"><?= htmlspecialchars($lbl['name']) ?></span>
                                </div>
                                <?php if (!empty($lbl['description'])): ?>
                                <p class="text-[11px] text-gray-500 mt-1 line-clamp-2"><?= htmlspecialchars($lbl['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="customization-labels-empty" class="<?= !empty($customizationLabels) ? 'hidden' : 'block' ?> text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <p class="text-xs text-gray-500 font-medium">No customized labels found.</p>
                    <button type="button" onclick="openCustomizationLabelModal()" class="mt-2.5 inline-flex items-center gap-1 text-xs font-bold text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-1.5 rounded-lg border border-pink-200 transition-colors">
                        <span>+ Create First Label</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
        function selectAllCustomizationLabels(checked) {
            document.querySelectorAll('.customization-label-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        }
        </script>
        <?php endif; ?>

        <div class="pt-6 border-t border-gray-100 flex justify-end space-x-4">
            <a href="<?= BASE_URL ?>/admin/catalog/products<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 transition-colors"><?= !empty($isCustomize) ? 'Save Fabric' : 'Save Product' ?></button>
        </div>
    </form>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const attributesContainer = document.getElementById('attributes-container');
    const noAttributesCheckbox = document.getElementById('no_attributes_checkbox');
    const variantManagementSection = document.getElementById('variant-management-section');

    function toggleNoAttributes() {
        if (!noAttributesCheckbox) return;
        
        if (noAttributesCheckbox.checked) {
            attributesContainer.style.display = 'none';
            if (variantManagementSection) {
                variantManagementSection.style.display = 'block';
                const header = variantManagementSection.querySelector('h3');
                if(header) header.innerHTML = '📦 Product Details <span class="text-red-500">*</span>';
                
                const variantInputs = variantManagementSection.querySelectorAll('input, select, textarea, button');
                variantInputs.forEach(input => input.disabled = false);
                
                if (typeof renderVariantGrid === 'function') {
                    // Generate a single default variant card
                    renderVariantGrid([[]]);
                    // Hide the refresh variants button since it's a simple product
                    const refreshBtn = document.getElementById('btn-refresh-variants');
                    if (refreshBtn) refreshBtn.style.display = 'none';
                }
            }
            const attrInputs = attributesContainer.querySelectorAll('input, select, textarea');
            attrInputs.forEach(input => input.disabled = true);
        } else {
            attributesContainer.style.display = 'block';
            if (variantManagementSection) {
                const header = variantManagementSection.querySelector('h3');
                if(header) header.innerHTML = '📑 Variant Management <span class="text-red-500">*</span>';
                
                const variantInputs = variantManagementSection.querySelectorAll('input, select, textarea, button');
                variantInputs.forEach(input => input.disabled = false);
                
                // Hide grid and show initial state
                const gridContainer = document.getElementById('variant-grid-container');
                if(gridContainer) gridContainer.style.display = 'none';
                const initialState = document.getElementById('variant-initial-state');
                if(initialState) initialState.style.display = 'block';
            }
            const attrInputs = attributesContainer.querySelectorAll('input, select, textarea');
            attrInputs.forEach(input => input.disabled = false);
            
            fetchAttributes();
        }
    }

    if (noAttributesCheckbox) {
        noAttributesCheckbox.addEventListener('change', toggleNoAttributes);
    }

    let currentAttributes = [];

    function escapeHtml(unsafe) {
        if (unsafe === 0 || unsafe === '0') return '0';
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    function getColorHex(colorName) {
        const colors = {
            'red': '#ef4444', 'blue': '#3b82f6', 'green': '#22c55e', 
            'yellow': '#eab308', 'orange': '#f97316', 'purple': '#a855f7',
            'pink': '#ec4899', 'black': '#000000', 'white': '#ffffff',
            'gray': '#6b7280', 'brown': '#D9A05B', 'nude': '#E5D3B3', 'wood': '#A06D41'
        };
        const key = (colorName || '').toLowerCase().trim();
        return colors[key] || '#D9A05B'; 
    }

    function captureCurrentAttributeValues() {
        const liveValues = {};
        if (!attributesContainer) return liveValues;

        // Checkboxes (colors, chips, picklists)
        attributesContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            const name = cb.name; // e.g. attributes[12][] or attributes[12]
            const match = name.match(/attributes\[(\d+)\]/);
            if (match) {
                const attrId = match[1];
                if (!liveValues[attrId]) liveValues[attrId] = { picklistIds: [], text: '', checked: false };
                if (cb.checked) {
                    if (name.includes('[]')) {
                        liveValues[attrId].picklistIds.push(String(cb.value));
                    } else {
                        liveValues[attrId].checked = true;
                    }
                }
            }
        });

        // Select dropdowns / multiselects
        attributesContainer.querySelectorAll('select').forEach(sel => {
            const name = sel.name;
            const match = name.match(/attributes\[(\d+)\]/);
            if (match) {
                const attrId = match[1];
                if (!liveValues[attrId]) liveValues[attrId] = { picklistIds: [], text: '', checked: false };
                if (sel.multiple) {
                    Array.from(sel.selectedOptions).forEach(opt => {
                        if (opt.value) liveValues[attrId].picklistIds.push(String(opt.value));
                    });
                } else if (sel.value) {
                    liveValues[attrId].picklistIds.push(String(sel.value));
                }
            }
        });

        // Text inputs, textareas, datepickers, numbers
        attributesContainer.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea').forEach(inp => {
            const name = inp.name;
            const match = name.match(/attributes\[(\d+)\]/);
            if (match) {
                const attrId = match[1];
                if (!liveValues[attrId]) liveValues[attrId] = { picklistIds: [], text: '', checked: false };
                liveValues[attrId].text = inp.value;
            }
        });

        return liveValues;
    }

    function fetchAttributes() {
        if (noAttributesCheckbox && noAttributesCheckbox.checked) return;
        const catId = categorySelect.value;
        const subCatId = subCategorySelect.value;
        
        if (!catId) {
            attributesContainer.innerHTML = '<p class="text-sm text-gray-500 italic">Select a category to view available attributes.</p>';
            document.getElementById('variant-management-section').style.display = 'none';
            return;
        }

        // Capture already-selected values in the DOM before reloading so they are not wiped out
        const savedLiveValues = captureCurrentAttributeValues();

        attributesContainer.innerHTML = '<p class="text-sm text-gray-500 italic">Loading attributes...</p>';

        let url = BASE_URL + '/admin/catalog/attributes/api-get-by-category?category_id=' + catId;
        if (subCatId) url += '&sub_category_id=' + subCatId;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (noAttributesCheckbox && noAttributesCheckbox.checked) return;
                currentAttributes = data || [];
                let hasVariants = false;

                if (currentAttributes.length === 0) {
                    attributesContainer.innerHTML = `
                        <div class="bg-pink-50/60 border border-dashed border-pink-200 rounded-2xl p-6 text-center">
                            <div class="w-10 h-10 bg-pink-100 text-[#F25996] rounded-full flex items-center justify-center mx-auto mb-2 font-bold text-lg">✨</div>
                            <p class="text-sm font-bold text-gray-800 mb-1">No custom specifications configured for this category yet.</p>
                            <p class="text-xs text-gray-500 mb-4 max-w-md mx-auto">Add attributes like Size, Color, or Material to generate product variants, or check "No Attributes (Simple Product)".</p>
                            <button type="button" onclick="openAttributeModal()" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-xs font-bold shadow-sm transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                + Add Specification Attribute
                            </button>
                        </div>
                    `;
                    document.getElementById('variant-management-section').style.display = 'none';
                    return;
                }
                
                let html = '<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">';
                
                currentAttributes.forEach(attr => {
                    // Check if it's a variant attribute or has choices
                    if (attr.is_variant == 1 || attr.input_type === 'colorpicker' || attr.input_type === 'checkbox' || attr.input_type === 'dropdown' || attr.input_type === 'multiselect' || (attr.values && attr.values.length > 0)) {
                        hasVariants = true;
                    }

                    // Only show on product form
                    if (attr.show_on_product_form == 0) return;

                    const savedAttr = savedLiveValues[attr.id];
                    const savedPicklist = savedAttr ? savedAttr.picklistIds.map(String) : [];
                    const savedText = savedAttr ? savedAttr.text : '';

                    html += `<div><label class="block text-sm font-medium text-gray-900 mb-1">${escapeHtml(attr.name)} ${attr.is_required ? '<span class="text-red-500">*</span>' : ''}</label>`;
                    if(attr.description) {
                        html += `<p class="text-xs text-gray-500 mb-2">${escapeHtml(attr.description)}</p>`;
                    }
                    
                    let req = attr.is_required ? 'required' : '';
                    let baseClass = "w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2";
                    
                    let nameSingle = `attributes[${attr.id}]`;
                    let nameMultiple = `attributes[${attr.id}][]`;
                    
                    let inputDataAttr = `data-attr-id="${attr.id}"`;

                    switch(attr.input_type) {
                        case 'textbox':
                        case 'fileurl':
                        case 'imageurl':
                            html += `<input type="text" name="${nameSingle}" value="${escapeHtml(savedText)}" ${inputDataAttr} ${req} class="${baseClass} attr-input">`;
                            break;
                        case 'number':
                            html += `<input type="text" step="any" name="${nameSingle}" value="${escapeHtml(savedText)}" ${inputDataAttr} ${req} class="${baseClass} attr-input">`;
                            break;
                        case 'colorpicker':
                            html += `<div class="flex flex-wrap gap-2.5 p-3 border border-gray-200 rounded-xl bg-gray-50/80 attr-input" ${inputDataAttr}>`;
                            if(attr.values) {
                                attr.values.forEach(v => {
                                    let colorHex = v.color_code || getColorHex(v.value);
                                    let isChecked = savedPicklist.includes(String(v.id)) ? 'checked' : '';
                                    html += `
                                    <label class="relative cursor-pointer flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-gray-200 hover:border-gray-400 has-[:checked]:border-[#c9080d] has-[:checked]:bg-[#fdf4f5] has-[:checked]:ring-1 has-[:checked]:ring-[#c9080d] transition-all shadow-xs select-none" title="${escapeHtml(v.value)} (${colorHex})">
                                        <input type="checkbox" name="${nameMultiple}" value="${v.id}" data-text="${escapeHtml(v.value)}" data-color="${colorHex}" ${isChecked} class="sr-only variant-checkbox">
                                        <span class="w-4 h-4 rounded-full border border-gray-300 shadow-xs shrink-0" style="background-color: ${colorHex}"></span>
                                        <span class="text-xs font-semibold text-gray-800">${escapeHtml(v.value)}</span>
                                    </label>`;
                                });
                            }
                            html += `</div>`;
                            break;
                        case 'datepicker':
                            html += `<input type="date" name="${nameSingle}" value="${escapeHtml(savedText)}" ${inputDataAttr} ${req} class="${baseClass} attr-input">`;
                            break;
                        case 'textarea':
                            html += `<textarea name="${nameSingle}" ${inputDataAttr} ${req} class="${baseClass} attr-input" rows="3">${escapeHtml(savedText)}</textarea>`;
                            break;
                        case 'dropdown':
                            html += `<select name="${nameSingle}" ${inputDataAttr} ${req} class="${baseClass} attr-input"><option value="">Select...</option>`;
                            if(attr.values) {
                                attr.values.forEach(v => {
                                    let isSelected = savedPicklist.includes(String(v.id)) ? 'selected' : '';
                                    html += `<option value="${v.id}" data-text="${escapeHtml(v.value)}" ${isSelected}>${escapeHtml(v.value)}</option>`;
                                });
                            }
                            html += `</select>`;
                            break;
                        case 'multiselect':
                            html += `<select name="${nameMultiple}" ${inputDataAttr} multiple ${req} class="${baseClass} h-32 attr-input">`;
                            if(attr.values) {
                                attr.values.forEach(v => {
                                    let isSelected = savedPicklist.includes(String(v.id)) ? 'selected' : '';
                                    html += `<option value="${v.id}" data-text="${escapeHtml(v.value)}" ${isSelected}>${escapeHtml(v.value)}</option>`;
                                });
                            }
                            html += `</select>`;
                            break;
                        case 'checkbox':
                            html += `<div class="space-y-2 max-h-40 overflow-y-auto p-3 border border-gray-100 rounded-lg bg-gray-50 attr-input" ${inputDataAttr}>`;
                            if(attr.values) {
                                attr.values.forEach(v => {
                                    let isChecked = savedPicklist.includes(String(v.id)) ? 'checked' : '';
                                    html += `<label class="flex items-center space-x-3 cursor-pointer">
                                                <input type="checkbox" name="${nameMultiple}" value="${v.id}" data-text="${escapeHtml(v.value)}" ${isChecked} class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 variant-checkbox">
                                                <span class="text-sm text-gray-700">${escapeHtml(v.value)}</span>
                                             </label>`;
                                });
                            }
                            html += `</div>`;
                            break;
                        case 'toggle':
                            let isToggled = (savedAttr && savedAttr.checked) ? 'checked' : '';
                            html += `<label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="${nameSingle}" ${inputDataAttr} value="1" ${isToggled} class="sr-only peer attr-input">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600 relative"></div>
                                     </label>`;
                            break;
                        default:
                            html += `<input type="text" name="${nameSingle}" value="${escapeHtml(savedText)}" ${inputDataAttr} ${req} class="${baseClass} attr-input">`;
                    }
                    
                    html += `</div>`;
                });
                
                html += '</div>';
                attributesContainer.innerHTML = html;

                if (hasVariants) {
                    document.getElementById('variant-management-section').style.display = 'block';
                } else if (!noAttributesCheckbox.checked) {
                    document.getElementById('variant-management-section').style.display = 'none';
                }

                // Add event listeners to auto-generate variants when options are selected or changed
                const triggerVariantSync = () => {
                    const hasChecked = document.querySelectorAll('.variant-checkbox:checked').length > 0;
                    const hasSelectVal = Array.from(document.querySelectorAll('select.attr-input')).some(s => s.value !== '');
                    if (hasChecked || hasSelectVal) {
                        document.getElementById('btn-generate-variants').click();
                    } else {
                        document.getElementById('variant-grid-container').style.display = 'none';
                        document.getElementById('variant-initial-state').style.display = 'block';
                    }
                };

                document.querySelectorAll('.variant-checkbox').forEach(cb => {
                    cb.addEventListener('change', triggerVariantSync);
                });
                document.querySelectorAll('select.attr-input').forEach(sel => {
                    sel.addEventListener('change', triggerVariantSync);
                });

                // If user had existing selections, preserve active sync
                const anyChecked = document.querySelectorAll('.variant-checkbox:checked').length > 0;
                if (anyChecked) {
                    triggerVariantSync();
                }
            })
            .catch(err => {
                console.error(err);
                attributesContainer.innerHTML = '<p class="text-sm text-red-500 italic">Error loading attributes.</p>';
            });
    }
    window.fetchAttributes = fetchAttributes;

    function updateCategoryGstRates() {
        if (!categorySelect) return;
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const sgst = selectedOption ? parseFloat(selectedOption.getAttribute('data-sgst')) || 0 : 0;
        const cgst = selectedOption ? parseFloat(selectedOption.getAttribute('data-cgst')) || 0 : 0;
        const totalGst = sgst + cgst;

        document.querySelectorAll('.variant-sgst').forEach(el => el.value = sgst.toFixed(2));
        document.querySelectorAll('.variant-cgst').forEach(el => el.value = cgst.toFixed(2));
        document.querySelectorAll('.variant-total-gst').forEach(el => el.value = totalGst.toFixed(2));

        document.querySelectorAll('.variant-base-price').forEach(el => calculateGst(el));
    }

    function filterSizeCharts(catId, subCatId) {
        const sizeChartSelect = document.getElementById('size_chart_id');
        const hintEl = document.getElementById('size_chart_hint');
        if (!sizeChartSelect) return;

        const options = sizeChartSelect.querySelectorAll('option[data-category-id]');
        let matchCount = 0;
        let subMatchCount = 0;

        options.forEach(opt => {
            const chartCatId = opt.getAttribute('data-category-id');
            const chartSubCatId = opt.getAttribute('data-subcategory-id') || '0';

            const matchesSub = subCatId && (chartSubCatId === String(subCatId));
            const matchesCat = catId && (chartCatId === String(catId));
            const isGlobal = !chartCatId || chartCatId === '0';

            if (matchesSub) {
                opt.style.display = '';
                subMatchCount++;
                matchCount++;
            } else if (matchesCat || isGlobal) {
                opt.style.display = '';
                if (matchesCat) matchCount++;
            } else {
                opt.style.display = 'none';
            }
        });

        if (subCatId && subMatchCount > 0) {
            const currentSelected = sizeChartSelect.querySelector(`option[value="${sizeChartSelect.value}"]`);
            if (!sizeChartSelect.value || (currentSelected && currentSelected.style.display === 'none')) {
                const matchedSubOpt = sizeChartSelect.querySelector(`option[data-subcategory-id="${subCatId}"]`);
                if (matchedSubOpt) {
                    sizeChartSelect.value = matchedSubOpt.value;
                }
            }
        }

        if (hintEl) {
            if (subCatId && subMatchCount > 0) {
                hintEl.innerHTML = `<span class="text-emerald-600 font-semibold">✓ ${subMatchCount} size chart(s) specifically tailored for this subcategory.</span>`;
            } else if (catId && matchCount > 0) {
                hintEl.innerHTML = `<span class="text-blue-600 font-semibold">✓ ${matchCount} size chart(s) available for this category.</span>`;
            } else {
                hintEl.innerHTML = `<span class="text-gray-500">Auto-filters to match selected Category and Subcategory.</span>`;
            }
        }
    }

    categorySelect.addEventListener('change', function() {
        updateCategoryGstRates();
        const catId = this.value;
        filterSizeCharts(catId, subCategorySelect ? subCategorySelect.value : '');
        
        if (!catId) {
            subCategorySelect.innerHTML = '<option value="">Select Category first...</option>';
            subCategorySelect.disabled = true;
            fetchAttributes();
            return;
        }

        subCategorySelect.disabled = true;
        subCategorySelect.innerHTML = '<option value="">Loading...</option>';

        fetch(BASE_URL + '/admin/catalog/subcategories/api-get-by-category?category_id=' + catId)
            .then(res => res.json())
            .then(data => {
                subCategorySelect.innerHTML = '<option value="">None / General</option>';
                data.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    subCategorySelect.appendChild(option);
                });
                subCategorySelect.disabled = false;
                
                fetchAttributes();
                filterSizeCharts(catId, subCategorySelect.value);
            })
            .catch(err => {
                console.error(err);
                subCategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
            });
    });

    subCategorySelect.addEventListener('change', function() {
        fetchAttributes();
        filterSizeCharts(categorySelect ? categorySelect.value : '', this.value);
    });

    // Variant Generation Logic
    const btnGenerateVariants = document.getElementById('btn-generate-variants');
    const variantGridContainer = document.getElementById('variant-grid-container');
    const variantInitialState = document.getElementById('variant-initial-state');

    function cartesianProduct(arrays) {
        if (arrays.length === 0) return [[]];
        return arrays.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())));
    }

    btnGenerateVariants.addEventListener('click', function() {
        let selectedVariants = [];
        
        currentAttributes.forEach(attr => {
            let selected = [];
            if (attr.input_type === 'checkbox' || attr.input_type === 'colorpicker') {
                const checkboxes = document.querySelectorAll(`input[name="attributes[${attr.id}][]"]:checked`);
                checkboxes.forEach(cb => {
                    selected.push({ id: cb.value, text: cb.getAttribute('data-text') || cb.value });
                });
            } else if (attr.input_type === 'dropdown') {
                const sel = document.querySelector(`select[name="attributes[${attr.id}]"]`);
                if (sel && sel.value) {
                    const opt = sel.options[sel.selectedIndex];
                    selected.push({ id: sel.value, text: opt.getAttribute('data-text') || opt.text || sel.value });
                }
            } else if (attr.input_type === 'multiselect') {
                const sel = document.querySelector(`select[name="attributes[${attr.id}][]"]`);
                if (sel) {
                    Array.from(sel.selectedOptions).forEach(opt => {
                        if (opt.value) {
                            selected.push({ id: opt.value, text: opt.getAttribute('data-text') || opt.text || opt.value });
                        }
                    });
                }
            }

            if (selected.length > 0) {
                selectedVariants.push({
                    attrId: attr.id,
                    attrName: attr.name,
                    selected: selected
                });
            }
        });

        if (selectedVariants.length === 0) {
            alert('Please select at least one variant option (e.g. check a color).');
            return;
        }

        let arraysToCombine = selectedVariants.map(v => v.selected.map(opt => ({
            attrId: v.attrId,
            attrName: v.attrName,
            optId: opt.id,
            optText: opt.text
        })));
        
        let combinations = cartesianProduct(arraysToCombine);
        
        renderVariantGrid(combinations);
    });

    function renderVariantGrid(combinations) {
        variantInitialState.style.display = 'none';
        variantGridContainer.style.display = 'block';
        
        let slugVal = document.querySelector('input[name="slug"]').value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        let baseSku = slugVal.substring(0, 6) || "PRD";
        let batchId = Math.floor(100 + Math.random() * 900);

        let html = `
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-end gap-4">
                    <button type="button" id="btn-refresh-variants" class="px-4 py-1.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 shadow-sm">REFRESH VARIANTS</button>
                </div>
                <div class="p-6 space-y-6">
        `;

        const selectedCatOption = categorySelect ? categorySelect.options[categorySelect.selectedIndex] : null;
        const currentSgst = selectedCatOption ? (parseFloat(selectedCatOption.getAttribute('data-sgst')) || 0).toFixed(2) : '0.00';
        const currentCgst = selectedCatOption ? (parseFloat(selectedCatOption.getAttribute('data-cgst')) || 0).toFixed(2) : '0.00';
        const currentTotalGst = (parseFloat(currentSgst) + parseFloat(currentCgst)).toFixed(2);

        combinations.forEach((combo, index) => {
            let comboArr = Array.isArray(combo) ? combo : (combo ? [combo] : []);
            
            let isSimple = comboArr.length === 0;
            let variantName = isSimple ? 'Default Product' : comboArr.map(c => c.optText).join(' - ');
            let variantSku = baseSku + (isSimple ? '-DEFAULT' : ('-' + comboArr.map(c => c.optText.substring(0,3).toUpperCase()).join('') + '-' + (index + 1) + '-' + batchId));
            
            html += `
                <div class="border border-gray-200 rounded-xl overflow-hidden relative variant-card">
                    <div class="absolute top-4 right-4 flex items-center space-x-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        <button type="button" onclick="this.closest('.variant-card').remove(); if(document.querySelectorAll('.variant-card').length === 0){ document.getElementById('variant-grid-container').style.display='none'; document.getElementById('variant-initial-state').style.display='block'; }" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Remove variant">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center space-x-3">
                        <h4 class="text-lg font-bold text-gray-900 font-display">${variantName}</h4>
                        ${index === 0 ? '<span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded">PRIMARY</span>' : ''}
                    </div>
                    <div class="p-6 flex flex-col gap-6 sm:flex-row">
                        <!-- Inputs Grid -->
                        <div class="flex-1 grid grid-cols-2 gap-x-4 gap-y-4 variant-card-container" style="min-width:0">
                            <!-- Hidden combo data -->
                            <input type="hidden" name="variants[${index}][name]" value="${variantName}">
                            ${comboArr.map(c => `<input type="hidden" name="variants[${index}][attributes][${c.attrId}]" value="${c.optId}">`).join('')}

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Base Price (Incl. GST) <span class="text-red-500">*</span></label>
                                <input type="text" step="0.01" min="0.01" name="variants[${index}][base_price]" id="base_price_${index}" value="" placeholder="e.g. 999.00" required oninput="calculateGst(this)" class="variant-price variant-base-price w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Discount Price (Incl. GST)</label>
                                <input type="text" step="0.01" min="0.01" name="variants[${index}][discount_price]" oninput="calculateGst(this)" class="variant-discount-price w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm" placeholder="e.g. 899.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">SGST (%)</label>
                                <input type="text" step="0.01" name="variants[${index}][sgst]" value="${currentSgst}" readonly class="variant-sgst w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 cursor-not-allowed text-gray-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">CGST (%)</label>
                                <input type="text" step="0.01" name="variants[${index}][cgst]" value="${currentCgst}" readonly class="variant-cgst w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 cursor-not-allowed text-gray-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Total GST (%)</label>
                                <input type="text" step="0.01" name="variants[${index}][total_gst]" value="${currentTotalGst}" readonly class="variant-total-gst w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 cursor-not-allowed font-semibold text-gray-700">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Total Amount (Incl. GST)</label>
                                <input type="text" step="0.01" name="variants[${index}][max_amount]" value="" readonly class="variant-total-amount w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 cursor-not-allowed font-bold text-brand-700">
                            </div>
                            ${<?= !empty($isCustomize) ? 'true' : 'false' ?> ? `
                            <!-- Variant Fabric Rules (Base & Max Qty only) -->
                            <div class="col-span-2 bg-gradient-to-r from-amber-50/80 to-pink-50/50 p-3.5 rounded-xl border border-amber-200/90 shadow-2xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                        <span>📏 Fabric Rules (Variant Limits)</span>
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">Per-Variant Rule</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-amber-900 mb-1">Base Qty (Pcs) <span class="text-red-500">*</span></label>
                                        <input type="number" min="1" name="variants[${index}][base_quantity]" value="2" required placeholder="e.g. 2" class="w-full border border-amber-300 rounded-lg focus:ring-amber-500 focus:border-amber-500 px-3 py-1.5 text-sm bg-white font-bold text-gray-800">
                                        <span class="text-[10px] text-gray-500">Base pieces per set</span>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-amber-900 mb-1">Max Allowed (Pcs) <span class="text-red-500">*</span></label>
                                        <input type="number" min="1" name="variants[${index}][max_quantity]" value="4" required placeholder="e.g. 4" class="w-full border border-amber-300 rounded-lg focus:ring-amber-500 focus:border-amber-500 px-3 py-1.5 text-sm bg-white font-bold text-gray-800">
                                        <span class="text-[10px] text-gray-500">Max limit per multiplier</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="variants[${index}][fabric_consumption]" value="1.00">
                            ` : `
                            <input type="hidden" name="variants[${index}][base_quantity]" value="2">
                            <input type="hidden" name="variants[${index}][max_quantity]" value="4">
                            <input type="hidden" name="variants[${index}][fabric_consumption]" value="1.00">
                            `}

                            ${!<?= !empty($isCustomize) ? 'true' : 'false' ?> ? `
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Inventory (Qty) <span class="text-red-500">*</span></label>
                                <input type="text" min="0" name="variants[${index}][inventory]" value="" placeholder="e.g. 50" required class="variant-inv w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Weight (kg) <span class="text-red-500">*</span></label>
                                <input type="text" step="0.0001" min="0.0001" name="variants[${index}][weight]" value="" placeholder="e.g. 0.5000" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm">
                            </div>
                            ` : ''}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Low Stock Alert <span class="text-red-500">*</span></label>
                                <input type="text" min="0" name="variants[${index}][low_stock_alert]" value="5" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                                <input type="text" name="variants[${index}][sku]" value="${variantSku}" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5 text-sm font-mono">
                            </div>
                        </div>

                        <!-- Images Upload -->
                        <div class="border-t sm:border-t-0 sm:border-l border-gray-100 pt-4 sm:pt-0 sm:pl-6" style="width:210px;flex-shrink:0">
                            <label class="block text-sm font-medium text-gray-900 mb-2 border-b border-gray-100 pb-2">Variant Images</label>
                            
                            <button type="button" onclick="openMediaSelector((rawUrl, fullUrl) => addVariantImage(${index}, rawUrl, fullUrl))" class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 mb-3 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Add Image
                            </button>
                            
                            <div id="variant-image-inputs-${index}"></div>
                            <div id="variant-image-preview-${index}" class="mt-3 grid grid-cols-2 gap-2"></div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
        
        variantGridContainer.innerHTML = html;

        // Run calculation on each variant
        document.querySelectorAll('.variant-base-price').forEach(el => calculateGst(el));

        // Sync fabric sizes when variants are rendered
        if (typeof syncFabricSizesFromVariants === 'function') {
            syncFabricSizesFromVariants();
        }

        // Re-attach refresh listener
        document.getElementById('btn-refresh-variants').addEventListener('click', () => {
            btnGenerateVariants.click();
        });
    }

    window.calculateGst = function(inputEl) {
        const container = inputEl.closest('.variant-card-container') || inputEl.closest('.grid') || inputEl.closest('tr') || inputEl.closest('.p-6');
        if (!container) return;

        const basePriceEl = container.querySelector('.variant-base-price') || container.querySelector('input[name*="[base_price]"]');
        const discountPriceEl = container.querySelector('.variant-discount-price') || container.querySelector('input[name*="[discount_price]"]');
        const sgstEl = container.querySelector('.variant-sgst') || container.querySelector('input[name*="[sgst]"]');
        const cgstEl = container.querySelector('.variant-cgst') || container.querySelector('input[name*="[cgst]"]');
        const totalGstEl = container.querySelector('.variant-total-gst') || container.querySelector('input[name*="[total_gst]"]');
        const totalAmountEl = container.querySelector('.variant-total-amount') || container.querySelector('input[name*="[max_amount]"]');

        const basePrice = basePriceEl ? parseFloat(basePriceEl.value) || 0 : 0;
        const discountPrice = discountPriceEl ? parseFloat(discountPriceEl.value) || 0 : 0;
        const sgst = sgstEl ? parseFloat(sgstEl.value) || 0 : 0;
        const cgst = cgstEl ? parseFloat(cgstEl.value) || 0 : 0;
        const totalGst = sgst + cgst;

        if (totalGstEl) {
            totalGstEl.value = totalGst.toFixed(2);
        }

        // Validate discount price < base price
        if (discountPriceEl) {
            let errorMsgEl = container.querySelector('.price-discount-error');
            if (discountPrice > 0 && discountPrice >= basePrice) {
                discountPriceEl.classList.add('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50/50');
                if (!errorMsgEl) {
                    errorMsgEl = document.createElement('p');
                    errorMsgEl.className = 'price-discount-error text-[11px] text-red-600 font-semibold mt-1';
                    discountPriceEl.parentNode.appendChild(errorMsgEl);
                }
                errorMsgEl.textContent = `Discount Price must be less than Base Price (₹${basePrice.toFixed(2)})`;
            } else {
                discountPriceEl.classList.remove('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50/50');
                if (errorMsgEl) {
                    errorMsgEl.remove();
                }
            }
        }

        const effectivePrice = (discountPrice > 0 && discountPrice < basePrice) ? discountPrice : basePrice;
        const totalAmount = effectivePrice;

        if (totalAmountEl) {
            totalAmountEl.value = totalAmount > 0 ? totalAmount.toFixed(2) : '';
        }
    };

    window.setFabricSlot = function(slotIndex, rawUrl, fullUrl) {
        const displayUrl = fullUrl || ((rawUrl.startsWith('http://') || rawUrl.startsWith('https://')) ? rawUrl : ('<?= BASE_URL ?>/' + rawUrl.replace(/^\/+/, '')));
        const input = document.getElementById('fabric_slot_input_' + slotIndex);
        const prevWrap = document.getElementById('fabric_slot_prev_wrap_' + slotIndex);
        const emptyWrap = document.getElementById('fabric_slot_empty_wrap_' + slotIndex);
        const img = document.getElementById('fabric_slot_img_' + slotIndex);

        if (input) input.value = rawUrl;
        if (img) img.src = displayUrl;
        if (emptyWrap) {
            emptyWrap.classList.add('hidden');
            emptyWrap.classList.remove('flex');
        }
        if (prevWrap) {
            prevWrap.classList.remove('hidden');
            prevWrap.classList.add('block');
        }
    };

    window.removeFabricSlot = function(slotIndex, productId) {
        const input = document.getElementById('fabric_slot_input_' + slotIndex);
        const prevWrap = document.getElementById('fabric_slot_prev_wrap_' + slotIndex);
        const emptyWrap = document.getElementById('fabric_slot_empty_wrap_' + slotIndex);

        if (input) input.value = '';
        if (prevWrap) {
            prevWrap.classList.add('hidden');
            prevWrap.classList.remove('block');
        }
        if (emptyWrap) {
            emptyWrap.classList.remove('hidden');
            emptyWrap.classList.add('flex');
        }
    };

    window.uploadNextFabricImage = function() {
        for (let i = 0; i < 5; i++) {
            const input = document.getElementById('fabric_slot_input_' + i);
            if (input && !input.value.trim()) {
                openMediaSelector((rawUrl, fullUrl) => {
                    window.setFabricSlot(i, rawUrl, fullUrl);
                });
                return;
            }
        }
        alert('All 5 image slots are currently filled. Use the "Change" or "Remove" buttons on an existing slot to replace an image.');
    };

    window.addVariantImage = function(index, rawUrl, fullUrl) {
        const inputsContainer = document.getElementById('variant-image-inputs-' + index);
        const previewContainer = document.getElementById('variant-image-preview-' + index);
        
        if (inputsContainer && inputsContainer.children.length >= 4) {
            alert('Maximum 4 images allowed per variant.');
            return;
        }

        const inputId = 'img_' + Math.random().toString(36).substr(2, 9);
        const displayUrl = fullUrl || ((rawUrl.startsWith('http://') || rawUrl.startsWith('https://')) ? rawUrl : ('<?= BASE_URL ?>/' + rawUrl.replace(/^\/+/, '')));
        
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `variants[${index}][image_paths][]`;
        hiddenInput.value = rawUrl;
        hiddenInput.id = 'input_' + inputId;
        if (inputsContainer) inputsContainer.appendChild(hiddenInput);

        const div = document.createElement('div');
        div.className = 'relative group aspect-square rounded-lg overflow-hidden border border-gray-200';
        div.id = 'preview_' + inputId;
        div.innerHTML = `<img src="${displayUrl}" class="w-full h-full object-cover">
        <button type="button" onclick="removeVariantImage('${inputId}')" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors z-20">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>`;
        if (previewContainer) previewContainer.appendChild(div);
    };

    window.removeVariantImage = function(inputId) {
        const input = document.getElementById('input_' + inputId);
        const preview = document.getElementById('preview_' + inputId);
        if (input) input.remove();
        if (preview) preview.remove();
    };

    // Custom Dynamic Description Fields
    let customFieldCount = 0;
    window.addCustomField = function(fieldName = '', fieldValue = '') {
        const container = document.getElementById('custom-fields-container');
        if (!container) return;
        const index = customFieldCount++;
        const row = document.createElement('div');
        row.className = 'custom-field-row flex items-start gap-3 p-3.5 bg-gray-50/80 border border-gray-200 rounded-xl transition-all duration-200 hover:border-gray-300 shadow-2xs';
        row.id = `custom-field-${index}`;
        row.innerHTML = `
            <div class="w-1/3 min-w-[140px]">
                <input type="text" name="custom_fields[${index}][name]" value="${fieldName.replace(/"/g, '&quot;')}" placeholder="Field Name" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2 text-sm bg-white font-medium text-gray-800">
            </div>
            <div class="flex-1">
                <textarea name="custom_fields[${index}][value]" rows="2" placeholder="Field details / content..." class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2 text-sm bg-white text-gray-800 resize-y">${fieldValue}</textarea>
            </div>
            <button type="button" onclick="this.closest('.custom-field-row').remove()" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors mt-0.5" title="Delete Field">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        container.appendChild(row);
        const nameInput = row.querySelector('input');
        if (nameInput && !fieldName) {
            nameInput.focus();
        }
    };
    const productForm = document.getElementById('product-create-form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            const catSelect = document.getElementById('category_id');
            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            const moqInput = document.querySelector('input[name="moq_override"]');
            const descInput = document.querySelector('textarea[name="description"]');

            if (!catSelect || !catSelect.value) {
                e.preventDefault();
                alert('Please select a Category.');
                catSelect ? catSelect.focus() : null;
                return false;
            }

            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                alert('<?= !empty($isCustomize) ? 'Fabric Name' : 'Product Name' ?> is required.');
                nameInput ? nameInput.focus() : null;
                return false;
            }

            if (!slugInput || !slugInput.value.trim()) {
                e.preventDefault();
                alert('Slug is required.');
                slugInput ? slugInput.focus() : null;
                return false;
            }

            if (moqInput && (!moqInput.value || parseInt(moqInput.value) < 1)) {
                e.preventDefault();
                alert('MOQ is required and must be at least 1.');
                moqInput.focus();
                return false;
            }

            if (!descInput || !descInput.value.trim()) {
                e.preventDefault();
                alert('<?= !empty($isCustomize) ? 'Fabric Description' : 'Product Description' ?> is required.');
                descInput ? descInput.focus() : null;
                return false;
            }

            const variantCards = document.querySelectorAll('.variant-card-container');
            if (variantCards.length === 0) {
                e.preventDefault();
                alert('Please select specifications and click "GENERATE VARIANTS", or check "No Attributes (Simple Product)" to configure product details.');
                return false;
            }

            let hasError = false;
            variantCards.forEach((card, idx) => {
                if (hasError) return;
                const priceInput = card.querySelector('.variant-base-price');
                const weightInput = card.querySelector('input[name*="[weight]"]');
                const skuInput = card.querySelector('input[name*="[sku]"]');
                const invInput = card.querySelector('input[name*="[inventory]"]');

                const price = priceInput ? parseFloat(priceInput.value) : 0;
                const weight = weightInput ? parseFloat(weightInput.value) : 0;
                const sku = skuInput ? skuInput.value.trim() : '';
                const inv = invInput ? invInput.value.trim() : '';

                if (!priceInput || isNaN(price) || price <= 0) {
                    e.preventDefault();
                    alert(`Base Price is required and must be greater than 0 for variant #${idx + 1}.`);
                    priceInput ? priceInput.focus() : null;
                    hasError = true;
                    return;
                }

                <?php if (empty($isCustomize)): ?>
                if (!weightInput || isNaN(weight) || weight <= 0) {
                    e.preventDefault();
                    alert(`Weight is required and must be greater than 0 kg for variant #${idx + 1}.`);
                    weightInput ? weightInput.focus() : null;
                    hasError = true;
                    return;
                }
                <?php endif; ?>

                if (!sku) {
                    e.preventDefault();
                    alert(`SKU is required for variant #${idx + 1}.`);
                    skuInput ? skuInput.focus() : null;
                    hasError = true;
                    return;
                }

                <?php if (empty($isCustomize)): ?>
                if (inv === '' || isNaN(parseInt(inv)) || parseInt(inv) < 0) {
                    e.preventDefault();
                    alert(`Inventory stock is required (0 or more) for variant #${idx + 1}.`);
                    invInput ? invInput.focus() : null;
                    hasError = true;
                    return;
                }
                <?php endif; ?>
            });

            if (hasError) {
                return false;
            }
        });
    }

    // Trigger change event if category is already selected (e.g., from browser autofill)
    if (categorySelect && categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
    // Fabric Size Variants & Limits JS Logic
    let fabricSizeIndex = 0;

    function escapeHtmlStr(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    window.addCustomFabricSizeRow = function(sizeName = '', baseQty = 2, maxQty = 4, enabled = true) {
        const tbody = document.getElementById('fabricSizesTbody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.className = 'fabric-size-row group';
        tr.innerHTML = `
            <td class="py-2.5 px-3">
                <input type="text" name="fabric_sizes[${fabricSizeIndex}][size_name]" value="${escapeHtmlStr(sizeName)}" required placeholder="e.g. M" class="fabric-size-name-input w-28 px-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white font-bold text-gray-800">
            </td>
            <td class="py-2.5 px-3">
                <div class="flex items-center gap-2">
                    <input type="number" name="fabric_sizes[${fabricSizeIndex}][base_quantity]" value="${baseQty}" min="0" required class="fabric-size-base-qty w-28 px-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white font-bold text-gray-800" oninput="updateFabricVariantSummary()">
                    <span class="text-xs text-gray-400 font-semibold">pcs</span>
                </div>
            </td>
            <td class="py-2.5 px-3">
                <div class="flex items-center gap-2">
                    <input type="number" name="fabric_sizes[${fabricSizeIndex}][max_quantity]" value="${maxQty}" min="1" required class="fabric-size-max-qty w-28 px-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-[#F25996] focus:ring-1 focus:ring-[#F25996] font-bold text-[#F25996]" oninput="updateFabricVariantSummary()">
                    <span class="text-xs text-gray-400 font-semibold">max pcs</span>
                </div>
            </td>
            <td class="py-2.5 px-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="fabric_sizes[${fabricSizeIndex}][enabled]" value="1" ${enabled ? 'checked' : ''} class="sr-only peer" onchange="updateFabricVariantSummary()">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F25996]"></div>
                </label>
            </td>
            <td class="py-2.5 px-3 text-right">
                <button type="button" onclick="removeFabricSizeRow(this)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" title="Delete variant">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        fabricSizeIndex++;
        updateFabricVariantSummary();
    };

    window.removeFabricSizeRow = function(btn) {
        const rows = document.querySelectorAll('.fabric-size-row');
        if (rows.length <= 1) {
            alert('You must have at least one size variant configured.');
            return;
        }
        btn.closest('tr').remove();
        updateFabricVariantSummary();
    };

    window.clearAllFabricSizes = function() {
        if (confirm('Clear all size variant rows?')) {
            const tbody = document.getElementById('fabricSizesTbody');
            if (tbody) tbody.innerHTML = '';
            addCustomFabricSizeRow('M', 2, 4);
        }
    };

    window.applySizeVariantPreset = function(type) {
        const presets = {
            'standard_m_4xl': [
                { name: 'M', base: 2, max: 4 },
                { name: 'L', base: 2, max: 4 },
                { name: 'XL', base: 2, max: 4 },
                { name: 'XXL', base: 2, max: 4 },
                { name: '3XL', base: 2, max: 4 },
                { name: '4XL', base: 2, max: 4 }
            ],
            'all_s_xxl': [
                { name: 'S', base: 2, max: 4 },
                { name: 'M', base: 2, max: 4 },
                { name: 'L', base: 2, max: 4 },
                { name: 'XL', base: 2, max: 4 },
                { name: 'XXL', base: 2, max: 4 }
            ],
            'plus_3xl_6xl': [
                { name: '3XL', base: 2, max: 4 },
                { name: '4XL', base: 2, max: 4 },
                { name: '5XL', base: 2, max: 4 },
                { name: '6XL', base: 2, max: 4 }
            ],
            'freesize': [
                { name: 'Free Size', base: 2, max: 10 }
            ]
        };

        const items = presets[type];
        if (!items) return;

        const tbody = document.getElementById('fabricSizesTbody');
        if (tbody) tbody.innerHTML = '';
        fabricSizeIndex = 0;
        items.forEach(it => {
            addCustomFabricSizeRow(it.name, it.base, it.max, true);
        });
    };

    window.applyShorthandInput = function() {
        const input = document.getElementById('quickShorthandInput');
        if (!input) return;
        const val = input.value.trim();
        if (!val) {
            alert('Please enter shorthand sizes (e.g. M-2, L-2, XL-2, XXL-2, 3XL-2, 4XL-4)');
            return;
        }

        const tokens = val.split(/[,;\s]+/).map(t => t.trim()).filter(Boolean);
        if (tokens.length === 0) return;

        const tbody = document.getElementById('fabricSizesTbody');
        if (tbody) tbody.innerHTML = '';
        fabricSizeIndex = 0;

        tokens.forEach(tok => {
            let name = tok;
            let base = 2;
            let max = 4;

            if (tok.includes(':')) {
                const parts = tok.split(':');
                name = parts[0].trim();
                if (parts[1]) {
                    if (parts[1].includes('/')) {
                        const qParts = parts[1].split('/');
                        base = parseInt(qParts[0]) || 2;
                        max = parseInt(qParts[1]) || (base * 2);
                    } else {
                        base = parseInt(parts[1]) || 2;
                        max = base * 2;
                    }
                }
            } else if (tok.includes('-')) {
                const parts = tok.split('-');
                if (parts.length >= 3) {
                    name = parts.slice(0, -2).join('-');
                    base = parseInt(parts[parts.length - 2]) || 2;
                    max = parseInt(parts[parts.length - 1]) || (base * 2);
                } else if (parts.length === 2) {
                    name = parts[0].trim();
                    base = parseInt(parts[1]) || 2;
                    max = Math.max(4, base * 2);
                }
            }

            if (name) {
                addCustomFabricSizeRow(name.toUpperCase(), base, max, true);
            }
        });

        input.value = '';
    };

    window.updateFabricVariantSummary = function() {
        const rows = document.querySelectorAll('.fabric-size-row');
        let active = 0;
        let totalBase = 0;

        rows.forEach(r => {
            const chk = r.querySelector('input[type="checkbox"]');
            const baseNum = parseInt(r.querySelector('.fabric-size-base-qty')?.value) || 0;
            if (chk && chk.checked) {
                active++;
                totalBase += baseNum;
            }
        });

        const activeEl = document.getElementById('adminActiveVariantsCount');
        const totalEl = document.getElementById('adminTotalBasePieces');
        if (activeEl) activeEl.textContent = active;
        if (totalEl) totalEl.textContent = totalBase;
    };

    window.syncFabricSizesFromVariants = function() {
        const sizeNames = [];
        document.querySelectorAll('input[name^="variants["][name$="[name]"], input[name^="new_variants["][name$="[name]"]').forEach(inp => {
            const val = inp.value.trim();
            if (val && !sizeNames.includes(val)) {
                sizeNames.push(val);
            }
        });

        if (sizeNames.length === 0) {
            ['M', 'L', 'XL', 'XXL', '3XL', '4XL'].forEach(sz => sizeNames.push(sz));
        }

        const tbody = document.getElementById('fabricSizesTbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        fabricSizeIndex = 0;

        sizeNames.forEach(sz => {
            addCustomFabricSizeRow(sz, 2, 4, true);
        });
    };

    // Auto-populate initial sizes for customizable fabric
    <?php if (!empty($isCustomize)): ?>
    syncFabricSizesFromVariants();
    <?php endif; ?>
});

function selectAllCustomLabels(checked) {
    document.querySelectorAll('.fabric-label-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}
</script>
<?php include __DIR__ . '/../../media/_selector_modal.php'; ?>
<?php include __DIR__ . '/../_category_request_modal.php'; ?>
<?php include __DIR__ . '/../_attribute_request_modal.php'; ?>
<?php if (!empty($isCustomize)): ?>
<?php include __DIR__ . '/../_customization_label_modal.php'; ?>
<?php endif; ?>

