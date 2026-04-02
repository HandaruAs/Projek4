<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komoditas;
use App\Models\Harga;

class DashboardController extends Controller
{
    
    private function oid($id)
    {
        try {
            return new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            return $id;
        }
    }

    private function getStats($commodityId, $wilayah)
    {
        $hargaTerbaru = Harga::where('commodity_id', $this->oid($commodityId))
            ->where('wilayah', $wilayah)
            ->orderBy('date', 'desc')
            ->first();

        if (!$hargaTerbaru) {
            return [
                'komoditas'                => '-',
                'harga_terbaru'            => 'Rp 0',
                'perubahan_harian'         => '+ Rp 0',
                'persentase'               => '+0%',
                'persentase_harian'        => '+0%',
                'volatilitas'              => 'Rendah',
                'status_volatilitas_label' => 'Stabil',
            ];
        }

        $selisih = $hargaTerbaru->selisih ?? 0;
        $persen  = $hargaTerbaru->persen ?? 0;

        $data30 = Harga::where('commodity_id', $this->oid($commodityId))
            ->where('wilayah', $wilayah)
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $persenValues     = $data30->pluck('persen')->filter()->values();
        $avgPersen        = $persenValues->count() > 0 ? $persenValues->avg() : 0;
        $volatilitas      = abs($avgPersen) < 1 ? 'Rendah' : (abs($avgPersen) < 3 ? 'Sedang' : 'Tinggi');
        $volatilitasLabel = abs($avgPersen) < 1 ? 'Stabil' : (abs($avgPersen) < 3 ? 'Waspada' : 'Tidak Stabil');

        return [
            'komoditas'                => $hargaTerbaru->commodity_name ?? '-',
            'harga_terbaru'            => 'Rp ' . number_format($hargaTerbaru->harga_sekarang ?? 0, 0, ',', '.'),
            'perubahan_harian'         => ($selisih >= 0 ? '+ ' : '- ') . 'Rp ' . number_format(abs($selisih), 0, ',', '.'),
            'persentase'               => ($persen >= 0 ? '+' : '') . $persen . '%',
            'persentase_harian'        => ($persen >= 0 ? '+' : '') . $persen . '%',
            'volatilitas'              => $volatilitas,
            'status_volatilitas_label' => $volatilitasLabel,
        ];
    }

    private function getChartData($commodityId, $wilayah, $limit = 30)
    {
        $data = Harga::where('commodity_id', $this->oid($commodityId))
            ->where('wilayah', $wilayah)
            ->orderBy('date', 'desc')
            ->take($limit)
            ->get();

        if ($data->isEmpty()) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        $data = $data->reverse()->values();

        return [
            'labels' => $data->map(fn($h) =>
                $h->date ? $h->date->format('d M') : '-'
            )->toArray(),

            'values' => $data->pluck('harga_sekarang')
                ->map(fn($v) => (int) $v)
                ->toArray(),
        ];
    }

    private function getPrediksi($commodityId, $wilayah)
    {
        $data7 = Harga::where('commodity_id', $this->oid($commodityId))
            ->where('wilayah', $wilayah)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        if ($data7->count() < 2) {
            return [
                'besok'  => ['harga' => 'N/A', 'persentase' => '0%'],
                '3hari'  => ['harga' => 'N/A', 'persentase' => '0%'],
                '7hari'  => ['harga' => 'N/A', 'persentase' => '0%'],
                'tren'   => 'Data tidak cukup',
            ];
        }

        $hargaAwal    = $data7->first()->harga_sekarang;
        $hargaTerkini = $data7->last()->harga_sekarang;
        $slope        = ($hargaTerkini - $hargaAwal) / max($data7->count() - 1, 1);

        $predBesok = round($hargaTerkini + ($slope * 1));
        $pred3Hari = round($hargaTerkini + ($slope * 3));
        $pred7Hari = round($hargaTerkini + ($slope * 7));

        $pct = fn($pred) => $hargaTerkini > 0
            ? number_format((($pred - $hargaTerkini) / $hargaTerkini) * 100, 1) . '%'
            : '0%';

        $tren = $slope > 0 ? 'Tren Naik Terdeteksi'
              : ($slope < 0 ? 'Tren Turun Terdeteksi' : 'Harga Stabil');

        return [
            'besok'  => ['harga' => 'Rp ' . number_format($predBesok, 0, ',', '.'), 'persentase' => ($slope >= 0 ? '+' : '') . $pct($predBesok)],
            '3hari'  => ['harga' => 'Rp ' . number_format($pred3Hari, 0, ',', '.'), 'persentase' => ($slope >= 0 ? '+' : '') . $pct($pred3Hari)],
            '7hari'  => ['harga' => 'Rp ' . number_format($pred7Hari, 0, ',', '.'), 'persentase' => ($slope >= 0 ? '+' : '') . $pct($pred7Hari)],
            'tren'   => $tren,
        ];
    }

    public function index(Request $request)
    {
        $komoditas = Komoditas::all();
        $daerah    = Harga::raw(fn($c) => $c->distinct('wilayah'));
        $daerah    = array_values(array_filter((array) $daerah));

        $selectedKomoditas = $request->get('komoditas', (string) $komoditas->first()?->_id);
        $selectedDaerah    = $request->get('daerah', $daerah[0] ?? 'Jember');

        $stats       = $this->getStats($selectedKomoditas, $selectedDaerah);
        $chartData30 = $this->getChartData($selectedKomoditas, $selectedDaerah, 30);
        $chartData90 = $this->getChartData($selectedKomoditas, $selectedDaerah, 90);
        $prediksi    = $this->getPrediksi($selectedKomoditas, $selectedDaerah);

        return view('dashboard.index', compact(
            'komoditas', 'daerah',
            'selectedKomoditas', 'selectedDaerah',
            'stats', 'chartData30', 'chartData90', 'prediksi',
        ));
    }

    public function filter(Request $request)
    {
        $komoditas = $request->input('komoditas');
        $daerah    = $request->input('daerah');

        return response()->json([
            'stats'        => $this->getStats($komoditas, $daerah),
            'chartData30'  => $this->getChartData($komoditas, $daerah, 30),
            'chartData90'  => $this->getChartData($komoditas, $daerah, 90),
            'prediksi'     => $this->getPrediksi($komoditas, $daerah),
        ]);
    }

    public function chartData(Request $request)
    {
        return response()->json([
            'chartData' => $this->getChartData(
                $request->komoditas,
                $request->daerah,
                (int) $request->periode
            )
        ]);
    }

    public function exportPdf(Request $request)
    {
        return response()->json(['message' => 'PDF export belum tersedia']);
    }
}