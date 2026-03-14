<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if ($user->role !== 'admin') return redirect('/dashboard');
        return $user;
    }

    /** GET /admin/komoditas */
    public function index()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $komoditas = Commodity::with('category')->orderBy('name')->paginate(15);

        return view('admin.komoditas', compact('user'));
    }

    /** GET /admin/komoditas/create */
    public function create()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $categories = Category::all();

        return view('admin.komoditas-create', compact('user'));
    }

    /** POST /admin/komoditas */
    public function store(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
            'unit'        => 'required|string|max:50',
            'stok_unit'   => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Commodity::create($request->all());

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil ditambahkan.');
    }

    /** GET /admin/komoditas/{id}/edit */
    public function edit(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $komoditas  = Commodity::findOrFail($id);
        // $categories = Category::all();

        return view('admin.komoditas-edit', compact('user'));
    }

    /** PUT /admin/komoditas/{id} */
    public function update(Request $request, string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
            'unit'        => 'required|string|max:50',
            'stok_unit'   => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Commodity::findOrFail($id)->update($request->all());

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil diperbarui.');
    }

    /** DELETE /admin/komoditas/{id} */
    public function destroy(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // Commodity::findOrFail($id)->delete();

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil dihapus.');
    }
}