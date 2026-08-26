<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
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
        $toko = auth()->user()->toko;

        return view('barang-masuk.index', [
            'riwayat' => PergerakanStok::with(['produk', 'gudang', 'pengguna'])
                ->where('jenis', 'masuk')
                ->latest()
                ->take(20)
                ->get(),
            'pemasok' => $toko->pemasok()->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        $toko = auth()->user()->toko;

        return view('barang-masuk.create', [
            'produk' => $toko->produk()->orderBy('nama')->get(),
            'gudang' => $toko->gudang()->orderBy('nama')->get(),
            'pemasok' => $toko->pemasok()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gudang_id' => ['required', 'exists:gudang,id'],
            'produk_id' => ['required', 'exists:produk,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $toko = $request->user()->toko;
        $gudang = Gudang::where('toko_id', $toko->id)->findOrFail($data['gudang_id']);
        $produk = Produk::where('toko_id', $toko->id)->findOrFail($data['produk_id']);

        app(StokService::class)->masuk(
            $produk,
            $gudang,
            (int) $data['jumlah'],
            $request->user()->id,
            $data['catatan'] ?? 'Penerimaan barang dari pemasok',
        );

        return redirect()->route('barang-masuk.index')
            ->with('status', "Barang masuk: {$produk->nama} +{$data['jumlah']} ke {$gudang->nama}.");
    }
}
