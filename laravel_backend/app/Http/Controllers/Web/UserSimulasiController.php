<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserSimulasiController extends Controller
{
    public function simulasi(Request $request)
    {
        return view('user.simulasi');
    }
}