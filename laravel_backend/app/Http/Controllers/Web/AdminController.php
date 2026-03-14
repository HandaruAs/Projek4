<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = session('user');

        if (!$user) {
            return redirect('/login');
        }

        return view('admin.dashboard', compact('user'));
    }
}
