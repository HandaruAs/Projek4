<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if (($user['role'] ?? null) !== 'admin') return redirect('/dashboard');
        return $user;
    }

    /** GET /admin/dashboard */
    public function dashboard()
    {
        $totalKomoditas = Commodity::count();
        $hargaTertinggi = PriceHistory::orderBy('harga_sekarang', 'desc')->first();
        $hargaTerendah  = PriceHistory::orderBy('harga_sekarang', 'asc')->first();
        $recentPrices   = PriceHistory::orderBy('date', 'desc')->limit(7)->get();

        return view('admin.dashboard', compact(
            'totalKomoditas',
            'hargaTertinggi',
            'hargaTerendah',
            'recentPrices'
        ));
    }


    public function profile()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        return view('admin.profile', compact('user'));
    }

    
    public function updateProfile(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        $userData = User::find($user['id']);

        $updateData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($userData->avatar) {
                Storage::disk('public')->delete($userData->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        $userData->update($updateData);
        $fresh = $userData->fresh();

        // Perbarui session dengan format array yang konsisten
        session([
            'user' => [
                'id'      => (string) $fresh->_id,
                'nama'    => $fresh->name,
                'email'   => $fresh->email,
                'role'    => $fresh->role,
                'phone'   => $fresh->phone   ?? null,
                'address' => $fresh->address ?? null,
                'avatar'  => $fresh->avatar  ?? null,
            ]
        ]);

        return redirect('/admin/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}