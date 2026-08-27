<?php

namespace App\Http\Controllers;

use App\Models\ItemPenjualanSederhana;
use App\Models\PenjualanSederhana;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenjualanSederhanaController extends Controller
{
    public function index(): View
    {
        $penjualans = PenjualanSederhana::with(['pengguna', 'items.produk'])
            ->latest('tanggal_penjualan')
            ->latest('id')
            ->paginate(15);

        return view('tenant.penjualan.index', compact('penjualans'));
    }

    public function create(): View
    {
        $produks = Produk::all();

        return view('tenant.penjualan.create', compact('produks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'tanggal_penjualan' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'exists:produk,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $total = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $produk = Produk::where('toko_id', $user->toko_id)->where('id', $item['produk_id'])->firstOrFail();
                $subtotal = $item['jumlah'] * $item['harga_satuan'];
                $total += $subtotal;

                $itemsData[] = [
                    'produk' => $produk,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                    'harga_beli_snapshot' => $produk->harga_beli,
                ];
            }

            $penjualan = PenjualanSederhana::create([
                'toko_id' => $user->toko_id,
                'pengguna_id' => $user->id,
                'tanggal_penjualan' => $validated['tanggal_penjualan'],
                'total' => $total,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($itemsData as $data) {
                ItemPenjualanSederhana::create([
                    'toko_id' => $user->toko_id,
                    'penjualan_sederhana_id' => $penjualan->id,
                    'produk_id' => $data['produk']->id,
                    'nama_produk' => $data['produk']->nama,
                    'jumlah' => $data['jumlah'],
                    'harga_satuan' => $data['harga_satuan'],
                    'subtotal' => $data['subtotal'],
                    'harga_beli_snapshot' => $data['harga_beli_snapshot'],
                ]);
            }
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dicatat.');
    }

    public function show(PenjualanSederhana $penjualanSederhana): View
    {
        $penjualanSederhana->load(['pengguna', 'items.produk']);

        return view('tenant.penjualan.show', ['penjualan' => $penjualanSederhana]);
    }

    public function destroy(PenjualanSederhana $penjualanSederhana): RedirectResponse
    {
        $penjualanSederhana->items()->delete();
        $penjualanSederhana->delete();

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus.');
    }
}
