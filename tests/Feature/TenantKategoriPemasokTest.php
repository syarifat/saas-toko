<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pemasok;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantKategoriPemasokTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $this->toko = Toko::create(['nama' => 'Toko KP', 'slug' => 'toko-kp', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Admin KP',
            'email' => 'admin@kp.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $m = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
    }

    public function test_tenant_bisa_membuat_dan_menghapus_kategori(): void
    {
        $response = $this->actingAs($this->user)->post(route('kategori.store'), [
            'nama' => 'Minuman Dingin',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategori', ['toko_id' => $this->toko->id, 'nama' => 'Minuman Dingin']);

        $kategori = Kategori::where('nama', 'Minuman Dingin')->first();
        $del = $this->actingAs($this->user)->delete(route('kategori.destroy', $kategori));
        $del->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    public function test_tenant_bisa_membuat_dan_menghapus_pemasok(): void
    {
        $response = $this->actingAs($this->user)->post(route('pemasok.store'), [
            'nama' => 'CV Sumber Makmur',
            'telepon' => '08123456789',
            'alamat' => 'Jl. Merdeka No. 10',
        ]);
        $response->assertRedirect(route('pemasok.index'));
        $this->assertDatabaseHas('pemasok', [
            'toko_id' => $this->toko->id,
            'nama' => 'CV Sumber Makmur',
            'telepon' => '08123456789',
        ]);

        $pemasok = Pemasok::where('nama', 'CV Sumber Makmur')->first();
        $del = $this->actingAs($this->user)->delete(route('pemasok.destroy', $pemasok));
        $del->assertRedirect(route('pemasok.index'));
        $this->assertDatabaseMissing('pemasok', ['id' => $pemasok->id]);
    }
}
