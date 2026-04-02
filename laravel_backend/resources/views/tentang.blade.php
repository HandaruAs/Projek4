@extends('layouts.app')

@section('title', 'Tentang')

@section('content')

{{-- HERO --}}
<div class="bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark py-16">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-primary font-bold text-sm tracking-widest uppercase mb-4 block">Mengenal Kami</span>
            <h1 class="text-text-primary-light dark:text-text-primary-dark text-4xl md:text-5xl font-black tracking-tight mb-6">
                Sistem Monitoring Pangan Nasional
            </h1>
            <p class="text-text-secondary-light dark:text-text-secondary-dark text-lg leading-relaxed">
                SIMOPANG adalah platform inovatif yang dirancang untuk memberikan transparansi harga komoditas pangan secara real-time. Kami menggabungkan pengumpulan data otomatis dengan kecerdasan buatan untuk membantu pengambilan keputusan yang lebih baik.
            </p>
        </div>
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-16">

    {{-- VISI MISI --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-24">
        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">visibility</span>
                Visi Kami
            </h2>
            <p class="text-text-secondary-light dark:text-text-secondary-dark leading-relaxed text-lg">
                Menjadi rujukan utama data pangan nasional yang akurat, transparan, dan mampu memprediksi dinamika pasar demi ketahanan pangan nasional.
            </p>
        </div>
        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">rocket_launch</span>
                Misi Kami
            </h2>
            <ul class="space-y-4">
                @foreach([
                    'Menyediakan data harga pangan yang diperbarui secara otomatis dan berkala.',
                    'Mengimplementasikan teknologi Machine Learning untuk prediksi tren harga masa depan.',
                    'Memberikan akses informasi yang merata bagi produsen, konsumen, dan pembuat kebijakan.',
                ] as $i => $misi)
                <li class="flex gap-4">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">
                        {{ $i + 1 }}
                    </div>
                    <p class="text-text-secondary-light dark:text-text-secondary-dark">{{ $misi }}</p>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- TEKNOLOGI --}}
    <div class="mb-24">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark mb-4">Teknologi Kami</h2>
            <p class="text-text-secondary-light dark:text-text-secondary-dark">
                Dibangun dengan arsitektur modern untuk menjamin performa, keamanan, dan akurasi data yang tinggi.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['icon' => 'terminal',   'color' => 'red',   'title' => 'Laravel Ecosystem', 'desc' => 'Menggunakan framework PHP paling populer untuk manajemen backend yang robust, skalabel, dan sistem keamanan tingkat tinggi.'],
                ['icon' => 'database',   'color' => 'green', 'title' => 'MongoDB',            'desc' => 'Penyimpanan data berbasis dokumen NoSQL yang memungkinkan fleksibilitas data tinggi dan pemrosesan big data harga komoditas secara efisien.'],
                ['icon' => 'psychology', 'color' => 'blue',  'title' => 'Prophet AI',         'desc' => 'Algoritma forecasting modern untuk menganalisis data time-series, mampu menangani seasonal effect untuk prediksi harga yang akurat.'],
            ] as $tech)
            <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-{{ $tech['color'] }}-50 dark:bg-{{ $tech['color'] }}-900/20 rounded-xl flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-{{ $tech['color'] }}-600 text-3xl">{{ $tech['icon'] }}</span>
                </div>
                <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-3">{{ $tech['title'] }}</h3>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm leading-relaxed">{{ $tech['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- KONTAK --}}
    <div class="bg-primary rounded-3xl p-8 md:p-12 overflow-hidden relative">
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Info Kontak --}}
            <div>
                <h2 class="text-3xl font-bold text-white mb-6">Informasi Kontak</h2>
                <p class="text-blue-100 mb-8 text-lg">
                    Punya pertanyaan atau butuh akses API khusus? Tim kami siap membantu Anda mendapatkan data yang diperlukan.
                </p>
                <div class="space-y-4">
                    @foreach([
                        ['icon' => 'mail',        'text' => 'kontak@simopang.go.id'],
                        ['icon' => 'call',        'text' => '+62 21 1234 5678'],
                        ['icon' => 'location_on', 'text' => 'Jakarta, Indonesia'],
                    ] as $kontak)
                    <div class="flex items-center gap-4 text-white">
                        <span class="material-symbols-outlined p-2 bg-white/10 rounded-lg">{{ $kontak['icon'] }}</span>
                        <span class="font-medium">{{ $kontak['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Form Kontak --}}
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20">
                <form id="form-kontak" onsubmit="kirimPesan(event)" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-white text-sm font-medium mb-1.5">Nama Lengkap</label>
                        <input id="input-nama" type="text" placeholder="Masukkan nama Anda"
                               class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/50 focus:ring-2 focus:ring-white/30 focus:border-transparent outline-none transition-all"/>
                    </div>
                    <div>
                        <label class="block text-white text-sm font-medium mb-1.5">Email</label>
                        <input id="input-email" type="email" placeholder="email@contoh.com"
                               class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/50 focus:ring-2 focus:ring-white/30 focus:border-transparent outline-none transition-all"/>
                    </div>
                    <div>
                        <label class="block text-white text-sm font-medium mb-1.5">Pesan</label>
                        <textarea id="input-pesan" rows="3" placeholder="Apa yang bisa kami bantu?"
                                  class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/50 focus:ring-2 focus:ring-white/30 focus:border-transparent outline-none transition-all"></textarea>
                    </div>
                    <button type="submit" id="btn-kirim"
                            class="w-full bg-white text-primary font-bold py-3 rounded-lg hover:bg-blue-50 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" id="btn-icon">send</span>
                        <span id="btn-text">Kirim Pesan</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Decorative blobs --}}
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-black/10 rounded-full blur-3xl"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
async function kirimPesan(e) {
    e.preventDefault();

    const nama  = document.getElementById('input-nama').value.trim();
    const email = document.getElementById('input-email').value.trim();
    const pesan = document.getElementById('input-pesan').value.trim();

    if (!nama || !email || !pesan) {
        showToast('Semua field harus diisi!', 'warning');
        return;
    }

    // Loading state
    document.getElementById('btn-kirim').disabled  = true;
    document.getElementById('btn-icon').textContent = 'hourglass_top';
    document.getElementById('btn-text').textContent = 'Mengirim...';

    // Simulasi kirim (ganti dengan fetch ke route API kalau sudah ada)
    await new Promise(r => setTimeout(r, 1500));

    // Reset form
    document.getElementById('form-kontak').reset();
    document.getElementById('btn-kirim').disabled  = false;
    document.getElementById('btn-icon').textContent = 'check_circle';
    document.getElementById('btn-text').textContent = 'Pesan Terkirim!';

    showToast('Pesan berhasil dikirim! Kami akan segera menghubungi Anda.', 'success');

    setTimeout(() => {
        document.getElementById('btn-icon').textContent = 'send';
        document.getElementById('btn-text').textContent = 'Kirim Pesan';
    }, 3000);
}
</script>
@endpush