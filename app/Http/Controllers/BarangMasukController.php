<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Pemasok;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangMasukController extends Controller
{
    public function index(): View
    {
        $riwayat = PergerakanStok::with(['produk', 'gudang'])
            ->where('jenis', 'masuk')
            ->latest()
            ->paginate(15);

        $produks = Produk::all();
        $gudangs = Gudang::all();
        $pemasoks = Pemasok::all();

        return view('tenant.gudang.masuk', compact('riwayat', 'produks', 'gudangs', 'pemasoks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'gudang_id' => ['required', 'exists:gudang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string'],
        ]);

        $produk = Produk::where('toko_id', $user->toko_id)->where('id', $validated['produk_id'])->firstOrFail();
        $gudang = Gudang::where('toko_id', $user->toko_id)->where('id', $validated['gudang_id'])->firstOrFail();

        app(StokService::class)->tambahStok(
            $produk,
            $gudang,
            $validated['jumlah'],
            '',
            0,
            $validated['catatan'] ?: 'Barang masuk dari supplier/restock'
        );

        return back()->with('success', "Stok [{$produk->nama}] sebanyak {$validated['jumlah']} unit berhasil ditambahkan ke [{$gudang->nama}].");
    }
}
