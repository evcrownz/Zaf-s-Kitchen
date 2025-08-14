// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.container');
    const signupBtn = document.querySelector('.signup-btn');
    const signinBtn = document.querySelector('.signin-btn');
    const forgotPasswordLink = document.querySelector('.forgot-password-link');
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    // Hamburger menu toggle
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');             
        });

        document.querySelectorAll('.nav-menu li a').forEach(link => {
            link.addEventListener('click', function () {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // Form toggles
    if (signupBtn) {
        signupBtn.addEventListener('click', () => {
            container.classList.remove('forgot-active');
            container.classList.add('active');
        });
    }

    if (signinBtn) {
        signinBtn.addEventListener('click', () => {
            container.classList.remove('active', 'forgot-active');
        });
    }

    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove('active');
            container.classList.add('forgot-active');
        });
    }

    // Initialize OTP modal
    initializeOTPModal();

    // Start countdown if OTP modal is visible and not yet expired
    const otpModal = document.getElementById('otpModal');
    const expiry = localStorage.getItem('otp_expiry');
    if (otpModal && otpModal.style.display !== 'none' && expiry) {
        const now = Date.now();
        if (now < parseInt(expiry)) {
            startCountdown(); // Resume countdown
        } else {
            localStorage.removeItem('otp_expiry');
        }
    }
});

// OTP Modal Logic
function initializeOTPModal() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendOtp');
    const resendForm = document.getElementById('resendForm');

    if (!otpInputs.length) return;

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 1) this.value = this.value.slice(0, 1);
            this.classList.toggle('filled', this.value.length === 1);
            if (this.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            checkOTPComplete();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].value = '';
                    otpInputs[index - 1].classList.remove('filled');
                } else {
                    this.value = '';
                    this.classList.remove('filled');
                }
                checkOTPComplete();
            } else if (e.key === 'ArrowLeft' && index > 0) {
                otpInputs[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
            if (pastedData.length === 6) {
                otpInputs.forEach((input, i) => {
                    input.value = pastedData[i] || '';
                    input.classList.toggle('filled', !!pastedData[i]);
                });
                checkOTPComplete();
                verifyBtn.focus();
            }
        });

        input.addEventListener('focus', function () {
            this.select();
        });
    });

    if (resendForm) {
        resendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!resendBtn.classList.contains('disabled')) {
                resendOTP();
            }
        });
    }

    function checkOTPComplete() {
        const complete = Array.from(otpInputs).every(input => input.value.length === 1);
        if (verifyBtn) {
            verifyBtn.disabled = !complete;
            verifyBtn.classList.toggle('enabled', complete);
        }
    }
}

// Countdown timer
let countdownInterval;

function startCountdown() {
    const resendBtn = document.getElementById('resendOtp');
    const countdownElement = document.getElementById('countdown');
    const timerElement = document.getElementById('timer');

    clearInterval(countdownInterval);

    let expiryTime = localStorage.getItem('otp_expiry');
    if (!expiryTime) {
        expiryTime = Date.now() + 60000;
        localStorage.setItem('otp_expiry', expiryTime);
    } else {
        expiryTime = parseInt(expiryTime);
    }

    function updateCountdown() {
        const remaining = Math.floor((expiryTime - Date.now()) / 1000);

        if (remaining >= 0 && countdownElement) {
            countdownElement.textContent = remaining;
        }

        if (remaining <= 0) {
            clearInterval(countdownInterval);
            if (timerElement) {
                timerElement.innerHTML = '<span style="color: #E75925; font-weight: bold;">You can now resend OTP</span>';
            }
            if (resendBtn) {
                resendBtn.classList.remove('disabled');
                resendBtn.style.pointerEvents = 'auto';
                resendBtn.innerHTML = 'Resend OTP';
            }
            localStorage.removeItem('otp_expiry');
        }
    }

    updateCountdown(); // first run
    countdownInterval = setInterval(updateCountdown, 1000);

    if (resendBtn) {
        resendBtn.classList.add('disabled');
        resendBtn.style.pointerEvents = 'none';
        resendBtn.innerHTML = 'Resend OTP';
    }
}

// Resend OTP
function resendOTP() {
    const resendBtn = document.getElementById('resendOtp');
    showLoadingState(resendBtn, 'Sending...');

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=resend-otp'
    })
        .then(response => response.json())
        .then(data => {
            hideLoadingState(resendBtn);
            if (data.success) {
                showOTPMessage(data.message, 'success');
                clearOTPInputs();
                localStorage.setItem('otp_expiry', Date.now() + 60000); // reset timer
                startCountdown();
            } else {
                showOTPMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoadingState(resendBtn);
            document.getElementById('resendForm').submit();
        });
}

// Utility Functions
function showLoadingState(element, loadingText = 'Loading...') {
    if (element) {
        element.disabled = true;
        element.dataset.originalText = element.innerHTML;
        element.innerHTML = `<span class="spinner"></span> ${loadingText}`;
    }
}

function hideLoadingState(element) {
    if (element && element.dataset.originalText) {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText;
        delete element.dataset.originalText;
    }
}

function clearOTPInputs() {
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
        setTimeout(() => {
            input.value = '';
            input.classList.remove('filled');
            input.classList.add('shake');
            setTimeout(() => input.classList.remove('shake'), 500);
        }, index * 50);
    });

    setTimeout(() => {
        if (otpInputs[0]) otpInputs[0].focus();
    }, 300);
}

function showOTPModal() {
    const otpModal = document.getElementById('otpModal');
    const otpModalContent = document.querySelector('.otp-modal-content');

    if (otpModal) {
        otpModal.style.display = 'flex';
        setTimeout(() => {
            otpModal.classList.add('show');
            if (otpModalContent) {
                otpModalContent.classList.add('show');
            }
        }, 10);

        const firstInput = document.querySelector('.otp-input');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 300);
        }

        startCountdown();
    }
}

function showOTPMessage(message, type) {
    const errorElement = document.getElementById('otpError');
    const successElement = document.getElementById('otpSuccess');

    if (errorElement) errorElement.style.display = 'none';
    if (successElement) successElement.style.display = 'none';

    if (type === 'error' && errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    } else if (type === 'success' && successElement) {
        successElement.textContent = message;
        successElement.style.display = 'block';
    }
}
