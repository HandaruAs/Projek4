<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HargaController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if ($user->role !== 'admin') return redirect('/dashboard');
        return $user;
    }

    /** GET /admin/harga */
    public function index()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $dataHarga = PriceHistory::with(['commodity','market'])
        //                ->orderBy('date','desc')->paginate(15);

        return view('admin.harga', compact('user'));
    }

    /** GET /admin/harga/create */
    public function create()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $komoditas = Commodity::orderBy('name')->get();
        // $markets   = Market::orderBy('name')->get();

        return view('admin.harga-create', compact('user'));
    }

    /** POST /admin/harga */
    public function store(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'commodity_id' => 'required',
            'market_id'    => 'required',
            'price'        => 'required|numeric|min:0',
            'stok'         => 'nullable|numeric|min:0',
            'date'         => 'required|date',
        ]);

        // PriceHistory::create($request->all());

        return redirect('/admin/harga')->with('success', 'Data harga berhasil ditambahkan.');
    }

    /** GET /admin/harga/{id}/edit */
    public function edit(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $harga     = PriceHistory::findOrFail($id);
        // $komoditas = Commodity::orderBy('name')->get();
        // $markets   = Market::orderBy('name')->get();

        return view('admin.harga-edit', compact('user'));
    }

    /** PUT /admin/harga/{id} */
    public function update(Request $request, string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'commodity_id' => 'required',
            'market_id'    => 'required',
            'price'        => 'required|numeric|min:0',
            'stok'         => 'nullable|numeric|min:0',
            'date'         => 'required|date',
        ]);

        // PriceHistory::findOrFail($id)->update($request->all());

        return redirect('/admin/harga')->with('success', 'Data harga berhasil diperbarui.');
    }

    /** DELETE /admin/harga/{id} */
    public function destroy(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // PriceHistory::findOrFail($id)->delete();

        return redirect('/admin/harga')->with('success', 'Data harga berhasil dihapus.');
    }
}