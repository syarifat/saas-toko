<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\KaryawanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    /** @use HasFactory<KaryawanFactory> */
    use BelongsToToko, HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'toko_id',
        'pengguna_id',
        'kode_karyawan',
        'posisi',
        'skema_gaji',
        'tarif_harian',
        'gaji_pokok',
        'tanggal_masuk',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tarif_harian' => 'decimal:2',
            'gaji_pokok' => 'decimal:2',
            'tanggal_masuk' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }
}
