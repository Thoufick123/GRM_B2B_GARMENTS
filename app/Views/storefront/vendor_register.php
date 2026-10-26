<?php
$old = $old ?? [];
$initialStep = (int)($initialStep ?? 1);
?>
<div class="min-h-[calc(100vh-80px)] h-auto relative flex flex-col justify-center py-8 px-4 sm:px-6 lg:px-8 bg-[#fdfdfd] overflow-hidden">
    
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-64 h-64 rounded-br-full -z-10 opacity-70" style="background-color: #FDBFDD;"></div>
    <div class="absolute top-8 right-12 w-16 h-16 rounded-full flex items-center justify-center -z-10 opacity-80" style="background-color: #FDBFDD; color: #F25996;">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
    </div>

    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center z-10 mb-3">
        <span class="inline-block px-3 py-1 text-xs font-bold rounded-full mb-2" style="background-color: #FDBFDD; color: #F25996;">B2B Vendor Portal</span>
        <h2 class="mt-1 text-3xl font-extrabold text-gray-900 font-display">
            Register as a <span style="color: #F25996;">Vendor / Seller</span>
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Expand your distribution network and sell directly to verified wholesale buyers. Already a vendor? 
            <a href="<?= BASE_URL ?>/login" class="font-bold hover:underline" style="color: #F25996;">Log in here</a>
        </p>
    </div>

    <!-- Role Switcher Tabs -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 mb-6 px-4">
        <div class="p-1.5 rounded-2xl flex border" style="background-color: rgba(253, 191, 221, 0.25); border-color: #FDBFDD;">
            <a href="<?= BASE_URL ?>/register" class="flex-1 py-2.5 text-center rounded-xl font-bold text-sm text-gray-500 hover:text-[#F25996] transition flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Wholesale Buyer
            </a>
            <a href="<?= BASE_URL ?>/vendor-register" class="flex-1 py-2.5 text-center rounded-xl font-bold text-sm bg-white shadow-sm transition flex items-center justify-center" style="color: #F25996;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Vendor / Seller
            </a>
        </div>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-3xl z-10 relative flex flex-col h-full mb-8">
        
        <!-- Custom Progress Stepper -->
        <div class="flex justify-between items-start mb-6 relative px-8 shrink-0">
            <!-- Connecting Line -->
            <div class="absolute top-5 left-16 right-16 h-[2px] bg-gray-200 -z-10">
                <div id="vendor-progress-line" class="h-full transition-all duration-500 w-0" style="background-color: #F25996;"></div>
            </div>

            <!-- Step 1 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="vstep-1-icon" class="w-10 h-10 rounded-full text-white flex items-center justify-center shadow-md mb-2 transition-colors duration-300" style="background-color: #F25996;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span id="vstep-1-text" class="text-xs font-bold" style="color: #F25996;">Account</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Basic contact info</span>
            </div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="vstep-2-icon" class="w-10 h-10 rounded-full bg-gray-100 border-2 border-gray-200 text-gray-400 flex items-center justify-center mb-2 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span id="vstep-2-text" class="text-xs font-bold text-gray-500">Business</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Store & Address</span>
            </div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="vstep-3-icon" class="w-10 h-10 rounded-full bg-gray-100 border-2 border-gray-200 text-gray-400 flex items-center justify-center mb-2 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <span id="vstep-3-text" class="text-xs font-bold text-gray-500">Bank & UPI</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Payout Details</span>
            </div>
        </div>

        <div class="bg-white shadow-[0_4px_24px_-4px_rgba(0,0,0,0.05)] rounded-2xl border border-gray-100 overflow-hidden">
            <form id="vendor-multi-step-form" action="<?= BASE_URL ?>/vendor-register" method="POST" enctype="multipart/form-data">
                
                <!-- STEP 1: Account Information -->
                <div id="vstep-1" class="vstep-content flex flex-col p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-sm mr-3" style="background-color: #F25996;">01</div>
                            <h3 class="text-lg font-bold text-gray-900 font-display">Account Information</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Full Name *</label>
                            <input id="name" name="name" type="text" value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Owner / Manager name" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Business Email *</label>
                            <input id="email" name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="vendor@company.com" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm" onblur="checkVendorFieldExists('email', this.value)">
                            <p id="email_error_msg" class="text-[11px] font-bold text-rose-500 mt-1 hidden"></p>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Mobile Number (10 digits) *</label>
                            <input id="phone" name="phone" type="tel" maxlength="10" pattern="[0-9]{10}" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" placeholder="9876543210" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="checkVendorFieldExists('phone', this.value)">
                            <p id="phone_error_msg" class="text-[11px] font-bold text-rose-500 mt-1 hidden"></p>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password *</label>
                            <div class="relative">
                                <input id="password" name="password" type="password" minlength="6" placeholder="Minimum 6 characters" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm pr-11">
                                <button type="button" onclick="toggleVendorPassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#F25996] transition-colors focus:outline-none" title="Show/Hide password">
                                    <!-- Eye Icon (View) -->
                                    <svg id="vendor-eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Off Icon (Hide) -->
                                    <svg id="vendor-eye-off-icon" class="w-4 h-4 hidden text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" onclick="goToVendorStep(2)" class="px-6 py-2.5 text-white font-semibold text-sm rounded-xl transition shadow hover:opacity-90" style="background: #F25996;">
                            Next: Business Details &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Vendor Business Information -->
                <div id="vstep-2" class="vstep-content hidden flex-col p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-sm mr-3" style="background-color: #F25996;">02</div>
                            <h3 class="text-lg font-bold text-gray-900 font-display">Store & Business Details</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
                        <div>
                            <label for="store_name" class="block text-sm font-semibold text-gray-700 mb-1">Store / Brand Name *</label>
                            <input id="store_name" name="store_name" type="text" value="<?= htmlspecialchars($old['store_name'] ?? '') ?>" placeholder="e.g. Apex Wholesale Electronics" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                        </div>
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-1">Registered Company Name</label>
                            <input id="company_name" name="company_name" type="text" value="<?= htmlspecialchars($old['company_name'] ?? '') ?>" placeholder="Legal registered company name" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                        </div>
                        <div>
                            <label for="vendor_type" class="block text-sm font-semibold text-gray-700 mb-1">Vendor Type *</label>
                            <?php 
                                if (!isset($vendorTypes)) {
                                    $vtModel = new \App\Models\VendorType();
                                    $vendorTypes = $vtModel->getAllActive();
                                }
                                $vt = $old['vendor_type'] ?? (!empty($vendorTypes[0]['slug']) ? $vendorTypes[0]['slug'] : 'wholesaler'); 
                            ?>
                            <select id="vendor_type" name="vendor_type" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white" required>
                                <?php if (!empty($vendorTypes)): ?>
                                    <?php foreach ($vendorTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type['slug']) ?>" <?= $vt === $type['slug'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="wholesaler" selected>Wholesaler</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label for="pincode" class="block text-sm font-semibold text-gray-700 mb-1">Pincode *</label>
                            <input id="pincode" name="pincode" type="text" maxlength="6" pattern="[0-9]{6}" value="<?= htmlspecialchars($old['pincode'] ?? '') ?>" placeholder="6-digit pincode" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="block text-sm font-semibold text-gray-700 mb-1">Warehouse / Shop Address *</label>
                        <input id="address" name="address" type="text" value="<?= htmlspecialchars($old['address'] ?? '') ?>" placeholder="Plot / Street / Area" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm mb-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input id="city" name="city" type="text" value="<?= htmlspecialchars($old['city'] ?? '') ?>" placeholder="City" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                            <input id="state" name="state" type="text" value="<?= htmlspecialchars($old['state'] ?? '') ?>" placeholder="State" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                        </div>
                    </div>

                    <!-- Tax & GST Registration Status Selector -->
                    <?php 
                        $hasGstVal = isset($old['has_gst']) ? (string)$old['has_gst'] : (isset($old['no_gst_reason']) && !empty($old['no_gst_reason']) ? '0' : '1');
                    ?>
                    <div class="mb-4 p-4 rounded-2xl border" style="background-color: rgba(253, 191, 221, 0.25); border-color: #FDBFDD;">
                        <label class="block text-sm font-bold text-gray-800 mb-2.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Tax &amp; Business Registration Type *
                        </label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <label id="label_has_gst_yes" class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 <?= $hasGstVal === '1' ? 'border-[#F25996] bg-white shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300' ?>" style="<?= $hasGstVal === '1' ? 'border-color: #F25996;' : '' ?>">
                                <input type="radio" name="has_gst" value="1" <?= $hasGstVal === '1' ? 'checked' : '' ?> onchange="onGstOptionChange(this.value)" class="w-4 h-4 border-gray-300" style="accent-color: #F25996;">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-900">GST Registered Vendor</span>
                                    <span class="block text-[11px] text-gray-500">I have a 15-digit GSTIN</span>
                                </div>
                            </label>
                            
                            <label id="label_has_gst_no" class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 <?= $hasGstVal === '0' ? 'border-[#F25996] bg-white shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300' ?>" style="<?= $hasGstVal === '0' ? 'border-color: #F25996;' : '' ?>">
                                <input type="radio" name="has_gst" value="0" <?= $hasGstVal === '0' ? 'checked' : '' ?> onchange="onGstOptionChange(this.value)" class="w-4 h-4 border-gray-300" style="accent-color: #F25996;">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-900">Non-GST / Unregistered Vendor</span>
                                    <span class="block text-[11px] text-gray-500">Turnover below threshold / Exempt</span>
                                </div>
                            </label>
                        </div>

                        <!-- Option A: GST Registered Input Fields -->
                        <div id="section_gst_fields" class="<?= $hasGstVal === '1' ? '' : 'hidden' ?> space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="gst_number" class="block text-xs font-bold text-gray-700 mb-1">
                                        GST Number (15 digits) <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="gst_number" name="gst_number" type="text" maxlength="15" value="<?= htmlspecialchars($old['gst_number'] ?? '') ?>" placeholder="e.g. 27AAAAA0000A1Z5" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm uppercase font-mono bg-white" oninput="handleGstInput(this)" onblur="checkVendorFieldExists('gst_number', this.value)">
                                    <p id="gst_number_error_msg" class="text-[11px] font-bold text-rose-500 mt-1 hidden"></p>
                                    <p class="text-[11px] text-gray-500 mt-1">PAN will be automatically extracted from your GSTIN.</p>
                                </div>
                                <div>
                                    <label for="gst_pan_number" class="block text-xs font-bold text-gray-700 mb-1">
                                        PAN Number (10 digits) <span class="text-gray-400 font-normal">(Auto-filled)</span>
                                    </label>
                                    <input id="gst_pan_number" <?= $hasGstVal === '1' ? 'name="pan_number"' : '' ?> type="text" maxlength="10" value="<?= htmlspecialchars($old['pan_number'] ?? '') ?>" placeholder="e.g. ABCDE1234F" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm uppercase font-mono bg-white text-gray-700" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                                </div>
                            </div>

                            <!-- Dedicated GST Certificate Uploader (Max 2MB, Cloudinary Storage) -->
                            <div class="mt-3 pt-3 border-t border-pink-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="gst_certificate" class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        GST Registration Certificate <span class="text-gray-400 font-normal">(Max 2MB)</span>
                                    </label>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#FDBFDD] text-[#F25996]">PDF, JPG, PNG, WEBP &bull; Max 2MB</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mb-2">Upload your official GST registration certificate for faster approval. Files are securely stored via Cloudinary.</p>

                                <!-- Dropzone Box -->
                                <div id="gst_dropzone" class="border-2 border-dashed border-[#FDBFDD] hover:border-[#F25996] bg-white rounded-xl p-3.5 transition text-center cursor-pointer relative group" onclick="document.getElementById('gst_certificate').click()">
                                    <input id="gst_certificate" name="gst_certificate" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden" onchange="handleGstCertChange(this)">
                                    
                                    <div id="gst_upload_prompt" class="flex flex-col items-center justify-center py-1">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center mb-1 group-hover:scale-110 transition-transform bg-pink-50 text-[#F25996]">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        </div>
                                        <span class="text-xs font-bold text-[#F25996]">Choose GST Certificate</span>
                                        <span class="text-[11px] text-gray-400 mt-0.5">Click to browse or drop file here (Max 2MB)</span>
                                    </div>

                                    <!-- Selected File Preview Card -->
                                    <div id="gst_cert_preview" class="hidden flex items-center justify-between p-2.5 bg-pink-50/70 border border-pink-200 rounded-xl text-left" onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div id="gst_preview_icon" class="w-10 h-10 rounded-lg bg-pink-100 text-[#F25996] flex items-center justify-center shrink-0 font-bold overflow-hidden">
                                                📄
                                            </div>
                                            <div class="overflow-hidden">
                                                <p id="gst_filename" class="text-xs font-bold text-gray-900 truncate">certificate.pdf</p>
                                                <p id="gst_filesize" class="text-[10px] text-gray-500 font-medium">1.2 MB &bull; Ready to upload</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="clearGstCertificate(event)" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Remove Certificate">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <div id="gst_size_error" class="hidden mt-1.5 p-2 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700 font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="gst_size_error_text">File size exceeds the 2MB limit. Please choose a file under 2MB.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Option B: Non-GST Registered Input Fields -->
                        <div id="section_non_gst_fields" class="<?= $hasGstVal === '0' ? '' : 'hidden' ?> space-y-3">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xs text-amber-800 leading-relaxed">
                                    <strong class="font-semibold">Non-GST Vendor Policy:</strong> As per tax regulations, vendors without a GST registration must provide their 10-digit PAN number and specify the reason for exemption.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="non_gst_pan_number" class="block text-xs font-bold text-gray-700 mb-1">
                                        Proprietor / Business PAN Number <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="non_gst_pan_number" <?= $hasGstVal === '0' ? 'name="pan_number"' : '' ?> type="text" maxlength="10" value="<?= htmlspecialchars($old['pan_number'] ?? '') ?>" placeholder="e.g. ABCDE1234F" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm uppercase font-mono bg-white" oninput="handleNonGstPanInput(this)">
                                </div>
                                <div>
                                    <label for="no_gst_reason" class="block text-xs font-bold text-gray-700 mb-1">
                                        Reason for No GST <span class="text-rose-500">*</span>
                                    </label>
                                    <?php 
                                        $reasonsList = [
                                            'Turnover below threshold limit (Under ₹40L / ₹20L)',
                                            'Composition scheme dealer',
                                            'Dealing exclusively in GST-exempt goods / produce',
                                            'Newly established business / not registered under GST',
                                            'Other'
                                        ];
                                        $rawReason = $old['no_gst_reason'] ?? '';
                                        $isPredefined = in_array($rawReason, array_slice($reasonsList, 0, 4));
                                        $selectedReason = $isPredefined ? $rawReason : (!empty($rawReason) ? 'Other' : '');
                                        $customReasonText = (!$isPredefined && !empty($rawReason)) ? $rawReason : ($old['other_gst_reason'] ?? '');
                                        $initialDisplayLabel = !empty($selectedReason) ? ($selectedReason === 'Other' ? 'Other Reason' : $selectedReason) : '-- Select Exemption Reason --';
                                    ?>
                                    <!-- Hidden Synchronized Native Select for Form Submit & Validation -->
                                    <select id="no_gst_reason" name="no_gst_reason" class="hidden" onchange="handleNonGstReasonChange(this.value)">
                                        <option value="">-- Select Exemption Reason --</option>
                                        <option value="Turnover below threshold limit (Under ₹40L / ₹20L)" <?= $selectedReason === 'Turnover below threshold limit (Under ₹40L / ₹20L)' ? 'selected' : '' ?>>Turnover below threshold limit (Under ₹40L / ₹20L)</option>
                                        <option value="Composition scheme dealer" <?= $selectedReason === 'Composition scheme dealer' ? 'selected' : '' ?>>Composition scheme dealer</option>
                                        <option value="Dealing exclusively in GST-exempt goods / produce" <?= $selectedReason === 'Dealing exclusively in GST-exempt goods / produce' ? 'selected' : '' ?>>Dealing exclusively in GST-exempt goods / produce</option>
                                        <option value="Newly established business / not registered under GST" <?= $selectedReason === 'Newly established business / not registered under GST' ? 'selected' : '' ?>>Newly established business / not registered under GST</option>
                                        <option value="Other" <?= $selectedReason === 'Other' ? 'selected' : '' ?>>Other Reason</option>
                                    </select>

                                    <!-- Custom Styled Pink Dropdown Trigger & Menu -->
                                    <div class="relative" id="custom_gst_reason_dropdown">
                                        <button type="button" id="custom_gst_reason_btn" onclick="toggleCustomGstDropdown()" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-left flex items-center justify-between transition-all focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-[#F25996] hover:border-[#FDBFDD] cursor-pointer">
                                            <span id="custom_gst_reason_text" class="<?= empty($selectedReason) ? 'text-gray-400 font-normal' : 'text-gray-900 font-semibold' ?>">
                                                <?= htmlspecialchars($initialDisplayLabel) ?>
                                            </span>
                                            <svg id="custom_gst_reason_arrow" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        
                                        <div id="custom_gst_reason_menu" class="hidden absolute z-30 left-0 right-0 mt-1.5 bg-white border border-[#FDBFDD] rounded-xl shadow-xl overflow-hidden py-1.5 transition-all">
                                            <div class="max-h-60 overflow-y-auto space-y-1 px-1.5">
                                                <?php foreach ($reasonsList as $rOption): 
                                                    $isThisSelected = ($selectedReason === $rOption);
                                                    $displayLabel = ($rOption === 'Other') ? 'Other Reason' : $rOption;
                                                ?>
                                                    <div onclick="selectCustomGstReason('<?= htmlspecialchars($rOption, ENT_QUOTES) ?>', '<?= htmlspecialchars($displayLabel, ENT_QUOTES) ?>')"
                                                         data-value="<?= htmlspecialchars($rOption) ?>"
                                                         class="custom-reason-option px-3.5 py-2.5 rounded-lg text-xs sm:text-sm cursor-pointer transition-all flex items-center justify-between <?= $isThisSelected ? 'bg-[#fdf2f7] text-[#F25996] font-bold border border-[#FDBFDD]' : 'text-gray-700 font-medium hover:bg-[#fdf2f7] hover:text-[#F25996]' ?>">
                                                        <span><?= htmlspecialchars($displayLabel) ?></span>
                                                        <svg class="w-4 h-4 text-[#F25996] <?= $isThisSelected ? '' : 'hidden' ?> option-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="other_reason_container" class="<?= $selectedReason === 'Other' ? '' : 'hidden' ?> mt-3">
                                <label for="other_gst_reason" class="block text-xs font-bold text-gray-700 mb-1">
                                    Specify Custom Reason <span class="text-rose-500">*</span>
                                </label>
                                <input id="other_gst_reason" name="other_gst_reason" type="text" value="<?= htmlspecialchars($customReasonText) ?>" placeholder="Explain why your business is exempt from GST..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Shop / Showroom Images Field -->
                    <div class="mb-4 p-4 rounded-2xl border" style="background-color: rgba(253, 191, 221, 0.2); border-color: #FDBFDD;">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Shop / Showroom Images
                            </label>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background-color: #FDBFDD; color: #F25996;">Multi-photo upload</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2.5">Upload photos of your storefront, showroom, or warehouse (JPG, PNG, WEBP).</p>
                        
                        <div class="border-2 border-dashed border-[#FDBFDD] hover:border-[#F25996] bg-white rounded-xl p-4 transition text-center cursor-pointer relative group" onclick="document.getElementById('shop_images').click()">
                            <input id="shop_images" name="shop_images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleShopImagesChange(this)">
                            <div class="flex flex-col items-center justify-center py-1.5">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center mb-1.5 group-hover:scale-110 transition-transform" style="background-color: #FDBFDD; color: #F25996;">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                <span class="text-xs font-bold" style="color: #F25996;">Choose Shop Photos</span>
                                <span id="shop_images_count_label" class="text-[11px] text-gray-400 mt-0.5">Select one or multiple images</span>
                            </div>
                        </div>

                        <!-- Image Preview Thumbnails -->
                        <div id="shop_images_preview_grid" class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 mt-3 hidden"></div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button type="button" onclick="goToVendorStep(1)" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                            &larr; Back
                        </button>
                        <button type="button" onclick="goToVendorStep(3)" class="px-6 py-2.5 text-white font-semibold text-sm rounded-xl transition shadow hover:opacity-90" style="background: #F25996;">
                            Next: Bank & Media &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Bank & UPI Payout Details -->
                <div id="vstep-3" class="vstep-content hidden flex-col p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-sm mr-3" style="background-color: #F25996;">03</div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 font-display">Bank &amp; UPI Payout Details</h3>
                                <p class="text-xs text-gray-500">Provide your bank account or UPI details for vendor settlements &amp; payouts.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Bank Account Details Grid -->
                    <div class="mb-5">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Primary Bank Account Details
                        </h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="bank_name" class="block text-xs font-bold text-gray-700 mb-1">Bank Name</label>
                                <input id="bank_name" name="bank_name" type="text" value="<?= htmlspecialchars($old['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank, SBI, ICICI" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="account_number" class="block text-xs font-bold text-gray-700">Account Number</label>
                                    <span id="acc_validation_msg" class="text-[11px] font-medium text-gray-400">9-18 digits only</span>
                                </div>
                                <div class="relative">
                                    <input id="account_number" name="account_number" type="text" inputmode="numeric" maxlength="18" value="<?= htmlspecialchars($old['account_number'] ?? '') ?>" placeholder="Bank account number (digits only)" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm font-mono tracking-wider transition" oninput="formatAndValidateAccount(this)" onblur="checkVendorFieldExists('account_number', this.value)">
                                    <div id="acc_valid_icon" class="absolute right-3 top-2.5 hidden text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                                <p id="account_number_error_msg" class="text-[11px] font-bold text-rose-500 mt-1 hidden"></p>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="ifsc_code" class="block text-xs font-bold text-gray-700">IFSC Code</label>
                                    <span id="ifsc_validation_msg" class="text-[11px] font-medium text-gray-400">11 chars (e.g. HDFC0001234)</span>
                                </div>
                                <div class="relative">
                                    <input id="ifsc_code" name="ifsc_code" type="text" maxlength="11" value="<?= htmlspecialchars($old['ifsc_code'] ?? '') ?>" placeholder="e.g. HDFC0001234" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm uppercase font-mono tracking-wider transition" oninput="formatAndValidateIFSC(this)">
                                    <div id="ifsc_valid_icon" class="absolute right-3 top-2.5 hidden text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="account_holder_name" class="block text-xs font-bold text-gray-700 mb-1">Account Holder Name</label>
                                <input id="account_holder_name" name="account_holder_name" type="text" value="<?= htmlspecialchars($old['account_holder_name'] ?? '') ?>" placeholder="Name as per bank account" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 2. UPI Payout Details Container -->
                    <div class="mb-5 p-4 rounded-2xl border" style="background-color: rgba(253, 191, 221, 0.2); border-color: #FDBFDD;">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-lg bg-[#FDBFDD] text-[#F25996] flex items-center justify-center font-bold text-xs">📱</span>
                                UPI Payment Details (Instant Settlements)
                            </h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white text-[#F25996] border border-[#FDBFDD]">GPay / PhonePe / Paytm / BHIM</span>
                        </div>
                        <p class="text-[11px] text-gray-600 mb-3">Enter your UPI Virtual Payment Address (VPA) and registered name for quick vendor settlements.</p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="upi_id" class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                    <span>UPI ID / VPA (Virtual Payment Address)</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="upi_id" name="upi_id" type="text" value="<?= htmlspecialchars($old['upi_id'] ?? '') ?>" placeholder="e.g. 9876543210@upi, store@okaxis" required class="w-full px-4 py-2 border border-pink-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm font-mono bg-white" onblur="checkVendorFieldExists('upi_id', this.value)">
                                <p id="upi_id_error_msg" class="text-[11px] font-bold text-rose-500 mt-1 hidden"></p>
                                <p class="text-[10px] text-gray-500 mt-1">Example: <code class="text-pink-600 font-mono font-bold">yourname@okaxis</code> or <code class="text-pink-600 font-mono font-bold">9876543210@paytm</code></p>
                            </div>
                            <div>
                                <label for="upi_name" class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                    <span>UPI Account Holder / Beneficiary Name</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="upi_name" name="upi_name" type="text" value="<?= htmlspecialchars($old['upi_name'] ?? '') ?>" placeholder="e.g. Business / Account Holder Name" required class="w-full px-4 py-2 border border-pink-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white">
                                <p class="text-[10px] text-gray-500 mt-1">Name registered on Google Pay / PhonePe / Paytm account.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Business Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-xs font-bold text-gray-700 mb-1">About Your Brand / Business Description</label>
                        <textarea id="description" name="description" rows="2" placeholder="Briefly describe products you manufacture or distribute..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] text-sm"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button type="button" onclick="goToVendorStep(2)" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                            &larr; Back
                        </button>
                        <button type="submit" id="submit-vendor-btn" class="px-8 py-2.5 text-white font-bold text-sm rounded-xl transition shadow-lg hover:shadow-xl hover:opacity-90" style="background: #F25996;">
                            Submit Vendor Application &rarr;
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// GST Certificate Upload (Max 2MB)
function handleGstCertChange(input) {
    const errorEl = document.getElementById('gst_size_error');
    const errorText = document.getElementById('gst_size_error_text');
    const promptEl = document.getElementById('gst_upload_prompt');
    const previewEl = document.getElementById('gst_cert_preview');
    const filenameEl = document.getElementById('gst_filename');
    const filesizeEl = document.getElementById('gst_filesize');
    const iconEl = document.getElementById('gst_preview_icon');

    if (!input.files || input.files.length === 0) {
        clearGstCertificate();
        return;
    }

    const file = input.files[0];
    const maxSizeBytes = 2 * 1024 * 1024; // 2MB

    // Validate size (2MB)
    if (file.size > maxSizeBytes) {
        const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
        if (errorText) errorText.innerText = `Selected file is ${sizeInMb} MB, which exceeds the 2MB limit. Please upload a certificate under 2MB.`;
        if (errorEl) errorEl.classList.remove('hidden');
        input.value = '';
        if (promptEl) promptEl.classList.remove('hidden');
        if (previewEl) previewEl.classList.add('hidden');
        return;
    }

    if (errorEl) errorEl.classList.add('hidden');

    // Display file details
    if (filenameEl) filenameEl.innerText = file.name;
    const formattedSize = file.size >= 1024 * 1024 
        ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
        : (file.size / 1024).toFixed(1) + ' KB';
    if (filesizeEl) filesizeEl.innerText = `${formattedSize} • Ready to upload`;

    // Preview Icon or Thumbnail
    if (iconEl) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                iconEl.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        } else {
            iconEl.innerHTML = '📄';
        }
    }

    if (promptEl) promptEl.classList.add('hidden');
    if (previewEl) previewEl.classList.remove('hidden');
}

function clearGstCertificate(e) {
    if (e) e.stopPropagation();
    const input = document.getElementById('gst_certificate');
    if (input) input.value = '';
    
    const promptEl = document.getElementById('gst_upload_prompt');
    const previewEl = document.getElementById('gst_cert_preview');
    const errorEl = document.getElementById('gst_size_error');

    if (promptEl) promptEl.classList.remove('hidden');
    if (previewEl) previewEl.classList.add('hidden');
    if (errorEl) errorEl.classList.add('hidden');
}

// Shop Images File List Holder
let selectedShopFiles = [];

function handleShopImagesChange(input) {
    if (!input.files || input.files.length === 0) return;
    
    selectedShopFiles = Array.from(input.files);
    renderShopImagesPreview();
}

function renderShopImagesPreview() {
    const grid = document.getElementById('shop_images_preview_grid');
    const label = document.getElementById('shop_images_count_label');
    grid.innerHTML = '';

    if (selectedShopFiles.length === 0) {
        grid.classList.add('hidden');
        label.innerText = 'Select one or multiple images';
        return;
    }

    grid.classList.remove('hidden');
    label.innerText = `${selectedShopFiles.length} photo(s) selected`;

    selectedShopFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const card = document.createElement('div');
            card.className = 'relative group rounded-xl overflow-hidden border border-indigo-200 bg-white shadow-sm';
            card.innerHTML = `
                <img src="${e.target.result}" class="w-full h-20 object-cover">
                <div class="p-1 text-[10px] text-gray-700 font-medium truncate bg-white/90 border-t border-gray-100">${file.name}</div>
                <button type="button" onclick="removeShopImage(${index})" class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow transition opacity-90 group-hover:opacity-100" title="Remove">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            grid.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
}

function removeShopImage(idx) {
    selectedShopFiles.splice(idx, 1);
    
    // Update dataTransfer on input
    const dataTransfer = new DataTransfer();
    selectedShopFiles.forEach(file => dataTransfer.items.add(file));
    document.getElementById('shop_images').files = dataTransfer.files;
    
    renderShopImagesPreview();
}

// Account Number Real-time Masking & Validation (9-18 digits numeric)
function formatAndValidateAccount(input) {
    // Strip non-digit characters
    input.value = input.value.replace(/[^0-9]/g, '').slice(0, 18);
    
    const val = input.value.trim();
    const msg = document.getElementById('acc_validation_msg');
    const icon = document.getElementById('acc_valid_icon');
    
    if (!val) {
        msg.innerText = '9-18 digits only';
        msg.className = 'text-[11px] font-medium text-gray-400';
        input.classList.remove('border-emerald-500', 'border-red-400', 'bg-emerald-50/20');
        icon.classList.add('hidden');
        return true;
    }
    
    const isValid = /^[0-9]{9,18}$/.test(val);
    if (isValid) {
        msg.innerText = '✓ Valid account format';
        msg.className = 'text-[11px] font-bold text-emerald-600';
        input.classList.add('border-emerald-500', 'bg-emerald-50/20');
        input.classList.remove('border-red-400');
        icon.classList.remove('hidden');
        return true;
    } else {
        msg.innerText = `Must be 9-18 digits (${val.length} digits entered)`;
        msg.className = 'text-[11px] font-bold text-red-500';
        input.classList.add('border-red-400');
        input.classList.remove('border-emerald-500', 'bg-emerald-50/20');
        icon.classList.add('hidden');
        return false;
    }
}

// IFSC Code Real-time Formatting & Validation (11 alphanumeric, standard Indian IFSC)
function formatAndValidateIFSC(input) {
    // Force uppercase and strip invalid chars
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 11);
    
    const val = input.value.trim();
    const msg = document.getElementById('ifsc_validation_msg');
    const icon = document.getElementById('ifsc_valid_icon');
    
    if (!val) {
        msg.innerText = '11 chars (e.g. HDFC0001234)';
        msg.className = 'text-[11px] font-medium text-gray-400';
        input.classList.remove('border-emerald-500', 'border-red-400', 'bg-emerald-50/20');
        icon.classList.add('hidden');
        return true;
    }
    
    const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
    if (ifscRegex.test(val)) {
        msg.innerText = '✓ Valid IFSC code';
        msg.className = 'text-[11px] font-bold text-emerald-600';
        input.classList.add('border-emerald-500', 'bg-emerald-50/20');
        input.classList.remove('border-red-400');
        icon.classList.remove('hidden');
        return true;
    } else {
        if (val.length < 11) {
            msg.innerText = `11 characters required (${val.length}/11)`;
        } else {
            msg.innerText = 'Invalid format. Format: 4 letters, 0, 6 alphanumeric (e.g. SBIN0001234)';
        }
        msg.className = 'text-[11px] font-bold text-red-500';
        input.classList.add('border-red-400');
        input.classList.remove('border-emerald-500', 'bg-emerald-50/20');
        icon.classList.add('hidden');
        return false;
    }
}

// GST vs Non-GST Selection Handler
function onGstOptionChange(val) {
    const isGst = val === '1';
    const yesLabel = document.getElementById('label_has_gst_yes');
    const noLabel = document.getElementById('label_has_gst_no');
    const gstFields = document.getElementById('section_gst_fields');
    const nonGstFields = document.getElementById('section_non_gst_fields');
    const gstPan = document.getElementById('gst_pan_number');
    const nonGstPan = document.getElementById('non_gst_pan_number');
    const docLabel = document.getElementById('doc_upload_label');

    if (isGst) {
        if (yesLabel) yesLabel.className = 'relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 border-[#F25996] bg-[#FDBFDD]/30 shadow-xs';
        if (noLabel) noLabel.className = 'relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 border-gray-200 bg-white hover:border-pink-200';
        if (gstFields) gstFields.classList.remove('hidden');
        if (nonGstFields) nonGstFields.classList.add('hidden');
        
        if (gstPan) {
            gstPan.name = 'pan_number';
            if (nonGstPan && nonGstPan.value && !gstPan.value) gstPan.value = nonGstPan.value;
        }
        if (nonGstPan) nonGstPan.removeAttribute('name');
        if (docLabel) docLabel.innerText = 'Upload GST Certificate / Business Proof (Optional)';
    } else {
        if (yesLabel) yesLabel.className = 'relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 border-gray-200 bg-white hover:border-pink-200';
        if (noLabel) noLabel.className = 'relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all duration-200 border-[#F25996] bg-[#FDBFDD]/30 shadow-xs';
        if (gstFields) gstFields.classList.add('hidden');
        if (nonGstFields) nonGstFields.classList.remove('hidden');
        
        if (nonGstPan) {
            nonGstPan.name = 'pan_number';
            if (gstPan && gstPan.value && !nonGstPan.value) nonGstPan.value = gstPan.value;
        }
        if (gstPan) gstPan.removeAttribute('name');
        if (docLabel) docLabel.innerText = 'Upload PAN Card / Business Identity Proof (Optional)';
    }
}

function handleGstInput(input) {
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 15);
    const val = input.value;
    const errEl = document.getElementById('gst_number_error_msg');

    if (val.length === 15) {
        if (!/[A-Z]/.test(val) || !/[0-9]/.test(val)) {
            if (errEl) {
                errEl.innerText = 'GST number must contain a mix of letters and numbers (cannot be all numbers). Example: 27AAAAA0000A1Z5';
                errEl.classList.remove('hidden');
            }
            input.classList.add('border-rose-400');
            return;
        }
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        if (!gstRegex.test(val)) {
            if (errEl) {
                errEl.innerText = 'Invalid GST Number format (e.g. 27AAAAA0000A1Z5). Must be a 15-character valid GSTIN.';
                errEl.classList.remove('hidden');
            }
            input.classList.add('border-rose-400');
            return;
        }
        if (errEl) errEl.classList.add('hidden');
        input.classList.remove('border-rose-400');
    } else if (val.length > 0 && val.length < 15) {
        if (errEl) {
            errEl.innerText = `15 alphanumeric characters required (${val.length}/15)`;
            errEl.classList.remove('hidden');
        }
        input.classList.add('border-rose-400');
    } else {
        if (errEl) errEl.classList.add('hidden');
        input.classList.remove('border-rose-400');
    }

    if (val.length >= 12) {
        const panPart = val.substring(2, 12);
        if (/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(panPart)) {
            const gstPan = document.getElementById('gst_pan_number');
            const nonGstPan = document.getElementById('non_gst_pan_number');
            if (gstPan) gstPan.value = panPart;
            if (nonGstPan) nonGstPan.value = panPart;
        }
    }
}

function handleNonGstPanInput(input) {
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
    const gstPan = document.getElementById('gst_pan_number');
    if (gstPan) gstPan.value = input.value;
}

function toggleCustomGstDropdown() {
    const menu = document.getElementById('custom_gst_reason_menu');
    const arrow = document.getElementById('custom_gst_reason_arrow');
    if (!menu) return;
    const isOpen = !menu.classList.contains('hidden');
    if (isOpen) {
        menu.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    } else {
        menu.classList.remove('hidden');
        if (arrow) arrow.classList.add('rotate-180');
    }
}

function selectCustomGstReason(val, label) {
    const select = document.getElementById('no_gst_reason');
    if (select) {
        select.value = val;
    }
    const textEl = document.getElementById('custom_gst_reason_text');
    if (textEl) {
        textEl.textContent = label;
        textEl.className = 'text-gray-900 font-semibold';
    }
    
    // Update active highlight classes
    document.querySelectorAll('.custom-reason-option').forEach(opt => {
        const optVal = opt.getAttribute('data-value');
        const checkIcon = opt.querySelector('.option-check-icon');
        if (optVal === val) {
            opt.className = 'custom-reason-option px-3.5 py-2.5 rounded-lg text-xs sm:text-sm font-bold cursor-pointer transition-all flex items-center justify-between bg-[#fdf2f7] text-[#F25996] border border-[#FDBFDD]';
            if (checkIcon) checkIcon.classList.remove('hidden');
        } else {
            opt.className = 'custom-reason-option px-3.5 py-2.5 rounded-lg text-xs sm:text-sm font-medium cursor-pointer transition-all flex items-center justify-between text-gray-700 hover:bg-[#fdf2f7] hover:text-[#F25996]';
            if (checkIcon) checkIcon.classList.add('hidden');
        }
    });

    toggleCustomGstDropdown();
    handleNonGstReasonChange(val);
}

// Close custom dropdown on outside click
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('custom_gst_reason_dropdown');
    const menu = document.getElementById('custom_gst_reason_menu');
    const arrow = document.getElementById('custom_gst_reason_arrow');
    if (dropdown && menu && !dropdown.contains(e.target)) {
        menu.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    }
});

function handleNonGstReasonChange(val) {
    const customContainer = document.getElementById('other_reason_container');
    if (val === 'Other') {
        if (customContainer) customContainer.classList.remove('hidden');
        const customInput = document.getElementById('other_gst_reason');
        if (customInput) customInput.focus();
    } else {
        if (customContainer) customContainer.classList.add('hidden');
    }
}

// GST Certificate Upload Handler (2MB size check & live preview)
function handleGstCertChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const maxSizeBytes = 2 * 1024 * 1024; // 2MB

    if (file.size > maxSizeBytes) {
        alert('File size exceeds the 2MB limit (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Please choose a file smaller than 2MB.');
        input.value = '';
        clearGstCertificate();
        return;
    }

    const previewContainer = document.getElementById('gst_cert_preview');
    const promptContainer = document.getElementById('gst_upload_prompt');
    const filenameEl = document.getElementById('gst_filename');
    const filesizeEl = document.getElementById('gst_filesize');
    const previewIcon = document.getElementById('gst_preview_icon');

    if (filenameEl) filenameEl.textContent = file.name;
    if (filesizeEl) {
        const sizeFormatted = file.size >= 1024 * 1024 
            ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' 
            : (file.size / 1024).toFixed(0) + ' KB';
        filesizeEl.textContent = sizeFormatted + ' • Ready to upload to Cloudinary';
    }

    if (previewIcon) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewIcon.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg" alt="GST Certificate">`;
            };
            reader.readAsDataURL(file);
        } else {
            previewIcon.innerHTML = `<span class="text-xl">📄</span>`;
        }
    }

    if (promptContainer) promptContainer.classList.add('hidden');
    if (previewContainer) {
        previewContainer.classList.remove('hidden');
        previewContainer.classList.add('flex');
    }
}

function clearGstCertificate(e) {
    if (e && e.stopPropagation) e.stopPropagation();
    const input = document.getElementById('gst_certificate');
    if (input) input.value = '';
    
    const previewContainer = document.getElementById('gst_cert_preview');
    const promptContainer = document.getElementById('gst_upload_prompt');
    const previewIcon = document.getElementById('gst_preview_icon');

    if (previewContainer) {
        previewContainer.classList.add('hidden');
        previewContainer.classList.remove('flex');
    }
    if (promptContainer) promptContainer.classList.remove('hidden');
    if (previewIcon) previewIcon.innerHTML = '📄';
}


function toggleVendorPassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('vendor-eye-icon');
    const eyeOffIcon = document.getElementById('vendor-eye-off-icon');

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) eyeIcon.classList.add('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) eyeIcon.classList.remove('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
    }
}

// Real-time inline duplicate verification
async function checkVendorFieldExists(field, value) {
    if (!value || !value.trim()) {
        hideFieldError(field);
        return true;
    }

    try {
        const response = await fetch('<?= BASE_URL ?>/api/check-vendor-exists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ field: field, value: value.trim() })
        });
        const data = await response.json();

        if (data.exists) {
            showFieldError(field, data.message);
            return false;
        } else {
            hideFieldError(field);
            return true;
        }
    } catch (err) {
        console.error("Duplicate check error for " + field, err);
        return true;
    }
}

function showFieldError(field, message) {
    const errorEl = document.getElementById(field + '_error_msg');
    const inputEl = document.getElementById(field);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
    if (inputEl) {
        inputEl.classList.add('border-rose-500', 'ring-1', 'ring-rose-500', 'bg-rose-50/20');
        inputEl.classList.remove('border-gray-200', 'border-pink-200');
    }
}

function hideFieldError(field) {
    const errorEl = document.getElementById(field + '_error_msg');
    const inputEl = document.getElementById(field);
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }
    if (inputEl) {
        inputEl.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500', 'bg-rose-50/20');
        if (field === 'upi_id' || field === 'upi_name') {
            inputEl.classList.add('border-pink-200');
        } else {
            inputEl.classList.add('border-gray-200');
        }
    }
}

async function validateVendorStep(currentStep) {
    if (currentStep === 1) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;

        if (!name || !email || !phone || !password) {
            alert('Please fill in all required fields in Step 1.');
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('email', 'Please enter a valid email address.');
            document.getElementById('email').focus();
            return false;
        }
        if (!/^[0-9]{10}$/.test(phone)) {
            showFieldError('phone', 'Mobile number must be exactly 10 digits.');
            document.getElementById('phone').focus();
            return false;
        }
        if (password.length < 6) {
            alert('Password must be at least 6 characters.');
            document.getElementById('password').focus();
            return false;
        }

        // Verify email and phone uniqueness
        const isEmailUnique = await checkVendorFieldExists('email', email);
        if (!isEmailUnique) {
            document.getElementById('email').focus();
            return false;
        }

        const isPhoneUnique = await checkVendorFieldExists('phone', phone);
        if (!isPhoneUnique) {
            document.getElementById('phone').focus();
            return false;
        }

        return true;
    }
    
    if (currentStep === 2) {
        const storeName = document.getElementById('store_name').value.trim();
        const pincode = document.getElementById('pincode').value.trim();
        const address = document.getElementById('address').value.trim();
        const city = document.getElementById('city').value.trim();
        const state = document.getElementById('state').value.trim();

        if (!storeName || !pincode || !address || !city || !state) {
            alert('Please fill in all required fields in Step 2.');
            return false;
        }
        if (!/^[0-9]{6}$/.test(pincode)) {
            alert('Pincode must be exactly 6 digits.');
            document.getElementById('pincode').focus();
            return false;
        }

        const hasGstRadio = document.querySelector('input[name="has_gst"]:checked');
        const isGst = hasGstRadio ? hasGstRadio.value === '1' : true;

        if (isGst) {
            const gstNum = document.getElementById('gst_number').value.trim();
            if (!gstNum) {
                alert('Please enter your 15-digit GST Number, or select "Non-GST / Unregistered Vendor" if you do not have GST.');
                document.getElementById('gst_number').focus();
                return false;
            }
            if (!/[A-Z]/.test(gstNum) || !/[0-9]/.test(gstNum)) {
                showFieldError('gst_number', 'GST number must contain a mix of letters and numbers (cannot be all numbers). Example: 27AAAAA0000A1Z5');
                document.getElementById('gst_number').focus();
                return false;
            }
            const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if (!gstRegex.test(gstNum)) {
                showFieldError('gst_number', 'Invalid GST Number format (e.g. 27AAAAA0000A1Z5). Must be a 15-character valid GSTIN.');
                document.getElementById('gst_number').focus();
                return false;
            }

            const isGstUnique = await checkVendorFieldExists('gst_number', gstNum);
            if (!isGstUnique) {
                document.getElementById('gst_number').focus();
                return false;
            }
        } else {
            const panNum = document.getElementById('non_gst_pan_number').value.trim();
            const reason = document.getElementById('no_gst_reason').value;
            const otherReason = document.getElementById('other_gst_reason').value.trim();

            if (!panNum) {
                alert('PAN Number is required for Non-GST vendor registration.');
                document.getElementById('non_gst_pan_number').focus();
                return false;
            }
            const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
            if (!panRegex.test(panNum)) {
                alert('Invalid PAN Number format (e.g. ABCDE1234F). Must be 5 letters, 4 numbers, and 1 letter.');
                document.getElementById('non_gst_pan_number').focus();
                return false;
            }
            if (!reason) {
                alert('Please select an Exemption Reason for not having a GST registration.');
                document.getElementById('no_gst_reason').focus();
                return false;
            }
            if (reason === 'Other' && !otherReason) {
                alert('Please specify your custom reason for Non-GST exemption.');
                document.getElementById('other_gst_reason').focus();
                return false;
            }
        }

        return true;
    }

    return true;
}

async function goToVendorStep(step, skipValidation = false) {
    const currentActiveStep = !document.getElementById('vstep-1').classList.contains('hidden') ? 1 :
                             (!document.getElementById('vstep-2').classList.contains('hidden') ? 2 : 3);

    // Only validate when going forward and not skipping validation (e.g. from page refresh)
    if (!skipValidation && step > currentActiveStep) {
        const isValid = await validateVendorStep(currentActiveStep);
        if (!isValid) {
            return;
        }
    }

    document.querySelectorAll('.vstep-content').forEach(el => el.classList.add('hidden'));
    const targetEl = document.getElementById('vstep-' + step);
    if (targetEl) targetEl.classList.remove('hidden');

    const progressLine = document.getElementById('vendor-progress-line');
    if (progressLine) {
        if (step === 1) progressLine.style.width = '0%';
        else if (step === 2) progressLine.style.width = '50%';
        else if (step === 3) progressLine.style.width = '100%';
    }
    setActiveStepIcon(step);

    // Save active step in sessionStorage & browser URL hash
    try {
        sessionStorage.setItem('grm_vendor_reg_step', step.toString());
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#step-' + step);
        }
    } catch(e) {}

    if (!skipValidation) {
        window.scrollTo({ top: 120, behavior: 'smooth' });
    }
}

function setActiveStepIcon(activeStep) {
    for (let i = 1; i <= 3; i++) {
        const icon = document.getElementById('vstep-' + i + '-icon');
        const text = document.getElementById('vstep-' + i + '-text');
        if (i <= activeStep) {
            icon.className = 'w-10 h-10 rounded-full bg-[#F25996] text-white flex items-center justify-center shadow-md mb-2 transition-colors duration-300';
            text.className = 'text-xs font-bold text-[#F25996]';
        } else {
            icon.className = 'w-10 h-10 rounded-full bg-gray-100 border-2 border-gray-200 text-gray-400 flex items-center justify-center mb-2 transition-colors duration-300';
            text.className = 'text-xs font-bold text-gray-500';
        }
    }
}

// Form submit validation
document.getElementById('vendor-multi-step-form').addEventListener('submit', async function(e) {
    const accInput = document.getElementById('account_number');
    const ifscInput = document.getElementById('ifsc_code');
    const upiIdInput = document.getElementById('upi_id');
    const upiNameInput = document.getElementById('upi_name');

    if (accInput.value.trim() && !/^[0-9]{9,18}$/.test(accInput.value.trim())) {
        e.preventDefault();
        showFieldError('account_number', 'Bank account number must be between 9 and 18 numeric digits.');
        goToVendorStep(3, true);
        accInput.focus();
        return false;
    }

    if (ifscInput.value.trim() && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscInput.value.trim())) {
        e.preventDefault();
        alert('Invalid IFSC code format (e.g. SBIN0001234). Must be 11 characters starting with 4 letters, 0, and 6 alphanumeric characters.');
        goToVendorStep(3, true);
        ifscInput.focus();
        return false;
    }

    if (!upiIdInput || !upiIdInput.value.trim()) {
        e.preventDefault();
        showFieldError('upi_id', 'Please enter your UPI ID / VPA.');
        goToVendorStep(3, true);
        if (upiIdInput) upiIdInput.focus();
        return false;
    }

    if (!/^[\w.\-_]{2,256}@[a-zA-Z]{2,64}$/.test(upiIdInput.value.trim())) {
        e.preventDefault();
        showFieldError('upi_id', 'Invalid UPI ID format (e.g. 9876543210@upi or store@okaxis).');
        goToVendorStep(3, true);
        upiIdInput.focus();
        return false;
    }

    if (!upiNameInput || !upiNameInput.value.trim()) {
        e.preventDefault();
        alert('Please enter the UPI Account Holder / Beneficiary Name.');
        goToVendorStep(3, true);
        if (upiNameInput) upiNameInput.focus();
        return false;
    }

    // Verify uniqueness for bank account & UPI ID
    if (accInput.value.trim()) {
        const accUnique = await checkVendorFieldExists('account_number', accInput.value.trim());
        if (!accUnique) {
            e.preventDefault();
            goToVendorStep(3, true);
            accInput.focus();
            return false;
        }
    }

    const upiUnique = await checkVendorFieldExists('upi_id', upiIdInput.value.trim());
    if (!upiUnique) {
        e.preventDefault();
        goToVendorStep(3, true);
        upiIdInput.focus();
        return false;
    }

    try {
        sessionStorage.removeItem('grm_vendor_reg_step');
    } catch(e) {}
});

document.addEventListener('DOMContentLoaded', function() {
    const phpStep = <?= (int)($initialStep ?? 1) ?>;
    let hashStep = 0;
    if (window.location.hash) {
        const parsed = parseInt(window.location.hash.replace(/\D/g, ''));
        if (parsed >= 1 && parsed <= 3) hashStep = parsed;
    }
    const savedStep = parseInt(sessionStorage.getItem('grm_vendor_reg_step') || '0');

    let activeStep = 1;
    if (phpStep > 1) {
        activeStep = phpStep;
    } else if (hashStep >= 1 && hashStep <= 3) {
        activeStep = hashStep;
    } else if (savedStep >= 1 && savedStep <= 3) {
        activeStep = savedStep;
    }

    if (activeStep > 1) {
        goToVendorStep(activeStep, true);
    } else {
        goToVendorStep(1, true);
    }

    const accInput = document.getElementById('account_number');
    const ifscInput = document.getElementById('ifsc_code');
    if (accInput && accInput.value) formatAndValidateAccount(accInput);
    if (ifscInput && ifscInput.value) formatAndValidateIFSC(ifscInput);
    onGstOptionChange('<?= $hasGstVal ?>');
});

</script>
