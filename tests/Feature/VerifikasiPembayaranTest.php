<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\Paket;
use App\Models\PaketModul;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $superadmin;

    private Toko $toko;

    private Paket $paketBaru;

    private Modul $modulAddon;

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

        $paketLama = Paket::create(['nama' => 'Paket Basic', 'jenis' => 'preset_1', 'harga' => 99000]);

        $this->modulAddon = Modul::create(['kode' => 'pengeluaran', 'nama' => 'Pencatatan Pengeluaran']);

        $this->paketBaru = Paket::create(['nama' => 'Paket Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        PaketModul::create(['paket_id' => $this->paketBaru->id, 'modul_id' => $this->modulAddon->id]);

        $this->toko = Toko::create([
            'nama' => 'Toko Maju',
            'slug' => 'toko-maju',
            'paket_id' => $paketLama->id,
            'status' => 'aktif',
            'langganan_berakhir_pada' => now()->addDays(5),
        ]);
    }

    public function test_superadmin_bisa_menyetujui_upgrade_paket_dan_memperpanjang_langganan(): void
    {
        $pembayaran = Pembayaran::create([
            'toko_id' => $this->toko->id,
            'jenis' => 'upgrade_paket',
            'paket_id' => $this->paketBaru->id,
            'jumlah' => 199000,
            'bukti_transfer' => 'bukti.jpg',
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.verifikasi.setujui', $pembayaran));

        $response->assertRedirect();
        $this->assertSame('disetujui', $pembayaran->fresh()->status);
        $this->assertSame($this->paketBaru->id, $this->toko->fresh()->paket_id);
        $this->assertTrue($this->toko->fresh()->modulAktif('pengeluaran'));
    }

    public function test_superadmin_bisa_menolak_pembayaran_dengan_catatan(): void
    {
        $pembayaran = Pembayaran::create([
            'toko_id' => $this->toko->id,
            'jenis' => 'upgrade_paket',
            'paket_id' => $this->paketBaru->id,
            'jumlah' => 199000,
            'bukti_transfer' => 'bukti.jpg',
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.verifikasi.tolak', $pembayaran), [
                'catatan_penolakan' => 'Bukti transfer tidak terbaca / buram.',
            ]);

        $response->assertRedirect();
        $this->assertSame('ditolak', $pembayaran->fresh()->status);
        $this->assertSame('Bukti transfer tidak terbaca / buram.', $pembayaran->fresh()->catatan_penolakan);
    }
}
