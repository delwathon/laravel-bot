<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExchangeAccount;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExchangeController extends Controller
{
    public function connect()
    {
        $user = auth()->user();
        
        if ($user->hasConnectedExchange()) {
            return redirect()->route('user.exchanges.manage')
                ->with('info', 'You already have a Bybit account connected. You can only connect one account.');
        }
        
        return view('user.exchanges.connect');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasConnectedExchange()) {
            return redirect()->route('user.exchanges.manage')
                ->with('error', 'You already have a Bybit account connected. Disconnect it first to add a new one.');
        }
        
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
        ]);
        
        try {
            $bybit = new BybitService($validated['api_key'], $validated['api_secret']);
            
            if (!$bybit->testConnection()) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to connect to Bybit. Please check your API credentials.']);
            }
            
            $balance = $bybit->getBalance();
            
            ExchangeAccount::create([
                'user_id' => $user->id,
                'exchange' => 'bybit',
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'],
                'is_active' => true,
                'balance' => $balance,
                'last_synced_at' => now(),
            ]);
            
            return redirect()->route('user.exchanges.manage')
                ->with('success', 'Bybit account connected successfully!');
                
        } catch (\Exception $e) {
            Log::error('Bybit connection failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to connect Bybit account: ' . $e->getMessage()]);
        }
    }

    public function manage()
    {
        $user = auth()->user();
        $exchangeAccount = $user->exchangeAccount;
        
        if (!$exchangeAccount) {
            return redirect()->route('user.exchanges.connect')
                ->with('info', 'Please connect your Bybit account first.');
        }
        
        try {
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
            $balance = $bybit->getBalance();
            
            $exchangeAccount->update([
                'balance' => $balance,
                'last_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync Bybit balance: ' . $e->getMessage());
        }
        
        return view('user.exchanges.manage', compact('exchangeAccount'));
    }

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
        
        try {
            $bybit = new BybitService($validated['api_key'], $validated['api_secret']);
            
            if (!$bybit->testConnection()) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to connect to Bybit. Please check your API credentials.']);
            }
            
            $balance = $bybit->getBalance();
            
            $exchangeAccount->update([
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'],
                'balance' => $balance,
                'last_synced_at' => now(),
            ]);
            
            return redirect()->route('user.exchanges.manage')
                ->with('success', 'Bybit account updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Bybit update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update Bybit account: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $exchangeAccount = ExchangeAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $openTrades = $user->openTrades()->count();
        $activePositions = $user->activePositions()->count();
        
        if ($openTrades > 0 || $activePositions > 0) {
            return back()->withErrors([
                'error' => 'Cannot disconnect exchange while you have open trades or active positions. Please close all positions first.'
            ]);
        }
        
        $exchangeAccount->delete();
        
        return redirect()->route('user.exchanges.connect')
            ->with('success', 'Bybit account disconnected successfully!');
    }

    public function syncBalance($id)
    {
        $user = auth()->user();
        $exchangeAccount = ExchangeAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        try {
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
            $balance = $bybit->getBalance();
            
            $exchangeAccount->update([
                'balance' => $balance,
                'last_synced_at' => now(),
            ]);
            
            return back()->with('success', 'Balance synced successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to sync balance: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to sync balance: ' . $e->getMessage()]);
        }
    }
}