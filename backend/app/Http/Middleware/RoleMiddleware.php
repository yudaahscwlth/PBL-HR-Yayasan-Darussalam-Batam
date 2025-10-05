<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }
        
        // Cek jika role user sesuai dengan role yang diizinkan
        if (Auth::user()->role !== $role) {
            // Redirect ke dashboard berdasarkan role user
            return match (Auth::user()->role) {
                'hrd' => redirect()->route('admin.index'),
                'kepala_yayasan' => redirect()->route('kepala.dashboard'),
                'pegawai' => redirect()->route('karyawan.index'),
                default => redirect()->route('login'),
            };
        }
        
        return $next($request);
    }
}