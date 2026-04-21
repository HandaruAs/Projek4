<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class UserProfilController extends Controller
{
    /**
     * Tampilkan halaman profil
     */
    public function index()
    {
        $sessionUser = Session::get('user');

        if (!$sessionUser || !isset($sessionUser['_id'])) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data user terbaru dari database
        $user = User::find($sessionUser['_id']);

        if (!$user) {
            Session::forget('user');
            return redirect()->route('login')
                ->with('error', 'Akun tidak ditemukan. Silakan login kembali.');
        }

        // Update session dengan data terbaru
        $this->updateSession($user);

        return view('user.profil', compact('user'));
    }

    /**
     * Update profil user
     */
    public function update(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|max:255',
            'telepon'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:255',
            'avatar_base64' => 'nullable|string',
            'remove_avatar' => 'nullable|in:0,1',
        ]);

        $userId = Session::get('user')['_id'] ?? null;

        if (!$userId) {
            return back()->with('error', 'Sesi tidak valid, silakan login ulang.');
        }

        $user = User::find($userId);

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $updateData = [
            'name'    => $request->nama,
            'email'   => $request->email,
            'phone'   => $request->telepon,
            'address' => $request->alamat,
        ];

        // Handle avatar
        if ($request->input('remove_avatar') === '1') {
            $updateData['avatar'] = null;
        } elseif ($request->filled('avatar_base64')) {
            $base64 = $request->input('avatar_base64');

            if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64)) {
                return back()->with('error', 'Format gambar tidak valid.');
            }

            if (strlen($base64) > 3 * 1024 * 1024) {
                return back()->with('error', 'Ukuran foto terlalu besar, maksimal 2MB.');
            }

            $updateData['avatar'] = $base64;
        }

        // Update ke database
        $user->update($updateData);

        // Ambil data terbaru dari DB (fresh instance)
        $user = $user->fresh();

        // Update session
        $this->updateSession($user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Helper: Update session dengan data user terbaru
     */
    private function updateSession(User $user)
    {
        $userArray = $user->toArray();

        // Hapus field sensitif
        unset(
            $userArray['password'],
            $userArray['remember_token'],
            $userArray['email_verified_at']
        );

        // Pastikan _id tetap ada
        if (!isset($userArray['_id']) && isset($userArray['id'])) {
            $userArray['_id'] = $userArray['id'];
        }

        Session::put('user', $userArray);
    }

    /**
     * Ganti password (placeholder)
     */
    public function password(Request $request)
    {
        // TODO: Tambahkan validasi dan logic ganti password nanti
        return back()->with('success', 'Password berhasil diubah.');
    }
}