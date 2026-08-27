<?php

namespace App\Http\Controllers;

use App\Models\ItemTransaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function hpp(Request $request): View
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $parts = explode('-', $bulan);
        $tahun = (int) ($parts[0] ?? now()->year);
        $bln = (int) ($parts[1] ?? now()->month);

        $laporanProduk = ItemTransaksi::whereHas('transaksi', function ($q) use ($tahun, $bln) {
            $q->whereYear('tanggal_transaksi', $tahun)
                ->whereMonth('tanggal_transaksi', $bln);
        })
            ->selectRaw('
            produk_id,
            nama_produk,
            SUM(jumlah) as total_terjual,
            SUM(subtotal) as total_omset,
            SUM(jumlah * harga_beli_snapshot) as total_hpp,
            SUM(subtotal - (jumlah * harga_beli_snapshot)) as total_laba_kotor
        ')
            ->groupBy('produk_id', 'nama_produk')
            ->orderByDesc('total_laba_kotor')
            ->get();

        $grandOmset = $laporanProduk->sum('total_omset');
        $grandHpp = $laporanProduk->sum('total_hpp');
        $grandLaba = $laporanProduk->sum('total_laba_kotor');

        return view('tenant.laporan.hpp', compact(
            'bulan',
            'laporanProduk',
            'grandOmset',
            'grandHpp',
            'grandLaba'
        ));
    }
}
