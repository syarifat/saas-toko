<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PenjualanSederhanaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\Superadmin;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TransferGudangController;
use Illuminate\Support\Facades\Route;

// ================================================================
// === PUBLIK ===
// ================================================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// === AUTH (Laravel Breeze) ===
require __DIR__.'/auth.php';

// ================================================================
// === SUPERADMIN PANEL ===
// ================================================================
Route::prefix('superadmin')
    ->middleware(['auth', 'peran:superadmin'])
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [Superadmin\DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Toko
        Route::resource('toko', Superadmin\TokoController::class);
        Route::post('toko/{toko}/pakai-preset/{paket}', [Superadmin\TokoController::class, 'pakaiPreset'])
            ->name('toko.pakai-preset');

        // Manajemen Modul per Toko
        Route::post('toko/{toko}/modul/{kode}/aktifkan', [Superadmin\ModulTokoController::class, 'aktifkan'])
            ->name('toko.modul.aktifkan');
        Route::post('toko/{toko}/modul/{kode}/nonaktifkan', [Superadmin\ModulTokoController::class, 'nonaktifkan'])
            ->name('toko.modul.nonaktifkan');

        // CRUD Paket (Preset + Custom)
        Route::resource('paket', Superadmin\PaketController::class);

        // Verifikasi Pembayaran & Langganan
        Route::get('verifikasi', [Superadmin\VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('verifikasi/{pembayaran}/setujui', [Superadmin\VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('verifikasi/{pembayaran}/tolak', [Superadmin\VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

        // Statistik Platform
        Route::get('statistik', [Superadmin\StatistikController::class, 'index'])->name('statistik');
    });

// ================================================================
// === PROFILE & UMUM ===
// ================================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ================================================================
// === TENANT PANEL ===
// ================================================================
Route::middleware(['auth', 'verified', 'konteks_toko'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ── Modul: pengeluaran ──────────────────────────────────
        Route::middleware('modul:pengeluaran')->group(function () {
            Route::resource('pengeluaran', PengeluaranController::class);
        });

        // ── Modul: master_produk ────────────────────────────────
        Route::middleware('modul:master_produk')->group(function () {
            Route::resource('produk', ProdukController::class);
            Route::resource('kategori', KategoriController::class)->except(['show']);
            Route::resource('pemasok', PemasokController::class)->except(['show']);
            Route::get('kasir/cari-produk', [KasirController::class, 'cariProduk'])->name('kasir.cari-produk');
        });

        // ── Modul: penjualan_ringkas ────────────────────────────
        Route::middleware('modul:penjualan_ringkas')->group(function () {
            Route::resource('penjualan', PenjualanSederhanaController::class)
                ->parameters(['penjualan' => 'penjualanSederhana']);
        });

        // ── Modul: rekap_keuangan ───────────────────────────────
        Route::middleware('modul:rekap_keuangan')->group(function () {
            Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');
        });

        // ── Modul: kasir_pos ────────────────────────────────────
        Route::middleware('modul:kasir_pos')->group(function () {
            Route::get('kasir', [KasirController::class, 'index'])->name('kasir.index');
            Route::post('kasir/transaksi', [KasirController::class, 'store'])->name('kasir.store');
            Route::get('kasir/riwayat', [KasirController::class, 'riwayat'])->name('kasir.riwayat');
            Route::get('kasir/{transaksi}', [KasirController::class, 'show'])->name('kasir.show');
        });

        // ── Modul: stock_alert ──────────────────────────────────
        Route::middleware('modul:stock_alert')->group(function () {
            Route::get('stok/alert', [StokController::class, 'alert'])->name('stok.alert');
        });

        // ── Modul: stok_opname ──────────────────────────────────
        Route::middleware('modul:stok_opname')->group(function () {
            Route::get('stok/opname', [StokController::class, 'opname'])->name('stok.opname');
            Route::post('stok/opname', [StokController::class, 'simpanOpname'])->name('stok.opname.simpan');
        });

        // ── Modul: laporan_hpp ──────────────────────────────────
        Route::middleware('modul:laporan_hpp')->group(function () {
            Route::get('laporan/hpp', [LaporanController::class, 'hpp'])->name('laporan.hpp');
        });

        // ── Modul: multi_gudang ─────────────────────────────────
        Route::middleware('modul:multi_gudang')->group(function () {
            Route::resource('gudang', GudangController::class)->except(['show']);
        });

        // ── Modul: barang_masuk ─────────────────────────────────
        Route::middleware('modul:barang_masuk')->group(function () {
            Route::get('gudang/masuk', [BarangMasukController::class, 'index'])->name('barang_masuk.index');
            Route::post('gudang/masuk', [BarangMasukController::class, 'store'])->name('barang_masuk.store');
        });

        // ── Modul: transfer_gudang ──────────────────────────────
        Route::middleware('modul:transfer_gudang')->group(function () {
            Route::get('gudang/transfer', [TransferGudangController::class, 'index'])->name('transfer_gudang.index');
            Route::post('gudang/transfer', [TransferGudangController::class, 'store'])->name('transfer_gudang.store');
        });

        // ── Modul: kartu_stok ───────────────────────────────────
        Route::middleware('modul:kartu_stok')->group(function () {
            Route::get('stok/kartu', [StokController::class, 'kartu'])->name('stok.kartu');
            Route::get('stok/kartu/{produk}', [StokController::class, 'kartuProduk'])->name('stok.kartu.detail');
        });

        // ── Manajemen Karyawan & Akun Staf (Paket 2: Kasir, Paket 3: Gudang, Addon: HR) ──
        Route::get('karyawan/hak-akses', [KaryawanController::class, 'hakAkses'])->name('karyawan.hak-akses');
        Route::post('karyawan/hak-akses', [KaryawanController::class, 'simpanHakAkses'])->name('karyawan.hak-akses.simpan');
        Route::resource('karyawan', KaryawanController::class);

        // ── Modul: absensi ──────────────────────────────────────
        Route::middleware('modul:absensi')->group(function () {
            Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('absensi/masuk', [AbsensiController::class, 'masuk'])->name('absensi.masuk');
            Route::post('absensi/keluar', [AbsensiController::class, 'keluar'])->name('absensi.keluar');
            Route::get('absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        });

        // ── Modul: payroll ──────────────────────────────────────
        Route::middleware('modul:payroll')->group(function () {
            Route::get('penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
            Route::post('penggajian/generate', [PenggajianController::class, 'generate'])->name('penggajian.generate');
            Route::get('penggajian/{penggajian}', [PenggajianController::class, 'show'])->name('penggajian.show');
            Route::post('penggajian/{penggajian}/bayar', [PenggajianController::class, 'bayar'])->name('penggajian.bayar');
            Route::get('penggajian/{penggajian}/slip', [PenggajianController::class, 'slip'])->name('penggajian.slip');
        });

        // ── Tagihan / Billing Tenant ────────────────────────────
        Route::prefix('tagihan')->name('tagihan.')->group(function () {
            Route::get('/', [TagihanController::class, 'index'])->name('index');
            Route::post('/ajukan', [TagihanController::class, 'ajukan'])->name('ajukan');
        });
    });
