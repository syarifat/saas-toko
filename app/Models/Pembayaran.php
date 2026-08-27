<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'toko_id',
        'jenis',
        'paket_id',
        'modul_id',
        'jumlah',
        'bukti_transfer',
        'status',
        'catatan_penolakan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh');
    }
}
