@extends('layouts.layout')

@section('title', 'Add Commodity')
@section('page-title', 'Add Commodity')
@section('page-sub', 'Add a new commodity to the SIMOPANG system.')

@section('content')

{{-- ===== FORM COMMODITY ===== --}}
<div class="table-card" style="max-width:660px" id="card-commodity">

    <div class="table-header">
        <div>
            <div class="table-title">New Commodity</div>
            <div class="table-subtitle">Fill in the details below to register a new commodity.</div>
        </div>
    </div>

    <div id="alert-commodity"></div>

    <form id="form-commodity" style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf

        <div class="form-group-admin">
            <label class="form-label-admin">Commodity Name</label>
            <input type="text" name="name" class="form-input-admin"
                   placeholder="e.g. Beras Premium" required>
        </div>

        <div class="form-group-admin">
            <label class="form-label-admin">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-actions">
            <a href="/admin/komoditas" class="btn-secondary">
                <i class="fas fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="btn-primary" id="btn-save-commodity">
                <i class="fas fa-plus"></i> Save Commodity
            </button>
        </div>
    </form>
</div>

{{-- ===== FORM HARGA (muncul setelah commodity tersimpan) ===== --}}
<div class="table-card" style="max-width:660px; margin-top:1.5rem; display:none;" id="card-harga">

    <div class="table-header">
        <div>
            <div class="table-title">📊 Input Data Harga</div>
            <div class="table-subtitle" id="harga-subtitle">Masukkan data harga bulanan untuk komoditas ini.</div>
        </div>
    </div>

    {{-- Info komoditas yang baru dibuat --}}
    <div id="info-commodity"
         style="margin:0 1.5rem; padding:.75rem 1rem;
                background:#eff6ff; color:#1e40af;
                border:1px solid #bfdbfe; border-radius:10px;
                font-size:13px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-circle-check"></i>
        <span id="info-commodity-text"></span>
    </div>

    {{-- Alert harga --}}
    <div id="alert-harga" style="margin:.75rem 1.5rem 0;"></div>

    {{-- Tahun-tahun yang sudah disimpan --}}
    <div id="saved-years-container" style="margin:.75rem 1.5rem 0; display:none;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:.5rem; font-weight:600;">
            TAHUN TERSIMPAN:
        </div>
        <div id="saved-years-badges" style="display:flex; gap:.5rem; flex-wrap:wrap;"></div>
    </div>

    <form id="form-harga" style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf

        {{-- Pilih Tahun & Satuan --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group-admin">
                <label class="form-label-admin">Tahun</label>
                <select name="year" id="select-year" class="form-select" required>
                    <option value="">-- Pilih Tahun --</option>
                    @for($y = 2021; $y <= 2025; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group-admin">
                <label class="form-label-admin">Satuan</label>
                <input type="text" name="satuan" class="form-input-admin"
                       placeholder="e.g. Kg, Liter, Ikat" required>
            </div>
        </div>

        {{-- Grid 12 Bulan --}}
        <div class="form-group-admin">
            <label class="form-label-admin" style="margin-bottom:.75rem;">
                Harga per Bulan (Rp)
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.75rem;">
                @php
                    $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                              'Juli','Agustus','September','Oktober','November','Desember'];
                @endphp
                @foreach($bulan as $i => $nama)
                <div>
                    <label style="font-size:11px; color:#6b7280; font-weight:600;
                                  display:block; margin-bottom:4px;">
                        {{ $nama }}
                    </label>
                    <input type="number"
                           name="harga[{{ $i }}]"
                           class="form-input-admin"
                           style="font-size:13px; padding:.5rem .75rem;"
                           placeholder="0"
                           min="0"
                           step="any"
                           required>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <a href="/admin/komoditas" class="btn-secondary">
                <i class="fas fa-check"></i> Selesai
            </a>
            <button type="submit" class="btn-primary" id="btn-save-harga">
                <i class="fas fa-floppy-disk"></i> Simpan Harga Tahun Ini
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ── State ──────────────────────────────────────────────────
    let commodityId   = null;
    let savedYears    = [];

    // ── Helper: tampilkan alert ────────────────────────────────
    function showAlert(containerId, type, msg) {
        // type: 'error' | 'success' | 'info'
        const colors = {
            error:   { bg:'#fef2f2', text:'#991b1b', border:'#fecaca', icon:'fa-circle-exclamation' },
            success: { bg:'#f0fdf4', text:'#166534', border:'#bbf7d0', icon:'fa-circle-check' },
            info:    { bg:'#eff6ff', text:'#1e40af', border:'#bfdbfe', icon:'fa-circle-info' },
        };
        const c = colors[type];
        document.getElementById(containerId).innerHTML = `
            <div style="padding:.75rem 1rem; background:${c.bg}; color:${c.text};
                        border:1px solid ${c.border}; border-radius:10px;
                        font-size:13px; display:flex; align-items:center; gap:8px;">
                <i class="fas ${c.icon}"></i> ${msg}
            </div>`;
    }

    function clearAlert(containerId) {
        document.getElementById(containerId).innerHTML = '';
    }

    // ── Helper: badge tahun tersimpan ──────────────────────────
    function addSavedYearBadge(year) {
        savedYears.push(year);

        // disable option di select
        const opt = document.querySelector(`#select-year option[value="${year}"]`);
        if (opt) {
            opt.disabled = true;
            opt.textContent = `${year} ✓`;
        }

        // tampilkan badge
        const badge = document.createElement('span');
        badge.style.cssText = `
            display:inline-flex; align-items:center; gap:4px;
            padding:4px 10px; border-radius:999px;
            background:#d1fae5; color:#065f46;
            font-size:12px; font-weight:600;`;
        badge.innerHTML = `<i class="fas fa-check" style="font-size:10px;"></i> ${year}`;
        document.getElementById('saved-years-badges').appendChild(badge);

        document.getElementById('saved-years-container').style.display = 'block';

        // reset form & pilih tahun berikutnya otomatis
        document.getElementById('form-harga').reset();
        const nextYear = year + 1;
        if (nextYear <= 2025) {
            const nextOpt = document.querySelector(`#select-year option[value="${nextYear}"]`);
            if (nextOpt && !nextOpt.disabled) {
                document.getElementById('select-year').value = nextYear;
            }
        }
    }

    // ── Submit Form Commodity (AJAX) ───────────────────────────
    document.getElementById('form-commodity').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearAlert('alert-commodity');

        const btn = document.getElementById('btn-save-commodity');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(this);

        try {
            const res = await fetch('/admin/komoditas', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });

            const data = await res.json();

            if (!res.ok) {
                // Validasi error dari Laravel
                const errMsg = data.errors
                    ? Object.values(data.errors).flat().join(', ')
                    : (data.message || 'Terjadi kesalahan.');
                showAlert('alert-commodity', 'error', errMsg);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus"></i> Save Commodity';
                return;
            }

            if (data.success) {
                commodityId = data.commodity_id;

                // Kunci form commodity
                this.querySelectorAll('input, select, button[type="submit"]')
                    .forEach(el => el.disabled = true);
                showAlert('alert-commodity', 'success',
                    `Komoditas <strong>${data.commodity_name}</strong> berhasil disimpan! Silakan input data harga di bawah.`);

                // Tampilkan info di card harga
                document.getElementById('info-commodity-text').textContent =
                    `Komoditas: ${data.commodity_name} · Kategori: ${data.category}`;

                // Tampilkan card harga dengan animasi
                const cardHarga = document.getElementById('card-harga');
                cardHarga.style.display = 'block';
                cardHarga.style.opacity = '0';
                cardHarga.style.transform = 'translateY(16px)';
                cardHarga.style.transition = 'opacity .4s ease, transform .4s ease';
                requestAnimationFrame(() => {
                    cardHarga.style.opacity = '1';
                    cardHarga.style.transform = 'translateY(0)';
                });
                cardHarga.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (err) {
            showAlert('alert-commodity', 'error', 'Gagal terhubung ke server. Coba lagi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Save Commodity';
        }
    });

    // ── Submit Form Harga (AJAX) ───────────────────────────────
    document.getElementById('form-harga').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearAlert('alert-harga');

        if (!commodityId) {
            showAlert('alert-harga', 'error', 'Commodity belum tersimpan.');
            return;
        }

        const year = parseInt(document.getElementById('select-year').value);
        if (!year) {
            showAlert('alert-harga', 'error', 'Pilih tahun terlebih dahulu.');
            return;
        }

        if (savedYears.includes(year)) {
            showAlert('alert-harga', 'error', `Harga tahun ${year} sudah disimpan.`);
            return;
        }

        const btn = document.getElementById('btn-save-harga');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(this);

        try {
            const res = await fetch(`/admin/komoditas/${commodityId}/harga`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });

            const data = await res.json();

            if (!res.ok) {
                const errMsg = data.errors
                    ? Object.values(data.errors).flat().join(', ')
                    : (data.message || 'Terjadi kesalahan.');
                showAlert('alert-harga', 'error', errMsg);
            } else if (data.success) {
                showAlert('alert-harga', 'success', data.message);
                addSavedYearBadge(year);
            }
        } catch (err) {
            showAlert('alert-harga', 'error', 'Gagal terhubung ke server. Coba lagi.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Harga Tahun Ini';
        }
    });
})();
</script>
@endpush
