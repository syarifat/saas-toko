<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AddonToko;
use App\Models\Pembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(): View
    {
        return view('superadmin.verifikasi', [
            'menunggu' => Pembayaran::with(['toko', 'paket', 'addon', 'pengaju'])
                ->where('status', 'menunggu')
                ->latest()
                ->get(),
            'riwayat' => Pembayaran::with(['toko', 'paket', 'addon', 'verifier'])
                ->whereIn('status', ['disetujui', 'ditolak'])
                ->latest()
                ->take(20)
                ->get(),
        ]);
    }

    public function setujui(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== 'menunggu') {
            return back()->withErrors(['verifikasi' => 'Pembayaran ini sudah diverifikasi.']);
        }

        DB::transaction(function () use ($request, $pembayaran) {
            $toko = $pembayaran->toko;

            if ($pembayaran->jenis === 'paket' && $pembayaran->paket_id) {
                // Naikkan paket & perpanjang langganan
                $basis = $toko->langganan_berakhir_pada && $toko->langganan_berakhir_pada->isFuture()
                    ? $toko->langganan_berakhir_pada
                    : now();

                $toko->update([
                    'paket_id' => $pembayaran->paket_id,
                    'status' => 'aktif',
                    'langganan_berakhir_pada' => $basis->copy()->addMonths((int) $pembayaran->jumlah_bulan),
                ]);
            } elseif ($pembayaran->jenis === 'addon' && $pembayaran->addon_id) {
                AddonToko::updateOrCreate(
                    ['toko_id' => $toko->id, 'addon_id' => $pembayaran->addon_id],
                    ['aktif' => true, 'diaktifkan_pada' => now()],
                );
            }

            $pembayaran->update([
                'status' => 'disetujui',
                'catatan_admin' => $request->input('catatan_admin'),
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
            ]);
        });

        return back()->with('status', 'Pembayaran #'.$pembayaran->id.' disetujui dan langganan toko "'.$pembayaran->toko->nama.'" diperbarui.');
    }

    public function tolak(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== 'menunggu') {
            return back()->withErrors(['verifikasi' => 'Pembayaran ini sudah diverifikasi.']);
        }

        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ]);

        $pembayaran->update([
            'status' => 'ditolak',
            'catatan_admin' => $data['catatan_admin'],
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_pada' => now(),
        ]);

        return back()->with('status', 'Pembayaran #'.$pembayaran->id.' ditolak.');
    }
}
