<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — SIMOPANG</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/register-user.css') }}" rel="stylesheet">
</head>
<body>

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
        <a href="#"><i class="fas fa-circle-question"></i><span>Bantuan</span></a>
        <a href="#"><i class="fas fa-book"></i><span>Dokumentasi</span></a>
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
                <div class="secure-label">Akun Pengguna</div>
                <div class="user-avatar-wrap">
                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                    <div class="user-avatar-badge"><i class="fas fa-check"></i></div>
                </div>
                <div class="icon-row">
                    <div class="icon-item active"><i class="fas fa-magnifying-glass-chart"></i></div>
                    <div class="icon-item active"><i class="fas fa-tags"></i></div>
                    <div class="icon-item"><i class="fas fa-bell"></i></div>
                    <div class="icon-item"><i class="fas fa-star"></i></div>
                </div>
                <div class="fake-field"><div class="fake-field-dot"></div><div class="fake-field-line"></div></div>
                <div class="fake-field"><div class="fake-field-dot"></div><div class="fake-field-line short"></div></div>
                <div class="fake-btn-wrap">
                    <div class="fake-btn"><div class="fake-btn-line"></div><div class="fake-btn-dot"></div></div>
                </div>
                <div class="card-dots"><span></span><span></span><span></span><span></span></div>
            </div>

            <div class="left-title">Bergabung Sekarang</div>
            <div class="left-desc">Pantau harga komoditas dan prediksi pasar secara real-time dengan akun SIMOPANG Anda.</div>

            <div class="user-features">
                <div class="user-feature-item">
                    <div class="user-feature-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="user-feature-text">Pantau prediksi harga komoditas</div>
                </div>
                <div class="user-feature-item">
                    <div class="user-feature-icon"><i class="fas fa-bell"></i></div>
                    <div class="user-feature-text">Notifikasi perubahan harga otomatis</div>
                </div>
                <div class="user-feature-item">
                    <div class="user-feature-icon"><i class="fas fa-download"></i></div>
                    <div class="user-feature-text">Unduh laporan & data historis</div>
                </div>
            </div>

            <div class="left-badge"><div class="pulse-dot"></div>Akses Gratis</div>
        </div>

        <div class="register-right">
            <div class="form-tag"><i class="fas fa-user-plus" style="font-size:10px"></i> Registrasi Pengguna</div>
            <div class="form-heading">Buat Akun Baru</div>
            <div class="form-subheading">Lengkapi data berikut untuk mulai menggunakan<br>layanan SIMOPANG secara gratis.</div>
            <div class="form-divider"></div>

            @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
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
                        placeholder="contoh@gmail.com"
                        pattern="[a-zA-Z0-9._%+\-]+@gmail\.com"
                        title="Gunakan akun email anda"
                        value="{{ old('email') }}" required>
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
                            placeholder="Minimal 6 karakter" oninput="checkStrength(this.value)">
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

                    <div class="pass-hints" id="passHints">
                        <div class="hint-item" id="hint-length"><i class="fas fa-circle-dot"></i> Minimal 6 karakter</div>
                        <div class="hint-item" id="hint-upper"><i class="fas fa-circle-dot"></i> Huruf besar (A-Z)</div>
                        <div class="hint-item" id="hint-lower"><i class="fas fa-circle-dot"></i> Huruf kecil (a-z)</div>
                        <div class="hint-item" id="hint-number"><i class="fas fa-circle-dot"></i> Angka (0-9)</div>
                        <div class="hint-item" id="hint-special"><i class="fas fa-circle-dot"></i> Karakter spesial</div>
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
                    Daftar Sekarang <i class="fas fa-arrow-right"></i>
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
        const wrap  = document.getElementById('strengthWrap');
        const bars  = ['bar1','bar2','bar3','bar4'].map(id => document.getElementById(id));
        const text  = document.getElementById('strengthText');
        const hints = document.getElementById('passHints');

        if (!val) { wrap.style.display = 'none'; hints.style.display = 'none'; return; }
        wrap.style.display  = 'flex';
        hints.style.display = 'flex';

        const setHint = (id, ok) => document.getElementById(id).classList.toggle('hint-ok', ok);
        setHint('hint-length',  val.length >= 6);
        setHint('hint-upper',   /[A-Z]/.test(val));
        setHint('hint-lower',   /[a-z]/.test(val));
        setHint('hint-number',  /[0-9]/.test(val));
        setHint('hint-special', /[^A-Za-z0-9]/.test(val));

        bars.forEach(b => b.className = 'bar');
        let score = 0;
        if (val.length >= 6)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const cls = {1:'active-weak', 2:'active-medium', 3:'active-medium', 4:'active-strong'};
        const lbl = {1:'Lemah', 2:'Sedang', 3:'Baik', 4:'Kuat'};
        const col = {1:'#ef4444', 2:'#f59e0b', 3:'#f59e0b', 4:'#22c55e'};
        for (let i = 0; i < score; i++) bars[i].classList.add(cls[score]);
        text.textContent = lbl[score] || '';
        text.style.color = col[score] || '#94a3b8';
    }
</script>

</body>
</html>