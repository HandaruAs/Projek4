@extends('layouts.app')

@section('title', 'Data Harga')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-text-primary-light dark:text-text-primary-dark text-3xl font-black tracking-tight mb-2">
            Data Harga Komoditas
        </h1>
        <p class="text-text-secondary-light dark:text-text-secondary-dark text-base">
            Informasi transparan harga pasar harian untuk berbagai komoditas pangan utama.
        </p>
    </div>

    {{-- FILTER --}}
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- Komoditas --}}
            <div class="flex flex-col gap-2">
                <label class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">category</span>
                    Komoditas
                </label>
                <div class="relative">
                    <select id="filter-komoditas" class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all appearance-none cursor-pointer">
                        <option value="semua">Semua Komoditas</option>
                        <option value="beras">Beras Premium</option>
                        <option value="cabai">Cabai Rawit</option>
                        <option value="bawang">Bawang Merah</option>
                        <option value="telur">Telur Ayam</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
                </div>
            </div>

            {{-- Daerah --}}
            <div class="flex flex-col gap-2">
                <label class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                    Daerah
                </label>
                <div class="relative">
                    <select id="filter-daerah" class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all appearance-none cursor-pointer">
                        <option value="semua">Semua Daerah</option>
                        <option value="jember">Jember</option>
                        <option value="surabaya">Surabaya</option>
                        <option value="malang">Malang</option>
                        <option value="banyuwangi">Banyuwangi</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
                </div>
            </div>

            {{-- Rentang Waktu --}}
            <div class="flex flex-col gap-2">
                <label class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
                    Rentang Waktu
                </label>
                <div class="relative">
                    <input id="filter-tanggal" type="text"
                           value="01 Nov 2023 - 07 Nov 2023"
                           placeholder="Pilih Tanggal"
                           class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all"/>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">date_range</span>
                </div>
            </div>

            {{-- Button --}}
            <div class="flex flex-col justify-end">
                <button onclick="tampilkanData()"
                        class="h-11 w-full bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined">filter_list</span>
                    Tampilkan Data
                </button>
            </div>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <div>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium">Harga Terbaru</p>
                <h3 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold">
                    Rp 14.500 <span class="text-xs font-normal text-text-secondary-light dark:text-text-secondary-dark">(Beras)</span>
                </h3>
            </div>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary">
                <span class="material-symbols-outlined">show_chart</span>
            </div>
            <div>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium">Perubahan Harian</p>
                <h3 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold flex items-center gap-1">
                    +Rp 200
                    <span class="text-green-600 text-xs font-bold bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">+1.5%</span>
                </h3>
            </div>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary">
                <span class="material-symbols-outlined">water_ec</span>
            </div>
            <div>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium">Status Volatilitas</p>
                <h3 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold">Stabil</h3>
            </div>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-center">
            <h3 class="font-bold text-text-primary-light dark:text-text-primary-dark">Tabel Rincian Harga</h3>
            <div class="flex gap-2">
                <button onclick="unduhCSV()"
                        class="flex items-center gap-2 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark border border-border-light dark:border-border-dark px-3 py-1.5 rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    <span class="material-symbols-outlined text-[16px]">download</span> Unduh CSV
                </button>
                <button onclick="window.print()"
                        class="flex items-center gap-2 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark border border-border-light dark:border-border-dark px-3 py-1.5 rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    <span class="material-symbols-outlined text-[16px]">print</span> Cetak
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tabel-harga">
                <thead class="bg-background-light dark:bg-background-dark/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Komoditas</th>
                        <th class="px-6 py-4 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Daerah</th>
                        <th class="px-6 py-4 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Harga (Rp)</th>
                        <th class="px-6 py-4 text-xs font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Tren</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark" id="tbody-harga">
                    @php
                        $dataHarga = [
                            ['tanggal' => '07 Nov 2023', 'komoditas' => 'Beras Premium',  'daerah' => 'Jember',     'harga' => '14.500', 'tren' => 'up',   'selisih' => '+200'],
                            ['tanggal' => '07 Nov 2023', 'komoditas' => 'Cabai Rawit',    'daerah' => 'Jember',     'harga' => '65.000', 'tren' => 'down', 'selisih' => '-1.200'],
                            ['tanggal' => '07 Nov 2023', 'komoditas' => 'Bawang Merah',   'daerah' => 'Surabaya',   'harga' => '32.000', 'tren' => 'flat', 'selisih' => '0'],
                            ['tanggal' => '06 Nov 2023', 'komoditas' => 'Beras Premium',  'daerah' => 'Jember',     'harga' => '14.300', 'tren' => 'up',   'selisih' => '+300'],
                            ['tanggal' => '06 Nov 2023', 'komoditas' => 'Telur Ayam',     'daerah' => 'Banyuwangi', 'harga' => '27.500', 'tren' => 'down', 'selisih' => '-500'],
                        ];
                    @endphp

                    @foreach($dataHarga as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $row['tanggal'] }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $row['komoditas'] }}</td>
                        <td class="px-6 py-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $row['daerah'] }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-text-primary-light dark:text-text-primary-dark">{{ $row['harga'] }}</td>
                        <td class="px-6 py-4">
                            @if($row['tren'] === 'up')
                                <span class="flex items-center gap-1 text-green-600 font-bold text-xs bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full w-fit">
                                    <span class="material-symbols-outlined text-[16px]">trending_up</span> {{ $row['selisih'] }}
                                </span>
                            @elseif($row['tren'] === 'down')
                                <span class="flex items-center gap-1 text-red-600 font-bold text-xs bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full w-fit">
                                    <span class="material-symbols-outlined text-[16px]">trending_down</span> {{ $row['selisih'] }}
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-amber-600 font-bold text-xs bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-full w-fit">
                                    <span class="material-symbols-outlined text-[16px]">trending_flat</span> {{ $row['selisih'] }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="px-6 py-4 bg-background-light dark:bg-background-dark/50 border-t border-border-light dark:border-border-dark flex items-center justify-between">
            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark font-medium">
                Menampilkan 5 dari 150 data
            </p>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark hover:bg-white dark:hover:bg-surface-dark">
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded border border-primary bg-primary text-white text-xs font-bold">1</button>
                <button class="w-8 h-8 flex items-center justify-center rounded border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark text-xs font-bold hover:bg-white dark:hover:bg-surface-dark">2</button>
                <button class="w-8 h-8 flex items-center justify-center rounded border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark text-xs font-bold hover:bg-white dark:hover:bg-surface-dark">3</button>
                <button class="w-8 h-8 flex items-center justify-center rounded border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark hover:bg-white dark:hover:bg-surface-dark">
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER INFO --}}
    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 p-6 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined">verified</span>
            </div>
            <div>
                <h4 class="font-bold text-text-primary-light dark:text-text-primary-dark">Data Resmi & Terverifikasi</h4>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark leading-relaxed">
                    Seluruh data yang ditampilkan bersumber dari pasar induk dan dikelola melalui infrastruktur MongoDB.
                </p>
            </div>
        </div>
        <div class="shrink-0">
            <button class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-lg font-bold text-sm transition-colors shadow-sm">
                Bantuan Data
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function tampilkanData() {
    const komoditas = document.getElementById('filter-komoditas').value;
    const daerah    = document.getElementById('filter-daerah').value;
    showToast('Filter diterapkan: ' + komoditas + ' - ' + daerah, 'success');
}

function unduhCSV() {
    const rows   = document.querySelectorAll('#tabel-harga tbody tr');
    let csv      = 'Tanggal,Komoditas,Daerah,Harga,Tren\n';

    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        const line = [
            cols[0]?.innerText.trim(),
            cols[1]?.innerText.trim(),
            cols[2]?.innerText.trim(),
            cols[3]?.innerText.trim(),
            cols[4]?.innerText.trim(),
        ].join(',');
        csv += line + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = 'data-harga-simopang.csv';
    a.click();
    showToast('CSV berhasil diunduh!', 'success');
}
</script>
@endpush