<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is an admin
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access the admin panel.');
        }

        if (!auth()->user()->is_admin) {
            return redirect()->route('user.dashboard')->with('error', 'You do not have admin access.');
        }

        return $next($request);
    }
}