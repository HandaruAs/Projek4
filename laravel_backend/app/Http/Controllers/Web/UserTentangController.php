<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserTentangController extends Controller
{
    public function tentang()
{
    return view('user.tentang');
}
}
