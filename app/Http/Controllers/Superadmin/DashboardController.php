<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Toko;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalToko = Toko::count();
        $tokoAktif = Toko::where('status', 'aktif')->count();
        $pembayaranMenunggu = Pembayaran::where('status', 'menunggu')->count();
        $pendapatanBulanIni = (float) Pembayaran::where('status', 'disetujui')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');

        $pembayaranTerbaru = Pembayaran::with(['toko', 'paket', 'modul'])
            ->latest()
            ->take(5)
            ->get();

        $tokoTerbaru = Toko::with('paket')
            ->latest()
            ->take(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalToko',
            'tokoAktif',
            'pembayaranMenunggu',
            'pendapatanBulanIni',
            'pembayaranTerbaru',
            'tokoTerbaru'
        ));
    }
}
