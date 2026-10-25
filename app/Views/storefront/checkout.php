<style>
    .form-input {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: #111827;
        transition: all 0.2s ease-in-out;
        outline: none;
    }
    .form-input:focus,
    input.form-input:focus,
    textarea.form-input:focus {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
        outline: none !important;
    }
    .form-input.has-error,
    .cs-trigger.has-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
    }
    .field-error-msg {
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 500;
        margin-top: 0.375rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .field-error-msg.hidden {
        display: none !important;
    }

    /* ── Custom Select Dropdown ── */
    .cs-wrapper {
        position: relative;
        width: 100%;
    }
    .cs-trigger {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 0.625rem 2.25rem 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: #111827;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s ease-in-out;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }
    .cs-trigger.placeholder { color: #9ca3af; }
    .cs-trigger:focus,
    .cs-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
    }
    .cs-arrow {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: #9ca3af;
        transition: transform 0.2s ease;
        pointer-events: none;
    }
    .cs-trigger.open .cs-arrow { transform: rotate(180deg); color: #F25996; }
    .cs-list {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        z-index: 9999;
        max-height: 220px;
        overflow-y: auto;
        display: none;
        padding: 4px;
    }
    .cs-list.open { display: block; }
    .cs-list::-webkit-scrollbar { width: 4px; }
    .cs-list::-webkit-scrollbar-track { background: transparent; }
    .cs-list::-webkit-scrollbar-thumb { background: #F25996; border-radius: 4px; }
    .cs-option {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.5rem;
        cursor: pointer;
        color: #374151;
        transition: background 0.15s, color 0.15s;
    }
    .cs-option:hover {
        background: #fce7f3;
        color: #F25996;
    }
    .cs-option.selected {
        background: #F25996;
        color: #ffffff;
        font-weight: 600;
    }
    .cs-option.disabled {
        color: #827777ff;
        cursor: default;
        pointer-events: none;
    }
    .modal-scroll::-webkit-scrollbar { width: 5px; }
    .modal-scroll::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 4px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #F25996; border-radius: 4px; }
</style>

<div class="bg-gray-50 py-12 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 font-display mb-6 md:mb-8 text-center">Secure Checkout</h1>

        <div class="flex flex-col lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Accordion Form Column — appears BELOW summary on mobile/tablet -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-4 order-2 lg:order-1 mt-6 lg:mt-0">
                <!-- Step 1: Shipping Address -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm" id="step-1-container">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 cursor-pointer" onclick="openStep(1)">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                <span id="badge-step-1" class="w-6 h-6 rounded-full bg-[#F25996] text-white text-xs flex items-center justify-center mr-3 font-bold">1</span>
                                Shipping Address
                            </h2>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200 rotate-90 shrink-0" id="icon-step-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        </div>
                        <div id="review-address-display" class="ml-9 mt-2 hidden"></div>
                    </div>
                    <div class="p-4 sm:p-6 block" id="content-step-1">
                        <form id="shipping-form" novalidate onsubmit="event.preventDefault(); proceedFromAddress();">
                            <?php if (!empty($addresses)): ?>
                                <!-- Saved Address Cards -->
                                <p class="text-sm font-semibold text-gray-700 mb-3">Select a delivery address</p>
                                <div class="space-y-3 mb-5" id="saved-addresses">
                                    <?php foreach ($addresses as $i => $addr): ?>
                                        <label class="flex items-start gap-3 sm:gap-4 p-3.5 sm:p-4 border-2 rounded-xl cursor-pointer transition-all address-card <?= $addr['is_default'] ? 'border-[#F25996] bg-pink-50/50' : 'border-gray-200 hover:border-pink-300' ?>"
                                               for="addr_<?= $addr['id'] ?>">
                                            <input type="radio" id="addr_<?= $addr['id'] ?>" name="selected_address" value="<?= $addr['id'] ?>"
                                                   data-line1="<?= htmlspecialchars($addr['line1']) ?>"
                                                   data-line2="<?= htmlspecialchars($addr['line2'] ?? '') ?>"
                                                   data-city="<?= htmlspecialchars($addr['city']) ?>"
                                                   data-state="<?= htmlspecialchars($addr['state']) ?>"
                                                   data-postal="<?= htmlspecialchars($addr['postal_code']) ?>"
                                                   data-country="<?= htmlspecialchars($addr['country']) ?>"
                                                   data-label="<?= htmlspecialchars($addr['label'] ?? '') ?>"
                                                   <?= $addr['is_default'] ? 'checked' : '' ?>
                                                   class="mt-1 w-4 h-4 accent-[#F25996] flex-shrink-0"
                                                   onchange="highlightCard(this)">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <?php if (!empty($addr['label'])): ?>
                                                            <span class="inline-block text-xs font-bold text-[#F25996] uppercase tracking-wide"><?= htmlspecialchars($addr['label']) ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($addr['is_default']): ?>
                                                            <span class="text-[11px] font-bold bg-[#F25996] text-white px-2 py-0.5 rounded-full leading-tight">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button type="button" class="text-xs font-semibold text-[#F25996] hover:underline focus:outline-none py-0.5 ml-auto" onclick="event.preventDefault(); openEditAddressModal(<?= htmlspecialchars(json_encode($addr)) ?>)">Edit</button>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-800 break-words"><?= htmlspecialchars($addr['line1']) ?></p>
                                                <?php if (!empty($addr['line2'])): ?>
                                                    <p class="text-sm text-gray-600 break-words"><?= htmlspecialchars($addr['line2']) ?></p>
                                                <?php endif; ?>
                                                <p class="text-sm text-gray-600 break-words">
                                                    <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> &ndash; <?= htmlspecialchars($addr['postal_code']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($addr['country']) ?></p>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>

                                    <!-- Option: Enter a different address -->
                                    <label class="flex items-start gap-3 sm:gap-4 p-3.5 sm:p-4 border-2 rounded-xl cursor-pointer transition-all address-card border-gray-200 hover:border-pink-300"
                                           for="addr_new">
                                        <input type="radio" id="addr_new" name="selected_address" value="new"
                                               class="mt-1 w-4 h-4 accent-[#F25996] flex-shrink-0"
                                               onchange="highlightCard(this); toggleManualEntry(true)">
                                        <div id="addr_new_content" class="flex-1 w-full min-w-0">
                                            <div class="flex justify-between items-center mb-1">
                                                <p class="text-sm font-semibold text-gray-800" id="addr_new_title">+ Enter a different address</p>
                                            </div>
                                            <p class="text-xs text-gray-500" id="addr_new_desc">Type a new delivery address below</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Manual entry (hidden by default when saved addresses exist) -->
                                <div id="manual-entry" class="hidden space-y-4 border-t border-gray-100 pt-4 mt-2">
                                    <div>
                                        <label class="form-label" for="ship_line1">Address Line 1 <span class="text-[#F25996]">*</span></label>
                                        <input type="text" id="ship_line1" required class="form-input" placeholder="House/Flat no., Building, Street">
                                        <div id="ship_line1-error" class="field-error-msg hidden">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="error-msg">Please enter Address Line 1</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label" for="ship_line2">Address Line 2 <span class="text-gray-400 font-normal">(optional)</span></label>
                                        <input type="text" id="ship_line2" class="form-input" placeholder="Area, Locality, Landmark">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label" for="ship_state">State <span class="text-[#F25996]">*</span></label>
                                            <select id="ship_state" name="state" required onchange="toggleShipDistrictFields()" class="form-input">
                                                <option value="">Select State</option>
                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                <option value="Others">Others</option>
                                            </select>
                                            <div id="ship_state-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please select a State</span>
                                            </div>
                                        </div>
                                        <!-- When TN: District Select Dropdown beside State -->
                                        <div id="ship_tn_district_container">
                                            <label class="form-label" id="ship_district_label" for="ship_district_select">District/City <span class="text-[#F25996]">*</span></label>
                                            <select id="ship_district_select" name="city" class="form-input">
                                                <option value="">Select District</option>
                                                <option value="Ariyalur">Ariyalur</option>
                                                <option value="Chengalpattu">Chengalpattu</option>
                                                <option value="Chennai">Chennai</option>
                                                <option value="Coimbatore">Coimbatore</option>
                                                <option value="Cuddalore">Cuddalore</option>
                                                <option value="Dharmapuri">Dharmapuri</option>
                                                <option value="Dindigul">Dindigul</option>
                                                <option value="Erode">Erode</option>
                                                <option value="Kallakurichi">Kallakurichi</option>
                                                <option value="Kanchipuram">Kanchipuram</option>
                                                <option value="Kanyakumari">Kanyakumari</option>
                                                <option value="Karur">Karur</option>
                                                <option value="Krishnagiri">Krishnagiri</option>
                                                <option value="Madurai">Madurai</option>
                                                <option value="Mayiladuthurai">Mayiladuthurai</option>
                                                <option value="Nagapattinam">Nagapattinam</option>
                                                <option value="Namakkal">Namakkal</option>
                                                <option value="Nilgiris">Nilgiris</option>
                                                <option value="Perambalur">Perambalur</option>
                                                <option value="Pudukkottai">Pudukkottai</option>
                                                <option value="Ramanathapuram">Ramanathapuram</option>
                                                <option value="Ranipet">Ranipet</option>
                                                <option value="Salem">Salem</option>
                                                <option value="Sivaganga">Sivaganga</option>
                                                <option value="Tenkasi">Tenkasi</option>
                                                <option value="Thanjavur">Thanjavur</option>
                                                <option value="Theni">Theni</option>
                                                <option value="Thoothukudi">Thoothukudi</option>
                                                <option value="Tiruchirappalli">Tiruchirappalli (Trichy)</option>
                                                <option value="Tirunelveli">Tirunelveli</option>
                                                <option value="Tirupathur">Tirupathur</option>
                                                <option value="Tiruppur">Tiruppur</option>
                                                <option value="Tiruvallur">Tiruvallur</option>
                                                <option value="Tiruvannamalai">Tiruvannamalai</option>
                                                <option value="Tiruvarur">Tiruvarur</option>
                                                <option value="Vellore">Vellore</option>
                                                <option value="Viluppuram">Viluppuram</option>
                                                <option value="Virudhunagar">Virudhunagar</option>
                                            </select>
                                            <div id="ship_district_select-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please select your District</span>
                                            </div>
                                        </div>

                                        <!-- When Others: State Name Input beside State (UP) -->
                                        <div id="ship_other_state_container" class="hidden">
                                            <label class="form-label" for="ship_other_state_name">State Name <span class="text-[#F25996]">*</span></label>
                                            <input id="ship_other_state_name" name="other_state_name" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter state name (e.g. Kerala, Karnataka)" autocomplete="off" class="form-input w-full" disabled>
                                            <div id="ship_other_state_name-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter State Name</span>
                                            </div>
                                        </div>

                                        <!-- When Others: District/City Input below State & State Name (DOWN) -->
                                        <div id="ship_other_district_container" class="hidden sm:col-span-2">
                                            <label class="form-label" for="ship_district_text">District/City <span class="text-[#F25996]">*</span></label>
                                            <input id="ship_district_text" name="city" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter District / City Name" autocomplete="off" class="form-input w-full" disabled>
                                            <div id="ship_district_text-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter District / City</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label" for="ship_pincode">Pincode <span class="text-[#F25996]">*</span></label>
                                            <input type="text" id="ship_pincode" required pattern="[0-9]{6}" maxlength="6" oninput="this.value=this.value.replace(/\D/g,'')" class="form-input" placeholder="6-digit pincode">
                                            <div id="ship_pincode-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter a valid 6-digit Pincode</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label" for="ship_country">Country</label>
                                            <input type="text" id="ship_country" value="India" class="form-input" readonly>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-gray-100">
                                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" id="save_manual_address_check" checked class="w-4 h-4 rounded text-[#F25996] accent-[#F25996] focus:ring-0">
                                            <span class="text-xs sm:text-sm font-semibold text-gray-700">Save this address to my profile</span>
                                        </label>
                                        <button type="button" id="save-manual-addr-btn" onclick="saveManualAddressAjax()" class="text-xs sm:text-sm font-bold text-[#F25996] hover:text-[#d8407d] hover:underline cursor-pointer inline-flex items-center gap-1 self-start sm:self-auto py-1">
                                            <span>+ Save Address to Profile</span>
                                        </button>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- No saved addresses — show full manual form -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="form-label">Full Name</label>
                                        <input type="text" id="ship_name" required class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="form-label">Complete Address <span class="text-[#F25996]">*</span></label>
                                        <textarea id="ship_line1" rows="3" required class="form-input" placeholder="Enter full address..."><?= htmlspecialchars($profile['shop_location'] ?? '') ?></textarea>
                                        <div id="ship_line1-error-fallback" class="field-error-msg hidden">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="error-msg">Please enter your Address</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label">State <span class="text-[#F25996]">*</span></label>
                                            <select id="ship_state" name="state" required onchange="toggleShipDistrictFields()" class="form-input">
                                                <option value="">Select State</option>
                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                <option value="Others">Others</option>
                                            </select>
                                            <div id="ship_state-error-fallback" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please select a State</span>
                                            </div>
                                        </div>
                                        <!-- When TN: District Select Dropdown beside State -->
                                        <div id="ship_fallback_tn_district_container">
                                            <label class="form-label" id="ship_fallback_district_label">District/City <span class="text-[#F25996]">*</span></label>
                                            <select id="ship_fallback_district_select" name="city" class="form-input">
                                                <option value="">Select District</option>
                                                <option value="Ariyalur">Ariyalur</option>
                                                <option value="Chengalpattu">Chengalpattu</option>
                                                <option value="Chennai">Chennai</option>
                                                <option value="Coimbatore">Coimbatore</option>
                                                <option value="Cuddalore">Cuddalore</option>
                                                <option value="Dharmapuri">Dharmapuri</option>
                                                <option value="Dindigul">Dindigul</option>
                                                <option value="Erode">Erode</option>
                                                <option value="Kallakurichi">Kallakurichi</option>
                                                <option value="Kanchipuram">Kanchipuram</option>
                                                <option value="Kanyakumari">Kanyakumari</option>
                                                <option value="Karur">Karur</option>
                                                <option value="Krishnagiri">Krishnagiri</option>
                                                <option value="Madurai">Madurai</option>
                                                <option value="Mayiladuthurai">Mayiladuthurai</option>
                                                <option value="Nagapattinam">Nagapattinam</option>
                                                <option value="Namakkal">Namakkal</option>
                                                <option value="Nilgiris">Nilgiris</option>
                                                <option value="Perambalur">Perambalur</option>
                                                <option value="Pudukkottai">Pudukkottai</option>
                                                <option value="Ramanathapuram">Ramanathapuram</option>
                                                <option value="Ranipet">Ranipet</option>
                                                <option value="Salem">Salem</option>
                                                <option value="Sivaganga">Sivaganga</option>
                                                <option value="Tenkasi">Tenkasi</option>
                                                <option value="Thanjavur">Thanjavur</option>
                                                <option value="Theni">Theni</option>
                                                <option value="Thoothukudi">Thoothukudi</option>
                                                <option value="Tiruchirappalli">Tiruchirappalli (Trichy)</option>
                                                <option value="Tirunelveli">Tirunelveli</option>
                                                <option value="Tirupathur">Tirupathur</option>
                                                <option value="Tiruppur">Tiruppur</option>
                                                <option value="Tiruvallur">Tiruvallur</option>
                                                <option value="Tiruvannamalai">Tiruvannamalai</option>
                                                <option value="Tiruvarur">Tiruvarur</option>
                                                <option value="Vellore">Vellore</option>
                                                <option value="Viluppuram">Viluppuram</option>
                                                <option value="Virudhunagar">Virudhunagar</option>
                                            </select>
                                            <div id="ship_fallback_district_select-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please select your District</span>
                                            </div>
                                        </div>

                                        <!-- When Others: State Name Input beside State (UP) -->
                                        <div id="ship_fallback_other_state_container" class="hidden">
                                            <label class="form-label" id="ship_fallback_other_state_label">State Name <span class="text-[#F25996]">*</span></label>
                                            <input id="ship_fallback_other_state_name" name="other_state_name" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter state name (e.g. Kerala, Karnataka)" autocomplete="off" class="form-input w-full" disabled>
                                            <div id="ship_fallback_other_state_name-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter your State Name</span>
                                            </div>
                                        </div>

                                        <!-- When Others: District/City Input below State & State Name (DOWN) -->
                                        <div id="ship_fallback_other_district_container" class="hidden sm:col-span-2">
                                            <label class="form-label">District/City <span class="text-[#F25996]">*</span></label>
                                            <input id="ship_fallback_district_text" name="city" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter District / City Name" autocomplete="off" class="form-input w-full" disabled>
                                            <div id="ship_fallback_district_text-error" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter District / City</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label">Pincode <span class="text-[#F25996]">*</span></label>
                                            <input type="text" id="ship_pincode" required pattern="[0-9]{6}" maxlength="6" oninput="this.value=this.value.replace(/\D/g,'')" class="form-input" placeholder="6-digit pincode">
                                            <div id="ship_pincode-error-fallback" class="field-error-msg hidden">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="error-msg">Please enter a valid 6-digit Pincode</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-gray-100">
                                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" id="save_fallback_address_check" checked class="w-4 h-4 rounded text-[#F25996] accent-[#F25996] focus:ring-0">
                                            <span class="text-xs sm:text-sm font-semibold text-gray-700">Save this address to my profile</span>
                                        </label>
                                        <button type="button" id="save-fallback-addr-btn" onclick="saveManualAddressAjax()" class="text-xs sm:text-sm font-bold text-[#F25996] hover:text-[#d8407d] hover:underline cursor-pointer inline-flex items-center gap-1 self-start sm:self-auto py-1">
                                            <span>+ Save Address to Profile</span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-6 flex flex-col items-center gap-3">
                                <button type="submit" class="w-full bg-[#F25996] hover:bg-[#d8407d] text-white font-bold py-3 px-6 text-sm sm:text-base rounded-xl transition-all shadow-md shadow-[#F25996]/20 active:scale-[0.99] cursor-pointer whitespace-nowrap flex items-center justify-center tracking-wide">
                                    Continue to Payment
                                </button>
                                <button type="button" onclick="openAddAddressModal()" class="text-sm sm:text-base text-[#F25996] hover:underline font-bold py-1.5 cursor-pointer bg-transparent border-0 outline-none inline-flex items-center justify-center gap-1 transition-colors">
                                    <span class="text-base font-bold">+</span>
                                    <span>Manage addresses</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 2: Payment Method (previously Step 3) -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm" id="step-3-container">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center cursor-pointer" onclick="openStep(3)">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-[#F25996] text-white text-xs flex items-center justify-center mr-3 font-bold" id="badge-step-3">2</span>
                            Payment
                        </h2>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200 rotate-0 shrink-0" id="icon-step-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </div>
                    <div class="p-6 hidden" id="content-step-3">
                        <form id="payment-form" action="<?= BASE_URL ?>/checkout/place-order" method="POST">
                            <?php
                            $defaultState = '';
                            if (!empty($addresses)) {
                                foreach ($addresses as $addr) {
                                    if ($addr['is_default']) {
                                        $defaultState = $addr['state'];
                                        break;
                                    }
                                }
                                if (empty($defaultState) && !empty($addresses)) {
                                    $defaultState = $addresses[0]['state'];
                                }
                            }
                            ?>
                            <input type="hidden" name="shipping_address" id="final_shipping_address">
                            <input type="hidden" name="shipping_state" id="final_shipping_state" value="<?= htmlspecialchars($defaultState) ?>">
                            <input type="hidden" name="payment_method" id="final_payment_method" value="Online">
                            <input type="hidden" name="grand_total" id="final_grand_total" value="<?= $subtotal ?>">
                            <input type="hidden" name="cod_advance" id="final_cod_advance" value="0">
                            <input type="hidden" name="balance_amount" id="final_balance_amount" value="<?= $subtotal ?>">
                            <input type="hidden" name="is_new_address" id="final_is_new_address" value="0">
                            <input type="hidden" name="new_addr_line1" id="final_new_addr_line1" value="">
                            <input type="hidden" name="new_addr_line2" id="final_new_addr_line2" value="">
                            <input type="hidden" name="new_addr_city" id="final_new_addr_city" value="">
                            <input type="hidden" name="new_addr_pincode" id="final_new_addr_pincode" value="">
                            <input type="hidden" name="new_addr_country" id="final_new_addr_country" value="">

                            <!-- Payment Method Selection -->
                            <p class="text-xs sm:text-sm font-semibold text-gray-700 mb-2.5">Select Payment Method</p>
                            <div class="space-y-2.5 mb-5" id="payment-methods">
                                <label class="flex items-center p-3 sm:p-3.5 border-2 border-[#F25996] rounded-xl bg-[#F25996] text-white cursor-pointer payment-card shadow-xs transition-all" for="pm_online">
                                    <div class="relative flex items-center justify-center shrink-0">
                                        <input type="radio" id="pm_online" name="pm_choice" value="Online" checked class="peer sr-only" onchange="onPaymentMethodChange('Online')">
                                        <div class="w-4.5 h-4.5 rounded-full bg-white flex items-center justify-center border-2 border-white shadow-2xs" style="width: 18px; height: 18px;">
                                            <div class="rounded-full bg-[#F25996]" style="width: 9px; height: 9px;"></div>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex flex-col">
                                        <span class="block text-xs sm:text-sm font-semibold text-white tracking-wide">Full payment via Razorpay secure gateway.</span>
                                    </div>
                                </label>
                            </div>
                            <!-- Hidden stubs so JS refs don't crash -->
                            <div id="cod-warning" class="hidden"></div>
                            <div id="cod-details-box" class="hidden"></div>
                            <span id="cod-advance-badge" class="hidden"></span>

                            <button type="submit" id="pay-btn" class="w-full bg-[#F25996] hover:bg-[#d8407d] text-white font-bold py-2.5 sm:py-3 text-sm sm:text-base flex justify-center items-center rounded-xl transition-all shadow-md shadow-[#F25996]/20 active:scale-[0.99] cursor-pointer tracking-wide">
                                <svg class="w-4 h-4 mr-2 animate-spin hidden" id="pay-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span id="pay-btn-label">Pay ₹<?= format_price($subtotal) ?></span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Summary Column — shows FIRST on mobile/tablet -->
            <div class="lg:col-span-5 xl:col-span-4 order-1 lg:order-2">
                <!-- Order Summary Card -->
                <div class="bg-white border border-gray-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 lg:p-7 lg:sticky lg:top-24 shadow-sm">
                    <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-2xl font-bold text-[#0f172a] mb-5 sm:mb-6 md:mb-7 lg:mb-6 font-display">Your Order</h2>
                    
                    <!-- Products List -->
                    <ul class="space-y-3.5 sm:space-y-4 md:space-y-5 lg:space-y-4 mb-5 sm:mb-6 md:mb-7 lg:mb-6">
                        <?php foreach($cartItems as $item):
                            $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : (float)($item['variant_discount_price'] ?: $item['variant_price'] ?: $item['wholesale_price']);
                            $rawImg = $item['primary_image'] ?? '';
                            if (empty($rawImg)) {
                                $imgUrl = 'https://placehold.co/80x80/f3f4f6/9ca3af?text=Img';
                            } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
                                $imgUrl = $rawImg; // already absolute (Cloudinary, etc.)
                            } else {
                                $imgUrl = BASE_URL . '/' . ltrim($rawImg, '/');
                            }
                        ?>
                            <li class="flex items-start justify-between gap-3 text-sm md:text-base lg:text-sm pb-3.5 sm:pb-4 md:pb-5 lg:pb-4 border-b border-gray-100">
                                <div class="flex items-start gap-3 md:gap-4 min-w-0 flex-1">
                                    <img src="<?= $imgUrl ?>" class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 lg:w-16 lg:h-16 object-cover rounded-lg border border-gray-100 shrink-0 shadow-sm" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div class="flex-1 min-w-0">
                                        <span class="font-bold text-[#1e293b] block leading-tight mb-1 text-sm md:text-lg lg:text-sm"><?= htmlspecialchars($item['name']) ?></span>
                                        <div class="text-[#64748b] text-xs md:text-base lg:text-xs">
                                            <?php if (!empty($item['variant_name'])): ?>
                                                <span class="mr-1"><?= htmlspecialchars($item['variant_name']) ?>:</span>
                                            <?php endif; ?>
                                            <?php 
                                                $unitWeight = !empty($item['weight']) ? floatval($item['weight']) : 1.0;
                                                $totalWeight = $unitWeight * $item['quantity'];
                                                $weightUnit = htmlspecialchars($item['weight_unit'] ?? 'kg');
                                            ?>
                                            <span><?= $totalWeight . ' ' . $weightUnit ?></span>
                                        </div>
                                        <div class="mt-1.5 md:mt-2.5 lg:mt-1.5 flex items-center">
                                            <div class="px-2 md:px-3 py-0.5 md:py-1 lg:px-2 lg:py-0.5 border border-gray-200 rounded bg-white text-xs md:text-sm lg:text-xs font-semibold text-gray-700">
                                                Qty: <?= $item['quantity'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="font-bold text-[#F25996] whitespace-nowrap mt-1 shrink-0 text-sm md:text-lg lg:text-sm">₹<?= format_price($unitPrice * $item['quantity']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Fee breakdown -->
                    <dl class="space-y-2.5 sm:space-y-3 md:space-y-4 lg:space-y-3 text-sm sm:text-[15px] md:text-lg lg:text-[15px] text-[#0f172a]" id="fee-summary">
                        <div class="flex justify-between items-center gap-2">
                            <dt class="whitespace-nowrap">Subtotal</dt>
                            <dd class="font-bold text-[#F25996] whitespace-nowrap">₹<?= format_price($subtotal) ?></dd>
                        </div>
                        
                        <!-- Dynamic fee rows inserted here by JS -->
                        <div id="dynamic-fees" class="space-y-2.5 sm:space-y-3 md:space-y-4 lg:space-y-3"></div>
                        
                        <!-- Balance / COD Row -->
                        <div id="balance-row" class="hidden flex justify-between items-center gap-2 text-[#a16207] text-sm md:text-base lg:text-sm font-semibold pt-1">
                            <dt class="whitespace-nowrap">Balance (Cash on delivery)</dt>
                            <dd id="balance-display" class="text-[#F25996] whitespace-nowrap font-bold">₹0</dd>
                        </div>
                    </dl>
                    
                    <div class="mt-5 sm:mt-6 md:mt-7 lg:mt-6 pt-5 sm:pt-6 md:pt-7 lg:pt-6 border-t border-gray-100">
                        <div class="flex justify-between items-center gap-2 mb-4 sm:mb-6 md:mb-7 lg:mb-6">
                            <span class="text-base sm:text-lg md:text-2xl lg:text-lg font-bold text-[#0f172a] whitespace-nowrap">Total To Pay</span>
                            <span class="text-xl sm:text-2xl md:text-4xl lg:text-2xl font-bold text-[#F25996] whitespace-nowrap" id="total-to-pay-display">₹<?= format_price($subtotal) ?></span>
                        </div>
                        
                        <button type="button" onclick="document.getElementById('payment-form').submit();" class="hidden bg-[#F25996] hover:bg-[#d8407d] text-white font-bold w-full py-3 text-lg rounded-xl shadow-md cursor-pointer">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /* ---- Highlight selected address card ---- */
    function highlightCard(radio) {
        document.querySelectorAll('.address-card').forEach(function(card) {
            card.classList.remove('border-[#F25996]', 'bg-pink-50');
            card.classList.add('border-gray-200');
        });
        radio.closest('label').classList.remove('border-gray-200');
        radio.closest('label').classList.add('border-[#F25996]', 'bg-pink-50');

        // Hide manual entry unless "new" is selected
        if (radio.value !== 'new') {
            toggleManualEntry(false);
            if (typeof recalculateFees === 'function') {
                recalculateFees(radio.dataset.state || '', typeof selectedPaymentMethod !== 'undefined' ? selectedPaymentMethod : 'Online');
            }
        } else {
            const stateInput = document.getElementById('ship_state');
            if (stateInput && typeof recalculateFees === 'function') {
                recalculateFees(stateInput.value || '', typeof selectedPaymentMethod !== 'undefined' ? selectedPaymentMethod : 'Online');
            }
        }
    }

    function toggleManualEntry(show) {
        const manual = document.getElementById('manual-entry');
        if (!manual) return;
        if (show) {
            manual.classList.remove('hidden');
        } else {
            manual.classList.add('hidden');
        }
    }

    /* ---- Step accordion ---- */
    function openStep(step) {
        for (let i of [1, 3]) {
            const content = document.getElementById('content-step-' + i);
            const icon = document.getElementById('icon-step-' + i);
            if (content) {
                content.classList.add('hidden');
                content.classList.remove('block');
            }
            if (icon) {
                icon.classList.remove('rotate-90');
                icon.classList.add('rotate-0');
            }
        }
        
        const targetContent = document.getElementById('content-step-' + step);
        const reviewAddress = document.getElementById('review-address-display');
        
        if (targetContent) {
            targetContent.classList.remove('hidden');
            targetContent.classList.add('block');
        }
        const targetIcon = document.getElementById('icon-step-' + step);
        if (targetIcon) {
            targetIcon.classList.remove('rotate-0');
            targetIcon.classList.add('rotate-90');
        }

        if (step === 1 && reviewAddress) {
            reviewAddress.classList.add('hidden');
        } else if (step !== 1 && reviewAddress && reviewAddress.innerHTML.trim() !== '') {
            reviewAddress.classList.remove('hidden');
        }
    }

    /* ---- Inline Validation Helpers ---- */
    function showInlineError(inputEl, message) {
        if (!inputEl) return;
        inputEl.classList.add('has-error');

        const wrapper = inputEl.closest('.cs-wrapper');
        if (wrapper) {
            const trigger = wrapper.querySelector('.cs-trigger');
            if (trigger) trigger.classList.add('has-error');
        }

        let errEl = document.getElementById(inputEl.id + '-error') ||
                    document.getElementById(inputEl.id + '-error-fallback');
        if (!errEl) {
            const parent = wrapper ? wrapper.parentElement : inputEl.parentElement;
            if (parent) {
                errEl = parent.querySelector('.field-error-msg');
                if (!errEl) {
                    errEl = document.createElement('div');
                    errEl.id = inputEl.id + '-error';
                    errEl.className = 'field-error-msg';
                    errEl.innerHTML = '<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="error-msg"></span>';
                    parent.appendChild(errEl);
                }
            }
        }
        if (errEl) {
            const span = errEl.querySelector('.error-msg') || errEl;
            span.textContent = message;
            errEl.classList.remove('hidden');
        }

        const clearThis = function() {
            clearInlineError(inputEl);
            inputEl.removeEventListener('input', clearThis);
            inputEl.removeEventListener('change', clearThis);
        };
        inputEl.addEventListener('input', clearThis);
        inputEl.addEventListener('change', clearThis);
    }

    function clearInlineError(inputEl) {
        if (!inputEl) return;
        inputEl.classList.remove('has-error');
        const wrapper = inputEl.closest('.cs-wrapper');
        if (wrapper) {
            const trigger = wrapper.querySelector('.cs-trigger');
            if (trigger) trigger.classList.remove('has-error');
        }
        const errEl = document.getElementById(inputEl.id + '-error') || 
                      document.getElementById(inputEl.id + '-error-fallback') ||
                      (wrapper ? wrapper.parentElement.querySelector('.field-error-msg') : (inputEl.parentElement ? inputEl.parentElement.querySelector('.field-error-msg') : null));
        if (errEl) {
            errEl.classList.add('hidden');
            const span = errEl.querySelector('.error-msg');
            if (span) span.textContent = '';
        }
    }

    function clearAllAddressErrors(container) {
        const root = container || document;
        root.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));
        root.querySelectorAll('.field-error-msg').forEach(el => {
            el.classList.add('hidden');
            const span = el.querySelector('.error-msg');
            if (span) span.textContent = '';
        });
    }

    function focusFirstAddressError(firstErrorEl) {
        if (!firstErrorEl) return;
        const wrapper = firstErrorEl.closest('.cs-wrapper');
        const target = wrapper ? wrapper.querySelector('.cs-trigger') : firstErrorEl;
        if (target) {
            if (typeof target.focus === 'function') target.focus();
            if (typeof target.scrollIntoView === 'function') {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    /* ---- Proceed from Step 1 ---- */
    function proceedFromAddress() {
        let fullAddr = '';

        const selectedRadio = document.querySelector('input[name="selected_address"]:checked');

        if (selectedRadio) {
            if (selectedRadio.value === 'new') {
                // Manual entry mode
                const manualContainer = document.getElementById('manual-entry');
                clearAllAddressErrors(manualContainer);
                let firstErrorEl = null;

                const line1El = document.getElementById('ship_line1');
                const line1  = (line1El ? line1El.value : '').trim();
                const line2  = ((document.getElementById('ship_line2')  || {}).value || '').trim();
                const stateEl = document.getElementById('ship_state');
                const state  = (stateEl ? stateEl.value : '').trim();
                
                if (!line1) {
                    showInlineError(line1El, 'Please enter Address Line 1');
                    if (!firstErrorEl) firstErrorEl = line1El;
                }

                if (!state) {
                    showInlineError(stateEl, 'Please select a State');
                    if (!firstErrorEl) firstErrorEl = stateEl;
                }
                
                let city = '';
                if (state === 'Tamil Nadu') {
                    const distSel = document.getElementById('ship_district_select');
                    city = (distSel ? distSel.value : '').trim();
                    if (!city) {
                        showInlineError(distSel, 'Please select your District');
                        if (!firstErrorEl) firstErrorEl = distSel;
                    }
                } else if (state === 'Others') {
                    const otherStateEl = document.getElementById('ship_other_state_name');
                    const otherState = (otherStateEl ? otherStateEl.value : '').trim();
                    const distTextEl = document.getElementById('ship_district_text');
                    const dist = (distTextEl ? distTextEl.value : '').trim();

                    if (!otherState) {
                        showInlineError(otherStateEl, 'Please enter State Name');
                        if (!firstErrorEl) firstErrorEl = otherStateEl;
                    }

                    if (!dist) {
                        showInlineError(distTextEl, 'Please enter District / City');
                        if (!firstErrorEl) firstErrorEl = distTextEl;
                    }

                    const tnRegex = /tamil\s*nadu/i;
                    if (tnRegex.test(dist) || tnRegex.test(otherState)) {
                        const targetEl = tnRegex.test(otherState) ? otherStateEl : distTextEl;
                        showInlineError(targetEl, 'If you are from Tamil Nadu, please select "Tamil Nadu" from the State dropdown.');
                        if (!firstErrorEl) firstErrorEl = targetEl;
                    }

                    city = dist;
                }

                const pinEl = document.getElementById('ship_pincode');
                const pin = (pinEl ? pinEl.value : '').trim();
                const country = ((document.getElementById('ship_country') || {}).value || 'India').trim();

                if (!pin || pin.length !== 6) {
                    showInlineError(pinEl, 'Please enter a valid 6-digit Pincode');
                    if (!firstErrorEl) firstErrorEl = pinEl;
                }

                if (firstErrorEl) {
                    focusFirstAddressError(firstErrorEl);
                    return;
                }

                const stateDisplayName = (state === 'Others' && document.getElementById('ship_other_state_name'))
                    ? (document.getElementById('ship_other_state_name').value.trim() || state)
                    : state;

                const parts = [line1, line2, city + (stateDisplayName ? ', ' + stateDisplayName : ''), pin, country].filter(Boolean);
                fullAddr = parts.join('\n');
                
                const saveCheck = document.getElementById('save_manual_address_check');
                const shouldSave = saveCheck ? (saveCheck.checked ? '1' : '0') : '1';

                document.getElementById('final_is_new_address').value = shouldSave;
                document.getElementById('final_new_addr_line1').value = line1;
                document.getElementById('final_new_addr_line2').value = line2;
                document.getElementById('final_new_addr_city').value = city;
                document.getElementById('final_new_addr_pincode').value = pin;
                document.getElementById('final_new_addr_country').value = country;
            } else {
                // Saved address — build from data attributes (excluding [Shop Address] label)
                const d = selectedRadio.dataset;
                const parts = [
                    d.line1,
                    d.line2,
                    d.city + (d.state && d.state !== 'Unknown' ? ', ' + d.state : ''),
                    d.postal !== '000000' ? d.postal : '',
                    d.country
                ].filter(Boolean);
                fullAddr = parts.join('\n');
                document.getElementById('final_is_new_address').value = '0';
            }
        } else {
            // Fallback: no radio buttons (no saved addresses) — use manual fields
            const formContainer = document.getElementById('shipping-form') || document;
            clearAllAddressErrors(formContainer);
            let firstErrorEl = null;

            const line1El = document.getElementById('ship_line1') || document.getElementById('ship_address');
            const line1 = (line1El ? line1El.value : '').trim();
            const stateEl = document.getElementById('ship_state');
            const state = (stateEl ? stateEl.value : '').trim();
            
            if (!line1) {
                showInlineError(line1El, 'Please enter your Address');
                if (!firstErrorEl) firstErrorEl = line1El;
            }

            if (!state) {
                showInlineError(stateEl, 'Please select a State');
                if (!firstErrorEl) firstErrorEl = stateEl;
            }

            let city = '';
            if (state === 'Tamil Nadu') {
                const distSel = document.getElementById('ship_fallback_district_select');
                city = (distSel ? distSel.value : '').trim();
                if (!city) {
                    showInlineError(distSel, 'Please select your District');
                    if (!firstErrorEl) firstErrorEl = distSel;
                }
            } else if (state === 'Others') {
                const distTextEl = document.getElementById('ship_fallback_district_text');
                city = (distTextEl ? distTextEl.value : '').trim();
                const otherStEl = document.getElementById('ship_fallback_other_state_name');
                const otherSt = (otherStEl ? otherStEl.value : '').trim();
                
                if (!otherSt) {
                    showInlineError(otherStEl, 'Please enter your State Name');
                    if (!firstErrorEl) firstErrorEl = otherStEl;
                }

                if (!city) {
                    showInlineError(distTextEl, 'Please enter District / City');
                    if (!firstErrorEl) firstErrorEl = distTextEl;
                }

                const tnRegex = /tamil\s*nadu/i;
                if (tnRegex.test(city) || tnRegex.test(otherSt)) {
                    const targetEl = tnRegex.test(otherSt) ? otherStEl : distTextEl;
                    showInlineError(targetEl, 'If you are from Tamil Nadu, please select "Tamil Nadu" from the State dropdown.');
                    if (!firstErrorEl) firstErrorEl = targetEl;
                }
            }
            
            const pinEl = document.getElementById('ship_pincode');
            const pin   = (pinEl ? pinEl.value : '').trim();
            const name  = ((document.getElementById('ship_name')  || {}).value || '').trim();

            if (!pin || pin.length !== 6) {
                showInlineError(pinEl, 'Please enter a valid 6-digit Pincode');
                if (!firstErrorEl) firstErrorEl = pinEl;
            }

            if (firstErrorEl) {
                focusFirstAddressError(firstErrorEl);
                return;
            }

            const otherStVal = (document.getElementById('ship_fallback_other_state_name') || {}).value || '';
            const fallbackStateName = state === 'Others' ? (otherStVal.trim() || state) : state;

            fullAddr = [name, line1, city + (fallbackStateName ? ', ' + fallbackStateName : '') + ' - ' + pin].filter(Boolean).join('\n');
            
            const fallbackSave = document.getElementById('save_fallback_address_check');
            const shouldSaveFallback = fallbackSave ? (fallbackSave.checked ? '1' : '0') : '1';
            document.getElementById('final_is_new_address').value = shouldSaveFallback;
            document.getElementById('final_new_addr_line1').value = line1;
            document.getElementById('final_new_addr_line2').value = '';
            document.getElementById('final_new_addr_city').value = city;
            document.getElementById('final_new_addr_pincode').value = pin;
            document.getElementById('final_new_addr_country').value = 'India';
        }

        // Populate review section
        const reviewEl = document.getElementById('review-address-display');
        if (reviewEl) {
            let p1 = '', p2 = '', p3 = 'India';
            
            if (selectedRadio && selectedRadio.value !== 'new') {
                const d = selectedRadio.dataset;
                p1 = [d.label, d.line1].filter(Boolean).join(', ');
                p2 = [d.city, (d.state && d.state !== 'Unknown' ? d.state : ''), d.postal !== '000000' ? d.postal : ''].filter(Boolean).join(' - ').replace(/^[ \-]+|[ \-]+$/g, '').replace(/ -  - /g, ' - ');
                p3 = d.country || 'India';
            } else {
                const l1 = (document.getElementById('ship_line1') || document.getElementById('ship_address') || {}).value || '';
                const nm = (document.getElementById('ship_name') || {}).value || '';
                const st = (document.getElementById('ship_state') || {}).value || '';
                let ct = '';
                let displayState = st;
                if (st === 'Tamil Nadu') {
                    ct = (document.getElementById('ship_district_select') || document.getElementById('ship_fallback_district_select') || {}).value || '';
                } else if (st === 'Others') {
                    ct = (document.getElementById('ship_district_text') || document.getElementById('ship_fallback_district_text') || {}).value || '';
                    const otherSt = (document.getElementById('ship_other_state_name') || document.getElementById('ship_fallback_other_state_name') || {}).value || '';
                    displayState = otherSt || '';
                }
                const pn = (document.getElementById('ship_pincode') || {}).value || '';
                
                p1 = [nm, l1].filter(Boolean).join(', ');
                p2 = [ct, displayState, pn].filter(Boolean).join(' - ').replace(/^[ \-]+|[ \-]+$/g, '').replace(/ -  - /g, ' - ');
            }

            reviewEl.innerHTML = `
                <div class="mt-2 p-3 bg-white border border-gray-200 rounded-lg shadow-sm w-full max-w-md">
                    <p class="text-xs font-bold tracking-wider text-[#F25996] uppercase mb-1">DELIVERY TO</p>
                    ${p1 ? `<p class="text-sm text-gray-800 font-medium">${p1}</p>` : ''}
                    ${p2 ? `<p class="text-sm text-gray-500 mt-0.5">${p2}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-1">${p3}</p>
                </div>
            `;
        }
        document.getElementById('final_shipping_address').value = fullAddr;

        // Resolve and update shipping state
        let state = '';
        if (selectedRadio) {
            if (selectedRadio.value === 'new') {
                const stVal = (document.getElementById('ship_state') || {}).value || '';
                if (stVal === 'Others') {
                    state = ((document.getElementById('ship_other_state_name') || {}).value || '').trim() || 'Others';
                } else {
                    state = stVal;
                }
            } else {
                state = selectedRadio.dataset.state || '';
            }
        } else {
            const stVal = (document.getElementById('ship_state') || {}).value || '';
            if (stVal === 'Others') {
                state = ((document.getElementById('ship_other_state_name') || document.getElementById('ship_fallback_other_state_name') || {}).value || '').trim() || 'Others';
            } else {
                state = stVal;
            }
        }
        const stateEl = document.getElementById('final_shipping_state');
        if (stateEl) {
            stateEl.value = state;
        }

        // Keep badges bg-[#F25996] with text-white
        const b1 = document.getElementById('badge-step-1');
        const b3 = document.getElementById('badge-step-3');
        if (b1) { b1.className = 'w-6 h-6 rounded-full bg-[#F25996] text-white text-xs flex items-center justify-center mr-3 font-bold'; }
        if (b3) { b3.className = 'w-6 h-6 rounded-full bg-[#F25996] text-white text-xs flex items-center justify-center mr-3 font-bold'; }

        openStep(3); // Proceed to Payment (which is step 3 internally)
    }

    function nextStep(step) {
        const b3 = document.getElementById('badge-step-3');
        if (b3) { b3.className = 'w-6 h-6 rounded-full bg-[#F25996] text-white text-xs flex items-center justify-center mr-3 font-bold'; }
        openStep(step);
    }

    const payForm = document.getElementById('payment-form');
    if (payForm) {
        payForm.addEventListener('submit', function () {
            const btn = document.getElementById('pay-btn');
            const spinner = document.getElementById('pay-spinner');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            if (spinner) spinner.classList.remove('hidden');
        });
    }

    // ============================================================
    //  orderPricing — Fee calculation engine
    // ============================================================
    const BASE_URL_JS = '<?= BASE_URL ?>';
    let currentFeeData        = null;
    let selectedState         = '';
    let selectedPaymentMethod = 'Online';

    function formatPriceJs(val) {
        if (val === null || val === undefined || isNaN(val)) return '0';
        const num = Number(val);
        if (Math.floor(num) === num) {
            return num.toLocaleString('en-IN');
        }
        return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    /**
     * Normalize state: strip spaces/dashes, lowercase.
     * Matches PHP: strtolower(str_replace([' ','-','_'], '', trim($state)))
     * So "tamilnadu" == "Tamil Nadu" == "TAMIL NADU" on both sides.
     */
    function normalizeState(raw) {
        if (!raw) return '';
        return raw.trim().toLowerCase().replace(/[\s\-_]/g, '');
    }

    /** Read the currently selected state from the address form. */
    function getSelectedState() {
        const radio = document.querySelector('input[name="selected_address"]:checked');
        if (radio && radio.value !== 'new') {
            return radio.dataset.state || '';
        }
        const stateInput = document.getElementById('ship_state');
        const val = stateInput ? stateInput.value : '';
        if (val === 'Others') {
            const otherInput = document.getElementById('ship_other_state_name') || document.getElementById('ship_fallback_other_state_name');
            return (otherInput && otherInput.value.trim()) ? otherInput.value.trim() : 'Others';
        }
        return val;
    }

    function recalculateFees(state, paymentMethod) {
        selectedState         = state || '';
        selectedPaymentMethod = paymentMethod || selectedPaymentMethod;

        const loadingEl = document.getElementById('fees-loading');
        const dynEl     = document.getElementById('dynamic-fees');
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (dynEl)     dynEl.innerHTML = '';

        fetch(BASE_URL_JS + '/api/calculate-checkout-fees', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'state='          + encodeURIComponent(normalizeState(selectedState))
                + '&payment_method=' + encodeURIComponent(selectedPaymentMethod)
        })
        .then(r => r.json())
        .then(data => {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (data.error) { console.warn('Fee API error:', data.error); return; }
            currentFeeData = data;
            renderPricingSummary(data);
        })
        .catch(err => {
            if (loadingEl) loadingEl.classList.add('hidden');
            console.error('Fee API fetch failed:', err);
        });
    }

    function renderPricingSummary(data) {
        const dynEl = document.getElementById('dynamic-fees');
        const roEl  = document.getElementById('round-off-display');
        const gtEl  = document.getElementById('grand-total-display');
        const ttpEl = document.getElementById('total-to-pay-display');
        const payBtn = document.getElementById('pay-btn-label');

        const feeLabels = {
            shipping_fee:  'Shipping Fee',
            weight_fee:    'Weight Fee',
            platform_fee:  'Platform Fee',
            packaging_fee: 'Packing Fee'
        };

        // Build fee rows from fee_breakdown if present (has note), fallback to fees object
        let rows = '';
        let hasFees = false;

        if (data.fee_breakdown && data.fee_breakdown.length > 0) {
            data.fee_breakdown.forEach(function(item) {
                hasFees = true;
                const noteHtml = ''; // Removed scaling note display
                rows += `<div class="flex justify-between items-center gap-2">
                    <dt class="whitespace-nowrap">${item.name}${noteHtml}</dt>
                    <dd class="font-medium text-gray-900 whitespace-nowrap">₹${formatPriceJs(item.amount)}</dd>
                </div>`;
            });
        } else if (data.fees) {
            for (const [key, label] of Object.entries(feeLabels)) {
                const val = parseFloat(data.fees[key] || 0);
                if (val > 0) {
                    hasFees = true;
                    rows += `<div class="flex justify-between items-center gap-2">
                        <dt class="whitespace-nowrap">${label}</dt>
                        <dd class="font-medium text-gray-900 whitespace-nowrap">₹${formatPriceJs(val)}</dd>
                    </div>`;
                }
            }
        }

        if (!hasFees) {
            rows = `<div class="text-xs text-gray-400 py-1">No applicable fees for this state/payment.</div>`;
        }
        if (dynEl) dynEl.innerHTML = rows;

        // Update Round Off row if present in DOM
        const roundOff = parseFloat(data.round_off !== undefined ? data.round_off : 0);
        if (roEl) {
            if (roundOff > 0) {
                roEl.textContent = '+ ₹' + formatPriceJs(roundOff);
            } else if (roundOff < 0) {
                roEl.textContent = '- ₹' + formatPriceJs(Math.abs(roundOff));
            } else {
                roEl.textContent = '₹0';
            }
        }

        const gt = parseFloat(data.grand_total || data.subtotal || 0);
        if (gtEl)  gtEl.textContent  = '₹' + formatPriceJs(gt);
        if (ttpEl) ttpEl.textContent = '₹' + formatPriceJs(gt);

        // Update pay button (Online only)
        if (payBtn) payBtn.textContent = 'Pay ₹' + formatPriceJs(gt);

        // Sync hidden fields
        const fgEl  = document.getElementById('final_grand_total');
        const fbaEl = document.getElementById('final_balance_amount');
        const fpmEl = document.getElementById('final_payment_method');
        if (fgEl)  fgEl.value  = gt;
        if (fbaEl) fbaEl.value = gt;
        if (fpmEl) fpmEl.value = 'Online';
    }

    function onPaymentMethodChange(method) {
        selectedPaymentMethod = method;

        document.querySelectorAll('.payment-card').forEach(c => {
            c.classList.remove('border-brand-500', 'bg-brand-50', 'border-amber-400', 'bg-amber-50');
            c.classList.add('border-gray-200');
        });
        const targetCard = method === 'COD'
            ? document.getElementById('cod-label')
            : document.querySelector('label[for="pm_online"]');
        if (targetCard) {
            targetCard.classList.remove('border-gray-200');
            targetCard.classList.add(
                method === 'COD' ? 'border-amber-400' : 'border-[#F25996]',
                method === 'COD' ? 'bg-amber-50'      : 'bg-[#F25996]'
            );
        }

        // Always recalculate — even if state hasn't changed yet
        const state = selectedState || getSelectedState();
        recalculateFees(state, method);
    }

    // ---- Patch proceedFromAddress to also kick off fee calculation ----
    const _origProceedFromAddress = window.proceedFromAddress;
    window.proceedFromAddress = function() {
        // Extract state BEFORE calling the original (which may open step 2)
        const rawState = getSelectedState();

        // Call original handler
        if (typeof _origProceedFromAddress === 'function') {
            _origProceedFromAddress();
        }

        // Trigger fee calculation with the resolved state
        if (rawState) {
            recalculateFees(rawState, selectedPaymentMethod);
        }
    };

    // ---- Auto-calculate on page load if a default address is already selected ----
    (function() {
        const defaultRadio = document.querySelector('input[name="selected_address"]:checked');
        if (defaultRadio && defaultRadio.value !== 'new') {
            const rawState = defaultRadio.dataset.state || '';
            if (rawState) {
                // Slight delay to let page render first
                setTimeout(() => recalculateFees(rawState, selectedPaymentMethod), 300);
            }
        }
    })();
    function saveManualAddressAjax() {
        const isFallback = !document.getElementById('manual-entry') || document.getElementById('manual-entry').classList.contains('hidden');
        const container = isFallback ? (document.querySelector('#shipping-form') || document) : document.getElementById('manual-entry');
        clearAllAddressErrors(container);

        let firstErrorEl = null;

        const line1El = document.getElementById('ship_line1') || document.getElementById('ship_address');
        const line1 = (line1El ? line1El.value : '').trim();
        const line2 = ((document.getElementById('ship_line2') || {}).value || '').trim();
        const stateEl = document.getElementById('ship_state');
        const state = (stateEl ? stateEl.value : '').trim();
        const pinEl = document.getElementById('ship_pincode');
        const pin   = (pinEl ? pinEl.value : '').trim();
        const country = ((document.getElementById('ship_country') || {}).value || 'India').trim();

        if (!line1) {
            showInlineError(line1El, 'Please enter Address Line 1 / Complete Address');
            if (!firstErrorEl) firstErrorEl = line1El;
        }

        if (!state) {
            showInlineError(stateEl, 'Please select a State');
            if (!firstErrorEl) firstErrorEl = stateEl;
        }

        let city = '';
        let otherStateName = '';
        if (state === 'Tamil Nadu') {
            const distSel = document.getElementById('ship_district_select') || document.getElementById('ship_fallback_district_select');
            city = (distSel ? distSel.value : '').trim();
            if (!city) {
                showInlineError(distSel, 'Please select your District');
                if (!firstErrorEl) firstErrorEl = distSel;
            }
        } else if (state === 'Others') {
            const distTextEl = document.getElementById('ship_district_text') || document.getElementById('ship_fallback_district_text');
            city = (distTextEl ? distTextEl.value : '').trim();
            const otherStEl = document.getElementById('ship_other_state_name') || document.getElementById('ship_fallback_other_state_name');
            otherStateName = (otherStEl ? otherStEl.value : '').trim();
            
            if (!otherStateName) {
                showInlineError(otherStEl, 'Please enter your State Name');
                if (!firstErrorEl) firstErrorEl = otherStEl;
            }

            if (!city) {
                showInlineError(distTextEl, 'Please enter District / City');
                if (!firstErrorEl) firstErrorEl = distTextEl;
            }

            const tnRegex = /tamil\s*nadu/i;
            if (tnRegex.test(city) || tnRegex.test(otherStateName)) {
                const targetEl = tnRegex.test(otherStateName) ? otherStEl : distTextEl;
                showInlineError(targetEl, 'If you are from Tamil Nadu, please select "Tamil Nadu" from the State dropdown.');
                if (!firstErrorEl) firstErrorEl = targetEl;
            }
        }

        if (!pin || pin.length !== 6) {
            showInlineError(pinEl, 'Please enter a valid 6-digit Pincode');
            if (!firstErrorEl) firstErrorEl = pinEl;
        }

        if (firstErrorEl) {
            focusFirstAddressError(firstErrorEl);
            return;
        }

        const saveBtn = document.getElementById('save-manual-addr-btn') || document.getElementById('save-fallback-addr-btn');
        const origText = saveBtn ? saveBtn.innerHTML : '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-1" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
        }

        const formData = new FormData();
        formData.append('label', 'Saved Address');
        formData.append('line1', line1);
        formData.append('line2', line2);
        formData.append('state', state);
        if (otherStateName) {
            formData.append('other_state_name', otherStateName);
        }
        formData.append('city', city);
        formData.append('postal_code', pin);
        formData.append('country', country);
        formData.append('is_default', '1');

        const baseUrlEndpoint = typeof BASE_URL_JS !== 'undefined' ? BASE_URL_JS : '<?= BASE_URL ?>';

        fetch(baseUrlEndpoint + '/checkout/address/add', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Address Saved',
                        text: 'Your address has been saved to your account.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert('Your address has been saved.');
                    window.location.reload();
                }
            } else {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origText;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'Failed to save address' });
                } else {
                    alert(data.error || 'Failed to save address');
                }
            }
        })
        .catch(err => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = origText;
            }
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while saving the address.' });
            } else {
                alert('An error occurred while saving the address.');
            }
        });
    }

    function toggleShipDistrictFields() {
        const stateEl = document.getElementById('ship_state');
        const state = stateEl ? stateEl.value : '';
        const tnContainer = document.getElementById('ship_tn_district_container') || document.getElementById('ship_fallback_tn_district_container');
        const select = document.getElementById('ship_district_select') || document.getElementById('ship_fallback_district_select');
        const otherStateContainer = document.getElementById('ship_other_state_container') || document.getElementById('ship_fallback_other_state_container');
        const otherStateInput = document.getElementById('ship_other_state_name') || document.getElementById('ship_fallback_other_state_name');
        const otherDistContainer = document.getElementById('ship_other_district_container') || document.getElementById('ship_fallback_other_district_container');
        const distText = document.getElementById('ship_district_text') || document.getElementById('ship_fallback_district_text');

        // Clear existing errors on district/state inputs when state changes
        if (stateEl) clearInlineError(stateEl);
        if (select) clearInlineError(select);
        if (otherStateInput) clearInlineError(otherStateInput);
        if (distText) clearInlineError(distText);

        if (state === 'Tamil Nadu') {
            if (tnContainer) tnContainer.classList.remove('hidden');
            if (select) { select.classList.remove('hidden'); select.disabled = false; select.required = true; }
            if (otherStateContainer) otherStateContainer.classList.add('hidden');
            if (otherStateInput) { otherStateInput.disabled = true; otherStateInput.required = false; }
            if (otherDistContainer) otherDistContainer.classList.add('hidden');
            if (distText) { distText.disabled = true; distText.required = false; }
        } else if (state === 'Others') {
            if (tnContainer) tnContainer.classList.add('hidden');
            if (select) { select.classList.add('hidden'); select.disabled = true; select.required = false; select.value = ''; }
            if (otherStateContainer) otherStateContainer.classList.remove('hidden');
            if (otherStateInput) { otherStateInput.disabled = false; otherStateInput.required = true; }
            if (otherDistContainer) otherDistContainer.classList.remove('hidden');
            if (distText) { distText.disabled = false; distText.required = true; distText.placeholder = 'Enter District / City Name'; }
        } else {
            if (tnContainer) tnContainer.classList.remove('hidden');
            if (select) { select.classList.remove('hidden'); select.disabled = true; select.required = false; select.value = ''; }
            if (otherStateContainer) otherStateContainer.classList.add('hidden');
            if (otherStateInput) { otherStateInput.disabled = true; otherStateInput.required = false; }
            if (otherDistContainer) otherDistContainer.classList.add('hidden');
            if (distText) { distText.disabled = true; distText.required = false; }
        }

        if (typeof recalculateFees === 'function') {
            const feeState = (state === 'Others' && otherStateInput && otherStateInput.value.trim()) ? otherStateInput.value.trim() : state;
            recalculateFees(feeState, typeof selectedPaymentMethod !== 'undefined' ? selectedPaymentMethod : 'Online');
        }
    }

    function toggleEditShipDistrictFields() {
        const stateEl = document.getElementById('edit_state');
        const state = stateEl ? stateEl.value : '';
        const tnContainer = document.getElementById('edit_tn_district_container');
        const select = document.getElementById('edit_district_select');
        const otherStateContainer = document.getElementById('edit_other_state_container');
        const otherStateInput = document.getElementById('edit_other_state_name');
        const otherDistContainer = document.getElementById('edit_other_district_container');
        const distText = document.getElementById('edit_district_text');

        if (state === 'Tamil Nadu') {
            if (tnContainer) tnContainer.classList.remove('hidden');
            if (select) { select.classList.remove('hidden'); select.disabled = false; select.required = true; }
            if (otherStateContainer) otherStateContainer.classList.add('hidden');
            if (otherStateInput) { otherStateInput.disabled = true; otherStateInput.required = false; }
            if (otherDistContainer) otherDistContainer.classList.add('hidden');
            if (distText) { distText.disabled = true; distText.required = false; }
        } else if (state === 'Others') {
            if (tnContainer) tnContainer.classList.add('hidden');
            if (select) { select.classList.add('hidden'); select.disabled = true; select.required = false; select.value = ''; }
            if (otherStateContainer) otherStateContainer.classList.remove('hidden');
            if (otherStateInput) { otherStateInput.disabled = false; otherStateInput.required = true; }
            if (otherDistContainer) otherDistContainer.classList.remove('hidden');
            if (distText) { distText.disabled = false; distText.required = true; distText.placeholder = 'Enter District / City Name'; }
        } else {
            if (tnContainer) tnContainer.classList.remove('hidden');
            if (select) { select.classList.remove('hidden'); select.disabled = true; select.required = false; }
            if (otherStateContainer) otherStateContainer.classList.add('hidden');
            if (otherStateInput) { otherStateInput.disabled = true; otherStateInput.required = false; }
            if (otherDistContainer) otherDistContainer.classList.add('hidden');
            if (distText) { distText.disabled = true; distText.required = false; }
        }
    }

    function openAddAddressModal() {
        document.getElementById('edit_address_id').value = '';
        document.getElementById('modal_address_title').innerText = 'Add New Delivery Address';
        document.getElementById('edit_label').value = 'Shop Address';
        document.getElementById('edit_line1').value = '';
        document.getElementById('edit_line2').value = '';
        document.getElementById('edit_pincode').value = '';
        document.getElementById('edit_is_default').checked = false;
        
        const stateSelect = document.getElementById('edit_state');
        stateSelect.value = 'Tamil Nadu';
        stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
        
        toggleEditShipDistrictFields();

        const districtSel = document.getElementById('edit_district_select');
        if (districtSel) {
            districtSel.value = '';
            districtSel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const distText = document.getElementById('edit_district_text');
        if (distText) distText.value = '';
        const otherStateInput = document.getElementById('edit_other_state_name');
        if (otherStateInput) otherStateInput.value = '';

        document.getElementById('editAddressModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openEditAddressModal(addr) {
        if (!addr) return;
        document.getElementById('edit_address_id').value = addr.id;
        document.getElementById('modal_address_title').innerText = 'Edit Delivery Address';
        document.getElementById('edit_label').value = addr.label || 'Saved Address';
        document.getElementById('edit_line1').value = addr.line1 || '';
        document.getElementById('edit_line2').value = addr.line2 || '';
        document.getElementById('edit_pincode').value = addr.postal_code || '';
        document.getElementById('edit_is_default').checked = (addr.is_default == 1 || addr.is_default === true || addr.is_default === '1');
        
        const rawCity = (addr.city || '').trim();
        const rawState = (addr.state || '').trim();
        const stateLower = rawState.toLowerCase().replace(/[\s\-_]/g, '');
        const isTN = (stateLower === 'tamilnadu' || stateLower === 'tn');
        
        const stateSelect = document.getElementById('edit_state');
        stateSelect.value = isTN ? 'Tamil Nadu' : 'Others';
        stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
        
        toggleEditShipDistrictFields();

        const districtSel = document.getElementById('edit_district_select');
        const distText = document.getElementById('edit_district_text');
        const otherStateInput = document.getElementById('edit_other_state_name');

        if (isTN) {
            if (districtSel) {
                const opt = Array.from(districtSel.options).find(o => o.value.toLowerCase() === rawCity.toLowerCase());
                if (opt) {
                    districtSel.value = opt.value;
                } else {
                    districtSel.value = rawCity;
                }
                districtSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (distText) distText.value = '';
            if (otherStateInput) otherStateInput.value = '';
        } else {
            if (districtSel) {
                districtSel.value = "";
                districtSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (distText) distText.value = rawCity;
            if (otherStateInput) otherStateInput.value = (rawState.toLowerCase() !== 'others' ? rawState : '');
        }

        document.getElementById('editAddressModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditAddressModal() {
        document.getElementById('editAddressModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const editForm = document.getElementById('editAddressForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();

                clearAllAddressErrors(editForm);
                let firstErrorEl = null;

                const line1El = document.getElementById('edit_line1');
                const line1 = (line1El ? line1El.value : '').trim();
                const stateEl = document.getElementById('edit_state');
                const state = (stateEl ? stateEl.value : '').trim();
                const pinEl = document.getElementById('edit_pincode');
                const pin = (pinEl ? pinEl.value : '').trim();

                if (!line1) {
                    showInlineError(line1El, 'Please enter Address Line 1');
                    if (!firstErrorEl) firstErrorEl = line1El;
                }
                if (!state) {
                    showInlineError(stateEl, 'Please select a State');
                    if (!firstErrorEl) firstErrorEl = stateEl;
                }

                let city = '';
                let otherState = '';
                if (state === 'Tamil Nadu') {
                    const distSel = document.getElementById('edit_district_select');
                    city = (distSel ? distSel.value : '').trim();
                    if (!city) {
                        showInlineError(distSel, 'Please select your District');
                        if (!firstErrorEl) firstErrorEl = distSel;
                    }
                } else if (state === 'Others') {
                    const distTextEl = document.getElementById('edit_district_text');
                    city = (distTextEl ? distTextEl.value : '').trim();
                    const otherStEl = document.getElementById('edit_other_state_name');
                    otherState = (otherStEl ? otherStEl.value : '').trim();
                    if (!city) {
                        showInlineError(distTextEl, 'Please enter District / City');
                        if (!firstErrorEl) firstErrorEl = distTextEl;
                    }
                    if (!otherState) {
                        showInlineError(otherStEl, 'Please enter State Name');
                        if (!firstErrorEl) firstErrorEl = otherStEl;
                    }
                    if (/tamil\s*nadu/i.test(city) || /tamil\s*nadu/i.test(otherState)) {
                        const targetEl = /tamil\s*nadu/i.test(otherState) ? otherStEl : distTextEl;
                        showInlineError(targetEl, 'If you are from Tamil Nadu, please select "Tamil Nadu" from the State dropdown.');
                        if (!firstErrorEl) firstErrorEl = targetEl;
                    }
                }

                if (!pin || pin.length !== 6) {
                    showInlineError(pinEl, 'Please enter a valid 6-digit Pincode');
                    if (!firstErrorEl) firstErrorEl = pinEl;
                }

                if (firstErrorEl) {
                    focusFirstAddressError(firstErrorEl);
                    return;
                }

                const formData = new FormData(this);
                formData.set('city', city);
                if (state === 'Others' && otherState) {
                    formData.set('other_state_name', otherState);
                }

                const addrId = formData.get('address_id');
                const baseUrlEndpoint = typeof BASE_URL_JS !== 'undefined' ? BASE_URL_JS : '<?= BASE_URL ?>';
                const endpoint = addrId ? (baseUrlEndpoint + '/checkout/address/edit') : (baseUrlEndpoint + '/checkout/address/add');

                const saveBtn = editForm.querySelector('button[type="submit"]');
                const origText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-1" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';

                fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        saveBtn.innerHTML = 'Saved!';
                        window.location.reload();
                    } else {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = origText;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'Failed to save address' });
                        } else {
                            alert(data.error || 'Failed to save address');
                        }
                    }
                })
                .catch(err => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origText;
                    console.error(err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while saving address.' });
                    } else {
                        alert('An error occurred while saving address.');
                    }
                });
            });
        }

        const manualInputs = [
            'ship_line1', 'ship_line2', 'ship_state', 'ship_district_select',
            'ship_district_text', 'ship_city_text', 'ship_pincode', 'ship_country'
        ];
        
        manualInputs.forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener('input', updateNewAddressPreview);
                el.addEventListener('change', updateNewAddressPreview);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditAddressModal();
            }
        });
    });

    function updateNewAddressPreview() {
        const titleEl = document.getElementById('addr_new_title');
        const descEl = document.getElementById('addr_new_desc');
        
        if (!titleEl || !descEl) return;
        
        const line1 = (document.getElementById('ship_line1') || {}).value || '';
        const line2 = (document.getElementById('ship_line2') || {}).value || '';
        const state = (document.getElementById('ship_state') || {}).value || '';
        let city = '';
        
        if (state === 'Tamil Nadu') {
            city = (document.getElementById('ship_district_select') || {}).value || '';
        } else if (state === 'Others') {
            const dist = (document.getElementById('ship_district_text') || {}).value || '';
            const cty = (document.getElementById('ship_city_text') || {}).value || '';
            city = [dist, cty].filter(Boolean).join(', ');
        }
        
        const pin = (document.getElementById('ship_pincode') || {}).value || '';
        const country = (document.getElementById('ship_country') || {}).value || 'India';
        
        if (line1 || line2 || city || state || pin) {
            titleEl.innerHTML = '<span class="text-xs font-bold tracking-wider text-[#F25996] uppercase">NEW ADDRESS</span>';
            
            const p1 = [line1, line2].filter(Boolean).join(', ');
            const p2 = [city, (state === 'Others' ? '' : state), pin].filter(Boolean).join(' - ').replace(/^[ \-]+|[ \-]+$/g, '').replace(/ -  - /g, ' - ');
            
            descEl.innerHTML = `
                <div class="mt-1">
                    ${p1 ? `<p class="text-sm text-gray-800 font-medium">${p1}</p>` : ''}
                    ${p2 ? `<p class="text-sm text-gray-500 mt-0.5">${p2}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-1">${country}</p>
                </div>
            `;
        } else {
            titleEl.innerText = '+ Enter a different address';
            descEl.innerText = 'Type a new delivery address below';
        }
    }
</script>

<!-- Manage Address Modal (Add & Edit) -->
<div id="editAddressModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" onclick="if(event.target === this) closeEditAddressModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto flex flex-col max-h-[85vh] sm:max-h-[90vh] my-auto overflow-hidden animate-fadeIn" onclick="event.stopPropagation()">
        <!-- Pinned Header -->
        <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 flex justify-between items-center shrink-0 bg-white">
            <h3 id="modal_address_title" class="text-base sm:text-lg font-bold text-gray-900">Manage Address</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" onclick="closeEditAddressModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="editAddressForm" novalidate class="flex flex-col flex-1 min-h-0 overflow-hidden">
            <input type="hidden" id="edit_address_id" name="address_id">
            
            <!-- Scrollable Form Body -->
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 modal-scroll overscroll-contain">
                <div>
                    <label class="form-label">Address Label <span class="text-gray-400 font-normal">(e.g. Shop, Warehouse, Home)</span></label>
                    <input type="text" id="edit_label" name="label" class="form-input" placeholder="Shop Address">
                </div>
                <div>
                    <label class="form-label">Address Line 1 <span class="text-[#F25996]">*</span></label>
                    <input type="text" id="edit_line1" name="line1" required class="form-input" placeholder="House/Flat no., Building, Street">
                </div>
                <div>
                    <label class="form-label">Address Line 2 <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" id="edit_line2" name="line2" class="form-input" placeholder="Area, Locality, Landmark">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">State <span class="text-[#F25996]">*</span></label>
                        <select id="edit_state" name="state" required onchange="toggleEditShipDistrictFields()" class="form-input">
                            <option value="">Select State</option>
                            <option value="Tamil Nadu">Tamil Nadu</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <!-- When TN: District Select Dropdown beside State -->
                    <div id="edit_tn_district_container">
                        <label class="form-label" id="edit_district_label">District/City <span class="text-[#F25996]">*</span></label>
                        <select id="edit_district_select" name="city" class="form-input">
                            <option value="">Select District</option>
                            <option value="Ariyalur">Ariyalur</option>
                            <option value="Chengalpattu">Chengalpattu</option>
                            <option value="Chennai">Chennai</option>
                            <option value="Coimbatore">Coimbatore</option>
                            <option value="Cuddalore">Cuddalore</option>
                            <option value="Dharmapuri">Dharmapuri</option>
                            <option value="Dindigul">Dindigul</option>
                            <option value="Erode">Erode</option>
                            <option value="Kallakurichi">Kallakurichi</option>
                            <option value="Kanchipuram">Kanchipuram</option>
                            <option value="Kanyakumari">Kanyakumari</option>
                            <option value="Karur">Karur</option>
                            <option value="Krishnagiri">Krishnagiri</option>
                            <option value="Madurai">Madurai</option>
                            <option value="Mayiladuthurai">Mayiladuthurai</option>
                            <option value="Nagapattinam">Nagapattinam</option>
                            <option value="Namakkal">Namakkal</option>
                            <option value="Nilgiris">Nilgiris</option>
                            <option value="Perambalur">Perambalur</option>
                            <option value="Pudukkottai">Pudukkottai</option>
                            <option value="Ramanathapuram">Ramanathapuram</option>
                            <option value="Ranipet">Ranipet</option>
                            <option value="Salem">Salem</option>
                            <option value="Sivaganga">Sivaganga</option>
                            <option value="Tenkasi">Tenkasi</option>
                            <option value="Thanjavur">Thanjavur</option>
                            <option value="Theni">Theni</option>
                            <option value="Thoothukudi">Thoothukudi</option>
                            <option value="Tiruchirappalli">Tiruchirappalli (Trichy)</option>
                            <option value="Tirunelveli">Tirunelveli</option>
                            <option value="Tirupathur">Tirupathur</option>
                            <option value="Tiruppur">Tiruppur</option>
                            <option value="Tiruvallur">Tiruvallur</option>
                            <option value="Tiruvannamalai">Tiruvannamalai</option>
                            <option value="Tiruvarur">Tiruvarur</option>
                            <option value="Vellore">Vellore</option>
                            <option value="Viluppuram">Viluppuram</option>
                            <option value="Virudhunagar">Virudhunagar</option>
                        </select>
                    </div>

                    <!-- When Others: State Name Input beside State (UP) -->
                    <div id="edit_other_state_container" class="hidden">
                        <label class="form-label">State Name <span class="text-[#F25996]">*</span></label>
                        <input id="edit_other_state_name" name="other_state_name" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter state name (e.g. Kerala, Karnataka)" autocomplete="off" class="form-input w-full" disabled>
                    </div>

                    <!-- When Others: District/City Input below State & State Name (DOWN) -->
                    <div id="edit_other_district_container" class="hidden sm:col-span-2">
                        <label class="form-label">District/City <span class="text-[#F25996]">*</span></label>
                        <input id="edit_district_text" name="city" type="text" pattern="[A-Za-z\s]+" oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" placeholder="Enter District / City Name" autocomplete="off" class="form-input w-full" disabled>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Pincode <span class="text-[#F25996]">*</span></label>
                        <input type="text" id="edit_pincode" name="postal_code" required pattern="[0-9]{6}" maxlength="6" oninput="this.value=this.value.replace(/\D/g,'')" class="form-input" placeholder="6-digit pincode">
                    </div>
                    <div class="flex items-center pt-2 sm:pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="edit_is_default" name="is_default" value="1" class="w-4 h-4 rounded text-[#F25996] accent-[#F25996] focus:ring-0">
                            <span class="text-xs sm:text-sm font-semibold text-gray-700">Set as default address</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Pinned Footer -->
            <div class="px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                <button type="button" onclick="closeEditAddressModal()" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-[#F25996] hover:bg-[#d8407d] rounded-xl transition-all shadow-md shadow-[#F25996]/20 cursor-pointer">Save Address</button>
            </div>
        </form>
    </div>
</div>

<script>
/* ─────────────────────────────────────────
   Custom Pink Select — replaces all select.form-input
   so the dropdown option colours are fully controllable
───────────────────────────────────────── */
function initCustomSelects() {
    document.querySelectorAll('select.form-input:not(.cs-hidden)').forEach(function(sel) {
        if (sel.dataset.csInit) return; // already converted
        sel.dataset.csInit = '1';

        // Hide native select but keep it in DOM for form submission
        sel.style.display = 'none';
        sel.classList.add('cs-hidden');

        // Build wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'cs-wrapper' + (sel.classList.contains('hidden') ? ' hidden' : '');
        sel.parentNode.insertBefore(wrapper, sel);
        wrapper.appendChild(sel);

        // Build trigger button
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'cs-trigger placeholder form-input';
        trigger.style.padding = '0.625rem 0.875rem';

        var label = document.createElement('span');
        label.className = 'cs-label';
        label.textContent = sel.options[0] ? sel.options[0].text : 'Select';
        trigger.appendChild(label);

        var arrow = document.createElementNS('http://www.w3.org/2000/svg','svg');
        arrow.setAttribute('class','cs-arrow');
        arrow.setAttribute('viewBox','0 0 20 20');
        arrow.setAttribute('fill','none');
        arrow.setAttribute('stroke','currentColor');
        arrow.setAttribute('stroke-width','2');
        var path = document.createElementNS('http://www.w3.org/2000/svg','path');
        path.setAttribute('stroke-linecap','round');
        path.setAttribute('stroke-linejoin','round');
        path.setAttribute('d','M5 7l5 5 5-5');
        arrow.appendChild(path);
        trigger.appendChild(arrow);
        wrapper.appendChild(trigger);

        // Build option list
        var list = document.createElement('div');
        list.className = 'cs-list';
        wrapper.appendChild(list);

        function buildOptions() {
            list.innerHTML = '';
            Array.from(sel.options).forEach(function(opt, i) {
                var item = document.createElement('div');
                item.className = 'cs-option' + (opt.value === '' ? ' disabled' : '') + (opt.selected ? ' selected' : '');
                item.textContent = opt.text;
                item.dataset.value = opt.value;
                if (opt.value !== '') {
                    item.addEventListener('click', function() {
                        sel.value = opt.value;
                        label.textContent = opt.text;
                        trigger.classList.remove('placeholder');
                        list.querySelectorAll('.cs-option').forEach(o => o.classList.remove('selected'));
                        item.classList.add('selected');
                        closeList();
                        // fire native change event so onchange handlers fire
                        sel.dispatchEvent(new Event('change', {bubbles:true}));
                    });
                }
                list.appendChild(item);
            });
        }

        // Set initial value if select already has one
        function syncTriggerToSelect() {
            var cur = sel.options[sel.selectedIndex];
            if (cur && cur.value !== '') {
                label.textContent = cur.text;
                trigger.classList.remove('placeholder');
            } else {
                label.textContent = sel.options[0] ? sel.options[0].text : 'Select';
                trigger.classList.add('placeholder');
            }
            list.querySelectorAll('.cs-option').forEach(function(o) {
                if (sel.value && o.dataset.value === sel.value) {
                    o.classList.add('selected');
                } else {
                    o.classList.remove('selected');
                }
            });
        }
        syncTriggerToSelect();
        buildOptions();

        function openList() {
            // close all other open dropdowns first
            document.querySelectorAll('.cs-trigger.open').forEach(function(t) {
                if (t !== trigger) {
                    t.classList.remove('open');
                    t.nextElementSibling && t.nextElementSibling.classList.remove('open');
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

        // Watch for programmatic changes to the native select (e.g. toggleShipDistrictFields)
        var mo = new MutationObserver(function() {
            if (sel.classList.contains('hidden')) {
                wrapper.classList.add('hidden');
            } else {
                wrapper.classList.remove('hidden');
            }
            buildOptions();
            syncTriggerToSelect();
        });
        mo.observe(sel, {childList: true, attributes: true, subtree: true, attributeFilter: ['class']});

        // Also observe class changes (hidden/show) on the wrapper
        sel.addEventListener('change', function() {
            syncTriggerToSelect();
        });
    });

    // Close on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('.cs-trigger.open').forEach(function(t) {
            t.classList.remove('open');
        });
        document.querySelectorAll('.cs-list.open').forEach(function(l) {
            l.classList.remove('open');
        });
    });
}

// Run on DOM ready and re-run whenever tabs open (address toggle etc.)
document.addEventListener('DOMContentLoaded', function() {
    initCustomSelects();
});

// Re-init after toggleManualEntry shows new selects
var _origToggleManualEntry = typeof toggleManualEntry === 'function' ? toggleManualEntry : null;
window.addEventListener('load', function() {
    // patch toggleManualEntry and toggleShipDistrictFields to re-init after DOM changes
    var _orig1 = window.toggleManualEntry;
    if (_orig1) {
        window.toggleManualEntry = function() {
            _orig1.apply(this, arguments);
            setTimeout(initCustomSelects, 50);
        };
    }
    var _orig2 = window.toggleShipDistrictFields;
    if (_orig2) {
        window.toggleShipDistrictFields = function() {
            _orig2.apply(this, arguments);
            setTimeout(initCustomSelects, 50);
        };
    }
    initCustomSelects();
});
</script>
