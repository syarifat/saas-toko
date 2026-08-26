<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapKehadiranController extends Controller
{
    public function index(Request $request): View
    {
        $toko = $request->user()->toko;
        $mulai = $request->filled('mulai') ? $request->mulai : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->toDateString();

        $rekap = $toko->karyawan()
            ->withCount([
                'absensi as hadir_count' => fn ($q) => $q->whereBetween('tanggal', [$mulai, $sampai])->whereNotNull('jam_masuk'),
                'absensi as telat_count' => fn ($q) => $q->whereBetween('tanggal', [$mulai, $sampai])->where('status', 'telat'),
            ])
            ->withSum(['absensi as total_lembur' => fn ($q) => $q->whereBetween('tanggal', [$mulai, $sampai])], 'menit_lembur')
            ->orderBy('nama')
            ->get();

        return view('rekap-kehadiran.index', [
            'rekap' => $rekap,
            'mulai' => $mulai,
            'sampai' => $sampai,
        ]);
    }
}
