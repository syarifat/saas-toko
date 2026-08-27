<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Services\StokService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferGudangController extends Controller
{
    public function index(): View
    {
        $riwayat = PergerakanStok::with(['produk', 'gudang', 'gudangTujuan'])
            ->where('jenis', 'transfer')
            ->whereNotNull('gudang_tujuan_id')
            ->latest()
            ->paginate(15);

        $produks = Produk::all();
        $gudangs = Gudang::all();

        return view('tenant.gudang.transfer', compact('riwayat', 'produks', 'gudangs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'gudang_asal_id' => ['required', 'exists:gudang,id', 'different:gudang_tujuan_id'],
            'gudang_tujuan_id' => ['required', 'exists:gudang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string'],
        ]);

        $produk = Produk::where('toko_id', $user->toko_id)->where('id', $validated['produk_id'])->firstOrFail();
        $gudangAsal = Gudang::where('toko_id', $user->toko_id)->where('id', $validated['gudang_asal_id'])->firstOrFail();
        $gudangTujuan = Gudang::where('toko_id', $user->toko_id)->where('id', $validated['gudang_tujuan_id'])->firstOrFail();

        try {
            app(StokService::class)->transferStok(
                $produk,
                $gudangAsal,
                $gudangTujuan,
                $validated['jumlah'],
                $validated['catatan'] ?: ''
            );

            return back()->with('success', "Transfer {$validated['jumlah']} unit [{$produk->nama}] dari [{$gudangAsal->nama}] ke [{$gudangTujuan->nama}] berhasil.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal transfer: '.$e->getMessage());
        }
    }
}
