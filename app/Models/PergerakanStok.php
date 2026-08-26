<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PergerakanStok extends Model
{
    use BelongsToToko;

    protected $table = 'pergerakan_stok';

    protected $fillable = [
        'toko_id',
        'produk_id',
        'gudang_id',
        'gudang_tujuan_id',
        'jenis',
        'jumlah',
        'referensi_tipe',
        'referensi_id',
        'catatan',
        'pengguna_id',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class);
    }

    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(Gudang::class, 'gudang_tujuan_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
