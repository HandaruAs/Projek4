<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class UserChatAiController extends Controller
{
    public function index()
    {
        return view('user.chatai');
    }
}