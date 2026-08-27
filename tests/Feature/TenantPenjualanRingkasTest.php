<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPenjualanRingkasTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $this->toko = Toko::create(['nama' => 'Toko Ringkas', 'slug' => 'toko-ringkas', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Staff Ringkas',
            'email' => 'staff@ringkas.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $m1 = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        $m2 = Modul::create(['kode' => 'penjualan_ringkas', 'nama' => 'Penjualan Ringkas']);

        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m1->id, 'aktif' => true]);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m2->id, 'aktif' => true]);

        $this->produk = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'SNK-01',
            'nama' => 'Keripik Singkong',
            'harga_beli' => 8000,
            'harga_jual' => 12000,
        ]);
    }

    public function test_pencatatan_penjualan_ringkas_berhasil_menyimpan_item_dan_hpp(): void
    {
        $response = $this->actingAs($this->user)->post(route('penjualan.store'), [
            'tanggal_penjualan' => '2026-08-27',
            'catatan' => 'Penjualan offline borongan',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'jumlah' => 5,
                    'harga_satuan' => 12000,
                ],
            ],
        ]);

        $response->assertRedirect(route('penjualan.index'));
        $this->assertDatabaseHas('penjualan_sederhana', [
            'toko_id' => $this->toko->id,
            'total' => 60000,
            'catatan' => 'Penjualan offline borongan',
        ]);

        $this->assertDatabaseHas('item_penjualan_sederhana', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'jumlah' => 5,
            'harga_satuan' => 12000,
            'subtotal' => 60000,
            'harga_beli_snapshot' => 8000,
        ]);
    }
}
