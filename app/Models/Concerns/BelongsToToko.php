<?php

namespace App\Models\Concerns;

use App\Models\Toko;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToToko
{
    public static function bootBelongsToToko(): void
    {
        static::addGlobalScope('toko', function (Builder $query) {
            $tokoId = app()->bound('toko.id') ? app('toko.id') : auth()->user()?->toko_id;

            if ($tokoId !== null) {
                $query->where($query->getModel()->getTable().'.toko_id', $tokoId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->toko_id)) {
                $model->toko_id = app()->bound('toko.id') ? app('toko.id') : auth()->user()?->toko_id;
            }
        });
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
