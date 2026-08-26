<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Paket;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaketDuaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Toko $toko;

    private Gudang $gudang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->toko = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 2)->first()->id]);
        $this->admin = User::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);
        $this->gudang = $this->toko->gudangUtama();
    }

    public function test_dapat_menambah_produk_dengan_stok_awal(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('produk.store'), [
                'sku' => 'ABC1234',
                'nama' => 'Indomie Goreng',
                'harga_beli' => 2800,
                'harga_jual' => 3500,
                'stok_minimum' => 10,
                'stok_awal' => 100,
            ]);

        $response->assertRedirect(route('produk.index'));

        $produk = Produk::where('sku', 'ABC1234')->first();
        $this->assertNotNull($produk);
        $this->assertSame(100, $produk->totalStok());

        // Pergerakan stok awal tercatat
        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $produk->id,
            'jenis' => 'masuk',
            'jumlah' => 100,
        ]);
    }

    public function test_sku_harus_unik_per_toko(): void
    {
        Produk::factory()->create(['toko_id' => $this->toko->id, 'sku' => 'UNIQ001']);

        $this->actingAs($this->admin)
            ->post(route('produk.store'), [
                'sku' => 'UNIQ001',
                'nama' => 'Duplikat',
                'harga_beli' => 1000,
                'harga_jual' => 2000,
                'stok_minimum' => 1,
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_kasir_mendeduct_stok_otomatis(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id, 'harga_jual' => 5000]);
        app(StokService::class)->masuk($produk, $this->gudang, 50, $this->admin->id);

        $response = $this->actingAs($this->admin)
            ->post(route('kasir.store'), [
                'gudang_id' => $this->gudang->id,
                'metode_pembayaran' => 'tunai',
                'diskon' => 0,
                'jumlah_bayar' => 15000,
                'barang' => [
                    ['produk_id' => $produk->id, 'jumlah' => 3],
                ],
            ]);

        $response->assertRedirect(route('kasir.index'));

        $produk->refresh();
        $this->assertSame(47, $produk->totalStok());

        $transaksi = Transaksi::first();
        $this->assertSame(15000.0, (float) $transaksi->total);
        $this->assertSame(0.0, (float) $transaksi->kembalian);
        $this->assertCount(1, $transaksi->item);

        // Pergerakan penjualan tercatat dengan referensi transaksi
        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $produk->id,
            'jenis' => 'penjualan',
            'jumlah' => -3,
            'referensi_tipe' => Transaksi::class,
            'referensi_id' => $transaksi->id,
        ]);
    }

    public function test_kasir_menolak_stok_tidak_cukup(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        app(StokService::class)->masuk($produk, $this->gudang, 2, $this->admin->id);

        $this->actingAs($this->admin)
            ->post(route('kasir.store'), [
                'gudang_id' => $this->gudang->id,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => 999999,
                'barang' => [
                    ['produk_id' => $produk->id, 'jumlah' => 10],
                ],
            ])
            ->assertSessionHasErrors('stok');

        $this->assertSame(2, $produk->totalStok());
        $this->assertSame(0, Transaksi::count());
    }

    public function test_fitur_paket_2_terblokir_untuk_tenant_paket_1(): void
    {
        $tokoPaketSatu = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 1)->first()->id]);
        $adminPaketSatu = User::factory()->create([
            'toko_id' => $tokoPaketSatu->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);

        $this->actingAs($adminPaketSatu)
            ->get(route('produk.index'))
            ->assertForbidden();

        $this->actingAs($adminPaketSatu)
            ->get(route('kasir.index'))
            ->assertForbidden();
    }

    public function test_stok_opname_menyesuaikan_selisih(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        app(StokService::class)->masuk($produk, $this->gudang, 50, $this->admin->id);

        $this->actingAs($this->admin)
            ->post(route('stok-opname.store'), [
                'gudang_id' => $this->gudang->id,
                'opname' => [
                    ['produk_id' => $produk->id, 'jumlah_fisik' => 45],
                ],
            ])
            ->assertRedirect(route('stok-opname.index'));

        $this->assertSame(45, $produk->totalStok());

        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $produk->id,
            'jenis' => 'opname',
            'jumlah' => -5,
        ]);
    }

    public function test_stock_alert_muncul_di_dashboard(): void
    {
        $produk = Produk::factory()->create([
            'toko_id' => $this->toko->id,
            'nama' => 'Produk Menipis ABC',
            'stok_minimum' => 10,
        ]);
        app(StokService::class)->masuk($produk, $this->gudang, 3, $this->admin->id);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Stok Menipis')
            ->assertSee('Produk Menipis ABC');
    }
}
