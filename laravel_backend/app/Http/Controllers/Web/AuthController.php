<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle Login Request
    public function login(Request $request)
    {
        $credentials = $request->only('email','password');

    if (!Auth::attempt($credentials)) {
        return back()->with('error','Email atau password salah');
    }

    $user = Auth::user();

    session([
        'user' => $user
    ]);

    if ($user->role === 'admin') {
        return redirect('/admin/dashboard');
    }

    return redirect('/dashboard');
    }

    // Handle Logout Request
    public function logout()
    {
        Auth::logout();
        session()->flush();

        return redirect('/login');
    }

    // Show Register UserForm
    public function showRegisterUser()
    {
        return view('auth.register-user');
    }

    // Handle Register User Request
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        return redirect('/login')->with('success','Registrasi berhasil, silakan login');
    }

    // Show Register Admin Form
    public function showRegisterAdmin()
    {
        return view('auth.register-admin');
    }

    // Handle Register Admin Request
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin'
        ]);

        return redirect('/login')->with('success','Registrasi berhasil, silakan login');
    }
}
