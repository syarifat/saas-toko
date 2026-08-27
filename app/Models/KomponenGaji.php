<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenGaji extends Model
{
    use BelongsToToko, HasFactory;

    protected $table = 'komponen_gaji';

    protected $fillable = [
        'toko_id',
        'penggajian_id',
        'jenis',
        'nama',
        'nominal',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
        ];
    }

    public function penggajian(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class);
    }
}
