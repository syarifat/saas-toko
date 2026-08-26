<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Paket;
use App\Models\Pembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function index(): View
    {
        $toko = auth()->user()->toko;

        return view('tagihan.index', [
            'paket' => Paket::where('aktif', true)->orderBy('tingkat')->get(),
            'addon' => Addon::where('aktif', true)->get(),
            'pembayaran' => $toko->pembayaran()->with(['paket', 'addon'])->latest()->paginate(10),
            'toko' => $toko,
        ]);
    }

    public function ajukan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:paket,addon'],
            'paket_id' => ['nullable', 'required_if:jenis,paket', 'exists:paket,id'],
            'addon_id' => ['nullable', 'required_if:jenis,addon', 'exists:addon,id'],
            'jumlah_bulan' => ['required', 'integer', 'min:1', 'max:24'],
            'catatan_tenant' => ['nullable', 'string', 'max:500'],
            'bukti_transfer' => ['required', 'image', 'max:4096'],
        ]);

        $toko = $request->user()->toko;

        // Hitung nominal berdasarkan harga master
        if ($data['jenis'] === 'paket') {
            $item = Paket::findOrFail($data['paket_id']);
        } else {
            $item = Addon::findOrFail($data['addon_id']);
        }

        $nominal = (float) $item->harga * (int) $data['jumlah_bulan'];

        Pembayaran::create([
            'toko_id' => $toko->id,
            'pengguna_id' => $request->user()->id,
            'jenis' => $data['jenis'],
            'paket_id' => $data['jenis'] === 'paket' ? $item->id : null,
            'addon_id' => $data['jenis'] === 'addon' ? $item->id : null,
            'jumlah_bulan' => $data['jumlah_bulan'],
            'nominal' => $nominal,
            'status' => 'menunggu',
            'catatan_tenant' => $data['catatan_tenant'] ?? null,
            'bukti_transfer' => $request->file('bukti_transfer')->store('transfer', 'public'),
        ]);

        return redirect()->route('tagihan.index')
            ->with('status', 'Pengajuan "'.$item->nama.'" ('.$data['jumlah_bulan'].' bln, Rp '.number_format($nominal, 0, ',', '.').') terkirim. Menunggu verifikasi superadmin.');
    }
}
