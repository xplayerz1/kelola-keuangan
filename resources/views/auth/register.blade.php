@extends('layouts.auth')

@section('title', 'Register - HMSI Finance')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <i class="fas fa-coins"></i>
        <h1>HMSI Finance</h1>
        <p>Daftar Akun Baru</p>
    </div>
    
    <div class="auth-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ url('/register') }}">
            @csrf
            
            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan nama lengkap">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
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
                           placeholder="contoh@email.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Provinsi -->
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi <small class="text-muted">(Opsional)</small></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                    <select class="form-control" id="provinsi" name="provinsi_id">
                        <option value="">-- Pilih Provinsi --</option>
                    </select>
                </div>
                <input type="hidden" name="provinsi_nama" id="provinsi_nama">
            </div>
            
            <!-- Kabupaten/Kota -->
            <div class="mb-3">
                <label for="kabkota" class="form-label">Kabupaten/Kota <small class="text-muted">(Opsional)</small></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                    <select class="form-control" id="kabkota" name="kabkota_id" disabled>
                        <option value="">-- Pilih Provinsi Dulu --</option>
                    </select>
                </div>
                <input type="hidden" name="kabkota_nama" id="kabkota_nama">
            </div>
            
            <!-- Role (hidden, default Viewer) -->
            <input type="hidden" name="role_id" value="3">
            
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
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Password Confirmation -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           placeholder="Ulangi password">
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Daftar
                </button>
            </div>
        </form>
        
        <!-- Role Information -->
        <div class="alert alert-warning mt-4 mb-0">
            <strong><i class="fas fa-user-tag me-1"></i> Info:</strong><br>
            <small>
                User baru terdaftar sebagai <strong>Viewer</strong> (read-only).<br>
                Hubungi Admin untuk upgrade ke Bendahara.
            </small>
        </div>
    </div>
    
    <div class="auth-footer">
        Sudah punya akun? 
        <a href="{{ url('/login') }}">Login di sini</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = window.location.origin;

document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
});

async function loadProvinces() {
    try {
        const response = await fetch(`${API_BASE}/api/provinces`);
        const data = await response.json();
        
        const provinsiSelect = document.getElementById('provinsi');
        
        if (data.status === 200 && data.result) {
            data.result.forEach(item => {
                const option = new Option(item.text, item.id);
                provinsiSelect.add(option);
            });
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

document.getElementById('provinsi').addEventListener('change', async function() {
    const provinsiId = this.value;
    const selectedText = this.options[this.selectedIndex].text;
    const kabkotaSelect = document.getElementById('kabkota');
    
    document.getElementById('provinsi_nama').value = selectedText !== '-- Pilih Provinsi --' ? selectedText : '';
    
    kabkotaSelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
    kabkotaSelect.disabled = !provinsiId;
    document.getElementById('kabkota_nama').value = '';
    
    if (provinsiId) {
        try {
            const response = await fetch(`${API_BASE}/api/cities?d_provinsi_id=${provinsiId}`);
            const data = await response.json();
            
            if (data.status === 200 && data.result) {
                data.result.forEach(item => {
                    const option = new Option(item.text, item.id);
                    kabkotaSelect.add(option);
                });
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }
});

document.getElementById('kabkota').addEventListener('change', function() {
    const selectedText = this.options[this.selectedIndex].text;
    document.getElementById('kabkota_nama').value = selectedText !== '-- Pilih Kabupaten/Kota --' ? selectedText : '';
});
</script>
@endsection
