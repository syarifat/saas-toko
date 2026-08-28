<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantHakAksesKaryawanTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $admin;

    private Pengguna $kasir;

    private Karyawan $karyawanKasir;

    private Modul $modulKasir;

    private Modul $modulProduk;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Paket 2', 'jenis' => 'preset_2', 'harga' => 199000]);
        $this->toko = Toko::create(['nama' => 'Toko Demo', 'slug' => 'toko-demo', 'paket_id' => $paket->id, 'status' => 'aktif']);

        $this->admin = Pengguna::create([
            'nama' => 'Pemilik Toko',
            'email' => 'admin@demo.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
            'aktif' => true,
        ]);

        $this->kasir = Pengguna::create([
            'nama' => 'Kasir Toko',
            'email' => 'kasir@demo.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'sub_peran' => 'kasir',
            'toko_id' => $this->toko->id,
            'aktif' => true,
        ]);

        $this->karyawanKasir = Karyawan::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->kasir->id,
            'kode_karyawan' => 'KASIR-01',
            'posisi' => 'Kasir',
            'tanggal_masuk' => now(),
            'aktif' => true,
        ]);

        $this->modulKasir = Modul::create(['kode' => 'kasir_pos', 'nama' => 'Kasir POS']);
        $this->modulProduk = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);

        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $this->modulKasir->id, 'aktif' => true]);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $this->modulProduk->id, 'aktif' => true]);
    }

    public function test_admin_toko_bisa_membuka_halaman_hak_akses_karyawan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('karyawan.hak-akses'));
        $response->assertOk();
        $response->assertSee('Hak Akses Menu Staf Karyawan');
        $response->assertSee('Kasir POS');
        $response->assertSee('Master Produk');
    }

    public function test_karyawan_tidak_bisa_membuka_halaman_pengaturan_hak_akses(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('karyawan.hak-akses'));
        $response->assertForbidden();
    }

    public function test_admin_bisa_mengatur_dan_membatasi_menu_yang_bisa_diakses_kasir(): void
    {
        // 1. Matikan akses 'master_produk' untuk kasir, hanya izinkan 'kasir_pos'
        $response = $this->actingAs($this->admin)->post(route('karyawan.hak-akses.simpan'), [
            'akses' => [
                $this->kasir->id => [$this->modulKasir->id], // Hanya kasir_pos
            ],
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi di DB pivot
        $this->assertDatabaseHas('akses_modul_pengguna', [
            'pengguna_id' => $this->kasir->id,
            'modul_id' => $this->modulKasir->id,
        ]);
        $this->assertDatabaseMissing('akses_modul_pengguna', [
            'pengguna_id' => $this->kasir->id,
            'modul_id' => $this->modulProduk->id,
        ]);

        // 2. Kasir login: Bisa akses Kasir POS
        $resKasirPOS = $this->actingAs($this->kasir)->get(route('kasir.index'));
        $resKasirPOS->assertOk();

        // 3. Kasir login: DILARANG akses Master Produk (Redirect ke dashboard dengan pesan error)
        $resProduk = $this->actingAs($this->kasir)->get(route('produk.index'));
        $resProduk->assertRedirect(route('dashboard'));
        $resProduk->assertSessionHas('error', 'Anda tidak memiliki hak akses untuk membuka menu ini.');

        // 4. Admin menyalakan kembali akses Master Produk untuk kasir
        $this->actingAs($this->admin)->post(route('karyawan.hak-akses.simpan'), [
            'akses' => [
                $this->kasir->id => [$this->modulKasir->id, $this->modulProduk->id],
            ],
        ]);

        // 5. Kasir sekarang bisa akses Master Produk
        $resProdukAllowed = $this->actingAs($this->kasir->fresh())->get(route('produk.index'));
        $resProdukAllowed->assertOk();
    }
}
