<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AccountController extends Controller
{
    /**
     * Display account settings
     */
    public function settings()
    {
        $user = auth()->user();
        
        return view('user.account.settings', compact('user'));
    }

    /**
     * Update account information
     */
    public function update(Request $request)
    {
        $user = auth()->user();

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
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => ['required'],
                'new_password' => ['required', 'min:8', 'confirmed', Rules\Password::defaults()],
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            
            return redirect()->route('user.account.settings')
                ->with('success', 'Account and password updated successfully!');
        }

        return redirect()->route('user.account.settings')
            ->with('success', 'Account updated successfully!');
    }
}