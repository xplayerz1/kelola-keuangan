@extends('layouts.app')

@section('title', 'Edit Kategori - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></li>
                <li class="breadcrumb-item active">Edit: {{ $category->nama_kategori }}</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Kategori</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Edit Kategori
            </div>
            <div class="card-body">
                <form action="{{ route('kategori.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Nama Kategori -->
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('nama_kategori') is-invalid @enderror" 
                               id="nama_kategori" 
                               name="nama_kategori" 
                               value="{{ old('nama_kategori', $category->nama_kategori) }}" 
                               required>
                        @error('nama_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Jenis Kategori -->
                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select @error('jenis') is-invalid @enderror" 
                                id="jenis" 
                                name="jenis" 
                                required>
                            <option value="Pemasukan" {{ old('jenis', $category->jenis) == 'Pemasukan' ? 'selected' : '' }}>
                                Pemasukan
                            </option>
                            <option value="Pengeluaran" {{ old('jenis', $category->jenis) == 'Pengeluaran' ? 'selected' : '' }}>
                                Pengeluaran
                            </option>
                        </select>
                        @error('jenis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                  id="keterangan" 
                                  name="keterangan" 
                                  rows="4"
                                  maxlength="500">{{ old('keterangan', $category->keterangan) }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Maksimal 500 karakter</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Kategori
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
                <i class="fas fa-info-circle"></i> Informasi Kategori
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Dibuat:</strong></td>
                        <td>{{ $category->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Diupdate:</strong></td>
                        <td>{{ $category->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jumlah Transaksi:</strong></td>
                        <td><span class="badge bg-info">{{ $category->transactions()->count() }}</span></td>
                    </tr>
                </table>
                
                @if($category->transactions()->count() > 0)
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Kategori ini memiliki {{ $category->transactions()->count() }} transaksi terkait.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
