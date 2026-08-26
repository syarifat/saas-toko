<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PenjualanSederhanaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekapKehadiranController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\Superadmin\AddonController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\PaketController;
use App\Http\Controllers\Superadmin\TokoController;
use App\Http\Controllers\Superadmin\VerifikasiController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TransferStokController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->peran) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        default => view('dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'peran:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('toko', TokoController::class)->except('show');
    Route::resource('paket', PaketController::class)->except('show');
    Route::resource('addon', AddonController::class)->except('show');
    Route::post('toko/{toko}/addon/{addon}/toggle', [AddonController::class, 'toggleToko'])->name('toko.addon.toggle');
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::post('/verifikasi/{pembayaran}/setujui', [VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
    Route::post('/verifikasi/{pembayaran}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Billing manual: tagihan & pengajuan pembayaran (semua tenant)
    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::post('/tagihan/ajukan', [TagihanController::class, 'ajukan'])->name('tagihan.ajukan');

    // Fitur Paket 1: pencatatan pengeluaran, penjualan ringkas, rekap
    Route::middleware(['paket:1'])->group(function () {
        Route::resource('pengeluaran', PengeluaranController::class)->except('show');
        Route::resource('penjualan-sederhana', PenjualanSederhanaController::class)
            ->parameters(['penjualan-sederhana' => 'penjualanSederhana'])
            ->except(['show', 'edit', 'update']);
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

        // Fitur Paket 2: master produk & kasir POS
        Route::middleware('paket:2')->group(function () {
            Route::resource('produk', ProdukController::class)->except('show');
            Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
            Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
            Route::post('/pemasok', [PemasokController::class, 'store'])->name('pemasok.store');
            Route::put('/pemasok/{pemasok}', [PemasokController::class, 'update'])->name('pemasok.update');
            Route::delete('/pemasok/{pemasok}', [PemasokController::class, 'destroy'])->name('pemasok.destroy');

            // Fitur Paket 3: multi-gudang & logistik
            Route::middleware('paket:3')->group(function () {
                Route::get('/gudang', [GudangController::class, 'index'])->name('gudang.index');
                Route::post('/gudang', [GudangController::class, 'store'])->name('gudang.store');
                Route::put('/gudang/{gudang}', [GudangController::class, 'update'])->name('gudang.update');
                Route::delete('/gudang/{gudang}', [GudangController::class, 'destroy'])->name('gudang.destroy');
                Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
                Route::get('/barang-masuk/create', [BarangMasukController::class, 'create'])->name('barang-masuk.create');
                Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
                Route::get('/transfer-stok', [TransferStokController::class, 'index'])->name('transfer-stok.index');
                Route::get('/transfer-stok/create', [TransferStokController::class, 'create'])->name('transfer-stok.create');
                Route::post('/transfer-stok', [TransferStokController::class, 'store'])->name('transfer-stok.store');
                Route::get('/kartu-stok', [KartuStokController::class, 'index'])->name('kartu-stok.index');
            });
        });

        // Add-on: Absensi (geotagging) & Penggajian
        Route::middleware('addon:absensi')->group(function () {
            Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('/absensi/masuk', [AbsensiController::class, 'clockIn'])->name('absensi.clock-in');
            Route::post('/absensi/keluar', [AbsensiController::class, 'clockOut'])->name('absensi.clock-out');
            Route::get('/rekap-kehadiran', [RekapKehadiranController::class, 'index'])->name('rekap-kehadiran.index');
        });

        Route::middleware('addon:penggajian')->group(function () {
            Route::resource('karyawan', KaryawanController::class)->except('show')->parameters(['karyawan' => 'karyawan']);
            Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
            Route::get('/penggajian/create', [PenggajianController::class, 'create'])->name('penggajian.create');
            Route::post('/penggajian', [PenggajianController::class, 'store'])->name('penggajian.store');
            Route::post('/penggajian/{penggajian}/dibayar', [PenggajianController::class, 'tandaiDibayar'])
                ->name('penggajian.dibayar');
            Route::get('/payslip/{penggajian}', [PayslipController::class, 'show'])->name('payslip.show');
        });

        // Fitur Paket 2: master produk & kasir POS (lanjutan)
        Route::middleware('paket:2')->group(function () {
            Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
            Route::post('/kasir', [KasirController::class, 'store'])->name('kasir.store');
            Route::get('/stok-opname', [StokOpnameController::class, 'index'])->name('stok-opname.index');
            Route::post('/stok-opname', [StokOpnameController::class, 'store'])->name('stok-opname.store');
        });
    });
});

require __DIR__.'/auth.php';
