{{--
  SIMOPANG — User Simulasi Pengeluaran AI
  File : resources/views/user/simulasi.blade.php
  Data : dari Flask /api/external/rekomendasi (buat_rekomendasi)
  Fields Flask: rekomendasi, warna, emoji, headline, alasan,
                skor, harga_kini, harga_7hari, harga_30hari_avg,
                volatilitas, budget_sekarang, budget_7hari,
                konsumsi, satuan, delta_pct_7, delta_pct_30, chart
--}}
@extends('layouts.layout')

@section('title', 'Simulasi Pengeluaran AI')
@section('page-title', 'Simulasi Pengeluaran AI')
@section('page-sub', 'Estimasi pengeluaran berdasarkan tren harga komoditas dan prediksi AI')

@section('content')

@php
    // Seluruh response Flask tersedia via $rekData
    $rek       = $rekData ?? [];
    $rekomText = $rek['rekomendasi'] ?? null;
    $warna     = $rek['warna']       ?? null;   // buy | buy_soon | wait | hold
    $emoji     = $rek['emoji']       ?? '';
    $headline  = $rek['headline']    ?? null;
    $alasan    = $rek['alasan']      ?? [];
    $skor      = $rek['skor']        ?? null;
    $deltaPct7 = $rek['delta_pct_7'] ?? null;
    $satuan    = $rek['satuan']      ?? 'kg';
    $chart     = $rek['chart']       ?? [];

    // Warna badge rekomendasi
    $warnaMap = [
        'buy'      => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'BELI SEKARANG'],
        'buy_soon' => ['bg' => '#fef9c3', 'color' => '#713f12', 'label' => 'BELI SEGERA'],
        'wait'     => ['bg' => '#dbeafe', 'color' => '#1e3a8a', 'label' => 'TUNGGU DULU'],
        'hold'     => ['bg' => '#fee2e2', 'color' => '#7f1d1d', 'label' => 'TUNDA PEMBELIAN'],
    ];
    $badge = $warnaMap[$warna] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => strtoupper($rekomText ?? '—')];
@endphp

<div class="u-sim-grid">

    {{-- ── LEFT: INPUT PANEL ── --}}
    <div class="u-sim-left">

        <div class="u-input-card">
            <div class="u-input-card__header">
                <div class="u-input-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <span class="u-input-card__title">Input Data Konsumsi</span>
            </div>

            <div class="u-input-card__body">
                <form method="GET" action="{{ route('user.simulasi') }}">

                    <div class="u-form-group">
                        <label class="u-form-label">Pilih Komoditas</label>
                        <select class="u-form-select" name="komoditas">
                            @foreach($komoditas as $item)
                                <option value="{{ $item->id }}"
                                    {{ ($selectedKomoditas ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="u-form-group">
                        <label class="u-form-label">Konsumsi per Minggu ({{ $satuan }})</label>
                        <div class="u-input-unit-wrap">
                            <input type="number" class="u-form-input" name="konsumsi"
                                   value="{{ $konsumsi ?? 0.5 }}"
                                   min="0.1" max="100" step="0.1" placeholder="0.5">
                            <span class="u-input-unit">{{ $satuan }}</span>
                        </div>
                        <p class="u-form-hint">*Digunakan untuk menghitung total bulanan</p>
                    </div>

                    <button type="submit" class="u-btn-hitung">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2"/>
                            <line x1="8" y1="10" x2="16" y2="10"/>
                            <line x1="8" y1="14" x2="16" y2="14"/>
                            <line x1="8" y1="18" x2="12" y2="18"/>
                        </svg>
                        Hitung Estimasi
                    </button>

                </form>
            </div>
        </div>

        {{-- Rekomendasi Badge dari Flask --}}
        @if($rekomText)
        <div class="u-ai-insight" style="margin-top:1rem">
            <div class="u-ai-insight__header">
                <span style="font-size:1.4rem">{{ $emoji }}</span>
                <span class="u-ai-insight__title">
                    <span style="display:inline-block;padding:3px 10px;border-radius:20px;
                                 font-size:12px;font-weight:700;
                                 background:{{ $badge['bg'] }};color:{{ $badge['color'] }}">
                        {{ $badge['label'] }}
                    </span>
                </span>
            </div>
            @if($headline)
            <p class="u-ai-insight__text" style="margin-bottom:.5rem">
                {{ $headline }}
            </p>
            @endif
            @foreach($alasan as $a)
            <p class="u-ai-insight__text" style="font-size:12px;color:var(--muted)">
                • {{ $a }}
            </p>
            @endforeach
            @if($skor !== null)
            <div style="margin-top:.5rem;font-size:11px;color:var(--muted)">
                Skor rekomendasi: <strong>{{ $skor }}/100</strong>
                @if($deltaPct7 !== null)
                    · Delta 7 hari: <strong>{{ $deltaPct7 >= 0 ? '+' : '' }}{{ $deltaPct7 }}%</strong>
                @endif
            </div>
            @endif
        </div>
        @else
        <div class="u-ai-insight" style="margin-top:1rem">
            <div class="u-ai-insight__header">
                <div class="u-ai-insight__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <span class="u-ai-insight__title">Wawasan AI</span>
            </div>
            <p class="u-ai-insight__text">{{ $wawasanAI ?? 'Pilih komoditas dan klik Hitung Estimasi untuk mendapatkan rekomendasi AI.' }}</p>
        </div>
        @endif

    </div>
    {{-- / LEFT --}}

    {{-- ── RIGHT: HASIL SIMULASI ── --}}
    <div class="u-sim-right">

        <div class="u-price-grid">

            {{-- Harga Saat Ini --}}
            <div class="u-price-card u-price-card--current">
                <div class="u-price-card__label">Harga Saat Ini</div>
                <div class="u-price-card__main">
                    <span class="u-price-card__value">
                        Rp {{ number_format($hargaTerbaru, 0, ',', '.') }}
                    </span>
                    <span class="u-price-card__unit">/{{ $satuan }}</span>
                </div>
                <div class="u-price-card__sub-label">Total Pengeluaran Sekarang</div>
                <div class="u-price-card__sub-value">
                    {{-- budget_sekarang dari Flask = konsumsi × harga_kini --}}
                    Rp {{ number_format($totalSekarang, 0, ',', '.') }}
                    <span class="u-price-card__sub-unit">/minggu</span>
                </div>
            </div>

            {{-- Prediksi 7 Hari --}}
            <div class="u-price-card u-price-card--predict">
                <div class="u-price-card__badge-wrap">
                    <span class="u-ai-badge">AI Prediction</span>
                </div>
                <div class="u-price-card__label">Prediksi Harga 7 Hari</div>
                <div class="u-price-card__main">
                    {{-- harga_7hari dari Flask = rata-rata forecast 7 hari ke depan --}}
                    <span class="u-price-card__value">
                        Rp {{ number_format($harga7Hari ?? $hargaPrediksi, 0, ',', '.') }}
                    </span>
                    <span class="u-price-card__unit">/{{ $satuan }}</span>
                </div>
                <div class="u-price-card__sub-label">Estimasi Pengeluaran 7 Hari</div>
                <div class="u-price-card__sub-value u-price-card__sub-value--predict">
                    {{-- budget_7hari dari Flask = konsumsi × harga_7hari --}}
                    Rp {{ number_format($totalPrediksi, 0, ',', '.') }}
                    <span class="u-price-card__sub-unit">/minggu</span>
                </div>
            </div>

        </div>

        {{-- Ringkasan Anggaran --}}
        <div class="u-budget-card">

            <div class="u-budget-card__header">
                <div>
                    <div class="u-budget-card__title">Ringkasan Anggaran</div>
                    <div class="u-budget-card__sub">
                        Berdasarkan konsumsi {{ $konsumsi ?? '0.5' }} {{ $satuan }} per minggu
                    </div>
                </div>
            </div>

            <div class="u-budget-body">

                <div class="u-budget-col">
                    <div class="u-budget-col__label">Selisih Pengeluaran (vs 7 hari)</div>
                    @php $isUp = ($selisih ?? 0) >= 0; @endphp
                    <div class="u-selisih {{ $isUp ? 'u-selisih--up' : 'u-selisih--down' }}">
                        @if($isUp)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                            <polyline points="16 7 22 7 22 13"/>
                        </svg>
                        @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/>
                            <polyline points="16 17 22 17 22 11"/>
                        </svg>
                        @endif
                        {{ $isUp ? '+ ' : '- ' }}Rp {{ number_format(abs($selisih ?? 0), 0, ',', '.') }}
                    </div>
                    <p class="u-selisih__desc">
                        {{ $isUp ? 'Peningkatan' : 'Penghematan' }} estimasi sekitar
                        <strong class="u-selisih__pct">{{ abs($changePercent ?? 0) }}%</strong>
                    </p>
                </div>

                <div class="u-budget-col">
                    <div class="u-budget-col__label">Rekomendasi Tindakan</div>
                    <div class="u-action-btns">
                        @if(in_array($warna, ['buy', 'buy_soon']))
                            <button class="u-btn-action u-btn-action--primary">Stok Lebih Awal</button>
                            <button class="u-btn-action u-btn-action--secondary">Cari Promo</button>
                        @elseif(in_array($warna, ['wait', 'hold']))
                            <button class="u-btn-action u-btn-action--secondary">Tunda Pembelian</button>
                            <button class="u-btn-action u-btn-action--primary">Pantau Harga</button>
                        @else
                            <button class="u-btn-action u-btn-action--primary">Stok Lebih Awal</button>
                            <button class="u-btn-action u-btn-action--secondary">Cari Promo</button>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Chart dari Flask (hist 90 hari + pred 30 hari) — dirender dinamis via JS --}}
            @if(!empty($chart['hist_harga']) && !empty($chart['pred_harga']))
            <div class="u-sim-chart-wrap" style="padding:1rem">
                <div style="font-size:11px;font-weight:700;color:var(--muted);
                            letter-spacing:.05em;margin-bottom:.5rem">
                    HISTORIS {{ count($chart['hist_harga']) }} HARI + PREDIKSI {{ count($chart['pred_harga']) }} HARI
                </div>
                <div style="position:relative;height:180px;width:100%">
                    <canvas id="simChart"></canvas>
                </div>
                <div class="u-sim-chart-labels" style="display:flex;justify-content:space-between;
                            font-size:11px;color:var(--muted);margin-top:4px">
                    <span>{{ \Carbon\Carbon::parse($chart['hist_tanggal'][0] ?? now())->format('d M') }}</span>
                    @php $mid = (int)(count($chart['hist_tanggal'])/2); @endphp
                    <span>{{ \Carbon\Carbon::parse($chart['hist_tanggal'][$mid] ?? now())->format('d M') }}</span>
                    <span>{{ \Carbon\Carbon::parse($chart['hist_tanggal'][count($chart['hist_tanggal'])-1] ?? now())->format('d M') }}</span>
                    <span style="color:#0ea5e9">
                        {{ \Carbon\Carbon::parse($chart['pred_tanggal'][count($chart['pred_tanggal'])-1] ?? now())->format('d M') }} →
                    </span>
                </div>
                <div class="u-sim-chart-caption">
                    <span style="display:inline-block;width:16px;height:3px;
                                 background:#2563eb;vertical-align:middle;margin-right:4px"></span>
                    Historis ·
                    <span style="display:inline-block;width:16px;height:2px;
                                 border-top:2px dashed #0ea5e9;vertical-align:middle;margin:0 4px"></span>
                    Prediksi 30 hari
                    @if(!empty($chart['ci_lower']))
                    ·
                    <span style="display:inline-block;width:12px;height:10px;
                                 background:rgba(14,165,233,.15);vertical-align:middle;margin:0 4px"></span>
                    Confidence Interval
                    @endif
                </div>
            </div>

            @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
            (function() {
                const histLabels = @json($chart['hist_tanggal']);
                const histData   = @json($chart['hist_harga']);
                const predLabels = @json($chart['pred_tanggal']);
                const predData   = @json($chart['pred_harga']);
                const ciLower    = @json($chart['ci_lower'] ?? []);
                const ciUpper    = @json($chart['ci_upper'] ?? []);

                // Gabungkan label: historis + prediksi
                const allLabels = [...histLabels, ...predLabels];

                // Historis: nilai asli, prediksi: null (tidak tampil di garis historis)
                const histLine = [
                    ...histData,
                    ...new Array(predLabels.length).fill(null)
                ];

                // Prediksi: null untuk historis, lalu nilai prediksi
                // Overlap 1 titik agar garis nyambung
                const predLine = [
                    ...new Array(histData.length - 1).fill(null),
                    histData[histData.length - 1], // titik sambung
                    ...predData
                ];

                const datasets = [
                    {
                        label: 'Historis',
                        data: histLine,
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                    },
                    {
                        label: 'Prediksi',
                        data: predLine,
                        borderColor: '#0ea5e9',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                    },
                ];

                // CI band (jika ada)
                if (ciLower.length && ciUpper.length) {
                    const ciUpperLine = [
                        ...new Array(histData.length - 1).fill(null),
                        histData[histData.length - 1],
                        ...ciUpper
                    ];
                    const ciLowerLine = [
                        ...new Array(histData.length - 1).fill(null),
                        histData[histData.length - 1],
                        ...ciLower
                    ];
                    datasets.push({
                        label: 'CI Upper',
                        data: ciUpperLine,
                        borderColor: 'rgba(14,165,233,.25)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: '+1',
                        backgroundColor: 'rgba(14,165,233,.08)',
                        tension: 0.3,
                        spanGaps: false,
                    });
                    datasets.push({
                        label: 'CI Lower',
                        data: ciLowerLine,
                        borderColor: 'rgba(14,165,233,.25)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                    });
                }

                // Hanya tampilkan label setiap N titik agar tidak penuh
                const totalPoints = allLabels.length;
                const tickStep    = Math.ceil(totalPoints / 8);

                new Chart(document.getElementById('simChart'), {
                    type: 'line',
                    data: { labels: allLabels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        if (ctx.parsed.y === null) return null;
                                        return ctx.dataset.label + ': Rp ' +
                                            ctx.parsed.y.toLocaleString('id-ID');
                                    }
                                }
                            },
                            annotation: {
                                annotations: {
                                    divider: {
                                        type: 'line',
                                        xMin: histLabels.length - 1,
                                        xMax: histLabels.length - 1,
                                        borderColor: '#bfdbfe',
                                        borderWidth: 1.5,
                                        borderDash: [4, 3],
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxTicksLimit: 8,
                                    font: { size: 10 },
                                    color: '#9ca3af',
                                    callback: function(val, idx) {
                                        return idx % tickStep === 0 ? allLabels[idx].slice(5) : '';
                                    }
                                },
                                grid: { color: '#f1f5f9' }
                            },
                            y: {
                                ticks: {
                                    font: { size: 10 },
                                    color: '#9ca3af',
                                    callback: v => 'Rp ' + (v/1000).toFixed(0) + 'rb'
                                },
                                grid: { color: '#f1f5f9' }
                            }
                        }
                    }
                });
            })();
            </script>
            @endonce
            @endif

        </div>

    </div>
    {{-- / RIGHT --}}

</div>

@endsection