@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
<<<<<<< HEAD
@section('page-sub', 'Upload data historis & jalankan model Holt-Winters')

@section('content')

{{-- ── FLASH MESSAGES ── --}}
@if(session('success'))
<div class="alert alert-success" style="
    background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.35);
    color:#059669;border-radius:10px;padding:12px 18px;margin-bottom:1.25rem;
    display:flex;align-items:center;gap:10px;font-size:14px;font-weight:500">
    <i class="fas fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="
    background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
    color:#dc2626;border-radius:10px;padding:12px 18px;margin-bottom:1.25rem;
    display:flex;align-items:center;gap:10px;font-size:14px;font-weight:500">
    <i class="fas fa-circle-xmark"></i>
    {{ session('error') }}
</div>
@endif

@if(session('import_errors') && count(session('import_errors')) > 0)
<div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);
    border-radius:10px;padding:12px 18px;margin-bottom:1.25rem;font-size:13px;color:#b91c1c">
    <strong>Peringatan import:</strong>
    <ul style="margin:6px 0 0 18px;padding:0">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── 1. UPLOAD HISTORICAL DATA ── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-upload" style="color:var(--accent);margin-right:8px"></i>
            Upload Historical Data
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('prediksi.upload') ?? '#' }}" enctype="multipart/form-data"
              style="display:flex;align-items:flex-end;gap:1.2rem;flex-wrap:wrap">
            @csrf
            <div style="flex:1;min-width:220px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin-bottom:6px">
                    CSV/Excel File
                </label>
                <input type="file" name="file" accept=".csv,.xlsx,.xls"
                       style="width:100%;font-size:13.5px;color:var(--text-primary);
                              background:var(--input-bg,#f8f9fb);border:1.5px solid var(--border);
                              border-radius:8px;padding:8px 12px;outline:none">
            </div>
            <button type="submit"
                    style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
                           background:var(--accent);color:#fff;border:none;border-radius:10px;
                           font-size:13.5px;font-weight:600;cursor:pointer;transition:.2s;white-space:nowrap"
                    onmouseover="this.style.opacity='.85'"
                    onmouseout="this.style.opacity='1'">
                <i class="fas fa-upload"></i> Upload
            </button>
        </form>
        <p style="margin:10px 0 0;font-size:12px;color:var(--muted)">
            Format kolom: <code>commodity_name</code>, <code>harga_sekarang</code>, <code>date</code>,
            <code>harga_lama</code> (opsional), <code>satuan</code> (opsional)
        </p>
    </div>
</div>

{{-- ── 2. GENERATE PREDICTION ── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-wand-magic-sparkles" style="color:var(--accent);margin-right:8px"></i>
            Generate Prediction
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('prediksi.generate') }}"
              style="display:flex;flex-wrap:wrap;gap:1.2rem;align-items:flex-end">
            @csrf

            {{-- Commodity --}}
            <div style="flex:2;min-width:200px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin-bottom:6px">
                    Commodity <span style="color:#ef4444">*</span>
                </label>
                <select name="commodity_id" required
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);
                               border-radius:8px;background:var(--input-bg,#f8f9fb);
                               color:var(--text-primary);font-size:13.5px;outline:none">
                    <option value="">Pilih...</option>
                    @foreach($commodities as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Days --}}
            <div style="flex:1;min-width:150px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin-bottom:6px">
                    Days <span style="color:#ef4444">*</span>
                </label>
                <select name="steps" required
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);
                               border-radius:8px;background:var(--input-bg,#f8f9fb);
                               color:var(--text-primary);font-size:13.5px;outline:none">
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30" selected>30 Hari</option>
                    <option value="60">60 Hari</option>
                    <option value="90">90 Hari</option>
                </select>
            </div>

            {{-- Submit --}}
            <div>
                <button type="submit"
                        style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
                               background:var(--accent);color:#fff;border:none;border-radius:10px;
                               font-size:13.5px;font-weight:600;cursor:pointer;transition:.2s;white-space:nowrap"
                        onmouseover="this.style.opacity='.85'"
                        onmouseout="this.style.opacity='1'">
                    <i class="fas fa-wand-magic-sparkles"></i> Run Prediction Model
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── 3. PREDICTION HISTORY ── --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-clock-rotate-left" style="margin-right:6px;color:var(--accent)"></i>
            Prediction History
            <span style="font-size:12px;font-weight:400;color:var(--muted);margin-left:6px">
                * ({{ $predictions->total() }})
            </span>
        </div>
    </div>

    @if($predictions->total() > 0)
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Komoditas</th>
                    <th>Tanggal Generate</th>
                    <th>Horizon</th>
                    <th>MAPE</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($predictions as $pred)
                @php
                    $predId   = (string) $pred->id;
                    $metrics  = $pred->metrics ?? [];
                    $status   = $metrics['status'] ?? 'completed';
                    $mape     = $metrics['mape']   ?? null;
                    $badgeColor = match($status) {
                        'completed'     => '#10b981',
                        'review_needed' => '#f59e0b',
                        'failed'        => '#ef4444',
                        default         => '#6b7280',
                    };
                    $badgeLabel = match($status) {
                        'completed'     => 'COMPLETED',
                        'review_needed' => 'REVIEW NEEDED',
                        'failed'        => 'FAILED',
                        default         => strtoupper($status),
                    };
                @endphp
                <tr>
                    <td class="date-text">{{ $loop->iteration }}</td>
                    <td style="font-weight:600;color:var(--text-primary)">
                        {{ $pred->commodity_name ?? '—' }}
                    </td>
                    <td class="date-text">
                        {{ $pred->predicted_at
                            ? \Carbon\Carbon::parse($pred->predicted_at)->format('d M Y, H:i')
                            : '—' }}
                    </td>
                    <td class="date-text">{{ $pred->horizon_days ?? '—' }} Hari</td>
                    <td>
                        @if($mape !== null)
                            <span style="font-weight:700;color:{{ $mape <= 10 ? '#10b981' : ($mape <= 20 ? '#f59e0b' : '#ef4444') }}">
                                {{ number_format($mape, 2) }}%
                            </span>
=======
@section('page-sub', 'Generate commodity price predictions using machine learning models.')

@section('content')

{{-- TWO PANEL ROW --}}
<div class="prediksi-panels">

    {{-- PANEL 1: Import Historical Data --}}
    <div class="panel-card">
        <div class="panel-step-badge">1</div>
        <div class="panel-header">
            <div class="panel-title">Import Historical Data</div>
            <div class="panel-sub">Upload latest price records</div>
        </div>
        <form method="POST" action="/admin/prediksi/upload" enctype="multipart/form-data">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-file-arrow-up upload-zone-icon"></i>
                <p class="upload-zone-text">Drop Excel or CSV file</p>
                <input type="file" id="fileInput" name="file" accept=".xlsx,.csv" hidden>
                <button type="button" class="btn-secondary" onclick="document.getElementById('fileInput').click()">
                    Browse Files
                </button>
            </div>
            <div class="panel-footer-row">
                <a href="#" class="template-link"><i class="fas fa-download"></i> Template</a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> Validate & Upload
                </button>
            </div>
        </form>
    </div>

    {{-- PANEL 2: Model Parameters --}}
    <div class="panel-card">
        <div class="panel-step-badge">2</div>
        <div class="panel-header">
            <div class="panel-title">Model Parameters</div>
            <div class="panel-sub">Configure Prophet forecasting settings</div>
        </div>
        <form method="POST" action="/admin/prediksi/generate">
            @csrf
            <div class="param-grid">
                <div class="form-group-admin">
                    <label class="form-label-admin">COMMODITY FOCUS</label>
                    <select class="form-select" name="commodity_id">
                        <option>Beras Premium</option>
                        <option>Cabai Merah Keriting</option>
                        <option>Minyak Goreng Curah</option>
                        <option>Bawang Merah</option>
                        <option>Daging Ayam Ras</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">PREDICTION HORIZON</label>
                    <select class="form-select" name="period">
                        <option>30 Days</option>
                        <option>7 Days</option>
                        <option>14 Days</option>
                        <option>90 Days</option>
                    </select>
                </div>
                <div class="form-group-admin param-full">
                    <label class="form-label-admin">UPDATE FREQUENCY</label>
                    <select class="form-select" name="frequency">
                        <option>Daily Update</option>
                        <option>Weekly Update</option>
                        <option>Monthly Update</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-run-model">
                <i class="fas fa-wand-magic-sparkles"></i> Run Prediction Model
            </button>
        </form>
    </div>

</div>

{{-- PREDICTION HISTORY TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Prediction History</div>
        </div>
        <a href="#" class="view-all">View All Logs</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Commodity</th>
                <th>Region</th>
                <th>Horizon</th>
                <th>Accuracy (MAE)</th>
                <th>Accuracy (RMSE)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{--
                @foreach($predictions as $item)
                <tr>
                    <td class="date-text">{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y H:i') }}</td>
                    <td class="commodity-name">{{ $item->commodity->name }}</td>
                    <td class="date-text">{{ $item->region }}</td>
                    <td class="date-text">{{ $item->horizon }}</td>
                    <td class="date-text">{{ $item->mae }}</td>
                    <td class="date-text">{{ $item->rmse }}</td>
                    <td>
                        <span class="badge badge-status-{{ $item->status }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td>
                        @if($item->status === 'failed')
                            <a href="/admin/prediksi/{{ $item->id }}/retry" class="pred-action-link retry">Retry</a>
>>>>>>> fd823bc0833f5e144f68f61a74f5531fc4687a14
                        @else
                            <div style="display:flex;flex-direction:column;gap:2px">
                                <a href="/admin/prediksi/{{ $item->id }}" class="pred-action-link">View</a>
                                <a href="/admin/prediksi/{{ $item->id }}/report" class="pred-action-link">Report</a>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;
                                     background:{{ $badgeColor }}22;color:{{ $badgeColor }};
                                     font-size:11px;font-weight:700;letter-spacing:.04em">
                            {{ $badgeLabel }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex;gap:8px;justify-content:flex-end">
                            {{-- Detail --}}
                            <a href="{{ route('prediksi.show', $predId) }}"
                               style="display:inline-flex;align-items:center;gap:5px;
                                      font-size:12px;font-weight:600;color:var(--accent);
                                      text-decoration:none;padding:5px 12px;border:1.5px solid var(--accent);
                                      border-radius:7px;transition:.2s"
                               onmouseover="this.style.background='var(--accent)';this.style.color='#fff'"
                               onmouseout="this.style.background='transparent';this.style.color='var(--accent)'">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            {{-- Export --}}
                            <a href="{{ route('prediksi.export', $predId) }}"
                               style="display:inline-flex;align-items:center;gap:5px;
                                      font-size:12px;font-weight:600;color:#6b7280;
                                      text-decoration:none;padding:5px 12px;border:1.5px solid #6b728044;
                                      border-radius:7px;transition:.2s"
                               onmouseover="this.style.background='#6b7280';this.style.color='#fff'"
                               onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <i class="fas fa-download"></i> CSV
                            </a>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('prediksi.destroy', $predId) }}"
                                  onsubmit="return confirm('Hapus prediksi ini?')"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="display:inline-flex;align-items:center;gap:5px;
                                               font-size:12px;font-weight:600;color:#ef4444;
                                               background:transparent;border:1.5px solid #ef444444;
                                               border-radius:7px;padding:5px 12px;cursor:pointer;transition:.2s"
                                        onmouseover="this.style.background='#ef4444';this.style.color='#fff'"
                                        onmouseout="this.style.background='transparent';this.style.color='#ef4444'">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
<<<<<<< HEAD
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align:center;padding:3rem;color:var(--muted)">
        <i class="fas fa-inbox" style="font-size:2.5rem;margin-bottom:1rem;display:block"></i>
        <p style="font-size:14px">Belum ada riwayat prediksi. Generate prediksi pertama di atas.</p>
    </div>
    @endif

    @if($predictions->hasPages())
    <div style="padding:1rem 1.2rem;border-top:1px solid var(--border)">
        {{ $predictions->links() }}
    </div>
    @endif
=======
            --}}
            <tr>
                <td class="date-text">Oct 24, 2023<br>14:30</td>
                <td class="commodity-name">Beras Premium</td>
                <td class="date-text">National</td>
                <td class="date-text">30 Days</td>
                <td class="date-text">145.20</td>
                <td class="date-text">189.45</td>
                <td><span class="badge badge-status-completed">COMPLETED</span></td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:2px">
                        <a href="#" class="pred-action-link">View</a>
                        <a href="#" class="pred-action-link">Report</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">Oct 24, 2023<br>10:15</td>
                <td class="commodity-name">Cabai Merah</td>
                <td class="date-text">West Java</td>
                <td class="date-text">60 Days</td>
                <td class="date-text">890.50</td>
                <td class="date-text">1250.10</td>
                <td><span class="badge badge-status-review">REVIEW NEEDED</span></td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:2px">
                        <a href="#" class="pred-action-link">View</a>
                        <a href="#" class="pred-action-link">Report</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">Oct 23, 2023<br>16:45</td>
                <td class="commodity-name">Bawang Merah</td>
                <td class="date-text">East Java</td>
                <td class="date-text">90 Days</td>
                <td class="date-text">210.15</td>
                <td class="date-text">305.80</td>
                <td><span class="badge badge-status-completed">COMPLETED</span></td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:2px">
                        <a href="#" class="pred-action-link">View</a>
                        <a href="#" class="pred-action-link">Report</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">Oct 23, 2023<br>09:20</td>
                <td class="commodity-name">Minyak Goreng</td>
                <td class="date-text">National</td>
                <td class="date-text">30 Days</td>
                <td class="date-text">55.30</td>
                <td class="date-text">82.10</td>
                <td><span class="badge badge-status-failed">FAILED</span></td>
                <td>
                    <a href="#" class="pred-action-link retry">Retry</a>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing 4 of 128 results</span>
        <div class="pagination">
            <button class="page-btn">Previous</button>
            <button class="page-btn active">Next</button>
        </div>
    </div>
>>>>>>> fd823bc0833f5e144f68f61a74f5531fc4687a14
</div>

@endsection
