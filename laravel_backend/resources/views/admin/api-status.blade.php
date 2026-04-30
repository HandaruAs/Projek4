@extends('layouts.layout')

@section('title', 'API Status')
@section('page-title', 'API Status Monitor')
@section('page-sub', 'Real-time health check for all internal API services.')

@section('content')

{{-- ── SUMMARY CARDS ── --}}
<div class="stats-grid" id="summaryCards">

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Services</div>
            <div class="stat-value" id="sumTotal">—</div>
            <div class="stat-change neutral">
                <i class="fas fa-circle-nodes"></i>
                <span class="stat-change-sub">registered endpoints</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-circle-nodes"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Online</div>
            <div class="stat-value" id="sumOnline" style="color:#16a34a;">—</div>
            <div class="stat-change up">
                <i class="fas fa-circle-check"></i>
                <span class="stat-change-sub">services operational</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-circle-check"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Offline</div>
            <div class="stat-value" id="sumOffline" style="color:#dc2626;">—</div>
            <div class="stat-change down">
                <i class="fas fa-circle-xmark"></i>
                <span class="stat-change-sub">services down</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-circle-xmark"></i></div>
    </div>

</div>

{{-- ── API STATUS TABLE ── --}}
<div class="table-card">

    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-server" style="color:var(--accent,#f97316); margin-right:6px;"></i>
                Service Health
            </div>
            <div class="table-subtitle">
                Auto-refreshes every 5 seconds. &nbsp;
                <span id="lastChecked" style="color:var(--text-muted,#94a3b8); font-size:12px;"></span>
            </div>
        </div>
        <div class="table-actions">
            <button class="view-all" id="btnRefresh" onclick="runCheck()" style="border:none; background:none; cursor:pointer;">
                <i class="fas fa-rotate-right" id="iconRefresh"></i> Refresh
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:36px;"></th>
                <th>Group</th>
                <th>Service</th>
                <th>Endpoint</th>
                <th>Status</th>
                <th>HTTP Code</th>
                <th>Result</th>
                <th>Latency</th>
            </tr>
        </thead>
        <tbody id="apiTableBody">
            {{-- Skeleton rows --}}
            @for($i = 0; $i < 8; $i++)
            <tr class="skeleton-row">
                <td><div class="skel skel-circle"></div></td>
                <td><div class="skel" style="width:80px; height:13px;"></div></td>
                <td><div class="skel" style="width:120px; height:13px;"></div></td>
                <td><div class="skel" style="width:160px; height:13px;"></div></td>
                <td><div class="skel" style="width:70px; height:22px; border-radius:999px;"></div></td>
                <td><div class="skel" style="width:40px; height:13px;"></div></td>
                <td><div class="skel" style="width:90px; height:22px; border-radius:999px;"></div></td>
                <td><div class="skel" style="width:60px; height:13px;"></div></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text" id="footerText">Checking services…</span>
        <div style="display:flex; align-items:center; gap:12px; font-size:12px; color:var(--text-muted,#94a3b8);">
            <span><i class="fas fa-circle" style="color:#16a34a; font-size:8px;"></i> Online</span>
            <span><i class="fas fa-circle" style="color:#dc2626; font-size:8px;"></i> Offline</span>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* ── Method badge ── */
    .method-get    { background:#dbeafe; color:#1d4ed8; font-size:11px; font-family:'DM Mono',monospace; padding:2px 7px; border-radius:4px; font-weight:600; }
    .method-post   { background:#dcfce7; color:#15803d; font-size:11px; font-family:'DM Mono',monospace; padding:2px 7px; border-radius:4px; font-weight:600; }
    .method-put    { background:#fef9c3; color:#a16207; font-size:11px; font-family:'DM Mono',monospace; padding:2px 7px; border-radius:4px; font-weight:600; }
    .method-delete { background:#fee2e2; color:#b91c1c; font-size:11px; font-family:'DM Mono',monospace; padding:2px 7px; border-radius:4px; font-weight:600; }

    /* ── Group badge ── */
    .group-badge {
        background: var(--surface2, #f1f5f9);
        color: var(--text-muted, #64748b);
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* ── Latency colors ── */
    .lat-fast   { color:#16a34a; font-family:'DM Mono',monospace; font-size:13px; }
    .lat-medium { color:#d97706; font-family:'DM Mono',monospace; font-size:13px; }
    .lat-slow   { color:#dc2626; font-family:'DM Mono',monospace; font-size:13px; }

    /* ── Status badges ── */
    .badge-online, .badge-offline {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; padding: 3px 10px; border-radius: 999px; font-weight: 500;
    }
    .badge-online  { background:#dcfce7; color:#15803d; }
    .badge-offline { background:#fee2e2; color:#b91c1c; }
    .dot { width:6px; height:6px; border-radius:50%; display:inline-block; }
    .dot-online  { background:#16a34a; }
    .dot-offline { background:#dc2626; }

    /* ── Result badges ── */
    .result-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; padding: 3px 10px; border-radius: 999px; font-weight: 500;
        white-space: nowrap;
    }
    .result-success    { background:#dcfce7; color:#15803d; }
    .result-auth       { background:#fef9c3; color:#a16207; }
    .result-notfound   { background:#f1f5f9; color:#64748b; }
    .result-validation { background:#fef3c7; color:#b45309; }
    .result-badrequest { background:#fef3c7; color:#b45309; }
    .result-error      { background:#fee2e2; color:#b91c1c; }

    /* ── Endpoint text ── */
    .ep-text { font-family:'DM Mono',monospace; font-size:12px; color:var(--text-muted,#64748b); }

    /* ── Spin ── */
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display:inline-block; animation: spin .7s linear infinite; }
</style>
@endpush

@push('scripts')
<script>
// ── Ekstrak HTTP method dari desc ──
function methodBadge(desc) {
    const m = desc.trim().split(' ')[0].toUpperCase();
    const cls = { GET:'method-get', POST:'method-post', PUT:'method-put', DELETE:'method-delete' }[m] ?? 'method-get';
    return `<span class="${cls}">${m}</span>`;
}

// ── Ekstrak path dari desc ──
function pathOnly(desc) {
    const parts = desc.trim().split(' ');
    return parts.length > 1 ? parts.slice(1).join(' ') : desc;
}

// ── Result badge berdasarkan HTTP code ──
function resultBadge(httpCode) {
    if (httpCode === null || httpCode === undefined)
        return `<span style="color:#94a3b8;">—</span>`;

    if (httpCode >= 200 && httpCode < 300)
        return `<span class="result-badge result-success"><i class="fas fa-check" style="font-size:10px;"></i> Success</span>`;

    if (httpCode === 401)
        return `<span class="result-badge result-auth"><i class="fas fa-lock" style="font-size:10px;"></i> Auth Required</span>`;

    if (httpCode === 403)
        return `<span class="result-badge result-auth"><i class="fas fa-ban" style="font-size:10px;"></i> Forbidden</span>`;

    if (httpCode === 404)
        return `<span class="result-badge result-notfound"><i class="fas fa-circle-minus" style="font-size:10px;"></i> Not Found</span>`;

    if (httpCode === 422)
        return `<span class="result-badge result-validation"><i class="fas fa-triangle-exclamation" style="font-size:10px;"></i> Validation</span>`;

    if (httpCode === 400)
        return `<span class="result-badge result-badrequest"><i class="fas fa-triangle-exclamation" style="font-size:10px;"></i> Bad Request</span>`;

    if (httpCode >= 500)
        return `<span class="result-badge result-error"><i class="fas fa-circle-xmark" style="font-size:10px;"></i> Server Error</span>`;

    return `<span class="result-badge result-notfound">${httpCode}</span>`;
}

async function runCheck() {
    const tbody   = document.getElementById('apiTableBody');
    const icon    = document.getElementById('iconRefresh');
    const footer  = document.getElementById('footerText');
    const lastChk = document.getElementById('lastChecked');

    icon.classList.add('spin');
    footer.textContent = 'Checking services…';

    try {
        const res = await fetch('{{ route("admin.api-status.check") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await res.json();

        // ── Update summary cards ──
        document.getElementById('sumTotal').textContent   = data.total;
        document.getElementById('sumOnline').textContent  = data.total_online;
        document.getElementById('sumOffline').textContent = data.total_offline;
        lastChk.textContent = 'Last checked: ' + new Date(data.checked_at).toLocaleTimeString('id-ID');

        // ── Render rows ──
        tbody.innerHTML = data.services.map(s => {
            const online = s.status === 'online';

            const badge = online
                ? `<span class="badge-online"><span class="dot dot-online"></span> Online</span>`
                : `<span class="badge-offline"><span class="dot dot-offline"></span> Offline</span>`;

            const httpCode = s.http_code
                ? `<span style="font-family:'DM Mono',monospace; font-size:13px;">${s.http_code}</span>`
                : `<span style="color:#94a3b8;">—</span>`;

            let latency = `<span style="color:#94a3b8;">—</span>`;
            if (s.latency_ms !== null && s.latency_ms !== undefined) {
                const cls = s.latency_ms < 200 ? 'lat-fast' : s.latency_ms < 600 ? 'lat-medium' : 'lat-slow';
                latency = `<span class="${cls}">${s.latency_ms} ms</span>`;
            }

            const dotIcon = online
                ? `<i class="fas fa-circle" style="color:#16a34a; font-size:10px;"></i>`
                : `<i class="fas fa-circle" style="color:#dc2626; font-size:10px;"></i>`;

            return `
            <tr>
                <td style="text-align:center;">${dotIcon}</td>
                <td><span class="group-badge">${s.group}</span></td>
                <td class="commodity-name">${s.name}</td>
                <td class="ep-text">${methodBadge(s.desc)} ${pathOnly(s.desc)}</td>
                <td>${badge}</td>
                <td>${httpCode}</td>
                <td>${resultBadge(s.http_code)}</td>
                <td>${latency}</td>
            </tr>`;
        }).join('');

        footer.textContent = `Showing ${data.total} endpoints — ${data.total_online} online, ${data.total_offline} offline`;

    } catch (err) {
        tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align:center; padding:2rem; color:#dc2626;">
                <i class="fas fa-triangle-exclamation"></i>
                Gagal memuat data. Pastikan route <code>admin.api-status.check</code> sudah terdaftar.
            </td>
        </tr>`;
        footer.textContent = 'Error fetching status.';
    } finally {
        icon.classList.remove('spin');
    }
}

document.addEventListener('DOMContentLoaded', runCheck);
setInterval(runCheck, 5_000);
</script>
@endpush