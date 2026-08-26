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
        'nama',
        'posisi',
        'skema_gaji',
        'tarif_harian',
        'gaji_pokok',
        'tanggal_masuk',
        'aktif',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tarif_harian' => 'decimal:2',
        'gaji_pokok' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    /**
     * Jumlah hari hadir dalam rentang tanggal.
     */
    public function jumlahHadir(string $mulai, string $selesai): int
    {
        return (int) $this->absensi()
            ->whereBetween('tanggal', [$mulai, $selesai])
            ->whereNotNull('jam_masuk')
            ->count();
    }
}
