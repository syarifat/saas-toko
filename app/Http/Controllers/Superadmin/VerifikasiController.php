<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\ModulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'menunggu');

        $query = Pembayaran::with(['toko', 'paket', 'modul', 'diverifikasiOleh']);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $pembayarans = $query->latest()->paginate(15)->withQueryString();

        $menungguCount = Pembayaran::where('status', 'menunggu')->count();
        $disetujuiCount = Pembayaran::where('status', 'disetujui')->count();
        $ditolakCount = Pembayaran::where('status', 'ditolak')->count();

        return view('superadmin.verifikasi.index', compact(
            'pembayarans',
            'status',
            'menungguCount',
            'disetujuiCount',
            'ditolakCount'
        ));
    }

    public function setujui(Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== 'menunggu') {
            return back()->with('error', 'Status pembayaran ini sudah tidak dapat diubah.');
        }

        DB::transaction(function () use ($pembayaran) {
            $pembayaran->update([
                'status' => 'disetujui',
                'diverifikasi_oleh' => auth()->id(),
                'diverifikasi_pada' => now(),
            ]);

            $toko = $pembayaran->toko;

            if ($pembayaran->jenis === 'upgrade_paket' && $pembayaran->paket_id) {
                app(ModulService::class)->pakaiPreset($toko, $pembayaran->paket);
                $masaAktif = $toko->langganan_berakhir_pada && $toko->langganan_berakhir_pada->isFuture()
                    ? $toko->langganan_berakhir_pada->addMonth()
                    : now()->addMonth();

                $toko->update(['langganan_berakhir_pada' => $masaAktif]);
            } elseif ($pembayaran->jenis === 'aktivasi_addon' && $pembayaran->modul_id) {
                app(ModulService::class)->aktifkanDenganDependency($toko, $pembayaran->modul->kode);
            }
        });

        return back()->with('success', 'Pembayaran berhasil disetujui dan layanan toko telah diperbarui.');
    }

    public function tolak(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== 'menunggu') {
            return back()->with('error', 'Status pembayaran ini sudah tidak dapat diubah.');
        }

        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:500'],
        ]);

        $pembayaran->update([
            'status' => 'ditolak',
            'catatan_penolakan' => $validated['catatan_penolakan'],
            'diverifikasi_oleh' => auth()->id(),
            'diverifikasi_pada' => now(),
        ]);

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
