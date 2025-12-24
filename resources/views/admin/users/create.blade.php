@extends('layouts.app')

@section('title', 'Tambah User Baru - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                <li class="breadcrumb-item active">Tambah User Baru</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-user-plus"></i> Tambah User Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Data User
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST" id="userForm">
                    @csrf
                    
                    <!-- Nama -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
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
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required
                               minlength="8">
                        <small class="text-muted">Minimal 8 karakter</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Role -->
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" 
                                id="role_id" 
                                name="role_id" 
                                required>
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                        <input type="hidden" name="provinsi_nama" id="provinsi_nama">
                    </div>

                    <!-- Kota/Kabupaten -->
                    <div class="mb-3">
                        <label for="kabkota" class="form-label">Kabupaten/Kota <small class="text-muted">(Opsional)</small></label>
                        <select class="form-select" id="kabkota" name="kabkota_id" disabled>
                            <option value="">-- Pilih Provinsi Dulu --</option>
                        </select>
                        <input type="hidden" name="kabkota_nama" id="kabkota_nama">
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
                            <i class="fas fa-save"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6 class="mb-3">Role Pengguna:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <span class="badge bg-danger">Admin</span><br>
                        <small class="text-muted">Akses penuh ke semua fitur</small>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-success">Bendahara</span><br>
                        <small class="text-muted">Kelola kategori, transaksi, saldo</small>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-secondary">Viewer</span><br>
                        <small class="text-muted">Hanya dapat melihat laporan</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = '/admin/api';

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
                provinsiSelect.add(option);
            });
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

// When province changes, load cities
document.getElementById('provinsi').addEventListener('change', async function() {
    const provinsiId = this.value;
    const provinsiText = this.options[this.selectedIndex].text;
    const kabkotaSelect = document.getElementById('kabkota');
    
    // Store province name
    document.getElementById('provinsi_nama').value = provinsiText !== '-- Pilih Provinsi --' ? provinsiText : '';
    
    // Reset and disable city
    kabkotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';
    kabkotaSelect.disabled = !provinsiId;
    document.getElementById('kabkota_nama').value = '';
    
    if (provinsiId) {
        try {
            // Fix: Use query parameter, not path parameter
            const response = await fetch(`${API_BASE}/cities?d_provinsi_id=${provinsiId}`);
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

// When city changes, store name
document.getElementById('kabkota').addEventListener('change', function() {
    const kabkotaText = this.options[this.selectedIndex].text;
    document.getElementById('kabkota_nama').value = kabkotaText !== '-- Pilih Kota/Kabupaten --' ? kabkotaText : '';
});

// Helper function to reset dropdown
function resetDropdown(selectId, placeholderText, disable) {
    const select = document.getElementById(selectId);
    select.innerHTML = `<option value="">${placeholderText}</option>`;
    select.disabled = disable;
    document.getElementById(`${selectId}_nama`).value = '';
}
</script>
@endsection
