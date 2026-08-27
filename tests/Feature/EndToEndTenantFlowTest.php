<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use Database\Seeders\KetergantunganModulSeeder;
use Database\Seeders\ModulSeeder;
use Database\Seeders\PaketSeeder;
use Database\Seeders\SuperadminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndTenantFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed full foundational matrix
        $this->seed([
            ModulSeeder::class,
            KetergantunganModulSeeder::class,
            PaketSeeder::class,
            SuperadminSeeder::class,
        ]);
    }

    public function test_alur_lengkap_siklus_hidup_tenant_dari_superadmin_hingga_kasir_dan_payroll(): void
    {
        Storage::fake('public');

        $superadmin = Pengguna::where('peran', 'superadmin')->first();
        $paket1 = Paket::where('jenis', 'preset_1')->first();
        $paket2 = Paket::where('jenis', 'preset_2')->first();

        // 1. Superadmin mendaftarkan toko baru dengan Preset 1 (4 modul dasar)
        $resStoreToko = $this->actingAs($superadmin)->post(route('superadmin.toko.store'), [
            'nama' => 'Kedai Kopi Nusantara',
            'paket_id' => $paket1->id,
            'admin_nama' => 'Pak Budi',
            'admin_email' => 'budi@kopinusantara.test',
            'admin_password' => 'password123',
            'garis_lintang' => -6.200000,
            'garis_bujur' => 106.816666,
            'radius_absensi' => 150,
        ]);
        $resStoreToko->assertRedirect(route('superadmin.toko.index'));

        $toko = Toko::where('nama', 'Kedai Kopi Nusantara')->first();
        $this->assertNotNull($toko);
        $this->assertTrue($toko->modulAktif('master_produk'));
        $this->assertTrue($toko->modulAktif('penjualan_ringkas'));

        // 2. Login sebagai Admin Toko & Buat Produk
        $adminToko = Pengguna::where('email', 'budi@kopinusantara.test')->first();

        $resProduk = $this->actingAs($adminToko)->post(route('produk.store'), [
            'sku' => 'KOPI-01',
            'nama' => 'Espresso Blend 1kg',
            'harga_beli' => 80000,
            'harga_jual' => 130000,
            'stok_minimum' => 3,
            'stok_awal' => 10,
        ]);
        $resProduk->assertRedirect(route('produk.index'));
        $produk = Produk::withoutGlobalScope('toko')->where('sku', 'KOPI-01')->first();
        $this->assertSame(10, $produk->totalStok());

        // 3. Catat Penjualan Ringkas
        $resPenjualan = $this->actingAs($adminToko)->post(route('penjualan.store'), [
            'tanggal_penjualan' => '2026-08-27',
            'catatan' => 'Pesanan event',
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2,
                    'harga_satuan' => 130000,
                ],
            ],
        ]);
        $resPenjualan->assertRedirect(route('penjualan.index'));

        // 4. Catat Pengeluaran Operasional
        $resPengeluaran = $this->actingAs($adminToko)->post(route('pengeluaran.store'), [
            'tanggal_pengeluaran' => '2026-08-27',
            'keterangan' => 'Beli Cup & Sedotan',
            'nominal' => 40000,
        ]);
        $resPengeluaran->assertRedirect(route('pengeluaran.index'));

        // 5. Verifikasi Dashboard & Rekap Keuangan
        $resRekap = $this->actingAs($adminToko)->get(route('rekap.index', ['bulan' => '2026-08']));
        $resRekap->assertOk();
        $resRekap->assertSee('260.000'); // Omset (2 x 130.000)
        $resRekap->assertSee('160.000'); // HPP (2 x 80.000)
        $resRekap->assertSee('100.000'); // Laba Kotor (260k - 160k)
        $resRekap->assertSee('40.000');  // Beban Pengeluaran
        $resRekap->assertSee('60.000');  // Laba Bersih (100k - 40k)

        // 6. Tenant mengajukan upgrade ke Preset 2 (Aktifkan POS, Stok Gudang, Kasir, dll)
        $buktiFile = UploadedFile::fake()->image('struk_upgrade.jpg');
        $resUpgrade = $this->actingAs($adminToko)->post(route('tagihan.ajukan'), [
            'jenis' => 'upgrade_paket',
            'paket_id' => $paket2->id,
            'jumlah' => $paket2->harga,
            'bukti_transfer' => $buktiFile,
        ]);
        $resUpgrade->assertRedirect();

        $pembayaran = Pembayaran::withoutGlobalScope('toko')
            ->where('toko_id', $toko->id)
            ->latest()
            ->first();

        // 7. Superadmin memverifikasi dan menyetujui upgrade
        $resApprove = $this->actingAs($superadmin)
            ->post(route('superadmin.verifikasi.setujui', $pembayaran));
        $resApprove->assertRedirect();

        $this->assertTrue($toko->fresh()->modulAktif('kasir_pos'));
        $this->assertTrue($toko->fresh()->modulAktif('stok_gudang'));

        // 8. Buka Kasir POS & lakukan transaksi dengan pemotongan stok otomatis
        $gudang = Gudang::withoutGlobalScope('toko')->where('toko_id', $toko->id)->first();
        $resPos = $this->actingAs($adminToko)->post(route('kasir.store'), [
            'gudang_id' => $gudang->id,
            'cart_data' => json_encode([
                ['id' => $produk->id, 'qty' => 3],
            ]),
            'diskon' => 0,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 400000,
        ]);
        $resPos->assertRedirect();

        // Sisa stok dari 10 - 3 = 7 unit
        $this->assertSame(7, $produk->fresh()->totalStok());

        // 9. Laporan HPP Kasir POS ter-update
        $resHpp = $this->actingAs($adminToko)->get(route('laporan.hpp', ['bulan' => '2026-08']));
        $resHpp->assertOk();
        $resHpp->assertSee('Espresso Blend 1kg');
        $resHpp->assertSee('390.000'); // Omset POS (3 x 130k)
        $resHpp->assertSee('240.000'); // HPP POS (3 x 80k)
        $resHpp->assertSee('150.000'); // Laba Kotor POS (390k - 240k)
    }
}
