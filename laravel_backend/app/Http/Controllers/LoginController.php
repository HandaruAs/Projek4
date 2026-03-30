<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Cek credentials ke database (sesuaikan dengan model/query yang kamu pakai)
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        // Simpan data user ke session (konsisten dengan AdminController & UserController)
        $request->session()->put('user', [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,  // 'admin' atau 'user'
        ]);

        // Redirect otomatis berdasarkan role — tanpa perlu user memilih
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/user/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}