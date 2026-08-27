<?php

namespace Tests\Feature;

use App\Models\KetergantunganModul;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\PaketModul;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminTokoTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $superadmin;

    private Pengguna $adminTenant;

    private Paket $paket;

    private Toko $toko;

    private Modul $modulProduk;

    private Modul $modulStok;

    private Modul $modulKasir;

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
        $this->modulKasir = Modul::create(['kode' => 'kasir_pos', 'nama' => 'Kasir POS']);

        KetergantunganModul::create([
            'modul_id' => $this->modulStok->id,
            'requires_modul_id' => $this->modulProduk->id,
        ]);

        KetergantunganModul::create([
            'modul_id' => $this->modulKasir->id,
            'requires_modul_id' => $this->modulStok->id,
        ]);

        $this->paket = Paket::create([
            'nama' => 'Preset POS',
            'jenis' => 'preset_2',
            'harga' => 199000,
            'aktif' => true,
        ]);
        PaketModul::create(['paket_id' => $this->paket->id, 'modul_id' => $this->modulProduk->id]);

        $this->toko = Toko::create([
            'nama' => 'Toko Demo',
            'slug' => 'toko-demo',
            'paket_id' => $this->paket->id,
            'status' => 'aktif',
        ]);

        $this->adminTenant = Pengguna::create([
            'nama' => 'Admin Toko',
            'email' => 'admin@tokodemo.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);
    }

    public function test_superadmin_bisa_melihat_daftar_toko(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('superadmin.toko.index'));
        $response->assertOk();
        $response->assertSee('Toko Demo');
    }

    public function test_non_superadmin_dilarang_mengakses_panel_superadmin(): void
    {
        $response = $this->actingAs($this->adminTenant)->get(route('superadmin.toko.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_bisa_membuat_toko_baru_dan_sinkronisasi_modul(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.toko.store'), [
            'nama' => 'Toko Berkah Baru',
            'paket_id' => $this->paket->id,
            'admin_nama' => 'Budi Santoso',
            'admin_email' => 'budi@berkah.com',
            'admin_password' => 'password123',
            'radius_absensi' => 150,
        ]);

        $response->assertRedirect(route('superadmin.toko.index'));
        $this->assertDatabaseHas('toko', ['nama' => 'Toko Berkah Baru']);
        $this->assertDatabaseHas('pengguna', ['email' => 'budi@berkah.com', 'peran' => 'admin']);

        $tokoBaru = Toko::where('nama', 'Toko Berkah Baru')->first();
        $this->assertTrue($tokoBaru->modulAktif('master_produk'));
    }

    public function test_superadmin_bisa_toggle_modul_aktif_dan_nonaktif(): void
    {
        // Aktifkan modul master_produk
        $res1 = $this->actingAs($this->superadmin)
            ->post(route('superadmin.toko.modul.aktifkan', [$this->toko, 'master_produk']));
        $res1->assertRedirect();
        $this->assertTrue($this->toko->fresh()->modulAktif('master_produk'));

        // Matikan modul master_produk
        $res2 = $this->actingAs($this->superadmin)
            ->post(route('superadmin.toko.modul.nonaktifkan', [$this->toko, 'master_produk']));
        $res2->assertRedirect();
        $this->assertFalse($this->toko->fresh()->modulAktif('master_produk'));
    }

    public function test_superadmin_bisa_mengaktifkan_modul_beserta_dependensinya(): void
    {
        // kasir_pos butuh stok_gudang, stok_gudang butuh master_produk
        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.toko.modul.aktifkan', [$this->toko, 'kasir_pos']), [
                'dengan_dependency' => '1',
            ]);

        $response->assertRedirect();
        $this->assertTrue($this->toko->fresh()->modulAktif('master_produk'));
        $this->assertTrue($this->toko->fresh()->modulAktif('stok_gudang'));
        $this->assertTrue($this->toko->fresh()->modulAktif('kasir_pos'));
    }
}
