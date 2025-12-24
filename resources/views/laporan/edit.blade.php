@extends('layouts.app')

@section('title', 'Edit Laporan - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
                <li class="breadcrumb-item"><a href="{{ route('laporan.show', $laporan->id) }}">{{ $laporan->judul }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Laporan</h2>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Form Edit Laporan
    </div>
    <div class="card-body">
        <form action="{{ route('laporan.update', $laporan->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Judul -->
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('judul') is-invalid @enderror" 
                       id="judul" 
                       name="judul" 
                       value="{{ old('judul', $laporan->judul) }}" 
                       required>
                @error('judul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Status -->
            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" 
                        id="status" 
                        name="status" 
                        required>
                    <option value="published" {{ old('status', $laporan->status) === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ old('status', $laporan->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Catatan -->
            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                <textarea class="form-control @error('catatan') is-invalid @enderror" 
                          id="catatan" 
                          name="catatan" 
                          rows="3">{{ old('catatan', $laporan->catatan) }}</textarea>
                @error('catatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Catatan:</strong> Periode laporan dan data transaksi tidak dapat diubah. Hanya metadata laporan yang dapat diperbarui.
            </div>
            
            <hr class="my-4">
            
            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('laporan.show', $laporan->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Laporan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
