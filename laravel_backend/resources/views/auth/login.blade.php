<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO-2.png') }}"> 
    <title>Login — SIMOPANG</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="{{ asset('css/auth-base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <a href="/" class="navbar-brand">
    <div style="width:36px; height:36px; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.15); transition:transform 0.2s;" 
         onmouseover="this.style.transform='scale(1.05)'" 
         onmouseout="this.style.transform='scale(1)'">
        <img src="{{ asset('images/LOGO-2.png') }}" 
             alt="Logo SIMOPANG" 
             style="width:100%; height:100%; object-fit:cover;">
    </div>
    <span class="brand-name">SIMOPANG</span>
</a>
    <div class="navbar-links">
        <a href="/" class="btn-back">
            <i class="fas fa-house"></i>
            <span class="back-text">Beranda</span>
        </a>
    </div>
</nav>

<!-- Main -->
<main class="login-container">
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Left -->
            <div class="login-left card-left">
                <div class="illustration-card">
                    <div class="secure-label">Secure Login</div>
                    <div class="shield-wrap">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="fake-field">
                        <div class="fake-field-dot"></div>
                        <div class="fake-field-line"></div>
                    </div>
                    <div class="fake-field">
                        <div class="fake-field-dot"></div>
                        <div class="fake-field-line short"></div>
                    </div>
                    <div class="fake-btn-wrap">
                        <div class="fake-btn">
                            <div class="fake-btn-line"></div>
                            <div class="fake-btn-dot"></div>
                        </div>
                    </div>
                    <div class="card-dots">
                        <span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="left-title">Panel SIMOPANG</div>
                <div class="left-desc">
                    Kelola operasional dan data sistem dengan aman
                    melalui enkripsi MongoDB dan Laravel Auth.
                </div>
                <div class="left-badge">
                    <div class="pulse-dot"></div>
                    Sistem Online & Aman
                </div>
            </div>

            <!-- Right -->
            <div class="login-right card-right">
                <div class="form-tag">
                    <i class="fas fa-fingerprint" style="font-size:11px"></i> Autentikasi
                </div>
                <div class="form-heading">Selamat Datang 👋</div>
                <div class="form-subheading">
                    Silakan masukkan kredensial Anda untuk<br>mengakses dashboard SIMOPANG.
                </div>
                <div class="form-divider"></div>

                @if(session('error'))
                <div class="alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input
                                type="email" id="email" name="email"
                                class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                                placeholder="nama@email.com"
                                value="{{ old('email') }}"
                                required autofocus
                            >
                        </div>
                        <div class="field-error" id="email-error" style="display:none">
                            <i class="fas fa-circle-exclamation"></i> Format email tidak valid.
                        </div>
                        @error('email')
                            <div class="field-error">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="label-row">
                            <label class="form-label" for="password">Password</label>
                            <a href="{{ route('forgot.password') }}" class="forgot-link">Lupa Password?</a>
                        </div>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password" id="password" name="password"
                                class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                                placeholder="Masukkan kata sandi"
                                required minlength="6"
                            >
                            <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                        <div class="field-error" id="password-error" style="display:none">
                            <i class="fas fa-circle-exclamation"></i> Password minimal 6 karakter.
                        </div>
                        @error('password')
                            <div class="field-error">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                        <label for="remember">Ingat Saya</label>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Login</span>
                        <i class="fas fa-arrow-right-to-bracket"></i>
                    </button>
                </form>

                <div class="register-redirect">
                    Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
                </div>
                <div class="powered-by">Powered by <span>SIMOPANG</span> Core</div>
            </div>

        </div>
    </div>
</main>

<footer class="footer">© 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password toggle
    const toggleBtn  = document.getElementById('togglePasswordBtn');
    const passInput  = document.getElementById('password');
    const eyeIcon    = document.getElementById('eye-icon');

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Validation helpers
    function validateEmail(input) {
        const error = document.getElementById('email-error');
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
        if (!input.value) { input.classList.remove('is-error'); error.style.display='none'; return true; }
        if (!valid) { input.classList.add('is-error'); error.style.display='flex'; return false; }
        input.classList.remove('is-error'); error.style.display='none'; return true;
    }

    function validatePassword(input) {
        const error = document.getElementById('password-error');
        if (!input.value) { input.classList.remove('is-error'); error.style.display='none'; return true; }
        if (input.value.length < 6) { input.classList.add('is-error'); error.style.display='flex'; return false; }
        input.classList.remove('is-error'); error.style.display='none'; return true;
    }

    const emailInput = document.getElementById('email');
    emailInput.addEventListener('input', () => validateEmail(emailInput));
    emailInput.addEventListener('blur',  () => validateEmail(emailInput));
    passInput.addEventListener('input',  () => validatePassword(passInput));
    passInput.addEventListener('blur',   () => validatePassword(passInput));

    // Focus effects
    document.querySelectorAll('.form-input').forEach(inp => {
        inp.addEventListener('focus', () => inp.parentElement.classList.add('has-focus'));
        inp.addEventListener('blur',  () => inp.parentElement.classList.remove('has-focus'));
        if (inp.value) inp.parentElement.classList.add('has-value');
    });

    // Form submit
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const eValid = validateEmail(emailInput);
        const pValid = validatePassword(passInput);
        if (!eValid || !pValid || !emailInput.value.trim() || !passInput.value.trim()) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>