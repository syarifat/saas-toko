<?php

namespace App\Http\Controllers;

use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayslipController extends Controller
{
    public function show(Request $request, Penggajian $penggajian): View
    {
        $user = $request->user();
        $toko = $user->toko;

        // Admin bisa lihat semua; karyawan hanya miliknya sendiri
        if ($user->peran !== 'admin') {
            $karyawan = $toko->karyawan()->where('pengguna_id', $user->id)->first();

            if (! $karyawan || $penggajian->karyawan_id !== $karyawan->id) {
                abort(403);
            }
        } elseif ($penggajian->toko_id !== $toko->id) {
            abort(403);
        }

        return view('payslip.show', [
            'penggajian' => $penggajian->load(['karyawan', 'komponen']),
            'toko' => $toko,
        ]);
    }
}
