<div class="bg-[#fafafa] min-h-screen flex flex-col md:flex-row relative">
    
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="flex-1 p-3 xs:p-4 sm:p-5 md:p-6 lg:p-12 relative overflow-y-auto w-full min-w-0">
        <div class="max-w-3xl mx-auto w-full min-w-0">
            
            <!-- Page Header -->
            <div class="mb-5 sm:mb-8 flex flex-row items-center justify-between gap-2">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-black text-[#1d1d1f] tracking-tight font-display truncate">Public profile</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5 truncate">Manage your buyer details & credentials</p>
                </div>
                <button type="button" onclick="toggleEditMode()" id="edit-profile-btn" class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-[#F25996] bg-pink-50 hover:bg-pink-100 px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl transition-all shadow-sm shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span id="edit-btn-text">Edit Profile</span>
                </button>
            </div>
            
            <form action="<?= BASE_URL ?>/dashboard/profile/update" method="POST" enctype="multipart/form-data" class="w-full min-w-0">
                
                <!-- Profile Picture & Quick Info Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 md:p-3.5 lg:p-6 border border-gray-100 shadow-sm mb-5 sm:mb-8 flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-6 md:gap-3.5 lg:gap-6 text-center sm:text-left">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 md:w-16 md:h-16 lg:w-28 lg:h-28 rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-100 flex-shrink-0 relative group">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img id="profile-pic-preview" src="<?= htmlspecialchars($user['profile_picture']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <img id="profile-pic-preview" class="w-full h-full object-cover hidden">
                            <svg id="profile-pic-placeholder" class="w-10 h-10 sm:w-12 sm:h-12 md:w-8 md:h-8 lg:w-14 lg:h-14 text-gray-300 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg md:text-sm lg:text-lg font-bold text-gray-900 truncate"><?= htmlspecialchars($user['name'] ?? 'Buyer Profile') ?></h2>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 md:gap-1.5 lg:gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 md:px-2 md:py-0.5 lg:px-2.5 lg:py-0.5 rounded-full text-[11px] sm:text-xs md:text-[10px] lg:text-xs font-semibold bg-pink-50 text-[#F25996] border border-pink-200">
                                <svg class="w-3 h-3 md:w-2.5 md:h-2.5 lg:w-3 lg:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                ID: <?= htmlspecialchars($user['unique_buyer_id'] ?? 'Pending') ?>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 md:px-2 md:py-0.5 lg:px-2.5 lg:py-0.5 rounded-full text-[11px] sm:text-xs md:text-[10px] lg:text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <span class="w-1.5 h-1.5 md:w-1 md:h-1 lg:w-1.5 lg:h-1.5 rounded-full bg-emerald-500"></span>
                                Verified Buyer
                            </span>
                        </div>
                        <div class="mt-3 hidden w-full sm:w-auto" id="change-picture-btn">
                            <label class="bg-[#F25996] hover:bg-[#d8407d] text-white px-4 py-2 rounded-xl font-bold text-xs sm:text-sm cursor-pointer transition-colors text-center shadow inline-flex items-center justify-center gap-1.5 w-full sm:w-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Change Picture</span>
                                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif" class="hidden" onchange="previewProfilePic(this)">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 md:p-6 border border-gray-100 shadow-sm space-y-4 sm:space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 md:gap-4 lg:gap-5">
                        <div class="min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">Business ID</label>
                            <input type="text" value="<?= htmlspecialchars($user['unique_buyer_id'] ?? 'Pending') ?>" disabled class="w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 font-mono text-xs sm:text-sm tracking-wider cursor-not-allowed">
                        </div>
                        <div class="min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required disabled class="profile-input w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 text-xs sm:text-sm focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-colors bg-white disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 md:gap-4 lg:gap-5">
                        <div class="min-w-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f]">Email Address</label>
                                <button type="button" onclick="openEmailChangeModal()" id="btn-change-email" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#F25996] hover:text-white hover:bg-[#F25996] transition-all bg-pink-50 hover:shadow-md px-3 py-1.5 rounded-xl border border-pink-200 shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    <span>Edit / Change Email</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input type="email" id="profile-display-email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled class="w-full px-3.5 py-2.5 sm:px-4 sm:py-3 pr-24 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 text-xs sm:text-sm cursor-not-allowed truncate font-medium">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Verified
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Email can be changed multiple times whenever needed via OTP verification.</p>
                        </div>
                        <div class="min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required pattern="\d{10}" title="Phone number must be exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" disabled class="profile-input w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 text-xs sm:text-sm focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-colors bg-white disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 md:gap-4 lg:gap-5">
                        <div class="sm:col-span-2 min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">Company / Store Name</label>
                            <input type="text" name="business_name" value="<?= htmlspecialchars($profile['business_name'] ?? '') ?>" disabled class="profile-input w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 text-xs sm:text-sm focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-colors bg-white disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-700">
                        </div>
                        <div class="min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">GST Number</label>
                            <input type="text" name="gst_number" value="<?= htmlspecialchars($profile['gst_number'] ?? '') ?>" disabled class="w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 font-mono text-xs sm:text-sm cursor-not-allowed">
                        </div>
                        <div class="min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">PAN Number</label>
                            <input type="text" name="pan_number" value="<?= htmlspecialchars($profile['pan_number'] ?? '') ?>" disabled class="w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 font-mono text-xs sm:text-sm cursor-not-allowed">
                        </div>
                        <div class="sm:col-span-2 min-w-0">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f] mb-1.5">Instagram Profile Link</label>
                            <input type="url" name="instagram_link" value="<?= htmlspecialchars($profile['instagram_link'] ?? '') ?>" disabled class="profile-input w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border border-gray-200 text-xs sm:text-sm focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-colors bg-white disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-700" placeholder="https://instagram.com/yourhandle">
                        </div>
                    </div>

                    <!-- Default Delivery Address -->
                    <div class="pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                            <label class="block text-xs sm:text-sm font-bold text-[#1d1d1f]">Default Delivery Address</label>
                            <a href="<?= BASE_URL ?>/dashboard/addresses" class="text-xs sm:text-sm font-bold text-[#F25996] hover:underline flex items-center gap-1">Manage Addresses &rarr;</a>
                        </div>
                        <div class="p-3.5 sm:p-5 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 text-xs sm:text-sm">
                            <?php if (!empty($defaultAddress)): ?>
                                <?php if (!empty($defaultAddress['label'])): ?>
                                    <span class="inline-block px-2.5 py-0.5 bg-[#F25996] text-white text-[11px] font-bold rounded-full mb-2 uppercase tracking-wider"><?= htmlspecialchars($defaultAddress['label']) ?></span>
                                <?php endif; ?>
                                <p class="font-semibold text-sm sm:text-base text-gray-900 mb-1 break-words"><?= htmlspecialchars($defaultAddress['line1']) ?></p>
                                <?php if (!empty($defaultAddress['line2'])): ?>
                                    <p class="text-gray-600 mb-1 break-words"><?= htmlspecialchars($defaultAddress['line2']) ?></p>
                                <?php endif; ?>
                                <p class="text-gray-600 mb-1"><?= htmlspecialchars($defaultAddress['city']) ?>, <?= htmlspecialchars($defaultAddress['state']) ?> &ndash; <?= htmlspecialchars($defaultAddress['postal_code']) ?></p>
                                <p class="text-gray-500 text-xs sm:text-sm"><?= htmlspecialchars($defaultAddress['country'] ?? 'India') ?></p>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-500 mb-3 text-xs sm:text-sm">No default address set.</p>
                                    <a href="<?= BASE_URL ?>/dashboard/addresses" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-300 rounded-xl text-xs sm:text-sm font-bold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add an Address
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Save / Cancel Container -->
                    <div id="save-button-container" class="mt-6 pt-5 border-t border-gray-100 hidden flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3">
                        <button type="button" onclick="toggleEditMode()" class="w-full sm:w-auto px-5 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors text-center order-2 sm:order-1">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto bg-[#F25996] hover:bg-[#d8407d] text-white px-6 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-md shadow-pink-500/20 transition-all text-center order-1 sm:order-2">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Shop Verification Images -->
            <?php if (!empty($shopImages)): ?>
            <div class="mt-8 sm:mt-12 pt-6 border-t border-gray-200">
                <h2 class="text-base sm:text-xl font-bold text-[#1d1d1f] mb-4">Shop Verification Images</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 sm:gap-4">
                    <?php foreach ($shopImages as $image): ?>
                        <div class="aspect-square bg-gray-50 rounded-xl sm:rounded-2xl overflow-hidden border border-gray-200 shadow-sm relative group">
                            <img src="<?= htmlspecialchars(get_image_url($image['file_path'])) ?>" alt="Shop Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- Email Change Popup Modal with OTP          -->
<!-- ========================================== -->
<div id="email-change-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop overlay -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEmailChangeModal()"></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-gray-100 p-5 sm:p-7">
            
            <!-- Close Button -->
            <button type="button" onclick="closeEmailChangeModal()" class="absolute top-4 right-4 sm:top-5 sm:right-5 text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- ================= STEP 1: Reason & New Email ================= -->
            <div id="email-step-1">
                <div class="flex items-center gap-3.5 mb-5">
                    <div class="w-12 h-12 rounded-2xl bg-pink-50 border border-pink-100 text-[#F25996] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight font-display">Update Email Address</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Change your email address if your current inbox is blocked or inaccessible</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Current Email Notice -->
                    <div class="bg-gray-50 border border-gray-200/70 rounded-xl p-3 text-xs text-gray-600 flex items-center justify-between">
                        <span class="text-gray-500">Current Registered Email:</span>
                        <span id="modal-current-email-text" class="font-bold text-gray-800 font-mono"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                    </div>

                    <!-- Field 1: Reason for Email Change -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-800">
                                Reason for Changing Email <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-medium">Recorded in admin audit log</span>
                        </div>

                        <!-- Quick suggestion chips -->
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <button type="button" onclick="setChangeReason('Email is blocked / cannot access my previous inbox')" class="text-[10px] sm:text-[11px] font-semibold px-2.5 py-1 bg-gray-100 hover:bg-pink-50 hover:text-[#F25996] hover:border-pink-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">
                                🔒 Email Blocked / Inaccessible
                            </button>
                            <button type="button" onclick="setChangeReason('Lost password / 2FA recovery not available on old email')" class="text-[10px] sm:text-[11px] font-semibold px-2.5 py-1 bg-gray-100 hover:bg-pink-50 hover:text-[#F25996] hover:border-pink-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">
                                🔑 Lost Access to Old Email
                            </button>
                            <button type="button" onclick="setChangeReason('Business domain or email address updated')" class="text-[10px] sm:text-[11px] font-semibold px-2.5 py-1 bg-gray-100 hover:bg-pink-50 hover:text-[#F25996] hover:border-pink-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">
                                🏢 New Company Email
                            </button>
                        </div>

                        <textarea id="email-change-reason" rows="2" placeholder="Explain why you are changing your email address (e.g., Old email is blocked, cannot receive emails)..." class="w-full text-xs text-gray-800 bg-gray-50 border border-gray-200 rounded-xl p-3 focus:outline-none focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-all resize-none"></textarea>
                        <p id="reason-error-msg" class="text-[11px] text-red-500 font-semibold mt-1 hidden"></p>
                    </div>

                    <!-- Field 2: New Email Address with Inline Real-Time Validation -->
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1.5">
                            New Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email-change-new-email" placeholder="example@domain.com" autocomplete="off" class="w-full text-xs sm:text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-xl p-3 pr-10 focus:outline-none focus:bg-white focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/10 transition-all">
                            
                            <!-- Inline Validation Indicator Icon -->
                            <div id="email-status-icon" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                <span id="icon-checking" class="hidden">
                                    <svg class="animate-spin h-4 w-4 text-[#F25996]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span id="icon-valid" class="hidden text-emerald-500">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </span>
                                <span id="icon-invalid" class="hidden text-red-500">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                </span>
                            </div>
                        </div>

                        <!-- Live Inline Message -->
                        <div id="email-feedback-container" class="mt-1.5 min-h-[18px]">
                            <p id="email-feedback-text" class="text-[11px] font-semibold text-gray-400"></p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-3 text-[11px] text-amber-800 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>A 6-digit verification code will be sent to the <strong>new email</strong>. Once verified, your old email cannot be used to login again.</span>
                    </div>

                    <!-- Step 1 Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                        <button type="button" onclick="closeEmailChangeModal()" class="px-4 py-2.5 rounded-xl font-bold text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="send-otp-btn" onclick="submitRequestOtp()" disabled class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-[#F25996] hover:bg-[#d8407d] disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-pink-500/20 transition-all">
                            <span id="send-otp-text">Send Verification Code</span>
                            <svg id="send-otp-spinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 2: OTP Verification ================= -->
            <div id="email-step-2" class="hidden">
                <div class="text-center mb-5">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-pink-50 border border-pink-200 text-[#F25996] flex items-center justify-center mb-3 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight font-display">Verify New Email</h3>
                    <p class="text-xs text-gray-500 mt-1">We sent a 6-digit confirmation code to:</p>
                    <p id="target-email-badge" class="inline-block mt-1.5 px-3 py-1 bg-pink-50 text-[#F25996] border border-pink-200 rounded-full font-bold font-mono text-xs"></p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 text-center mb-2">
                            Enter 6-Digit OTP Code
                        </label>
                        <div class="max-w-xs mx-auto">
                            <input type="text" id="email-otp-input" maxlength="6" pattern="\d{6}" placeholder="------" class="w-full text-center font-mono text-2xl font-bold tracking-[0.5em] px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:bg-white focus:border-[#F25996] focus:ring-4 focus:ring-pink-100 transition-all text-gray-900" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); checkOtpReady();">
                        </div>
                        <p id="otp-error-msg" class="text-xs text-red-500 font-semibold text-center mt-2 hidden"></p>
                    </div>

                    <!-- Resend Section -->
                    <div class="text-center pt-1">
                        <p class="text-xs text-gray-500">
                            Didn't receive the email?
                            <button type="button" id="resend-otp-btn" onclick="submitResendOtp()" disabled class="font-bold text-[#F25996] hover:underline disabled:opacity-50 disabled:no-underline cursor-pointer">
                                Resend Code <span id="resend-countdown-text">(60s)</span>
                            </button>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" onclick="backToStep1()" class="px-4 py-2.5 rounded-xl font-bold text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            &larr; Change Details
                        </button>
                        <button type="button" id="verify-otp-btn" onclick="submitVerifyOtp()" disabled class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-[#F25996] hover:bg-[#d8407d] disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-pink-500/20 transition-all">
                            <span id="verify-otp-text">Verify & Complete</span>
                            <svg id="verify-otp-spinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 3: Success Confirmation ================= -->
            <div id="email-step-3" class="hidden text-center py-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 border-2 border-emerald-200 text-emerald-600 flex items-center justify-center mb-4 shadow-sm animate-bounce">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight font-display mb-1">Email Successfully Updated!</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mb-4">
                    Your account email has been updated to <strong id="success-new-email-text" class="text-gray-800 font-mono"></strong>.
                </p>
                <div class="bg-pink-50 border border-pink-200/80 rounded-2xl p-3.5 text-xs text-pink-900 text-left mb-5">
                    <p class="font-bold flex items-center gap-1.5 mb-1">
                        <svg class="w-4 h-4 text-[#F25996]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        Important Notice:
                    </p>
                    <p class="text-gray-700">For all future logins, you must use your new email address. Your old email is now disconnected and can no longer be used to login.</p>
                </div>
                <button type="button" onclick="closeEmailChangeModal()" class="w-full bg-[#F25996] hover:bg-[#d8407d] text-white py-3 px-6 rounded-xl font-bold text-sm shadow-md shadow-pink-500/20 transition-all">
                    Got it, continue
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// --- Profile Edit Mode ---
function toggleEditMode() {
    const changePicBtn = document.getElementById('change-picture-btn');
    if (changePicBtn) {
        changePicBtn.classList.toggle('hidden');
    }
    
    const saveContainer = document.getElementById('save-button-container');
    const editBtnText = document.getElementById('edit-btn-text');
    
    if (saveContainer) {
        if (saveContainer.classList.contains('hidden')) {
            saveContainer.classList.remove('hidden');
            saveContainer.classList.add('flex');
            if (editBtnText) editBtnText.textContent = 'Editing...';
        } else {
            saveContainer.classList.add('hidden');
            saveContainer.classList.remove('flex');
            if (editBtnText) editBtnText.textContent = 'Edit Profile';
        }
    }
    
    const inputs = document.querySelectorAll('.profile-input');
    inputs.forEach(input => {
        input.disabled = !input.disabled;
    });
}

function previewProfilePic(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profile-pic-preview');
            const placeholder = document.getElementById('profile-pic-placeholder');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ========================================================
// Email Change & OTP Verification Logic
// ========================================================
let emailCheckTimeout = null;
let isEmailValid = false;
let resendTimerInterval = null;
let resendSecondsRemaining = 60;

function openEmailChangeModal() {
    const modal = document.getElementById('email-change-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    resetEmailModalState();
}

function closeEmailChangeModal() {
    const modal = document.getElementById('email-change-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    if (resendTimerInterval) {
        clearInterval(resendTimerInterval);
    }
}

function resetEmailModalState() {
    document.getElementById('email-step-1').classList.remove('hidden');
    document.getElementById('email-step-2').classList.add('hidden');
    document.getElementById('email-step-3').classList.add('hidden');
    
    document.getElementById('email-change-reason').value = '';
    document.getElementById('email-change-new-email').value = '';
    document.getElementById('email-otp-input').value = '';
    
    document.getElementById('reason-error-msg').classList.add('hidden');
    document.getElementById('otp-error-msg').classList.add('hidden');
    
    hideEmailStatusIcons();
    setFeedback('', '');
    isEmailValid = false;
    document.getElementById('send-otp-btn').disabled = true;
}

function setChangeReason(reason) {
    const textarea = document.getElementById('email-change-reason');
    if (textarea) {
        textarea.value = reason;
        document.getElementById('reason-error-msg').classList.add('hidden');
        validateStep1();
    }
}

// Real-Time Inline Validation on Email Input
const newEmailInput = document.getElementById('email-change-new-email');
const reasonInput = document.getElementById('email-change-reason');

if (newEmailInput) {
    newEmailInput.addEventListener('input', function() {
        const email = this.value.trim();
        clearTimeout(emailCheckTimeout);
        isEmailValid = false;
        updateSendBtnState();

        if (!email) {
            hideEmailStatusIcons();
            setFeedback('', '');
            return;
        }

        // Basic Regex format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailRegex.test(email)) {
            showStatusIcon('invalid');
            setFeedback('Please enter a valid email format (e.g. name@domain.com)', 'red');
            return;
        }

        // Format is valid -> show checking spinner and debounce backend availability check
        showStatusIcon('checking');
        setFeedback('Checking availability in real-time...', 'gray');

        emailCheckTimeout = setTimeout(() => {
            performEmailAvailabilityCheck(email);
        }, 350);
    });
}

if (reasonInput) {
    reasonInput.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            document.getElementById('reason-error-msg').classList.add('hidden');
        }
        updateSendBtnState();
    });
}

function hideEmailStatusIcons() {
    document.getElementById('email-status-icon').classList.add('hidden');
    document.getElementById('icon-checking').classList.add('hidden');
    document.getElementById('icon-valid').classList.add('hidden');
    document.getElementById('icon-invalid').classList.add('hidden');
}

function showStatusIcon(type) {
    const wrap = document.getElementById('email-status-icon');
    wrap.classList.remove('hidden');
    document.getElementById('icon-checking').classList.toggle('hidden', type !== 'checking');
    document.getElementById('icon-valid').classList.toggle('hidden', type !== 'valid');
    document.getElementById('icon-invalid').classList.toggle('hidden', type !== 'invalid');
}

function setFeedback(msg, color) {
    const textEl = document.getElementById('email-feedback-text');
    textEl.textContent = msg;
    textEl.className = 'text-[11px] font-semibold';
    if (color === 'red') {
        textEl.classList.add('text-red-500');
    } else if (color === 'green') {
        textEl.classList.add('text-emerald-600');
    } else {
        textEl.classList.add('text-gray-400');
    }
}

function updateSendBtnState() {
    const reason = document.getElementById('email-change-reason').value.trim();
    const btn = document.getElementById('send-otp-btn');
    btn.disabled = !(isEmailValid && reason.length > 2);
}

function validateStep1() {
    updateSendBtnState();
}

function performEmailAvailabilityCheck(email) {
    fetch('<?= BASE_URL ?>/dashboard/profile/check-email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if (data.available) {
            showStatusIcon('valid');
            setFeedback('✓ ' + data.message, 'green');
            isEmailValid = true;
        } else {
            showStatusIcon('invalid');
            setFeedback('✕ ' + data.message, 'red');
            isEmailValid = false;
        }
        updateSendBtnState();
    })
    .catch(err => {
        showStatusIcon('invalid');
        setFeedback('Unable to verify email availability right now.', 'red');
        isEmailValid = false;
        updateSendBtnState();
    });
}

// Submit Request for OTP
function submitRequestOtp() {
    const reason = document.getElementById('email-change-reason').value.trim();
    const email = document.getElementById('email-change-new-email').value.trim();

    if (!reason) {
        const rErr = document.getElementById('reason-error-msg');
        rErr.textContent = 'Please enter a reason for changing your email address.';
        rErr.classList.remove('hidden');
        return;
    }

    if (!isEmailValid) {
        return;
    }

    const sendBtn = document.getElementById('send-otp-btn');
    const sendText = document.getElementById('send-otp-text');
    const sendSpinner = document.getElementById('send-otp-spinner');

    sendBtn.disabled = true;
    sendText.textContent = 'Sending OTP...';
    sendSpinner.classList.remove('hidden');

    fetch('<?= BASE_URL ?>/dashboard/profile/request-email-change', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, reason: reason })
    })
    .then(res => res.json())
    .then(data => {
        sendBtn.disabled = false;
        sendText.textContent = 'Send Verification Code';
        sendSpinner.classList.add('hidden');

        if (data.success) {
            // Move to Step 2
            document.getElementById('email-step-1').classList.add('hidden');
            document.getElementById('email-step-2').classList.remove('hidden');
            document.getElementById('target-email-badge').textContent = data.target_email || email;
            document.getElementById('email-otp-input').value = '';
            document.getElementById('email-otp-input').focus();
            startResendTimer();
        } else {
            alert(data.message || 'Failed to send OTP code.');
        }
    })
    .catch(err => {
        sendBtn.disabled = false;
        sendText.textContent = 'Send Verification Code';
        sendSpinner.classList.add('hidden');
        alert('Server communication error. Please try again.');
    });
}

function startResendTimer() {
    if (resendTimerInterval) clearInterval(resendTimerInterval);
    resendSecondsRemaining = 60;
    const resendBtn = document.getElementById('resend-otp-btn');
    const countdownText = document.getElementById('resend-countdown-text');

    resendBtn.disabled = true;
    countdownText.textContent = `(${resendSecondsRemaining}s)`;

    resendTimerInterval = setInterval(() => {
        resendSecondsRemaining--;
        if (resendSecondsRemaining <= 0) {
            clearInterval(resendTimerInterval);
            resendBtn.disabled = false;
            countdownText.textContent = '';
        } else {
            countdownText.textContent = `(${resendSecondsRemaining}s)`;
        }
    }, 1000);
}

function submitResendOtp() {
    const resendBtn = document.getElementById('resend-otp-btn');
    resendBtn.disabled = true;

    fetch('<?= BASE_URL ?>/dashboard/profile/resend-email-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            startResendTimer();
            alert(data.message || 'A new code has been sent to your email.');
        } else {
            alert(data.message || 'Could not resend OTP.');
            resendBtn.disabled = false;
        }
    })
    .catch(err => {
        alert('Error resending OTP code.');
        resendBtn.disabled = false;
    });
}

function checkOtpReady() {
    const otp = document.getElementById('email-otp-input').value.trim();
    document.getElementById('verify-otp-btn').disabled = (otp.length !== 6);
    document.getElementById('otp-error-msg').classList.add('hidden');
}

function backToStep1() {
    document.getElementById('email-step-2').classList.add('hidden');
    document.getElementById('email-step-1').classList.remove('hidden');
    if (resendTimerInterval) clearInterval(resendTimerInterval);
}

function submitVerifyOtp() {
    const otp = document.getElementById('email-otp-input').value.trim();
    if (otp.length !== 6) return;

    const verifyBtn = document.getElementById('verify-otp-btn');
    const verifyText = document.getElementById('verify-otp-text');
    const verifySpinner = document.getElementById('verify-otp-spinner');
    const otpError = document.getElementById('otp-error-msg');

    verifyBtn.disabled = true;
    verifyText.textContent = 'Verifying...';
    verifySpinner.classList.remove('hidden');
    otpError.classList.add('hidden');

    fetch('<?= BASE_URL ?>/dashboard/profile/verify-email-change', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ otp: otp })
    })
    .then(res => res.json())
    .then(data => {
        verifyBtn.disabled = false;
        verifyText.textContent = 'Verify & Complete';
        verifySpinner.classList.add('hidden');

        if (data.success) {
            // Update email on the profile page
            const displayEmailInput = document.getElementById('profile-display-email');
            if (displayEmailInput) {
                displayEmailInput.value = data.new_email;
            }
            const modalCurrentText = document.getElementById('modal-current-email-text');
            if (modalCurrentText) {
                modalCurrentText.textContent = data.new_email;
            }

            // Show Step 3 Success View
            document.getElementById('email-step-2').classList.add('hidden');
            document.getElementById('email-step-3').classList.remove('hidden');
            document.getElementById('success-new-email-text').textContent = data.new_email;
        } else {
            otpError.textContent = data.message || 'Invalid or expired OTP code.';
            otpError.classList.remove('hidden');
        }
    })
    .catch(err => {
        verifyBtn.disabled = false;
        verifyText.textContent = 'Verify & Complete';
        verifySpinner.classList.add('hidden');
        otpError.textContent = 'Server verification error. Please try again.';
        otpError.classList.remove('hidden');
    });
}
</script>
