<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO-2.png') }}">
    <title>Reset Password — SIMOPANG</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/reset-password.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar">
   <a href="/" class="navbar-brand">
    <img src="{{ asset('images/LOGO-2.png') }}" alt="SIMOPANG Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px;">
    <span class="brand-name">SIMOPANG</span>
    </a>
    <div class="navbar-links">
        <a href="/" class="btn-back">
            <i class="fas fa-house"></i>
            <span class="back-text">Beranda</span>
        </a>
    </div>
    <div class="navbar-links">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i>
            <span>Ke Login</span>
        </a>
    </div>

</nav>

<div class="page-wrapper">
    <div class="auth-card">

        <div class="card-left">
            <div class="left-illustration">
                <div class="lock-wrap">
                    <div class="lock-shackle"></div>
                    <div class="lock-body">
                        <i class="fas fa-lock-open" id="lock-icon"></i>
                    </div>
                    <div class="lock-glow"></div>
                </div>
                <div class="bar-row">
                    <div class="bar-item">
                        <div class="bar-label">Keamanan Password</div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:0%" id="sec-bar"></div>
                        </div>
                        <div class="bar-val" id="sec-val">—</div>
                    </div>
                </div>
            </div>

            <div class="left-title">Buat Password Baru</div>
            <div class="left-desc">
                Langkah terakhir! Buat password yang<br>
                kuat untuk melindungi akunmu.
            </div>

            <div class="left-steps">
                <div class="step done">
                    <div class="step-num"><i class="fas fa-check"></i></div>
                    <span>Masukkan Email</span>
                </div>
                <div class="step-line done-line"></div>
                <div class="step done">
                    <div class="step-num"><i class="fas fa-check"></i></div>
                    <span>Verifikasi OTP</span>
                </div>
                <div class="step-line done-line"></div>
                <div class="step active">
                    <div class="step-num">3</div>
                    <span>Reset Password</span>
                </div>
            </div>
        </div>

        <div class="card-right">
            <div class="page-icon">
                <i class="fas fa-key"></i>
            </div>

            <div class="form-heading">Reset Password</div>
            <div class="form-subheading">
                Buat password baru yang kuat untuk akun<br>
                <strong>{{ $email ?? session('email') ?? 'Anda' }}</strong>.
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

            <form method="POST" action="/reset-password" id="reset-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? session('email') }}">

                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password" id="password" name="password"
                            class="form-input"
                            placeholder="Minimal 6 karakter"
                            required minlength="6"
                            oninput="checkStrength(this.value)"
                        >
                        <button type="button" class="toggle-password" onclick="toggleVis('password','eye1')">
                            <i class="fas fa-eye" id="eye1"></i>
                        </button>
                    </div>

                    <div class="strength-wrap" id="strength-wrap" style="display:none">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <span class="strength-label" id="strength-label"></span>
                    </div>

                    <ul class="req-list" id="req-list">
                        <li id="req-len"><i class="fas fa-circle-xmark"></i> Minimal 6 karakter</li>
                        <li id="req-upper"><i class="fas fa-circle-xmark"></i> Huruf kapital</li>
                        <li id="req-num"><i class="fas fa-circle-xmark"></i> Angka</li>
                        <li id="req-sym"><i class="fas fa-circle-xmark"></i> Simbol (!@#$...)</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon" id="confirm-icon-left"></i>
                        <input
                            type="password" id="password_confirmation" name="password_confirmation"
                            class="form-input"
                            placeholder="Ulangi password baru"
                            required
                            oninput="checkMatch()"
                        >
                        <button type="button" class="toggle-password" onclick="toggleVis('password_confirmation','eye2')">
                            <i class="fas fa-eye" id="eye2"></i>
                        </button>
                    </div>
                    <div class="match-msg" id="match-ok" style="display:none">
                        <i class="fas fa-circle-check"></i> Password cocok
                    </div>
                    <div class="match-msg error" id="match-err" style="display:none">
                        <i class="fas fa-circle-xmark"></i> Password tidak cocok
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btn-submit">
                    <span id="btn-text">Simpan Password Baru</span>
                    <i class="fas fa-shield-check" id="btn-icon"></i>
                </button>
            </form>

            <div class="powered-by">Powered by <span>SIMOPANG</span> Core</div>
        </div>

    </div>
</div>

<footer class="footer">© 2024 SIMOPANG. Hak Cipta Dilindungi Undang-Undang.</footer>

<script>
    function toggleVis(id, eyeId) {
        const input = document.getElementById(id);
        const eye   = document.getElementById(eyeId);
        input.type    = input.type === 'password' ? 'text' : 'password';
        eye.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    function checkStrength(val) {
        const wrap     = document.getElementById('strength-wrap');
        const fill     = document.getElementById('strength-fill');
        const label    = document.getElementById('strength-label');
        const bar      = document.getElementById('sec-bar');
        const secVal   = document.getElementById('sec-val');
        const lockIcon = document.getElementById('lock-icon');

        if (!val.length) { wrap.style.display = 'none'; return; }
        wrap.style.display = 'flex';

        const checks = {
            len:   val.length >= 8,
            upper: /[A-Z]/.test(val),
            num:   /[0-9]/.test(val),
            sym:   /[^A-Za-z0-9]/.test(val),
        };

        setReq('req-len',   checks.len);
        setReq('req-upper', checks.upper);
        setReq('req-num',   checks.num);
        setReq('req-sym',   checks.sym);

        const score = Object.values(checks).filter(Boolean).length;
        const levels = [
            { pct:'25%',  lbl:'Lemah',   color:'#ef4444' },
            { pct:'50%',  lbl:'Cukup',   color:'#f59e0b' },
            { pct:'75%',  lbl:'Bagus',   color:'#3b82f6' },
            { pct:'100%', lbl:'Kuat 🔒', color:'#22c55e' },
        ];
        const lvl = levels[score - 1] || levels[0];

        fill.style.width      = lvl.pct;
        fill.style.background = lvl.color;
        label.textContent     = lvl.lbl;
        label.style.color     = lvl.color;

        bar.style.width       = lvl.pct;
        bar.style.background  = lvl.color;
        secVal.textContent    = lvl.lbl;
        secVal.style.color    = lvl.color;

        lockIcon.className = score === 4 ? 'fas fa-lock' : 'fas fa-lock-open';

        checkMatch();
    }

    function setReq(id, ok) {
        const el = document.getElementById(id);
        el.className = ok ? 'ok' : '';
        el.querySelector('i').className = ok ? 'fas fa-circle-check' : 'fas fa-circle-xmark';
    }

    function checkMatch() {
        const p1  = document.getElementById('password').value;
        const p2  = document.getElementById('password_confirmation').value;
        const ok  = document.getElementById('match-ok');
        const err = document.getElementById('match-err');
        if (!p2) { ok.style.display = 'none'; err.style.display = 'none'; return; }
        if (p1 === p2) { ok.style.display = 'flex'; err.style.display = 'none'; }
        else           { ok.style.display = 'none'; err.style.display = 'flex'; }
    }

    document.getElementById('reset-form').addEventListener('submit', function(e) {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('password_confirmation').value;
        if (p1 !== p2 || p1.length < 8) { e.preventDefault(); return; }
        const btn  = document.getElementById('btn-submit');
        const text = document.getElementById('btn-text');
        const icon = document.getElementById('btn-icon');
        btn.disabled     = true;
        text.textContent = 'Menyimpan...';
        icon.className   = 'fas fa-spinner fa-spin';
    });
</script>

</body>
</html>
