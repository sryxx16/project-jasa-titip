<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $user = $request->user(); // Ambil user setelah berhasil diautentikasi

        // Pengecekan khusus untuk role traveler
        if ($user->role === 'traveler') {
            // Memuat travelerProfile agar bisa diakses
            $user->load('travelerProfile');

            if (!$user->travelerProfile || $user->travelerProfile->verification_status !== 'verified') {
                Auth::guard('web')->logout(); // Logout user yang belum diverifikasi
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => 'Akun traveler Anda belum diverifikasi oleh admin. Silakan tunggu konfirmasi.']);
            }
        }

        $request->session()->regenerate();

        if ($user->role === 'admin') {
            // Redirect to admin dashboard if user is an admin
            return redirect()->intended('/admin');
        }

        // Redirect based on user role
        if ($user->role === 'penitip') {
            return redirect()->route('customer.dashboard');
        } elseif ($user->role === 'traveler') {
            return redirect()->route('traveler.dashboard');
        }

        return redirect('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
