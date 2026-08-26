<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\TransaksiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    /** @use HasFactory<TransaksiFactory> */
    use BelongsToToko, HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'toko_id',
        'pengguna_id',
        'gudang_id',
        'tanggal_transaksi',
        'subtotal',
        'diskon',
        'total',
        'jumlah_bayar',
        'kembalian',
        'metode_pembayaran',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'total' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class);
    }

    public function item(): HasMany
    {
        return $this->hasMany(ItemTransaksi::class);
    }
}
