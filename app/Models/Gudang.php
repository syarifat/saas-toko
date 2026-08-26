<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gudang extends Model
{
    use BelongsToToko;

    protected $table = 'gudang';

    protected $fillable = ['toko_id', 'nama', 'jenis'];

    public function stokGudang(): HasMany
    {
        return $this->hasMany(StokGudang::class);
    }
}
