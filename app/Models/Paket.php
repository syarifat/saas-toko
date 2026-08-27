<?php

namespace App\Models;

use Database\Factories\PaketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    /** @use HasFactory<PaketFactory> */
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = [
        'nama',
        'jenis',
        'harga',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'harga' => 'decimal:2',
        ];
    }

    public function toko(): HasMany
    {
        return $this->hasMany(Toko::class);
    }

    public function modul(): BelongsToMany
    {
        return $this->belongsToMany(Modul::class, 'paket_modul');
    }
}
