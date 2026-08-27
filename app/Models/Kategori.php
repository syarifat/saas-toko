<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'kategori';

    protected $fillable = [
        'toko_id',
        'nama',
    ];

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class);
    }
}
