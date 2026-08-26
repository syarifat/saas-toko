<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\ProdukFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use BelongsToToko, HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'toko_id',
        'kategori_id',
        'pemasok_id',
        'sku',
        'nama',
        'harga_beli',
        'harga_jual',
        'stok_minimum',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok_minimum' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pemasok(): BelongsTo
    {
        return $this->belongsTo(Pemasok::class);
    }

    public function stokGudang(): HasMany
    {
        return $this->hasMany(StokGudang::class);
    }

    /**
     * Total stok di semua gudang.
     */
    public function totalStok(): int
    {
        return (int) $this->stokGudang()->sum('jumlah');
    }
}
