<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::where('is_admin', false)
            ->withCount(['trades', 'activePositions']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }
        
        // Exchange filter
        if ($request->filled('exchange')) {
            if ($request->exchange === 'connected') {
                $query->whereHas('exchangeAccount');
            } elseif ($request->exchange === 'not_connected') {
                $query->whereDoesntHave('exchangeAccount');
            }
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $users = $query->paginate(20)->withQueryString();
        
        // Load relationships for each user
        $users->each(function($user) {
            // Calculate today's trades
            $user->today_trades_count = $user->trades()
                ->whereDate('created_at', today())
                ->count();
            
            // Get total P&L
            $user->total_pnl = $user->total_pnl;
            
            // Calculate P&L percentage
            $closedTrades = $user->closedTrades()->count();
            if ($closedTrades > 0) {
                $totalInvested = $user->closedTrades()
                    ->sum(\DB::raw('entry_price * quantity'));
                $user->total_pnl_percent = $totalInvested > 0 
                    ? round(($user->total_pnl / $totalInvested) * 100, 2)
                    : 0;
            } else {
                $user->total_pnl_percent = 0;
            }
        });
        
        // Statistics - all calculated in controller
        $totalUsers = User::where('is_admin', false)->count();
        $activeUsers = User::where('is_admin', false)
            ->whereNotNull('email_verified_at')
            ->count();
        $newThisMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $connectedExchanges = User::where('is_admin', false)
            ->whereHas('exchangeAccount')
            ->count();
        
        // Calculate average per day
        $firstUser = User::where('is_admin', false)->oldest()->first();
        $avgPerDay = 0;
        if ($firstUser) {
            $daysSinceFirstUser = max(now()->diffInDays($firstUser->created_at), 1);
            $avgPerDay = round($totalUsers / $daysSinceFirstUser, 1);
        }
        
        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'newThisMonth',
            'connectedExchanges',
            'avgPerDay'
        ));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Prevent viewing admin users
        if ($user->is_admin) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot view admin user details');
        }
        
        // Load exchange account relationship
        $user->load('exchangeAccount');
        
        // User statistics - REAL DATA
        $userStats = [
            'total_trades' => $user->total_trades_count,
            'active_positions' => $user->active_positions_count,
            'total_profit' => $user->total_pnl,
            'win_rate' => $user->win_rate,
            'has_exchange' => $user->hasConnectedExchange(),
        ];
        
        return view('admin.users.show', compact('user', 'userStats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Prevent editing admin users
        if ($user->is_admin) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot edit admin users');
        }
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating admin users
        if ($user->is_admin) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot update admin users');
        }
        
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        ]);
        
        // Handle password update if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting admin users
        if ($user->is_admin) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete admin users');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been deleted successfully!");
    }
}