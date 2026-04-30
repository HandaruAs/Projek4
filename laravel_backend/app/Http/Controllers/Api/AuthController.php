<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{

    // ─────────────────────────────────────────────
    // LOGIN
    // ─────────────────────────────────────────────    
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();


        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak ditemukan'
            ], 404);
        }


        if ($user->role === 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak ditemukan'
            ], 404);
        }

        // ❌ kalau password salah
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => [
                    'id'    => (string) $user->_id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                    'phone' => $user->phone ?? '',
                    'address' => $user->address ?? '',
                ],
                'token' => $token
            ]
        ]);
    }


    // ─────────────────────────────────────────────
    // REGISTER USER
    // ─────────────────────────────────────────────
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email', // ✅ cek duplikat email
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user'  => [
                    'id'    => (string) $user->_id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role, // ✅ pastikan role ikut di response
                ],
                'token' => $token,
            ],
        ]);
    }


    // ─────────────────────────────────────────────
    // REGISTER ADMIN
    // ─────────────────────────────────────────────
    public function registerAdmin(Request $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin'
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Admin berhasil dibuat',
            'data'    => $user
        ]);
    }


    // ─────────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────────
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil'
        ]);
    }


    // ─────────────────────────────────────────────
    // FORGOT PASSWORD — kirim OTP ke email
    // ─────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Selalu kembalikan sukses agar email tidak bisa di-enumerate
        if (!$user) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Email tidak ditemukan.'
            ], 404);
        }

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp_code       = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10); // berlaku 10 menit
        $user->save();

        // Kirim OTP via email
        Mail::send([], [], function ($message) use ($user, $otp) {
            $message->to($user->email)
                    ->subject('Kode OTP Reset Password SIMOPANG')
                    ->html("
                        <div style='font-family:sans-serif;max-width:480px;margin:auto'>
                            <h2 style='color:#2563eb'>Reset Password SIMOPANG</h2>
                            <p>Gunakan kode OTP berikut untuk mereset password Anda:</p>
                            <div style='font-size:36px;font-weight:bold;letter-spacing:8px;
                                        color:#0a0f1e;background:#f0f4ff;padding:20px;
                                        border-radius:12px;text-align:center;margin:20px 0'>
                                {$otp}
                            </div>
                            <p style='color:#64748b;font-size:13px'>
                                Kode ini berlaku selama <strong>10 menit</strong>.<br>
                                Abaikan email ini jika Anda tidak meminta reset password.
                            </p>
                        </div>
                    ");
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda.'
        ]);
    }


    // ─────────────────────────────────────────────
    // VERIFY OTP
    // ─────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode OTP tidak valid.'
            ], 400);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta ulang.'
            ], 400);
        }

        // Tandai OTP telah diverifikasi — perpanjang window 10 menit untuk reset
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'OTP berhasil diverifikasi.'
        ]);
    }


    // ─────────────────────────────────────────────
    // RESET PASSWORD — setelah OTP terverifikasi
    // ─────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                 => 'required|email',
            'otp'                   => 'required|string|size:6',
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi tidak valid. Ulangi proses dari awal.'
            ], 400);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi telah kedaluwarsa. Ulangi proses dari awal.'
            ], 400);
        }

        // Update password & hapus OTP
        $user->password       = Hash::make($request->password);
        $user->otp_code       = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password berhasil diperbarui. Silakan login.'
        ]);
    }

    // ─────────────────────────────────────────────
    // GET PROFILE
    // ─────────────────────────────────────────────
    public function getProfile()
    {
        $user = auth()->user();
        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'      => (string) $user->_id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone   ?? '',
                'address' => $user->address ?? '',
                'role'    => $user->role,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|email|unique:users,email,' . $user->_id . ',_id',
            'phone'   => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($request->filled('name'))    $user->name    = $request->name;
        if ($request->filled('email'))   $user->email   = $request->email;
        if ($request->has('phone'))      $user->phone   = $request->phone;
        if ($request->has('address'))    $user->address = $request->address;

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data'    => [
                'id'      => (string) $user->_id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone   ?? '',
                'address' => $user->address ?? '',
                'role'    => $user->role,
            ]
        ]);
    }

    // ─────────────────────────────────────────────
    // CHANGE PASSWORD (user harus login)
    // ─────────────────────────────────────────────
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password'     => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password lama tidak sesuai',
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }
}
