<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    public function index(Request $request)
    {
        $totalKomoditas     = Commodity::count();
        $distinctCategories = Commodity::pluck('category')->filter()->unique();
        $totalCategories    = $distinctCategories->count();
        $activeKomoditas    = Commodity::whereHas('priceHistories')->count();

        $query = Commodity::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $commodities  = $query->paginate(10)->withQueryString();
        $categoryList = Commodity::pluck('category')->filter()->unique()->sort()->values();

        return view('admin.komoditas', compact(
            'commodities', 'totalKomoditas',
            'totalCategories', 'activeKomoditas', 'categoryList'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.komoditas-create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
        ]);

        $category  = Category::findOrFail($request->category_id);
        $commodity = Commodity::create([
            'name'        => $request->name,
            'category'    => (string) $category->name,    // ✅ cast string
            'category_id' => (string) $category->id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success'        => true,
                'commodity_id'   => (string) $commodity->id,
                'commodity_name' => (string) $commodity->name,
                'category'       => (string) $commodity->category,
            ]);
        }

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil ditambahkan.');
    }

    public function storeHarga(Request $request, string $id)
    {
        $request->validate([
            'year'    => 'required|integer|min:2021|max:2025',
            'satuan'  => 'required|string',
            'harga'   => 'required|array|size:12',
            'harga.*' => 'required|numeric|min:0',
        ]);

        $commodity = Commodity::findOrFail($id);
        $year      = $request->year;
        $months    = $request->harga;
        $satuan    = (string) $request->satuan;
        $inserted  = 0;
        $hargaLama = null;

        // ✅ Selalu ambil langsung dari Category model, bypass $commodity->category
        $categoryName = (string) (Category::find($commodity->category_id)?->name ?? '');

        foreach ($months as $monthIndex => $harga) {
            $month = $monthIndex + 1;
            $date  = \Carbon\Carbon::create($year, $month, 1);

            $prevDate   = $date->copy()->subMonth();
            $prevRecord = PriceHistory::where('commodity_id', (string) $commodity->id)
                ->where('date', $prevDate->startOfMonth())
                ->first();

            $hargaLamaVal = $prevRecord
                ? (float) $prevRecord->harga_sekarang
                : (float) ($hargaLama ?? $harga);

            $selisih = (float) $harga - $hargaLamaVal;
            $persen  = $hargaLamaVal > 0
                ? round(($selisih / $hargaLamaVal) * 100, 2)
                : 0;

            PriceHistory::updateOrCreate(
                [
                    'commodity_id' => (string) $commodity->id,
                    'date'         => $date->startOfMonth()->toDateTime(),
                ],
                [
                    'commodity_name' => (string) $commodity->name,
                    'category'       => $categoryName,
                    'satuan'         => $satuan,
                    'harga_lama'     => $hargaLamaVal,
                    'harga_sekarang' => (float) $harga,
                    'selisih'        => $selisih,
                    'persen'         => $persen,
                    'source'         => 'manual',
                ]
            );

            $hargaLama = $harga;
            $inserted++;
        }

        return response()->json([
            'success' => true,
            'message' => "Data harga tahun {$year} berhasil disimpan ({$inserted} bulan).",
        ]);
    }

    public function edit(string $id)
    {
        $commodity  = Commodity::findOrFail($id);
        $categories = Category::orderBy('name')->get();

        $priceHistories = PriceHistory::where('commodity_id', $id)
            ->orderBy('date', 'asc')
            ->get();

        $hargaPerTahun  = [];
        $satuanPerTahun = [];

        foreach ($priceHistories as $ph) {
            $year  = \Carbon\Carbon::parse($ph->date)->year;
            $month = \Carbon\Carbon::parse($ph->date)->month;
            $hargaPerTahun[$year][$month] = $ph->harga_sekarang;
            $satuanPerTahun[$year]        = $ph->satuan;
        }

        return view('admin.komoditas-edit', compact(
            'commodity', 'categories', 'hargaPerTahun', 'satuanPerTahun'
        ));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
        ]);

        $category  = Category::findOrFail($request->category_id);
        $commodity = Commodity::findOrFail($id);

        $commodity->update([
            'name'        => (string) $request->name,
            'category'    => (string) $category->name,   // ✅ cast string
            'category_id' => (string) $category->id,
        ]);

        // ✅ Sync semua PriceHistory milik commodity ini
        PriceHistory::where('commodity_id', $id)->update([
            'commodity_name' => (string) $request->name,
            'category'       => (string) $category->name,
        ]);

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        PriceHistory::where('commodity_id', $id)->delete();
        Commodity::findOrFail($id)->delete();
        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil dihapus.');
    }
}
