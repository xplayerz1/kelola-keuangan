@extends('layouts.app')

@section('title', 'Manajemen User - HMSI Finance')

@section('styles')
<style>
    /* Prevent modal flickering */
    .modal {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }
    .modal-backdrop {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="fas fa-users"></i> Manajemen User</h2>
        <p class="text-muted">Kelola pengguna dan role mereka</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Tambah User Baru
        </a>
    </div>
</div>

<!-- User Management Table -->
<h5 class="mb-3"><i class="fas fa-list"></i> Daftar User</h5>

<div class="card">
    @if($users->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="20%">Nama</th>
                    <th width="20%">Email</th>
                    <th width="15%">Role</th>
                    <th width="15%">Domisili</th>
                    <th width="15%">Terdaftar</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->id === auth()->id())
                            <span class="badge bg-info ms-1">Anda</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{-- MENAMPILKAN RELASI: User -> Role --}}
                        <span class="badge 
                            @if($user->role->nama_role === 'Admin') bg-danger
                            @elseif($user->role->nama_role === 'Bendahara') bg-success
                            @else bg-secondary
                            @endif">
                            {{ $user->role->nama_role }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">
                            @if($user->kelurahan_nama || $user->kecamatan_nama || $user->kabkota_nama || $user->provinsi_nama)
                                {{ $user->kabkota_nama ?? '-' }}, {{ $user->provinsi_nama ?? '-' }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           title="Edit">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        @if($user->id !== auth()->id())
                            <button type="button" 
                                    class="btn btn-link btn-sm p-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#changeRoleModal{{ $user->id }}"
                                    title="Ubah Role">
                                <i class="fas fa-user-tag text-warning"></i>
                            </button>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-link btn-sm p-1" 
                                        title="Hapus">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <p class="text-muted">Belum ada user terdaftar</p>
    </div>
    @endif
</div>

{{-- Modals - Completely Outside Card --}}
@if($users->count() > 0)
    @foreach($users as $user)
        @if($user->id !== auth()->id())
        <div class="modal fade" id="changeRoleModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Role: {{ $user->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.users.change-role', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Role Saat Ini</label>
                                <input type="text" class="form-control" value="{{ $user->role->nama_role }}" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="role_id{{ $user->id }}" class="form-label">Role Baru</label>
                                <select class="form-select" id="role_id{{ $user->id }}" name="role_id" required>
                                    @foreach(\App\Models\Role::all() as $role)
                                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                        {{ $role->nama_role }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endif

{{-- Statistik Role --}}
<div class="row mt-4 g-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-danger">{{ $users->where('role.nama_role', 'Admin')->count() }}</h3>
                <p class="text-muted mb-0">Admin</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">{{ $users->where('role.nama_role', 'Bendahara')->count() }}</h3>
                <p class="text-muted mb-0">Bendahara</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-secondary">{{ $users->where('role.nama_role', 'Viewer')->count() }}</h3>
                <p class="text-muted mb-0">Viewer</p>
            </div>
        </div>
    </div>
</div>
@endsection
