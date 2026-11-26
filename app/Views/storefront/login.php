<div class="min-h-[calc(100vh-80px)] flex flex-col justify-center py-8 px-4 sm:px-6 lg:px-8 bg-gray-50 overflow-visible">
    <?php
    $redirectTarget = !empty($redirect) ? $redirect : ($_GET['redirect'] ?? \Core\Session::get('return_to') ?? '');
    ?>
    <div class="w-full sm:mx-auto sm:max-w-md text-center">
        <h2 class="mt-2 text-3xl font-extrabold text-gray-900 font-display">
            Log in to your account
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Or
            <a href="<?= BASE_URL ?>/register<?= !empty($redirectTarget) ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="font-medium text-[#f25996] hover:text-[#d94883]">
                register as a new B2B buyer
            </a>
        </p>
    </div>

    <div class="mt-8 w-full sm:mx-auto sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-lg rounded-2xl sm:shadow-card sm:rounded-xl sm:px-10 border border-gray-100">
            <form class="space-y-5" action="<?= BASE_URL ?>/login" method="POST">
                <?php if (!empty($redirectTarget)): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTarget) ?>">
                <?php endif; ?>
                
                <div>
                    <label for="email" class="form-label text-sm font-semibold text-gray-700">Username, Mobile number or Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="text" placeholder="Enter username, mobile or email" autocomplete="username" autocapitalize="none" autocorrect="off" required class="form-input px-3 py-2.5 border border-gray-200 rounded-lg w-full focus:ring-2 focus:ring-[#f25996] focus:border-[#f25996] outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label text-sm font-semibold text-gray-700">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input px-3 py-2.5 border border-gray-200 rounded-lg w-full focus:ring-2 focus:ring-[#f25996] focus:border-[#f25996] outline-none bg-white pr-10">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <style>
                    @media (max-width: 639px) {
                        .mobile-stack {
                            flex-direction: column !important;
                            align-items: flex-start !important;
                            gap: 1rem;
                        }
                    }
                </style>
                <div class="flex items-center justify-between mobile-stack">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-[#f25996] focus:ring-[#f25996] border-gray-300 rounded cursor-pointer" style="accent-color: #f25996;">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900 cursor-pointer select-none">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= BASE_URL ?>/forgot-password" class="font-medium text-[#f25996] hover:text-[#d94883]">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full py-2.5 mt-2 inline-flex justify-center items-center px-4 border border-transparent rounded-lg font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90 shadow-sm" style="background:#f25996;">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Don't have an account?</p>
                <div class="grid <?= is_vendor_module_enabled() ? 'grid-cols-2' : 'grid-cols-1' ?> gap-2">
                    <a href="<?= BASE_URL ?>/register<?= !empty($redirectTarget) ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="py-2 px-3 border border-[#fbaed2] bg-[#fdf2f7] hover:bg-[#fce7f1] text-[#f25996] text-xs font-bold rounded-xl transition flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-[#f25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Join as Buyer
                    </a>
                    <?php if (is_vendor_module_enabled()): ?>
                    <a href="<?= BASE_URL ?>/vendor-register" class="py-2 px-3 border border-[#fbaed2] bg-[#fdf2f7] hover:bg-[#fce7f1] text-[#f25996] text-xs font-bold rounded-xl transition flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-[#f25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Sell as Vendor
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 639px) {
        .cms-section > .w-full.flex.md\:hidden {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        main > div.min-h-\[calc\(100vh-80px\)\] {
            min-height: calc(100vh - 60px);
            padding-top: 1.5rem;
            padding-bottom: 2.5rem;
        }
    }
</style>

<script>
    function togglePassword() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>
