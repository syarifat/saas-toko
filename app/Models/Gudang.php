<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gudang extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'gudang';

    protected $fillable = [
        'toko_id',
        'nama',
        'jenis',
    ];

    public function stokGudang(): HasMany
    {
        return $this->hasMany(StokGudang::class);
    }

    public function pergerakanStok(): HasMany
    {
        return $this->hasMany(PergerakanStok::class);
    }

    public function stokProduk(int $produkId): int
    {
        return (int) $this->stokGudang()->where('produk_id', $produkId)->value('jumlah');
    }
}
