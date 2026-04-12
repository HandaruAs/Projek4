<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function home()
    {
        $user = session('user');

        if (!$user) {
            return redirect('/login');
        }

        return view('user.home', compact('user'));
    }
}
