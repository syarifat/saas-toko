<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use BelongsToToko;

    public $timestamps = false;

    protected $table = 'absensi';

    protected $fillable = [
        'toko_id',
        'karyawan_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'lintang_masuk',
        'bujur_masuk',
        'lintang_keluar',
        'bujur_keluar',
        'foto_masuk',
        'foto_keluar',
        'status',
        'menit_telat',
        'menit_lembur',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
        'lintang_masuk' => 'float',
        'bujur_masuk' => 'float',
        'lintang_keluar' => 'float',
        'bujur_keluar' => 'float',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
