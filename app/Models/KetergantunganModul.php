<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetergantunganModul extends Model
{
    protected $table = 'ketergantungan_modul';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'modul_id',
        'requires_modul_id',
    ];

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }

    public function requiredModul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'requires_modul_id');
    }
}
