@extends('layouts.layout')

@section('title', 'Edit Commodity')
@section('page-title', 'Edit Commodity')
@section('page-sub', 'Update commodity information.')

@section('content')

{{-- ===== FORM EDIT COMMODITY ===== --}}
<div class="table-card" style="max-width:660px">
    <div class="table-header">
        <div>
            <div class="table-title">Edit: {{ $commodity->name }}</div>
            <div class="table-subtitle">Update the commodity name and category.</div>
        </div>
    </div>

    @if($errors->any())
    <div style="margin:1rem 1.5rem 0; padding:.75rem 1rem;
                background:#fef2f2; color:#991b1b;
                border:1px solid #fecaca; border-radius:10px;
                font-size:13px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-circle-exclamation"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="/admin/komoditas/{{ $commodity->id }}"
          style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf
        @method('PUT')

        <div class="form-group-admin">
            <label class="form-label-admin">Commodity Name</label>
            <input type="text" name="name" class="form-input-admin"
                   value="{{ old('name', $commodity->name) }}" required>
        </div>

        <div class="form-group-admin">
            <label class="form-label-admin">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id', (string) $commodity->category_id) == (string) $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-actions">
            <a href="/admin/komoditas" class="btn-secondary">
                <i class="fas fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-floppy-disk"></i> Update Commodity
            </button>
        </div>
    </form>
</div>

{{-- ===== FORM EDIT HARGA ===== --}}
<div class="table-card" style="max-width:660px; margin-top:1.5rem;">

    <div class="table-header">
        <div>
            <div class="table-title">📊 Data Harga</div>
            <div class="table-subtitle">Edit atau tambah data harga bulanan.</div>
        </div>
    </div>

    {{-- Info tahun tersimpan --}}
    @if(count($hargaPerTahun) > 0)
    <div style="margin:0 1.5rem; padding:.875rem 1rem;
                background:#eff6ff; color:#1e40af;
                border:1px solid #bfdbfe; border-radius:10px;
                font-size:13px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:.5rem;">
            <i class="fas fa-circle-info" style="flex-shrink:0;"></i>
            <span style="font-weight:600;">Data harga sudah tersedia untuk tahun:</span>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:6px; padding-left:22px;">
            @foreach(array_keys($hargaPerTahun) as $y)
                <span style="display:inline-flex; align-items:center; gap:4px;
                             padding:3px 12px;
                             background:#dbeafe; color:#1e40af;
                             border-radius:999px; font-weight:700; font-size:12px;">
                    <i class="fas fa-check" style="font-size:9px;"></i> {{ $y }}
                </span>
            @endforeach
        </div>
        <div style="padding-left:22px; margin-top:.5rem; color:#3b82f6; font-size:12px;">
            Pilih tahun di bawah untuk melihat atau mengedit data harga.
        </div>
    </div>
    @endif

    <div id="alert-harga" style="margin:.75rem 1.5rem 0;"></div>

    <form id="form-harga" style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf

        @php
            $satuanGlobal = collect($satuanPerTahun)->first() ?? '-';
        @endphp

        {{-- Row: Tahun & Satuan --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; align-items:start;">

            <div class="form-group-admin" style="margin:0;">
                <label class="form-label-admin">Tahun</label>
                <select name="year" id="select-year" class="form-select" required>
                    <option value="">-- Pilih Tahun --</option>
                    @for($y = 2021; $y <= 2025; $y++)
                        <option value="{{ $y }}"
                                data-harga="{{ json_encode($hargaPerTahun[$y] ?? []) }}"
                                data-exists="{{ isset($hargaPerTahun[$y]) ? 'true' : 'false' }}">
                            {{ $y }}{{ isset($hargaPerTahun[$y]) ? ' ✓' : '' }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="form-group-admin" style="margin:0;">
                <label class="form-label-admin" style="display:flex; align-items:center; gap:6px;">
                    Satuan
                    <span style="font-size:10px; background:#e5e7eb; color:#6b7280;
                                 padding:1px 7px; border-radius:999px; font-weight:600;
                                 letter-spacing:.3px;">
                        READONLY
                    </span>
                </label>
                <input type="hidden" name="satuan" value="{{ $satuanGlobal }}">
                <input type="text"
                       id="display-satuan"
                       class="form-input-admin"
                       value="{{ $satuanGlobal }}"
                       readonly
                       style="background:#f9fafb; color:#9ca3af;
                              cursor:not-allowed; border-color:#e5e7eb;">
            </div>

        </div>

        {{-- Divider --}}
        <div style="border-top:1px solid #f3f4f6; margin:0 -.25rem;"></div>

        {{-- Grid 12 Bulan --}}
        <div class="form-group-admin" style="margin:0;">
            <label class="form-label-admin" style="margin-bottom:.875rem; font-size:13px;">
                Harga per Bulan (Rp)
                <span id="label-year-selected"
                      style="margin-left:6px; font-weight:400; color:#9ca3af; font-size:12px;">
                    — pilih tahun dulu
                </span>
            </label>
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:.75rem;">
                @php
                    $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                              'Juli','Agustus','September','Oktober','November','Desember'];
                @endphp
                @foreach($bulan as $i => $nama)
                <div>
                    <label style="font-size:11px; color:#6b7280; font-weight:600;
                                  display:block; margin-bottom:4px; text-transform:uppercase;
                                  letter-spacing:.4px;">
                        {{ $nama }}
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:.65rem; top:50%; transform:translateY(-50%);
                                     font-size:12px; color:#9ca3af; pointer-events:none;">
                            Rp
                        </span>
                        <input type="number"
                               name="harga[{{ $i }}]"
                               id="harga-{{ $i }}"
                               class="form-input-admin"
                               style="font-size:13px; padding:.5rem .65rem .5rem 2rem;"
                               placeholder="—"
                               min="0"
                               step="any"
                               required>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div style="border-top:1px solid #f3f4f6; padding-top:1rem;">
            <div class="form-actions">
                <a href="/admin/komoditas" class="btn-secondary">
                    <i class="fas fa-xmark"></i> Kembali
                </a>
                <button type="submit" class="btn-primary" id="btn-save-harga">
                    <i class="fas fa-floppy-disk"></i> Simpan Harga Tahun Ini
                </button>
            </div>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const commodityId = "{{ $commodity->id }}";

    // ── Helper alert ───────────────────────────────────────────
    function showAlert(type, msg) {
        const colors = {
            error:   { bg:'#fef2f2', text:'#991b1b', border:'#fecaca', icon:'fa-circle-exclamation' },
            success: { bg:'#f0fdf4', text:'#166534', border:'#bbf7d0', icon:'fa-circle-check' },
        };
        const c = colors[type];
        document.getElementById('alert-harga').innerHTML = `
            <div style="padding:.75rem 1rem; background:${c.bg}; color:${c.text};
                        border:1px solid ${c.border}; border-radius:10px;
                        font-size:13px; display:flex; align-items:center; gap:8px;">
                <i class="fas ${c.icon}"></i> ${msg}
            </div>`;
    }

    function clearAlert() {
        document.getElementById('alert-harga').innerHTML = '';
    }

    // ── Isi form saat tahun dipilih ────────────────────────────
    document.getElementById('select-year').addEventListener('change', function () {
        const year     = this.value;
        const opt      = this.options[this.selectedIndex];
        const hargaMap = JSON.parse(opt.dataset.harga || '{}');

        // Update label tahun di atas grid
        const labelYear = document.getElementById('label-year-selected');
        labelYear.textContent = year ? `— Tahun ${year}` : '— pilih tahun dulu';

        // Isi 12 field bulan
        for (let i = 0; i < 12; i++) {
            const month = i + 1;
            const input = document.getElementById(`harga-${i}`);
            input.value = hargaMap[month] !== undefined ? hargaMap[month] : '';
        }

        clearAlert();
    });

    // ── Submit form harga (AJAX) ───────────────────────────────
    document.getElementById('form-harga').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearAlert();

        const year = document.getElementById('select-year').value;
        if (!year) {
            showAlert('error', 'Pilih tahun terlebih dahulu.');
            return;
        }

        const btn = document.getElementById('btn-save-harga');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch(`/admin/komoditas/${commodityId}/harga`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this),
            });

            const data = await res.json();

            if (!res.ok) {
                const errMsg = data.errors
                    ? Object.values(data.errors).flat().join(', ')
                    : (data.message || 'Terjadi kesalahan.');
                showAlert('error', errMsg);
            } else if (data.success) {
                showAlert('success', data.message);

                // Sync data-harga di option tanpa reload
                const formData = new FormData(this);
                const opt      = document.querySelector(`#select-year option[value="${year}"]`);
                const newHarga = {};
                for (let i = 0; i < 12; i++) {
                    newHarga[i + 1] = parseFloat(formData.get(`harga[${i}]`)) || 0;
                }
                opt.dataset.harga  = JSON.stringify(newHarga);
                opt.dataset.exists = 'true';
                if (!opt.textContent.includes('✓')) {
                    opt.textContent = `${year} ✓`;
                }
            }
        } catch (err) {
            showAlert('error', 'Gagal terhubung ke server. Coba lagi.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Harga Tahun Ini';
        }
    });
})();
</script>
@endpush
