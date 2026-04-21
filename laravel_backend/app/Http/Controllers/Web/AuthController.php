<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Email atau password salah');
        }

        $request->session()->regenerate();

        $user = Auth::user();

    session([
    'user' => [
        '_id'    => (string) $user->_id,
        'nama'   => $user->name,
        'email'  => $user->email,
        'role'   => $user->role,
        'avatar' => $user->avatar ?? null,  
    ]
]);

        if ($user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('user.home');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showRegisterUser()
    {
        return view('auth.register-user');
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user'
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil, silakan login');
    }

    public function showRegisterAdmin()
    {
        return view('auth.register-admin');
    }

    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin'
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil, silakan login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email tidak ditemukan');
        }

        $otp = rand(100000, 999999);

        $user->otp            = (string) $otp;
        $user->otp_expired_at = now()->addMinutes(5);
        $user->save();

        Mail::raw("Kode OTP kamu adalah: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Reset Password OTP');
        });

        return redirect()->route('verify.otp')->with('email', $user->email);
    }

    public function showVerifyOtp()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp !== $request->otp) { // pakai !== bukan !=
            return back()->with('error', 'OTP salah');
        }

        if (!$user->otp_expired_at || now()->greaterThan($user->otp_expired_at)) {
            return back()->with('error', 'OTP sudah kadaluarsa');
        }

        return redirect()->route('reset.password')->with('email', $user->email);
    }

    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $user->update([
            'password'       => Hash::make($request->password),
            'otp'            => null,
            'otp_expired_at' => null
        ]);

        return redirect('/login')->with('success', 'Password berhasil diubah');
    }
}
