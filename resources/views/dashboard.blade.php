@extends('layouts.app')

@section('title', 'Dashboard - HMSI Finance')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h2 class="mb-2">Dashboard</h2>
    <p class="text-muted">Selamat datang{{ auth()->check() ? ', ' . auth()->user()->name : '' }}! Berikut ringkasan keuangan Koalisi HMSI.</p>
</div>

@auth
<!-- Statistics Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Pemasukan -->
    <div class="col-md-4">
        <div class="stats-card" style="background: #28a745;">
            <div class="stats-icon">
                <i class="fas fa-arrow-trend-up"></i>
            </div>
            <div class="stats-label">Total Pemasukan</div>
            <div class="stats-value">Rp {{ number_format($totalPemasukan ?? 0, 0, ',', '.') }}</div>
            <small class="opacity-75"><i class="fas fa-arrow-up me-1"></i>Pendapatan</small>
        </div>
    </div>
    
    <!-- Total Pengeluaran -->
    <div class="col-md-4">
        <div class="stats-card" style="background: #dc3545;">
            <div class="stats-icon">
                <i class="fas fa-arrow-trend-down"></i>
            </div>
            <div class="stats-label">Total Pengeluaran</div>
            <div class="stats-value">Rp {{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}</div>
            <small class="opacity-75"><i class="fas fa-arrow-down me-1"></i>Biaya</small>
        </div>
    </div>
    
    <!-- Saldo Akhir -->
    <div class="col-md-4">
        <div class="stats-card" style="background: #3ab2be;">
            <div class="stats-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stats-label">Saldo Akhir</div>
            <div class="stats-value">Rp {{ number_format($saldoAkhir ?? 0, 0, ',', '.') }}</div>
            <small class="opacity-75"><i class="fas fa-coins me-1"></i>Kas Tersedia</small>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-lg-8">
        <h5 class="mb-3">
            <i class="fas fa-history me-2"></i>Transaksi Terbaru
        </h5>
        
        <div class="card">
            @if(isset($recentTransactions) && count($recentTransactions) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="15%">Tanggal</th>
                            <th width="25%">Keterangan</th>
                            <th width="20%">Kategori</th>
                            <th width="15%">Jenis</th>
                            <th width="25%" class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $transaction)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d M Y') }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ $transaction->keterangan ? Str::limit($transaction->keterangan, 30) : '-' }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $transaction->category->nama_kategori ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $transaction->category->jenis == 'Pemasukan' ? 'bg-success' : 'bg-danger' }}">
                                    <i class="fas fa-{{ $transaction->category->jenis == 'Pemasukan' ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                    {{ $transaction->category->jenis }}
                                </span>
                            </td>
                            <td class="text-end">
                                <strong class="text-{{ $transaction->category->jenis == 'Pemasukan' ? 'success' : 'danger' }}">
                                    {{ $transaction->category->jenis == 'Pemasukan' ? '+' : '-' }} 
                                    Rp {{ number_format($transaction->nominal, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card-body border-top text-center">
                <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua Transaksi <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            @else
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Belum ada transaksi</p>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(in_array(auth()->user()->role_id, [1, 2]))
                        <a href="{{ url('/transaksi/create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Transaksi
                        </a>
                        <a href="{{ url('/laporan/create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-2"></i>Buat Laporan
                        </a>
                    @endif
                    <a href="{{ url('/transaksi') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>Lihat Transaksi
                    </a>
                    <a href="{{ url('/laporan') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar me-2"></i>Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-user-shield text-primary me-2"></i>
                        <strong>Role:</strong> {{ auth()->user()->role->nama_role }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong>Email:</strong> {{ auth()->user()->email }}
                    </li>
                    @if(auth()->user()->kabkota_nama)
                    <li class="mb-0">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <strong>Lokasi:</strong> {{ auth()->user()->kabkota_nama }}, {{ auth()->user()->provinsi_nama }}
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@else
<!-- Guest Welcome -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card text-center">
            <div class="card-body p-5">
                <i class="fas fa-coins fa-4x text-primary mb-4"></i>
                <h2 class="mb-3">Selamat Datang di HMSI Finance</h2>
                <p class="lead mb-4">
                    Sistem Pengelolaan Keuangan Kas Komunitas Koalisi HMSI
                </p>
                <div class="d-grid gap-2 d-md-block">
                    <a href="{{ url('/login') }}" class="btn btn-primary btn-lg me-md-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="{{ url('/register') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth

@endsection
