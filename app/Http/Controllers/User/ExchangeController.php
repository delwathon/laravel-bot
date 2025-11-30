<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExchangeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExchangeController extends Controller
{
    /**
     * Show Bybit connection form
     */
    public function connect()
    {
        $user = auth()->user();
        
        // Check if already connected
        if ($user->hasConnectedExchange()) {
            return redirect()->route('user.exchanges.manage')
                ->with('info', 'You already have a Bybit account connected. You can only connect one account.');
        }
        
        return view('user.exchanges.connect');
    }

    /**
     * Store Bybit connection
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if already connected
        if ($user->hasConnectedExchange()) {
            return redirect()->route('user.exchanges.manage')
                ->with('error', 'You already have a Bybit account connected. Disconnect it first to add a new one.');
        }
        
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
        ]);
        
        // TODO: Validate API credentials with Bybit
        // For now, we'll skip validation and just store
        
        try {
            ExchangeAccount::create([
                'user_id' => $user->id,
                'exchange' => 'bybit',
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'],
                'is_active' => true,
            ]);
            
            return redirect()->route('user.exchanges.manage')
                ->with('success', 'Bybit account connected successfully!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to connect Bybit account. Please try again.']);
        }
    }

    /**
     * Show connected Bybit account
     */
    public function manage()
    {
        $user = auth()->user();
        $exchangeAccount = $user->exchangeAccount;
        
        return view('user.exchanges.manage', compact('exchangeAccount'));
    }

    /**
     * Update Bybit account settings
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $exchangeAccount = ExchangeAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
        ]);
        
        // TODO: Validate new API credentials with Bybit
        
        try {
            $exchangeAccount->update([
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'],
            ]);
            
            return redirect()->route('user.exchanges.manage')
                ->with('success', 'Bybit account updated successfully!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update Bybit account. Please try again.']);
        }
    }

    /**
     * Disconnect Bybit account
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $exchangeAccount = ExchangeAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // TODO: 
        // 1. Close any open positions
        // 2. Verify no active trades
        
        $exchangeAccount->delete();
        
        return redirect()->route('user.exchanges.connect')
            ->with('success', 'Bybit account disconnected successfully!');
    }
}