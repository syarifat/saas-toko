<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Produk;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StokOpnameController extends Controller
{
    public function index(): View
    {
        $toko = auth()->user()->toko;
        $gudang = $toko->gudangUtama();

        return view('stok-opname.index', [
            'gudang' => $gudang,
            'produk' => Produk::with(['stokGudang' => fn ($q) => $q->where('gudang_id', $gudang->id)])
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gudang_id' => ['required', 'exists:gudang,id'],
            'opname' => ['required', 'array'],
            'opname.*.produk_id' => ['required', 'exists:produk,id'],
            'opname.*.jumlah_fisik' => ['required', 'integer', 'min:0'],
        ]);

        $toko = $request->user()->toko;
        $gudang = Gudang::where('toko_id', $toko->id)->findOrFail($data['gudang_id']);
        $stokService = app(StokService::class);
        $diubah = 0;

        foreach ($data['opname'] as $baris) {
            if (! isset($baris['jumlah_fisik'])) {
                continue;
            }

            $produk = Produk::where('toko_id', $toko->id)->findOrFail($baris['produk_id']);
            $sebelum = $stokService->stok($produk, $gudang)->jumlah;

            if ($sebelum !== (int) $baris['jumlah_fisik']) {
                $stokService->opname($produk, $gudang, (int) $baris['jumlah_fisik'], $request->user()->id);
                $diubah++;
            }
        }

        return redirect()->route('stok-opname.index')
            ->with('status', "Stok opname selesai. {$diubah} produk disesuaikan.");
    }
}
