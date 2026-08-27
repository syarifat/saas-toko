<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantPengeluaranTest extends TestCase
{
    use RefreshDatabase;

    private Toko $toko;

    private Pengguna $user;

    protected function setUp(): void
    {
        parent::setUp();

        $paket = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $this->toko = Toko::create(['nama' => 'Toko Beban', 'slug' => 'toko-beban', 'paket_id' => $paket->id, 'status' => 'aktif']);
        $this->user = Pengguna::create([
            'nama' => 'Admin Beban',
            'email' => 'admin@beban.com',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'toko_id' => $this->toko->id,
        ]);

        $m = Modul::create(['kode' => 'pengeluaran', 'nama' => 'Pencatatan Pengeluaran']);
        ModulToko::create(['toko_id' => $this->toko->id, 'modul_id' => $m->id, 'aktif' => true]);
    }

    public function test_pencatatan_pengeluaran_dengan_foto_struk(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('struk_listrik.jpg');

        $response = $this->actingAs($this->user)->post(route('pengeluaran.store'), [
            'tanggal_pengeluaran' => '2026-08-27',
            'keterangan' => 'Bayar Tagihan Listrik Toko',
            'nominal' => 250000,
            'bukti_struk' => $file,
        ]);

        $response->assertRedirect(route('pengeluaran.index'));
        $this->assertDatabaseHas('pengeluaran', [
            'toko_id' => $this->toko->id,
            'keterangan' => 'Bayar Tagihan Listrik Toko',
            'nominal' => 250000,
        ]);

        Storage::disk('public')->assertExists('bukti_pengeluaran/'.$file->hashName());
    }
}
