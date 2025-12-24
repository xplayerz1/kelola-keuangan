@extends('layouts.app')

@section('title', 'Tambah Kategori - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></li>
                <li class="breadcrumb-item active">Tambah Kategori</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Kategori Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Kategori
            </div>
            <div class="card-body">
                <form action="{{ route('kategori.store') }}" method="POST">
                    @csrf
                    
                    <!-- Nama Kategori with KBBI Suggestion -->
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">
                            Nama Kategori <span class="text-danger">*</span>
                            <small class="text-muted">(min. 3 karakter untuk cek KBBI)</small>
                        </label>
                        <input type="text" 
                               class="form-control @error('nama_kategori') is-invalid @enderror" 
                               id="nama_kategori" 
                               name="nama_kategori" 
                               value="{{ old('nama_kategori') }}" 
                               required
                               autocomplete="off"
                               placeholder="Contoh: Donasi, Konsumsi, Transport">
                        @error('nama_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- KBBI Suggestions -->
                        <div id="kbbi-suggestions" class="mt-2 d-none">
                            <div class="alert alert-info small">
                                <strong><i class="fas fa-book"></i> Saran dari KBBI:</strong>
                                <div id="kbbi-content"></div>
                            </div>
                        </div>
                        
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Sistem akan mencari definisi dari KBBI untuk membantu menjelaskan kategori
                        </small>
                    </div>
                    
                    <!-- Jenis Kategori -->
                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select @error('jenis') is-invalid @enderror" 
                                id="jenis" 
                                name="jenis" 
                                required>
                            <option value="">Pilih Jenis</option>
                            <option value="Pemasukan" {{ old('jenis') == 'Pemasukan' ? 'selected' : '' }}>
                                Pemasukan
                            </option>
                            <option value="Pengeluaran" {{ old('jenis') == 'Pengeluaran' ? 'selected' : '' }}>
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
                                  maxlength="500"
                                  placeholder="Deskripsi atau catatan untuk kategori ini...">{{ old('keterangan') }}</textarea>
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
                            <i class="fas fa-save"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// KBBI API Integration for Category Name Suggestion
const API_BASE = window.location.origin + '/kategori/api/kbbi';
let typingTimer;
const typingDelay = 800; // Wait 800ms after user stops typing

document.getElementById('nama_kategori').addEventListener('input', function() {
    clearTimeout(typingTimer);
    const keyword = this.value.trim();
    
    // Hide suggestions if less than 3 characters
    if (keyword.length < 3) {
        document.getElementById('kbbi-suggestions').classList.add('d-none');
        return;
    }
    
    // Set timer to search KBBI
    typingTimer = setTimeout(() => searchKBBI(keyword), typingDelay);
});

async function searchKBBI(keyword) {
    const suggestionsDiv = document.getElementById('kbbi-suggestions');
    const contentDiv = document.getElementById('kbbi-content');
    
    try {
        // Show loading
        suggestionsDiv.classList.remove('d-none');
        contentDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari definisi di KBBI...';
        
        const response = await fetch(`${API_BASE}?q=${encodeURIComponent(keyword)}`);
        const data = await response.json();
        
        if (data.status === 200 && data.data && data.data.length > 0) {
            // Show first suggestion
            const suggestion = data.data[0];
            contentDiv.innerHTML = `
                <strong>${suggestion.kata}</strong>: ${suggestion.arti}
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="useSuggestion(\`${suggestion.arti.replace(/`/g, '\\`')}\`)">
                    <i class="fas fa-copy"></i> Gunakan sebagai keterangan
                </button>
            `;
        } else if (data.status === 404) {
            contentDiv.innerHTML = '<i class="fas fa-info-circle"></i> Kata tidak ditemukan di KBBI';
        } else {
            suggestionsDiv.classList.add('d-none');
        }
    } catch (error) {
        console.error('Error fetching KBBI:', error);
        contentDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mengambil data KBBI';
    }
}

function useSuggestion(text) {
    const keteranganField = document.getElementById('keterangan');
    keteranganField.value = text;
    keteranganField.focus();
    
    // Show success feedback
    const suggestionsDiv = document.getElementById('kbbi-suggestions');
    suggestionsDiv.classList.remove('alert-info');
    suggestionsDiv.classList.add('alert-success');
    setTimeout(() => {
        suggestionsDiv.classList.remove('alert-success');
        suggestionsDiv.classList.add('alert-info');
    }, 1000);
}
</script>
@endsection
