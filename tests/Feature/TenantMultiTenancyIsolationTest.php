<?php

namespace Tests\Feature;

use App\Models\Gudang;
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

class TenantMultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Toko $tokoA;

    private Toko $tokoB;

    private Pengguna $userA;

    private Pengguna $userB;

    private Produk $produkA;

    private Produk $produkB;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);

        $this->tokoA = Toko::create(['nama' => 'Toko Alpha', 'slug' => 'toko-alpha', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->userA = Pengguna::create([
            'nama' => 'User Alpha',
            'email' => 'alpha@test.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->tokoA->id,
        ]);

        $this->tokoB = Toko::create(['nama' => 'Toko Beta', 'slug' => 'toko-beta', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->userB = Pengguna::create([
            'nama' => 'User Beta',
            'email' => 'beta@test.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->tokoB->id,
        ]);

        foreach (['master_produk', 'pengeluaran', 'kasir_pos'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->tokoA->id, 'modul_id' => $m->id, 'aktif' => true]);
            ModulToko::create(['toko_id' => $this->tokoB->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        $this->produkA = Produk::create([
            'toko_id' => $this->tokoA->id,
            'sku' => 'PROD-A',
            'nama' => 'Produk Khusus Alpha',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
        ]);

        $this->produkB = Produk::create([
            'toko_id' => $this->tokoB->id,
            'sku' => 'PROD-B',
            'nama' => 'Produk Khusus Beta',
            'harga_beli' => 20000,
            'harga_jual' => 30000,
        ]);
    }

    public function test_tenant_a_hanya_melihat_produk_miliknya_sendiri(): void
    {
        $response = $this->actingAs($this->userA)->get(route('produk.index'));
        $response->assertOk();
        $response->assertSee('Produk Khusus Alpha');
        $response->assertDontSee('Produk Khusus Beta');
    }

    public function test_tenant_a_tidak_bisa_mengedit_atau_menghapus_produk_tenant_b(): void
    {
        // Mencoba edit produk Toko B
        $resEdit = $this->actingAs($this->userA)->put(route('produk.update', $this->produkB), [
            'sku' => 'PROD-B',
            'nama' => 'Hacked Name',
            'harga_beli' => 1000,
            'harga_jual' => 2000,
        ]);
        $resEdit->assertNotFound();
        $this->assertSame('Produk Khusus Beta', $this->produkB->fresh()->nama);

        // Mencoba delete produk Toko B
        $resDel = $this->actingAs($this->userA)->delete(route('produk.destroy', $this->produkB));
        $resDel->assertNotFound();
        $this->assertDatabaseHas('produk', ['id' => $this->produkB->id]);
    }

    public function test_tenant_a_tidak_bisa_melihat_pengeluaran_tenant_b(): void
    {
        $pengeluaranB = Pengeluaran::create([
            'toko_id' => $this->tokoB->id,
            'pengguna_id' => $this->userB->id,
            'tanggal_pengeluaran' => '2026-08-27',
            'keterangan' => 'Rahasia Belanja Toko Beta',
            'nominal' => 500000,
        ]);

        $response = $this->actingAs($this->userA)->get(route('pengeluaran.index'));
        $response->assertOk();
        $response->assertDontSee('Rahasia Belanja Toko Beta');
    }

    public function test_tenant_a_tidak_bisa_melihat_transaksi_kasir_tenant_b(): void
    {
        $gudangB = Gudang::create(['toko_id' => $this->tokoB->id, 'nama' => 'Etalase B', 'jenis' => 'etalase']);

        $transaksiB = Transaksi::create([
            'toko_id' => $this->tokoB->id,
            'pengguna_id' => $this->userB->id,
            'gudang_id' => $gudangB->id,
            'tanggal_transaksi' => '2026-08-27',
            'subtotal' => 100000,
            'diskon' => 0,
            'total' => 100000,
            'jumlah_bayar' => 100000,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
        ]);

        $response = $this->actingAs($this->userA)->get(route('kasir.show', $transaksiB));
        $response->assertNotFound();
    }
}
