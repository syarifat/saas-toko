<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Paket;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->superadmin = User::where('peran', 'superadmin')->first();
    }

    public function test_superadmin_dapat_mengakses_panel_dashboard(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('Panel Superadmin');
    }

    public function test_non_superadmin_ditolak_akses_panel_superadmin(): void
    {
        $toko = Toko::factory()->create();

        $admin = User::factory()->create([
            'toko_id' => $toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
            'email' => 'admin@tokotest.test',
        ]);

        $this->actingAs($admin)
            ->get(route('superadmin.dashboard'))
            ->assertForbidden();
    }

    public function test_superadmin_dapat_mendaftarkan_toko_baru_beserta_adminnya(): void
    {
        $paket = Paket::first();

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.toko.store'), [
                'nama' => 'Toko Maju Jaya',
                'paket_id' => $paket->id,
                'status' => 'aktif',
                'nama_admin' => 'Budi Owner',
                'email_admin' => 'budi@majujaya.test',
                'password_admin' => 'password123',
            ]);

        $response->assertRedirect(route('superadmin.toko.index'));

        $this->assertTrue(Toko::where('nama', 'Toko Maju Jaya')->exists());
        $this->assertTrue(User::where('email', 'budi@majujaya.test')->where('peran', 'admin')->exists());
    }

    public function test_superadmin_dapat_mengubah_paket_toko(): void
    {
        $toko = Toko::factory()->create();
        $paketBaru = Paket::where('tingkat', 3)->first();

        $this->actingAs($this->superadmin)
            ->put(route('superadmin.toko.update', $toko), [
                'nama' => $toko->nama,
                'paket_id' => $paketBaru->id,
                'status' => 'aktif',
            ])
            ->assertRedirect(route('superadmin.toko.index'));

        $this->assertSame($paketBaru->id, $toko->fresh()->paket_id);
    }

    public function test_superadmin_dapat_toggle_addon_untuk_toko(): void
    {
        $toko = Toko::factory()->create();
        $addon = Addon::first();

        $this->actingAs($this->superadmin)
            ->post(route('superadmin.toko.addon.toggle', [$toko, $addon]))
            ->assertRedirect();

        $this->assertTrue($toko->punyaAddon($addon->kode));

        $this->post(route('superadmin.toko.addon.toggle', [$toko, $addon]));

        $this->assertFalse($toko->punyaAddon($addon->kode));
    }

    public function test_helper_paket_dan_addon_bekerja(): void
    {
        $toko = Toko::factory()->create(['paket_id' => Paket::where('tingkat', 2)->first()->id]);

        $this->assertTrue($toko->setidaknyaPaket(2));
        $this->assertFalse($toko->setidaknyaPaket(3));
        $this->assertFalse($toko->punyaAddon('absensi'));
    }
}
