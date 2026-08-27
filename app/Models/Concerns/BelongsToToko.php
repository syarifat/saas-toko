<?php

namespace App\Models\Concerns;

use App\Models\Toko;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToToko
{
    public static function bootBelongsToToko(): void
    {
        static::addGlobalScope('toko', function ($query) {
            if (auth()->check() && auth()->user()->toko_id) {
                $query->where(
                    (new static)->qualifyColumn('toko_id'),
                    auth()->user()->toko_id
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->toko_id && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
