@extends('layouts.app')

@section('title', 'Edit User - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-user-edit"></i> Edit User: {{ $user->name }}</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Edit User
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="userForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Nama -->
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
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   minlength="8">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <!-- Role -->
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" 
                                id="role_id" 
                                name="role_id" 
                                required>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">
                        <i class="fas fa-map-marked-alt text-primary"></i> 
                        Alamat Lengkap 
                        <small class="text-muted">(Opsional - dari API Wilayah Indonesia)</small>
                    </h5>
                    
                    <!-- Provinsi -->
                    <div class="mb-3">
                        <label for="provinsi" class="form-label">Provinsi <small class="text-muted">(Opsional)</small></label>
                        <select class="form-select" id="provinsi" name="provinsi_id">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                        <input type="hidden" name="provinsi_nama" id="provinsi_nama" value="{{ $user->provinsi_nama }}">
                    </div>

                    <!-- Kota/Kabupaten -->
                    <div class="mb-3">
                        <label for="kabkota" class="form-label">Kabupaten/Kota <small class="text-muted">(Opsional)</small></label>
                        <select class="form-select" id="kabkota" name="kabkota_id" disabled>
                            <option value="">-- Pilih Provinsi Dulu --</option>
                        </select>
                        <input type="hidden" name="kabkota_nama" id="kabkota_nama" value="{{ $user->kabkota_nama }}">
                        <small class="text-muted">
                            <i class="fas fa-globe"></i> Data dari API Wilayah Indonesia
                        </small>
                    </div>
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Current Info Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-circle"></i> Data Saat Ini
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Nama:</strong></td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td>
                            <span class="badge 
                                @if($user->role->nama_role === 'Admin') bg-danger
                                @elseif($user->role->nama_role === 'Bendahara') bg-success
                                @else bg-secondary
                                @endif">
                                {{ $user->role->nama_role }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Domisili:</strong></td>
                        <td>{{ $user->kabkota_nama ?? $user->provinsi_nama ?? 'Tidak ada' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Terdaftar:</strong></td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
                
                @if($user->id === auth()->id())
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    <strong>Catatan:</strong> Anda sedang mengedit akun Anda sendiri.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = '/admin/api';

// Store existing user location data
const existingProvinsiId = '{{ $user->provinsi_id }}';
const existingKabkotaId = '{{ $user->kabkota_id }}';

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
                if (item.id == existingProvinsiId) {
                    option.selected = true;
                }
                provinsiSelect.add(option);
            });
            
            // If user has existing province, load cities
            if (existingProvinsiId) {
                document.getElementById('provinsi_nama').value = '{{ $user->provinsi_nama }}';
                loadCities(existingProvinsiId);
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
                if (item.id == existingKabkotaId) {
                    option.selected = true;
                }
                kabkotaSelect.add(option);
            });
            
            // Set existing kabkota name if present
            if (existingKabkotaId) {
                document.getElementById('kabkota_nama').value = '{{ $user->kabkota_nama }}';
            }
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
    
    // Store province name
    document.getElementById('provinsi_nama').value = provinsiText !== '-- Pilih Provinsi --' ? provinsiText : '';
    
    // Reset city
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

// Toggle password visibility
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
</script>
@endsection
