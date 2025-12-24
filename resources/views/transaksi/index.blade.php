@extends('layouts.app')

@section('title', 'Transaksi Keuangan - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="fas fa-exchange-alt"></i> Transaksi Keuangan</h2>
        <p class="text-muted">Kelola transaksi pemasukan dan pengeluaran</p>
    </div>
    <div class="col-md-6 text-end">
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Transaksi
        </a>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pemasukan</h6>
                <h4 class="text-success mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pengeluaran</h6>
                <h4 class="text-danger mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-{{ $saldo >= 0 ? 'primary' : 'warning' }}">
            <div class="card-body">
                <h6 class="text-muted mb-2">Saldo</h6>
                <h4 class="text-{{ $saldo >= 0 ? 'primary' : 'warning' }} mb-0">
                    Rp {{ number_format($saldo, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('transaksi.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-select" id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('kategori') == $category->id ? 'selected' : '' }}>
                        {{ $category->nama_kategori}}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="jenis" class="form-label">Jenis</label>
                <select class="form-select" id="jenis" name="jenis">
                    <option value="">Semua Jenis</option>
                    <option value="Pemasukan" {{ request('jenis') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ request('jenis') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="bulan" class="form-label">Bulan</label>
                <input type="month" class="form-control" id="bulan" name="bulan" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<h5 class="mb-3"><i class="fas fa-list"></i> Daftar Transaksi</h5>

<div class="card">
    @if($transactions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="10%">Tanggal</th>
                    <th width="20%">Kategori</th>
                    <th width="10%">Jenis</th>
                    <th width="15%">Nominal</th>
                    <th width="20%">Keterangan</th>
                    <th width="10%">User</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $transaksi)
                <tr>
                    <td>{{ $transactions->firstItem() + $index }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d M Y') }}</td>
                    <td>{{ $transaksi->category->nama_kategori }}</td>
                    <td>
                        <span class="badge {{ $transaksi->category->jenis === 'Pemasukan' ? 'bg-success' : 'bg-danger' }}">
                            {{ $transaksi->category->jenis }}
                        </span>
                    </td>
                    <td class="fw-bold {{ $transaksi->category->jenis === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                    </td>
                    <td>
                        <small>{{ $transaksi->keterangan ? Str::limit($transaksi->keterangan, 30) : '-' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $transaksi->user->name }}</small>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('transaksi.show', $transaksi->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           title="Detail">
                            <i class="fas fa-eye text-info"></i>
                        </a>
                        @if(in_array(auth()->user()->role_id, [1, 2]))
                            <a href="{{ route('transaksi.edit', $transaksi->id) }}" 
                               class="btn btn-link btn-sm p-1" 
                               title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                            <form action="{{ route('transaksi.destroy', $transaksi->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-link btn-sm p-1" 
                                        title="Hapus">
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
    
    <div class="card-body border-top">
        {{ $transactions->links() }}
    </div>
    @else
    <div class="card-body text-center py-5">
        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-3">Belum ada transaksi.</p>
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Transaksi Pertama
        </a>
        @endif
    </div>
    @endif
</div>
@endsection
