<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $toko = $user->toko->load(['paket.modul', 'modulToko.modul']);

        $pakets = Paket::with('modul')
            ->where('aktif', true)
            ->where('id', '!=', $toko->paket_id)
            ->get();

        $semuaModul = Modul::where('aktif', true)->get();
        $addonModuls = $semuaModul->filter(fn ($m) => ! $toko->modulAktif($m->kode));

        $riwayatPembayaran = Pembayaran::with(['paket', 'modul'])
            ->where('toko_id', $toko->id)
            ->latest()
            ->paginate(10);

        return view('tenant.tagihan.index', compact('toko', 'pakets', 'addonModuls', 'riwayatPembayaran'));
    }

    public function ajukan(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'jenis' => ['required', 'in:upgrade_paket,aktivasi_addon'],
            'paket_id' => ['nullable', 'required_if:jenis,upgrade_paket', 'exists:paket,id'],
            'modul_id' => ['nullable', 'required_if:jenis,aktivasi_addon', 'exists:modul,id'],
            'jumlah' => ['required', 'numeric', 'min:1000'],
            'bukti_transfer' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');

        Pembayaran::create([
            'toko_id' => $user->toko_id,
            'jenis' => $validated['jenis'],
            'paket_id' => $validated['paket_id'] ?? null,
            'modul_id' => $validated['modul_id'] ?? null,
            'jumlah' => $validated['jumlah'],
            'bukti_transfer' => $path,
            'status' => 'menunggu',
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi Superadmin.');
    }
}
