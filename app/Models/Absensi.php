<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use BelongsToToko, HasFactory;

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

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_masuk' => 'datetime',
            'jam_keluar' => 'datetime',
            'lintang_masuk' => 'decimal:7',
            'bujur_masuk' => 'decimal:7',
            'lintang_keluar' => 'decimal:7',
            'bujur_keluar' => 'decimal:7',
            'menit_telat' => 'integer',
            'menit_lembur' => 'integer',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
