<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'tanggal',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Get the user that created the transaction.
     * 
     * RELASI: Transaction belongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the transaction.
     * 
     * RELASI: Transaction belongsTo Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
