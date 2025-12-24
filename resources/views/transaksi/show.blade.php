@extends('layouts.app')

@section('title', 'Detail Transaksi - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('transaksi.index') }}">Transaksi</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-file-invoice"></i> Detail Transaksi</h2>
    </div>
    <div class="col-md-6 text-end">
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('transaksi.edit', $transaction->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        @endif
        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Transaction Details -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Informasi Transaksi
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="40%">Tanggal:</th>
                        <td>{{ $transaction->tanggal->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Kategori:</th>
                        <td>
                            <strong>{{ $transaction->category->nama_kategori }}</strong>
                            <br>
                            <span class="badge {{ $transaction->category->jenis === 'Pemasukan' ? 'bg-success' : 'bg-danger' }}">
                                {{ $transaction->category->jenis }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Nominal:</th>
                        <td>
                            <h4 class="mb-0 {{ $transaction->category->jenis === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($transaction->nominal, 0, ',', '.') }}
                            </h4>
                        </td>
                    </tr>
                    <tr>
                        <th>Keterangan:</th>
                        <td>{{ $transaction->keterangan ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- User & Category Info -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-user"></i> Relasi: User yang Input
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Nama User:</th>
                        <td>{{ $transaction->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $transaction->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td>
                            <span class="badge bg-primary">
                                {{ $transaction->user->role->nama_role }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tag"></i> Relasi: Kategori Detail
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Nama Kategori:</th>
                        <td>{{ $transaction->category->nama_kategori }}</td>
                    </tr>
                    <tr>
                        <th>Jenis:</th>
                        <td>{{ $transaction->category->jenis }}</td>
                    </tr>
                    <tr>
                        <th>Keterangan:</th>
                        <td>{{ $transaction->category->keterangan ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Metadata -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock"></i> Metadata
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">
                            <strong>Dibuat:</strong> {{ $transaction->created_at->format('d M Y, H:i:s') }}
                            <small class="text-muted">({{ $transaction->created_at->diffForHumans() }})</small>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-0">
                            <strong>Terakhir Diupdate:</strong> {{ $transaction->updated_at->format('d M Y, H:i:s') }}
                            <small class="text-muted">({{ $transaction->updated_at->diffForHumans() }})</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
