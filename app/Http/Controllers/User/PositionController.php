<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        // TODO: Fetch user's active positions from database
        return view('user.positions.index');
    }
}

