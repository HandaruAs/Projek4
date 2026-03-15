<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMOPANG Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/loginadmin.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">
        <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
        <span class="brand-name">SIMOPANG Admin</span>
    </a>
    <div class="navbar-links">
        <a href="#"><i class="fas fa-circle-question"></i>Bantuan</a>
        <a href="#"><i class="fas fa-book"></i>Dokumentasi</a>
    </div>
</nav>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-left">
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

            <div class="left-title">Panel Administrasi</div>
            <div class="left-desc">
                Kelola operasional dan data sistem dengan aman
                melalui enkripsi MongoDB dan Laravel Auth.
            </div>
            <div class="left-badge">
                <div class="pulse-dot"></div>
                Sistem Online & Aman
            </div>
        </div>

        <div class="login-right">

            <div class="form-tag">
                <i class="fas fa-lock"></i>
                Admin Access
            </div>

            <div class="form-heading">Selamat Datang 👋</div>
            <div class="form-subheading">
                Silakan masukkan kredensial Anda untuk<br>mengakses dashboard SIMOPANG.
            </div>

            <div class="form-divider"></div>

            @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
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
                        <a href="#" class="forgot-link">Lupa Password?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                            placeholder="Masukkan kata sandi"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">
                            <i class="fas fa-circle-exclamation"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>

                <button type="submit" class="btn-login">
                    Login Ke Panel Admin
                    <i class="fas fa-arrow-right-to-bracket"></i>
                </button>

            </form>

            <div class="register-redirect">
                Belum punya akun? <a href="{{ route('register.admin') }}">Daftar di sini</a>
            </div>

            <div class="powered-by">
                Powered by <span>SIMOPANG</span> Core
            </div>

        </div>

    </div>
</div>

<footer class="footer">
    © 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.
</footer>

<script>
    function togglePassword() {
        const input   = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type        = 'text';
            eyeIcon.className = 'fas fa-eye-slash';
        } else {
            input.type        = 'password';
            eyeIcon.className = 'fas fa-eye';
        }
    }
</script>

</body>
</html>