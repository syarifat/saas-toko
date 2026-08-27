<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PenjualanSederhana;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        $toko = $user->toko;

        // Omset hari ini (gabungan Transaksi POS + Penjualan Sederhana)
        $omsetPosHariIni = 0;
        $totalTransaksiHariIni = 0;
        if ($toko->modulAktif('kasir_pos')) {
            $omsetPosHariIni = (float) Transaksi::whereDate('tanggal_transaksi', today())->sum('total');
            $totalTransaksiHariIni = Transaksi::whereDate('tanggal_transaksi', today())->count();
        }

        $omsetRingkasHariIni = 0;
        if ($toko->modulAktif('penjualan_ringkas')) {
            $omsetRingkasHariIni = (float) PenjualanSederhana::whereDate('tanggal_penjualan', today())->sum('total');
        }

        $totalOmsetHariIni = $omsetPosHariIni + $omsetRingkasHariIni;

        // Pengeluaran bulan ini
        $pengeluaranBulanIni = 0;
        if ($toko->modulAktif('pengeluaran')) {
            $pengeluaranBulanIni = (float) Pengeluaran::whereMonth('tanggal_pengeluaran', now()->month)
                ->whereYear('tanggal_pengeluaran', now()->year)
                ->sum('nominal');
        }

        // Alert Stok Menipis
        $stokMenipisCount = 0;
        $produkMenipis = collect();
        if ($toko->modulAktif('stock_alert') || $toko->modulAktif('stok_gudang')) {
            $produkMenipis = Produk::with('stokGudang')
                ->get()
                ->filter(fn ($p) => $p->totalStok() <= $p->stok_minimum);

            $stokMenipisCount = $produkMenipis->count();
        }

        // Transaksi POS terbaru
        $transaksiTerbaru = collect();
        if ($toko->modulAktif('kasir_pos')) {
            $transaksiTerbaru = Transaksi::with(['pengguna', 'gudang'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'toko',
            'totalOmsetHariIni',
            'totalTransaksiHariIni',
            'pengeluaranBulanIni',
            'stokMenipisCount',
            'produkMenipis',
            'transaksiTerbaru'
        ));
    }
}
