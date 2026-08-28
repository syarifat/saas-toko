<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantKaryawanRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private Toko $tokoPaket1;

    private Toko $tokoPaket2;

    private Toko $tokoPaket3;

    private Pengguna $userPaket1;

    private Pengguna $userPaket2;

    private Pengguna $userPaket3;

    protected function setUp(): void
    {
        parent::setUp();

        $p1 = Paket::create(['nama' => 'Basic', 'jenis' => 'preset_1', 'harga' => 99000]);
        $p2 = Paket::create(['nama' => 'Pro', 'jenis' => 'preset_2', 'harga' => 199000]);
        $p3 = Paket::create(['nama' => 'Enterprise', 'jenis' => 'preset_3', 'harga' => 399000]);

        $this->tokoPaket1 = Toko::create(['nama' => 'Toko P1', 'slug' => 'toko-p1', 'paket_id' => $p1->id, 'status' => 'aktif']);
        $this->userPaket1 = Pengguna::create(['nama' => 'Owner P1', 'email' => 'p1@test.com', 'password' => bcrypt('password'), 'peran' => 'admin', 'toko_id' => $this->tokoPaket1->id]);

        $this->tokoPaket2 = Toko::create(['nama' => 'Toko P2', 'slug' => 'toko-p2', 'paket_id' => $p2->id, 'status' => 'aktif']);
        $this->userPaket2 = Pengguna::create(['nama' => 'Owner P2', 'email' => 'p2@test.com', 'password' => bcrypt('password'), 'peran' => 'admin', 'toko_id' => $this->tokoPaket2->id]);

        $this->tokoPaket3 = Toko::create(['nama' => 'Toko P3', 'slug' => 'toko-p3', 'paket_id' => $p3->id, 'status' => 'aktif']);
        $this->userPaket3 = Pengguna::create(['nama' => 'Owner P3', 'email' => 'p3@test.com', 'password' => bcrypt('password'), 'peran' => 'admin', 'toko_id' => $this->tokoPaket3->id]);

        // Modul P1
        $mProduk = Modul::create(['kode' => 'master_produk', 'nama' => 'Master Produk']);
        ModulToko::create(['toko_id' => $this->tokoPaket1->id, 'modul_id' => $mProduk->id, 'aktif' => true]);

        // Modul P2 (Paket 2: master_produk, kasir_pos, stok_gudang, stock_alert, stok_opname, laporan_hpp)
        $mKasir = Modul::create(['kode' => 'kasir_pos', 'nama' => 'Kasir POS']);
        $mStokGudang = Modul::create(['kode' => 'stok_gudang', 'nama' => 'Stok Gudang']);
        $mStockAlert = Modul::create(['kode' => 'stock_alert', 'nama' => 'Stock Alert']);
        $mStokOpname = Modul::create(['kode' => 'stok_opname', 'nama' => 'Stok Opname']);
        $mLaporanHpp = Modul::create(['kode' => 'laporan_hpp', 'nama' => 'Laporan HPP']);

        foreach ([$mProduk, $mKasir, $mStokGudang, $mStockAlert, $mStokOpname, $mLaporanHpp] as $m) {
            ModulToko::create(['toko_id' => $this->tokoPaket2->id, 'modul_id' => $m->id, 'aktif' => true]);
        }

        // Modul P3 (Paket 3: semua modul P2 + multi_gudang, barang_masuk, transfer_gudang, kartu_stok)
        $mGudang = Modul::create(['kode' => 'multi_gudang', 'nama' => 'Multi Gudang']);
        $mBarangMasuk = Modul::create(['kode' => 'barang_masuk', 'nama' => 'Barang Masuk']);
        $mTransferGudang = Modul::create(['kode' => 'transfer_gudang', 'nama' => 'Transfer Gudang']);
        $mKartuStok = Modul::create(['kode' => 'kartu_stok', 'nama' => 'Kartu Stok']);

        foreach ([$mProduk, $mKasir, $mStokGudang, $mStockAlert, $mStokOpname, $mLaporanHpp, $mGudang, $mBarangMasuk, $mTransferGudang, $mKartuStok] as $m) {
            ModulToko::create(['toko_id' => $this->tokoPaket3->id, 'modul_id' => $m->id, 'aktif' => true]);
        }
    }

    public function test_paket_1_tidak_bisa_mengakses_manajemen_staf(): void
    {
        $response = $this->actingAs($this->userPaket1)->get(route('karyawan.index'));
        $response->assertForbidden();
    }

    public function test_paket_2_bisa_membuat_akun_kasir_tetapi_tidak_bisa_membuat_akun_gudang(): void
    {
        // 1. Buat akun kasir -> Berhasil
        $resKasir = $this->actingAs($this->userPaket2)->post(route('karyawan.store'), [
            'nama' => 'Kasir Baru P2',
            'email' => 'kasirp2@test.com',
            'password' => 'password123',
            'sub_peran' => 'kasir',
        ]);
        $resKasir->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseHas('pengguna', [
            'email' => 'kasirp2@test.com',
            'peran' => 'karyawan',
            'sub_peran' => 'kasir',
            'toko_id' => $this->tokoPaket2->id,
        ]);

        // 2. Coba buat akun gudang pada Paket 2 -> Validasi error (tidak diizinkan)
        $resGudang = $this->actingAs($this->userPaket2)->post(route('karyawan.store'), [
            'nama' => 'Gudang Ilegal P2',
            'email' => 'gudangp2@test.com',
            'password' => 'password123',
            'sub_peran' => 'gudang',
        ]);
        $resGudang->assertSessionHasErrors('sub_peran');
        $this->assertDatabaseMissing('pengguna', ['email' => 'gudangp2@test.com']);
    }

    public function test_paket_3_bisa_membuat_akun_kasir_dan_akun_gudang(): void
    {
        // 1. Buat akun Kasir
        $resKasir = $this->actingAs($this->userPaket3)->post(route('karyawan.store'), [
            'nama' => 'Kasir P3',
            'email' => 'kasirp3@test.com',
            'password' => 'password123',
            'sub_peran' => 'kasir',
        ]);
        $resKasir->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseHas('pengguna', ['email' => 'kasirp3@test.com', 'sub_peran' => 'kasir']);

        // 2. Buat akun Gudang
        $resGudang = $this->actingAs($this->userPaket3)->post(route('karyawan.store'), [
            'nama' => 'Staff Gudang P3',
            'email' => 'gudangp3@test.com',
            'password' => 'password123',
            'sub_peran' => 'gudang',
        ]);
        $resGudang->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseHas('pengguna', ['email' => 'gudangp3@test.com', 'sub_peran' => 'gudang']);
    }
}
