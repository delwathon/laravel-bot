<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index()
    {
        // TODO: Fetch user's trades from database
        return view('user.trades.index');
    }
}
