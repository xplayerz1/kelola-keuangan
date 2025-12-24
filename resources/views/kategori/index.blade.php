@extends('layouts.app')

@section('title', 'Kategori Keuangan - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="fas fa-tags"></i> Kategori Keuangan</h2>
        <p class="text-muted">Kelola kategori pemasukan dan pengeluaran</p>
    </div>
    <div class="col-md-6 text-end">
        @if(auth()->user()->role->nama_role !== 'Viewer')
        <a href="{{ route('kategori.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <h3 class="text-success mb-0">{{ $totalPemasukan }}</h3>
                <p class="text-muted mb-0">Kategori Pemasukan</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h3 class="text-danger mb-0">{{ $totalPengeluaran }}</h3>
                <p class="text-muted mb-0">Kategori Pengeluaran</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <h3 class="text-primary mb-0">{{ $categories->count() }}</h3>
                <p class="text-muted mb-0">Total Kategori</p>
            </div>
        </div>
    </div>
</div>

<!-- Kategori Table -->
<h5 class="mb-3"><i class="fas fa-list"></i> Daftar Kategori</h5>

<div class="card">
    @if($categories->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Nama Kategori</th>
                    <th width="15%">Jenis</th>
                    <th width="30%">Keterangan</th>
                    <th width="10%">Transaksi</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $category->nama_kategori }}</strong>
                    </td>
                    <td>
                        <span class="badge 
                            @if($category->jenis === 'Pemasukan') bg-success
                            @else bg-danger
                            @endif">
                            <i class="fas fa-{{ $category->jenis === 'Pemasukan' ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ $category->jenis }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $category->keterangan ? Str::limit($category->keterangan, 50) : '-' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $category->transactions_count }} transaksi</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('kategori.show', $category->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           title="Lihat Detail">
                            <i class="fas fa-eye text-info"></i>
                        </a>
                        
                        @if(auth()->user()->role->nama_role !== 'Viewer')
                            <a href="{{ route('kategori.edit', $category->id) }}" 
                               class="btn btn-link btn-sm p-1" 
                               title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                            
                            <form action="{{ route('kategori.destroy', $category->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->nama_kategori }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-link btn-sm p-1" 
                                        title="Hapus"
                                        {{ $category->transactions_count > 0 ? 'disabled' : '' }}>
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
    <div class="card-body text-center py-5">
        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-3">Belum ada kategori. Tambahkan kategori pertama Anda!</p>
        @if(auth()->user()->role->nama_role !== 'Viewer')
        <a href="{{ route('kategori.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
        @endif
    </div>
    @endif
</div>

@endsection
