@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
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
                        @else
                            <div style="display:flex;flex-direction:column;gap:2px">
                                <a href="/admin/prediksi/{{ $item->id }}" class="pred-action-link">View</a>
                                <a href="/admin/prediksi/{{ $item->id }}/report" class="pred-action-link">Report</a>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
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
</div>

@endsection
