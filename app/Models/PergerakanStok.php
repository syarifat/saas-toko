<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PergerakanStok extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'pergerakan_stok';

    protected $fillable = [
        'toko_id',
        'produk_id',
        'gudang_id',
        'gudang_tujuan_id',
        'jenis',
        'jumlah',
        'referensi_type',
        'referensi_id',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

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

    public function referensi(): MorphTo
    {
        return $this->morphTo('referensi');
    }
}
