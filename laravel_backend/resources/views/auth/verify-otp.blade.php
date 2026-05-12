<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO-2.png') }}"> 
    <title>Verifikasi OTP — SIMOPANG</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/verify-otp.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">
        <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
        <span class="brand-name">SIMOPANG</span>
    </a>
    <div class="navbar-links">
        <a href="/" class="btn-back">
            <i class="fas fa-house"></i>
            <span class="back-text">Beranda</span>
        </a>
        <a href="{{ route('forgot.password') }}">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>
</nav>

<div class="page-wrapper">
    <div class="auth-card">

        <div class="card-left">
            <div class="left-illustration">
                <div class="hex-bg"></div>
                <div class="otp-display">
                    <div class="otp-box">•</div>
                    <div class="otp-box">•</div>
                    <div class="otp-box active">8</div>
                    <div class="otp-box">•</div>
                    <div class="otp-box">•</div>
                    <div class="otp-box">•</div>
                </div>
                <div class="scan-line"></div>
            </div>

            <div class="left-title">Cek Emailmu</div>
            <div class="left-desc">
                Kode OTP 6 digit telah dikirim ke<br>
                email yang kamu daftarkan.
            </div>

            <div class="left-steps">
                <div class="step done">
                    <div class="step-num"><i class="fas fa-check"></i></div>
                    <span>Masukkan Email</span>
                </div>
                <div class="step-line done-line"></div>
                <div class="step active">
                    <div class="step-num">2</div>
                    <span>Verifikasi OTP</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-num">3</div>
                    <span>Reset Password</span>
                </div>
            </div>
        </div>

        <div class="card-right">
            <div class="page-icon">
                <i class="fas fa-mobile-screen-button"></i>
            </div>

            <div class="form-heading">Masukkan Kode OTP</div>
            <div class="form-subheading">
                Kode dikirim ke <strong>{{ session('email') ?? 'email Anda' }}</strong>.<br>
                Kode berlaku selama <span class="timer-text" id="timer">05:00</span>.
            </div>

            <div class="form-divider"></div>

            @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/verify-otp" id="otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                <input type="hidden" name="otp" id="otp-hidden">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autofocus>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <div class="otp-dash">—</div>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                </div>

                <button type="submit" class="btn-submit" id="btn-submit" disabled>
                    <span id="btn-text">Verifikasi Kode</span>
                    <i class="fas fa-shield-check" id="btn-icon"></i>
                </button>
            </form>

            <div class="resend-wrap">
                <span>Tidak menerima kode?</span>
                <form method="POST" action="/forgot-password" style="display:inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('email') }}">
                    <button type="submit" class="resend-btn" id="resend-btn" disabled>
                        Kirim Ulang (<span id="resend-timer">60</span>s)
                    </button>
                </form>
            </div>

            <div class="powered-by">Powered by <span>SIMOPANG</span> Core</div>
        </div>

    </div>
</div>

<footer class="footer">© 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.</footer>

<script>
    /* OTP Input Logic */
    const inputs = document.querySelectorAll('.otp-input');
    const hidden = document.getElementById('otp-hidden');
    const btn    = document.getElementById('btn-submit');

    inputs.forEach((input, i) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value && i < inputs.length - 1) inputs[i + 1].focus();
            syncOTP();
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && i > 0) inputs[i - 1].focus();
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            paste.split('').forEach((char, idx) => {
                if (inputs[idx]) { inputs[idx].value = char; inputs[idx].classList.add('filled'); }
            });
            const focusIdx = Math.min(paste.length, inputs.length - 1);
            if (inputs[focusIdx]) inputs[focusIdx].focus();
            syncOTP();
        });
        input.addEventListener('focus', function() { this.select(); });
    });

    function syncOTP() {
        const otp = Array.from(inputs).map(i => i.value).join('');
        hidden.value = otp;
        inputs.forEach(i => i.classList.toggle('filled', i.value !== ''));
        btn.disabled = otp.length < 6;
    }

    /* Countdown Timer (5 min) */
    let seconds = 300;
    const timerEl = document.getElementById('timer');
    const countdown = setInterval(() => {
        seconds--;
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
        if (seconds <= 60) timerEl.classList.add('urgent');
        if (seconds <= 0) {
            clearInterval(countdown);
            timerEl.textContent = 'Kadaluarsa';
            timerEl.classList.add('expired');
        }
    }, 1000);

    /* Resend Cooldown (60s) */
    let resendSec = 60;
    const resendEl  = document.getElementById('resend-timer');
    const resendBtn = document.getElementById('resend-btn');
    const resendInterval = setInterval(() => {
        resendSec--;
        resendEl.textContent = resendSec;
        if (resendSec <= 0) {
            clearInterval(resendInterval);
            resendBtn.disabled = false;
            resendBtn.textContent = 'Kirim Ulang';
        }
    }, 1000);

    /* Submit loading state */
    document.getElementById('otp-form').addEventListener('submit', function() {
        const text = document.getElementById('btn-text');
        const icon = document.getElementById('btn-icon');
        btn.disabled     = true;
        text.textContent = 'Memverifikasi...';
        icon.className   = 'fas fa-spinner fa-spin';
    });
</script>

</body>
</html>