@extends('layouts.app')

@section('title', 'Detail Laporan - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="fas fa-file-alt"></i> {{ $laporan->judul }}</h2>
        <p class="text-muted">Periode: {{ $laporan->start_date->format('d M Y') }} - {{ $laporan->end_date->format('d M Y') }}</p>
    </div>
    <div class="col-md-6 text-end">
        <div>
            <a href="{{ route('laporan.pdf', $laporan->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            
            @if(in_array(auth()->user()->role_id, [1, 2]))
            <a href="{{ route('laporan.edit', $laporan->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            
            @if(in_array(auth()->user()->role_id, [1, 2]))
            <form action="{{ route('laporan.destroy', $laporan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
            @endif
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pemasukan</h6>
                <h4 class="text-success mb-0">Rp {{ number_format($laporan->total_pemasukan, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pengeluaran</h6>
                <h4 class="text-danger mb-0">Rp {{ number_format($laporan->total_pengeluaran, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-{{ $laporan->selisih >= 0 ? 'primary' : 'warning' }}">
            <div class="card-body">
                <h6 class="text-muted mb-2">Selisih ({{ $laporan->selisih_label }})</h6>
                <h4 class="text-{{ $laporan->selisih >= 0 ? 'primary' : 'warning' }} mb-0">
                    Rp {{ number_format($laporan->selisih, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted mb-2">Status</h6>
                <h4 class="mb-0">
                    <span class="badge bg-{{ $laporan->status_badge }}">{{ ucfirst($laporan->status) }}</span>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Timestamp Info (World Time API) -->
@if($laporan->keterangan_libur)
<div class="alert alert-info">
    <h5 class="alert-heading"><i class="fas fa-clock"></i> Timestamp Laporan</h5>
    <p class="mb-2">Laporan ini dibuat pada:</p>
    <div class="row">
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>DateTime:</strong> 
                    @php
                        $datetime = $laporan->keterangan_libur['datetime'] ?? now();
                        $carbonDate = \Carbon\Carbon::parse($datetime)->timezone('Asia/Jakarta');
                    @endphp
                    {{ $carbonDate->format('d M Y, H:i:s') }}
                </li>
                <li><strong>Timezone:</strong> {{ $laporan->keterangan_libur['timezone'] ?? 'Asia/Jakarta' }} ({{ $laporan->keterangan_libur['abbreviation'] ?? 'WIB' }})</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="mb-0">
                @if(isset($laporan->keterangan_libur['day_of_week']))
                <li><strong>Hari ke:</strong> {{ $laporan->keterangan_libur['day_of_week'] }} (dalam seminggu)</li>
                @endif
                @if(isset($laporan->keterangan_libur['day_of_year']))
                <li><strong>Hari ke:</strong> {{ $laporan->keterangan_libur['day_of_year'] }} (dalam setahun)</li>
                @endif
                @if(isset($laporan->keterangan_libur['week_number']))
                <li><strong>Minggu ke:</strong> {{ $laporan->keterangan_libur['week_number'] }}</li>
                @endif
                <li><strong>Source:</strong> 
                    @if(isset($laporan->keterangan_libur['method']) && $laporan->keterangan_libur['method'] === 'worldtime_api')
                        <span class="badge bg-success">World Time API</span>
                    @else
                        <span class="badge bg-warning">Server Time</span>
                    @endif
                </li>
            </ul>
        </div>
    </div>
    <small class="text-muted mt-2 d-block"><i class="fas fa-globe"></i> Timestamp diambil menggunakan World Time API untuk akurasi timezone.</small>
</div>
@endif

<!-- Category Breakdown (RELASI 2 TABEL) -->
<h5 class="mb-3"><i class="fas fa-chart-pie"></i> Ringkasan per Kategori</h5>

<div class="card mb-4">
    @if($categoryBreakdown->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th class="text-center">Jumlah Transaksi</th>
                    <th class="text-end">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryBreakdown as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $category->nama_kategori }}</strong></td>
                    <td>
                        <span class="badge bg-{{ $category->jenis === 'Pemasukan' ? 'success' : 'danger' }}">
                            {{ $category->jenis }}
                        </span>
                    </td>
                    <td class="text-center">{{ $category->jumlah_transaksi }}</td>
                    <td class="text-end fw-bold text-{{ $category->jenis === 'Pemasukan' ? 'success' : 'danger' }}">
                        Rp {{ number_format($category->total_nominal, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
        <p class="text-muted">Tidak ada transaksi dalam periode ini</p>
    </div>
    @endif
</div>

<!-- Detail Transactions -->
<h5 class="mb-3"><i class="fas fa-list"></i> Detail Transaksi ({{ $transactions->count() }} transaksi)</h5>

<div class="card">
    @if($transactions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $t)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $t->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $t->category->nama_kategori }}</td>
                    <td>
                        <span class="badge bg-{{ $t->category->jenis === 'Pemasukan' ? 'success' : 'danger' }}">
                            {{ $t->category->jenis }}
                        </span>
                    </td>
                    <td class="fw-bold text-{{ $t->category->jenis === 'Pemasukan' ? 'success' : 'danger' }}">
                        Rp {{ number_format($t->nominal, 0, ',', '.') }}
                    </td>
                    <td><small>{{ $t->keterangan ?? '-' }}</small></td>
                    <td><small>{{ $t->user->name }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-list fa-3x text-muted mb-3"></i>
        <p class="text-muted">Tidak ada transaksi dalam periode ini</p>
    </div>
    @endif
</div>

<!-- Metadata -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <small><strong>Dibuat oleh:</strong> {{ $laporan->user->name }}</small>
            </div>
            <div class="col-md-6 text-end">
                <small><strong>Dibuat pada:</strong> {{ $laporan->created_at->format('d M Y H:i') }}</small>
            </div>
        </div>
        @if($laporan->catatan)
        <hr>
        <small><strong>Catatan:</strong> {{ $laporan->catatan }}</small>
        @endif
    </div>
</div>

@endsection
