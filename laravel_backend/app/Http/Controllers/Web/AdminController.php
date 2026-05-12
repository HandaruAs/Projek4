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

        // ✅ Ambil semua prediksi terbaru per komoditas
        $allPredictions = \App\Models\Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->map(function ($pred) {
                $forecast      = $pred->payload['forecast']     ?? [];
                $tanggal       = $pred->payload['tanggal_pred'] ?? [];
                $hargaTerakhir = (float) ($pred->payload['harga_terakhir'] ?? 0);

                $maxHarga   = !empty($forecast) ? max($forecast) : 0;
                $maxIndex   = !empty($forecast) ? array_search($maxHarga, $forecast) : 0;
                $maxTanggal = $tanggal[$maxIndex] ?? null;

                $selisih = $maxHarga - $hargaTerakhir;
                $persen  = $hargaTerakhir > 0
                    ? round(($selisih / $hargaTerakhir) * 100, 2)
                    : 0;

                return (object) [
                    'commodity_name' => $pred->commodity_name,
                    'category'       => $pred->payload['kategori']  ?? '-',
                    'harga_sekarang' => $maxHarga,
                    'date'           => $maxTanggal,
                    'satuan'         => $pred->payload['satuan']    ?? '-',
                    'selisih'        => $selisih,
                    'persen'         => $persen,
                    'harga_terakhir' => $hargaTerakhir,
                ];
            });

        // ✅ Stat cards dari predictions
        $hargaTertinggi = $allPredictions->sortByDesc('harga_sekarang')->first();
        $hargaTerendah  = $allPredictions->sortBy('harga_sekarang')->first();

        // ✅ Tabel: top 7 harga forecast tertinggi
        $recentPrices = $allPredictions
            ->sortByDesc('harga_sekarang')
            ->take(7)
            ->values();

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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // email dihapus dari validasi karena tidak boleh diubah
        ]);

        $updateData = [
            'name'    => $request->name,
            // email tidak diubah - tetap menggunakan email lama
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
