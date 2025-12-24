<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $laporan->judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .summary-box h3 {
            margin-top: 0;
            font-size: 14px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #ddd;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #333;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #007bff; }
        .text-warning { color: #ffc107; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KEUANGAN</h1>
        <h2>{{ $laporan->judul }}</h2>
        <p>Periode: {{ $laporan->start_date->format('d F Y') }} - {{ $laporan->end_date->format('d F Y') }}</p>
        <p>Dibuat oleh: {{ $laporan->user->name }} | {{ $laporan->created_at->format('d F Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <h3>Ringkasan Keuangan</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Pemasukan</div>
                <div class="summary-value text-success">Rp {{ number_format($laporan->total_pemasukan, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Pengeluaran</div>
                <div class="summary-value text-danger">Rp {{ number_format($laporan->total_pengeluaran, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Selisih ({{ $laporan->selisih_label }})</div>
                <div class="summary-value {{ $laporan->selisih >= 0 ? 'text-primary' : 'text-warning' }}">
                    Rp {{ number_format($laporan->selisih, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Timestamp Info (World Time API) -->
    @if($laporan->keterangan_libur && isset($laporan->keterangan_libur['datetime']))
    <div class="alert" style="background: #d1ecf1; border-color: #bee5eb;">
        <strong>Timestamp Laporan</strong><br>
        Dibuat pada: 
        @php
            $datetime = $laporan->keterangan_libur['datetime'];
            $carbonDate = \Carbon\Carbon::parse($datetime)->timezone('Asia/Jakarta');
        @endphp
        {{ $carbonDate->format('d F Y, H:i:s') }} WIB
        <br>
        <small>Timezone: {{ $laporan->keterangan_libur['timezone'] ?? 'Asia/Jakarta' }} | 
        Source: {{ $laporan->keterangan_libur['method'] === 'worldtime_api' ? 'World Time API' : 'Server Time' }}
        @if(isset($laporan->keterangan_libur['week_number']))
        | Minggu ke-{{ $laporan->keterangan_libur['week_number'] }}
        @endif
        </small>
    </div>
    @endif

    <!-- Category Breakdown -->
    <h3 style="margin-top: 25px;">Ringkasan per Kategori</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="35%">Kategori</th>
                <th width="20%">Jenis</th>
                <th width="20%" class="text-center">Jumlah Transaksi</th>
                <th width="20%" class="text-end">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryBreakdown as $index => $category)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $category->nama_kategori }}</td>
                <td>{{ $category->jenis }}</td>
                <td class="text-center">{{ $category->jumlah_transaksi }}</td>
                <td class="text-end fw-bold {{ $category->jenis === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($category->total_nominal, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detail Transactions -->
    <h3 style="margin-top: 25px;">Detail Transaksi ({{ $transactions->count() }} transaksi)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="12%">Tanggal</th>
                <th width="20%">Kategori</th>
                <th width="12%">Jenis</th>
                <th width="18%">Nominal</th>
                <th width="28%">Keterangan</th>
                <th width="15%">User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $t)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $t->tanggal->format('d/m/Y') }}</td>
                <td>{{ $t->category->nama_kategori }}</td>
                <td>{{ $t->category->jenis }}</td>
                <td class="fw-bold {{ $t->category->jenis === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($t->nominal, 0, ',', '.') }}
                </td>
                <td>{{ $t->keterangan ?? '-' }}</td>
                <td>{{ $t->user->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini di-generate secara otomatis oleh Sistem Pengelolaan Keuangan Kas HMSI</p>
        @if($laporan->catatan)
        <p><strong>Catatan:</strong> {{ $laporan->catatan }}</p>
        @endif
    </div>
</body>
</html>
