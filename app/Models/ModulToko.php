<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulToko extends Model
{
    protected $table = 'modul_toko';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'toko_id',
        'modul_id',
        'aktif',
        'diaktifkan_pada',
        'berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'diaktifkan_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }
}
