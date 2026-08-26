<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'addon';

    protected $fillable = [
        'kode',
        'nama',
        'harga',
        'aktif',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'aktif' => 'boolean',
    ];
}
