<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\PengeluaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    /** @use HasFactory<PengeluaranFactory> */
    use BelongsToToko, HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'toko_id',
        'pengguna_id',
        'tanggal_pengeluaran',
        'keterangan',
        'nominal',
        'bukti_struk',
    ];

    protected $casts = [
        'tanggal_pengeluaran' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
