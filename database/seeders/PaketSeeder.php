<?php

namespace Database\Seeders;

use App\Models\Modul;
use App\Models\Paket;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Preset 1 — Cashbook (4 modul)
        $preset1 = Paket::updateOrCreate(
            ['jenis' => 'preset_1'],
            [
                'nama' => 'Paket 1 — Cashbook',
                'harga' => 99000,
                'deskripsi' => 'Pencatatan pengeluaran, penjualan ringkas dari master produk, dan rekap laba kotor.',
                'aktif' => true,
            ]
        );
        $modulPreset1 = ['pengeluaran', 'master_produk', 'penjualan_ringkas', 'rekap_keuangan'];
        $preset1->modul()->sync(Modul::whereIn('kode', $modulPreset1)->pluck('id'));

        // Preset 2 — POS & Stok (9 modul)
        $preset2 = Paket::updateOrCreate(
            ['jenis' => 'preset_2'],
            [
                'nama' => 'Paket 2 — POS & Stok',
                'harga' => 199000,
                'deskripsi' => 'Kasir POS dengan auto deduct stok, manajemen stok, alert, opname, dan laporan HPP.',
                'aktif' => true,
            ]
        );
        $modulPreset2 = array_merge($modulPreset1, [
            'stok_gudang',
            'kasir_pos',
            'stock_alert',
            'stok_opname',
            'laporan_hpp',
        ]);
        $preset2->modul()->sync(Modul::whereIn('kode', $modulPreset2)->pluck('id'));

        // Preset 3 — Gudang (13 modul)
        $preset3 = Paket::updateOrCreate(
            ['jenis' => 'preset_3'],
            [
                'nama' => 'Paket 3 — Gudang',
                'harga' => 299000,
                'deskripsi' => 'Multi-gudang, barang masuk dari supplier, transfer antar gudang, dan kartu stok.',
                'aktif' => true,
            ]
        );
        $modulPreset3 = array_merge($modulPreset2, [
            'multi_gudang',
            'barang_masuk',
            'transfer_gudang',
            'kartu_stok',
        ]);
        $preset3->modul()->sync(Modul::whereIn('kode', $modulPreset3)->pluck('id'));
    }
}
