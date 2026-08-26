<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use BelongsToToko;

    protected $table = 'pembayaran';

    protected $fillable = [
        'toko_id',
        'pengguna_id',
        'jenis',
        'paket_id',
        'addon_id',
        'jumlah_bulan',
        'nominal',
        'bukti_transfer',
        'status',
        'catatan_tenant',
        'catatan_admin',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'diverifikasi_pada' => 'datetime',
    ];

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Label item yang dibayar (paket atau add-on).
     */
    public function labelItem(): string
    {
        return $this->jenis === 'paket'
            ? 'Paket: '.($this->paket?->nama ?? '-')
            : 'Add-on: '.($this->addon?->nama ?? '-');
    }
}
