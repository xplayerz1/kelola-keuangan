<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'nama_role',
    ];

    /**
     * Get the users for the role.
     * 
     * RELASI: Role hasMany Users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
