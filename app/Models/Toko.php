<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    /** @use HasFactory<TokoFactory> */
    use HasFactory;

    protected $table = 'toko';

    protected $fillable = [
        'nama',
        'slug',
        'paket_id',
        'status',
        'garis_lintang',
        'garis_bujur',
        'radius_absensi',
        'langganan_berakhir_pada',
    ];

    protected $casts = [
        'langganan_berakhir_pada' => 'datetime',
        'garis_lintang' => 'float',
        'garis_bujur' => 'float',
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }

    public function penjualanSederhana(): HasMany
    {
        return $this->hasMany(PenjualanSederhana::class);
    }

    public function gudang(): HasMany
    {
        return $this->hasMany(Gudang::class);
    }

    public function kategori(): HasMany
    {
        return $this->hasMany(Kategori::class);
    }

    public function pemasok(): HasMany
    {
        return $this->hasMany(Pemasok::class);
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class);
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Gudang utama toko (etalase default), dibuat otomatis bila belum ada.
     */
    public function gudangUtama(): Gudang
    {
        return $this->gudang()->firstOrCreate(
            ['toko_id' => $this->id],
            ['nama' => 'Etalase', 'jenis' => 'etalase'],
        );
    }

    public function admin()
    {
        return $this->hasOne(User::class)->where('peran', 'admin');
    }

    public function addonAktif(): HasMany
    {
        return $this->hasMany(AddonToko::class)->where('aktif', true);
    }

    public function punyaAddon(string $kode): bool
    {
        return $this->addonAktif()->whereHas('addon', fn ($q) => $q->where('kode', $kode))->exists();
    }

    public function setidaknyaPaket(int $tingkat): bool
    {
        return $this->paket && $this->paket->tingkat >= $tingkat;
    }
}
