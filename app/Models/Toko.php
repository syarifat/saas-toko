<?php

namespace App\Models;

use App\Services\ModulService;
use Database\Factories\TokoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    /** @use HasFactory<TokoFactory> */
    use HasFactory;

    protected $table = 'toko';

    protected $fillable = [
        'nama',
        'slug',
        'paket_id',
        'status',
        'garis_lintang',
        'garis_bujur',
        'radius_absensi',
        'langganan_berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'garis_lintang' => 'decimal:7',
            'garis_bujur' => 'decimal:7',
            'radius_absensi' => 'integer',
            'langganan_berakhir_pada' => 'datetime',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class);
    }

    public function modulToko(): HasMany
    {
        return $this->hasMany(ModulToko::class);
    }

    public function modul(): BelongsToMany
    {
        return $this->belongsToMany(Modul::class, 'modul_toko', 'toko_id', 'modul_id')
            ->withPivot(['aktif', 'diaktifkan_pada', 'berakhir_pada']);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function modulAktif(string $kode): bool
    {
        return $this->modulToko()
            ->whereHas('modul', fn ($q) => $q->where('kode', $kode))
            ->where('aktif', true)
            ->exists();
    }

    public function pakaiPreset(Paket $paket): void
    {
        app(ModulService::class)->pakaiPreset($this, $paket);
    }
}
