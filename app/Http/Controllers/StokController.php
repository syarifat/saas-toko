<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\StokGudang;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StokController extends Controller
{
    public function alert(): View
    {
        $produks = Produk::with(['kategori', 'stokGudang.gudang'])
            ->get()
            ->filter(fn ($p) => $p->totalStok() <= $p->stok_minimum);

        return view('tenant.stok.alert', compact('produks'));
    }

    public function opname(): View
    {
        $user = auth()->user();
        $gudangs = Gudang::where('toko_id', $user->toko_id)->get();
        $produks = Produk::with('stokGudang')->where('toko_id', $user->toko_id)->get();

        $riwayatOpname = PergerakanStok::with(['produk', 'gudang'])
            ->where('jenis', 'opname')
            ->latest()
            ->paginate(15);

        return view('tenant.stok.opname', compact('gudangs', 'produks', 'riwayatOpname'));
    }

    public function simpanOpname(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'gudang_id' => ['required', 'exists:gudang,id'],
            'jumlah_fisik' => ['required', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        $produk = Produk::where('toko_id', $user->toko_id)->where('id', $validated['produk_id'])->firstOrFail();
        $gudang = Gudang::where('toko_id', $user->toko_id)->where('id', $validated['gudang_id'])->firstOrFail();

        app(StokService::class)->opname(
            $produk,
            $gudang,
            $validated['jumlah_fisik'],
            $validated['catatan'] ?: ''
        );

        return back()->with('success', "Stok opname produk [{$produk->nama}] berhasil disesuaikan menjadi {$validated['jumlah_fisik']} unit.");
    }

    public function kartu(Request $request): View
    {
        $query = Produk::with(['kategori', 'stokGudang.gudang']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $produks = $query->paginate(15)->withQueryString();

        return view('tenant.stok.kartu', compact('produks'));
    }

    public function kartuProduk(Request $request, Produk $produk): View
    {
        $pergerakans = PergerakanStok::with(['gudang', 'gudangTujuan'])
            ->where('produk_id', $produk->id)
            ->latest()
            ->paginate(20);

        $stokPerGudang = StokGudang::with('gudang')
            ->where('produk_id', $produk->id)
            ->get();

        return view('tenant.stok.kartu_detail', compact('produk', 'pergerakans', 'stokPerGudang'));
    }
}
