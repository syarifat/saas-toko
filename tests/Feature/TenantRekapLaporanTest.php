<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\ItemTransaksi;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengeluaran;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantRekapLaporanTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        $this->toko = Toko::create(['nama' => 'Toko Laporan', 'slug' => 'toko-laporan', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Admin Laporan',
            'email' => 'admin@laporan.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        foreach (['master_produk', 'kasir_pos', 'pengeluaran', 'rekap_keuangan', 'laporan_hpp'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        $gudang = Gudang::create(['toko_id' => $this->toko->id, 'nama' => 'Etalase', 'jenis' => 'etalase']);

        $p1 = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'P-01',
            'nama' => 'Kopi Robusta',
            'harga_beli' => 20000,
            'harga_jual' => 35000,
        ]);

        // Buat 1 Transaksi POS
        $trx = Transaksi::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->user->id,
            'gudang_id' => $gudang->id,
            'tanggal_transaksi' => '2026-08-27',
            'subtotal' => 70000,
            'diskon' => 0,
            'total' => 70000,
            'jumlah_bayar' => 100000,
            'kembalian' => 30000,
            'metode_pembayaran' => 'tunai',
        ]);

        ItemTransaksi::create([
            'toko_id' => $this->toko->id,
            'transaksi_id' => $trx->id,
            'produk_id' => $p1->id,
            'nama_produk' => 'Kopi Robusta',
            'jumlah' => 2,
            'harga_satuan' => 35000,
            'subtotal' => 70000,
            'harga_beli_snapshot' => 20000,
        ]);

        // Buat 1 Pengeluaran
        Pengeluaran::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->user->id,
            'tanggal_pengeluaran' => '2026-08-27',
            'keterangan' => 'Beli Cup Plastik',
            'nominal' => 15000,
        ]);
    }

    public function test_rekap_keuangan_menampilkan_omset_hpp_dan_laba_bersih(): void
    {
        $response = $this->actingAs($this->user)->get(route('rekap.index', ['bulan' => '2026-08']));
        $response->assertOk();
        $response->assertSee('70.000'); // Omset
        $response->assertSee('40.000'); // Total HPP (2 x 20.000)
        $response->assertSee('30.000'); // Laba Kotor (70.000 - 40.000)
        $response->assertSee('15.000'); // Beban Pengeluaran
        $response->assertSee('15.000'); // Estimasi Laba Bersih (30.000 - 15.000)
    }

    public function test_laporan_hpp_menampilkan_margin_laba_per_produk(): void
    {
        $response = $this->actingAs($this->user)->get(route('laporan.hpp', ['bulan' => '2026-08']));
        $response->assertOk();
        $response->assertSee('Kopi Robusta');
        $response->assertSee('70.000'); // Omset
        $response->assertSee('40.000'); // HPP
        $response->assertSee('30.000'); // Laba Kotor
        $response->assertSee('43%');    // Margin % (30.000 / 70.000)
    }
}
