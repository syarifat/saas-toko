<?php

namespace Tests\Feature;

use App\Models\KetergantunganModul;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\PaketModul;
use App\Models\Toko;
use App\Services\ModulService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ModulServiceTest extends TestCase
{
    use RefreshDatabase;

    private ModulService $service;

    private Toko $toko;

    private Modul $modulProduk;

    private Modul $modulStok;

    private Modul $modulKasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ModulService::class);

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

        $this->modulProduk = Modul::create([
            'kode' => 'master_produk',
            'nama' => 'Master Produk',
        ]);

        $this->modulStok = Modul::create([
            'kode' => 'stok_gudang',
            'nama' => 'Manajemen Stok',
        ]);

        $this->modulKasir = Modul::create([
            'kode' => 'kasir_pos',
            'nama' => 'Kasir POS',
        ]);

        // Dependencies:
        // stok_gudang -> requires master_produk
        KetergantunganModul::create([
            'modul_id' => $this->modulStok->id,
            'requires_modul_id' => $this->modulProduk->id,
        ]);

        // kasir_pos -> requires master_produk AND stok_gudang
        KetergantunganModul::create([
            'modul_id' => $this->modulKasir->id,
            'requires_modul_id' => $this->modulProduk->id,
        ]);
        KetergantunganModul::create([
            'modul_id' => $this->modulKasir->id,
            'requires_modul_id' => $this->modulStok->id,
        ]);
    }

    public function test_bisa_mengaktifkan_modul_tanpa_dependency(): void
    {
        $this->service->aktifkan($this->toko, 'master_produk');

        $this->assertTrue($this->toko->modulAktif('master_produk'));
    }

    public function test_gagal_mengaktifkan_modul_jika_dependency_belum_aktif(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->aktifkan($this->toko, 'kasir_pos');
    }

    public function test_bisa_mengaktifkan_modul_beserta_seluruh_dependensinya_secara_rekursif(): void
    {
        $this->service->aktifkanDenganDependency($this->toko, 'kasir_pos');

        $this->assertTrue($this->toko->fresh()->modulAktif('master_produk'));
        $this->assertTrue($this->toko->fresh()->modulAktif('stok_gudang'));
        $this->assertTrue($this->toko->fresh()->modulAktif('kasir_pos'));
    }

    public function test_gagal_menonaktifkan_modul_jika_masih_ada_dependan_yang_aktif(): void
    {
        $this->service->aktifkanDenganDependency($this->toko, 'kasir_pos');

        $this->expectException(ValidationException::class);

        // stok_gudang tidak bisa dimatikan karena kasir_pos masih aktif
        $this->service->nonaktifkan($this->toko, 'stok_gudang');
    }

    public function test_bisa_menonaktifkan_modul_jika_tidak_ada_dependan_aktif(): void
    {
        $this->service->aktifkan($this->toko, 'master_produk');
        $this->assertTrue($this->toko->modulAktif('master_produk'));

        $this->service->nonaktifkan($this->toko, 'master_produk');
        $this->assertFalse($this->toko->modulAktif('master_produk'));
    }

    public function test_pakai_preset_sinkronisasi_seluruh_modul(): void
    {
        $paketPreset = Paket::create([
            'nama' => 'Preset POS',
            'jenis' => 'preset_2',
            'harga' => 199000,
        ]);

        PaketModul::create(['paket_id' => $paketPreset->id, 'modul_id' => $this->modulProduk->id]);
        PaketModul::create(['paket_id' => $paketPreset->id, 'modul_id' => $this->modulStok->id]);

        $this->service->pakaiPreset($this->toko, $paketPreset);

        $this->assertTrue($this->toko->fresh()->modulAktif('master_produk'));
        $this->assertTrue($this->toko->fresh()->modulAktif('stok_gudang'));
        $this->assertFalse($this->toko->fresh()->modulAktif('kasir_pos'));
        $this->assertSame($paketPreset->id, $this->toko->fresh()->paket_id);
    }
}
