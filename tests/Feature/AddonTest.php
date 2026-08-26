<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Addon;
use App\Models\AddonToko;
use App\Models\Karyawan;
use App\Models\Paket;
use App\Models\Penggajian;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Toko $toko;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->toko = Toko::factory()->create([
            'paket_id' => Paket::where('tingkat', 1)->first()->id,
            'garis_lintang' => -6.2000000,
            'garis_bujur' => 106.8166667,
            'radius_absensi' => 100,
        ]);
        $this->admin = User::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);
        $this->actingAs($this->admin);

        // Profil karyawan milik admin (dibutuhkan untuk absensi)
        Karyawan::factory()->create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->admin->id,
            'nama' => $this->admin->name,
        ]);
    }

    public function aktifkanAddon(string $kode): void
    {
        AddonToko::create([
            'toko_id' => $this->toko->id,
            'addon_id' => Addon::where('kode', $kode)->firstOrFail()->id,
            'aktif' => true,
            'diaktifkan_pada' => now(),
        ]);
    }

    public function test_dapat_menambah_karyawan_harian_dan_bulanan(): void
    {
        $this->aktifkanAddon('penggajian');

        $this->post(route('karyawan.store'), [
            'nama' => 'Siti Kasir',
            'posisi' => 'Kasir',
            'skema_gaji' => 'harian',
            'tarif_harian' => 80000,
            'tanggal_masuk' => now()->toDateString(),
        ])->assertRedirect(route('karyawan.index'));

        $this->post(route('karyawan.store'), [
            'nama' => 'Budi Gudang',
            'posisi' => 'Gudang',
            'skema_gaji' => 'bulanan',
            'gaji_pokok' => 3000000,
            'tanggal_masuk' => now()->toDateString(),
        ])->assertRedirect(route('karyawan.index'));

        $this->assertDatabaseHas('karyawan', ['nama' => 'Siti Kasir', 'tarif_harian' => 80000]);
        $this->assertDatabaseHas('karyawan', ['nama' => 'Budi Gudang', 'gaji_pokok' => 3000000]);
    }

    public function test_absensi_terblokir_tanpa_addon(): void
    {
        $this->get(route('absensi.index'))->assertForbidden();
    }

    public function test_clock_in_dalam_radius_berhasil(): void
    {
        $this->aktifkanAddon('absensi');

        // Titik ~30 m dari koordinat toko
        $response = $this->post(route('absensi.clock-in'), [
            'lintang' => -6.1997300,
            'bujur' => 106.8166667,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('absensi', [
            'toko_id' => $this->toko->id,
            'status' => 'tepat_waktu',
        ]);
    }

    public function test_clock_in_di_luar_radius_ditolak(): void
    {
        $this->aktifkanAddon('absensi');

        // Titik ~1,1 km dari toko
        $response = $this->from(route('absensi.index'))
            ->post(route('absensi.clock-in'), [
                'lintang' => -6.1900000,
                'bujur' => 106.8166667,
            ]);

        $response->assertSessionHasErrors('absensi');
        $this->assertSame(0, Absensi::count());
    }

    public function test_clock_in_lengkap_dengan_foto(): void
    {
        Storage::fake('public');
        $this->aktifkanAddon('absensi');

        $this->post(route('absensi.clock-in'), [
            'lintang' => -6.2000000,
            'bujur' => 106.8166667,
            'foto' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $absensi = Absensi::first();
        $this->assertNotNull($absensi->foto_masuk);
        Storage::disk('public')->assertExists($absensi->foto_masuk);
    }

    public function test_clock_in_ganda_ditolak(): void
    {
        $this->aktifkanAddon('absensi');
        $payload = ['lintang' => -6.2000000, 'bujur' => 106.8166667];

        $this->post(route('absensi.clock-in'), $payload);
        $this->from(route('absensi.index'))->post(route('absensi.clock-in'), $payload)
            ->assertSessionHasErrors('absensi');

        $this->assertSame(1, Absensi::count());
    }

    public function test_clock_out_menghitung_lembur(): void
    {
        $this->aktifkanAddon('absensi');

        $karyawan = Karyawan::where('pengguna_id', $this->admin->id)->first();

        Absensi::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $karyawan->id,
            'tanggal' => today(),
            'jam_masuk' => today()->setTime(8, 0),
            'status' => 'tepat_waktu',
        ]);

        // Simulasi clock-out jam 19:00 (2 jam lembur)
        $this->travelTo(today()->setTime(19, 0));
        $this->post(route('absensi.clock-out'), [
            'lintang' => -6.2000000,
            'bujur' => 106.8166667,
        ]);

        $this->assertSame(120, Absensi::first()->menit_lembur);
    }

    public function test_penggajian_harian_dihitung_dari_kehadiran(): void
    {
        $this->aktifkanAddon('absensi');
        $this->aktifkanAddon('penggajian');

        $karyawan = Karyawan::factory()->create([
            'toko_id' => $this->toko->id,
            'skema_gaji' => 'harian',
            'tarif_harian' => 80000,
        ]);

        // Hadir 3 hari dalam periode ini
        foreach ([now()->subDays(2), now()->subDay(), now()] as $hari) {
            Absensi::create([
                'toko_id' => $this->toko->id,
                'karyawan_id' => $karyawan->id,
                'tanggal' => $hari->toDateString(),
                'jam_masuk' => $hari->setTime(8, 0),
                'status' => 'tepat_waktu',
            ]);
        }

        $this->post(route('penggajian.store'), [
            'karyawan_id' => $karyawan->id,
            'periode_mulai' => now()->startOfMonth()->toDateString(),
            'periode_selesai' => now()->toDateString(),
            'komponen' => [
                ['jenis' => 'tunjangan', 'nama' => 'Uang makan', 'nominal' => 50000],
                ['jenis' => 'potongan', 'nama' => 'Telat', 'nominal' => 10000],
            ],
        ])->assertRedirect(route('penggajian.index'));

        $pg = Penggajian::first();
        $this->assertSame(3, $pg->jumlah_hadir);
        $this->assertSame(240000.0, (float) $pg->jumlah_dasar);   // 80rb × 3 hari
        $this->assertSame(50000.0, (float) $pg->total_tunjangan);
        $this->assertSame(10000.0, (float) $pg->total_potongan);
        $this->assertSame(280000.0, (float) $pg->gaji_bersih);    // 240rb + 50rb - 10rb
    }

    public function test_penggajian_bulanan_pakai_gaji_pokok(): void
    {
        $this->aktifkanAddon('penggajian');

        $karyawan = Karyawan::factory()->bulanan(3000000)->create(['toko_id' => $this->toko->id]);

        $this->post(route('penggajian.store'), [
            'karyawan_id' => $karyawan->id,
            'periode_mulai' => now()->startOfMonth()->toDateString(),
            'periode_selesai' => now()->toDateString(),
        ]);

        $pg = Penggajian::first();
        $this->assertSame(3000000.0, (float) $pg->jumlah_dasar);
        $this->assertSame('bulanan', $pg->skema_gaji_snapshot);
        $this->assertSame(3000000.0, (float) $pg->gaji_bersih);
    }

    public function test_karyawan_hanya_bisa_lihat_payslip_sendiri(): void
    {
        $this->aktifkanAddon('penggajian');

        $karyawanSendiri = Karyawan::factory()->bulanan()->create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->admin->id,
        ]);
        $karyawanLain = Karyawan::factory()->bulanan()->create(['toko_id' => $this->toko->id]);

        $pgSendiri = Penggajian::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $karyawanSendiri->id,
            'periode_mulai' => now()->startOfMonth(),
            'periode_selesai' => now(),
            'skema_gaji_snapshot' => 'bulanan',
            'jumlah_dasar' => 3000000,
            'gaji_bersih' => 3000000,
        ]);
        $pgLain = Penggajian::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $karyawanLain->id,
            'periode_mulai' => now()->startOfMonth(),
            'periode_selesai' => now(),
            'skema_gaji_snapshot' => 'bulanan',
            'jumlah_dasar' => 3000000,
            'gaji_bersih' => 3000000,
        ]);

        // Admin bisa lihat semua payslip di tokonya
        $this->get(route('payslip.show', $pgLain))->assertOk();

        // Tapi karyawan non-admin hanya miliknya
        $userKaryawan = User::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'karyawan',
            'sub_peran' => 'kasir',
        ]);
        $karyawanUser = Karyawan::factory()->create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $userKaryawan->id,
        ]);
        $pgMiliknya = Penggajian::create([
            'toko_id' => $this->toko->id,
            'karyawan_id' => $karyawanUser->id,
            'periode_mulai' => now()->startOfMonth(),
            'periode_selesai' => now(),
            'skema_gaji_snapshot' => 'bulanan',
            'jumlah_dasar' => 2500000,
            'gaji_bersih' => 2500000,
        ]);

        $this->actingAs($userKaryawan)->get(route('payslip.show', $pgMiliknya))->assertOk();
        $this->actingAs($userKaryawan)->get(route('payslip.show', $pgLain))->assertForbidden();
    }
}
