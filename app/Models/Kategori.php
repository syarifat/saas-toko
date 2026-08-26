<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use BelongsToToko;

    protected $table = 'kategori';

    protected $fillable = ['toko_id', 'nama'];
}
