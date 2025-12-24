@extends('layouts.app')

@section('title', 'Set Saldo Awal - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('saldo.index') }}">Saldo</a></li>
                <li class="breadcrumb-item active">Set Saldo Awal</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Set Saldo Awal Periode Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Saldo Awal
            </div>
            <div class="card-body">
                @if($activeSaldo)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Saat ini ada periode aktif ({{ $activeSaldo->periode }}).
                    Membuat periode baru akan menutup periode aktif tersebut.
                </div>
                @endif
                
                <form action="{{ route('saldo.store') }}" method="POST">
                    @csrf
                    
                    <!-- Periode -->
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('periode') is-invalid @enderror" 
                               id="periode" 
                               name="periode" 
                               value="{{ old('periode', date('Y-m')) }}" 
                               placeholder="Contoh: 2025-01 atau Q1-2025"
                               required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: YYYY-MM atau Q1-YYYY untuk kuartal</small>
                    </div>
                    
                    <!-- Saldo Awal -->
                    <div class="mb-3">
                        <label for="saldo_awal" class="form-label">Saldo Awal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('saldo_awal') is-invalid @enderror" 
                               id="saldo_awal" 
                               name="saldo_awal" 
                               value="{{ old('saldo_awal', 0) }}" 
                               min="0"
                               step="1"
                               placeholder="Contoh: 10000000"
                               required>
                        @error('saldo_awal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Saldo awal periode ini (tidak boleh negatif)</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('saldo.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Buat Periode Baru
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
                <h6 class="fw-bold">Apa itu Set Saldo Awal?</h6>
                <p class="small">
                    Set Saldo Awal adalah proses memulai periode keuangan baru dengan menentukan
                    saldo awal untuk periode tersebut.
                </p>
                
                <h6 class="fw-bold mt-3">Dampak Membuat Periode Baru:</h6>
                <ul class="small">
                    <li>Periode aktif saat ini akan otomatis ditutup</li>
                    <li>Transaksi baru akan masuk ke periode yang baru</li>
                    <li>Saldo akan dihitung ulang dari saldo awal baru</li>
                </ul>
                
                <h6 class="fw-bold mt-3">Tips:</h6>
                <ul class="small mb-0">
                    <li>Gunakan format periode yang konsisten</li>
                    <li>Pastikan saldo awal sudah benar</li>
                    <li>Periode tidak boleh duplikat</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
