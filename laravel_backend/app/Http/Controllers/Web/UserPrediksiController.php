<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\PrediksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserPrediksiController extends Controller
{
    // GET /prediksi
    // User hanya MELIHAT hasil prediksi yang sudah di-generate oleh admin.
    // Tidak ada generate dari sisi user.
    public function prediksi(Request $request)
    {
        // 1. Ambil parameter dari search engine baru
        $searchKomoditas = $request->get('search_komoditas');
        $searchKategori = $request->get('search_kategori');

        // 2. Ambil parameter dari dropdown filter (existing)
        $selectedNama = $request->get('komoditas');

        // 3. Ambil daftar komoditas yang sudah pernah di-generate admin
        $prediksiList = Prediction::orderBy('created_at', 'desc')
            ->get(['commodity_name', 'steps', 'created_at', 'accuracy_mape',
                   'accuracy_mae', 'accuracy_rmse', 'status', '_id', 'kategori']);

        // Daftar unik komoditas untuk dropdown
        $komoditasList = $prediksiList->pluck('commodity_name')->unique()->values()->toArray();

        // 4. Ambil daftar unik kategori untuk filter search engine
        $kategoriList = $prediksiList->pluck('kategori')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // 5. Query untuk hasil pencarian (search engine)
        $searchResults = collect();

        if ($searchKomoditas || $searchKategori) {
            $query = Prediction::select('commodity_name as name', 'kategori')
                ->distinct('commodity_name');

            if ($searchKomoditas && trim($searchKomoditas) !== '') {
                $query->where('commodity_name', 'like', '%' . $searchKomoditas . '%');
            }

            if ($searchKategori && $searchKategori !== '') {
                $query->where('kategori', $searchKategori);
            }

            $searchResults = $query->get()
                ->groupBy('name')
                ->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'name' => $first->name,
                        'kategori' => $first->kategori ?? 'Tidak ada kategori'
                    ];
                })
                ->values();
        }

        // 6. Default komoditas pertama
        if (empty($selectedNama) && empty($searchKomoditas) && empty($searchKategori) && count($komoditasList) > 0) {
            $selectedNama = $komoditasList[0];
        }

        $prediction       = null;
        $chartData        = null;
        $prediksiMingguan = [];
        $estimasiHarga    = null;
        $trenPersen       = null;
        $kepercayaan      = null;

        // 7. Ambil prediksi berdasarkan komoditas yang dipilih
        if ($selectedNama && !empty($selectedNama)) {
            $prediction = Prediction::where('commodity_name', $selectedNama)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($prediction) {
                $payload   = $prediction->payload ?? [];
                $forecast  = $payload['forecast']       ?? [];
                $tanggal   = $payload['tanggal_pred']   ?? [];
                $ciLower   = $payload['ci_lower']       ?? [];
                $ciUpper   = $payload['ci_upper']       ?? [];
                $hargaKini = $payload['harga_terakhir'] ?? 0;
                $accuracy  = $payload['accuracy']['accuracy'] ?? $prediction->accuracy_mape ?? null;

                $estimasiHarga = !empty($forecast) ? (count($forecast) >= 30 ? $forecast[29] : end($forecast)) : null;

                if ($hargaKini > 0 && $estimasiHarga !== null) {
                    $trenPersen = round(($estimasiHarga - $hargaKini) / $hargaKini * 100, 1);
                }

                $kepercayaan = $accuracy !== null ? round(100 - $accuracy, 1) : null;

                $chartData = [
                    'pred_tanggal' => array_slice($tanggal, 0, 14),
                    'pred_harga'   => array_slice($forecast, 0, 14),
                    'ci_lower'     => array_slice(is_array($ciLower) ? $ciLower : [], 0, 14),
                    'ci_upper'     => array_slice(is_array($ciUpper) ? $ciUpper : [], 0, 14),
                    'harga_kini'   => $hargaKini,
                ];

                $prediksiMingguan = $this->buildWeeklyTable($tanggal, $forecast, $hargaKini);
            }
        }

        // 8. Ambil semua riwayat prediksi
        $allPredictions = Prediction::orderBy('created_at', 'desc')->paginate(10);

        return view('user.prediksi', compact(
            'komoditasList',
            'selectedNama',
            'prediction',
            'chartData',
            'prediksiMingguan',
            'estimasiHarga',
            'trenPersen',
            'kepercayaan',
            'searchResults',
            'kategoriList',
            'searchKomoditas',
            'searchKategori',
            'allPredictions'
        ));
    }

    /**
     * Build weekly table from forecast data
     */
    private function buildWeeklyTable(array $tanggal, array $forecast, float $hargaKini): array
    {
        $weeks = [];

        // Pastikan kedua array memiliki panjang yang sama
        $minLength = min(count($tanggal), count($forecast));

        if ($minLength === 0) {
            return $weeks;
        }

        $tanggalSlice = array_slice($tanggal, 0, $minLength);
        $forecastSlice = array_slice($forecast, 0, $minLength);

        // Gabungkan tanggal dan forecast
        $combined = [];
        for ($i = 0; $i < $minLength; $i++) {
            $combined[] = [$tanggalSlice[$i], $forecastSlice[$i]];
        }

        // Bagi per minggu (7 hari)
        $chunks = array_chunk($combined, 7);

        foreach ($chunks as $index => $chunk) {
            $prices = [];
            $dates = [];

            foreach ($chunk as $item) {
                // Perbaikan: tidak menggunakan isset() pada expression
                // Cek apakah item[0] (tanggal) valid
                $isDateValid = false;
                if (is_array($item) && count($item) >= 2) {
                    $dateValue = $item[0];
                    $priceValue = $item[1];

                    // Cek tanggal tidak null dan tidak empty
                    if ($dateValue !== null && $dateValue !== '' && $dateValue !== 'null') {
                        $isDateValid = true;
                    }

                    // Cek price tidak null
                    if ($priceValue !== null && is_numeric($priceValue)) {
                        $prices[] = $priceValue;
                    }

                    if ($isDateValid) {
                        $dates[] = $dateValue;
                    }
                }
            }

            if (empty($prices) || empty($dates)) {
                continue;
            }

            // Hitung rata-rata harga per minggu
            $avgPrice = array_sum($prices) / count($prices);

            // Hitung persentase perubahan
            $deltaPct = 0;
            if ($hargaKini > 0) {
                $deltaPct = round(($avgPrice - $hargaKini) / $hargaKini * 100, 1);
            }

            // Format periode
            $startDate = $this->formatDate($dates[0]);
            $endDate = $this->formatDate(end($dates));
            $periode = $startDate;
            if ($endDate !== $startDate) {
                $periode .= ' – ' . $endDate;
            }

            $weeks[] = [
                'minggu'    => 'Minggu ' . ($index + 1),
                'periode'   => $periode,
                'estimasi'  => (int) round($avgPrice),
                'delta_pct' => $deltaPct,
            ];
        }

        return $weeks;
    }

    /**
     * Format date to Indonesian format
     */
    private function formatDate($date): string
    {
        if ($date === null || $date === '' || $date === 'null') {
            return '';
        }

        try {
            $carbon = Carbon::parse($date);
            return $carbon->locale('id')->isoFormat('DD MMM');
        } catch (\Exception $e) {
            // Fallback: return string as is
            return (string) $date;
        }
    }

    /**
     * Search komoditas API endpoint (AJAX)
     */
    public function searchKomoditas(Request $request)
    {
        $query = $request->get('q', '');
        $kategori = $request->get('kategori', '');

        $results = Prediction::select('commodity_name as name', 'kategori')
            ->distinct('commodity_name');

        if (!empty($query) && trim($query) !== '') {
            $results->where('commodity_name', 'like', '%' . $query . '%');
        }

        if (!empty($kategori) && $kategori !== '') {
            $results->where('kategori', $kategori);
        }

        $collection = $results->get();

        $groupedResults = $collection->groupBy('name')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'name' => $first->name,
                    'kategori' => $first->kategori ?? 'Tidak ada kategori'
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $groupedResults,
            'total' => $groupedResults->count()
        ]);
    }
}
