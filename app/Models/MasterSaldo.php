<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSaldo extends Model
{
    protected $table = 'master_saldo';

    protected $fillable = [
        'periode',
        'saldo_awal',
        'total_masuk',
        'total_keluar',
        'saldo_akhir',
        'status',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'total_masuk' => 'decimal:2',
        'total_keluar' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    /**
     * Get the history for the balance.
     * 
     * RELASI: MasterSaldo hasMany HistoriSaldo
     */
    public function historiSaldo()
    {
        return $this->hasMany(HistoriSaldo::class, 'id_saldo');
    }
}
