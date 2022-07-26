<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        return view('system.message.index');
    }


}
