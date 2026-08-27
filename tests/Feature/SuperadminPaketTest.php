<?php

namespace Tests\Feature;

use App\Models\KetergantunganModul;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminPaketTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $superadmin;

    private Modul $modulProduk;

    private Modul $modulStok;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = Pengguna::create([
            'nama' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'peran' => 'superadmin',
            'toko_id' => null,
        ]);

        $this->modulProduk = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        $this->modulStok = Modul::create(['kode' => 'stok_gudang', 'nama' => 'Manajemen Stok']);

        KetergantunganModul::create([
            'modul_id' => $this->modulStok->id,
            'requires_modul_id' => $this->modulProduk->id,
        ]);
    }

    public function test_superadmin_bisa_membuat_paket_dengan_modul_valid(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.paket.store'), [
            'nama' => 'Paket Spesial',
            'jenis' => 'custom',
            'harga' => 150000,
            'deskripsi' => 'Paket custom lengkap',
            'aktif' => 1,
            'modul_ids' => [$this->modulProduk->id, $this->modulStok->id],
        ]);

        $response->assertRedirect(route('superadmin.paket.index'));
        $this->assertDatabaseHas('paket', ['nama' => 'Paket Spesial']);

        $paket = Paket::where('nama', 'Paket Spesial')->first();
        $this->assertSame(2, $paket->modul()->count());
    }

    public function test_gagal_membuat_paket_jika_ada_modul_yang_dependensinya_tidak_dipilih(): void
    {
        // stok_gudang membutuhkan master_produk, tapi master_produk tidak disertakan
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.paket.store'), [
            'nama' => 'Paket Invalid',
            'jenis' => 'custom',
            'harga' => 100000,
            'modul_ids' => [$this->modulStok->id],
        ]);

        $response->assertSessionHasErrors('modul_ids');
        $this->assertDatabaseMissing('paket', ['nama' => 'Paket Invalid']);
    }
}
