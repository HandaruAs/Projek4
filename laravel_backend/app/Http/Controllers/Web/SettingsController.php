<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if ($user->role !== 'admin') return redirect('/dashboard');
        return $user;
    }

    /** GET /admin/settings */
    public function index()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        return view('admin.settings', compact('user'));
    }

    /** PUT /admin/settings/profile */
    public function updateProfile(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        // $userData = User::find($user->_id);
        // $userData->update(['name' => $request->name, 'email' => $request->email]);
        // session(['user' => $userData]);

        return redirect('/admin/settings')->with('success', 'Profil berhasil diperbarui.');
    }

    /** PUT /admin/settings/password */
    public function updatePassword(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        // $userData = User::find($user->_id);
        // if (!Hash::check($request->current_password, $userData->password)) {
        //     return back()->withErrors(['current_password' => 'Password lama salah.']);
        // }
        // $userData->update(['password' => Hash::make($request->new_password)]);

        return redirect('/admin/settings')->with('success', 'Password berhasil diperbarui.');
    }
}