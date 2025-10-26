<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function index()
    {
        return view('admin.signals.index');
    }
}
