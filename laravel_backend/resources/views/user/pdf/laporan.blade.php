<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Laporan Harga Komoditas – SIMOPANG</title>
<style>
  /* ── Reset & Base ── */
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'DejaVu Sans', 'Arial', sans-serif;
    font-size: 9pt;
    color: #111827;
    background: #fff;
  }

  /* ── Page Setup ── */
  @page {
    margin: 0;
    size: A4 portrait;
  }

  /* ── Header ── */
  .page-header {
    background: #fff7ed;
    padding: 14px 20px 10px 20px;
    border-bottom: 3px solid #f97316;
    width: 100%;
  }
  .header-top-stripe {
    background: #f97316;
    height: 6px;
    width: 100%;
    margin-bottom: 0;
  }
  .header-inner {
    display: flex; /* DomPDF: use table */
    justify-content: space-between;
    align-items: center;
  }
  .header-table { width:100%; border-collapse: collapse; }
  .header-logo-cell { width: 50%; vertical-align: middle; }
  .header-right-cell { width: 50%; text-align: right; vertical-align: middle; }

  .logo-circle {
    display:inline-block;
    width: 36px; height: 36px;
    background: #f97316;
    border-radius: 50%;
    text-align: center;
    line-height: 36px;
    color: #fff;
    font-weight: 700;
    font-size: 9pt;
    vertical-align: middle;
    margin-right: 8px;
  }
  .sys-title   { font-size: 14pt; font-weight: 700; color: #111827; }
  .sys-subtitle{ font-size: 8pt;  color: #6b7280; }
  .report-title{ font-size: 11pt; font-weight: 700; color: #111827; }
  .report-date { font-size: 7.5pt; color: #6b7280; margin-top: 2px; }

  /* ── Body ── */
  .body-wrap { padding: 16px 20px 20px 20px; }

  /* ── Meta bar ── */
  .meta-table { width:100%; border-collapse: collapse; margin-bottom: 14px; }
  .meta-table th {
    background: #f3f4f6; color: #6b7280; font-size: 7.5pt;
    font-weight: 400; text-align: center;
    padding: 5px 8px; border: 0.5px solid #e5e7eb;
  }
  .meta-table td {
    font-size: 9pt; font-weight: 700; text-align: center;
    padding: 6px 8px; border: 0.5px solid #e5e7eb;
    color: #111827;
  }
  .badge-aktif {
    background: #dcfce7; color: #16a34a;
    padding: 2px 8px; border-radius: 4px; font-size: 8pt;
  }

  /* ── Section titles ── */
  .section-title {
    font-size: 10.5pt; font-weight: 700; color: #111827;
    margin-bottom: 3px; margin-top: 14px;
    border-left: 3px solid #f97316;
    padding-left: 7px;
  }
  .section-sub { font-size: 8pt; color: #6b7280; margin-bottom: 8px; padding-left: 10px; }

  /* ── Stat cards ── */
  .stat-table { width:100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 0; }
  .stat-card-cell {
    border: 0.8px solid #e5e7eb;
    border-top: 3px solid #f97316;
    padding: 8px 10px 8px 10px;
    width: 33.33%;
    vertical-align: top;
  }
  .stat-label { font-size: 7.5pt; color: #6b7280; margin-bottom: 4px; }
  .stat-value { font-size: 16pt; font-weight: 700; color: #111827; line-height: 1.2; }
  .stat-change-up   { font-size: 8pt; color: #16a34a; margin-top: 4px; }
  .stat-change-down { font-size: 8pt; color: #dc2626; margin-top: 4px; }
  .stat-change-neutral{ font-size: 8pt; color: #6b7280; margin-top: 4px; }

  /* ── Data table (prices) ── */
  .data-table { width:100%; border-collapse: collapse; margin-bottom: 0; }
  .data-table thead tr th {
    background: #f97316;
    color: #fff;
    font-size: 8.5pt;
    font-weight: 700;
    padding: 7px 8px;
    text-align: center;
    border-bottom: 1.5px solid #ea6c0a;
  }
  .data-table thead tr th:first-child { text-align: left; }
  .data-table tbody tr td {
    font-size: 8.5pt;
    padding: 6px 8px;
    border-bottom: 0.3px solid #e5e7eb;
    vertical-align: middle;
  }
  .data-table tbody tr:nth-child(even) td { background: #fafafa; }
  .data-table tbody tr:nth-child(odd)  td { background: #ffffff; }

  .td-name  { color: #111827; font-weight: 600; }
  .td-cat   { color: #6b7280; font-size: 7.5pt; font-weight: 700; text-align: center; }
  .td-price { color: #047857; font-weight: 700; text-align: right; }
  .td-date  { color: #6b7280; text-align: center; }

  .chg-up      { color: #16a34a; font-weight: 700; text-align: center; }
  .chg-down    { color: #dc2626; font-weight: 700; text-align: center; }
  .chg-neutral { color: #6b7280; font-weight: 400; text-align: center; }

  .cat-badge {
    display: inline-block;
    background: #f3f4f6; color: #6b7280;
    font-size: 7pt; font-weight: 700;
    padding: 2px 6px; border-radius: 3px;
    letter-spacing: 0.3px;
  }

  /* ── Category table ── */
  .cat-table { width:100%; border-collapse: collapse; }
  .cat-table thead tr th {
    background: #f97316; color: #fff;
    font-size: 8.5pt; font-weight: 700;
    padding: 7px 8px; border-bottom: 1.5px solid #ea6c0a;
  }
  .cat-table thead tr th:first-child { text-align:left; }
  .cat-table tbody tr td {
    font-size: 8.5pt; padding: 6px 8px;
    border-bottom: 0.3px solid #e5e7eb; vertical-align: middle;
  }
  .cat-table tbody tr:nth-child(even) td { background: #fafafa; }
  .cat-dot { font-size: 11pt; margin-right: 4px; }

  /* ── Progress bar ── */
  .pct-bar-bg  { background:#e5e7eb; height:6px; border-radius:3px; width:80px; display:inline-block; vertical-align:middle; }
  .pct-bar-fill{ height:6px; border-radius:3px; display:inline-block; }

  /* ── Footer note ── */
  .footer-note {
    margin-top: 14px;
    border: 0.5px solid #fed7aa;
    border-left: 3px solid #f97316;
    background: #fff7ed;
    padding: 8px 12px;
    font-size: 7.5pt;
    color: #6b7280;
    line-height: 1.5;
  }

  /* ── Page footer ── */
  .page-footer {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: #fff;
    border-top: 0.5px solid #e5e7eb;
    padding: 6px 20px;
    font-size: 7.5pt;
    color: #9ca3af;
  }
  .page-footer table { width:100%; }
  .footer-bottom-stripe {
    height: 4px; background: #f97316;
    position: fixed; bottom:0; left:0; right:0;
  }
</style>
</head>
<body>

{{-- ── TOP STRIPE ── --}}
<div class="header-top-stripe"></div>

{{-- ── HEADER ── --}}
<div class="page-header">
  <table class="header-table">
    <tr>
      <td class="header-logo-cell">
        <table><tr>
          <td style="width:44px; vertical-align:middle">
            <div class="logo-circle">SIMO</div>
          </td>
          <td style="vertical-align:middle">
            <div class="sys-title">SIMOPANG</div>
            <div class="sys-subtitle">Sistem Monitoring Harga Pangan</div>
          </td>
        </tr></table>
      </td>
      <td class="header-right-cell">
        <div class="report-title">Laporan Harga Komoditas</div>
        <div class="report-date">Dicetak: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }} WIB</div>
      </td>
    </tr>
  </table>
</div>

{{-- ── PAGE FOOTER (fixed) ── --}}
<div class="page-footer">
  <table>
    <tr>
      <td>SIMOPANG — Sistem Monitoring Harga Pangan</td>
      <td style="text-align:right">Dokumen ini digenerate secara otomatis</td>
    </tr>
  </table>
</div>
<div class="footer-bottom-stripe"></div>

{{-- ── BODY ── --}}
<div class="body-wrap">

  {{-- Meta bar --}}
  <table class="meta-table">
    <thead>
      <tr>
        <th>Periode Data</th>
        <th>Komoditas</th>
        <th>Wilayah</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $periodeLabel ?? 'Semua Periode' }}</td>
        <td>{{ $namaKomoditas ?? 'Semua Komoditas' }}</td>
        <td>Kab. Jember</td>
        <td><span class="badge-aktif">Aktif</span></td>
      </tr>
    </tbody>
  </table>

  {{-- Stat cards --}}
  <div class="section-title">Ringkasan Statistik</div>
  <div class="section-sub">Kondisi harga komoditas pangan terkini.</div>

  <table class="stat-table">
    <tr>
      <td class="stat-card-cell">
        <div class="stat-label">Harga Terbaru ({{ $namaKomoditas ?? 'Semua Komoditas' }})</div>
        <div class="stat-value">Rp {{ number_format($hargaTerbaru ?? 0, 0, ',', '.') }}</div>
        <div class="stat-change-up">▲ Update terbaru</div>
      </td>
      <td class="stat-card-cell">
        <div class="stat-label">Perubahan Bulanan</div>
        <div class="stat-value">
          @if(($hargaChange ?? 0) >= 0)
            + Rp {{ number_format($hargaChange ?? 0, 0, ',', '.') }}
          @else
            - Rp {{ number_format(abs($hargaChange ?? 0), 0, ',', '.') }}
          @endif
        </div>
        @if(($hargaChange ?? 0) >= 0)
          <div class="stat-change-up">▲ vs bulan lalu Rp {{ number_format($hargaKemarin ?? 0, 0, ',', '.') }} ({{ ($hargaPercent ?? 0) >= 0 ? '+' : '' }}{{ number_format($hargaPercent ?? 0, 2) }}%)</div>
        @else
          <div class="stat-change-down">▼ vs bulan lalu Rp {{ number_format($hargaKemarin ?? 0, 0, ',', '.') }} ({{ number_format($hargaPercent ?? 0, 2) }}%)</div>
        @endif
      </td>
      <td class="stat-card-cell">
        <div class="stat-label">Status Volatilitas</div>
        <div class="stat-value">{{ $statusVolatilitas ?? 'Rendah' }}</div>
        <div class="stat-change-neutral">— Indeks: {{ $indexVolatilitas ?? '0.00' }} (normal)</div>
      </td>
    </tr>
  </table>

  {{-- Price table --}}
  <div class="section-title" style="margin-top:16px">Riwayat Harga Terkini</div>
  <div class="section-sub">Data harga komoditas, urut berdasarkan tanggal terbaru.</div>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width:28%">Komoditas</th>
        <th style="width:18%; text-align:center">Kategori</th>
        <th style="width:18%; text-align:right">Harga (IDR)</th>
        <th style="width:18%; text-align:center">Perubahan</th>
        <th style="width:18%; text-align:center">Tanggal</th>
      </tr>
    </thead>
    <tbody>
      @forelse($recentPrices as $item)
      @php
        $selisih = $item->selisih ?? 0;
        $persen  = $item->persen  ?? 0;
      @endphp
      <tr>
        <td class="td-name">{{ $item->commodity_name ?? '-' }}</td>
        <td class="td-cat"><span class="cat-badge">{{ strtoupper($item->category ?? '-') }}</span></td>
        <td class="td-price">Rp {{ number_format($item->harga_sekarang ?? 0, 0, ',', '.') }}</td>
        <td>
          @if($selisih > 0)
            <span class="chg-up">▲ +{{ number_format(abs($persen), 2) }}%</span>
          @elseif($selisih < 0)
            <span class="chg-down">▼ -{{ number_format(abs($persen), 2) }}%</span>
          @else
            <span class="chg-neutral">— 0%</span>
          @endif
        </td>
        <td class="td-date">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="5" style="text-align:center; color:#6b7280; padding:20px">
          Belum ada data harga.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Category distribution --}}
  @if(isset($chartLabels) && count($chartLabels) > 0)
  <div class="section-title" style="margin-top:18px">Distribusi Rata-rata Harga per Kategori</div>
  <div class="section-sub">Perbandingan rata-rata harga berdasarkan kelompok kategori.</div>

  @php
  $catColors = [
    '#f97316','#3b82f6','#10b981','#f59e0b','#6366f1',
    '#ec4899','#14b8a6','#8b5cf6','#ef4444','#84cc16',
    '#06b6d4','#a855f7','#fb923c','#22d3ee','#e11d48',
    '#4ade80','#facc15',
  ];
  $totalVal = array_sum($chartValues->toArray());
  @endphp

  <table class="cat-table">
    <thead>
      <tr>
        <th style="width:35%">Kategori</th>
        <th style="width:25%; text-align:right">Rata-rata Harga</th>
        <th style="width:20%; text-align:center">Porsi</th>
        <th style="width:20%; text-align:center">Indikator</th>
      </tr>
    </thead>
    <tbody>
      @foreach($chartLabels as $i => $label)
      @php
        $val = $chartValues[$i] ?? 0;
        $pct = $totalVal > 0 ? round(($val / $totalVal) * 100, 1) : 0;
        $color = $catColors[$i % count($catColors)];
        $barW  = max(4, round($pct * 0.8)); // max ~80px
      @endphp
      <tr>
        <td>
          <span class="cat-dot" style="color:{{ $color }}">■</span>
          {{ $label }}
        </td>
        <td style="text-align:right; color:#047857; font-weight:700">
          Rp {{ number_format($val, 0, ',', '.') }}
        </td>
        <td style="text-align:center; color:#6b7280">{{ $pct }}%</td>
        <td style="text-align:center">
          <div class="pct-bar-bg">
            <div class="pct-bar-fill" style="width:{{ $barW }}px; background:{{ $color }}"></div>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- Footer note --}}
  <div class="footer-note">
    <strong>Catatan:</strong> Laporan ini digenerate otomatis oleh sistem SIMOPANG.
    Data harga bersumber dari input petugas lapangan dan diverifikasi secara berkala.
    Untuk informasi lebih lanjut hubungi Dinas Perdagangan Kab. Jember.
    Total data ditampilkan: {{ isset($recentPrices) ? $recentPrices->count() : 0 }}
  </div>

</div>{{-- /body-wrap --}}
</body>
</html>