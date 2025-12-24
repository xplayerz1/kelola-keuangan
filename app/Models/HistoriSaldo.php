<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldo extends Model
{
    protected $table = 'histori_saldo';

    protected $fillable = [
        'id_saldo',
        'transaction_id',
        'nominal',
        'saldo_sebelum',
        'saldo_sesudah',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
    ];

    /**
     * Get the master balance that owns the history.
     * 
     * RELASI: HistoriSaldo belongsTo MasterSaldo
     */
    public function masterSaldo()
    {
        return $this->belongsTo(MasterSaldo::class, 'id_saldo');
    }

    /**
     * Get the transaction that triggered this history.
     * 
     * RELASI 2 TABEL: HistoriSaldo belongsTo Transaction
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
