<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserPrediksiController extends Controller
{
  public function prediksi(Request $request)
{
    return view('user.prediksi');
}
}
