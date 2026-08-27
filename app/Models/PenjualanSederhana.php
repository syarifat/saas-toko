<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\PenjualanSederhanaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenjualanSederhana extends Model
{
    /** @use HasFactory<PenjualanSederhanaFactory> */
    use BelongsToToko, HasFactory;

    protected $table = 'penjualan_sederhana';

    protected $fillable = [
        'toko_id',
        'pengguna_id',
        'tanggal_penjualan',
        'total',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penjualan' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPenjualanSederhana::class);
    }
}
