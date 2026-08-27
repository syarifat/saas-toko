<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantTagihanTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    private Paket $paketUpgrade;

    protected function setUp(): void
    {
        parent::setUp();

        $paketAwal = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $this->paketUpgrade = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);

        $this->toko = Toko::create(['nama' => 'Toko Sub', 'slug' => 'toko-sub', 'paket_id' => $paketAwal->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Owner Sub',
            'email' => 'owner@sub.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);
    }

    public function test_tenant_bisa_mengajukan_bukti_transfer_upgrade_paket(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('bukti_transfer.png');

        $response = $this->actingAs($this->user)->post(route('tagihan.ajukan'), [
            'jenis' => 'upgrade_paket',
            'paket_id' => $this->paketUpgrade->id,
            'jumlah' => 199000,
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayaran', [
            'toko_id' => $this->toko->id,
            'jenis' => 'upgrade_paket',
            'paket_id' => $this->paketUpgrade->id,
            'jumlah' => 199000,
            'status' => 'menunggu',
        ]);
    }

    public function test_tenant_bisa_mengajukan_pembelian_addon_modul(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('bukti_addon.png');

        $modul = Modul::create(['kode' => 'payroll', 'nama' => 'Penggajian & Slip Gaji']);

        $response = $this->actingAs($this->user)->post(route('tagihan.ajukan'), [
            'jenis' => 'aktivasi_addon',
            'modul_id' => $modul->id,
            'jumlah' => 49000,
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayaran', [
            'toko_id' => $this->toko->id,
            'jenis' => 'aktivasi_addon',
            'modul_id' => $modul->id,
            'jumlah' => 49000,
            'status' => 'menunggu',
        ]);
    }
}
