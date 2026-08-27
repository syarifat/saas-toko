<?php

namespace App\Services;

use App\Models\Gudang;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\StokGudang;
use Exception;
use Illuminate\Support\Facades\DB;

class StokService
{
    /**
     * Kurangi stok saat transaksi POS.
     * Validasi: stok tidak boleh kurang dari jumlah yang dijual.
     *
     * @throws Exception jika stok tidak cukup
     */
    public function kurangiStok(
        Produk $produk,
        Gudang $gudang,
        int $jumlah,
        string $referensiTipe,
        int $referensiId,
        string $catatan = ''
    ): void {
        DB::transaction(function () use ($produk, $gudang, $jumlah, $referensiTipe, $referensiId, $catatan) {
            $stok = StokGudang::where('toko_id', $produk->toko_id)
                ->where('produk_id', $produk->id)
                ->where('gudang_id', $gudang->id)
                ->lockForUpdate()
                ->first();

            $stokTersedia = $stok?->jumlah ?? 0;

            if ($stokTersedia < $jumlah) {
                throw new Exception("Stok [{$produk->nama}] di gudang [{$gudang->nama}] tidak mencukupi. Tersedia: {$stokTersedia}, diminta: {$jumlah}.");
            }

            $stok->decrement('jumlah', $jumlah);

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'jenis' => 'penjualan',
                'jumlah' => -$jumlah,
                'referensi_type' => $referensiTipe,
                'referensi_id' => $referensiId,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Tambah stok (barang masuk dari supplier atau restock).
     */
    public function tambahStok(
        Produk $produk,
        Gudang $gudang,
        int $jumlah,
        string $referensiTipe = '',
        int $referensiId = 0,
        string $catatan = ''
    ): void {
        DB::transaction(function () use ($produk, $gudang, $jumlah, $referensiTipe, $referensiId, $catatan) {
            $stok = StokGudang::where('toko_id', $produk->toko_id)
                ->where('produk_id', $produk->id)
                ->where('gudang_id', $gudang->id)
                ->lockForUpdate()
                ->first();

            if ($stok) {
                $stok->increment('jumlah', $jumlah);
            } else {
                StokGudang::create([
                    'toko_id' => $produk->toko_id,
                    'produk_id' => $produk->id,
                    'gudang_id' => $gudang->id,
                    'jumlah' => $jumlah,
                ]);
            }

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'jenis' => 'masuk',
                'jumlah' => $jumlah,
                'referensi_type' => $referensiTipe ?: null,
                'referensi_id' => $referensiId ?: null,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Transfer stok antar gudang.
     *
     * @throws Exception jika stok gudang asal tidak cukup
     */
    public function transferStok(
        Produk $produk,
        Gudang $gudangAsal,
        Gudang $gudangTujuan,
        int $jumlah,
        string $catatan = ''
    ): void {
        DB::transaction(function () use ($produk, $gudangAsal, $gudangTujuan, $jumlah, $catatan) {
            $stokAsal = StokGudang::where('toko_id', $produk->toko_id)
                ->where('produk_id', $produk->id)
                ->where('gudang_id', $gudangAsal->id)
                ->lockForUpdate()
                ->first();

            $tersedia = $stokAsal?->jumlah ?? 0;

            if ($tersedia < $jumlah) {
                throw new Exception("Stok [{$produk->nama}] di gudang asal [{$gudangAsal->nama}] tidak mencukupi untuk transfer.");
            }

            $stokAsal->decrement('jumlah', $jumlah);

            $stokTujuan = StokGudang::where('toko_id', $produk->toko_id)
                ->where('produk_id', $produk->id)
                ->where('gudang_id', $gudangTujuan->id)
                ->lockForUpdate()
                ->first();

            if ($stokTujuan) {
                $stokTujuan->increment('jumlah', $jumlah);
            } else {
                StokGudang::create([
                    'toko_id' => $produk->toko_id,
                    'produk_id' => $produk->id,
                    'gudang_id' => $gudangTujuan->id,
                    'jumlah' => $jumlah,
                ]);
            }

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudangAsal->id,
                'gudang_tujuan_id' => $gudangTujuan->id,
                'jenis' => 'transfer',
                'jumlah' => -$jumlah,
                'catatan' => $catatan ?: "Transfer ke {$gudangTujuan->nama}",
            ]);

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudangTujuan->id,
                'gudang_tujuan_id' => null,
                'jenis' => 'transfer',
                'jumlah' => $jumlah,
                'catatan' => $catatan ?: "Diterima dari transfer {$gudangAsal->nama}",
            ]);
        });
    }

    /**
     * Stok opname / penyesuaian stok fisik.
     */
    public function opname(
        Produk $produk,
        Gudang $gudang,
        int $jumlahFisik,
        string $catatan = ''
    ): void {
        DB::transaction(function () use ($produk, $gudang, $jumlahFisik, $catatan) {
            $stok = StokGudang::where('toko_id', $produk->toko_id)
                ->where('produk_id', $produk->id)
                ->where('gudang_id', $gudang->id)
                ->lockForUpdate()
                ->first();

            $stokSaatIni = $stok?->jumlah ?? 0;
            $selisih = $jumlahFisik - $stokSaatIni;

            if ($stok) {
                $stok->update(['jumlah' => $jumlahFisik]);
            } else {
                StokGudang::create([
                    'toko_id' => $produk->toko_id,
                    'produk_id' => $produk->id,
                    'gudang_id' => $gudang->id,
                    'jumlah' => $jumlahFisik,
                ]);
            }

            PergerakanStok::create([
                'toko_id' => $produk->toko_id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'jenis' => 'opname',
                'jumlah' => $selisih,
                'catatan' => $catatan ?: "Stok opname (sistem: {$stokSaatIni}, fisik: {$jumlahFisik}, selisih: {$selisih})",
            ]);
        });
    }
}
