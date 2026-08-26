<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Paket;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private Toko $toko;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->superadmin = User::where('peran', 'superadmin')->first();

        $this->toko = Toko::factory()->create([
            'paket_id' => Paket::where('tingkat', 1)->first()->id,
            'status' => 'coba_gratis',
        ]);
        $this->admin = User::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'admin',
            'sub_peran' => null,
        ]);
    }

    public function test_admin_dapat_mengajukan_upgrade_paket_dengan_bukti(): void
    {
        Storage::fake('public');
        $paketPro = Paket::where('tingkat', 2)->first();

        $response = $this->actingAs($this->admin)
            ->post(route('tagihan.ajukan'), [
                'jenis' => 'paket',
                'paket_id' => $paketPro->id,
                'jumlah_bulan' => 3,
                'bukti_transfer' => UploadedFile::fake()->image('transfer.jpg'),
            ]);

        $response->assertRedirect(route('tagihan.index'));

        $pb = Pembayaran::first();
        $this->assertSame('paket', $pb->jenis);
        $this->assertSame($paketPro->id, $pb->paket_id);
        $this->assertSame(3, $pb->jumlah_bulan);
        $this->assertSame(99000.0 * 3, (float) $pb->nominal); // harga master × bulan
        $this->assertSame('menunggu', $pb->status);

        Storage::disk('public')->assertExists($pb->bukti_transfer);
    }

    public function test_pengajuan_tanpa_bukti_ditolak_validasi(): void
    {
        $paketPro = Paket::where('tingkat', 2)->first();

        $this->actingAs($this->admin)
            ->post(route('tagihan.ajukan'), [
                'jenis' => 'paket',
                'paket_id' => $paketPro->id,
                'jumlah_bulan' => 1,
            ])
            ->assertSessionHasErrors('bukti_transfer');
    }

    public function test_superadmin_menyetujui_upgrade_paket_dan_langganan_aktif(): void
    {
        $paketPro = Paket::where('tingkat', 2)->first();
        $this->buatPengajuanPaket($paketPro);
        $pb = Pembayaran::first();

        $this->actingAs($this->superadmin)
            ->post(route('superadmin.verifikasi.setujui', $pb), ['catatan_admin' => 'Transfer diterima'])
            ->assertRedirect();

        // Pembayaran disetujui
        $this->assertSame('disetujui', $pb->fresh()->status);
        $this->assertSame($this->superadmin->id, $pb->fresh()->diverifikasi_oleh);

        // Toko naik paket & langganan aktif +3 bulan
        $toko = $this->toko->fresh();
        $this->assertSame($paketPro->id, $toko->paket_id);
        $this->assertSame('aktif', $toko->status);
        $this->assertTrue($toko->langganan_berakhir_pada->isFuture());
        $this->assertTrue(now()->diffInDays($toko->langganan_berakhir_pada, false) >= 88); // ±3 bulan
    }

    public function test_superadmin_menyetujui_addon_dan_fitur_terbuka(): void
    {
        $addonAbsensi = Addon::where('kode', 'absensi')->first();
        $this->buatPengajuanAddon($addonAbsensi);
        $pb = Pembayaran::first();

        // Sebelum verifikasi: absensi terblokir
        $this->actingAs($this->admin)->get(route('absensi.index'))->assertForbidden();

        $this->actingAs($this->superadmin)
            ->post(route('superadmin.verifikasi.setujui', $pb))
            ->assertRedirect();

        $this->assertSame('disetujui', $pb->fresh()->status);
        $this->assertTrue($this->toko->fresh()->punyaAddon('absensi'));

        // Setelah verifikasi: absensi bisa diakses
        $this->actingAs($this->admin)->get(route('absensi.index'))->assertOk();
    }

    public function test_superadmin_dapat_menolak_dengan_catatan(): void
    {
        $paketPro = Paket::where('tingkat', 2)->first();
        $this->buatPengajuanPaket($paketPro);
        $pb = Pembayaran::first();

        $this->actingAs($this->superadmin)
            ->post(route('superadmin.verifikasi.tolak', $pb), ['catatan_admin' => 'Bukti transfer tidak jelas'])
            ->assertRedirect();

        $pb->refresh();
        $this->assertSame('ditolak', $pb->status);
        $this->assertSame('Bukti transfer tidak jelas', $pb->catatan_admin);

        // Toko tetap di paket lama
        $this->assertSame(Paket::where('tingkat', 1)->first()->id, $this->toko->fresh()->paket_id);
    }

    public function test_verifikasi_ganda_ditolak(): void
    {
        $paketPro = Paket::where('tingkat', 2)->first();
        $this->buatPengajuanPaket($paketPro);
        $pb = Pembayaran::first();

        $this->actingAs($this->superadmin)->post(route('superadmin.verifikasi.setujui', $pb));
        $this->actingAs($this->superadmin)
            ->from(route('superadmin.verifikasi.index'))
            ->post(route('superadmin.verifikasi.setujui', $pb))
            ->assertSessionHasErrors('verifikasi');
    }

    public function test_non_superadmin_tidak_bisa_akses_halaman_verifikasi(): void
    {
        $this->actingAs($this->admin)
            ->get(route('superadmin.verifikasi.index'))
            ->assertForbidden();
    }

    public function test_banner_status_langganan_tampil_di_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Langganan');

        // Admin melihat link kelola langganan
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('Kelola langganan');
    }

    public function test_perpanjangan_berulang_menumpuk_dari_tanggal_akhir(): void
    {
        // Toko sudah aktif s/d 1 bulan ke depan; beli 2 bulan lagi → total ~3 bulan
        $this->toko->update(['status' => 'aktif', 'langganan_berakhir_pada' => now()->addMonth()]);
        $paketSama = Paket::where('tingkat', 1)->first();

        $this->buatPengajuanPaket($paketSama, 2);
        $pb = Pembayaran::first();

        $this->actingAs($this->superadmin)->post(route('superadmin.verifikasi.setujui', $pb));

        $sisa = now()->diffInDays($this->toko->fresh()->langganan_berakhir_pada, false);
        $this->assertTrue($sisa >= 85 && $sisa <= 95, "Sisa hari: {$sisa}");
    }

    private function buatPengajuanPaket(Paket $paket, int $bulan = 3): void
    {
        Pembayaran::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->admin->id,
            'jenis' => 'paket',
            'paket_id' => $paket->id,
            'jumlah_bulan' => $bulan,
            'nominal' => (float) $paket->harga * $bulan,
            'status' => 'menunggu',
        ]);
    }

    private function buatPengajuanAddon(Addon $addon): void
    {
        Pembayaran::create([
            'toko_id' => $this->toko->id,
            'pengguna_id' => $this->admin->id,
            'jenis' => 'addon',
            'addon_id' => $addon->id,
            'jumlah_bulan' => 1,
            'nominal' => (float) $addon->harga,
            'status' => 'menunggu',
        ]);
    }
}
