<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Paket;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\User;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaketTigaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Toko $toko;

    private Gudang $etalase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->toko = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 3)->first()->id]);
        $this->admin = User::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);
        $this->etalase = $this->toko->gudangUtama();
        $this->actingAs($this->admin);
    }

    private function buatGudang(string $nama = 'Gudang Utama'): Gudang
    {
        return $this->toko->gudang()->create(['nama' => $nama, 'jenis' => 'gudang']);
    }

    public function test_dapat_menambah_gudang_baru(): void
    {
        $this->post(route('gudang.store'), [
            'nama' => 'Gudang Cabang B',
            'jenis' => 'gudang',
        ])->assertRedirect();

        $this->assertDatabaseHas('gudang', [
            'toko_id' => $this->toko->id,
            'nama' => 'Gudang Cabang B',
            'jenis' => 'gudang',
        ]);
    }

    public function test_gudang_berisi_stok_tidak_bisa_dihapus(): void
    {
        $gudang = $this->buatGudang();
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        app(StokService::class)->masuk($produk, $gudang, 10, $this->admin->id);

        $this->delete(route('gudang.destroy', $gudang))
            ->assertSessionHasErrors('gudang');

        $this->assertDatabaseHas('gudang', ['id' => $gudang->id]);
    }

    public function test_barang_masuk_menambah_stok(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        $gudang = $this->buatGudang();

        $this->post(route('barang-masuk.store'), [
            'produk_id' => $produk->id,
            'gudang_id' => $gudang->id,
            'jumlah' => 100,
            'catatan' => 'PO dari Pemasok A',
        ])->assertRedirect(route('barang-masuk.index'));

        $this->assertSame(100, $produk->totalStok());
        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $produk->id,
            'jenis' => 'masuk',
            'jumlah' => 100,
        ]);
    }

    public function test_transfer_antar_gudang_memindahkan_stok(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        $stokService = app(StokService::class);
        $stokService->masuk($produk, $this->etalase, 50, $this->admin->id);
        $gudangTujuan = $this->buatGudang();

        $this->post(route('transfer-stok.store'), [
            'produk_id' => $produk->id,
            'gudang_asal_id' => $this->etalase->id,
            'gudang_tujuan_id' => $gudangTujuan->id,
            'jumlah' => 20,
        ])->assertRedirect(route('transfer-stok.index'));

        $this->assertSame(30, $stokService->stok($produk, $this->etalase)->jumlah);
        $this->assertSame(20, $stokService->stok($produk, $gudangTujuan)->jumlah);
        $this->assertSame(50, $produk->totalStok());

        // Satu baris pergerakan transfer dengan referensi dua gudang
        $this->assertDatabaseHas('pergerakan_stok', [
            'produk_id' => $produk->id,
            'jenis' => 'transfer',
            'jumlah' => -20,
            'gudang_id' => $this->etalase->id,
            'gudang_tujuan_id' => $gudangTujuan->id,
        ]);
    }

    public function test_transfer_ditolak_jika_stok_asal_kurang(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        $stokService = app(StokService::class);
        $stokService->masuk($produk, $this->etalase, 5, $this->admin->id);
        $gudangTujuan = $this->buatGudang();

        $this->post(route('transfer-stok.store'), [
            'produk_id' => $produk->id,
            'gudang_asal_id' => $this->etalase->id,
            'gudang_tujuan_id' => $gudangTujuan->id,
            'jumlah' => 10,
        ])->assertSessionHasErrors('stok');

        // Tidak ada stok yang berpindah
        $this->assertSame(5, $produk->totalStok());
    }

    public function test_transfer_ke_gudang_yang_sama_ditolak(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);

        // Validasi different:gudang_asal_id menolak sebelum sampai service
        $this->post(route('transfer-stok.store'), [
            'produk_id' => $produk->id,
            'gudang_asal_id' => $this->etalase->id,
            'gudang_tujuan_id' => $this->etalase->id,
            'jumlah' => 1,
        ])->assertSessionHasErrors('gudang_tujuan_id');
    }

    public function test_kartu_stok_menampilkan_riwayat_pergerakan(): void
    {
        $produk = Produk::factory()->create(['toko_id' => $this->toko->id]);
        $stokService = app(StokService::class);
        $stokService->masuk($produk, $this->etalase, 30, $this->admin->id);
        $stokService->opname($produk, $this->etalase, 28, $this->admin->id);

        $response = $this->get(route('kartu-stok.index', ['produk_id' => $produk->id]));

        $response->assertOk()
            ->assertSee($produk->nama)
            ->assertSee('Total stok: 28');
    }

    public function test_fitur_paket_3_terblokir_untuk_tenant_paket_2(): void
    {
        $tokoPaketDua = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 2)->first()->id]);
        $adminPaketDua = User::factory()->create([
            'toko_id' => $tokoPaketDua->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);

        $this->actingAs($adminPaketDua)
            ->post(route('barang-masuk.store'), [
                'produk_id' => 1,
                'gudang_id' => 1,
                'jumlah' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($adminPaketDua)
            ->get(route('kartu-stok.index'))
            ->assertForbidden();
    }
}
