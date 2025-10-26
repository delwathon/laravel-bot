<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function settings()
    {
        return view('user.account.settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
            'default_leverage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'position_size' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'risk_level' => ['nullable', 'in:conservative,moderate,aggressive'],
            'stop_loss_percent' => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            'take_profit_percent' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        $user = auth()->user();

        // Update basic info
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = $validated['first_name'] . ' ' . $validated['last_name'];
        
        // Check if email changed
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null; // Require re-verification
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = \Hash::make($validated['new_password']);
        }

        $user->save();

        // TODO: Save trading preferences to user_settings table
        // TODO: Save notification preferences to user_settings table

        return back()->with('success', 'Settings updated successfully!');
    }
}
