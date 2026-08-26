<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    use BelongsToToko;

    protected $table = 'pemasok';

    protected $fillable = ['toko_id', 'nama', 'telepon', 'alamat'];
}
