<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonToko extends Model
{
    public $timestamps = false;

    protected $table = 'addon_toko';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'toko_id',
        'addon_id',
        'aktif',
        'diaktifkan_pada',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'diaktifkan_pada' => 'datetime',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }
}
