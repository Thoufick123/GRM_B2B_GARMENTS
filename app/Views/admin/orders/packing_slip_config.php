<?php
/**
 * Admin Packing Slip Configuration & Layout Customizer
 *
 * @var string $title
 * @var array $config
 * @var string $cmsLogo
 */
?>

<div class="w-full">
    <!-- Header Section (Matches Orders Management Layout) -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Order Management <span class="mx-1 text-gray-300">›</span> <a href="<?= BASE_URL ?>/admin/orders" class="hover:text-brand-700">Orders</a> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Packing Slip Configuration</span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 w-full">
            <div>
                <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Packing Slip Configuration</h2>
                <p class="text-sm text-gray-500 mt-1 font-medium">Configure company branding, GSTIN, and rearrange sections for 100mm × 100mm thermal labels.</p>
            </div>
            
            <div class="flex items-center space-x-3 sm:ml-auto">
                <a href="<?= BASE_URL ?>/admin/orders" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded-full flex items-center gap-2 shadow-xs transition active:scale-95 group">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>BACK TO ORDERS</span>
                </a>
                <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('packing_slip_config', 'edit') || $this->hasPermission('all_orders', 'edit')): ?>
                <button type="button" onclick="resetPackingSlipConfig()" class="bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded-full flex items-center gap-1.5 shadow-xs transition active:scale-95 cursor-pointer" title="Reset all settings to system defaults">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>RESET DEFAULTS</span>
                </button>
                <?php endif; ?>
                <?php if ($this->hasPermission('packing_slip_config', 'edit') || $this->hasPermission('all_orders', 'edit')): ?>
                <button type="button" onclick="submitPackingSlipConfig()" id="topSaveBtn" class="bg-[#F25996] hover:bg-[#d94480] text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-full flex items-center gap-1.5 shadow-xs transition active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>SAVE CONFIGURATION</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Grid: Left (Controls & Section Order) | Right (Live 100x100mm Preview) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Panel: Form Settings & Position Ordering (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <form id="packingSlipConfigForm" onsubmit="event.preventDefault(); submitPackingSlipConfig();" class="space-y-6">
                <input type="hidden" name="is_ajax" value="1">

                <!-- Card 1: Sender / Company Details -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-pink-50 text-[#F25996] flex items-center justify-center font-bold text-base">🏢</span>
                            <div>
                                <h3 class="font-black text-gray-900 text-base">Sender Company Details</h3>
                                <p class="text-xs text-gray-400">Shown in the "From" address section and header of the slip</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Company Info</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Company / Brand Name <span class="text-rose-500">*</span></label>
                            <input type="text" id="companyName" name="company_name" value="<?= htmlspecialchars($config['company_name']) ?>" required
                                   class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all"
                                   placeholder="e.g. GRM Garments">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Packing Slip Subtitle / Tagline</label>
                            <input type="text" id="slipSubtitle" name="subtitle" value="<?= htmlspecialchars($config['subtitle'] ?? 'FORWARDING WITH CARE') ?>"
                                   class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all uppercase"
                                   placeholder="e.g. FORWARDING WITH CARE">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Brand Logo URL</label>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center p-1 overflow-hidden shrink-0">
                                    <img id="logoPreviewThumb" src="<?= htmlspecialchars($config['logo_url'] ?? $cmsLogo) ?>" alt="Logo" class="max-w-full max-h-full object-contain <?= empty($config['logo_url'] ?? $cmsLogo) ? 'hidden' : '' ?>">
                                    <span id="logoPlaceholderIcon" class="text-xs text-gray-400 <?= !empty($config['logo_url'] ?? $cmsLogo) ? 'hidden' : '' ?>">🏷️</span>
                                </div>
                                <input type="text" id="logoUrlInput" name="logo_url" value="<?= htmlspecialchars($config['logo_url'] ?? $cmsLogo) ?>"
                                       class="flex-1 px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-mono text-gray-700 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all"
                                       placeholder="https://yourstore.com/logo.png">
                                <?php if (!empty($cmsLogo)): ?>
                                    <button type="button" onclick="document.getElementById('logoUrlInput').value = '<?= htmlspecialchars($cmsLogo) ?>'; updateLivePreview();" class="px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl shrink-0 transition-colors" title="Reset to current Store Navbar Logo">
                                        Use CMS Logo
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Dispatch / Warehouse Address <span class="text-rose-500">*</span></label>
                            <textarea id="companyAddress" name="address" rows="3" required
                                      class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all leading-relaxed"
                                      placeholder="Building, Street Address, City, State - Pincode"><?= htmlspecialchars($config['address']) ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Contact Phone Number</label>
                            <input type="text" id="companyPhone" name="phone" value="<?= htmlspecialchars($config['phone']) ?>"
                                   class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all"
                                   placeholder="+91 98765 43210">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Support Email Address</label>
                            <input type="email" id="companyEmail" name="email" value="<?= htmlspecialchars($config['email']) ?>"
                                   class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all"
                                   placeholder="orders@grmgarments.com">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">GSTIN Number</label>
                            <input type="text" id="companyGstin" name="gstin" value="<?= htmlspecialchars($config['gstin']) ?>"
                                   class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-mono font-semibold text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all uppercase"
                                   placeholder="33BFGPG7507C2ZW">
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="showGstin" name="show_gstin" value="1" <?= !empty($config['show_gstin']) ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F25996]"></div>
                            </label>
                            <span class="text-xs font-bold text-gray-700">Print GSTIN on Packing Slip</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Section Position Customizer (Format Position Customization) -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">↕️</span>
                            <div>
                                <h3 class="font-black text-gray-900 text-base">Section Positioning & Layout Order</h3>
                                <p class="text-xs text-gray-400">Rearrange the sections based on your dispatch and printing needs</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">Position Format</span>
                    </div>

                    <p class="text-xs text-gray-500 mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        💡 Use the <strong>▲ Up</strong> and <strong>▼ Down</strong> buttons or drag the handles to change where each section appears on the 100mm slip. The live preview on the right will update immediately.
                    </p>

                    <!-- Sortable Sections Container -->
                    <div id="sectionOrderList" class="space-y-2">
                        <?php
                        $sectionMeta = [
                            'header'    => ['title' => 'Company Header & Order Details', 'desc' => 'Brand logo, company name, GSTIN, Order ID, Date & Payment', 'icon' => '🏷️'],
                            'addresses' => ['title' => 'Shipping Addresses (From & To)', 'desc' => 'Sender dispatch address and Customer destination address', 'icon' => '📍'],
                            'notes'     => ['title' => 'Footer Terms / Declaration', 'desc' => 'Compliance declaration and support note', 'icon' => '✍️']
                        ];
                        
                        $orderedSections = $config['section_order'] ?? array_keys($sectionMeta);
                        foreach ($orderedSections as $idx => $secKey):
                            if (!isset($sectionMeta[$secKey])) continue;
                            $meta = $sectionMeta[$secKey];
                        ?>
                        <div class="section-item flex items-center justify-between p-3.5 bg-gray-50 hover:bg-pink-50/40 border border-gray-200/90 rounded-xl transition-all select-none cursor-grab active:cursor-grabbing hover:shadow-xs" draggable="true" data-section="<?= $secKey ?>">
                            <input type="hidden" name="section_order[]" value="<?= $secKey ?>" class="section-order-input">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-400 hover:text-gray-700 font-bold text-base cursor-grab active:cursor-grabbing select-none" title="Drag to reorder">⋮⋮</span>
                                <span class="w-5 h-5 rounded-full bg-pink-100 text-[#F25996] font-black text-[11px] flex items-center justify-center section-idx shrink-0"><?= ($idx + 1) ?></span>
                                <span class="text-lg"><?= $meta['icon'] ?></span>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 section-title"><?= htmlspecialchars($meta['title']) ?></div>
                                    <div class="text-[11px] text-gray-400"><?= htmlspecialchars($meta['desc']) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="moveSection(this, -1)" class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-[#F25996] hover:border-[#F25996] flex items-center justify-center text-xs font-bold transition-all" title="Move Up">▲</button>
                                <button type="button" onclick="moveSection(this, 1)" class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-[#F25996] hover:border-[#F25996] flex items-center justify-center text-xs font-bold transition-all" title="Move Down">▼</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Card 3: Footer Notes & Declaration -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">📝</span>
                            <div>
                                <h3 class="font-black text-gray-900 text-base">Bottom Notes & Declarations</h3>
                                <p class="text-xs text-gray-400">Printed at the bottom of the 100mm packing slip</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Footer Text</span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Declaration & Terms</label>
                            <textarea id="companyNotes" name="notes" rows="3"
                                      class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-pink-100 transition-all leading-relaxed"
                                      placeholder="Declaration text..."><?= htmlspecialchars($config['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" id="showLogo" name="show_logo" value="1" <?= !empty($config['show_logo']) ? 'checked' : '' ?> class="rounded text-[#F25996] focus:ring-pink-400">
                                <span class="text-xs font-semibold text-gray-700">Print Brand Logo</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" id="showNotes" name="show_notes" value="1" <?= !empty($config['show_notes']) ? 'checked' : '' ?> class="rounded text-[#F25996] focus:ring-pink-400">
                                <span class="text-xs font-semibold text-gray-700">Print Bottom Notes</span>
                            </label>
                        </div>
                    </div>
                <!-- Card 4: Direct USB Thermal Printer Settings (TSC TE244) -->
                <div id="printer-settings" class="bg-white rounded-2xl p-6 border border-gray-200/80 shadow-xs scroll-mt-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">⚡</span>
                            <div>
                                <h3 class="font-black text-gray-900 text-base">Direct USB Thermal Printer Settings</h3>
                                <p class="text-xs text-gray-400">Configure hardware parameters for 1-click direct printing to TSC TE244</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">TSC Hardware</span>
                    </div>

                    <div class="space-y-4">
                        <!-- Print Engine Mode -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Print Engine Mode</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php $curMode = $config['tspl_mode'] ?? 'bitmap'; ?>
                                <label class="p-3.5 rounded-xl border <?= $curMode === 'bitmap' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500/30' : 'border-gray-200 bg-gray-50/50' ?> cursor-pointer flex flex-col gap-1 transition-all">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-900">Exact Visual Layout</span>
                                        <input type="radio" name="tspl_mode" value="bitmap" <?= $curMode === 'bitmap' ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-400">
                                    </div>
                                    <span class="text-[11px] text-gray-500 leading-tight">High-Res 203 DPI TSPL Bitmap. Retains CMS logo, exact fonts, geometric corners, and custom styling.</span>
                                </label>
                                <label class="p-3.5 rounded-xl border <?= $curMode === 'vector' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500/30' : 'border-gray-200 bg-gray-50/50' ?> cursor-pointer flex flex-col gap-1 transition-all">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-900">Pure Vector TSPL</span>
                                        <input type="radio" name="tspl_mode" value="vector" <?= $curMode === 'vector' ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-400">
                                    </div>
                                    <span class="text-[11px] text-gray-500 leading-tight">Native printer primitives with Code 128 barcode. Ultra-fast thermal print speed (~0.1s).</span>
                                </label>
                            </div>
                        </div>

                        <!-- Hardware Parameter Dropdowns -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200/70">
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-gray-600 mb-1">Target Printer</label>
                                <select name="tspl_printer" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800 p-2 focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500">
                                    <?php $curPrinter = $config['tspl_printer'] ?? 'TSC TE244'; ?>
                                    <option value="TSC TE244" <?= $curPrinter === 'TSC TE244' ? 'selected' : '' ?>>TSC TE244 (USB002)</option>
                                    <option value="TSC DA220" <?= $curPrinter === 'TSC DA220' ? 'selected' : '' ?>>TSC DA220 (USB001)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-gray-600 mb-1">Darkness (Head Heat)</label>
                                <select name="tspl_density" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800 p-2 focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500">
                                    <?php $curDensity = (int)($config['tspl_density'] ?? 7); ?>
                                    <option value="3" <?= $curDensity === 3 ? 'selected' : '' ?>>3 (Light)</option>
                                    <option value="5" <?= $curDensity === 5 ? 'selected' : '' ?>>5 (Medium Light)</option>
                                    <option value="7" <?= $curDensity === 7 ? 'selected' : '' ?>>7 (Crisp & Dark - Recommended)</option>
                                    <option value="8" <?= $curDensity === 8 ? 'selected' : '' ?>>8 (Very Dark)</option>
                                    <option value="10" <?= $curDensity === 10 ? 'selected' : '' ?>>10 (Maximum Heat)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-gray-600 mb-1">Text Boldness (Threshold)</label>
                                <select name="tspl_threshold" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800 p-2 focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500">
                                    <?php $curThresh = (int)($config['tspl_threshold'] ?? 165); ?>
                                    <option value="120" <?= $curThresh === 120 ? 'selected' : '' ?>>120 (Light / Thin Text)</option>
                                    <option value="140" <?= $curThresh === 140 ? 'selected' : '' ?>>140 (Standard Text)</option>
                                    <option value="165" <?= $curThresh === 165 ? 'selected' : '' ?>>165 (Solid Bold - Recommended)</option>
                                    <option value="185" <?= $curThresh === 185 ? 'selected' : '' ?>>185 (Extra Bold Text)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-gray-600 mb-1">Print Speed</label>
                                <select name="tspl_speed" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800 p-2 focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500">
                                    <?php $curSpeed = (int)($config['tspl_speed'] ?? 4); ?>
                                    <option value="2" <?= $curSpeed === 2 ? 'selected' : '' ?>>2 ips</option>
                                    <option value="3" <?= $curSpeed === 3 ? 'selected' : '' ?>>3 ips</option>
                                    <option value="4" <?= $curSpeed === 4 ? 'selected' : '' ?>>4 ips (Standard)</option>
                                    <option value="5" <?= $curSpeed === 5 ? 'selected' : '' ?>>5 ips (Fast)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hardware Testing & Polarity -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="tspl_invert" value="1" <?= !empty($config['tspl_invert']) ? 'checked' : '' ?> class="rounded text-emerald-600 focus:ring-emerald-400">
                                <span class="text-xs font-medium text-gray-700">Invert B/W Polarity (Only if printer outputs white-on-black)</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="sendHardwareTest('FORMFEED\r\n')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-colors border border-gray-200" title="Feed 1 label to test alignment">
                                    📄 Feed 1 Label
                                </button>
                                <button type="button" onclick="sendHardwareTest('GAPDETECT\r\n')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-colors border border-gray-200" title="Calibrate gap sensor on printer">
                                    🎯 Calibrate Gap
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Submit Action -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <?php if ($this->hasPermission('packing_slip_config', 'edit') || $this->hasPermission('all_orders', 'edit')): ?>
                    <button type="submit" id="mainSaveBtn" class="bg-[#F25996] hover:bg-[#e04481] text-white px-8 py-3 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Packing Slip Format</span>
                    </button>
                    <?php else: ?>
                    <span class="px-8 py-3 bg-gray-100 text-gray-400 font-bold text-xs uppercase tracking-wider rounded-xl cursor-not-allowed">
                        Read-Only Mode
                    </span>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Right Panel: Sticky Live 100mm × 100mm Preview Card (5 Cols) -->
        <div class="lg:col-span-5 sticky top-6 space-y-4">
            <div class="bg-gray-900 text-white rounded-2xl p-5 shadow-lg border border-gray-800">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h3 class="font-bold text-sm text-gray-200">Live 100mm × 100mm Preview</h3>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30">
                        1 Page Thermal Slip
                    </span>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed">
                    This preview shows the exact proportional layout that will print on thermal label rolls (100mm × 100mm). Updates live as you edit.
                </p>
            </div>

            <!-- Informational Banner Explaining Sample vs Real Orders -->
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-3.5 text-xs shadow-xs flex items-start gap-2.5">
                <span class="text-base shrink-0 leading-none mt-0.5">💡</span>
                <div class="space-y-1">
                    <p class="font-bold text-amber-950">Why are recipient & order details shown here?</p>
                    <p class="text-[11px] text-amber-800 leading-normal">
                        The Order Number (<strong>GB00108</strong>) and Recipient Customer (<strong>Sri Raghavendra Textiles</strong>) below are <strong>sample placeholders</strong> for previewing the format.
                    </p>
                    <p class="text-[11px] text-amber-800 leading-normal">
                        When you print packing slips for actual customer orders under <strong>Orders</strong>, the customer's real name, delivery address, and order ID are dynamically loaded automatically.
                    </p>
                </div>
            </div>

            <!-- 100mm x 100mm Aspect Ratio Thermal Paper Preview Container -->
            <div class="bg-gray-200 p-4 rounded-2xl shadow-inner flex justify-center items-center overflow-hidden">
                <div id="liveSlipPreview" class="slip-paper-preview bg-white text-gray-900 border border-gray-300 shadow-2xl relative overflow-hidden flex flex-col justify-between"
                     style="width: 378px; height: 378px; max-width: 100%; aspect-ratio: 1 / 1; padding: 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 8px; line-height: 1.25; box-sizing: border-box;">
                    
                    <!-- Top-Right Geometric Corner Accents -->
                    <svg class="absolute top-0 right-0 w-20 h-14 pointer-events-none z-0" viewBox="0 0 100 65" preserveAspectRatio="none">
                        <polygon points="50,0 78,0 100,22 100,0" fill="#4b5563" />
                        <polygon points="72,0 100,0 100,48 40,0" fill="#000000" />
                    </svg>

                    <!-- Bottom-Right Geometric Corner Accents -->
                    <svg class="absolute bottom-0 right-0 w-28 h-18 pointer-events-none z-0" viewBox="0 0 140 85" preserveAspectRatio="none">
                        <polygon points="140,42 140,85 70,85" fill="#4b5563" />
                        <polygon points="140,20 140,85 35,85" fill="#1f2937" />
                        <polygon points="140,0 140,85 0,85" fill="#000000" />
                    </svg>

                    <!-- Background Subtle Watermark Placeholder -->
                    <div id="previewWatermark" class="pointer-events-none select-none z-0"></div>

                    <!-- Dynamic Sections Container -->
                    <div id="previewSectionsContainer" class="relative z-10 flex flex-col justify-between h-full">
                        <!-- Sections will be arranged dynamically via JavaScript based on section_order -->
                    </div>

                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-500 px-2">
                <span>Standard Thermal Size: <strong>100mm × 100mm (4" × 4")</strong></span>
                <span class="text-emerald-600 font-semibold">✓ Guaranteed Single Page</span>
            </div>

            <!-- Optional: Sample Recipient Tester Accordion -->
            <details class="group bg-white rounded-xl border border-gray-200 text-xs shadow-2xs overflow-hidden">
                <summary class="px-4 py-2.5 font-bold text-gray-700 cursor-pointer select-none flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <span>🧪</span>
                        <span>Customize Sample Recipient in Preview (Visual Test)</span>
                    </span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <div class="p-4 border-t border-gray-100 bg-gray-50/50 space-y-3">
                    <p class="text-[11px] text-gray-500">Test how long recipient names and addresses fit on this 100mm layout:</p>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Sample Recipient Name</label>
                        <input type="text" id="sampleCustomerName" value="Sri Raghavendra Textiles" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Sample Recipient Address</label>
                        <textarea id="sampleCustomerAddress" rows="2" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium">42 Cross Cut Rd, Gandhipuram, Coimbatore - 641012</textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Sample Recipient Phone</label>
                        <input type="text" id="sampleCustomerPhone" value="94433 22110" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium">
                    </div>
                </div>
            </details>
        </div>

    </div>
</div>

<!-- Drag & Drop & Realtime Preview Script -->
<script>
    const cmsLogoDefault = <?= json_encode($config['logo_url'] ?: $cmsLogo) ?>;

    // Helper escape function
    function escapeHtmlSafe(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Real-time elements
    const companyNameEl    = document.getElementById('companyName');
    const slipSubtitleEl   = document.getElementById('slipSubtitle');
    const logoUrlInputEl   = document.getElementById('logoUrlInput');
    const logoPreviewThumbEl = document.getElementById('logoPreviewThumb');
    const logoPlaceholderIconEl = document.getElementById('logoPlaceholderIcon');
    const companyAddressEl = document.getElementById('companyAddress');
    const companyPhoneEl   = document.getElementById('companyPhone');
    const companyEmailEl   = document.getElementById('companyEmail');
    const companyGstinEl   = document.getElementById('companyGstin');
    const companyNotesEl   = document.getElementById('companyNotes');
    const showLogoEl       = document.getElementById('showLogo');
    const showGstinEl      = document.getElementById('showGstin');
    const showNotesEl      = document.getElementById('showNotes');
    const previewContainer = document.getElementById('previewSectionsContainer');

    // Sample Recipient Inputs for visual testing
    const sampleCustomerNameEl    = document.getElementById('sampleCustomerName');
    const sampleCustomerAddressEl = document.getElementById('sampleCustomerAddress');
    const sampleCustomerPhoneEl   = document.getElementById('sampleCustomerPhone');

    function updateLivePreview() {
        const companyName = companyNameEl ? (companyNameEl.value.trim() || 'GRM Garments') : 'GRM Garments';
        const subtitle    = slipSubtitleEl ? (slipSubtitleEl.value.trim() || 'FORWARDING WITH CARE') : 'FORWARDING WITH CARE';
        const logoUrl     = logoUrlInputEl ? logoUrlInputEl.value.trim() : cmsLogoDefault;
        const address     = companyAddressEl ? (companyAddressEl.value.trim() || '123 Textile Park, Tirupur, Tamil Nadu') : '';
        const phone       = companyPhoneEl ? companyPhoneEl.value.trim() : '';
        const email       = companyEmailEl ? companyEmailEl.value.trim() : '';
        const gstin       = companyGstinEl ? companyGstinEl.value.trim() : '';
        const notes       = companyNotesEl ? companyNotesEl.value.trim() : '';
        const showLogo    = showLogoEl ? showLogoEl.checked : true;
        const showGstin   = showGstinEl ? showGstinEl.checked : true;
        const showNotes   = showNotesEl ? showNotesEl.checked : true;

        // Sample Customer in Preview
        const custName    = sampleCustomerNameEl ? (sampleCustomerNameEl.value.trim() || 'Sri Raghavendra Textiles') : 'Sri Raghavendra Textiles';
        const custAddress = sampleCustomerAddressEl ? (sampleCustomerAddressEl.value.trim() || '42 Cross Cut Rd, Gandhipuram, Coimbatore - 641012') : '42 Cross Cut Rd, Gandhipuram, Coimbatore - 641012';
        const custPhone   = sampleCustomerPhoneEl ? sampleCustomerPhoneEl.value.trim() : '94433 22110';

        // Update logo thumbnail preview in Card 1
        if (logoPreviewThumbEl && logoPlaceholderIconEl) {
            if (logoUrl) {
                logoPreviewThumbEl.src = logoUrl;
                logoPreviewThumbEl.classList.remove('hidden');
                logoPlaceholderIconEl.classList.add('hidden');
            } else {
                logoPreviewThumbEl.classList.add('hidden');
                logoPlaceholderIconEl.classList.remove('hidden');
            }
        }

        // Update watermark
        const watermarkEl = document.getElementById('previewWatermark');
        if (watermarkEl) {
            if (showLogo && logoUrl) {
                watermarkEl.innerHTML = `<img src="${logoUrl}" class="absolute bottom-4 right-4 w-44 h-44 object-contain opacity-[0.06] pointer-events-none select-none z-0" alt="Watermark">`;
            } else {
                watermarkEl.innerHTML = `<div class="absolute bottom-6 right-6 font-serif font-black text-5xl tracking-widest text-black opacity-[0.04] pointer-events-none select-none z-0">${escapeHtmlSafe(companyName)}</div>`;
            }
        }

        // Get current order of sections
        const sectionInputs = document.querySelectorAll('#sectionOrderList input.section-order-input');
        const sectionsOrder = Array.from(sectionInputs).map(inp => inp.value);

        if (!previewContainer) return;
        previewContainer.innerHTML = '';

        sectionsOrder.forEach(sec => {
            if (sec === 'header') {
                // Header section with Order Details on Right (Replacing PACKING SLIP & FORWARDING WITH CARE)
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between gap-3 pb-2 border-b-2 border-black';
                div.innerHTML = `
                    <!-- Left: Big Logo with Company Name & GSTIN below -->
                    <div class="flex flex-col items-start gap-0.5 min-w-0">
                        ${showLogo && logoUrl ? `
                            <img src="${logoUrl}" alt="Logo" class="h-14 w-auto max-w-[150px] object-contain mb-1">
                        ` : `
                            <div class="h-12 w-20 rounded-lg bg-black text-white flex items-center justify-center font-black text-sm tracking-wider mb-1">
                                ${escapeHtmlSafe(companyName.substring(0, 3).toUpperCase())}
                            </div>
                        `}
                        <h2 class="font-serif font-black text-[14px] uppercase tracking-wide text-black leading-tight">
                            ${escapeHtmlSafe(companyName)}
                        </h2>
                        ${showGstin && gstin ? `
                            <div class="text-[9.5px] font-mono font-bold text-black mt-0.5 tracking-wider">
                                GSTIN: <span class="font-black">${escapeHtmlSafe(gstin)}</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Right: Order ID, Date, Payment Status (Replaces PACKING SLIP & FORWARDING WITH CARE) -->
                    <div class="border-l-2 border-black pl-3 shrink-0 flex flex-col justify-center gap-1.5 min-w-[155px]">
                        <!-- Order No -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1 text-[8.5px] font-bold text-gray-700 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>ORDER NO:</span>
                            </div>
                            <span class="text-[12px] font-black font-mono text-black leading-none">GB00108</span>
                        </div>

                        <!-- Order Date -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1 text-[8.5px] font-bold text-gray-700 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>DATE:</span>
                            </div>
                            <span class="text-[10px] font-black text-black leading-none">15 Oct 2026</span>
                        </div>

                        <!-- Payment Status -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1 text-[8.5px] font-bold text-gray-700 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>PAYMENT:</span>
                            </div>
                            <div class="text-[10.5px] font-black text-black leading-none">
                                PAID <span class="text-[9px] font-bold text-gray-700">(Online)</span>
                            </div>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(div);
            } else if (sec === 'meta') {
                // Separate meta bar removed per user request (integrated into Header above)
            } else if (sec === 'addresses') {
                // Addresses (From & To Cards, Bigger Fonts)
                const cleanCustAddress = custAddress.replace(/\[.*?\]/g, '').replace(/Shop Address/gi, '').trim();
                const div = document.createElement('div');
                div.className = 'grid grid-cols-2 gap-2.5 my-2';
                div.innerHTML = `
                    <!-- SHIP FROM Card (Styled to match SHIP TO) -->
                    <div class="border-2 border-black rounded-xl overflow-hidden bg-white shadow-none">
                        <div class="bg-gray-100 px-3 py-1.5 border-b-2 border-black flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-md bg-black text-white flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            </span>
                            <span class="text-[10px] font-black uppercase text-black tracking-wider">SHIP FROM</span>
                        </div>
                        <div class="p-2.5 flex flex-col gap-0.5">
                            <div class="font-black text-[11px] text-black leading-tight tracking-tight">${escapeHtmlSafe(companyName)}</div>
                            <div class="text-[8px] text-black font-medium leading-tight">${escapeHtmlSafe(address)}</div>
                            ${phone ? `<div class="text-[8px] text-black font-bold mt-0.5">Ph: ${escapeHtmlSafe(phone)}</div>` : ''}
                            ${email ? `<div class="text-[8px] text-black font-bold">Email: ${escapeHtmlSafe(email)}</div>` : ''}
                        </div>
                    </div>

                    <!-- SHIP TO Card (Big font size) -->
                    <div class="border-2 border-black rounded-xl overflow-hidden bg-white shadow-none">
                        <div class="bg-gray-100 px-3 py-1.5 border-b-2 border-black flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-md bg-black text-white flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                            </span>
                            <span class="text-[10px] font-black uppercase text-black tracking-wider">SHIP TO</span>
                        </div>
                        <div class="p-2.5 flex flex-col gap-1">
                            <div class="font-black text-[15px] text-black leading-tight tracking-tight">${escapeHtmlSafe(custName)}</div>
                            <div class="text-[11px] text-black font-bold leading-snug">${escapeHtmlSafe(cleanCustAddress)}</div>
                            ${custPhone ? `<div class="text-[12px] text-black font-black mt-1">Ph: ${escapeHtmlSafe(custPhone)}</div>` : ''}
                        </div>
                    </div>
                `;
                previewContainer.appendChild(div);
            } else if (sec === 'notes') {
                // Footer Notes / Declaration (Bigger Fonts)
                if (showNotes && notes) {
                    const div = document.createElement('div');
                    div.className = 'border border-gray-400 rounded-xl p-2 bg-white flex items-start gap-2 mt-auto relative z-10';
                    div.innerHTML = `
                        <div class="w-4 h-4 rounded-full bg-black text-white flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5">
                            i
                        </div>
                        <div class="text-[8.5px] text-black font-medium leading-tight">${escapeHtmlSafe(notes)}</div>
                    `;
                    previewContainer.appendChild(div);
                }
            }
        });
    }

    // Update Section Index Badges (1, 2, 3, 4)
    function updateSectionNumbers() {
        const list = document.getElementById('sectionOrderList');
        if (!list) return;
        const badges = list.querySelectorAll('.section-idx');
        badges.forEach((badge, idx) => {
            badge.textContent = idx + 1;
        });
    }

    // Move Section Up or Down via Buttons
    function moveSection(btn, direction) {
        const item = btn.closest('.section-item');
        const list = document.getElementById('sectionOrderList');
        if (direction === -1 && item.previousElementSibling) {
            list.insertBefore(item, item.previousElementSibling);
        } else if (direction === 1 && item.nextElementSibling) {
            list.insertBefore(item.nextElementSibling, item);
        }
        updateSectionNumbers();
        updateLivePreview();
    }

    // Initialize HTML5 Drag & Drop Reordering
    function initDragAndDrop() {
        const list = document.getElementById('sectionOrderList');
        if (!list) return;

        let draggedItem = null;

        list.querySelectorAll('.section-item').forEach(item => {
            item.setAttribute('draggable', 'true');

            item.addEventListener('dragstart', (e) => {
                draggedItem = item;
                item.classList.add('opacity-40', 'border-pink-500', 'bg-pink-50/70', 'ring-2', 'ring-pink-300');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.section || '');
            });

            item.addEventListener('dragend', () => {
                draggedItem = null;
                list.querySelectorAll('.section-item').forEach(i => {
                    i.classList.remove('opacity-40', 'border-pink-500', 'bg-pink-50/70', 'ring-2', 'ring-pink-300');
                });
                updateSectionNumbers();
                updateLivePreview();
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (!draggedItem || draggedItem === item) return;

                const rect = item.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;

                if (e.clientY > midY) {
                    if (item.nextElementSibling !== draggedItem) {
                        list.insertBefore(draggedItem, item.nextElementSibling);
                        updateSectionNumbers();
                    }
                } else {
                    if (item !== draggedItem) {
                        list.insertBefore(draggedItem, item);
                        updateSectionNumbers();
                    }
                }
            });

            item.addEventListener('drop', (e) => {
                e.preventDefault();
                updateSectionNumbers();
                updateLivePreview();
            });
        });
    }

    // Submit Configuration
    async function submitPackingSlipConfig() {
        const form = document.getElementById('packingSlipConfigForm');
        const topBtn = document.getElementById('topSaveBtn');
        const mainBtn = document.getElementById('mainSaveBtn');

        const formData = new FormData(form);

        topBtn.disabled = true;
        mainBtn.disabled = true;
        topBtn.innerHTML = 'Saving...';
        mainBtn.innerHTML = 'Saving...';

        try {
            const res = await fetch('<?= BASE_URL ?>/admin/orders/packing-slip-config/save', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(data.message);
                }
            } else {
                throw new Error(data.message || 'Failed to save');
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message
                });
            } else {
                alert('Error: ' + err.message);
            }
        } finally {
            topBtn.disabled = false;
            mainBtn.disabled = false;
            topBtn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Configuration';
            mainBtn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Packing Slip Format';
        }
    }

    async function resetPackingSlipConfig() {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Reset to Defaults?',
                text: 'This will reset all packing slip configurations to factory defaults.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Reset Defaults'
            });
            if (!result.isConfirmed) return;
        } else if (!confirm('Are you sure you want to reset all packing slip configurations to defaults?')) {
            return;
        }

        try {
            const res = await fetch('<?= BASE_URL ?>/admin/orders/packing-slip-config/reset', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reset Complete!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    alert(data.message);
                    location.reload();
                }
            } else {
                throw new Error(data.message || 'Failed to reset');
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            } else {
                alert('Error: ' + err.message);
            }
        }
    }

    // Read-only lockdown if edit is not allowed
    const canEditConfig = <?= ($this->hasPermission('packing_slip_config', 'edit') || $this->hasPermission('all_orders', 'edit')) ? 'true' : 'false' ?>;
    if (!canEditConfig) {
        document.querySelectorAll('#packingSlipConfigForm input, #packingSlipConfigForm textarea, #packingSlipConfigForm select').forEach(el => {
            el.disabled = true;
        });
    }

    // Event Listeners for Live Preview
    [companyNameEl, slipSubtitleEl, logoUrlInputEl, companyAddressEl, companyPhoneEl, companyEmailEl, companyGstinEl, companyNotesEl, sampleCustomerNameEl, sampleCustomerAddressEl, sampleCustomerPhoneEl].forEach(input => {
        if (input) input.addEventListener('input', updateLivePreview);
    });
    [showLogoEl, showGstinEl, showNotesEl].forEach(cb => {
        if (cb) cb.addEventListener('change', updateLivePreview);
    });

    async function sendHardwareTest(cmdString) {
        const printerName = document.querySelector('select[name="tspl_printer"]')?.value || 'TSC TE244';
        try {
            const encoder = new TextEncoder();
            const bytes = encoder.encode(cmdString);
            let binary = '';
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            const base64 = window.btoa(binary);

            const formData = new FormData();
            formData.append('printer_name', printerName);
            formData.append('raw_base64', base64);

            const res = await fetch('<?= BASE_URL ?>/admin/orders/packing-slips/direct-print', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                alert(`Command sent to ${printerName} successfully!`);
            } else {
                alert(`Printer error: ${data.message}`);
            }
        } catch (err) {
            alert(`Communication error with ${printerName}: ` + err.message);
        }
    }

    // Initialize Preview and Drag & Drop on Load
    document.addEventListener('DOMContentLoaded', () => {
        initDragAndDrop();
        updateSectionNumbers();
        updateLivePreview();
    });
</script>
