<style>
    /* ── Custom Pink Select (Register Page) ── */
    .reg-cs-wrapper {
        position: relative;
        width: 100%;
    }
    .reg-cs-trigger {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 0.625rem 0.875rem;
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
        font-weight: 500;
    }
    .reg-cs-trigger.placeholder { color: #9ca3af; font-weight: 400; }
    .reg-cs-trigger:focus,
    .reg-cs-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
    }
    .reg-cs-trigger.error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
    }
    .reg-cs-arrow {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: #9ca3af;
        transition: transform 0.2s ease;
        pointer-events: none;
    }
    .reg-cs-trigger.open .reg-cs-arrow { transform: rotate(180deg); color: #F25996; }
    .reg-cs-list {
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
    .reg-cs-list.open { display: block; }
    .reg-cs-list::-webkit-scrollbar { width: 4px; }
    .reg-cs-list::-webkit-scrollbar-track { background: transparent; }
    .reg-cs-list::-webkit-scrollbar-thumb { background: #F25996; border-radius: 4px; }
    .reg-cs-option {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.5rem;
        cursor: pointer;
        color: #374151;
        transition: background 0.15s, color 0.15s;
    }
    .reg-cs-option:hover {
        background: #fce7f3;
        color: #F25996;
    }
    .reg-cs-option.selected {
        background: #F25996;
        color: #ffffff;
        font-weight: 600;
    }
    .reg-cs-option.disabled {
        color: #827777ff;
        cursor: default;
        pointer-events: none;
    }
</style>

<div class="min-h-[calc(100vh-80px)] h-auto relative flex flex-col justify-center py-8 px-4 sm:px-6 lg:px-8 bg-[#fdfdfd] overflow-hidden">
    
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-64 h-64 rounded-br-full -z-10 opacity-70" style="background-color: #FDBFDD;"></div>
    <div class="absolute top-8 right-12 w-16 h-16 rounded-full flex items-center justify-center -z-10 opacity-80" style="background-color: #FDBFDD; color: #F25996;">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
    </div>
    <!-- Dot pattern -->
    <div class="absolute top-24 left-12 grid grid-cols-4 gap-2 opacity-20 -z-10">
        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div><div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
    </div>

    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center z-10 mb-3">
        <h2 class="mt-2 text-3xl font-extrabold text-gray-900 font-display">
            Create an Account on <span style="color: #F25996;">GRM B2B</span>
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Already have an account? 
            <?php $regRedirect = $redirect ?? $_GET['redirect'] ?? \Core\Session::get('return_to') ?? ''; ?>
            <a href="<?= BASE_URL ?>/login<?= !empty($regRedirect) ? '?redirect=' . urlencode($regRedirect) : '' ?>" class="font-bold hover:underline" style="color: #F25996;">
                Log in here
            </a>
        </p>
    </div>

    <!-- Role Switcher Tabs -->
    <?php if (is_vendor_module_enabled()): ?>
    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 mb-6 px-4">
        <div class="p-1.5 rounded-2xl flex border" style="background-color: rgba(253, 191, 221, 0.25); border-color: #FDBFDD;">
            <a href="<?= BASE_URL ?>/register" class="flex-1 py-2.5 text-center rounded-xl font-bold text-sm bg-white shadow-sm transition flex items-center justify-center" style="color: #F25996;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Wholesale Buyer
            </a>
            <a href="<?= BASE_URL ?>/vendor-register" class="flex-1 py-2.5 text-center rounded-xl font-bold text-sm text-gray-500 hover:text-[#F25996] transition flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Vendor / Seller
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="sm:mx-auto sm:w-full sm:max-w-3xl z-10 relative flex flex-col h-full mb-8">
        
        <!-- Custom Progress Stepper -->
        <div class="flex justify-between items-start mb-6 relative px-8 shrink-0">
            <!-- Connecting Line -->
            <div class="absolute top-5 left-16 right-16 h-[2px] bg-gray-200 -z-10">
                <div id="progress-line" class="h-full transition-all duration-500 w-0" style="background-color: #F25996;"></div>
            </div>

            <!-- Step 1 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="step-1-icon" class="w-10 h-10 rounded-full text-white flex items-center justify-center shadow-md mb-2 transition-colors duration-300" style="background-color: #F25996;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span id="step-1-text" class="text-xs font-bold" style="color: #F25996;">Personal</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Your basic information</span>
            </div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="step-2-icon" class="w-10 h-10 rounded-full bg-gray-100 border-2 border-gray-200 text-gray-400 flex items-center justify-center mb-2 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span id="step-2-text" class="text-xs font-bold text-gray-500">Business</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Business details</span>
            </div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center relative bg-[#fdfdfd] px-2">
                <div id="step-3-icon" class="w-10 h-10 rounded-full bg-gray-100 border-2 border-gray-200 text-gray-400 flex items-center justify-center mb-2 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <span id="step-3-text" class="text-xs font-bold text-gray-500">Verification</span>
                <span class="text-[10px] text-gray-400 font-medium hidden sm:block mt-0.5">Verify your identity</span>
            </div>
        </div>

        <div class="bg-white shadow-[0_4px_24px_-4px_rgba(0,0,0,0.05)] rounded-2xl sm:rounded-2xl border border-gray-100 overflow-hidden h-auto">
            <form id="multi-step-form" action="<?= BASE_URL ?>/register" method="POST" enctype="multipart/form-data" class="h-auto" autocomplete="off" novalidate>
                
                <!-- STEP 1: Personal Details -->
                <div id="step-1" class="step-content flex flex-col h-auto">
                    
                    <div class="p-6 pb-2">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-sm mr-3" style="background-color: #F25996;">01</div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 font-display">Personal Details</h3>
                                </div>
                            </div>
                            <!-- Decorative graphic -->
                            <div class="hidden sm:block w-16 h-10 rounded-lg relative overflow-hidden transform -skew-x-12 border" style="background-color: #FDBFDD; border-color: #FDBFDD;">
                                <div class="absolute inset-0 flex items-center justify-center opacity-40">
                                    <svg class="w-8 h-8" style="color: #F25996;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                </div>
                                <div class="absolute bottom-1 right-1 w-3 h-3 rounded-full text-white flex items-center justify-center shadow" style="background-color: #F25996;">
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
                            <div>
                                <label for="name" class="flex items-center text-sm font-semibold text-gray-600 mb-1.5">
                                    <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Full Name <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input id="name" name="name" type="text" pattern="^[^0-9]+$" title="Name cannot contain numbers" oninput="this.value = this.value.replace(/[0-9]/g, '')" placeholder="Enter your full name" required autocomplete="off" class="form-input px-4 py-2 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white">
                                <div id="name-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="name-error-text">Please enter your full name.</span>
                                </div>
                            </div>
                            <div>
                                <label for="phone" class="flex items-center text-sm font-semibold text-gray-600 mb-1.5">
                                    <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    Phone Number <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div id="phone-wrapper" class="flex border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-[#F25996] focus-within:border-[#F25996] bg-white transition-colors">
                                    <div class="bg-gray-50 flex items-center px-3 border-r border-gray-200">
                                        <span class="text-xs font-bold text-gray-700 mr-1">IN</span> <span class="text-sm font-medium text-gray-600">+91</span>
                                    </div>
                                    <input id="phone" name="phone" type="tel" pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Please enter a valid 10-digit phone number" placeholder="Enter phone number" required autocomplete="off" class="w-full px-4 py-2 border-none outline-none focus:ring-0 text-sm bg-transparent">
                                </div>
                                <div id="phone-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="phone-error-text">Please enter a valid 10-digit phone number.</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="flex items-center text-sm font-semibold text-gray-600 mb-1.5">
                                <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Email Address <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input id="email" name="email" type="email" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.(com|in|co\.in|net|org|edu|gov|io|biz|info)$" title="Please enter a valid email address with a proper domain (e.g. .com, .in)" placeholder="admin@example.com" required autocomplete="off" class="form-input px-4 py-2 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm pr-10 bg-white">
                            </div>
                            <div id="email-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="email-error-text">Please enter a valid email address.</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1">This email will receive an OTP for verification during registration.</p>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="flex items-center text-sm font-semibold text-gray-600 mb-1.5">
                                <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Password <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Password must contain at least 8 characters, including one uppercase letter, one lowercase letter, and one number" placeholder="••••••••" required autocomplete="new-password" class="form-input px-4 py-2 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm pr-10 bg-white transition-colors duration-300">
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                            <div id="password-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="password-error-text">Password must be at least 8 characters and include uppercase, lowercase, and numbers.</span>
                            </div>
                            <!-- Password Validation Feedback -->
                            <div id="password-feedback" class="hidden mt-2 ml-1 text-xs transition-all duration-300">
                                <ul class="space-y-1">
                                    <li id="req-length" class="flex items-center text-red-500"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> <span>At least 8 characters</span></li>
                                    <li id="req-upper" class="flex items-center text-red-500"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> <span>One uppercase letter</span></li>
                                    <li id="req-lower" class="flex items-center text-red-500"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> <span>One lowercase letter</span></li>
                                    <li id="req-number" class="flex items-center text-red-500"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> <span>One number</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="p-3 sm:px-6 flex flex-col sm:flex-row items-center justify-between shrink-0" style="background-color: rgba(253, 191, 221, 0.25);">
                        <div class="flex items-center text-xs text-gray-600 mb-2 sm:mb-0">
                            <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Your information is safe with us.
                        </div>
                        <button type="button" onclick="nextStep(1)" class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex items-center justify-center transition-all shadow-lg text-sm" style="background: #F25996;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Next Step <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Business Details -->
                <div id="step-2" class="step-content hidden flex flex-col h-auto">
                    <div class="p-6 md:p-8 pb-4">
                        
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center">
                                <div class="font-bold text-xl rounded-2xl w-14 h-14 flex items-center justify-center mr-4 shadow-sm border" style="background-color: #FDBFDD; color: #F25996; border-color: #FDBFDD;">02</div>
                                <div>
                                    <h3 class="text-lg sm:text-2xl font-bold text-gray-900 font-display mb-1">Business Details</h3>
                                    <p class="text-sm text-gray-500 font-medium">Tell us about your business</p>
                                </div>
                            </div>
                            <div class="w-16 h-16 rounded-full flex items-center justify-center shadow-sm border relative" style="background-color: #FDBFDD; color: #F25996; border-color: #FDBFDD;">
                                <!-- Sparkles -->
                                <svg class="absolute -top-1 -right-1 w-4 h-4" style="color: #F25996;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
                                <svg class="absolute bottom-1 -left-2 w-3 h-3" style="color: #F25996;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
                                <!-- Store Icon -->
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V10C19 8.89543 18.1046 8 17 8H7C5.89543 8 5 8.89543 5 10V21M3 21H21M9 21V16H15V21M6 8L7.5 4H16.5L18 8M6 8H18"></path></svg>
                            </div>
                        </div>

                        <!-- Business Name -->
                        <div class="mb-4">
                            <label for="business_name" class="flex items-center text-sm font-bold text-gray-700 mb-2">
                                <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Business Name <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input id="business_name" name="business_name" type="text" placeholder="Enter your registered business name" minlength="4" required autocomplete="off" oninput="this.value = this.value.toUpperCase()" class="form-input px-4 py-3 border-2 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] font-semibold text-sm sm:text-base text-gray-900 bg-white uppercase pr-10 transition-colors duration-300" style="border-color: #F25996;">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                            <div id="business-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="business-error-text">This business name is already registered.</span>
                            </div>
                        </div>

                        <!-- Address Details Grid -->
                        <div class="mb-6 space-y-4">
                            <div>
                                <label for="shop_location" class="flex items-center text-sm font-bold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Shop Location / Street Address <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input id="shop_location" name="shop_location" type="text" placeholder="Enter your street address or landmark" required autocomplete="off" class="form-input px-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm text-gray-900 bg-white">
                                <div id="shop_location-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="shop_location-error-text">Please enter your shop address or location.</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="state" class="flex items-center text-xs font-bold text-gray-700 mb-1.5">State <span class="text-red-500 ml-1">*</span></label>
                                    <select id="state" name="state" required onchange="toggleDistrictFields()" class="form-input px-4 py-2.5 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900">
                                        <option value="">Select State</option>
                                        <option value="Tamil Nadu">Tamil Nadu</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <div id="state-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="state-error-text">Please select your state.</span>
                                    </div>
                                </div>
                                <div id="district_container">
                                    <label for="district" class="flex items-center text-xs font-bold text-gray-700 mb-1.5">District <span class="text-red-500 ml-1">*</span></label>
                                    
                                    <!-- Dropdown for Tamil Nadu -->
                                    <select id="district_select" name="district" required class="form-input px-4 py-2.5 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900 hidden">
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
                                    
                                    <!-- Text Input for Others -->
                                    <input id="district_text" name="district" type="text" pattern="[A-Za-z\s]+" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')" title="Please enter letters only" placeholder="Enter District Name" autocomplete="off" class="form-input px-4 py-2.5 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900 hidden" disabled>
                                    <div id="district-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="district-error-text">Please select or enter your district.</span>
                                    </div>
                                </div>
                                <div id="other_state_container" class="hidden sm:col-span-2">
                                    <label for="other_state_name" class="flex items-center text-xs font-bold text-gray-700 mb-1.5">State Name <span class="text-red-500 ml-1">*</span></label>
                                    <input id="other_state_name" name="other_state_name" type="text" pattern="[A-Za-z\s]+" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')" title="Please enter letters only" placeholder="Enter state name" autocomplete="off" class="form-input px-4 py-2.5 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900" disabled>
                                    <div id="other_state_name-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="other_state_name-error-text">Please enter your state name.</span>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="pincode" class="flex items-center text-xs font-bold text-gray-700 mb-1.5">Pincode <span class="text-red-500 ml-1">*</span></label>
                                    <input id="pincode" name="pincode" type="text" pattern="[0-9]{6}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Please enter a valid 6-digit pincode" placeholder="e.g. 600001" required autocomplete="off" class="form-input px-4 py-2.5 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm text-gray-900 bg-white">
                                    <div id="pincode-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="pincode-error-text">Please enter a valid 6-digit pincode.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GST Toggle Section -->
                        <div class="mb-6">
                            <div id="gst_section" class="space-y-3 mb-4">
                                <div>
                                    <label for="gst_number" class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-1.5" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        GST Number
                                    </label>
                                    <input id="gst_number" name="gst_number" type="text" minlength="15" maxlength="15" pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" title="Please enter a valid 15-character GST number containing both letters and numbers (e.g. 33AAAAA0000A1Z5)" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" autocomplete="off" class="form-input px-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white uppercase font-medium text-gray-900" placeholder="e.g. 33AAAAA0000A1Z5" required>
                                    <div id="gst_number-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="gst_number-error-text">Please enter a valid 15-character GSTIN containing letters and numbers.</span>
                                    </div>
                                </div>

                                <!-- GST Document Upload -->
                                <div class="mt-3">
                                    <label class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-1.5" style="color:#F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        GST Document <span class="text-red-500 ml-1">*</span>
                                        <span class="ml-1 text-gray-400 font-normal">(PDF / JPG / PNG · Max 3MB)</span>
                                    </label>
                                    <label id="gst_doc_label" for="gst_document" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-[#F25996] hover:bg-pink-50/40 transition-all group">
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#F25996] flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span id="gst_doc_name" class="text-sm text-gray-400 group-hover:text-gray-600 truncate">Click to upload GST certificate / registration document</span>
                                    </label>
                                    <input id="gst_document" name="gst_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleDocUpload(this, 'gst_doc_name', 'gst_doc_label', 'gst_document-error', 'gst_document-error-text')">
                                    <div id="gst_document-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="gst_document-error-text">Please upload your GST document.</span>
                                    </div>
                                </div>
                            </div>

                            <label class="flex items-start mb-2 cursor-pointer group">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input type="checkbox" id="has_gst" name="has_gst" value="1" class="h-4 w-4 rounded border-gray-300" style="accent-color: #F25996;" onchange="toggleGstFields()" checked>
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-bold text-gray-900 group-hover:text-[#F25996] transition-colors">I have a GST Number</span>
                                    <p class="text-gray-500 font-medium mt-0.5">Add your GST details for faster verification</p>
                                </div>
                            </label>
                            
                            <div id="non_gst_section" class="hidden space-y-4 mt-4">
                                <div>
                                    <label for="no_gst_reason" class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-1.5" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Reason for no GST
                                    </label>
                                    <select id="no_gst_reason" name="no_gst_reason" onchange="toggleOtherGstReason()" class="form-input px-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900">
                                        <option value="">Select a reason</option>
                                        <option value="Turnover below threshold">Turnover below threshold</option>
                                        <option value="Only dealing in exempt goods">Only dealing in exempt goods</option>
                                        <option value="Not started operations yet">Not started operations yet</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div id="no_gst_reason-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="no_gst_reason-error-text">Please select a reason for not having GST.</span>
                                    </div>
                                </div>
                                <div id="other_gst_reason_container" class="hidden mt-3">
                                    <label for="other_gst_reason" class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        Specify Other Reason <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input id="other_gst_reason" name="other_gst_reason" type="text" minlength="5" pattern="[A-Za-z\s]+" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')" title="Please enter letters only, minimum 5 characters" autocomplete="off" class="form-input px-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900" placeholder="Please explain why...">
                                    <div id="other_gst_reason-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="other_gst_reason-error-text">Please provide a reason (minimum 5 characters).</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="pan_number" class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-1.5" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                        PAN Number (For Non-GST Verification) <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <input id="pan_number" name="pan_number" type="text" maxlength="10" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" title="Please enter a valid PAN format (e.g. ABCDE1234F)" autocomplete="off" class="form-input px-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white uppercase font-medium text-gray-900" placeholder="10-DIGIT PAN NUMBER">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                    </div>
                                    <div id="pan_number-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="pan_number-error-text">Please enter a valid 10-character PAN number.</span>
                                    </div>
                                </div>

                                <!-- PAN Document Upload -->
                                <div class="mt-3">
                                    <label class="flex items-center text-xs font-bold text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-1.5" style="color:#F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        PAN Card Document <span class="text-red-500 ml-1">*</span>
                                        <span class="ml-1 text-gray-400 font-normal">(PDF / JPG / PNG · Max 3MB)</span>
                                    </label>
                                    <label id="pan_doc_label" for="pan_document" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-[#F25996] hover:bg-pink-50/40 transition-all group">
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#F25996] flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span id="pan_doc_name" class="text-sm text-gray-400 group-hover:text-gray-600 truncate">Click to upload PAN card document</span>
                                    </label>
                                    <input id="pan_document" name="pan_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleDocUpload(this, 'pan_doc_name', 'pan_doc_label', 'pan_document-error', 'pan_document-error-text')">
                                    <div id="pan_document-error" class="hidden mt-1.5 ml-1 text-xs text-red-500 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="pan_document-error-text">Please upload your PAN card document.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="p-4 sm:px-8 border-t flex flex-col sm:flex-row items-center justify-between rounded-b-2xl shrink-0" style="background-color: rgba(253, 191, 221, 0.25); border-color: #FDBFDD;">
                        <button type="button" onclick="prevStep(2)" class="w-full sm:w-auto font-bold py-3 px-8 rounded-xl flex items-center justify-center transition-all shadow-sm text-sm border-2 border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300 mb-4 sm:mb-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Back
                        </button>
                        <div class="w-full sm:w-auto flex flex-col items-center sm:items-end">
                            <button type="button" onclick="nextStep(2)" class="w-full sm:w-auto text-white font-bold py-3 px-8 rounded-xl flex items-center justify-center transition-all shadow-lg text-sm mb-2" style="background: #F25996;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                Next Step <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                            <div class="flex items-center text-xs font-medium text-gray-500">
                                <svg class="w-3.5 h-3.5 mr-1" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                Your information is secure with us
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Verification -->
                <div id="step-3" class="step-content hidden flex flex-col h-auto">
                    <div class="p-6 md:p-8 pb-4">
                        
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center">
                                <div class="font-bold text-xl rounded-2xl w-14 h-14 flex items-center justify-center mr-4 shadow-sm border" style="background-color: #FDBFDD; color: #F25996; border-color: #FDBFDD;">03</div>
                                <div>
                                    <h3 class="text-lg sm:text-2xl font-bold text-gray-900 font-display mb-1">Shop Verification</h3>
                                    <p class="text-sm text-gray-500 font-medium">Help us verify your business</p>
                                </div>
                            </div>
                            <div class="w-16 h-16 rounded-full flex items-center justify-center shadow-sm border relative" style="background-color: #FDBFDD; color: #F25996; border-color: #FDBFDD;">
                                <!-- Sparkles -->
                                <svg class="absolute -top-1 -right-1 w-4 h-4" style="color: #F25996;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
                                <svg class="absolute bottom-1 -left-2 w-3 h-3" style="color: #F25996;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
                                <!-- Store Icon -->
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V10C19 8.89543 18.1046 8 17 8H7C5.89543 8 5 8.89543 5 10V21M3 21H21M9 21V16H15V21M6 8L7.5 4H16.5L18 8M6 8H18"></path></svg>
                                <!-- Verified Shield Overlap -->
                                <div class="absolute -bottom-1 -right-1 rounded-full p-1 border-2 border-white" style="background-color: #F25996;">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upload Section -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-1">
                                <label class="flex items-center text-sm font-bold text-gray-700">
                                    <svg class="w-4 h-4 mr-2" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Upload Shop Photos <span class="text-red-500 ml-1">*</span>
                                </label>
                                <span id="shop_photos_counter_badge" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-pink-100 text-[#F25996] border border-pink-200">
                                    0 / 4 Uploaded
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-500 font-medium mb-3 ml-6">Please upload 2 to 4 photos (inside & outside views, display, and stock areas).</p>
                            
                            <div id="dropzone_container" class="border-2 border-dashed border-gray-300 rounded-2xl p-6 md:p-8 text-center bg-gray-50/50 hover:bg-[#FDBFDD]/20 transition-colors relative group">
                                <input id="shop_photos" name="shop_photos[]" type="file" multiple accept="image/jpeg, image/png, image/webp" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)" onclick="this.value=null;">
                                
                                <div id="upload_icon" class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300" style="background-color: rgba(253, 191, 221, 0.5);">
                                    <svg class="w-6 h-6" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                </div>
                                <h4 id="upload_text" class="text-gray-900 font-bold mb-1 text-base">Click or drag & drop to upload (Max 4 Photos)</h4>
                                <p id="upload_subtext" class="text-xs text-gray-500 font-medium mb-5">JPG, PNG or WEBP (Max size 5MB each)</p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 relative z-0">
                                    <div class="bg-white border border-gray-200 rounded-xl py-2 px-2.5 flex items-center text-[11px] font-semibold text-gray-700 shadow-2xs">
                                        <div class="w-6 h-6 rounded flex items-center justify-center mr-2 shrink-0 bg-pink-100 text-[#F25996] font-bold text-xs">1</div>
                                        <span class="truncate">Photo 1: Shop Front</span>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-xl py-2 px-2.5 flex items-center text-[11px] font-semibold text-gray-700 shadow-2xs">
                                        <div class="w-6 h-6 rounded flex items-center justify-center mr-2 shrink-0 bg-pink-100 text-[#F25996] font-bold text-xs">2</div>
                                        <span class="truncate">Photo 2: Inside View</span>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-xl py-2 px-2.5 flex items-center text-[11px] font-semibold text-gray-700 shadow-2xs">
                                        <div class="w-6 h-6 rounded flex items-center justify-center mr-2 shrink-0 bg-pink-100 text-[#F25996] font-bold text-xs">3</div>
                                        <span class="truncate">Photo 3: Stock / Racks</span>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-xl py-2 px-2.5 flex items-center text-[11px] font-semibold text-gray-700 shadow-2xs">
                                        <div class="w-6 h-6 rounded flex items-center justify-center mr-2 shrink-0 bg-pink-100 text-[#F25996] font-bold text-xs">4</div>
                                        <span class="truncate">Photo 4: Sign / Counter</span>
                                    </div>
                                </div>
                            </div>
                            <div id="shop_photos-error" class="hidden mt-2 ml-1 text-xs text-red-500 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="shop_photos-error-text">Please upload at least 2 photos (inside and outside views).</span>
                            </div>
                            
                            <!-- Dynamic 4-Slot Image Previews Container with Remove Actions -->
                            <div id="image_previews" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 empty:hidden"></div>
                        </div>

                        <hr class="border-gray-100 mb-6">

                        <!-- Instagram Link -->
                        <div class="mb-2">
                            <label class="flex items-center text-sm font-bold text-gray-700 mb-1">
                                <!-- Instagram colorful icon -->
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="url(#instagramGradient)"><defs><linearGradient id="instagramGradient" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" stop-color="#f09433" /><stop offset="25%" stop-color="#e6683c" /><stop offset="50%" stop-color="#dc2743" /><stop offset="75%" stop-color="#cc2366" /><stop offset="100%" stop-color="#bc1888" /></linearGradient></defs><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                Instagram Shop Link <span class="text-red-500 ml-1">*</span>
                            </label>
                            <p class="text-sm text-gray-500 font-medium mb-3 ml-7">Share your Instagram shop profile link</p>
                            
                            <div class="relative ml-7">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                </div>
                                <input id="instagram_link" name="instagram_link" type="url" required pattern="^https?:\/\/(www\.)?instagram\.com\/.*$" title="Please provide a valid Instagram link. Facebook links are not accepted." placeholder="https://www.instagram.com/yourshop" autocomplete="off" class="form-input pl-11 pr-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-[#F25996] focus:border-[#F25996] text-sm bg-white font-medium text-gray-900">
                            </div>
                            <div id="instagram-error" class="hidden mt-1.5 ml-7 text-xs text-red-500 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="instagram-error-text">Please provide a valid Instagram link. Facebook links are not accepted.</span>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Action Bar -->
                    <div class="p-4 sm:px-8 border-t flex flex-col sm:flex-row items-center justify-between rounded-b-2xl shrink-0" style="background-color: rgba(253, 191, 221, 0.25); border-color: #FDBFDD;">
                        <button type="button" onclick="prevStep(3)" class="w-full sm:w-auto font-bold py-3 px-8 rounded-xl flex items-center justify-center transition-all shadow-sm text-sm border-2 border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300 mb-4 sm:mb-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Back
                        </button>
                        <button type="button" onclick="submitFinalForm()" class="w-full sm:w-auto text-white font-bold py-3 px-8 rounded-xl flex items-center justify-center transition-all shadow-lg text-sm" style="background: #F25996;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Submit <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const eye = document.getElementById('eye-icon');
    const eyeOff = document.getElementById('eye-off-icon');
    
    if (pwd.type === 'password') {
        pwd.type = 'text';
        eye.classList.add('hidden');
        eyeOff.classList.remove('hidden');
    } else {
        pwd.type = 'password';
        eye.classList.remove('hidden');
        eyeOff.classList.add('hidden');
    }
}

// ── Comprehensive Inline Validation Engine ──
function getFieldAndError(fieldId) {
    let field = document.getElementById(fieldId);
    let errorBox = document.getElementById(fieldId + '-error');

    if (!field) {
        if (fieldId === 'business') field = document.getElementById('business_name');
        else if (fieldId === 'instagram') field = document.getElementById('instagram_link');
    }
    if (!errorBox) {
        if (fieldId === 'business_name') errorBox = document.getElementById('business-error');
        else if (fieldId === 'instagram_link') errorBox = document.getElementById('instagram-error');
    }
    return { field, errorBox, fieldId };
}

function setFieldError(fieldId, msg) {
    const { field, errorBox } = getFieldAndError(fieldId);
    const errorText = errorBox ? (document.getElementById(fieldId + '-error-text') || errorBox.querySelector('span')) : null;

    if (errorBox) {
        errorBox.classList.remove('hidden');
        if (errorText) errorText.innerText = msg;
    }

    if (field) {
        if (field.id === 'phone' || fieldId === 'phone') {
            const wrap = document.getElementById('phone-wrapper');
            if (wrap) wrap.classList.add('border-red-500', 'ring-1', 'ring-red-500');
        } else if (field.tagName === 'SELECT') {
            const csTrigger = field.parentElement ? field.parentElement.querySelector('.reg-cs-trigger') : null;
            if (csTrigger) csTrigger.classList.add('error');
            field.classList.add('border-red-500', 'ring-1', 'ring-red-500');
        } else if (field.id === 'shop_photos' || fieldId === 'shop_photos') {
            const dropzone = document.getElementById('dropzone_container');
            if (dropzone) {
                dropzone.classList.add('border-red-500', 'bg-red-50/30');
                dropzone.classList.remove('border-gray-300');
            }
        } else {
            field.classList.add('border-red-500', 'ring-1', 'ring-red-500');
            field.classList.remove('border-gray-200');
        }
    }
}

function clearFieldError(fieldId) {
    const { field, errorBox } = getFieldAndError(fieldId);

    if (errorBox) {
        errorBox.classList.add('hidden');
    }

    if (field) {
        if (field.id === 'phone' || fieldId === 'phone') {
            const wrap = document.getElementById('phone-wrapper');
            if (wrap) wrap.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        } else if (field.tagName === 'SELECT') {
            const csTrigger = field.parentElement ? field.parentElement.querySelector('.reg-cs-trigger') : null;
            if (csTrigger) csTrigger.classList.remove('error');
            field.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        } else if (field.id === 'shop_photos' || fieldId === 'shop_photos') {
            const dropzone = document.getElementById('dropzone_container');
            if (dropzone) {
                dropzone.classList.remove('border-red-500', 'bg-red-50/30');
                dropzone.classList.add('border-gray-300');
            }
        } else {
            field.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            field.classList.add('border-gray-200');
        }
    }
}

function validateSingleField(fieldId) {
    if (fieldId === 'name') {
        const val = document.getElementById('name').value.trim();
        if (!val) {
            setFieldError('name', 'Full name is required.');
            return false;
        }
        if (val.length < 2) {
            setFieldError('name', 'Name must be at least 2 characters.');
            return false;
        }
        if (/[0-9]/.test(val)) {
            setFieldError('name', 'Name cannot contain numbers.');
            return false;
        }
        clearFieldError('name');
        return true;
    }

    if (fieldId === 'phone') {
        const val = document.getElementById('phone').value.trim();
        if (!val) {
            setFieldError('phone', 'Phone number is required.');
            return false;
        }
        if (!/^[0-9]{10}$/.test(val)) {
            setFieldError('phone', 'Please enter a valid 10-digit phone number.');
            return false;
        }
        if (_dbDuplicateErrors['phone']) {
            setFieldError('phone', _dbDuplicateErrors['phone']);
            return false;
        }
        clearFieldError('phone');
        return true;
    }

    if (fieldId === 'email') {
        const val = document.getElementById('email').value.trim();
        if (!val) {
            setFieldError('email', 'Email address is required.');
            return false;
        }
        const emailRegex = /^[\w._%+-]+@[\w.-]+\.(com|in|co\.in|net|org|edu|gov|io|biz|info)$/i;
        if (!emailRegex.test(val)) {
            setFieldError('email', 'Please enter a valid email address (e.g. name@example.com).');
            return false;
        }
        if (_dbDuplicateErrors['email']) {
            setFieldError('email', _dbDuplicateErrors['email']);
            return false;
        }
        clearFieldError('email');
        return true;
    }

    if (fieldId === 'password') {
        const val = document.getElementById('password').value;
        if (!val) {
            setFieldError('password', 'Password is required.');
            return false;
        }
        const valid = val.length >= 8 && /[A-Z]/.test(val) && /[a-z]/.test(val) && /[0-9]/.test(val);
        if (!valid) {
            setFieldError('password', 'Password must satisfy all requirement rules.');
            return false;
        }
        clearFieldError('password');
        return true;
    }

    if (fieldId === 'business_name') {
        const val = document.getElementById('business_name').value.trim();
        if (!val) {
            setFieldError('business', 'Business name is required.');
            return false;
        }
        if (val.length < 4) {
            setFieldError('business', 'Business name must be at least 4 characters.');
            return false;
        }
        if (_dbDuplicateErrors['business']) {
            setFieldError('business', _dbDuplicateErrors['business']);
            return false;
        }
        clearFieldError('business');
        return true;
    }

    if (fieldId === 'shop_location') {
        const val = document.getElementById('shop_location').value.trim();
        if (!val) {
            setFieldError('shop_location', 'Shop location / street address is required.');
            return false;
        }
        if (val.length < 3) {
            setFieldError('shop_location', 'Address must be at least 3 characters.');
            return false;
        }
        clearFieldError('shop_location');
        return true;
    }

    if (fieldId === 'state') {
        const val = document.getElementById('state').value;
        if (!val) {
            setFieldError('state', 'Please select your state.');
            return false;
        }
        clearFieldError('state');
        return true;
    }

    if (fieldId === 'district') {
        const stateVal = document.getElementById('state').value;
        if (stateVal === 'Tamil Nadu') {
            const val = document.getElementById('district_select').value;
            if (!val) {
                setFieldError('district', 'Please select a district.');
                return false;
            }
            clearFieldError('district');
            return true;
        } else if (stateVal === 'Others') {
            const val = document.getElementById('district_text').value.trim();
            if (!val) {
                setFieldError('district', 'Please enter your district name.');
                return false;
            }
            const tnDistricts = ["ariyalur", "chengalpattu", "chennai", "coimbatore", "cuddalore", "dharmapuri", "dindigul", "erode", "kallakurichi", "kanchipuram", "kanyakumari", "karur", "krishnagiri", "madurai", "mayiladuthurai", "nagapattinam", "namakkal", "nilgiris", "perambalur", "pudukkottai", "ramanathapuram", "ranipet", "salem", "sivaganga", "tenkasi", "thanjavur", "theni", "thoothukudi", "tiruchirappalli", "trichy", "tirunelveli", "tirupathur", "tiruppur", "tiruvallur", "tiruvannamalai", "tiruvarur", "vellore", "viluppuram", "virudhunagar"];
            if (tnDistricts.includes(val.toLowerCase())) {
                setFieldError('district', 'Please select Tamil Nadu as state for this district.');
                return false;
            }
            clearFieldError('district');
            return true;
        }
        return true;
    }

    if (fieldId === 'other_state_name') {
        const stateVal = document.getElementById('state').value;
        if (stateVal === 'Others') {
            const val = document.getElementById('other_state_name').value.trim();
            if (!val) {
                setFieldError('other_state_name', 'Please enter your state name.');
                return false;
            }
            if (['tamil nadu', 'tamilnadu', 'tn'].includes(val.toLowerCase())) {
                setFieldError('other_state_name', 'Please select Tamil Nadu from the state dropdown.');
                return false;
            }
            clearFieldError('other_state_name');
            return true;
        }
        return true;
    }

    if (fieldId === 'pincode') {
        const val = document.getElementById('pincode').value.trim();
        if (!val) {
            setFieldError('pincode', 'Pincode is required.');
            return false;
        }
        if (!/^[0-9]{6}$/.test(val)) {
            setFieldError('pincode', 'Please enter a valid 6-digit postal pincode.');
            return false;
        }
        clearFieldError('pincode');
        return true;
    }

    if (fieldId === 'gst_number') {
        const hasGst = document.getElementById('has_gst') ? document.getElementById('has_gst').checked : true;
        if (hasGst) {
            const val = document.getElementById('gst_number').value.trim().toUpperCase();
            if (!val) {
                setFieldError('gst_number', 'GST number is required.');
                return false;
            }
            if (val.length < 15) {
                setFieldError('gst_number', `GST number must be 15 characters with letters and numbers (${val.length}/15 entered).`);
                return false;
            }
            if (val.length > 15) {
                setFieldError('gst_number', 'GST number cannot exceed 15 characters.');
                return false;
            }
            // Must contain a mix of letters and numbers (cannot be all numbers or all letters)
            if (!/[A-Z]/.test(val) || !/[0-9]/.test(val)) {
                setFieldError('gst_number', 'GST number must contain a mix of letters and numbers (cannot be only numbers or letters). Example: 33AAAAA0000A1Z5');
                return false;
            }
            // Standard Indian GSTIN pattern: 2 digits state + 5 letters PAN + 4 digits PAN + 1 letter PAN + 1 entity + 'Z' + 1 check digit
            const gstPattern = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if (!gstPattern.test(val)) {
                setFieldError('gst_number', 'Invalid GSTIN format (e.g. 33AAAAA0000A1Z5). Must follow state code, PAN, entity, Z and check digit.');
                return false;
            }
            if (_dbDuplicateErrors['gst_number']) {
                setFieldError('gst_number', _dbDuplicateErrors['gst_number']);
                return false;
            }
            clearFieldError('gst_number');
            return true;
        }
        return true;
    }

    if (fieldId === 'no_gst_reason') {
        const hasGst = document.getElementById('has_gst').checked;
        if (!hasGst) {
            const val = document.getElementById('no_gst_reason').value;
            if (!val) {
                setFieldError('no_gst_reason', 'Please select a reason for not having GST.');
                return false;
            }
            clearFieldError('no_gst_reason');
            return true;
        }
        return true;
    }

    if (fieldId === 'other_gst_reason') {
        const hasGst = document.getElementById('has_gst').checked;
        const reason = document.getElementById('no_gst_reason').value;
        if (!hasGst && reason === 'Other') {
            const val = document.getElementById('other_gst_reason').value.trim();
            if (!val || val.length < 5) {
                setFieldError('other_gst_reason', 'Please provide an explanation (min 5 characters).');
                return false;
            }
            clearFieldError('other_gst_reason');
            return true;
        }
        return true;
    }

    if (fieldId === 'pan_number') {
        const hasGst = document.getElementById('has_gst').checked;
        if (!hasGst) {
            const val = document.getElementById('pan_number').value.trim().toUpperCase();
            if (!val) {
                setFieldError('pan_number', 'PAN number is required for Non-GST verification.');
                return false;
            }
            if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(val)) {
                setFieldError('pan_number', 'Please enter a valid 10-character PAN (e.g. ABCDE1234F).');
                return false;
            }
            if (_dbDuplicateErrors['pan_number']) {
                setFieldError('pan_number', _dbDuplicateErrors['pan_number']);
                return false;
            }
            clearFieldError('pan_number');
            return true;
        }
        return true;
    }

    if (fieldId === 'shop_photos') {
        if (selectedFiles.length < 2) {
            setFieldError('shop_photos', 'Please upload at least 2 shop photos (inside and outside views).');
            return false;
        }
        clearFieldError('shop_photos');
        return true;
    }

    if (fieldId === 'instagram_link' || fieldId === 'instagram') {
        const val = document.getElementById('instagram_link').value.trim();
        if (!val) {
            setFieldError('instagram', 'Instagram shop profile link is required.');
            return false;
        }
        const instaRegex = /^https?:\/\/(www\.)?instagram\.com\/[A-Za-z0-9_.\-\/]+(\?.*)?$/i;
        if (!instaRegex.test(val)) {
            setFieldError('instagram', 'Please enter a valid Instagram profile link (e.g. https://www.instagram.com/yourshop).');
            return false;
        }
        if (_dbDuplicateErrors['instagram']) {
            setFieldError('instagram', _dbDuplicateErrors['instagram']);
            return false;
        }
        clearFieldError('instagram');
        return true;
    }

    return true;
}

// Password Requirement Checklist Indicator (friendly live guide, no error borders until button click)
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    const feedback = document.getElementById('password-feedback');
    
    if (val.length > 0) {
        feedback.classList.remove('hidden');
    } else {
        feedback.classList.add('hidden');
    }

    const checks = {
        'req-length': val.length >= 8,
        'req-upper': /[A-Z]/.test(val),
        'req-lower': /[a-z]/.test(val),
        'req-number': /[0-9]/.test(val)
    };

    let allValid = true;
    const checkIcon = '<svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    const crossIcon = '<svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

    for (const [id, isValid] of Object.entries(checks)) {
        const el = document.getElementById(id);
        if (!el) continue;
        const span = el.querySelector('span');
        const textSpan = span ? span.innerText : '';
        if (isValid) {
            el.className = 'flex items-center text-green-600 transition-colors duration-300';
            el.innerHTML = checkIcon + '<span>' + textSpan + '</span>';
        } else {
            allValid = false;
            el.className = 'flex items-center text-gray-400 transition-colors duration-300';
            el.innerHTML = crossIcon + '<span>' + textSpan + '</span>';
        }
    }

    if (allValid) {
        clearFieldError('password');
    }
});

// ── Real-time inline validation & Database duplicate checking ────────────────
const _touched = {};
const _debounceTimers = {};
const _asyncCheckTimers = {};
const _dbDuplicateErrors = {
    email: null,
    phone: null,
    business: null,
    gst_number: null,
    pan_number: null,
    instagram: null
};

async function checkFieldDbDuplicate(fieldId) {
    let payload = {};
    if (fieldId === 'email') {
        const val = document.getElementById('email').value.trim();
        const emailRegex = /^[\w._%+-]+@[\w.-]+\.(com|in|co\.in|net|org|edu|gov|io|biz|info)$/i;
        if (!val || !emailRegex.test(val)) return;
        payload = { email: val };
    } else if (fieldId === 'phone') {
        const val = document.getElementById('phone').value.trim();
        if (!val || !/^[0-9]{10}$/.test(val)) return;
        payload = { phone: val };
    } else if (fieldId === 'business_name') {
        const val = document.getElementById('business_name').value.trim();
        if (!val || val.length < 4) return;
        payload = { business_name: val };
    } else if (fieldId === 'gst_number') {
        const val = document.getElementById('gst_number').value.trim().toUpperCase();
        if (!val || val.length !== 15) return;
        payload = { gst_number: val };
    } else if (fieldId === 'pan_number') {
        const val = document.getElementById('pan_number').value.trim().toUpperCase();
        if (!val || val.length !== 10) return;
        payload = { pan_number: val };
    } else if (fieldId === 'instagram_link' || fieldId === 'instagram') {
        const val = document.getElementById('instagram_link').value.trim();
        const instaRegex = /^https?:\/\/(www\.)?instagram\.com\/[A-Za-z0-9_.\-\/]+(\?.*)?$/i;
        if (!val || !instaRegex.test(val)) return;
        payload = { instagram_link: val };
    } else {
        return;
    }

    let targetErrKey = fieldId;
    if (fieldId === 'business_name') targetErrKey = 'business';
    if (fieldId === 'instagram_link') targetErrKey = 'instagram';

    try {
        const response = await fetch('<?= BASE_URL ?>/api/check-user-exists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        
        if (data.exists && (data.field === targetErrKey || data.field === fieldId)) {
            _dbDuplicateErrors[targetErrKey] = data.message;
            setFieldError(targetErrKey, data.message);
        } else {
            if (_dbDuplicateErrors[targetErrKey]) {
                _dbDuplicateErrors[targetErrKey] = null;
                clearFieldError(targetErrKey);
            }
        }
    } catch (e) {
        console.error("Async duplicate check error:", e);
    }
}

function _liveValidate(id, fieldIdForValidate) {
    clearTimeout(_debounceTimers[id]);
    _debounceTimers[id] = setTimeout(() => {
        const el = document.getElementById(id);
        if (!el) return;
        const hasValue = el.value.trim().length > 0;
        if (!_touched[id] && !hasValue) {
            // Not yet touched and still empty — stay silent
            return;
        }
        if (hasValue) _touched[id] = true;

        let key = id;
        if (id === 'business_name') key = 'business';
        if (id === 'instagram_link') key = 'instagram';
        if (_dbDuplicateErrors.hasOwnProperty(key)) {
            _dbDuplicateErrors[key] = null;
        }

        const isValid = validateSingleField(fieldIdForValidate || id);

        if (isValid && ['email', 'phone', 'business_name', 'gst_number', 'pan_number', 'instagram_link'].includes(id)) {
            clearTimeout(_asyncCheckTimers[id]);
            _asyncCheckTimers[id] = setTimeout(() => {
                checkFieldDbDuplicate(id);
            }, 300);
        }
    }, 300);
}

// Text / number inputs — live validation
[
    { id: 'name',             validate: 'name' },
    { id: 'phone',            validate: 'phone' },
    { id: 'email',            validate: 'email' },
    { id: 'shop_location',    validate: 'shop_location' },
    { id: 'pincode',          validate: 'pincode' },
    { id: 'gst_number',       validate: 'gst_number' },
    { id: 'other_gst_reason', validate: 'other_gst_reason' },
    { id: 'pan_number',       validate: 'pan_number' },
    { id: 'other_state_name', validate: 'other_state_name' },
    { id: 'district_text',    validate: 'district' },
    { id: 'instagram_link',   validate: 'instagram' },
    { id: 'business_name',    validate: 'business_name' }
].forEach(({ id, validate }) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', () => _liveValidate(id, validate));
});

// Immediate validation and DB duplicate check on blur
['email', 'phone', 'business_name', 'gst_number', 'pan_number', 'instagram_link'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('blur', () => {
        const val = el.value.trim();
        if (val) {
            _touched[id] = true;
            let validateKey = id;
            if (id === 'business_name') validateKey = 'business_name';
            if (id === 'instagram_link') validateKey = 'instagram';
            const valid = validateSingleField(validateKey);
            if (valid) {
                checkFieldDbDuplicate(id);
            }
        }
    });
});

// Select / dropdown inputs — validate immediately on change (no debounce needed)
[
    { id: 'state',          validate: 'state' },
    { id: 'district_select', validate: 'district' },
    { id: 'no_gst_reason',  validate: 'no_gst_reason' }
].forEach(({ id, validate }) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('change', () => {
        _touched[id] = true;
        validateSingleField(validate);
    });
});

let selectedFiles = [];
const MAX_SHOP_PHOTOS = 4;
const SHOP_SLOT_LABELS = [
    'Photo 1: Shop Front / Outside',
    'Photo 2: Inside Shop / Space',
    'Photo 3: Stock / Racks',
    'Photo 4: Counter / Signboard'
];

function updateFileName(input) {
    if (input.files) {
        let addedCount = 0;
        let excessCount = 0;
        Array.from(input.files).forEach(file => {
            if (selectedFiles.length < MAX_SHOP_PHOTOS) {
                // Check if file already added
                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedFiles.push(file);
                    addedCount++;
                }
            } else {
                excessCount++;
            }
        });

        if (excessCount > 0) {
            Swal.fire({
                title: 'Photo Limit Reached',
                text: `You can upload a maximum of 4 shop photos. The first 4 selected photos have been kept.`,
                icon: 'warning',
                confirmButtonColor: '#F25996'
            });
        }
    }
    
    syncInputFiles();
    renderPreviews();
    updateDropzoneUI();
    validateSingleField('shop_photos');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    syncInputFiles();
    renderPreviews();
    updateDropzoneUI();
    validateSingleField('shop_photos');
}

function syncInputFiles() {
    const input = document.getElementById('shop_photos');
    if (!input) return;
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
}

function renderPreviews() {
    const previewContainer = document.getElementById('image_previews');
    const badgeCounter = document.getElementById('shop_photos_counter_badge');
    if (!previewContainer) return;

    previewContainer.innerHTML = '';
    
    if (badgeCounter) {
        badgeCounter.textContent = `${selectedFiles.length} / 4 Uploaded`;
        if (selectedFiles.length >= 2 && selectedFiles.length <= 4) {
            badgeCounter.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200';
        } else if (selectedFiles.length > 4) {
            badgeCounter.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-200';
        } else {
            badgeCounter.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-pink-100 text-[#F25996] border border-pink-200';
        }
    }
    
    selectedFiles.forEach((file, index) => {
        const url = URL.createObjectURL(file);
        const slotName = SHOP_SLOT_LABELS[index] || `Photo ${index + 1}`;
        const sizeKb = Math.round(file.size / 1024);
        const sizeFormatted = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';

        const div = document.createElement('div');
        div.className = 'relative group rounded-2xl overflow-hidden border-2 border-pink-200 bg-white shadow-sm flex flex-col transition-all hover:shadow-md';
        div.innerHTML = `
            <div class="relative w-full aspect-square bg-gray-100 overflow-hidden">
                <img src="${url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/70 text-white font-bold text-[10px] backdrop-blur-xs">
                    #${index + 1}
                </div>
                <button type="button" onclick="removeFile(${index})" title="Remove Photo" class="absolute top-2 right-2 bg-white/95 text-red-500 hover:bg-red-500 hover:text-white rounded-full p-1.5 shadow-md transition-colors z-20 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-2.5 bg-white border-t border-gray-100 flex flex-col">
                <span class="text-[11px] font-bold text-gray-900 truncate" title="${slotName}">${slotName}</span>
                <span class="text-[10px] text-gray-400 font-medium">${sizeFormatted}</span>
            </div>
        `;
        previewContainer.appendChild(div);
    });
}

function updateDropzoneUI() {
    const textElement = document.getElementById('upload_text');
    const subtextElement = document.getElementById('upload_subtext');
    const iconElement = document.getElementById('upload_icon');
    
    if (selectedFiles.length > 0) {
        textElement.textContent = `${selectedFiles.length} of 4 photo(s) selected`;
        textElement.style.color = '#F25996';
        subtextElement.textContent = selectedFiles.length >= 2 ? "Ready to upload (Click or drag to add more)" : "Please add at least 1 more photo";
        iconElement.innerHTML = '<svg class="w-6 h-6" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        iconElement.style.backgroundColor = 'rgba(253, 191, 221, 0.5)';
    } else {
        textElement.textContent = "Click or drag & drop to upload (Max 4 Photos)";
        textElement.style.color = '';
        subtextElement.textContent = "JPG, PNG or WEBP (Max size 5MB each)";
        iconElement.innerHTML = '<svg class="w-6 h-6" style="color: #F25996;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>';
        iconElement.style.backgroundColor = 'rgba(253, 191, 221, 0.5)';
    }
}

// Document upload handler — validates 3MB limit and shows filename
function handleDocUpload(input, nameId, labelId, errorId, errorTextId) {
    const MAX_BYTES = 3 * 1024 * 1024; // 3MB
    const errDiv  = document.getElementById(errorId);
    const errSpan = document.getElementById(errorTextId);
    const nameEl  = document.getElementById(nameId);
    const label   = document.getElementById(labelId);

    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > MAX_BYTES) {
        errDiv.classList.remove('hidden');
        errSpan.textContent = 'File is too large. Maximum allowed size is 3MB.';
        label.classList.add('border-red-400');
        label.classList.remove('border-[#F25996]');
        nameEl.textContent = 'Click to choose a file';
        input.value = '';
        return;
    }

    errDiv.classList.add('hidden');
    label.classList.remove('border-red-400');
    label.classList.add('border-[#F25996]', 'bg-pink-50/40');
    nameEl.textContent = '✓ ' + file.name;
    nameEl.style.color = '#F25996';
}

function toggleGstFields() {
    const hasGst = document.getElementById('has_gst').checked;
    const gstSection = document.getElementById('gst_section');
    const nonGstSection = document.getElementById('non_gst_section');
    const gstInput = document.getElementById('gst_number');
    const panInput = document.getElementById('pan_number');
    const noGstReason = document.getElementById('no_gst_reason');

    if (hasGst) {
        gstSection.classList.remove('hidden');
        nonGstSection.classList.add('hidden');
        gstInput.required = true;
        panInput.required = false;
        if (noGstReason) noGstReason.required = false;
        clearFieldError('no_gst_reason');
        clearFieldError('other_gst_reason');
        clearFieldError('pan_number');
    } else {
        gstSection.classList.add('hidden');
        nonGstSection.classList.remove('hidden');
        gstInput.required = false;
        panInput.required = true;
        if (noGstReason) noGstReason.required = true;
        clearFieldError('gst_number');
    }
}

function toggleOtherGstReason() {
    const noGstReason = document.getElementById('no_gst_reason');
    const otherReasonContainer = document.getElementById('other_gst_reason_container');
    const otherReasonInput = document.getElementById('other_gst_reason');
    
    if (noGstReason && noGstReason.value === 'Other') {
        otherReasonContainer.classList.remove('hidden');
        otherReasonInput.required = true;
    } else {
        otherReasonContainer.classList.add('hidden');
        otherReasonInput.required = false;
        if (otherReasonInput) otherReasonInput.value = '';
        clearFieldError('other_gst_reason');
    }
}

function toggleDistrictFields() {
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district_select');
    const districtText = document.getElementById('district_text');
    const otherStateContainer = document.getElementById('other_state_container');
    const otherStateName = document.getElementById('other_state_name');
    
    if (stateSelect.value === 'Tamil Nadu') {
        districtSelect.classList.remove('hidden');
        districtSelect.required = true;
        districtSelect.disabled = false;
        
        districtText.classList.add('hidden');
        districtText.required = false;
        districtText.disabled = true;

        otherStateContainer.classList.add('hidden');
        otherStateName.required = false;
        otherStateName.disabled = true;
        clearFieldError('other_state_name');
    } else if (stateSelect.value === 'Others') {
        districtText.classList.remove('hidden');
        districtText.required = true;
        districtText.disabled = false;
        
        districtSelect.classList.add('hidden');
        districtSelect.required = false;
        districtSelect.disabled = true;

        otherStateContainer.classList.remove('hidden');
        otherStateName.required = true;
        otherStateName.disabled = false;
    } else {
        // Nothing selected
        districtSelect.classList.add('hidden');
        districtSelect.required = false;
        districtSelect.disabled = true;
        
        districtText.classList.add('hidden');
        districtText.required = false;
        districtText.disabled = true;

        otherStateContainer.classList.add('hidden');
        otherStateName.required = false;
        otherStateName.disabled = true;
        clearFieldError('other_state_name');
        clearFieldError('district');
    }
}

function validateStep(step) {
    let isValid = true;
    let firstInvalidField = null;

    if (step === 1) {
        const fields = ['name', 'phone', 'email', 'password'];
        fields.forEach(f => {
            const valid = validateSingleField(f);
            if (!valid && !firstInvalidField) {
                firstInvalidField = document.getElementById(f);
            }
            if (!valid) isValid = false;
        });
    } else if (step === 2) {
        const hasGst = document.getElementById('has_gst').checked;
        const stateVal = document.getElementById('state').value;
        const reasonVal = document.getElementById('no_gst_reason').value;

        const fields = ['business_name', 'shop_location', 'state', 'district'];
        if (stateVal === 'Others') fields.push('other_state_name');
        fields.push('pincode');
        if (hasGst) {
            fields.push('gst_number');
        } else {
            fields.push('no_gst_reason');
            if (reasonVal === 'Other') fields.push('other_gst_reason');
            fields.push('pan_number');
        }

        fields.forEach(f => {
            const valid = validateSingleField(f);
            if (!valid && !firstInvalidField) {
                firstInvalidField = document.getElementById(f) || (f === 'district' ? (stateVal === 'Tamil Nadu' ? document.getElementById('district_select') : document.getElementById('district_text')) : null);
            }
            if (!valid) isValid = false;
        });
    } else if (step === 3) {
        const fields = ['shop_photos', 'instagram_link'];
        fields.forEach(f => {
            const valid = validateSingleField(f);
            if (!valid && !firstInvalidField) {
                firstInvalidField = f === 'shop_photos' ? document.getElementById('dropzone_container') : document.getElementById(f);
            }
            if (!valid) isValid = false;
        });
    }

    if (!isValid && firstInvalidField) {
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (firstInvalidField.focus) firstInvalidField.focus();
    }

    return isValid;
}

async function nextStep(currentStep) {
    if (!validateStep(currentStep)) {
        return;
    }

    if (currentStep === 1) {
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();

        try {
            const response = await fetch('<?= BASE_URL ?>/api/check-user-exists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, phone })
            });

            const data = await response.json();
            
            if (data.exists) {
                const targetField = data.field || 'email';
                _dbDuplicateErrors[targetField] = data.message;
                setFieldError(targetField, data.message);
                const targetEl = document.getElementById(targetField);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (targetEl.focus) targetEl.focus();
                }
                return; // Stop here, do not advance
            }
        } catch (error) {
            console.error("Error checking user:", error);
            setFieldError('email', "Error checking details. Please try again.");
            return;
        }
    }
    
    if (currentStep === 2) {
        const businessName = document.getElementById('business_name').value.trim();
        const hasGst = document.getElementById('has_gst').checked;

        // Validate document uploads
        if (hasGst) {
            const gstDoc = document.getElementById('gst_document');
            if (!gstDoc || !gstDoc.files || !gstDoc.files[0]) {
                const errDiv  = document.getElementById('gst_document-error');
                const errSpan = document.getElementById('gst_document-error-text');
                if (errDiv) { errDiv.classList.remove('hidden'); errSpan.textContent = 'Please upload your GST document.'; }
                gstDoc && gstDoc.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        } else {
            const panDoc = document.getElementById('pan_document');
            if (!panDoc || !panDoc.files || !panDoc.files[0]) {
                const errDiv  = document.getElementById('pan_document-error');
                const errSpan = document.getElementById('pan_document-error-text');
                if (errDiv) { errDiv.classList.remove('hidden'); errSpan.textContent = 'Please upload your PAN card document.'; }
                panDoc && panDoc.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        const checkPayload = { business_name: businessName };
        if (hasGst) {
            const gstVal = document.getElementById('gst_number').value.trim().toUpperCase();
            if (gstVal) checkPayload.gst_number = gstVal;
        } else {
            const panVal = document.getElementById('pan_number').value.trim().toUpperCase();
            if (panVal) checkPayload.pan_number = panVal;
        }

        try {
            const response = await fetch('<?= BASE_URL ?>/api/check-user-exists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(checkPayload)
            });

            const data = await response.json();
            
            if (data.exists) {
                const targetField = data.field === 'business' ? 'business' : data.field;
                _dbDuplicateErrors[targetField] = data.message;
                setFieldError(targetField, data.message);
                const targetEl = document.getElementById(targetField === 'business' ? 'business_name' : targetField);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (targetEl.focus) targetEl.focus();
                }
                return; // Stop here, do not advance
            } else {
                _dbDuplicateErrors['business'] = null;
                _dbDuplicateErrors['gst_number'] = null;
                _dbDuplicateErrors['pan_number'] = null;
                clearFieldError('business');
                clearFieldError('gst_number');
                clearFieldError('pan_number');
            }
        } catch (error) {
            console.error("Error checking step 2 duplicates:", error);
        }
    }
    
    goToStep(currentStep + 1, true);
}

function prevStep(currentStep) {
    goToStep(currentStep - 1, true);
}

    let currentStepIndex = 1;

    function goToStep(step, scroll = true) {
        if (step < 1 || step > 3) step = 1;
        currentStepIndex = step;

        // Hide all steps, show target step
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        const targetEl = document.getElementById('step-' + step);
        if (targetEl) targetEl.classList.remove('hidden');

        updateProgress(step);

        // Save active step in sessionStorage & browser URL hash
        try {
            sessionStorage.setItem('grm_user_reg_step', step.toString());
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', '#step-' + step);
            }
        } catch(e) {}

        if (scroll) {
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }
    }

    function updateProgress(step) {
        const line = document.getElementById('progress-line');
        if (step === 1) line.style.width = '0%';
        if (step === 2) line.style.width = '50%';
        if (step === 3) line.style.width = '100%';

        // Reset all icons to inactive state first
        for(let i=1; i<=3; i++) {
            const icon = document.getElementById('step-' + i + '-icon');
            const text = document.getElementById('step-' + i + '-text');
            icon.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors duration-300 bg-gray-100 border-2 border-gray-200 text-gray-400';
            icon.style.backgroundColor = '';
            icon.style.color = '';
            text.className = 'text-xs font-bold text-gray-500';
            text.style.color = '';
        }

        // Apply active/completed state based on step
        for(let i=1; i<=step; i++) {
            const icon = document.getElementById('step-' + i + '-icon');
            const text = document.getElementById('step-' + i + '-text');
            icon.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors duration-300 text-white shadow-md border-0';
            icon.style.backgroundColor = '#F25996';
            text.className = 'text-xs font-bold';
            text.style.color = '#F25996';
        }
    }
    
    let _isSubmitting = false;

    async function submitFinalForm() {
        if (_isSubmitting) return;

        if (!validateStep(1)) {
            goToStep(1, true);
            return;
        }
        if (!validateStep(2)) {
            goToStep(2, true);
            return;
        }
        if (!validateStep(3)) {
            goToStep(3, true);
            return;
        }

        const instaVal = document.getElementById('instagram_link').value.trim();
        if (instaVal) {
            try {
                const response = await fetch('<?= BASE_URL ?>/api/check-user-exists', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ instagram_link: instaVal })
                });
                const data = await response.json();
                if (data.exists) {
                    _dbDuplicateErrors['instagram'] = data.message;
                    setFieldError('instagram', data.message);
                    const instaEl = document.getElementById('instagram_link');
                    if (instaEl) {
                        instaEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        if (instaEl.focus) instaEl.focus();
                    }
                    return;
                }
            } catch(err) {
                console.error("Error checking instagram duplicate:", err);
            }
        }

        try {
            sessionStorage.removeItem('grm_user_reg_step');
        } catch(e) {}

        _isSubmitting = true;
        document.getElementById('multi-step-form').submit();
    }

    // Prevent premature submit on Enter key and form submit event
    const userForm = document.getElementById('multi-step-form');
    if (userForm) {
        userForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                if (currentStepIndex === 1) {
                    nextStep(1);
                } else if (currentStepIndex === 2) {
                    nextStep(2);
                } else if (currentStepIndex === 3) {
                    submitFinalForm();
                }
                return false;
            }
        });

        userForm.addEventListener('submit', function(e) {
            if (!_isSubmitting) {
                e.preventDefault();
                if (currentStepIndex === 1) nextStep(1);
                else if (currentStepIndex === 2) nextStep(2);
                else if (currentStepIndex === 3) submitFinalForm();
                return false;
            }
        });
    }

    function resetRegistrationForm() {
        if (userForm) {
            userForm.reset();
        }
        _dbDuplicateErrors = {};
        sessionStorage.removeItem('grm_user_reg_step');
        if (window.location.hash) {
            try {
                history.replaceState(null, null, window.location.pathname);
            } catch (e) {}
        }
        
        // Hide all error messages
        document.querySelectorAll('[id$="-error"]').forEach(function(el) {
            el.classList.add('hidden');
        });
        
        // Clear image previews
        const previews = document.getElementById('image_previews');
        if (previews) previews.innerHTML = '';
        
        // Reset file upload label texts
        const gstLabel = document.getElementById('gst_doc_name');
        if (gstLabel) gstLabel.textContent = 'Click to upload GST certificate / registration document';
        const panLabel = document.getElementById('pan_doc_name');
        if (panLabel) panLabel.textContent = 'Click to upload PAN card document';
        const uploadText = document.getElementById('upload_text');
        if (uploadText) uploadText.textContent = 'Click or drag & drop to upload';

        toggleGstFields();
        toggleDistrictFields();
        toggleOtherGstReason();
        goToStep(1, false);
    }

    // Initialize on DOM ready & pageshow (handles refresh and browser navigation)
    document.addEventListener('DOMContentLoaded', resetRegistrationForm);
    window.addEventListener('pageshow', resetRegistrationForm);
</script>

<script src=https://cdn.jsdelivr.net/npm/sweetalert2@11></script>

<script>
/* ─────────────────────────────────────────────────
   Custom Pink Select for Buyer Register Page
   Scoped only to #state and #district_select
───────────────────────────────────────────────── */
function initRegCustomSelect(sel) {
    if (!sel || sel.dataset.regCsInit) return;
    sel.dataset.regCsInit = '1';

    sel.style.display = 'none';

    var wrapper = document.createElement('div');
    wrapper.className = 'reg-cs-wrapper';
    if (sel.classList.contains('hidden')) wrapper.classList.add('hidden');
    sel.parentNode.insertBefore(wrapper, sel);
    wrapper.appendChild(sel);

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'reg-cs-trigger placeholder';

    var label = document.createElement('span');
    label.className = 'reg-cs-label';
    label.textContent = sel.options[0] ? sel.options[0].text : 'Select';
    trigger.appendChild(label);

    var arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    arrow.setAttribute('class', 'reg-cs-arrow');
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
    list.className = 'reg-cs-list';
    wrapper.appendChild(list);

    function buildOptions() {
        list.innerHTML = '';
        Array.from(sel.options).forEach(function(opt) {
            var item = document.createElement('div');
            item.className = 'reg-cs-option' +
                (opt.value === '' ? ' disabled' : '') +
                (opt.selected ? ' selected' : '');
            item.textContent = opt.text;
            if (opt.value !== '') {
                item.addEventListener('click', function() {
                    sel.value = opt.value;
                    label.textContent = opt.text;
                    trigger.classList.remove('placeholder');
                    list.querySelectorAll('.reg-cs-option').forEach(function(o) { o.classList.remove('selected'); });
                    item.classList.add('selected');
                    closeList();
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            list.appendChild(item);
        });
    }

    function syncTrigger() {
        var cur = sel.options[sel.selectedIndex];
        if (cur && cur.value !== '') {
            label.textContent = cur.text;
            trigger.classList.remove('placeholder');
        } else {
            label.textContent = sel.options[0] ? sel.options[0].text : 'Select';
            trigger.classList.add('placeholder');
        }
    }
    syncTrigger();
    buildOptions();

    function openList() {
        document.querySelectorAll('.reg-cs-trigger.open').forEach(function(t) {
            if (t !== trigger) {
                t.classList.remove('open');
                var sib = t.parentElement && t.parentElement.querySelector('.reg-cs-list');
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

    // Watch native select class changes (hidden/visible from toggleDistrictFields)
    new MutationObserver(function() {
        if (sel.classList.contains('hidden')) {
            wrapper.classList.add('hidden');
        } else {
            wrapper.classList.remove('hidden');
        }
        buildOptions();
        syncTrigger();
    }).observe(sel, { attributes: true, attributeFilter: ['class'], childList: true, subtree: true });

    sel.addEventListener('change', syncTrigger);
}

document.addEventListener('DOMContentLoaded', function() {
    initRegCustomSelect(document.getElementById('state'));
    initRegCustomSelect(document.getElementById('district_select'));
    initRegCustomSelect(document.getElementById('no_gst_reason'));

    document.addEventListener('click', function() {
        document.querySelectorAll('.reg-cs-trigger.open').forEach(function(t) { t.classList.remove('open'); });
        document.querySelectorAll('.reg-cs-list.open').forEach(function(l) { l.classList.remove('open'); });
    });
});
</script>
