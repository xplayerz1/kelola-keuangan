<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'nama_kategori',
        'jenis',
        'keterangan',
    ];

    /**
     * Get the transactions for the category.
     * 
     * RELASI: Category hasMany Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }
}
