<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     * 
     * Manual Laravel Authentication menggunakan Auth facade
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = $request->has('remember');

        // Attempt login using Auth facade
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard')
                ->with('success', 'Login berhasil! Selamat datang, ' . auth()->user()->name);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     * 
     * Secara otomatis memberikan role_id = 3 (Viewer) untuk user baru
     */
    public function register(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'provinsi_id' => 'nullable|string',
            'provinsi_nama' => 'nullable|string',
            'kabkota_id' => 'nullable|string',
            'kabkota_nama' => 'nullable|string',
        ]);

        // Create user dengan role_id default = 3 (Viewer)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3, // Default: Viewer
            'provinsi_id' => $validated['provinsi_id'] ?? null,
            'provinsi_nama' => $validated['provinsi_nama'] ?? null,
            'kabkota_id' => $validated['kabkota_id'] ?? null,
            'kabkota_nama' => $validated['kabkota_nama'] ?? null,
        ]);

        // Auto login after registration
        Auth::login($user);

        return redirect('/dashboard')
            ->with('success', 'Registrasi berhasil! Anda terdaftar sebagai Viewer.');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Logout berhasil. Sampai jumpa!');
    }
}
