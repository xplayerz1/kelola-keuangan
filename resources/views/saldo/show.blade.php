@extends('layouts.app')

@section('title', 'Detail Periode Saldo - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('saldo.index') }}">Saldo</a></li>
                <li class="breadcrumb-item active">Detail Periode</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-wallet"></i> Detail Periode: {{ $saldo->periode }}</h2>
    </div>
    <div class="col-md-6 text-end">
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('saldo.edit', $saldo->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        @if($saldo->status === 'aktif')
        <form action="{{ route('saldo.destroy', $saldo->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Yakin ingin menutup periode ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-lock"></i> Tutup Periode
            </button>
        </form>
        @endif
        @endif
        <a href="{{ route('saldo.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Summary -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted mb-2">Status</h6>
                <h4 class="mb-0">
                    <span class="badge bg-{{ $saldo->status === 'aktif' ? 'success' : 'secondary' }}">
                        {{ ucfirst($saldo->status) }}
                    </span>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-primary">
            <div class="card-body">
                <h6 class="text-muted mb-2">Saldo Awal</h6>
                <h4 class="text-primary mb-0">Rp {{ number_format($saldo->saldo_awal, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pemasukan</h6>
                <h4 class="text-success mb-0">Rp {{ number_format($saldo->total_masuk, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pengeluaran</h6>
                <h4 class="text-danger mb-0">Rp {{ number_format($saldo->total_keluar, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Saldo Akhir Periode</h6>
                <h2 class="text-{{ $saldo->saldo_akhir >= 0 ? 'info' : 'danger' }} mb-0">
                    Rp {{ number_format($saldo->saldo_akhir, 0, ',', '.') }}
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Histori Detail -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i> Histori Perubahan Saldo - Periode {{ $saldo->periode }}
    </div>
    <div class="card-body">
        @if($saldo->historiSaldo->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">Waktu</th>
                        <th width="15%">Transaksi ID</th>
                        <th width="15%">Kategori</th>
                        <th width="12%">Nominal</th>
                        <th width="12%">Saldo Sebelum</th>
                        <th width="12%">Saldo Sesudah</th>
                        <th width="17%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saldo->historiSaldo as $index => $histori)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><small>{{ $histori->created_at->format('d/m/Y H:i') }}</small></td>
                        <td>
                            @if($histori->transaction)
                                <a href="{{ route('transaksi.show', $histori->transaction->id) }}" 
                                   class="btn btn-sm btn-outline-info">
                                    #{{ $histori->transaction->id }}
                                </a>
                            @else
                                <small class="text-muted">System</small>
                            @endif
                        </td>
                        <td>
                            @if($histori->transaction)
                                <small>{{ $histori->transaction->category->nama_kategori }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td class="fw-bold">Rp {{ number_format($histori->nominal, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($histori->saldo_sebelum, 0, ',', '.') }}</td>
                        <td class="fw-bold text-primary">Rp {{ number_format($histori->saldo_sesudah, 0, ',', '.') }}</td>
                        <td><small>{{ $histori->keterangan }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada histori untuk periode ini</p>
        </div>
        @endif
    </div>
</div>

@endsection
