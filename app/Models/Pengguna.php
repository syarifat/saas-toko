<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'toko_id',
        'nama',
        'name',
        'email',
        'password',
        'peran',
        'sub_peran',
        'aktif',
        'dibuat_oleh',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function pembuatAkun(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class, 'pengguna_id');
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function isSuperadmin(): bool
    {
        return $this->peran === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    public function isKaryawan(): bool
    {
        return $this->peran === 'karyawan';
    }

    public function isKasir(): bool
    {
        return $this->sub_peran === 'kasir';
    }

    public function isGudang(): bool
    {
        return $this->sub_peran === 'gudang';
    }
}
