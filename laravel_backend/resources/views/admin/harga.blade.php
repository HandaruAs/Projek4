@extends('layouts.layout')

@section('title', 'Data Harga')
@section('page-title', 'Data Harga')
@section('page-sub', 'Monitor and manage commodity price data from all registered regions.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Price Records</div>
            <div class="stat-value">{{ number_format($totalRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-database"></i>
                <span class="stat-change-sub">history + prediksi</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-database"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Records Today</div>
            <div class="stat-value">{{ number_format($todayRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-calendar-day"></i>
                <span class="stat-change-sub">today</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-calendar-day"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Commodities</div>
            <div class="stat-value">{{ number_format($totalKomoditas) }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">registered</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>
</div>

{{-- FILTER BAR --}}
<form method="GET" action="{{ url('/admin/harga') }}">
    <x-filter-bar
        placeholder="Search commodity name..."
        :categories="$categories"
        :with-date="true"
        search-id="searchInput"
        category-id="categoryFilter"
        date-id="dateFilter">
    </x-filter-bar>
</form>

{{-- PRICE TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title" style="display:flex;align-items:center;gap:8px;">
                Price History
                <span id="live-badge" style="
                    display:inline-flex;align-items:center;gap:5px;
                    font-size:11px;font-weight:600;padding:3px 10px;
                    background:#f0fdf4;color:#16a34a;border-radius:999px;">
                    <span id="live-dot" style="width:7px;height:7px;border-radius:50%;
                        background:#16a34a;animation:liveBlink 1.4s infinite;
                        display:inline-block;"></span>
                    LIVE
                </span>
            </div>
            <div class="table-subtitle">
                Complete commodity price records from all monitored regions.
                @if(!request('search') && !request('category') && !request('date'))
                    <span style="color:#3b82f6;font-weight:600;">
                        — {{ $predictionRows->count() }} baris teratas adalah data prediksi terbaru.
                    </span>
                @endif
                <span style="color:var(--text-3);font-size:11px;margin-left:6px;">
                    Terakhir diperbarui: <span id="last-update-time">—</span>
                </span>
            </div>
        </div>
    </div>

    <table id="hargaTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Commodity</th>
                <th>Category</th>
                <th>Price (IDR)</th>
                <th>Satuan</th>
                <th>Date</th>
                <th>Pred. Berakhir</th>
                <th>Perubahan</th>
                <th>Pred Status</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="harga-tbody">

            {{-- ── Baris Prediksi ── --}}
            @if(!request('search') && !request('category') && !request('date'))
                @foreach($predictionRows as $pred)
                <tr style="background:rgba(59,130,246,0.04);border-left:3px solid #3b82f6;" title="">
                    <td class="date-text">—</td>
                    <td class="commodity-name">
                        {{ $pred->commodity_name }}
                        <div style="font-size:10px;color:var(--text-3);margin-top:2px;">
                            Dibuat: {{ $pred->created_at ?? '-' }}
                        </div>
                    </td>
                    <td><span class="region-text">{{ $pred->category }}</span></td>
                    <td class="price-text">
                        @if($pred->harga_sekarang)
                            Rp {{ number_format($pred->harga_sekarang, 0, ',', '.') }}
                            @if($pred->harga_terakhir)
                                <div style="font-size:10px;color:var(--text-3);margin-top:2px;">
                                    dari Rp {{ number_format($pred->harga_terakhir, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span class="date-text">—</span>
                        @endif
                    </td>
                    <td><span class="region-text">{{ $pred->satuan ?? '-' }}</span></td>
                    <td class="date-text">
                        {{ $pred->date ? \Carbon\Carbon::parse($pred->date)->format('M d, Y') : '-' }}
                    </td>
                    {{-- ── KOLOM BARU: Pred. Berakhir ── --}}
                    <td>
                        @if(!empty($pred->tanggal_akhir))
                            <span style="font-size:11px;padding:3px 10px;
                                background:#fef3c7;color:#d97706;
                                border-radius:999px;font-weight:600;
                                display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-calendar-xmark" style="font-size:10px;"></i>
                                {{ \Carbon\Carbon::parse($pred->tanggal_akhir)->format('M d, Y') }}
                            </span>
                        @else
                            <span class="date-text">—</span>
                        @endif
                    </td>
                    <td>
                        @php $pct = $pred->selisih_persen ?? null; @endphp
                        @if($pct === null)
                            <span class="date-text">—</span>
                        @elseif($pct > 0)
                            <span class="stat-change down" style="font-size:11px;">
                                <i class="fas fa-arrow-up"></i> +{{ $pct }}%
                            </span>
                        @elseif($pct < 0)
                            <span class="stat-change up" style="font-size:11px;">
                                <i class="fas fa-arrow-down"></i> {{ $pct }}%
                            </span>
                        @else
                            <span class="stat-change neutral" style="font-size:11px;">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($pred->pred_status === 'completed')
                            <span style="font-size:11px;padding:3px 10px;background:#f0fdf4;color:#16a34a;border-radius:999px;font-weight:600;">
                                Completed
                            </span>
                        @elseif($pred->pred_status === 'pending')
                            <span style="font-size:11px;padding:3px 10px;background:#fef3c7;color:#d97706;border-radius:999px;font-weight:600;">
                                Pending
                            </span>
                        @elseif($pred->pred_status === 'processing')
                            <span style="font-size:11px;padding:3px 10px;background:#eff6ff;color:#3b82f6;border-radius:999px;font-weight:600;">
                                Processing
                            </span>
                        @else
                            <span style="font-size:11px;padding:3px 10px;background:#f3f4f6;color:#6b7280;border-radius:999px;font-weight:600;">
                                Belum
                            </span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:11px;padding:3px 10px;
                            background:#eff6ff;color:#3b82f6;
                            border-radius:999px;font-weight:600;">
                            Prediksi
                        </span>
                    </td>
                </tr>
                @endforeach
            @endif

            {{-- ── Baris Price History ── --}}
            @forelse($hargaList as $index => $item)
            @php
                $predMatch     = $prediksiMap->get($item->commodity_name ?? '');
                $hargaPrediksi = $predMatch ? (float) ($predMatch->forecast[0] ?? 0) : null;
                $hargaHist     = (float) ($item->harga_sekarang ?? 0);
                $selisihHist   = ($hargaPrediksi && $hargaHist > 0)
                    ? round((($hargaPrediksi - $hargaHist) / $hargaHist) * 100, 2)
                    : null;
            @endphp
            <tr>
                <td class="date-text">{{ $hargaList->firstItem() + $index }}</td>
                <td class="commodity-name">{{ $item->commodity_name ?? '-' }}</td>
                <td>
                    @if(!empty($item->category))
                        <span class="region-text">{{ $item->category }}</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td class="price-text">
                    Rp {{ number_format($item->harga_sekarang, 0, ',', '.') }}
                    @if($hargaPrediksi)
                        <div style="font-size:10px;color:var(--text-3);margin-top:2px;">
                            prediksi: Rp {{ number_format($hargaPrediksi, 0, ',', '.') }}
                        </div>
                    @endif
                </td>
                <td>
                    @if(!empty($item->satuan))
                        <span class="region-text">{{ $item->satuan }}</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td class="date-text">
                    {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
                </td>
                {{-- Kolom Pred. Berakhir kosong untuk baris historis --}}
                <td><span class="date-text">—</span></td>
                <td>
                    @if($selisihHist !== null)
                        @if($selisihHist > 0)
                            <span class="stat-change down" style="font-size:11px;">
                                <i class="fas fa-arrow-up"></i> +{{ $selisihHist }}%
                            </span>
                        @elseif($selisihHist < 0)
                            <span class="stat-change up" style="font-size:11px;">
                                <i class="fas fa-arrow-down"></i> {{ $selisihHist }}%
                            </span>
                        @else
                            <span class="stat-change neutral" style="font-size:11px;">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td><span class="date-text">—</span></td>
                <td>
                    <span style="font-size:11px;padding:3px 10px;
                        background:#f0fdf4;color:#16a34a;
                        border-radius:999px;font-weight:600;">
                        Historis
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center;padding:2rem;color:var(--text-muted)">
                    Belum ada data harga.
                </td>
            </tr>
            @endforelse

        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $hargaList->firstItem() }}–{{ $hargaList->lastItem() }}
            of {{ number_format($hargaList->total()) }} records
            @if(!request('search') && !request('category') && !request('date'))
                + {{ $predictionRows->count() }} prediksi
            @endif
        </span>
        <x-pagination :paginator="$hargaList" />
    </div>
</div>

@endsection

@push('scripts')
<style>
@keyframes liveBlink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}
@keyframes rowFlash {
    0%   { background-color: rgba(59,130,246,0.18); }
    100% { background-color: rgba(59,130,246,0.04); }
}
@keyframes rowFlashGreen {
    0%   { background-color: rgba(16,185,129,0.12); }
    100% { background-color: transparent; }
}
.row-pred-updated { animation: rowFlash 1.5s ease-out; }
.row-hist-updated { animation: rowFlashGreen 1.5s ease-out; }
</style>

<script>
(function () {
    const INTERVAL = 30000;
    const endpoint = '{{ route("admin.harga.realtime") }}';
    let prevPrices = {};

    document.querySelectorAll('#harga-tbody tr').forEach(row => {
        const name  = row.querySelector('td:nth-child(2)')?.textContent.trim();
        const price = row.querySelector('td:nth-child(4)')?.textContent.trim();
        if (name && price) prevPrices[name] = price;
    });

    document.getElementById('last-update-time').textContent =
        new Date().toLocaleTimeString('id-ID');

    function formatRp(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function formatDate(str) {
        if (!str) return '-';
        return new Date(str).toLocaleDateString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric'
        });
    }

    function trendBadge(pct) {
        if (pct === null || pct === undefined)
            return '<span class="date-text">—</span>';
        if (pct > 0)
            return `<span class="stat-change down" style="font-size:11px;"><i class="fas fa-arrow-up"></i> +${pct}%</span>`;
        if (pct < 0)
            return `<span class="stat-change up" style="font-size:11px;"><i class="fas fa-arrow-down"></i> ${pct}%</span>`;
        return `<span class="stat-change neutral" style="font-size:11px;"><i class="fas fa-minus"></i> 0%</span>`;
    }

    function predStatusBadge(status) {
        if (status === 'completed')
            return `<span style="font-size:11px;padding:3px 10px;background:#f0fdf4;color:#16a34a;border-radius:999px;font-weight:600;">Completed</span>`;
        if (status === 'pending')
            return `<span style="font-size:11px;padding:3px 10px;background:#fef3c7;color:#d97706;border-radius:999px;font-weight:600;">Pending</span>`;
        if (status === 'processing')
            return `<span style="font-size:11px;padding:3px 10px;background:#eff6ff;color:#3b82f6;border-radius:999px;font-weight:600;">Processing</span>`;
        return `<span style="font-size:11px;padding:3px 10px;background:#f3f4f6;color:#6b7280;border-radius:999px;font-weight:600;">Belum</span>`;
    }

    function tanggalAkhirBadge(tanggal_akhir) {
        if (!tanggal_akhir) return '<span class="date-text">—</span>';
        return `<span style="font-size:11px;padding:3px 10px;background:#fef3c7;color:#d97706;border-radius:999px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
            <i class="fas fa-calendar-xmark" style="font-size:10px;"></i>
            ${formatDate(tanggal_akhir)}
        </span>`;
    }

    function buildPredRow(item) {
        const priceStr = item.harga_sekarang ? formatRp(item.harga_sekarang) : '—';
        const changed  = prevPrices[item.commodity_name] &&
                         prevPrices[item.commodity_name] !== priceStr;
        prevPrices[item.commodity_name] = priceStr;

        const hargaTerakhirInfo = item.harga_terakhir
            ? `<div style="font-size:10px;color:var(--text-3);margin-top:2px;">dari ${formatRp(item.harga_terakhir)}</div>`
            : '';

        return `
        <tr style="background:rgba(59,130,246,0.04);border-left:3px solid #3b82f6;"
            class="${changed ? 'row-pred-updated' : ''}">
            <td class="date-text">—</td>
            <td class="commodity-name">
                ${item.commodity_name ?? '-'}
                <div style="font-size:10px;color:var(--text-3);margin-top:2px;">Dibuat: ${item.created_at ?? '-'}</div>
            </td>
            <td><span class="region-text">${item.category ?? '-'}</span></td>
            <td class="price-text">${priceStr}${hargaTerakhirInfo}</td>
            <td><span class="region-text">${item.satuan ?? '-'}</span></td>
            <td class="date-text">${formatDate(item.date)}</td>
            <td>${tanggalAkhirBadge(item.tanggal_akhir)}</td>
            <td>${trendBadge(item.selisih_persen ?? null)}</td>
            <td>${predStatusBadge(item.pred_status)}</td>
            <td>
                <span style="font-size:11px;padding:3px 10px;background:#eff6ff;color:#3b82f6;border-radius:999px;font-weight:600;">Prediksi</span>
            </td>
        </tr>`;
    }

    function buildHistRow(item, no) {
        const priceStr = formatRp(item.harga_sekarang);
        const changed  = prevPrices[item.commodity_name] &&
                         prevPrices[item.commodity_name] !== priceStr;
        prevPrices[item.commodity_name] = priceStr;

        const predInfo = item.harga_prediksi
            ? `<div style="font-size:10px;color:var(--text-3);margin-top:2px;">prediksi: ${formatRp(item.harga_prediksi)}</div>`
            : '';

        return `
        <tr class="${changed ? 'row-hist-updated' : ''}">
            <td class="date-text">${no}</td>
            <td class="commodity-name">${item.commodity_name ?? '-'}</td>
            <td>${item.category ? `<span class="region-text">${item.category}</span>` : `<span class="date-text">—</span>`}</td>
            <td class="price-text">${priceStr}${predInfo}</td>
            <td>${item.satuan ? `<span class="region-text">${item.satuan}</span>` : `<span class="date-text">—</span>`}</td>
            <td class="date-text">${formatDate(item.date)}</td>
            <td><span class="date-text">—</span></td>
            <td>${trendBadge(item.selisih_persen ?? null)}</td>
            <td><span class="date-text">—</span></td>
            <td>
                <span style="font-size:11px;padding:3px 10px;background:#f0fdf4;color:#16a34a;border-radius:999px;font-weight:600;">Historis</span>
            </td>
        </tr>`;
    }

    function poll() {
        const params = new URLSearchParams(window.location.search);

        fetch(`${endpoint}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('harga-tbody');
            if (!tbody) return;

            let html = '';

            if (!data.hasFilter) {
                data.predictionRows.forEach(pred => { html += buildPredRow(pred); });
            }

            data.hargaList.forEach((item, i) => {
                const no = (data.pagination.firstItem ?? 1) + i;
                html += buildHistRow(item, no);
            });

            if (!html) {
                html = `<tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--text-muted)">Belum ada data harga.</td></tr>`;
            }

            tbody.innerHTML = html;

            const elTime = document.getElementById('last-update-time');
            if (elTime) elTime.textContent = data.lastUpdate;

            const badge   = document.getElementById('live-badge');
            const liveDot = document.getElementById('live-dot');
            if (badge)   { badge.style.background = '#f0fdf4'; badge.style.color = '#16a34a'; }
            if (liveDot) { liveDot.style.background = '#16a34a'; }
        })
        .catch(() => {
            const badge   = document.getElementById('live-badge');
            const liveDot = document.getElementById('live-dot');
            if (badge)   { badge.style.background = '#fef3c7'; badge.style.color = '#d97706'; }
            if (liveDot) { liveDot.style.background = '#d97706'; }
        });
    }

    setInterval(poll, INTERVAL);
})();
</script>
@endpush