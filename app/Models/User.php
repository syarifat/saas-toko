<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'toko_id',
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

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function isSuperadmin(): bool
    {
        return $this->peran === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }
}
