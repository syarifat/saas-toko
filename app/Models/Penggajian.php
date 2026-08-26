<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penggajian extends Model
{
    use BelongsToToko;

    protected $table = 'penggajian';

    protected $fillable = [
        'toko_id',
        'karyawan_id',
        'periode_mulai',
        'periode_selesai',
        'skema_gaji_snapshot',
        'jumlah_dasar',
        'jumlah_hadir',
        'total_tunjangan',
        'total_potongan',
        'gaji_bersih',
        'status',
        'dibayar_pada',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'jumlah_dasar' => 'decimal:2',
        'total_tunjangan' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
        'dibayar_pada' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function komponen(): HasMany
    {
        return $this->hasMany(KomponenGaji::class);
    }
}
