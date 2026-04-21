<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — SIMOPANG</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/forgot-password.css') }}" rel="stylesheet">
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
    </div>
</nav>

<div class="page-wrapper">
    <div class="auth-card">

        <div class="card-left">
            <div class="left-illustration">
                <div class="orbit-ring ring-1"></div>
                <div class="orbit-ring ring-2"></div>
                <div class="orbit-ring ring-3"></div>
                <div class="center-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="orbit-dot dot-1"><i class="fas fa-lock"></i></div>
                <div class="orbit-dot dot-2"><i class="fas fa-key"></i></div>
                <div class="orbit-dot dot-3"><i class="fas fa-shield-halved"></i></div>
            </div>

            <div class="left-title">Lupa Password?</div>
            <div class="left-desc">
                Tenang, kami akan kirimkan kode OTP<br>
                ke email kamu untuk verifikasi identitas.
            </div>

            <div class="left-steps">
                <div class="step active">
                    <div class="step-num">1</div>
                    <span>Masukkan Email</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
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
                <i class="fas fa-envelope-open-text"></i>
            </div>

            <div class="form-heading">Verifikasi Email</div>
            <div class="form-subheading">
                Masukkan alamat email yang terdaftar.<br>
                Kami akan mengirimkan kode OTP ke sana.
            </div>

            <div class="form-divider"></div>

            @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/forgot-password">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email" id="email" name="email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}"
                            required autofocus
                            oninput="validateEmail(this)"
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

                <button type="submit" class="btn-submit" id="btn-submit">
                    <span id="btn-text">Kirim Kode OTP</span>
                    <i class="fas fa-paper-plane" id="btn-icon"></i>
                </button>
            </form>

            <div class="back-link">
                Ingat password? <a href="{{ route('login') }}">Login di sini</a>
            </div>
            <div class="powered-by">Powered by <span>SIMOPANG</span> Core</div>
        </div>

    </div>
</div>

<footer class="footer">© 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.</footer>

<script>
    function validateEmail(input) {
        const error = document.getElementById('email-error');
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
        if (!valid && input.value.length > 0) {
            input.classList.add('is-error');
            error.style.display = 'flex';
        } else {
            input.classList.remove('is-error');
            error.style.display = 'none';
        }
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('email');
        validateEmail(email);
        if (email.classList.contains('is-error') || !email.value) {
            e.preventDefault();
            return;
        }
        const btn  = document.getElementById('btn-submit');
        const text = document.getElementById('btn-text');
        const icon = document.getElementById('btn-icon');
        btn.disabled     = true;
        text.textContent = 'Mengirim...';
        icon.className   = 'fas fa-spinner fa-spin';
    });
</script>

</body>
</html>