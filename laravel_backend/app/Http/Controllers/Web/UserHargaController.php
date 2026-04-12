<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserHargaController extends Controller
{
    //

    public function harga(Request $request)
    {
        return view('user.harga');
    }
}