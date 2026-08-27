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

class TenantStokGudangTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    private Produk $produk;

    private Gudang $gudangAsal;

    private Gudang $gudangTujuan;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        $this->toko = Toko::create(['nama' => 'Toko Gudang', 'slug' => 'toko-gudang', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Kepala Gudang',
            'email' => 'gudang@tokogudang.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'sub_peran' => 'gudang',
            'toko_id' => $this->toko->id,
        ]);

        foreach (['master_produk', 'stok_gudang', 'multi_gudang', 'barang_masuk', 'transfer_gudang', 'stok_opname'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        $this->gudangAsal = Gudang::create(['toko_id' => $this->toko->id, 'nama' => 'Gudang Pusat', 'jenis' => 'gudang']);
        $this->gudangTujuan = Gudang::create(['toko_id' => $this->toko->id, 'nama' => 'Etalase Toko', 'jenis' => 'etalase']);

        $this->produk = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'ITM-01',
            'nama' => 'Beras Pandan Wangi 5kg',
            'harga_beli' => 60000,
            'harga_jual' => 75000,
        ]);

        StokGudang::create(['toko_id' => $this->toko->id, 'produk_id' => $this->produk->id, 'gudang_id' => $this->gudangAsal->id, 'jumlah' => 50]);
        StokGudang::create(['toko_id' => $this->toko->id, 'produk_id' => $this->produk->id, 'gudang_id' => $this->gudangTujuan->id, 'jumlah' => 5]);
    }

    public function test_barang_masuk_menambah_stok_gudang(): void
    {
        $response = $this->actingAs($this->user)->post(route('barang_masuk.store'), [
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->gudangAsal->id,
            'jumlah' => 20,
            'catatan' => 'Restock supplier PT Beras',
        ]);

        $response->assertRedirect();
        $this->assertSame(70, $this->gudangAsal->stokProduk($this->produk->id));
        $this->assertDatabaseHas('pergerakan_stok', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'jenis' => 'masuk',
            'jumlah' => 20,
        ]);
    }

    public function test_transfer_antar_gudang_mengurangi_asal_dan_menambah_tujuan(): void
    {
        $response = $this->actingAs($this->user)->post(route('transfer_gudang.store'), [
            'produk_id' => $this->produk->id,
            'gudang_asal_id' => $this->gudangAsal->id,
            'gudang_tujuan_id' => $this->gudangTujuan->id,
            'jumlah' => 15,
            'catatan' => 'Pindah ke etalase untuk display',
        ]);

        $response->assertRedirect();
        $this->assertSame(35, $this->gudangAsal->stokProduk($this->produk->id));
        $this->assertSame(20, $this->gudangTujuan->stokProduk($this->produk->id));
    }

    public function test_stok_opname_menyesuaikan_stok_fisik(): void
    {
        $response = $this->actingAs($this->user)->post(route('stok.opname.simpan'), [
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->gudangAsal->id,
            'jumlah_fisik' => 48, // Selisih -2 (rusak/bocor)
            'catatan' => '2 karung kemasan sobek',
        ]);

        $response->assertRedirect();
        $this->assertSame(48, $this->gudangAsal->stokProduk($this->produk->id));
        $this->assertDatabaseHas('pergerakan_stok', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'jenis' => 'opname',
            'jumlah' => -2,
        ]);
    }
}
