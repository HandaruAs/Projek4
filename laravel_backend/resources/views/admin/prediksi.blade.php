@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga')
@section('page-sub', 'Hasil prediksi harga komoditas menggunakan Holt-Winters Exponential Smoothing.')

@push('styles')
<style>
    /* Searchable Dropdown Styles */
    .searchable-dropdown {
        position: relative;
        width: 100%;
    }

    .dropdown-search-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
        background: white;
    }

    .dropdown-search-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-top: 4px;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    .dropdown-options.show {
        display: block;
    }

    .dropdown-option {
        padding: 10px 12px;
        cursor: pointer;
        transition: background 0.15s ease;
        font-size: 14px;
        border-bottom: 1px solid #f3f4f6;
    }

    .dropdown-option:hover {
        background: #f3f4f6;
    }

    .dropdown-option.selected {
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 500;
    }

    .dropdown-option.hidden {
        display: none;
    }

    .no-results {
        padding: 10px 12px;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
    }

    .mape-good { font-weight:600; color:#16a34a; }
    .mape-warn { font-weight:600; color:#d97706; }
    .mape-bad { font-weight:600; color:#dc2626; }
    .mape-muted { font-weight:600; color:var(--muted,#9ca3af); }

    .search-result-card {
        transition: all 0.2s ease;
    }

    .search-result-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #3b82f6;
    }
</style>
@endpush

@section('content')

{{-- ── SEARCH ENGINE BARU ── --}}
<div class="table-card" style="margin-bottom:1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-search" style="color: #3b82f6;"></i>
                Cari Komoditas
            </div>
            <div class="table-subtitle">Temukan komoditas dengan cepat berdasarkan nama atau kategori</div>
        </div>
    </div>
    <div style="padding: 1rem 1.5rem 1.5rem 1.5rem;">
        <form method="GET" action="{{ route('user.prediksi') }}" id="searchForm">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 3; min-width: 250px;">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px;"></i>
                        <input type="text"
                               name="search_komoditas"
                               id="searchKomoditas"
                               value="{{ request('search_komoditas') }}"
                               placeholder="Cari komoditas... (contoh: bawang, telur, minyak)"
                               style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; transition: all 0.2s; background: white;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                               onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        @if(request('search_komoditas'))
                            <a href="{{ route('user.prediksi') }}"
                               style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; text-decoration: none; padding: 4px 8px; border-radius: 20px; background: #f3f4f6; font-size: 12px;">
                                <i class="fas fa-times"></i> Hapus
                            </a>
                        @endif
                    </div>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <select name="search_kategori" id="searchKategori" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; background: white; outline: none; cursor: pointer;">
                        <option value="">Semua Kategori</option>
                        @php
                            $kategoriList = isset($kategoriList) ? $kategoriList : [];
                        @endphp
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('search_kategori') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    @if(request('search_komoditas') || request('search_kategori'))
                        <a href="{{ route('user.prediksi') }}" style="background: #f3f4f6; color: #374151; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-undo-alt"></i> Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- Informasi hasil pencarian --}}
            @if(request('search_komoditas') || request('search_kategori'))
                <div style="margin-top: 16px; padding: 12px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <div>
                            <i class="fas fa-chart-simple" style="color: #3b82f6;"></i>
                            <strong style="color: #1e40af;">Hasil pencarian:</strong>
                            <span style="color: #1e3a8a;">
                                Ditemukan <strong>{{ isset($searchResults) ? $searchResults->count() : 0 }}</strong> komoditas
                                @if(request('search_komoditas'))
                                    untuk kata kunci "<strong>{{ request('search_komoditas') }}</strong>"
                                @endif
                                @if(request('search_kategori') && request('search_kategori') != '')
                                    di kategori "<strong>{{ request('search_kategori') }}</strong>"
                                @endif
                            </span>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #6b7280;">
                                <i class="fas fa-lightbulb"></i> Klik komoditas di bawah untuk melihat prediksi
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ── HASIL PENCARIAN KOMODITAS (GRID VIEW) ── --}}
@if(request('search_komoditas') || request('search_kategori'))
    @if(isset($searchResults) && $searchResults->count() > 0)
    <div class="table-card" style="margin-bottom: 1.5rem;">
        <div class="table-header">
            <div>
                <div class="table-title">
                    <i class="fas fa-list-ul"></i>
                    Hasil Pencarian Komoditas
                </div>
                <div class="table-subtitle">Klik salah satu komoditas untuk melihat detail prediksi</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; padding: 1.5rem;">
            @foreach($searchResults as $komoditas)
            <a href="{{ route('user.prediksi', ['komoditas' => $komoditas->name]) }}"
               class="search-result-card"
               style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; text-decoration: none; transition: all 0.2s ease;">
                <div>
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                        <i class="fas fa-box" style="color: #3b82f6; margin-right: 8px;"></i>
                        {{ $komoditas->name }}
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        <i class="fas fa-tag"></i> {{ $komoditas->kategori ?? 'Tidak ada kategori' }}
                    </div>
                </div>
                <div style="background: #eff6ff; padding: 6px 12px; border-radius: 20px;">
                    <i class="fas fa-arrow-right" style="color: #3b82f6;"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @elseif(request('search_komoditas') || request('search_kategori'))
    <div class="table-card" style="margin-bottom: 1.5rem; background: #fef2f2; border-left: 4px solid #ef4444;">
        <div style="padding: 2rem; text-align: center;">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #ef4444; margin-bottom: 1rem;"></i>
            <div style="font-weight: 600; color: #991b1b; margin-bottom: 4px;">Komoditas tidak ditemukan</div>
            <div style="font-size: 13px; color: #7f1d1d;">
                Tidak ada komoditas yang cocok
                @if(request('search_komoditas')) dengan kata kunci "<strong>{{ request('search_komoditas') }}</strong>" @endif
                @if(request('search_kategori') && request('search_kategori') != '') di kategori "<strong>{{ request('search_kategori') }}</strong>" @endif
            </div>
            <div style="margin-top: 16px;">
                <a href="{{ route('user.prediksi') }}" style="display: inline-block; padding: 8px 20px; background: #f3f4f6; color: #374151; border-radius: 8px; text-decoration: none;">
                    <i class="fas fa-undo-alt"></i> Reset Pencarian
                </a>
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ── FILTER KOMODITAS (DROPDOWN) ── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <form method="GET" action="{{ route('user.prediksi') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:250px">
                <label style="font-size:11px;font-weight:700;letter-spacing:.05em;
                              color:var(--muted);display:block;margin-bottom:6px">
                    FILTER KOMODITAS (DROPDOWN)
                </label>

                {{-- Searchable Dropdown Container --}}
                <div class="searchable-dropdown" id="searchableDropdown">
                    <input type="text"
                           class="dropdown-search-input"
                           id="komoditasSearchInput"
                           placeholder="Cari komoditas..."
                           autocomplete="off">
                    <input type="hidden" name="komoditas" id="selectedKomoditas" value="{{ $selectedNama ?? '' }}">

                    <div class="dropdown-options" id="dropdownOptions">
                        <div class="dropdown-option" data-value="">
                            — Semua Komoditas —
                        </div>
                        @foreach($komoditasList as $nama)
                            <div class="dropdown-option" data-value="{{ $nama }}"
                                 data-name="{{ strtolower($nama) }}">
                                {{ $nama }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <button type="submit" class="btn-primary" style="background:#3b82f6">
                    <i class="fas fa-check"></i> Pilih
                </button>
            </div>
            @if($selectedNama)
                <div>
                    <a href="{{ route('user.prediksi') }}" class="btn-secondary" style="display:inline-block; padding:10px 16px; background:#f3f4f6; color:#374151; border-radius:8px; text-decoration:none;">
                        <i class="fas fa-times"></i> Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ── STAT CARDS (tampil jika ada prediksi terpilih) ── --}}
@if($prediction ?? false)
@php
    $payload   = $prediction->payload ?? [];
    $acc       = $payload['accuracy'] ?? [];
    $hargaKini = $payload['harga_terakhir'] ?? 0;
    $satuan    = $payload['satuan'] ?? 'kg';
    $estimasiHarga = $estimasiHarga ?? 0;
    $trenPersen = $trenPersen ?? 0;
    $kepercayaan = $kepercayaan ?? null;
@endphp
<div class="u-pred-stat-row" style="margin-bottom:1.5rem">

    <div class="u-pred-stat-card u-pred-stat-card--blue">
        <div class="u-pred-stat-card__label">Estimasi Harga (30 Hari)</div>
        <div class="u-pred-stat-card__value">
            Rp {{ number_format($estimasiHarga ?? 0, 0, ',', '.') }}
            <span class="u-pred-stat-card__unit">/{{ $satuan }}</span>
        </div>
        <div class="u-pred-stat-card__note">
            Harga saat ini: Rp {{ number_format($hargaKini, 0, ',', '.') }}
        </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--rose">
        <div class="u-pred-stat-card__label">Tren Prediksi (30 Hari)</div>
        <div class="u-pred-stat-card__value {{ ($trenPersen ?? 0) >= 0 ? 'u-pred-stat-card__value--up' : 'u-pred-stat-card__value--down' }}">
            {{ ($trenPersen ?? 0) >= 0 ? '+' : '' }}{{ $trenPersen ?? '0' }}%
        </div>
        <div class="u-pred-stat-card__sub">
            {{ ($trenPersen ?? 0) >= 0 ? 'Harga diprediksi cenderung naik.' : 'Harga diprediksi cenderung turun.' }}
        </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--blue">
        <div class="u-pred-stat-card__label">Tingkat Kepercayaan AI</div>
        <div class="u-pred-stat-card__value u-pred-stat-card__value--conf">
            {{ $kepercayaan ? number_format($kepercayaan, 1).'%' : '—' }}
        </div>
        @if($kepercayaan)
        <div class="u-conf-bar-wrap">
            <div class="u-conf-bar">
                <div class="u-conf-bar__fill" style="width:{{ min($kepercayaan, 100) }}%"></div>
            </div>
        </div>
        @endif
        <div class="u-pred-stat-card__sub">
            MAPE: {{ $prediction->accuracy_mape !== null ? number_format($prediction->accuracy_mape, 2).'%' : '—' }}
        </div>
    </div>

</div>

{{-- ── TABEL MINGGUAN ── --}}
<div class="u-table-card" style="margin-bottom:1.5rem">
    <div class="u-table-card__header">
        <div class="u-table-card__title">
            Detail Prediksi Mingguan — {{ $selectedNama }}
            <small style="font-weight:400;color:var(--muted);font-size:12px">
                (Generate: {{ $prediction->created_at?->format('d M Y H:i') }})
            </small>
        </div>
    </div>
    <div class="u-table-wrap">
        <table class="u-table">
            <thead>
                <tr>
                    <th>Minggu</th>
                    <th>Periode</th>
                    <th>Estimasi Harga Rata-rata</th>
                    <th>Perubahan vs Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prediksiMingguan ?? [] as $row)
                <tr>
                    <td class="u-pred-week">{{ $row['minggu'] }}</td>
                    <td class="u-table__date">{{ $row['periode'] }}</td>
                    <td class="u-table__harga">
                        Rp {{ number_format($row['estimasi'], 0, ',', '.') }}
                    </td>
                    <td>
                        <span style="font-weight:600;
                            color:{{ ($row['delta_pct'] ?? 0) >= 0 ? '#ef4444' : '#10b981' }}">
                            {{ ($row['delta_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $row['delta_pct'] ?? 0 }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:2rem;color:var(--muted)">
                        Tidak ada data prediksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
{{-- Belum pilih komoditas atau belum ada prediksi --}}
<div style="text-align:center;padding:3rem;color:var(--muted)">
    <i class="fas fa-chart-line" style="font-size:2.5rem;display:block;margin-bottom:1rem;opacity:.3"></i>
    <div style="font-weight:600;margin-bottom:4px">
        {{ count($komoditasList ?? []) > 0 ? 'Pilih komoditas untuk melihat prediksi' : 'Belum ada prediksi' }}
    </div>
    <div style="font-size:13px">
        {{ count($komoditasList ?? []) > 0
            ? 'Gunakan filter dropdown di atas atau cari komoditas dengan search engine.'
            : 'Admin belum melakukan generate prediksi. Silakan hubungi administrator.' }}
    </div>
</div>
@endif

{{-- ── TABEL SEMUA RIWAYAT PREDIKSI (seluruh komoditas) ── --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-clock-rotate-left" style="color:var(--accent);margin-right:8px"></i>
                Semua Riwayat Prediksi
            </div>
            <div class="table-sub">Data prediksi yang telah di-generate oleh admin</div>
        </div>
    </div>

    @if(isset($allPredictions) && $allPredictions->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Komoditas</th>
                <th>Generated</th>
                <th>Days</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allPredictions as $i => $item)
            @php
                $mape      = $item->accuracy_mape;
                $mapeClass = $mape === null ? 'mape-muted'
                           : ($mape < 5 ? 'mape-good' : ($mape < 10 ? 'mape-warn' : 'mape-bad'));
            @endphp
            <tr>
                <td class="date-text">{{ $allPredictions->firstItem() + $i }}</td>
                <td class="commodity-name">{{ $item->commodity_name }}</td>
                <td class="date-text">
                    {{ $item->created_at?->format('d M Y H:i') ?? '—' }}
                </td>
                <td class="date-text">{{ $item->steps ?? '—' }} days</td>
                <td class="date-text">
                    {{ $item->accuracy_mae !== null ? number_format($item->accuracy_mae, 0) : '—' }}
                </td>
                <td class="date-text">
                    {{ $item->accuracy_rmse !== null ? number_format($item->accuracy_rmse, 0) : '—' }}
                </td>
                <td>
                    @if($mape !== null)
                        <span class="{{ $mapeClass }}">{{ number_format($mape, 2) }}%</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                 padding:3px 10px;border-radius:20px;font-size:11.5px;
                                 font-weight:600;background:#d1fae5;color:#065f46">
                        <i class="fas fa-circle" style="font-size:6px"></i>
                        {{ ucfirst($item->status ?? 'completed') }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Menampilkan {{ $allPredictions->firstItem() }}–{{ $allPredictions->lastItem() }}
            dari {{ $allPredictions->total() }} prediksi
        </span>
        {{ $allPredictions->links('components.pagination') }}
    </div>
    @else
    <div class="empty-pred">
        <i class="fas fa-chart-line"></i>
        <div style="font-weight:600; margin-bottom:4px">Belum ada riwayat prediksi</div>
        <div style="font-size:13px">Generate prediksi pertama menggunakan halaman admin.</div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
// Searchable Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('komoditasSearchInput');
    const dropdownOptions = document.getElementById('dropdownOptions');
    const selectedHidden = document.getElementById('selectedKomoditas');
    const options = document.querySelectorAll('.dropdown-option');

    let isOpen = false;
    let selectedValue = selectedHidden.value;

    // Function to toggle dropdown
    function toggleDropdown(show) {
        if (show === undefined) {
            isOpen = !isOpen;
        } else {
            isOpen = show;
        }

        if (isOpen) {
            dropdownOptions.classList.add('show');
        } else {
            dropdownOptions.classList.remove('show');
        }
    }

    // Function to filter options based on search
    function filterOptions(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        let hasVisible = false;

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            const dataName = option.getAttribute('data-name') || '';

            if (term === '' || text.includes(term) || dataName.includes(term)) {
                option.classList.remove('hidden');
                hasVisible = true;
            } else {
                option.classList.add('hidden');
            }
        });

        // Add "no results" message if needed
        let noResultsMsg = dropdownOptions.querySelector('.no-results');
        if (!hasVisible && !noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.textContent = 'Komoditas tidak ditemukan';
            dropdownOptions.appendChild(noResultsMsg);
        } else if (hasVisible && noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Function to select an option
    function selectOption(optionElement) {
        const value = optionElement.getAttribute('data-value');
        const text = optionElement.textContent;

        // Update hidden input
        selectedHidden.value = value;

        // Update search input text
        searchInput.value = text;

        // Update selected class
        options.forEach(opt => opt.classList.remove('selected'));
        optionElement.classList.add('selected');

        // Close dropdown
        toggleDropdown(false);
    }

    // Handle search input click
    if (searchInput) {
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(true);
            filterOptions(searchInput.value);
        });

        // Handle search input typing
        searchInput.addEventListener('input', function(e) {
            filterOptions(this.value);
            if (!isOpen) {
                toggleDropdown(true);
            }
        });
    }

    // Handle option clicks
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            selectOption(this);
            // Auto submit form after selection
            const form = this.closest('form');
            if (form) form.submit();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('searchableDropdown');
        if (container && !container.contains(e.target)) {
            toggleDropdown(false);
        }
    });

    // Set initial selected value if exists
    if (selectedValue) {
        options.forEach(option => {
            if (option.getAttribute('data-value') === selectedValue) {
                selectOption(option);
            }
        });
    }

    // Prevent form submit on Enter in search input
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const visibleOptions = Array.from(options).filter(opt => !opt.classList.contains('hidden'));
                if (visibleOptions.length === 1 && visibleOptions[0].getAttribute('data-value') !== '') {
                    selectOption(visibleOptions[0]);
                }
                setTimeout(() => {
                    const form = searchInput.closest('form');
                    if (form) form.submit();
                }, 100);
            }
        });
    }
});
</script>
@endpush
