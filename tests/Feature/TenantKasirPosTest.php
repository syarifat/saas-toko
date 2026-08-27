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

class TenantKasirPosTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    private Gudang $gudang;

    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        $this->toko = Toko::create(['nama' => 'Toko Mart', 'slug' => 'toko-mart', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Kasir Mart',
            'email' => 'kasir@mart.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'sub_peran' => 'kasir',
            'toko_id' => $this->toko->id,
        ]);

        $m1 = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        $m2 = Modul::create(['kode' => 'stok_gudang', 'nama' => 'Manajemen Stok']);
        $m3 = Modul::create(['kode' => 'kasir_pos', 'nama' => 'Kasir POS']);

        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m1->id, 'aktif' => true]);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m2->id, 'aktif' => true]);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m3->id, 'aktif' => true]);

        $this->gudang = Gudang::create([
            'toko_id' => $this->toko->id,
            'nama' => 'Etalase Depan',
            'jenis' => 'etalase',
        ]);

        $this->produk = Produk::create([
            'toko_id' => $this->toko->id,
            'sku' => 'MINUM-01',
            'nama' => 'Teh Botol 450ml',
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok_minimum' => 5,
        ]);

        StokGudang::create([
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'gudang_id' => $this->gudang->id,
            'jumlah' => 20,
        ]);
    }

    public function test_transaksi_kasir_pos_berhasil_memotong_stok_dan_mencatat_hpp(): void
    {
        $cartData = [
            [
                'id' => $this->produk->id,
                'qty' => 3,
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('kasir.store'), [
            'gudang_id' => $this->gudang->id,
            'cart_data' => json_encode($cartData),
            'diskon' => 1000,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 20000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', [
            'toko_id' => $this->toko->id,
            'subtotal' => 15000,
            'diskon' => 1000,
            'total' => 14000,
            'jumlah_bayar' => 20000,
            'kembalian' => 6000,
        ]);

        $this->assertDatabaseHas('item_transaksi', [
            'toko_id' => $this->toko->id,
            'produk_id' => $this->produk->id,
            'jumlah' => 3,
            'harga_satuan' => 5000,
            'harga_beli_snapshot' => 3000,
        ]);

        // Sisa stok harus berkurang dari 20 menjadi 17
        $stokTersisa = StokGudang::where('produk_id', $this->produk->id)
            ->where('gudang_id', $this->gudang->id)
            ->value('jumlah');

        $this->assertSame(17, $stokTersisa);
    }

    public function test_transaksi_pos_gagal_jika_stok_tidak_mencukupi(): void
    {
        $cartData = [
            [
                'id' => $this->produk->id,
                'qty' => 50, // Melebihi stok 20
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('kasir.store'), [
            'gudang_id' => $this->gudang->id,
            'cart_data' => json_encode($cartData),
            'diskon' => 0,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 300000,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('transaksi', 0);
    }
}
