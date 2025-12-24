@extends('layouts.app')

@section('title', 'Tambah Transaksi - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('transaksi.index') }}">Transaksi</a></li>
                <li class="breadcrumb-item active">Tambah Transaksi</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Transaksi Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Transaksi
            </div>
            <div class="card-body">
                <form action="{{ route('transaksi.store') }}" method="POST">
                    @csrf
                    
                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('tanggal') is-invalid @enderror" 
                               id="tanggal" 
                               name="tanggal" 
                               value="{{ old('tanggal', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}"
                               required>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Tanggal tidak boleh di masa depan</small>
                    </div>
                    
                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" 
                                name="category_id" 
                                required>
                            <option value="">Pilih Kategori</option>
                            @php
                                $currentJenis = null;
                            @endphp
                            @foreach($categories as $category)
                                @if($currentJenis !== $category->jenis)
                                    @if($currentJenis !== null)
                                        </optgroup>
                                    @endif
                                    <optgroup label="{{ $category->jenis }}">
                                    @php $currentJenis = $category->jenis; @endphp
                                @endif
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->nama_kategori }}
                                </option>
                            @endforeach
                            @if($currentJenis !== null)
                                </optgroup>
                            @endif
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Nominal -->
                    <div class="mb-3">
                        <label for="nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('nominal') is-invalid @enderror" 
                               id="nominal" 
                               name="nominal" 
                               value="{{ old('nominal') }}" 
                               min="0"
                               step="1"
                               placeholder="Contoh: 50000"
                               required>
                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nominal tidak boleh negatif</small>
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                  id="keterangan" 
                                  name="keterangan" 
                                  rows="4"
                                  maxlength="500"
                                  placeholder="Deskripsi atau catatan untuk transaksi ini...">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Maksimal 500 karakter</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-md-4">
        <!-- Exchange Rate Card -->
        <div class="card bg-light" id="exchange-rate-card">
            <div class="card-header">
                <i class="fas fa-dollar-sign"></i> Kurs Hari Ini
            </div>
            <div class="card-body text-center">
                <div id="exchange-rate-loading">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted small mt-2">Memuat kurs...</p>
                </div>
                <div id="exchange-rate-content" class="d-none">
                    <h5 class="text-primary mb-2" id="rate-display">-</h5>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Informasi kurs untuk konversi donasi internasional
                    </small>
                    <hr>
                    <small class="text-muted">
                        Update: <span id="rate-date">-</span>
                    </small>
                </div>
                <div id="exchange-rate-error" class="d-none">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <p class="text-muted small mb-0">Gagal memuat kurs</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Exchange Rate API Integration
const EXCHANGE_API = window.location.origin + '/transaksi/api/exchange-rate';

async function loadExchangeRate() {
    const loadingDiv = document.getElementById('exchange-rate-loading');
    const contentDiv = document.getElementById('exchange-rate-content');
    const errorDiv = document.getElementById('exchange-rate-error');
    
    try {
        const response = await fetch(EXCHANGE_API);
        const result = await response.json();
        
        if (result.status === 200 && result.data) {
            // Show exchange rate
            document.getElementById('rate-display').textContent = result.data.formatted;
            document.getElementById('rate-date').textContent = result.data.date;
            
            loadingDiv.classList.add('d-none');
            contentDiv.classList.remove('d-none');
        } else {
            throw new Error('Failed to load exchange rate');
        }
    } catch (error) {
        console.error('Exchange rate error:', error);
        loadingDiv.classList.add('d-none');
        errorDiv.classList.remove('d-none');
    }
}

// Load exchange rate on page load
document.addEventListener('DOMContentLoaded', function() {
    loadExchangeRate();
});

// Format nominal input with thousand separator
document.getElementById('nominal').addEventListener('blur', function() {
    const value = this.value.replace(/\D/g, '');
    if (value) {
        this.value = value;
    }
});
</script>
@endsection
