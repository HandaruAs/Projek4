<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SIMOPANG Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">
        <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
        <span class="brand-name">SIMOPANG Admin</span>
    </a>
    <div class="navbar-links">
        <a href="#"><i class="fas fa-circle-question" style="margin-right:5px"></i>Bantuan</a>
        <a href="#"><i class="fas fa-book" style="margin-right:5px"></i>Dokumentasi</a>
    </div>
</nav>

<div class="register-wrapper">
    <div class="register-card">

        <div class="register-left">
            <div class="step-indicator">
                <div class="step-dot done"></div>
                <div class="step-line"></div>
                <div class="step-dot active"></div>
                <div class="step-line"></div>
                <div class="step-dot"></div>
            </div>
            <div class="illustration-card">
                <div class="secure-label">Daftar Akun</div>
                <div class="icon-row">
                    <div class="icon-item active"><i class="fas fa-user"></i></div>
                    <div class="icon-item active"><i class="fas fa-envelope"></i></div>
                    <div class="icon-item"><i class="fas fa-lock"></i></div>
                    <div class="icon-item"><i class="fas fa-check"></i></div>
                </div>
                <div class="fake-field"><div class="fake-field-dot"></div><div class="fake-field-line"></div></div>
                <div class="fake-field"><div class="fake-field-dot"></div><div class="fake-field-line"></div></div>
                <div class="fake-field"><div class="fake-field-dot"></div><div class="fake-field-line short"></div></div>
                <div class="fake-btn-wrap">
                    <div class="fake-btn"><div class="fake-btn-line"></div><div class="fake-btn-dot"></div></div>
                </div>
                <div class="card-dots"><span></span><span></span><span></span><span></span></div>
            </div>
            <div class="left-title">Buat Akun Admin</div>
            <div class="left-desc">Daftarkan akun Anda untuk mengelola data komoditas dan prediksi harga SIMOPANG.</div>
            <div class="left-badge"><div class="pulse-dot"></div>Enkripsi Data Aktif</div>
        </div>

        <div class="register-right">
            <div class="form-tag"><i class="fas fa-user-plus" style="font-size:10px"></i> Registrasi Admin</div>
            <div class="form-heading">Buat Akun Baru</div>
            <div class="form-subheading">Lengkapi data berikut untuk mendaftarkan<br>akun administrator SIMOPANG.</div>
            <div class="form-divider"></div>

            @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register.admin') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="name" name="name"
                            class="form-input {{ $errors->has('name') ? 'is-error' : '' }}"
                            placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required autofocus>
                    </div>
                    @error('name')
                        <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="nama@email.com" value="{{ old('email') }}" required>
                    </div>
                    @error('email')
                        <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                            class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                            placeholder="Minimal 8 karakter" required oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePass('password','eye1')">
                            <i class="fas fa-eye" id="eye1"></i>
                        </button>
                    </div>
                    <div class="strength-bar-wrap" id="strengthWrap" style="display:none">
                        <div class="bar" id="bar1"></div>
                        <div class="bar" id="bar2"></div>
                        <div class="bar" id="bar3"></div>
                        <div class="bar" id="bar4"></div>
                        <span class="strength-text" id="strengthText"></span>
                    </div>
                    @error('password')
                        <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-input" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="toggle-password" onclick="togglePass('password_confirmation','eye2')">
                            <i class="fas fa-eye" id="eye2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    Buat Akun Admin <i class="fas fa-user-check"></i>
                </button>
            </form>

            <div class="login-redirect">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
            </div>
            <div class="terms-note">
                Dengan mendaftar, kamu menyetujui <a href="#">Kebijakan Privasi</a> dan <a href="#">Syarat Penggunaan</a> SIMOPANG.
            </div>
            <div class="powered-by">Powered by <span>SIMOPANG</span> Core</div>
        </div>

    </div>
</div>

<footer class="footer">© 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.</footer>

<script>
    function togglePass(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon  = document.getElementById(iconId);
        input.type     = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    function checkStrength(val) {
        const wrap = document.getElementById('strengthWrap');
        const bars = ['bar1','bar2','bar3','bar4'].map(id => document.getElementById(id));
        const text = document.getElementById('strengthText');
        if (!val) { wrap.style.display = 'none'; return; }
        wrap.style.display = 'flex';
        bars.forEach(b => b.className = 'bar');
        let score = 0;
        if (val.length >= 8)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const cls = {1:'active-weak',2:'active-medium',3:'active-medium',4:'active-strong'};
        const lbl = {1:'Lemah',2:'Sedang',3:'Baik',4:'Kuat'};
        const col = {1:'#ef4444',2:'#f59e0b',3:'#f59e0b',4:'#22c55e'};
        for (let i = 0; i < score; i++) bars[i].classList.add(cls[score]);
        text.textContent = lbl[score] || '';
        text.style.color = col[score] || '#94a3b8';
    }
</script>

</body>
</html>