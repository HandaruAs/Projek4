<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('user.profil', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama'    => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat'  => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            
        ]);

        $user->name  = $request->nama;
        $user->telepon = $request->telepon;
        $user->alamat  = $request->alamat;


        if ($request->hasFile('avatar')) {
            // hapus lama
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
