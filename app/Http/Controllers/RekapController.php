<?php

namespace App\Http\Controllers;

use App\Models\ItemPenjualanSederhana;
use App\Models\ItemTransaksi;
use App\Models\Pengeluaran;
use App\Models\PenjualanSederhana;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $parts = explode('-', $bulan);
        $tahun = (int) ($parts[0] ?? now()->year);
        $bln = (int) ($parts[1] ?? now()->month);

        $toko = auth()->user()->toko;

        // Total Penjualan Ringkas
        $totalPenjualanRingkas = 0;
        $hppPenjualanRingkas = 0;
        if ($toko->modulAktif('penjualan_ringkas')) {
            $totalPenjualanRingkas = (float) PenjualanSederhana::whereYear('tanggal_penjualan', $tahun)
                ->whereMonth('tanggal_penjualan', $bln)
                ->sum('total');

            $hppPenjualanRingkas = (float) ItemPenjualanSederhana::whereHas('penjualanSederhana', function ($q) use ($tahun, $bln) {
                $q->whereYear('tanggal_penjualan', $tahun)->whereMonth('tanggal_penjualan', $bln);
            })->selectRaw('SUM(jumlah * harga_beli_snapshot) as total_hpp')->value('total_hpp');
        }

        // Total Transaksi POS
        $totalPos = 0;
        $hppPos = 0;
        if ($toko->modulAktif('kasir_pos')) {
            $totalPos = (float) Transaksi::whereYear('tanggal_transaksi', $tahun)
                ->whereMonth('tanggal_transaksi', $bln)
                ->sum('total');

            $hppPos = (float) ItemTransaksi::whereHas('transaksi', function ($q) use ($tahun, $bln) {
                $q->whereYear('tanggal_transaksi', $tahun)->whereMonth('tanggal_transaksi', $bln);
            })->selectRaw('SUM(jumlah * harga_beli_snapshot) as total_hpp')->value('total_hpp');
        }

        $totalPemasukan = $totalPenjualanRingkas + $totalPos;
        $totalHpp = $hppPenjualanRingkas + $hppPos;

        // Total Pengeluaran
        $totalPengeluaran = 0;
        if ($toko->modulAktif('pengeluaran')) {
            $totalPengeluaran = (float) Pengeluaran::whereYear('tanggal_pengeluaran', $tahun)
                ->whereMonth('tanggal_pengeluaran', $bln)
                ->sum('nominal');
        }

        $labaKotor = $totalPemasukan - $totalHpp;
        $labaBersihEstimasi = $labaKotor - $totalPengeluaran;

        return view('tenant.rekap.index', compact(
            'bulan',
            'totalPenjualanRingkas',
            'totalPos',
            'totalPemasukan',
            'totalHpp',
            'labaKotor',
            'totalPengeluaran',
            'labaBersihEstimasi'
        ));
    }
}
