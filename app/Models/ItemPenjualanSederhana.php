<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPenjualanSederhana extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'item_penjualan_sederhana';

    protected $fillable = [
        'toko_id',
        'penjualan_sederhana_id',
        'produk_id',
        'nama_produk',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'harga_beli_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'harga_beli_snapshot' => 'decimal:2',
        ];
    }

    public function penjualanSederhana(): BelongsTo
    {
        return $this->belongsTo(PenjualanSederhana::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
