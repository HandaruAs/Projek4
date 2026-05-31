<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Prediction;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // HELPER: Ambil harga untuk tanggal tertentu dari forecast
    // ─────────────────────────────────────────────────────────────
    private function getHargaForDate(array $forecast, array $tanggalPred, float $hargaTerakhir, Carbon $targetDate): float
    {
        if (empty($forecast) || empty($tanggalPred)) {
            return $hargaTerakhir;
        }

        $targetStr = $targetDate->toDateString();

        // Cari exact match dulu
        $index = array_search($targetStr, $tanggalPred);
        if ($index !== false && isset($forecast[$index])) {
            return (float) $forecast[$index];
        }

        // Kalau tanggal sebelum range prediksi → pakai harga_terakhir
        if ($targetStr < $tanggalPred[0]) {
            return $hargaTerakhir;
        }

        // Kalau tanggal setelah range prediksi → pakai forecast terakhir
        $lastTanggal = end((array) $tanggalPred);
        if ($targetStr > $lastTanggal) {
            $lastForecast = end((array) $forecast);
            return (float) $lastForecast;
        }

        // Fallback: cari tanggal terdekat
        foreach ($tanggalPred as $i => $tgl) {
            if ($tgl >= $targetStr) {
                return (float) ($forecast[$i] ?? $hargaTerakhir);
            }
        }

        return $hargaTerakhir;
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: Build item prediksi dengan harga dinamis
    // ─────────────────────────────────────────────────────────────
    private function buildPrediksiItem($pred, Carbon $today): object
    {
        $payload         = $pred->payload ?? [];
        $forecast        = $payload['forecast']          ?? [];
        $tanggalPred     = $payload['tanggal_pred']      ?? [];
        $hargaTerakhir   = (float) ($payload['harga_terakhir']  ?? 0);
        $tanggalTerakhir = $payload['tanggal_terakhir']  ?? null;
        $kategori        = $payload['kategori']          ?? ($pred->kategori ?? '');

        // ── Range prediksi ───────────────────────────────────────
        $tanggalPredArr = (array) $tanggalPred;
        $tanggalMulai   = !empty($tanggalPredArr) ? Carbon::parse($tanggalPredArr[0]) : null;
        $lastTgl        = end($tanggalPredArr);
        $tanggalAkhir   = !empty($tanggalPredArr) ? Carbon::parse($lastTgl) : null;

        $sudahMulai      = $tanggalMulai  && $today->gte($tanggalMulai);
        $belumSelesai    = $tanggalAkhir  && $today->lte($tanggalAkhir);
        $dalamRange      = $sudahMulai    && $belumSelesai;
        $sudahKadaluarsa = $tanggalAkhir  && $today->gt($tanggalAkhir);
        $belumMulai      = $tanggalMulai  && $today->lt($tanggalMulai);

        // ── Harga & tanggal harga berdasarkan kondisi ────────────
        if ($dalamRange) {
            // Hari ini dalam range → ambil harga forecast hari ini
            $hargaHariIni = $this->getHargaForDate($forecast, $tanggalPredArr, $hargaTerakhir, $today);
            $tanggalHarga = $today->toDateString();

        } elseif ($sudahKadaluarsa) {
            // Prediksi sudah habis → pakai forecast terakhir
            $lastForecast = end((array) $forecast);
            $hargaHariIni = (float) ($lastForecast ?: $hargaTerakhir);
            $tanggalHarga = $tanggalAkhir->toDateString();

        } else {
            // Belum mulai → pakai harga aktual terakhir
            $hargaHariIni = $hargaTerakhir;
            $tanggalHarga = $tanggalTerakhir; // tanggal data aktual terakhir dari Flask
        }

        // Selisih vs harga aktual terakhir
        $selisih = $hargaHariIni - $hargaTerakhir;
        $persen  = $hargaTerakhir > 0 ? ($selisih / $hargaTerakhir) * 100 : 0;

        $item = new \stdClass();
        $item->commodity_name    = $pred->commodity_name;
        $item->kategori          = $kategori;
        $item->harga_sekarang    = $hargaHariIni;
        $item->harga_terakhir    = $hargaTerakhir;
        $item->selisih           = $selisih;
        $item->persen            = $persen;
        $item->tanggal_mulai     = $tanggalMulai;
        $item->tanggal_akhir     = $tanggalAkhir;
        $item->dalam_range       = $dalamRange;
        $item->sudah_kadaluarsa  = $sudahKadaluarsa;
        $item->belum_mulai       = $belumMulai;
        $item->tanggal_harga     = $tanggalHarga;
        $item->current_price     = $hargaTerakhir;
        $item->date              = $pred->created_at;

        return $item;
    }

    // ─────────────────────────────────────────────────────────────
    // GET /home
    // ─────────────────────────────────────────────────────────────
    public function home(Request $request)
    {
        $user     = Auth::user();
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');

        $today = $date ? Carbon::parse($date) : Carbon::today();

        // ── Ambil semua prediksi terbaru per komoditas ──────────
        $allPredictions = Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestPerCommodity = $allPredictions
            ->groupBy('commodity_name')
            ->map(fn($group) => $group->first())
            ->values();

        // ── Filter ───────────────────────────────────────────────
        if ($search) {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => str_contains(strtolower($p->commodity_name), strtolower($search))
            )->values();
        }

        if ($category && $category !== 'Semua') {
            $latestPerCommodity = $latestPerCommodity->filter(function ($p) use ($category) {
                $kat = $p->payload['kategori'] ?? ($p->kategori ?? '');
                return strtolower($kat) === strtolower($category);
            })->values();
        }

        // ── Build items ──────────────────────────────────────────
        $items = $latestPerCommodity->map(
            fn($pred) => $this->buildPrediksiItem($pred, $today)
        );

        $items = $items->sortByDesc(fn($p) => $p->harga_sekarang)->values();

        // ── Pagination ───────────────────────────────────────────
        $perPage     = 10;
        $currentPage = (int) $request->get('page', 1);
        $total       = $items->count();
        $pageItems   = $items->forPage($currentPage, $perPage);

        $recentPrices = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ── Stat cards ───────────────────────────────────────────
        $rataRataHarga          = (int) round($items->avg(fn($p) => $p->harga_sekarang) ?? 0);
        $komoditasTertinggi     = $items->first();
        $hargaTertinggi         = $komoditasTertinggi?->harga_sekarang ?? 0;
        $namaKomoditasTertinggi = $komoditasTertinggi?->commodity_name ?? '-';
        $totalKomoditas         = Commodity::count();

        // ── Banner range prediksi global ─────────────────────────
        $allTanggalMulai    = $items->filter(fn($p) => $p->tanggal_mulai)->map(fn($p) => $p->tanggal_mulai);
        $allTanggalAkhir    = $items->filter(fn($p) => $p->tanggal_akhir)->map(fn($p) => $p->tanggal_akhir);
        $globalTanggalMulai = $allTanggalMulai->min();
        $globalTanggalAkhir = $allTanggalAkhir->max();

        // ── Category list ────────────────────────────────────────
        $categoryList = $allPredictions
            ->pluck('payload')
            ->map(fn($p) => $p['kategori'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn($name) => (object) ['_id' => $name, 'name' => $name]);

        // ── Pie Chart ────────────────────────────────────────────
        try {
            $grouped = $items
                ->filter(fn($p) => !empty($p->kategori) && $p->harga_sekarang > 0)
                ->groupBy('kategori')
                ->map(fn($grp) => (int) round($grp->avg(fn($p) => $p->harga_sekarang)))
                ->filter(fn($val) => $val > 0)
                ->sortDesc();

            $chartLabels = array_values($grouped->keys()->toArray());
            $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
        } catch (\Exception $e) {
            Log::error('Pie Chart Error: ' . $e->getMessage());
            $chartLabels = [];
            $chartValues = [];
        }

        // ── Volatilitas ──────────────────────────────────────────
        $last7 = [];
        if ($komoditasTertinggi) {
            $pred7 = $allPredictions->firstWhere('commodity_name', $komoditasTertinggi->commodity_name);
            if ($pred7) {
                $last7 = array_slice($pred7->payload['forecast'] ?? [], 0, 7);
            }
        }

        if (count($last7) > 1) {
            $mean              = array_sum($last7) / count($last7);
            $variance          = array_sum(array_map(fn($x) => pow($x - $mean, 2), $last7)) / count($last7);
            $indexVolatilitas  = round(sqrt($variance) / $mean, 2);
            $statusVolatilitas = match(true) {
                $indexVolatilitas < 0.1 => 'Rendah',
                $indexVolatilitas < 0.3 => 'Sedang',
                default                 => 'Tinggi',
            };
        } else {
            $statusVolatilitas = 'Rendah';
            $indexVolatilitas  = 0;
        }

        return view('user.home', compact(
            'user', 'recentPrices',
            'rataRataHarga', 'hargaTertinggi', 'namaKomoditasTertinggi', 'totalKomoditas',
            'statusVolatilitas', 'indexVolatilitas',
            'categoryList', 'chartLabels', 'chartValues',
            'globalTanggalMulai', 'globalTanggalAkhir',
            'today'
        ));
    }

    public function downloadPdf(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');
        $today    = $date ? Carbon::parse($date) : Carbon::today();

        $allPredictions = Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestPerCommodity = $allPredictions
            ->groupBy('commodity_name')
            ->map(fn($group) => $group->first())
            ->values();

        if ($search) {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => str_contains(strtolower($p->commodity_name), strtolower($search))
            )->values();
        }

        if ($category && $category !== 'Semua') {
            $latestPerCommodity = $latestPerCommodity->filter(function ($p) use ($category) {
                $kat = $p->payload['kategori'] ?? ($p->kategori ?? '');
                return strtolower($kat) === strtolower($category);
            })->values();
        }

        $recentPrices = $latestPerCommodity
            ->map(fn($pred) => $this->buildPrediksiItem($pred, $today))
            ->sortByDesc(fn($p) => $p->harga_sekarang)
            ->take(500);

        $adaFilter = $search || ($category && $category !== 'Semua') || $date;

        if ($adaFilter && $recentPrices->isNotEmpty()) {
            $hargaTerbaruData = $recentPrices->first();
            $hargaTerbaru     = $hargaTerbaruData->harga_sekarang;
            $namaKomoditas    = $hargaTerbaruData->commodity_name;
        } else {
            $hargaTerbaru     = (int) round($recentPrices->avg(fn($p) => $p->harga_sekarang) ?? 0);
            $namaKomoditas    = 'Semua Komoditas';
            $hargaTerbaruData = $recentPrices->first();
        }

        $hargaBulanLalu = $hargaTerbaruData?->harga_terakhir ?? $hargaTerbaru;
        $hargaKemarin   = $hargaBulanLalu;
        $hargaChange    = $hargaTerbaru - $hargaBulanLalu;
        $hargaPercent   = $hargaBulanLalu > 0 ? ($hargaChange / $hargaBulanLalu) * 100 : 0;

        $last7 = [];
        if ($hargaTerbaruData) {
            $pred7 = Prediction::where('status', 'completed')
                ->where('commodity_name', $hargaTerbaruData->commodity_name)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($pred7) {
                $last7 = array_slice($pred7->payload['forecast'] ?? [], 0, 7);
            }
        }

        if (count($last7) > 1) {
            $mean              = array_sum($last7) / count($last7);
            $variance          = array_sum(array_map(fn($x) => pow($x - $mean, 2), $last7)) / count($last7);
            $indexVolatilitas  = round(sqrt($variance) / $mean, 2);
            $statusVolatilitas = match(true) {
                $indexVolatilitas < 0.1 => 'Rendah',
                $indexVolatilitas < 0.3 => 'Sedang',
                default                 => 'Tinggi',
            };
        } else {
            $statusVolatilitas = 'Rendah';
            $indexVolatilitas  = 0;
        }

        try {
            $grouped = $recentPrices
                ->filter(fn($p) => !empty($p->kategori) && $p->harga_sekarang > 0)
                ->groupBy('kategori')
                ->map(fn($grp) => (int) round($grp->avg(fn($p) => $p->harga_sekarang)))
                ->filter(fn($val) => $val > 0)
                ->sortDesc();

            $chartLabels = array_values($grouped->keys()->toArray());
            $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
        } catch (\Exception $e) {
            $chartLabels = [];
            $chartValues = [];
        }

        $periodeLabel = $date
            ? Carbon::parse($date)->locale('id')->isoFormat('DD MMMM YYYY')
            : 'Semua Periode';

        $pdf = Pdf::loadView('user.pdf.laporan', compact(
            'recentPrices', 'namaKomoditas', 'hargaTerbaru',
            'hargaChange', 'hargaPercent', 'hargaKemarin',
            'statusVolatilitas', 'indexVolatilitas',
            'chartLabels', 'chartValues', 'periodeLabel'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'     => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isPhpEnabled'    => true,
                'dpi'             => 150,
            ]);

        $filename = 'laporan-prediksi-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}
