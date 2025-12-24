@extends('layouts.app')

@section('title', 'Generate Laporan Baru - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Generate Laporan Baru</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Generate Laporan Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Laporan
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.store') }}" method="POST">
                    @csrf
                    
                    <!-- Judul -->
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('judul') is-invalid @enderror" 
                               id="judul" 
                               name="judul" 
                               value="{{ old('judul') }}" 
                               placeholder="Contoh: Laporan Keuangan Desember 2025"
                               required>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Periode -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date', date('Y-m-01')) }}" 
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ old('end_date', date('Y-m-t')) }}" 
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tidak  boleh lebih kecil dari tanggal mulai</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
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
                                  rows="3"
                                  placeholder="Catatan tambahan untuk laporan ini">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Generate Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Yang Akan Dilakukan:</h6>
                <ul class="small">
                    <li>Sistem akan mengambil semua transaksi dalam rentang tanggal</li>
                    <li>Menghitung total pemasukan & pengeluaran</li>
                    <li>Membuat ringkasan per kategori</li>
                    <li>Menyimpan timestamp dari World Time API</li>
                </ul>
                
                <h6 class="fw-bold mt-3">Catatan:</h6>
                <ul class="small mb-0">
                    <li>Laporan yang sudah dibuat tidak akan berubah jika ada transaksi baru</li>
                    <li>Status "Draft" hanya bisa dilihat Admin/Bendahara</li>
                    <li>Laporan bisa di-export ke PDF & Excel</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
