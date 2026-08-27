<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $superadmin;

    private Pengguna $admin;

    private Pengguna $karyawan;

    private Modul $modul;

    protected function setUp(): void
    {
        parent::setUp();

        // Register temporary test routes
        Route::get('/test/superadmin-only', fn () => 'ok')
            ->middleware(['auth', 'peran:superadmin']);

        Route::get('/test/admin-or-karyawan', fn () => 'ok')
            ->middleware(['auth', 'peran:admin,karyawan']);

        Route::get('/test/toko-context', fn () => 'ok')
            ->middleware(['auth', 'konteks_toko']);

        Route::get('/test/modul-kasir', fn () => 'ok')
            ->middleware(['auth', 'konteks_toko', 'modul:kasir_pos']);

        $paket = Paket::create([
            'nama' => 'Paket Basic',
            'jenis' => 'preset_1',
            'harga' => 99000,
        ]);

        $this->toko = Toko::create([
            'nama' => 'Toko Demo',
            'slug' => 'toko-demo',
            'paket_id' => $paket->id,
            'status' => 'aktif',
        ]);

        $this->superadmin = Pengguna::create([
            'nama' => 'Superadmin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'peran' => 'superadmin',
            'toko_id' => null,
        ]);

        $this->admin = Pengguna::create([
            'nama' => 'Admin Toko',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $this->karyawan = Pengguna::create([
            'nama' => 'Kasir Toko',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'sub_peran' => 'kasir',
            'toko_id' => $this->toko->id,
        ]);

        $this->modul = Modul::create([
            'kode' => 'kasir_pos',
            'nama' => 'Kasir POS',
        ]);
    }

    public function test_peran_middleware_mengizinkan_role_yang_sesuai(): void
    {
        $response = $this->actingAs($this->superadmin)->get('/test/superadmin-only');
        $response->assertOk();
        $response->assertSee('ok');
    }

    public function test_peran_middleware_menolak_role_yang_tidak_sesuai(): void
    {
        $response = $this->actingAs($this->admin)->get('/test/superadmin-only');
        $response->assertForbidden();
    }

    public function test_peran_middleware_mendukung_multiple_roles(): void
    {
        $res1 = $this->actingAs($this->admin)->get('/test/admin-or-karyawan');
        $res1->assertOk();

        $res2 = $this->actingAs($this->karyawan)->get('/test/admin-or-karyawan');
        $res2->assertOk();

        $res3 = $this->actingAs($this->superadmin)->get('/test/admin-or-karyawan');
        $res3->assertForbidden();
    }

    public function test_konteks_toko_mengizinkan_user_dengan_toko_id(): void
    {
        $response = $this->actingAs($this->admin)->get('/test/toko-context');
        $response->assertOk();
    }

    public function test_konteks_toko_mengizinkan_superadmin_tanpa_toko_id(): void
    {
        $response = $this->actingAs($this->superadmin)->get('/test/toko-context');
        $response->assertOk();
    }

    public function test_konteks_toko_menolak_non_superadmin_tanpa_toko_id(): void
    {
        $userTanpaToko = Pengguna::create([
            'nama' => 'User Lepas',
            'email' => 'lepas@test.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => null,
        ]);

        $response = $this->actingAs($userTanpaToko)->get('/test/toko-context');
        $response->assertForbidden();
    }

    public function test_cek_modul_mengizinkan_akses_jika_modul_aktif(): void
    {
        ModulToko::create([
            'toko_id' => $this->toko->id,
            'modul_id' => $this->modul->id,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/test/modul-kasir');
        $response->assertOk();
        $response->assertSee('ok');
    }

    public function test_cek_modul_redirect_ke_dashboard_jika_modul_nonaktif(): void
    {
        $response = $this->actingAs($this->admin)->get('/test/modul-kasir');
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }
}
