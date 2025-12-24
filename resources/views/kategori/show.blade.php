@extends('layouts.app')

@section('title', 'Detail Kategori - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></li>
                <li class="breadcrumb-item active">{{ $category->nama_kategori }}</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-tag"></i> {{ $category->nama_kategori }}</h2>
        <span class="badge {{ $category->jenis === 'Pemasukan' ? 'bg-success' : 'bg-danger' }} fs-6">
            {{ $category->jenis }}
        </span>
    </div>
    <div class="col-md-6 text-end">
        @if(auth()->user()->role->nama_role !== 'Viewer')
        <a href="{{ route('kategori.edit', $category->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        @endif
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Detail Kategori -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Informasi Kategori
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="40%">Nama Kategori:</th>
                        <td><strong>{{ $category->nama_kategori }}</strong></td>
                    </tr>
                    <tr>
                        <th>Jenis:</th>
                        <td>
                            <span class="badge {{ $category->jenis === 'Pemasukan' ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas fa-{{ $category->jenis === 'Pemasukan' ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ $category->jenis }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Keterangan:</th>
                        <td>{{ $category->keterangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat:</th>
                        <td>{{ $category->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Diupdate:</th>
                        <td>{{ $category->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Statistik
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="text-primary">{{ $totalTransaksi }}</h3>
                        <p class="text-muted mb-0">Total Transaksi</p>
                    </div>
                    <div class="col-6">
                        <h3 class="text-success">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0">Total Nominal</p>
                    </div>
                </div>
            </div>
        </div>
        
        @if($totalTransaksi === 0)
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            Belum ada transaksi untuk kategori ini.
        </div>
        @endif
    </div>
</div>

<!-- Recent Transactions -->
@if($category->transactions->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> 10 Transaksi Terakhir
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Nominal</th>
                                <th>Input By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->transactions as $transaction)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transaction->tanggal)->format('d M Y') }}</td>
                                <td>{{ Str::limit($transaction->keterangan, 50) }}</td>
                                <td class="fw-bold {{ $category->jenis === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($transaction->nominal, 0, ',', '.') }}
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $transaction->user->name ?? 'N/A' }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($totalTransaksi > 10)
                <div class="text-center mt-3">
                    <small class="text-muted">Menampilkan 10 dari {{ $totalTransaksi }} transaksi</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@endsection
