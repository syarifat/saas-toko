<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferStokController extends Controller
{
    public function index(): View
    {
        $toko = auth()->user()->toko;

        return view('transfer-stok.index', [
            'riwayat' => PergerakanStok::with(['produk', 'gudang', 'gudangTujuan'])
                ->where('jenis', 'transfer')
                ->latest()
                ->take(20)
                ->get(),
            'gudang' => $toko->gudang()->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        $toko = auth()->user()->toko;

        return view('transfer-stok.create', [
            'produk' => Produk::with('stokGudang')->where('toko_id', $toko->id)->orderBy('nama')->get(),
            'gudang' => $toko->gudang()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'gudang_asal_id' => ['required', 'exists:gudang,id'],
            'gudang_tujuan_id' => ['required', 'exists:gudang,id', 'different:gudang_asal_id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $toko = $request->user()->toko;
        $asal = Gudang::where('toko_id', $toko->id)->findOrFail($data['gudang_asal_id']);
        $tujuan = Gudang::where('toko_id', $toko->id)->findOrFail($data['gudang_tujuan_id']);
        $produk = Produk::where('toko_id', $toko->id)->findOrFail($data['produk_id']);

        try {
            app(StokService::class)->transfer(
                $produk,
                $asal,
                $tujuan,
                (int) $data['jumlah'],
                $request->user()->id,
                $data['catatan'] ?? null,
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['stok' => $e->getMessage()])->withInput();
        }

        return redirect()->route('transfer-stok.index')
            ->with('status', "Transfer berhasil: {$produk->nama} {$data['jumlah']} unit dari {$asal->nama} ke {$tujuan->nama}.");
    }
}
