<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\View\View;

class StatistikController extends Controller
{
    public function index(): View
    {
        $totalToko = Toko::count();
        $totalPengguna = Pengguna::where('peran', '!=', 'superadmin')->count();

        $paketDistribution = Paket::withCount('toko')->get();

        $modulPopularity = Modul::withCount(['toko' => fn ($q) => $q->where('modul_toko.aktif', true)])
            ->orderByDesc('toko_count')
            ->get();

        $totalRevenue = (float) Pembayaran::where('status', 'disetujui')->sum('jumlah');

        $monthlyRevenue = Pembayaran::where('status', 'disetujui')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, SUM(jumlah) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('superadmin.statistik.index', compact(
            'totalToko',
            'totalPengguna',
            'paketDistribution',
            'modulPopularity',
            'totalRevenue',
            'monthlyRevenue'
        ));
    }
}
