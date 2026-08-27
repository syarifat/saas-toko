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

class TenantProdukTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $this->toko = Toko::create(['nama' => 'Toko A', 'slug' => 'toko-a', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Owner A',
            'email' => 'owner@tokoa.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $modul = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $modul->id, 'aktif' => true]);
    }

    public function test_tenant_bisa_membuat_produk_dengan_stok_awal(): void
    {
        $response = $this->actingAs($this->user)->post(route('produk.store'), [
            'sku' => 'PRD-01',
            'nama' => 'Kopi Arabika 250g',
            'harga_beli' => 30000,
            'harga_jual' => 50000,
            'stok_minimum' => 5,
            'stok_awal' => 20,
        ]);

        $response->assertRedirect(route('produk.index'));
        $this->assertDatabaseHas('produk', ['toko_id' => $this->toko->id, 'sku' => 'PRD-01']);

        $produk = Produk::where('sku', 'PRD-01')->first();
        $this->assertSame(20, $produk->totalStok());
    }

    public function test_sku_harus_unik_hanya_dalam_toko_yang_sama(): void
    {
        // Toko A buat SKU PRD-01
        Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'PRD-01',
            'nama' => 'Kopi Toko A',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
        ]);

        // Toko A coba buat SKU yang sama -> Gagal
        $res1 = $this->actingAs($this->user)->post(route('produk.store'), [
            'sku' => 'PRD-01',
            'nama' => 'Kopi Duplikat',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
        ]);
        $res1->assertSessionHasErrors('sku');

        // Toko B buat SKU PRD-01 -> Berhasil
        $tokoB = Toko::create(['nama' => 'Toko B', 'slug' => 'toko-b', 'paket_id' => $this->toko->paket_id, 'status' => 'aktif']);
        $userB = Pengguna::create([
            'nama' => 'Owner B',
            'email' => 'owner@tokob.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $tokoB->id,
        ]);
        ModulToko::create(['toko_id' => $tokoB->id, 'modul_id' => Modul::first()->id, 'aktif' => true]);

        $res2 = $this->actingAs($userB)->post(route('produk.store'), [
            'sku' => 'PRD-01',
            'nama' => 'Kopi Toko B',
            'harga_beli' => 12000,
            'harga_jual' => 18000,
        ]);
        $res2->assertRedirect(route('produk.index'));
        $this->assertDatabaseHas('produk', ['toko_id' => $tokoB->id, 'sku' => 'PRD-01']);
    }
}
