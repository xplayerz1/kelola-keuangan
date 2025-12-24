@extends('layouts.app')

@section('title', 'Laporan Keuangan - HMSI Finance')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="fas fa-file-alt"></i> Laporan Keuangan</h2>
        <p class="text-muted">Laporan periodik aktivitas kas komunitas</p>
    </div>
    <div class="col-md-6 text-end">
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('laporan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Generate Laporan Baru
        </a>
        @endif
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('laporan.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reports Table -->
<h5 class="mb-3"><i class="fas fa-list"></i> Daftar Laporan</h5>

<div class="card">
    @if($laporan->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Judul Laporan</th>
                    <th width="15%">Periode</th>
                    <th width="15%">Jenis</th>
                    <th width="15%">Total Nominal</th>
                    <th width="20%">Keterangan</th>
                    <th width="15%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $item)
                <tr>
                    <td>{{ $laporan->firstItem() + $index }}</td>
                    <td>
                        <strong>{{ $item->judul }}</strong>
                    </td>
                    <td>{{ $item->start_date->format('d M Y') }} - {{ $item->end_date->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $item->status_badge }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="fw-bold">
                        <div class="text-success">Rp {{ number_format($item->total_pemasukan, 0, ',', '.') }}</div>
                        <div class="text-danger">Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <small class="text-{{ $item->selisih >= 0 ? 'primary' : 'warning' }}">
                            Selisih: Rp {{ number_format($item->selisih, 0, ',', '.') }}
                        </small>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('laporan.show', $item->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           title="Lihat Detail">
                            <i class="fas fa-eye text-info"></i>
                        </a>
                        <a href="{{ route('laporan.pdf', $item->id) }}" 
                           class="btn btn-link btn-sm p-1" 
                           target="_blank" 
                           title="Export PDF">
                            <i class="fas fa-file-pdf text-danger"></i>
                        </a>
                        @if(in_array(auth()->user()->role_id, [1, 2]))
                            <a href="{{ route('laporan.edit', $item->id) }}" 
                               class="btn btn-link btn-sm p-1" 
                               title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                            <form action="{{ route('laporan.destroy', $item->id) }}" 
                                  method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
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
        {{ $laporan->links() }}
    </div>
    @else
    <div class="card-body text-center py-5">
        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-3">Belum ada laporan</p>
        @if(in_array(auth()->user()->role_id, [1, 2]))
        <a href="{{ route('laporan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Laporan Pertama
        </a>
        @endif
    </div>
    @endif
</div>
@endsection
