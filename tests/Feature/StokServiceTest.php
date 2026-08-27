<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Paket;
use App\Models\Produk;
use App\Models\StokGudang;
use App\Models\Toko;
use App\Services\StokService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokServiceTest extends TestCase
{
    use RefreshDatabase;

    private StokService $service;

    private Toko $toko;

    private Produk $produk;

    private Gudang $etalase;

    private Gudang $gudangPusat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(StokService::class);

        $paket = Paket::create([
            'nama' => 'Paket Basic',
            'jenis' => 'preset_2',
            'harga' => 199000,
        ]);

        $this->toko = Toko::create([
            'nama' => 'Toko Demo',
            'slug' => 'toko-demo',
            'paket_id' => $paket->id,
            'status' => 'aktif',
        ]);

        $this->produk = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'PRD001',
            'nama' => 'Kopi Arabika 250g',
            'harga_beli' => 30000,
            'harga_jual' => 50000,
            'stok_minimum' => 5,
        ]);

        $this->etalase = Gudang::create([
            'toko_id' => $this->toko->id,
            'nama' => 'Etalase Depan',
            'jenis' => 'etalase',
        ]);

        $this->gudangPusat = Gudang::create([
            'toko_id' => $this->toko->id,
            'nama' => 'Gudang Pusat',
            'jenis' => 'gudang',
        ]);
    }

    public function test_bisa_menambah_stok_dan_mencatat_pergerakan(): void
    {
        $this->service->tambahStok($this->produk, $this->etalase, 10, 'Inbound', 1, 'Kulakan awal');

        $stok = StokGudang::where('produk_id', $this->produk->id)
            ->where('gudang_id', $this->etalase->id)
            ->first();

        $this->assertNotNull($stok);
        $this->assertSame(10, $stok->jumlah);

        $this->assertDatabaseHas('pergerakan_stok', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->etalase->id,
            'jenis' => 'masuk',
            'jumlah' => 10,
        ]);
    }

    public function test_bisa_mengurangi_stok_saat_transaksi(): void
    {
        $this->service->tambahStok($this->produk, $this->etalase, 10);
        $this->service->kurangiStok($this->produk, $this->etalase, 3, 'Transaksi', 101, 'Kasir POS');

        $stok = StokGudang::where('produk_id', $this->produk->id)
            ->where('gudang_id', $this->etalase->id)
            ->first();

        $this->assertSame(7, $stok->jumlah);

        $this->assertDatabaseHas('pergerakan_stok', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->etalase->id,
            'jenis' => 'penjualan',
            'jumlah' => -3,
        ]);
    }

    public function test_gagal_mengurangi_stok_jika_sisa_stok_tidak_mencukupi(): void
    {
        $this->service->tambahStok($this->produk, $this->etalase, 2);

        $this->expectException(Exception::class);

        $this->service->kurangiStok($this->produk, $this->etalase, 5, 'Transaksi', 102);
    }

    public function test_bisa_transfer_stok_antar_gudang(): void
    {
        $this->service->tambahStok($this->produk, $this->gudangPusat, 20);
        $this->service->transferStok($this->produk, $this->gudangPusat, $this->etalase, 8, 'Restock etalase');

        $stokPusat = StokGudang::where('produk_id', $this->produk->id)->where('gudang_id', $this->gudangPusat->id)->first();
        $stokEtalase = StokGudang::where('produk_id', $this->produk->id)->where('gudang_id', $this->etalase->id)->first();

        $this->assertSame(12, $stokPusat->jumlah);
        $this->assertSame(8, $stokEtalase->jumlah);

        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->gudangPusat->id,
            'gudang_tujuan_id' => $this->etalase->id,
            'jenis' => 'transfer',
            'jumlah' => -8,
        ]);
    }

    public function test_bisa_melakukan_stok_opname_dan_menghitung_selisih(): void
    {
        $this->service->tambahStok($this->produk, $this->etalase, 15);
        $this->service->opname($this->produk, $this->etalase, 12, 'Opname bulanan');

        $stok = StokGudang::where('produk_id', $this->produk->id)->where('gudang_id', $this->etalase->id)->first();
        $this->assertSame(12, $stok->jumlah);

        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->etalase->id,
            'jenis' => 'opname',
            'jumlah' => -3, // 12 - 15 = -3
        ]);
    }
}
