@extends('layouts.app')

@section('title', 'Edit Periode Saldo - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('saldo.index') }}">Saldo</a></li>
                <li class="breadcrumb-item active">Edit Periode</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Periode Saldo</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Edit Periode
            </div>
            <div class="card-body">
                @if($historiCount > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Periode ini sudah memiliki {{ $historiCount }} transaksi.
                    Mengubah saldo awal akan mempengaruhi saldo akhir.
                </div>
                @endif
                
                <form action="{{ route('saldo.update', $saldo->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Periode -->
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('periode') is-invalid @enderror" 
                               id="periode" 
                               name="periode" 
                               value="{{ old('periode', $saldo->periode) }}" 
                               required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Saldo Awal -->
                    <div class="mb-3">
                        <label for="saldo_awal" class="form-label">Saldo Awal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('saldo_awal') is-invalid @enderror" 
                               id="saldo_awal" 
                               name="saldo_awal" 
                               value="{{ old('saldo_awal', $saldo->saldo_awal) }}" 
                               min="0"
                               step="1"
                               required>
                        @error('saldo_awal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Saldo awal saat ini: Rp {{ number_format($saldo->saldo_awal, 0, ',', '.') }}</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('saldo.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Periode
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
                <i class="fas fa-info-circle"></i> Informasi Periode
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $saldo->status === 'aktif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($saldo->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Saldo Awal:</strong></td>
                        <td>Rp {{ number_format($saldo->saldo_awal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Masuk:</strong></td>
                        <td>Rp {{ number_format($saldo->total_masuk, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Keluar:</strong></td>
                        <td>Rp {{ number_format($saldo->total_keluar, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Saldo Akhir:</strong></td>
                        <td class="fw-bold">Rp {{ number_format($saldo->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Transaksi:</strong></td>
                        <td>{{ $historiCount }} transaksi</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mt-3 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Catatan:</h6>
                <ul class="small mb-0">
                    <li>Edit periode hanya ubah nama dan saldo awal</li>
                    <li>Perubahan saldo awal akan tercatat di histori</li>
                    <li>Saldo akhir otomatis disesuaikan</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
