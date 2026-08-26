<?php

namespace App\Http\Controllers;

use App\Models\PergerakanStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KartuStokController extends Controller
{
    public function index(Request $request): View
    {
        $toko = $request->user()->toko;

        $produkId = $request->filled('produk_id') ? (int) $request->produk_id : null;
        $produkDipilih = $produkId ? Produk::where('toko_id', $toko->id)->findOrFail($produkId) : null;

        return view('kartu-stok.index', [
            'produkList' => $toko->produk()->orderBy('nama')->get(),
            'produkDipilih' => $produkDipilih,
            'pergerakan' => $produkDipilih
                ? PergerakanStok::with(['gudang', 'gudangTujuan', 'pengguna'])
                    ->where('produk_id', $produkDipilih->id)
                    ->latest()
                    ->paginate(30)
                    ->withQueryString()
                : collect(),
        ]);
    }
}
