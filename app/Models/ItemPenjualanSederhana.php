<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPenjualanSederhana extends Model
{
    use BelongsToToko;

    protected $table = 'item_penjualan_sederhana';

    protected $fillable = [
        'toko_id',
        'penjualan_sederhana_id',
        'nama_barang',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function penjualanSederhana(): BelongsTo
    {
        return $this->belongsTo(PenjualanSederhana::class);
    }
}
