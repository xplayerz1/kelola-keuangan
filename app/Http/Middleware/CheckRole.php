<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Middleware untuk membatasi akses berdasarkan role.
     * Usage: Route::middleware(['auth', 'checkrole:Admin,Bendahara'])
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Nama-nama role yang diizinkan
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Ambil nama role user dari relasi
        $userRoleName = $user->role->nama_role ?? null;

        // Cek apakah role user ada dalam list role yang diizinkan
        if (!in_array($userRoleName, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
