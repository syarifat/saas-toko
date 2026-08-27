<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\StokGudang;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantGudangDanStokTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        $this->toko = Toko::create(['nama' => 'Toko Gudang', 'slug' => 'toko-gudang', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        foreach (['master_produk', 'stok_gudang', 'multi_gudang', 'stock_alert', 'kartu_stok'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
        }
    }

    public function test_tidak_bisa_menghapus_gudang_yang_masih_memiliki_stok(): void
    {
        $gudang = Gudang::create(['toko_id' => $this->toko->id, 'nama' => 'Gudang Utama', 'jenis' => 'gudang']);
        $produk = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'P-01',
            'nama' => 'Barang A',
            'harga_beli' => 1000,
            'harga_jual' => 2000,
        ]);

        StokGudang::create([
            'toko_id' => $this->toko->id,
            'gudang_id' => $gudang->id,
            'produk_id' => $produk->id,
            'jumlah' => 10,
        ]);

        $response = $this->actingAs($this->user)->delete(route('gudang.destroy', $gudang));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('gudang', ['id' => $gudang->id]);
    }

    public function test_alert_stok_hanya_menampilkan_produk_di_bawah_batas_minimum(): void
    {
        $gudang = Gudang::create(['toko_id' => $this->toko->id, 'nama' => 'Etalase', 'jenis' => 'etalase']);

        // Produk A: Stok 2 <= Minimum 5 (HARUS MUNCUL)
        $pA = Produk::create(['toko_id' => $this->toko->id, 'sku' => 'A', 'nama' => 'Kopi Menipis', 'harga_beli' => 1000, 'harga_jual' => 2000, 'stok_minimum' => 5]);
        StokGudang::create(['toko_id' => $this->toko->id, 'gudang_id' => $gudang->id, 'produk_id' => $pA->id, 'jumlah' => 2]);

        // Produk B: Stok 50 > Minimum 10 (TIDAK MUNCUL)
        $pB = Produk::create(['toko_id' => $this->toko->id, 'sku' => 'B', 'nama' => 'Kopi Melimpah', 'harga_beli' => 1000, 'harga_jual' => 2000, 'stok_minimum' => 10]);
        StokGudang::create(['toko_id' => $this->toko->id, 'gudang_id' => $gudang->id, 'produk_id' => $pB->id, 'jumlah' => 50]);

        $response = $this->actingAs($this->user)->get(route('stok.alert'));
        $response->assertOk();
        $response->assertSee('Kopi Menipis');
        $response->assertDontSee('Kopi Melimpah');
    }
}
