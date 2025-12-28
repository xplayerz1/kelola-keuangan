@extends('layouts.app')

@section('title', 'Dashboard Saldo - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="fas fa-wallet"></i> Dashboard Saldo</h2>
        <p class="text-muted">Monitor saldo dan histori perubahan secara real-time</p>
    </div>
    <div class="col-md-6 text-end">
        @if(in_array(auth()->user()->role_id, [1, 2]))
            @if(!$activeSaldo)
            <a href="{{ route('saldo.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Set Saldo Awal
            </a>
            @else
            <form action="{{ route('saldo.recalculate') }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Sync ulang data saldo dari transaksi yang ada?')">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Sync Data
                </button>
            </form>
            @endif
        @endif
    </div>
</div>

@if($activeSaldo)
<!-- Summary Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card text-center border-primary">
            <div class="card-body">
                <h6 class="text-muted mb-2">Saldo Awal</h6>
                <h4 class="text-primary mb-0">Rp {{ number_format($activeSaldo->saldo_awal, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pemasukan</h6>
                <h4 class="text-success mb-0">Rp {{ number_format($activeSaldo->total_masuk, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pengeluaran</h6>
                <h4 class="text-danger mb-0">Rp {{ number_format($activeSaldo->total_keluar, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-{{ $activeSaldo->saldo_akhir >= 0 ? 'info' : 'warning' }}">
            <div class="card-body">
                <h6 class="text-muted mb-2">Saldo Akhir</h6>
                <h4 class="text-{{ $activeSaldo->saldo_akhir >= 0 ? 'info' : 'warning' }} mb-0">
                    Rp {{ number_format($activeSaldo->saldo_akhir, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="fas fa-info-circle"></i> Informasi Periode Aktif</h6>
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Periode:</strong></td>
                        <td>{{ $activeSaldo->periode }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Transaksi:</strong></td>
                        <td>{{ $totalTransaksi }} transaksi</td>
                    </tr>
                    <tr>
                        <td><strong>Persentase Penggunaan:</strong></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $persentasePenggunaan > 80 ? 'bg-danger' : ($persentasePenggunaan > 50 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ min($persentasePenggunaan, 100) }}%;">
                                    {{ number_format($persentasePenggunaan, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $activeSaldo->status === 'aktif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($activeSaldo->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="fas fa-chart-line"></i> Tren Saldo</h6>
                <div style="position: relative; height: 200px;">
                    <canvas id="saldoTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Periods List -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Semua Periode Saldo</h5>
    @if(in_array(auth()->user()->role_id, [1, 2]))
    <a href="{{ route('saldo.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus"></i> Buat Periode Baru
    </a>
    @endif
</div>

<div class="card mb-4">
    @if($saldoPeriods->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="20%">Periode</th>
                    <th width="15%">Saldo Awal</th>
                    <th width="15%">Total Masuk</th>
                    <th width="15%">Total Keluar</th>
                    <th width="15%">Saldo Akhir</th>
                    <th width="10%">Status</th>
                    <th width="15%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saldoPeriods as $index => $periode)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $periode->periode }}</strong></td>
                    <td>Rp {{ number_format($periode->saldo_awal, 0, ',', '.') }}</td>
                    <td class="text-success">Rp {{ number_format($periode->total_masuk, 0, ',', '.') }}</td>
                    <td class="text-danger">Rp {{ number_format($periode->total_keluar, 0, ',', '.') }}</td>
                    <td class="fw-bold text-primary">Rp {{ number_format($periode->saldo_akhir, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge bg-{{ $periode->status === 'aktif' ? 'success' : 'secondary' }}">
                            {{ ucfirst($periode->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('saldo.show', $periode->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           title="Detail">
                            <i class="fas fa-eye text-info"></i>
                        </a>
                        @if(in_array(auth()->user()->role_id, [1, 2]))
                            <a href="{{ route('saldo.edit', $periode->id) }}" 
                               class="btn btn-link btn-sm p-1" 
                               title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                            @if($periode->status === 'aktif')
                                <form action="{{ route('saldo.destroy', $periode->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menutup periode {{ $periode->periode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-link btn-sm p-1" 
                                            title="Tutup Periode">
                                        <i class="fas fa-lock text-warning"></i>
                                    </button>
                                </form>
                            @endif
                            {{-- Delete Permanent --}}
                            <form action="{{ route('saldo.destroy', ['id' => $periode->id, 'permanent' => 'true']) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('HAPUS PERMANEN periode {{ $periode->periode }}? Hanya bisa jika tidak ada transaksi!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-link btn-sm p-1" 
                                        title="Hapus Permanen">
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
    <div class="card-body text-center py-4">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-3">Belum ada periode saldo</p>
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('saldo.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Periode Pertama
        </a>
        @endif
    </div>
    @endif
</div>

<!-- Histori Terbaru -->
<h5 class="mb-3"><i class="fas fa-history"></i> Histori Perubahan Saldo (20 Terbaru)</h5>

<div class="card">
    @if($recentHistori->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="15%">Waktu</th>
                    <th width="20%">Transaksi</th>
                    <th width="15%">Nominal</th>
                    <th width="15%">Saldo Sebelum</th>
                    <th width="15%">Saldo Sesudah</th>
                    <th width="20%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentHistori as $histori)
                <tr>
                    <td><small class="text-muted">{{ $histori->created_at->format('d/m H:i') }}</small></td>
                    <td>
                        @if($histori->transaction)
                            <small class="text-muted">{{ $histori->transaction->category->nama_kategori }}</small>
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
        <p class="text-muted">Belum ada histori perubahan saldo</p>
    </div>
    @endif
</div>

@else
<!-- No Active Saldo -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-wallet fa-4x text-muted mb-3"></i>
        <h5>Belum Ada Periode Saldo Aktif</h5>
        <p class="text-muted">Buat periode saldo baru untuk mulai tracking keuangan</p>
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('saldo.create') }}" class="btn btn-primary btn-lg mt-3">
            <i class="fas fa-plus"></i> Set Saldo Awal Sekarang
        </a>
        @endif
    </div>
</div>
@endif

@endsection

@section('scripts')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Chart.js for Saldo Trend
const ctx = document.getElementById('saldoTrendChart');
if (ctx) {
    // Fetch chart data from API
    fetch('{{ route("saldo.api.chart") }}')
        .then(response => response.json())
        .then(result => {
            if (result.status === 200) {
                const data = result.data;
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Saldo',
                            data: data.saldo,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Chart error:', error));
}
</script>
@endsection
