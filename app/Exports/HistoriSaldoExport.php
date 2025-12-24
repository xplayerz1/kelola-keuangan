<?php

namespace App\Exports;

use App\Models\HistoriSaldo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistoriSaldoExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $saldoId;

    public function __construct($saldoId = null)
    {
        $this->saldoId = $saldoId;
    }

    /**
     * Get data collection for export
     */
    public function collection()
    {
        $query = HistoriSaldo::with(['masterSaldo', 'transaction.category', 'transaction.user'])
            ->orderBy('created_at', 'desc');
        
        if ($this->saldoId) {
            $query->where('id_saldo', $this->saldoId);
        }
        
        return $query->get();
    }

    /**
     * Define Excel column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Periode',
            'Tanggal',
            'Transaksi',
            'Kategori',
            'Nominal',
            'Saldo Sebelum',
            'Saldo Sesudah',
            'Keterangan',
            'User',
        ];
    }

    /**
     * Map data to Excel rows
     */
    public function map($histori): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $histori->masterSaldo->periode,
            $histori->created_at->format('d/m/Y H:i'),
            $histori->transaction ? '#' . $histori->transaction->id : '-',
            $histori->transaction ? $histori->transaction->category->nama_kategori : '-',
            'Rp ' . number_format($histori->nominal, 0, ',', '.'),
            'Rp ' . number_format($histori->saldo_sebelum, 0, ',', '.'),
            'Rp ' . number_format($histori->saldo_sesudah, 0, ',', '.'),
            $histori->keterangan,
            $histori->transaction ? $histori->transaction->user->name : 'System',
        ];
    }

    /**
     * Apply styles to Excel sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
