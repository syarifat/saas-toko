<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaketModul extends Model
{
    protected $table = 'paket_modul';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'paket_id',
        'modul_id',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }
}
