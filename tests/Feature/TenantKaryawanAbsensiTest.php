<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantKaryawanAbsensiTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $admin;

    private Pengguna $stafUser;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Enterprise', 'jenis' => 'preset_3', 'harga' => 399000]);
        // Toko di Monas Jakarta
        $this->toko = Toko::create([
            'nama' => 'Toko Monas',
            'slug' => 'toko-monas',
            'paket_id' => $paket->id,
            'status' => 'aktif',
            'garis_lintang' => -6.175392,
            'garis_bujur' => 106.827153,
            'radius_absensi' => 200, // 200 meter
        ]);

        $this->admin = Pengguna::create([
            'nama' => 'Owner Monas',
            'email' => 'owner@monas.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        foreach (['karyawan', 'absensi', 'payroll'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        $this->stafUser = Pengguna::create([
            'nama' => 'Andi Staff',
            'email' => 'andi@monas.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'toko_id' => $this->toko->id,
        ]);

        $this->karyawan = Karyawan::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->stafUser->id,
            'kode_karyawan' => 'KRJ-001',
            'posisi' => 'Staff Toko',
            'skema_gaji' => 'harian',
            'tarif_harian' => 100000,
            'tanggal_masuk' => '2026-01-01',
            'aktif' => true,
        ]);
    }

    public function test_absensi_masuk_berhasil_jika_di_dalam_radius_geofence(): void
    {
        // Koordinat sangat dekat (jarak ~10m)
        $response = $this->actingAs($this->stafUser)->post(route('absensi.masuk'), [
            'lintang' => -6.175400,
            'bujur' => 106.827160,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('absensi', [
            'toko_id' => $this->toko->id,
            'karyawan_id' => $this->karyawan->id,
            'tanggal' => today()->format('Y-m-d'),
        ]);
    }

    public function test_absensi_masuk_gagal_jika_di_luar_radius_geofence(): void
    {
        // Koordinat jauh di Bandung (-6.917464, 107.619123)
        $response = $this->actingAs($this->stafUser)->post(route('absensi.masuk'), [
            'lintang' => -6.917464,
            'bujur' => 107.619123,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('absensi', [
            'karyawan_id' => $this->karyawan->id,
        ]);
    }

    public function test_generate_payroll_menghitung_kehadiran_karyawan(): void
    {
        // Buat 3 catatan absensi
        Absensi::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $this->karyawan->id,
            'tanggal' => '2026-08-01',
            'jam_masuk' => '2026-08-01 08:00:00',
            'jam_keluar' => '2026-08-01 17:00:00',
            'status' => 'tepat_waktu',
        ]);
        Absensi::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $this->karyawan->id,
            'tanggal' => '2026-08-02',
            'jam_masuk' => '2026-08-02 08:00:00',
            'jam_keluar' => '2026-08-02 17:00:00',
            'status' => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->admin)->post(route('penggajian.generate'), [
            'periode_mulai' => '2026-08-01',
            'periode_selesai' => '2026-08-31',
        ]);

        $response->assertRedirect();
        // 2 hari x Rp 100.000 = Rp 200.000
        $this->assertDatabaseHas('penggajian', [
            'toko_id' => $this->toko->id,
            'karyawan_id' => $this->karyawan->id,
            'gaji_bersih' => 200000,
            'status' => 'draf',
        ]);
    }
}
