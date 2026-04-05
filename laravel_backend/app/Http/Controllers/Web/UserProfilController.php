<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserProfilController extends Controller
{
      public function index() {
    $user = session('user'); // atau ambil dari DB
    return view('user.profil', compact('user'));
}

public function update(Request $request) {
    // update data user
    return back()->with('success', 'Profil berhasil diperbarui.');
}

public function password(Request $request) {
    // update password
    return back()->with('success', 'Password berhasil diubah.');
}
}
