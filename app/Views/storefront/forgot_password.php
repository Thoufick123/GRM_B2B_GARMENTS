<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <div class="text-center">
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 font-display">Forgot Password</h2>
            <p class="mt-2 text-center text-sm text-gray-500">
                Choose how you would like to receive your 6-digit password reset OTP.
            </p>
        </div>

        <?php 
        $flashError = \Core\Session::getFlash('error');
        if ($flashError): 
        ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($flashError) ?></p>
            </div>
        <?php endif; ?>

        <!-- Channel Selector Tabs -->
        <div class="grid grid-cols-2 gap-2 p-1.5 bg-gray-100 rounded-xl">
            <button type="button" id="tab-whatsapp" onclick="switchChannel('whatsapp')"
                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all shadow-sm bg-white text-[#F25996]">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-12.416c-5.514 0-10 4.486-10 10 0 1.761.458 3.417 1.258 4.861l-1.297 4.747 4.869-1.276c1.4 1.026 3.123 1.636 4.989 1.636 5.514 0 10-4.486 10-10s-4.486-10-10-10z"/>
                </svg>
                <span>WhatsApp</span>
            </button>
            <button type="button" id="tab-email" onclick="switchChannel('email')"
                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Email</span>
            </button>
        </div>

        <form id="forgot-password-form" class="mt-6 space-y-6" action="<?= BASE_URL ?>/forgot-password" method="POST">
            <!-- Hidden channel input -->
            <input type="hidden" name="channel" id="channel-input" value="whatsapp">

            <!-- WhatsApp input block (active by default) -->
            <div id="whatsapp-input-block">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Registered WhatsApp / Mobile Number <span class="text-red-500">*</span>
                </label>
                <div id="phone-input-wrapper" class="flex rounded-xl shadow-sm border border-gray-200 focus-within:ring-2 focus-within:ring-[#F25996] focus-within:border-transparent overflow-hidden transition-all bg-white">
                    <span class="inline-flex items-center px-4 bg-gray-50 text-gray-600 font-semibold text-sm border-r border-gray-200 select-none">
                        +91
                    </span>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="[0-9]{10}"
                        class="block w-full px-4 py-3 border-0 placeholder-gray-400 text-gray-900 focus:outline-none sm:text-sm font-medium" 
                        placeholder="Enter 10-digit mobile number">
                </div>
                <!-- Inline customer verification badge -->
                <div id="phone-feedback" class="mt-2 min-h-[22px]"></div>
                <p class="mt-1 text-xs text-gray-500">
                    We will send an instant 6-digit reset code to your WhatsApp.
                </p>
            </div>

            <!-- Email input block (hidden by default) -->
            <div id="email-input-block" class="hidden">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Registered Email Address <span class="text-red-500">*</span>
                </label>
                <input id="email" name="email" type="email" autocomplete="email"
                    class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-transparent transition-all sm:text-sm font-medium shadow-sm" 
                    placeholder="Enter your registered email">
                <!-- Inline customer verification badge -->
                <div id="email-feedback" class="mt-2 min-h-[22px]"></div>
                <p class="mt-1 text-xs text-gray-500">
                    We will send a 6-digit verification code to your email inbox.
                </p>
            </div>

            <div>
                <button type="submit" id="submit-btn"
                    class="group relative w-full flex items-center justify-center gap-2 py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white transition-all shadow-sm hover:opacity-90 active:scale-[0.99] cursor-pointer" 
                    style="background: #F25996;">
                    <span>Send OTP via WhatsApp</span>
                </button>
            </div>
            
            <div class="text-center">
                <a href="<?= BASE_URL ?>/login" class="font-semibold text-sm text-[#F25996] hover:text-[#d94883] transition-colors">
                    Back to Login
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let isCustomerVerified = false;
let checkDebounceTimer = null;

function switchChannel(channel) {
    const tabWhatsApp = document.getElementById('tab-whatsapp');
    const tabEmail = document.getElementById('tab-email');
    const waBlock = document.getElementById('whatsapp-input-block');
    const emailBlock = document.getElementById('email-input-block');
    const channelInput = document.getElementById('channel-input');
    const submitBtn = document.getElementById('submit-btn');
    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');

    channelInput.value = channel;
    isCustomerVerified = false;

    // Clear feedback
    document.getElementById('phone-feedback').innerHTML = '';
    document.getElementById('email-feedback').innerHTML = '';
    document.getElementById('phone-input-wrapper').className = "flex rounded-xl shadow-sm border border-gray-200 focus-within:ring-2 focus-within:ring-[#F25996] focus-within:border-transparent overflow-hidden transition-all bg-white";
    emailInput.className = "appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-transparent transition-all sm:text-sm font-medium shadow-sm";

    if (channel === 'whatsapp') {
        tabWhatsApp.className = "flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all shadow-sm bg-white text-[#F25996]";
        tabEmail.className = "flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all text-gray-600 hover:text-gray-900";

        waBlock.classList.remove('hidden');
        emailBlock.classList.add('hidden');

        phoneInput.required = true;
        emailInput.required = false;

        submitBtn.style.background = '#F25996';
        submitBtn.innerHTML = '<span>Send OTP via WhatsApp</span>';
        phoneInput.focus();

        if (phoneInput.value.trim().length === 10) {
            verifyCustomer('whatsapp', phoneInput.value.trim());
        }
    } else {
        tabEmail.className = "flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all shadow-sm bg-white text-[#F25996]";
        tabWhatsApp.className = "flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-semibold transition-all text-gray-600 hover:text-gray-900";

        emailBlock.classList.remove('hidden');
        waBlock.classList.add('hidden');

        emailInput.required = true;
        phoneInput.required = false;

        submitBtn.style.background = '#F25996';
        submitBtn.innerHTML = '<span>Send OTP via Email</span>';
        emailInput.focus();

        if (emailInput.value.trim().length > 3 && emailInput.value.includes('@')) {
            verifyCustomer('email', emailInput.value.trim());
        }
    }
}

async function verifyCustomer(channel, identifier) {
    const feedbackEl = channel === 'whatsapp' ? document.getElementById('phone-feedback') : document.getElementById('email-feedback');
    const inputWrapper = channel === 'whatsapp' ? document.getElementById('phone-input-wrapper') : document.getElementById('email');

    feedbackEl.innerHTML = `<span class="inline-flex items-center gap-1.5 text-xs text-gray-500 font-medium">
        <svg class="animate-spin w-3.5 h-3.5 text-[#F25996]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
        Verifying customer account...
    </span>`;

    try {
        const res = await fetch('<?= BASE_URL ?>/forgot-password/check-customer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ channel: channel, identifier: identifier })
        });
        const data = await res.json();

        if (data.exists) {
            isCustomerVerified = true;
            if (channel === 'whatsapp') {
                inputWrapper.className = "flex rounded-xl shadow-sm border-2 border-emerald-500 overflow-hidden transition-all bg-white";
            } else {
                inputWrapper.className = "appearance-none block w-full px-4 py-3 border-2 border-emerald-500 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none transition-all sm:text-sm font-medium shadow-sm";
            }
            feedbackEl.innerHTML = `<span class="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Registered Customer: ${data.name}
            </span>`;
            return true;
        } else {
            isCustomerVerified = false;
            if (channel === 'whatsapp') {
                inputWrapper.className = "flex rounded-xl shadow-sm border-2 border-rose-500 overflow-hidden transition-all bg-white";
            } else {
                inputWrapper.className = "appearance-none block w-full px-4 py-3 border-2 border-rose-500 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none transition-all sm:text-sm font-medium shadow-sm";
            }
            feedbackEl.innerHTML = `<span class="inline-flex items-center gap-1.5 text-xs text-rose-700 font-semibold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200">
                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                ${data.message || 'Account not found. Only registered customers can receive an OTP.'}
            </span>`;
            return false;
        }
    } catch (e) {
        feedbackEl.innerHTML = '';
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    switchChannel('whatsapp');

    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');
    const form = document.getElementById('forgot-password-form');

    // Phone input auto-formatting & verification
    phoneInput.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        clearTimeout(checkDebounceTimer);

        const val = this.value.trim();
        if (val.length === 10) {
            checkDebounceTimer = setTimeout(() => {
                verifyCustomer('whatsapp', val);
            }, 300);
        } else {
            isCustomerVerified = false;
            document.getElementById('phone-feedback').innerHTML = '';
            document.getElementById('phone-input-wrapper').className = "flex rounded-xl shadow-sm border border-gray-200 focus-within:ring-2 focus-within:ring-[#F25996] focus-within:border-transparent overflow-hidden transition-all bg-white";
        }
    });

    // Email input auto-verification
    emailInput.addEventListener('input', function(e) {
        clearTimeout(checkDebounceTimer);
        const val = this.value.trim();
        if (val.length > 5 && val.includes('@') && val.includes('.')) {
            checkDebounceTimer = setTimeout(() => {
                verifyCustomer('email', val);
            }, 400);
        } else {
            isCustomerVerified = false;
            document.getElementById('email-feedback').innerHTML = '';
            this.className = "appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-transparent transition-all sm:text-sm font-medium shadow-sm";
        }
    });

    // Form submit guard
    form.addEventListener('submit', async function(e) {
        const channel = document.getElementById('channel-input').value;
        const submitBtn = document.getElementById('submit-btn');

        if (channel === 'whatsapp') {
            const phone = phoneInput.value.replace(/[^0-9]/g, '').trim();
            if (phone.length !== 10) {
                e.preventDefault();
                document.getElementById('phone-feedback').innerHTML = `<span class="inline-flex items-center gap-1.5 text-xs text-rose-700 font-semibold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200">Please enter a valid 10-digit Indian mobile number.</span>`;
                phoneInput.focus();
                return;
            }

            if (!isCustomerVerified) {
                e.preventDefault();
                submitBtn.disabled = true;
                const ok = await verifyCustomer('whatsapp', phone);
                submitBtn.disabled = false;
                if (ok) {
                    form.submit();
                }
                return;
            }
        } else {
            const email = emailInput.value.trim();
            if (!email || !email.includes('@')) {
                e.preventDefault();
                document.getElementById('email-feedback').innerHTML = `<span class="inline-flex items-center gap-1.5 text-xs text-rose-700 font-semibold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200">Please enter a valid registered email address.</span>`;
                emailInput.focus();
                return;
            }

            if (!isCustomerVerified) {
                e.preventDefault();
                submitBtn.disabled = true;
                const ok = await verifyCustomer('email', email);
                submitBtn.disabled = false;
                if (ok) {
                    form.submit();
                }
                return;
            }
        }
    });
});
</script>
