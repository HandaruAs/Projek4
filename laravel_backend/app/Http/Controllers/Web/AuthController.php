<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
    'user' => [
        '_id'    => (string) $user->_id,
        'nama'   => $user->name,
        'email'  => $user->email,
        'role'   => $user->role,
        'avatar' => $user->avatar ?? null,  
    ]
]);

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/home');
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
            'name' => 'User',
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

    // Show Forgot Password Form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Handle Send OTP Request
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email tidak ditemukan');
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Simpan OTP ke MongoDB
        $user->otp = (string)$otp;
        $user->otp_expired_at = now()->addMinutes(5);
        $user->save();

        // Kirim email OTP
        Mail::raw("Kode OTP kamu adalah: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Reset Password OTP');
        });

        return redirect()->route('verify.otp')->with('email', $user->email);
    }

    // Show Verify OTP Form
    public function showVerifyOtp()
    {
        return view('auth.verify-otp');
    }

    // Handle Verify OTP Request
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp != $request->otp) {
            return back()->with('error', 'OTP salah');
        }

        // Cek expired
        if (!$user->otp_expired_at || now()->greaterThan($user->otp_expired_at)) {
            return back()->with('error', 'OTP sudah kadaluarsa');
        }

        return redirect()->route('reset.password')->with('email', $user->email);
    }

    // Show Reset Password Form
    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    // Handle Reset Password Request
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expired_at' => null
        ]);

        return redirect('/login')->with('success', 'Password berhasil diubah');
    }
}