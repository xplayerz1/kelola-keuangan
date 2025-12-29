@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Profile Header Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-circle"></i> Profil Saya</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="avatar-circle bg-primary text-white mb-3" style="width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mb-2">{{ $user->name }}</h4>
                        <p class="text-muted mb-1"><i class="fas fa-envelope"></i> {{ $user->email }}</p>
                        <p class="text-muted mb-1"><i class="fas fa-user-tag"></i> <span class="badge bg-{{ $user->role_id == 1 ? 'danger' : ($user->role_id == 2 ? 'success' : 'secondary') }}">{{ $user->role->nama_role }}</span></p>
                        @if($user->provinsi_nama || $user->kota_nama)
                        <p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> {{ $user->kota_nama ?? '' }}{{ $user->kota_nama && $user->provinsi_nama ? ', ' : '' }}{{ $user->provinsi_nama ?? '' }}</p>
                        @else
                        <p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi belum diisi</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#profile-info">
                    <i class="fas fa-user"></i> Informasi Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#change-password">
                    <i class="fas fa-lock"></i> Ganti Password
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Profile Info Tab -->
            <div class="tab-pane fade show active" id="profile-info">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Lokasi (Opsional)</h6>

                            <!-- Provinsi -->
                            <div class="mb-3">
                                <label for="provinsi" class="form-label">Provinsi</label>
                                <select class="form-select" id="provinsi" name="provinsi_id">
                                    <option value="">-- Pilih Provinsi --</option>
                                </select>
                                <input type="hidden" name="provinsi_nama" id="provinsi_nama" value="{{ $user->provinsi_nama }}">
                            </div>

                            <!-- Kota/Kabupaten -->
                            <div class="mb-3">
                                <label for="kabkota" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="kabkota" name="kabkota_id" disabled>
                                    <option value="">-- Pilih Provinsi Dulu --</option>
                                </select>
                                <input type="hidden" name="kabkota_nama" id="kabkota_nama" value="{{ $user->kabkota_nama }}">
                                <small class="text-muted">
                                    <i class="fas fa-globe"></i> Data dari API Wilayah Indonesia
                                </small>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="change-password">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key"></i> Ganti Password</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                        <i class="fas fa-eye" id="toggleCurrentPasswordIcon"></i>
                                    </button>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                        <i class="fas fa-eye" id="togglePasswordConfirmationIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-lock"></i> Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = '/api'; // Use public API endpoints
let currentProvinsiId = '{{ $user->provinsi_id }}';
let currentKabkotaId = '{{ $user->kabkota_id }}';

// Load provinces on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
});

// Load Provinces
async function loadProvinces() {
    try {
        const response = await fetch(`${API_BASE}/provinces`);
        const data = await response.json();
        
        const provinsiSelect = document.getElementById('provinsi');
        provinsiSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
        
        if (data.status === 200 && data.result) {
            data.result.forEach(item => {
                const option = new Option(item.text, item.id);
                if (item.id == currentProvinsiId) {
                    option.selected = true;
                }
                provinsiSelect.add(option);
            });
            
            // If user has province, load cities
            if (currentProvinsiId) {
                loadCities(currentProvinsiId);
            }
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

// Load Cities
async function loadCities(provinsiId) {
    try {
        const response = await fetch(`${API_BASE}/cities?d_provinsi_id=${provinsiId}`);
        const data = await response.json();
        
        const kabkotaSelect = document.getElementById('kabkota');
        kabkotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';
        kabkotaSelect.disabled = false;
        
        if (data.status === 200 && data.result) {
            data.result.forEach(item => {
                const option = new Option(item.text, item.id);
                if (item.id == currentKabkotaId) {
                    option.selected = true;
                }
                kabkotaSelect.add(option);
            });
        }
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

// Province change event
document.getElementById('provinsi').addEventListener('change', async function() {
    const provinsiId = this.value;
    const provinsiText = this.options[this.selectedIndex].text;
    const kabkotaSelect = document.getElementById('kabkota');
    
    document.getElementById('provinsi_nama').value = provinsiText !== '-- Pilih Provinsi --' ? provinsiText : '';
    
    kabkotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';
    kabkotaSelect.disabled = !provinsiId;
    document.getElementById('kabkota_nama').value = '';
    
    if (provinsiId) {
        loadCities(provinsiId);
    }
});

// City change event
document.getElementById('kabkota').addEventListener('change', function() {
    const kabkotaText = this.options[this.selectedIndex].text;
    document.getElementById('kabkota_nama').value = kabkotaText !== '-- Pilih Kota/Kabupaten --' ? kabkotaText : '';
});

// Toggle current password visibility
document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('current_password');
    const toggleIcon = document.getElementById('toggleCurrentPasswordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});

// Toggle new password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});

// Toggle password confirmation visibility
document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirmation');
    const toggleIcon = document.getElementById('togglePasswordConfirmationIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});
</script>
@endsection
