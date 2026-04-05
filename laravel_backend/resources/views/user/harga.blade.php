{{--
  =====================================================
  SIMOPANG — User Data Harga
  File : resources/views/user/harga.blade.php
  Desc : Halaman tabel rincian harga komoditas
  =====================================================
--}}
@extends('user.layouts')

@section('title', 'Data Harga Komoditas')

@section('content')

  {{-- ── PAGE HEADER ───────────────────────────────── --}}
  <div class="u-page-header">
    <div>
      <h1>Data Harga Komoditas</h1>
      <p>Informasi transparan harga pasar harian untuk berbagai komoditas pangan utama.</p>
    </div>
  </div>

  {{-- ── FILTER BAR ────────────────────────────────── --}}
  <div class="u-filter-bar">

    {{-- Komoditas --}}
    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
        </svg>
        Komoditas
      </label>
      <select class="u-filter-select" name="komoditas">
        <option value="">Semua Komoditas</option>
        @isset($komoditas)
          @foreach($komoditas as $item)
            <option value="{{ $item->id }}"
              {{ ($selectedKomoditas ?? '') == $item->id ? 'selected' : '' }}>
              {{ $item->nama }}
            </option>
          @endforeach
        @else
          <option value="1">Beras Premium</option>
          <option value="2">Cabai Rawit</option>
          <option value="3">Bawang Merah</option>
          <option value="4">Telur Ayam</option>
        @endisset
      </select>
    </div>

    {{-- Daerah --}}
    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        Daerah
      </label>
      <select class="u-filter-select" name="daerah">
        <option value="">Semua Daerah</option>
        @isset($daerah)
          @foreach($daerah as $d)
            <option value="{{ $d->id }}"
              {{ ($selectedDaerah ?? '') == $d->id ? 'selected' : '' }}>
              {{ $d->nama }}
            </option>
          @endforeach
        @else
          <option value="1">Jember</option>
          <option value="2">Surabaya</option>
          <option value="3">Malang</option>
          <option value="4">Banyuwangi</option>
        @endisset
      </select>
    </div>

    {{-- Rentang Waktu --}}
    <div class="u-filter-date">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8"  y1="2" x2="8"  y2="6"/>
          <line x1="3"  y1="10" x2="21" y2="10"/>
        </svg>
        Rentang Waktu
      </label>
      <div class="u-filter-date-wrap">
        <input type="date" name="dari"   value="{{ $dari   ?? date('Y-m-01') }}">
        <span class="u-filter-date-sep">–</span>
        <input type="date" name="sampai" value="{{ $sampai ?? date('Y-m-d') }}">
      </div>
    </div>

    <button class="u-btn-filter" onclick="applyFilter(this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
      </svg>
      Tampilkan Data
    </button>

  </div>

  {{-- ── STAT CARDS ────────────────────────────────── --}}
  <div class="u-stat-row">

    <div class="u-stat-card u-stat-card--blue">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
        </div>
      </div>
      <div class="u-stat-card__label">
        Harga Terbaru <span style="font-weight:500;text-transform:none;letter-spacing:0">(Beras)</span>
      </div>
      <div class="u-stat-card__value">
        Rp {{ number_format($hargaTerbaru ?? 14500, 0, ',', '.') }}
        <span class="u-stat-card__unit">/kg</span>
      </div>
      <div class="u-stat-card__sub">07 Nov 2023 — Jember</div>
    </div>

    <div class="u-stat-card u-stat-card--green">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
            <polyline points="16 7 22 7 22 13"/>
          </svg>
        </div>
        <span class="u-stat-chip u-stat-chip--up">↑ +{{ $changePercent ?? '1.9' }}%</span>
      </div>
      <div class="u-stat-card__label">Perubahan Harian</div>
      <div class="u-stat-card__value">
        +Rp {{ number_format($hargaChange ?? 200, 0, ',', '.') }}
      </div>
      <div class="u-stat-card__sub">vs kemarin Rp {{ number_format($hargaKemarin ?? 14300, 0, ',', '.') }}</div>
    </div>

    <div class="u-stat-card u-stat-card--amber">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--amber">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
        </div>
        <span class="u-stat-chip u-stat-chip--low">Stabil</span>
      </div>
      <div class="u-stat-card__label">Status Volatilitas</div>
      <div class="u-stat-card__value" style="font-size:20px;letter-spacing:0;font-family:'Plus Jakarta Sans',sans-serif">
        {{ $statusVolatilitas ?? 'Rendah' }}
      </div>
      <div class="u-stat-card__sub">Indeks: {{ $indexVolatilitas ?? '0.38' }} (normal)</div>
    </div>

  </div>

  {{-- ── TABLE CARD ─────────────────────────────────── --}}
  <div class="u-table-card">

    <div class="u-table-card__header">
      <div class="u-table-card__title">Tabel Rincian Harga</div>
      <div class="u-table-actions">
        <button class="u-btn-csv" onclick="downloadCSV()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Unduh CSV
        </button>
        <button class="u-btn-print" onclick="window.print()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
          </svg>
          Cetak
        </button>
      </div>
    </div>

    <div class="u-table-wrap">
      <table class="u-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Komoditas</th>
            <th>Daerah</th>
            <th>Harga (Rp)</th>
            <th>Tren</th>
          </tr>
        </thead>
        <tbody>
          @isset($dataHarga)
            @forelse($dataHarga as $row)
            <tr>
              <td class="u-table__date">{{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}</td>
              <td class="u-table__komoditas">{{ $row->komoditas }}</td>
              <td class="u-table__daerah">{{ $row->daerah }}</td>
              <td class="u-table__harga">{{ number_format($row->harga, 0, ',', '.') }}</td>
              <td>
                @if($row->tren > 0)
                  <span class="u-tren-badge u-tren-badge--up">↑ +{{ number_format($row->tren, 0, ',', '.') }}</span>
                @elseif($row->tren < 0)
                  <span class="u-tren-badge u-tren-badge--down">↓ {{ number_format($row->tren, 0, ',', '.') }}</span>
                @else
                  <span class="u-tren-badge u-tren-badge--flat">→ 0</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align:center;padding:40px;color:var(--text-3);font-size:13px">
                Tidak ada data untuk filter yang dipilih.
              </td>
            </tr>
            @endforelse
          @else
            <tr>
              <td class="u-table__date">07 Nov 2023</td>
              <td class="u-table__komoditas">Beras Premium</td>
              <td class="u-table__daerah">Jember</td>
              <td class="u-table__harga">14.500</td>
              <td><span class="u-tren-badge u-tren-badge--up">↑ +200</span></td>
            </tr>
            <tr>
              <td class="u-table__date">07 Nov 2023</td>
              <td class="u-table__komoditas">Cabai Rawit</td>
              <td class="u-table__daerah">Jember</td>
              <td class="u-table__harga">65.000</td>
              <td><span class="u-tren-badge u-tren-badge--down">↓ -1.500</span></td>
            </tr>
            <tr>
              <td class="u-table__date">07 Nov 2023</td>
              <td class="u-table__komoditas">Bawang Merah</td>
              <td class="u-table__daerah">Surabaya</td>
              <td class="u-table__harga">32.000</td>
              <td><span class="u-tren-badge u-tren-badge--flat">→ 0</span></td>
            </tr>
            <tr>
              <td class="u-table__date">06 Nov 2023</td>
              <td class="u-table__komoditas">Beras Premium</td>
              <td class="u-table__daerah">Jember</td>
              <td class="u-table__harga">14.300</td>
              <td><span class="u-tren-badge u-tren-badge--up">↑ +300</span></td>
            </tr>
            <tr>
              <td class="u-table__date">06 Nov 2023</td>
              <td class="u-table__komoditas">Telur Ayam</td>
              <td class="u-table__daerah">Banyuwangi</td>
              <td class="u-table__harga">27.500</td>
              <td><span class="u-tren-badge u-tren-badge--down">↓ -500</span></td>
            </tr>
          @endisset
        </tbody>
      </table>
    </div>

    <div class="u-table-footer">
      <span class="u-table-footer__info">
        Menampilkan 5 dari {{ $totalData ?? '150' }} data
      </span>
      <div class="u-pagination">
        <button class="u-page-btn" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
               style="width:12px;height:12px">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </button>
        <button class="u-page-btn active">1</button>
        <button class="u-page-btn">2</button>
        <button class="u-page-btn">3</button>
        <button class="u-page-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
               style="width:12px;height:12px">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </button>
      </div>
    </div>

  </div>

  {{-- ── INFO BAR ───────────────────────────────────── --}}
  <div class="u-info-bar">
    <div class="u-info-bar__icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
    </div>
    <div class="u-info-bar__text">
      <strong>Data Resmi &amp; Terverifikasi</strong>
      <p>Seluruh data yang ditampilkan bersumber dari pasar inkar dan dikelola melalui infrastruktur MongoDB.</p>
    </div>
    <div class="u-info-bar__actions">
      <button class="u-btn-help">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
          <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        Bantuan Data
      </button>
    </div>
  </div>

@endsection

@push('scripts')
<script>
function applyFilter(btn) {
  const orig = btn.innerHTML;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round"
         style="animation:spin .7s linear infinite;width:14px;height:14px">
      <path d="M21 12a9 9 0 11-6.22-8.56"/>
    </svg> Memuat...`;
  btn.disabled = true;
  setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 1500);
}

function downloadCSV() {
  /* TODO: implement CSV export */
  alert('Mengunduh data CSV...');
}

document.querySelectorAll('.u-page-btn:not([disabled])').forEach(btn => {
  btn.addEventListener('click', function () {
    if (this.querySelector('svg')) return;
    document.querySelectorAll('.u-page-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>
@endpush