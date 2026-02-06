<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Periksa apakah user adalah admin
        if (!Auth::user()->isAdmin()) {
            // Redirect berdasarkan role user
            if (Auth::user()->role === 'penitip') {
                return redirect()->route('customer.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
            } elseif (Auth::user()->role === 'traveler') {
                return redirect()->route('traveler.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
            } else {
                return redirect()->route('landing')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
            }
        }

        return $next($request);
    }
}
