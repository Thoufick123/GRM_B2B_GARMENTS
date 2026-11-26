<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 font-display">Enter OTP</h2>
            <p class="mt-2 text-center text-sm text-gray-500">
                <?php 
                $channel = \Core\Session::get('reset_channel');
                if ($channel === 'whatsapp'): 
                ?>
                    We've sent a 6-digit OTP directly to your <strong>WhatsApp</strong>. Please enter it below to proceed.
                <?php elseif ($channel === 'email'): ?>
                    We've sent a 6-digit OTP to your <strong>Email</strong> inbox. Please enter it below to proceed.
                <?php else: ?>
                    We've sent a 6-digit OTP to your registered mobile number (WhatsApp / SMS). Please enter it below to proceed.
                <?php endif; ?>
            </p>
        </div>
        
        <?php 
        $flashError = \Core\Session::getFlash('error') ?: \Core\Session::getFlash('Invalid OTP');
        if ($flashError): 
        ?>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error',
                            text: <?= json_encode($flashError) ?>,
                            icon: 'error',
                            confirmButtonColor: '#f25996',
                            customClass: {
                                popup: 'rounded-2xl',
                                confirmButton: 'rounded-xl px-6 py-2'
                            }
                        });
                    } else {
                        alert(<?= json_encode($flashError) ?>);
                    }
                });
            </script>
        <?php endif; ?>

        <?php if ($flashSuccess = \Core\Session::getFlash('success')): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md mt-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($flashSuccess) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center bg-[#fdf2f7] p-4 rounded-xl border border-[#fbaed2]">
            <p class="text-sm text-[#f25996] font-medium mb-1">Time Remaining</p>
            <div id="countdown" class="text-3xl font-bold font-display text-[#f25996] tracking-wider">01:00</div>
            <p id="expired-text" class="text-red-500 font-bold hidden mt-1">OTP Expired!</p>
        </div>


        <form id="otp-form" class="mt-6 space-y-6" action="<?= BASE_URL ?><?= $formAction ?? '/verify-otp' ?>" method="POST" novalidate>
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">6-Digit OTP</label>
                <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" required maxlength="6" autocomplete="one-time-code" autofocus
                    class="appearance-none relative block w-full px-4 py-4 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#f25996] focus:border-[#f25996] transition-all sm:text-lg text-center font-bold tracking-[0.5em]" 
                    placeholder="------">
            </div>

            <div>
                <button id="submit-btn" type="submit" 
                    class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white transition-all shadow-sm hover:opacity-90" style="background: #f25996;">
                    Verify & Proceed
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <?php if (isset($isRegistration) && $isRegistration): ?>
                <form action="<?= BASE_URL ?><?= $resendAction ?? '/register/resend-otp' ?>" method="POST" class="inline">
                    <button type="submit" class="font-medium text-sm text-[#f25996] hover:text-[#d94883] transition-colors bg-transparent border-0 cursor-pointer p-0 underline">
                        Resend OTP
                    </button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?><?= $resendAction ?? '/forgot-password' ?>" class="font-medium text-sm text-gray-500 hover:text-gray-900 transition-colors">
                    Request a new OTP
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let timeLeft = <?= isset($otpExpirationRemaining) ? (int)$otpExpirationRemaining : 0 ?>;
    const countdownEl = document.getElementById('countdown');
    const expiredText = document.getElementById('expired-text');
    const submitBtn = document.getElementById('submit-btn');
    const otpInput = document.getElementById('otp');

    function updateTimerDisplay() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        let minsStr = minutes < 10 ? '0' + minutes : minutes;
        let secsStr = seconds < 10 ? '0' + seconds : seconds;
        countdownEl.textContent = minsStr + ':' + secsStr;
    }

    function handleExpiration() {
        countdownEl.classList.add('hidden');
        expiredText.classList.remove('hidden');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        submitBtn.textContent = 'OTP Expired';
        otpInput.disabled = true;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'OTP Expired',
                text: 'Your OTP has expired. Please request a new one.',
                icon: 'warning',
                confirmButtonColor: '#f25996',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2'
                }
            });
        }
    }

    if (timeLeft <= 0) {
        handleExpiration();
    } else {
        updateTimerDisplay();
        const timer = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();

            if (timeLeft <= 0) {
                clearInterval(timer);
                handleExpiration();
            }
        }, 1000);
    }
    
    // Custom Frontend Validation to prevent empty/invalid submissions
    document.getElementById('otp-form').addEventListener('submit', function(e) {
        const otpVal = otpInput.value.trim();
        if (!otpVal || !/^\d{6}$/.test(otpVal)) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Incomplete',
                    text: 'Please enter the full 6-digit OTP before verifying.',
                    icon: 'warning',
                    confirmButtonColor: '#f25996',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl px-6 py-2'
                    }
                });
            } else {
                alert('Please enter a valid 6-digit OTP.');
            }
        }
    });
</script>


