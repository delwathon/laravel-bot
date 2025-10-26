<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function index()
    {
        return view('user.exchanges.connect');
    }

    public function manage()
    {
        // TODO: Fetch user's connected exchanges from database
        return view('user.exchanges.manage');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exchange' => ['required', 'in:bybit,binance'],
            'api_key' => ['required', 'string'],
            'api_secret' => ['required', 'string'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        // TODO: Encrypt and store API keys
        // TODO: Test connection to exchange
        // TODO: Fetch initial balance

        return redirect()->route('user.exchanges.manage')
            ->with('success', 'Exchange connected successfully!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        // TODO: Update exchange connection label

        return back()->with('success', 'Connection updated successfully!');
    }

    public function destroy($id)
    {
        // TODO: Close all positions
        // TODO: Delete API connection

        return redirect()->route('user.exchanges.manage')
            ->with('success', 'Exchange disconnected successfully!');
    }
}
