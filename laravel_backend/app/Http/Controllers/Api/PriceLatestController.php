<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use Illuminate\Http\Request;

class PriceLatestController extends Controller
{
    /**
     * GET /api/prices/latest
     *
     * Ambil harga terbaru per komoditas langsung dari price_histories.
     * Pendekatan sama seperti HargaController web yang sudah berhasil.
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search   = $request->get('search');

        // Ambil harga terbaru per commodity_name menggunakan aggregation
        $latest = PriceHistory::raw(function ($collection) use ($category, $search) {
            $match = [];

            if ($category && $category !== 'Semua') {
                $match['category'] = $category;
            }

            if ($search) {
                $match['commodity_name'] = ['$regex' => $search, '$options' => 'i'];
            }

            $pipeline = [];

            if (!empty($match)) {
                $pipeline[] = ['$match' => $match];
            }

            $pipeline = array_merge($pipeline, [
                ['$sort' => ['date' => -1]],
                [
                    '$group' => [
                        '_id'            => '$commodity_name',
                        'commodity_id'   => ['$first' => '$commodity_id'],
                        'commodity_name' => ['$first' => '$commodity_name'],
                        'category'       => ['$first' => '$category'],
                        'satuan'         => ['$first' => '$satuan'],
                        'harga_sekarang' => ['$first' => '$harga_sekarang'],
                        'harga_lama'     => ['$first' => '$harga_lama'],
                        'selisih'        => ['$first' => '$selisih'],
                        'persen'         => ['$first' => '$persen'],
                        'date'           => ['$first' => '$date'],
                    ],
                ],
                ['$sort' => ['commodity_name' => 1]],
            ]);

            return $collection->aggregate($pipeline);
        });

        $result = collect($latest)->map(fn($item) => [
            'commodity_id'   => (string) ($item['commodity_id'] ?? ''),
            'commodity_name' => $item['commodity_name'] ?? '',
            'category'       => $item['category'] ?? '',
            'satuan'         => $item['satuan'] ?? '',
            'unit'           => $item['satuan'] ?? 'kg',
            'date'           => isset($item['date'])
                ? \Carbon\Carbon::parse($item['date'])->toDateString()
                : null,
            'harga_sekarang' => (float) ($item['harga_sekarang'] ?? 0),
            'harga_lama'     => (float) ($item['harga_lama'] ?? 0),
            'selisih'        => (float) ($item['selisih'] ?? 0),
            'persen'         => (float) ($item['persen'] ?? 0),
        ])->values();

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/prices/categories
     *
     * Daftar kategori unik — langsung dari price_histories, sama seperti HargaController.
     */
    public function categories()
    {
        $categories = collect(
            PriceHistory::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->values();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    /**
     * GET /api/prices/top?limit=3
     *
     * Top N komoditas harga tertinggi — langsung dari price_histories.
     */
    public function top(Request $request)
    {
        $limit = (int) $request->get('limit', 3);

        $top = PriceHistory::raw(function ($collection) use ($limit) {
            return $collection->aggregate([
                ['$sort' => ['date' => -1]],
                [
                    '$group' => [
                        '_id'            => '$commodity_name',
                        'commodity_id'   => ['$first' => '$commodity_id'],
                        'commodity_name' => ['$first' => '$commodity_name'],
                        'category'       => ['$first' => '$category'],
                        'satuan'         => ['$first' => '$satuan'],
                        'harga_sekarang' => ['$first' => '$harga_sekarang'],
                        'harga_lama'     => ['$first' => '$harga_lama'],
                        'selisih'        => ['$first' => '$selisih'],
                        'persen'         => ['$first' => '$persen'],
                    ],
                ],
                ['$sort' => ['harga_sekarang' => -1]],
                ['$limit' => $limit],
            ]);
        });

        $result = collect($top)->map(fn($item) => [
            'commodity_id'   => (string) ($item['commodity_id'] ?? ''),
            'commodity_name' => $item['commodity_name'] ?? '',
            'category'       => $item['category'] ?? '',
            'satuan'         => $item['satuan'] ?? '',
            'unit'           => $item['satuan'] ?? 'kg',
            'harga_sekarang' => (float) ($item['harga_sekarang'] ?? 0),
            'harga_lama'     => (float) ($item['harga_lama'] ?? 0),
            'selisih'        => (float) ($item['selisih'] ?? 0),
            'persen'         => (float) ($item['persen'] ?? 0),
        ])->values();

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
