<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
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
        $user = Auth::user();

        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $updateData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
