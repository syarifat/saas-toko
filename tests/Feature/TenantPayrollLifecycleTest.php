<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\KomponenGaji;
use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Penggajian;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPayrollLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $admin;

    private Pengguna $staff;

    private Karyawan $karyawan;

    private Penggajian $penggajian;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Enterprise', 'jenis' => 'preset_3', 'harga' => 399000]);
        $this->toko = Toko::create(['nama' => 'Toko Gaji', 'slug' => 'toko-gaji', 'paket_id' => $paket->id, 'status' => 'aktif']);

        $this->admin = Pengguna::create([
            'nama' => 'Owner Gaji',
            'email' => 'owner@gaji.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $this->staff = Pengguna::create([
            'nama' => 'Staf Gaji',
            'email' => 'staf@gaji.com',
            'password' => bcrypt('password'),
            'peran' => 'karyawan',
            'toko_id' => $this->toko->id,
        ]);

        foreach (['karyawan', 'payroll'] as $kode) {
            $m = Modul::create(['kode' => $kode, 'nama' => ucfirst($kode)]);
            ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        $this->karyawan = Karyawan::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->staff->id,
            'kode_karyawan' => 'KRJ-099',
            'skema_gaji' => 'bulanan',
            'gaji_pokok' => 3000000,
            'tanggal_masuk' => '2026-01-01',
            'aktif' => true,
        ]);

        $this->penggajian = Penggajian::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $this->karyawan->id,
            'periode_mulai' => '2026-08-01',
            'periode_selesai' => '2026-08-31',
            'skema_gaji_snapshot' => 'bulanan',
            'jumlah_dasar' => 3000000,
            'total_tunjangan' => 500000,
            'total_potongan' => 50000,
            'gaji_bersih' => 3450000,
            'status' => 'draf',
        ]);

        KomponenGaji::create([
            'toko_id' => $this->toko->id,
            'penggajian_id' => $this->penggajian->id,
            'jenis' => 'tunjangan',
            'nama' => 'Tunjangan Kinerja',
            'nominal' => 500000,
        ]);
    }

    public function test_admin_bisa_melihat_slip_gaji_dan_melunasi_status_pembayaran(): void
    {
        // Lihat Slip Gaji
        $resSlip = $this->actingAs($this->admin)->get(route('penggajian.slip', $this->penggajian));
        $resSlip->assertOk();
        $resSlip->assertSee('Staf Gaji');
        $resSlip->assertSee('3.450.000');

        // Bayar Gaji
        $resBayar = $this->actingAs($this->admin)->post(route('penggajian.bayar', $this->penggajian));
        $resBayar->assertRedirect();
        $this->assertSame('dibayar', $this->penggajian->fresh()->status);
        $this->assertNotNull($this->penggajian->fresh()->dibayar_pada);
    }
}
