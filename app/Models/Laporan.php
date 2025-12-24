<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = [
        'judul',
        'start_date',
        'end_date',
        'total_pemasukan',
        'total_pengeluaran',
        'selisih',
        'status',
        'generated_by',
        'keterangan_libur',
        'dashboard_screenshot', // Statically.io visual audit
        'catatan',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_pemasukan' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
        'selisih' => 'decimal:2',
        'keterangan_libur' => 'array', // JSON to array
        'dashboard_screenshot' => 'array', // Screenshot metadata
    ];

    /**
     * Get the user who generated this report
     * 
     * RELASI: Laporan belongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status === 'published' ? 'success' : 'secondary';
    }

    /**
     * Get surplus/defisit label
     */
    public function getSelisihLabelAttribute()
    {
        return $this->selisih >= 0 ? 'Surplus' : 'Defisit';
    }
}
