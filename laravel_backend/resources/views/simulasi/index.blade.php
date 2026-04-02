@extends('layouts.app')

@section('title', 'Simulasi Pengeluaran AI')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- BREADCRUMB + HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs font-medium text-primary mb-2">
            <a href="{{ route('dashboard') }}" class="hover:underline">Beranda</a>
            <span>/</span>
            <span class="text-text-secondary-light dark:text-text-secondary-dark">Simulasi Pengeluaran AI</span>
        </div>
        <h1 class="text-text-primary-light dark:text-text-primary-dark text-3xl font-black tracking-tight mb-2">
            Simulasi Pengeluaran AI
        </h1>
        <p class="text-text-secondary-light dark:text-text-secondary-dark text-base max-w-2xl">
            Estimasi pengeluaran Anda berdasarkan tren harga komoditas terkini dan prediksi cerdas untuk perencanaan finansial yang lebih baik.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- KOLOM KIRI: INPUT --}}
        <div class="lg:col-span-4 flex flex-col gap-6">

            {{-- Form Input --}}
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-border-light dark:border-border-dark p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">edit_note</span>
                    </div>
                    <h3 class="font-bold text-text-primary-light dark:text-text-primary-dark">Input Data Konsumsi</h3>
                </div>

                <div class="space-y-5">
                    {{-- Pilih Komoditas --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">
                            Pilih Komoditas
                        </label>
                        <div class="relative">
                            <select id="sim-komoditas"
                                    class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary appearance-none cursor-pointer">
                                <option value="14500">Beras (Premium)</option>
                                <option value="65000">Cabai Rawit</option>
                                <option value="32000">Bawang Merah</option>
                                <option value="27500">Telur Ayam</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    {{-- Konsumsi per Minggu --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">
                            Konsumsi per Minggu (Kg/Liter)
                        </label>
                        <div class="relative">
                            <input id="sim-konsumsi" type="number" step="0.1" min="0.1" value="0.5"
                                   class="w-full h-11 pl-4 pr-12 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary"/>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark text-xs font-bold">kg</span>
                        </div>
                        <p class="text-[11px] italic text-text-secondary-light dark:text-text-secondary-dark">
                            *Data ini akan digunakan untuk menghitung total bulanan.
                        </p>
                    </div>

                    <button onclick="hitungEstimasi()"
                            id="btn-hitung"
                            class="w-full h-12 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-100 dark:shadow-none mt-2">
                        <span class="material-symbols-outlined text-xl" id="btn-hitung-icon">calculate</span>
                        <span id="btn-hitung-text">Hitung Estimasi</span>
                    </button>
                </div>
            </div>

            {{-- Wawasan AI --}}
            <div class="bg-blue-50/50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-5 flex gap-4">
                <div class="text-primary mt-0.5">
                    <span class="material-symbols-outlined text-2xl">info</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-primary mb-1">Wawasan AI</h4>
                    <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark leading-relaxed" id="wawasan-text">
                        AI kami memprediksi kenaikan harga beras sekitar 2.5% bulan depan dikarenakan faktor musim panen yang bergeser.
                    </p>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: HASIL --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Harga Sekarang vs Prediksi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Harga Saat Ini --}}
                <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-6 rounded-xl">
                    <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">
                        Harga Saat Ini
                    </p>
                    <h3 class="text-2xl font-black text-text-primary-light dark:text-text-primary-dark mb-4">
                        <span id="result-harga-sekarang">Rp 14.500</span>
                        <span class="text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark">/kg</span>
                    </h3>
                    <div class="pt-4 border-t border-border-light dark:border-border-dark">
                        <p class="text-[10px] font-medium text-text-secondary-light dark:text-text-secondary-dark mb-1">
                            Total Pengeluaran Sekarang
                        </p>
                        <p class="text-lg font-bold text-primary">
                            <span id="result-total-sekarang">Rp 29.000</span>
                            <span class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark">/bulan</span>
                        </p>
                    </div>
                </div>

                {{-- Prediksi Bulan Depan --}}
                <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-6 rounded-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-primary text-white text-[9px] font-black px-3 py-1 rounded-bl-lg tracking-widest uppercase">
                        AI Prediction
                    </div>
                    <p class="text-[10px] font-bold text-primary uppercase tracking-wider mb-2">
                        Prediksi Harga Bulan Depan
                    </p>
                    <h3 class="text-2xl font-black text-text-primary-light dark:text-text-primary-dark mb-4">
                        <span id="result-harga-prediksi">Rp 14.862</span>
                        <span class="text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark">/kg</span>
                    </h3>
                    <div class="pt-4 border-t border-border-light dark:border-border-dark">
                        <p class="text-[10px] font-medium text-text-secondary-light dark:text-text-secondary-dark mb-1">
                            Estimasi Pengeluaran Bulan Depan
                        </p>
                        <p class="text-lg font-bold text-primary">
                            <span id="result-total-prediksi">Rp 29.724</span>
                            <span class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark">/bulan</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Anggaran --}}
            <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Ringkasan Anggaran</h3>
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark" id="ringkasan-subtitle">
                            Berdasarkan konsumsi 0.5 kg per minggu
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                </div>
                <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex-1 border-r-0 md:border-r border-border-light dark:border-border-dark pr-6">
                        <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest mb-2">
                            Selisih Pengeluaran
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-500 font-bold">trending_up</span>
                            <span class="text-3xl font-black text-red-500" id="result-selisih">+ Rp 724</span>
                        </div>
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark mt-1">
                            Peningkatan biaya estimasi sekitar
                            <span class="font-bold text-red-500" id="result-persen-selisih">2.5%</span>
                        </p>
                    </div>
                    <div class="flex-1 md:pl-6">
                        <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest mb-3">
                            Rekomendasi Tindakan:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="showToast('Segera beli dan simpan stok sebelum harga naik!', 'info')"
                                    class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-primary text-xs font-bold rounded-lg hover:bg-blue-100 transition-colors">
                                Stok Lebih Awal
                            </button>
                            <button onclick="showToast('Cari promo di marketplace terdekat!', 'info')"
                                    class="px-4 py-2 bg-background-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark text-xs font-bold rounded-lg hover:bg-slate-100 transition-colors">
                                Cari Promo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafik Bar --}}
            <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-6">
                <div class="flex flex-col items-center">
                    <div class="w-full flex items-end justify-between h-48 px-10 gap-4 mb-4" id="bar-chart">
                        @php
                            $bars = [
                                ['label' => 'MAR', 'height' => '60%',  'color' => 'bg-slate-200 dark:bg-slate-700', 'active' => false],
                                ['label' => 'APR', 'height' => '75%',  'color' => 'bg-slate-200 dark:bg-slate-700', 'active' => false],
                                ['label' => 'MEI', 'height' => '85%',  'color' => 'bg-blue-200 dark:bg-blue-900/50','active' => false],
                                ['label' => 'JUN*','height' => '100%', 'color' => 'bg-primary',                      'active' => true],
                            ];
                        @endphp
                        @foreach($bars as $bar)
                        <div class="flex flex-col items-center w-full gap-3 relative">
                            @if($bar['active'])
                                <div class="absolute -top-6 text-[9px] font-black text-primary">{{ $bar['label'] }}</div>
                            @endif
                            <div class="w-full {{ $bar['color'] }} rounded-t-lg" style="height: {{ $bar['height'] }}"></div>
                            <span class="text-[10px] font-bold {{ $bar['active'] ? 'text-primary' : 'text-text-secondary-light dark:text-text-secondary-dark' }}">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest text-center mt-2 border-t border-border-light dark:border-border-dark pt-4 w-full">
                        GRAFIK TREN HARGA 4 BULAN TERAKHIR & PREDIKSI
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data harga per komoditas (simulasi statis)
const dataKomoditas = {
    '14500': { nama: 'Beras Premium',  harga: 14500, kenaikan: 2.5,  wawasan: 'AI kami memprediksi kenaikan harga beras sekitar 2.5% bulan depan dikarenakan faktor musim panen yang bergeser.' },
    '65000': { nama: 'Cabai Rawit',    harga: 65000, kenaikan: 5.2,  wawasan: 'Harga cabai rawit diprediksi naik 5.2% karena cuaca ekstrem yang mengganggu panen di sentra produksi.' },
    '32000': { nama: 'Bawang Merah',   harga: 32000, kenaikan: 1.8,  wawasan: 'Bawang merah relatif stabil dengan potensi kenaikan 1.8% akibat peningkatan permintaan akhir tahun.' },
    '27500': { nama: 'Telur Ayam',     harga: 27500, kenaikan: 3.1,  wawasan: 'Harga telur ayam diperkirakan naik 3.1% bulan depan seiring kenaikan harga pakan ternak.' },
};

function hitungEstimasi() {
    const komoditasVal = document.getElementById('sim-komoditas').value;
    const konsumsi     = parseFloat(document.getElementById('sim-konsumsi').value) || 0.5;
    const data         = dataKomoditas[komoditasVal];

    if (!data) return;

    // Loading
    const btn = document.getElementById('btn-hitung');
    document.getElementById('btn-hitung-icon').textContent = 'hourglass_top';
    document.getElementById('btn-hitung-text').textContent = 'Menghitung...';
    btn.disabled = true;

    setTimeout(() => {
        // Hitung
        const konsumsiPerBulan  = konsumsi * 4; // 4 minggu
        const totalSekarang     = data.harga * konsumsiPerBulan;
        const hargaPrediksi     = data.harga * (1 + data.kenaikan / 100);
        const totalPrediksi     = hargaPrediksi * konsumsiPerBulan;
        const selisih           = totalPrediksi - totalSekarang;

        // Format angka
        const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

        // Update UI
        document.getElementById('result-harga-sekarang').textContent  = fmt(data.harga);
        document.getElementById('result-total-sekarang').textContent  = fmt(totalSekarang);
        document.getElementById('result-harga-prediksi').textContent  = fmt(hargaPrediksi);
        document.getElementById('result-total-prediksi').textContent  = fmt(totalPrediksi);
        document.getElementById('result-selisih').textContent         = '+ ' + fmt(selisih);
        document.getElementById('result-persen-selisih').textContent  = data.kenaikan + '%';
        document.getElementById('ringkasan-subtitle').textContent     = `Berdasarkan konsumsi ${konsumsi} kg per minggu`;
        document.getElementById('wawasan-text').textContent           = data.wawasan;

        // Reset button
        document.getElementById('btn-hitung-icon').textContent = 'check_circle';
        document.getElementById('btn-hitung-text').textContent = 'Estimasi Diperbarui!';
        btn.disabled = false;

        showToast('Estimasi berhasil dihitung!', 'success');

        setTimeout(() => {
            document.getElementById('btn-hitung-icon').textContent = 'calculate';
            document.getElementById('btn-hitung-text').textContent = 'Hitung Estimasi';
        }, 2500);

    }, 800);
}

// Hitung otomatis saat halaman load
document.addEventListener('DOMContentLoaded', hitungEstimasi);
</script>
@endpush