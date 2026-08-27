<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemasok extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'pemasok';

    protected $fillable = [
        'toko_id',
        'nama',
        'telepon',
        'alamat',
    ];

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class);
    }
}
