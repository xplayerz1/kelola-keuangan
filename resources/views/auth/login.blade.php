@extends('layouts.auth')

@section('title', 'Login - HMSI Finance')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <i class="fas fa-coins"></i>
        <h1>HMSI Finance</h1>
        <p>Sistem Pengelolaan Keuangan</p>
    </div>
    
    <div class="auth-body">
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif
        
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus
                           placeholder="admin@hmsi.or.id">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required
                           placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Remember Me -->
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Ingat Saya
                </label>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>
            </div>
        </form>
        
        <!-- Demo Credentials -->
        <div class="alert alert-info mt-4 mb-0">
            <strong><i class="fas fa-info-circle me-1"></i> Akun Demo:</strong><br>
            <small>
                Email: <code>admin@hmsi.or.id</code><br>
                Password: <code>password123</code>
            </small>
        </div>
    </div>
    
    <div class="auth-footer">
        Belum punya akun? 
        <a href="{{ url('/register') }}">Daftar sekarang</a>
    </div>
</div>
@endsection
