<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Paket;
use App\Models\Pengeluaran;
use App\Models\PenjualanSederhana;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaketSatuTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $toko = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 1)->first()->id]);
        $this->admin = User::factory()->create([
            'toko_id' => $toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);
    }

    public function test_dapat_mencatat_pengeluaran(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal_pengeluaran' => now()->toDateString(),
                'keterangan' => 'Beli gula pasir 10 kg',
                'nominal' => 150000,
            ]);

        $response->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseHas('pengeluaran', [
            'toko_id' => $this->admin->toko_id,
            'keterangan' => 'Beli gula pasir 10 kg',
            'nominal' => 150000,
        ]);
    }

    public function test_validasi_nominal_wajib_ada(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal_pengeluaran' => now()->toDateString(),
                'keterangan' => 'Tanpa nominal',
            ])
            ->assertSessionHasErrors('nominal');
    }

    public function test_dapat_mencatat_penjualan_ringkas_multi_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('penjualan-sederhana.store'), [
                'tanggal_penjualan' => now()->toDateString(),
                'barang' => [
                    ['nama_barang' => 'Kopi Sachet', 'jumlah' => 3, 'harga_satuan' => 2000],
                    ['nama_barang' => 'Roti Tawar', 'jumlah' => 1, 'harga_satuan' => 14000],
                ],
            ]);

        $response->assertRedirect(route('penjualan-sederhana.index'));

        $penjualan = PenjualanSederhana::first();
        $this->assertSame(20000.0, (float) $penjualan->total);
        $this->assertCount(2, $penjualan->item);
    }

    public function test_rekap_menampilkan_laba_kotor(): void
    {
        // Penjualan 100.000, pengeluaran 30.000 → laba 70.000
        $penjualan = PenjualanSederhana::factory()->create([
            'toko_id' => $this->admin->toko_id,
            'pengguna_id' => $this->admin->id,
            'tanggal_penjualan' => now()->toDateString(),
        ]);
        $penjualan->item()->delete();
        $penjualan->update(['total' => 100000]);
        Pengeluaran::factory()->create([
            'toko_id' => $this->admin->toko_id,
            'pengguna_id' => $this->admin->id,
            'tanggal_pengeluaran' => now()->toDateString(),
            'nominal' => 30000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('rekap.index'));

        $response->assertOk()
            ->assertSee('70.000')
            ->assertSee('100.000')
            ->assertSee('30.000');
    }

    public function test_data_tenant_terisolasi(): void
    {
        $tokoLain = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 1)->first()->id]);

        Pengeluaran::factory()->create([
            'toko_id' => $tokoLain->id,
            'pengguna_id' => $this->admin->id,
            'keterangan' => 'Pengeluaran toko lain',
        ]);

        $response = $this->actingAs($this->admin)->get(route('pengeluaran.index'));

        $response->assertOk()
            ->assertDontSee('Pengeluaran toko lain');
    }

    public function test_addon_absensi_belum_aktif_tidak_memengaruhi_fitur_paket_1(): void
    {
        $addon = Addon::where('kode', 'absensi')->first();

        $this->assertFalse($this->admin->toko->punyaAddon($addon->kode));

        $this->actingAs($this->admin)
            ->get(route('rekap.index'))
            ->assertOk();
    }
}
