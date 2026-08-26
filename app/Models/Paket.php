<?php

namespace App\Models;

use Database\Factories\PaketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    /** @use HasFactory<PaketFactory> */
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = [
        'nama',
        'tingkat',
        'harga',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function toko(): HasMany
    {
        return $this->hasMany(Toko::class);
    }
}
