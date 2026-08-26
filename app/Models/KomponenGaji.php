<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenGaji extends Model
{
    use BelongsToToko;

    protected $table = 'komponen_gaji';

    protected $fillable = ['toko_id', 'penggajian_id', 'jenis', 'nama', 'nominal'];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function penggajian(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class);
    }
}
