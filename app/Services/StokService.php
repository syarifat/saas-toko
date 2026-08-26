<?php

namespace App\Services;

use App\Models\Gudang;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\StokGudang;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class StokService
{
    /**
     * Ambil atau buat baris stok untuk kombinasi produk-gudang.
     */
    public function stok(Produk $produk, Gudang $gudang, int $jumlah = 0): StokGudang
    {
        return StokGudang::firstOrCreate(
            ['produk_id' => $produk->id, 'gudang_id' => $gudang->id],
            ['toko_id' => $produk->toko_id, 'jumlah' => max($jumlah, 0)],
        );
    }

    /**
     * Catat pergerakan masuk (barang datang / retur).
     */
    public function masuk(Produk $produk, Gudang $gudang, int $jumlah, int $penggunaId, ?string $catatan = null, ?string $referensiTipe = null, ?int $referensiId = null): void
    {
        $this->ubahStok($produk, $gudang, abs($jumlah), $penggunaId, 'masuk', $catatan, $referensiTipe, $referensiId);
    }

    /**
     * Catat pergerakan keluar manual.
     */
    public function keluar(Produk $produk, Gudang $gudang, int $jumlah, int $penggunaId, ?string $catatan = null): void
    {
        $this->ubahStok($produk, $gudang, -abs($jumlah), $penggunaId, 'keluar', $catatan);
    }

    /**
     * Stok opname: set stok ke jumlah fisik hasil hitung.
     */
    public function opname(Produk $produk, Gudang $gudang, int $jumlahFisik, int $penggunaId, ?string $catatan = null): void
    {
        DB::transaction(function () use ($produk, $gudang, $jumlahFisik, $penggunaId, $catatan) {
            $baris = $this->stok($produk, $gudang);
            $selisih = $jumlahFisik - $baris->jumlah;

            if ($selisih === 0) {
                return;
            }

            $baris->update(['jumlah' => $jumlahFisik]);

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'jenis' => 'opname',
                'jumlah' => $selisih,
                'catatan' => $catatan ?? 'Stok opname',
                'pengguna_id' => $penggunaId,
            ]);
        });
    }

    /**
     * Deduct stok saat penjualan POS + catat pergerakan penjualan.
     */
    public function deductUntukTransaksi(Produk $produk, Gudang $gudang, int $jumlah, Transaksi $transaksi, int $penggunaId): void
    {
        $baris = $this->stok($produk, $gudang);

        if ($baris->jumlah < $jumlah) {
            throw new \DomainException("Stok {$produk->nama} tidak cukup (tersedia {$baris->jumlah}).");
        }

        $baris->decrement('jumlah', $jumlah);

        PergerakanStok::create([
            'toko_id' => $produk->toko_id,
            'produk_id' => $produk->id,
            'gudang_id' => $gudang->id,
            'jenis' => 'penjualan',
            'jumlah' => -$jumlah,
            'referensi_tipe' => Transaksi::class,
            'referensi_id' => $transaksi->id,
            'pengguna_id' => $penggunaId,
        ]);
    }

    /**
     * Transfer stok antar gudang dalam satu toko.
     */
    public function transfer(Produk $produk, Gudang $asal, Gudang $tujuan, int $jumlah, int $penggunaId, ?string $catatan = null): void
    {
        if ($asal->id === $tujuan->id) {
            throw new \DomainException('Gudang asal dan tujuan tidak boleh sama.');
        }

        DB::transaction(function () use ($produk, $asal, $tujuan, $jumlah, $penggunaId, $catatan) {
            $barisAsal = $this->stok($produk, $asal);

            if ($barisAsal->jumlah < $jumlah) {
                throw new \DomainException("Stok {$produk->nama} di {$asal->nama} tidak cukup (tersedia {$barisAsal->jumlah}).");
            }

            $barisAsal->decrement('jumlah', $jumlah);

            $barisTujuan = $this->stok($produk, $tujuan);
            $barisTujuan->increment('jumlah', $jumlah);

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $asal->id,
                'gudang_tujuan_id' => $tujuan->id,
                'jenis' => 'transfer',
                'jumlah' => -$jumlah,
                'catatan' => $catatan ?? "Transfer ke {$tujuan->nama}",
                'pengguna_id' => $penggunaId,
            ]);
        });
    }

    private function ubahStok(Produk $produk, Gudang $gudang, int $delta, int $penggunaId, string $jenis, ?string $catatan = null, ?string $referensiTipe = null, ?int $referensiId = null): void
    {
        DB::transaction(function () use ($produk, $gudang, $delta, $penggunaId, $jenis, $catatan, $referensiTipe, $referensiId) {
            $baris = $this->stok($produk, $gudang);
            $baru = $baris->jumlah + $delta;

            if ($baru < 0) {
                throw new \DomainException("Stok {$produk->nama} tidak cukup (tersedia {$baris->jumlah}).");
            }

            $baris->update(['jumlah' => $baru]);

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'jenis' => $jenis,
                'jumlah' => $delta,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'catatan' => $catatan,
                'pengguna_id' => $penggunaId,
            ]);
        });
    }
}
