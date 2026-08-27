<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modul extends Model
{
    use HasFactory;

    protected $table = 'modul';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function paket(): BelongsToMany
    {
        return $this->belongsToMany(Paket::class, 'paket_modul');
    }

    public function toko(): BelongsToMany
    {
        return $this->belongsToMany(Toko::class, 'modul_toko')
            ->withPivot(['aktif', 'diaktifkan_pada', 'berakhir_pada']);
    }

    public function ketergantungan(): BelongsToMany
    {
        return $this->belongsToMany(
            Modul::class,
            'ketergantungan_modul',
            'modul_id',
            'requires_modul_id'
        );
    }

    public function dependan(): BelongsToMany
    {
        return $this->belongsToMany(
            Modul::class,
            'ketergantungan_modul',
            'requires_modul_id',
            'modul_id'
        );
    }
}
