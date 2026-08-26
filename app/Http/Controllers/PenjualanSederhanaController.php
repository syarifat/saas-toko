<?php

namespace App\Http\Controllers;

use App\Models\ItemPenjualanSederhana;
use App\Models\PenjualanSederhana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenjualanSederhanaController extends Controller
{
    public function index(Request $request): View
    {
        $penjualan = PenjualanSederhana::with(['item', 'pencatat'])
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_penjualan', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_penjualan', '<=', $request->sampai))
            ->latest('tanggal_penjualan')
            ->paginate(15)
            ->withQueryString();

        return view('penjualan-sederhana.index', [
            'penjualan' => $penjualan,
            'total' => PenjualanSederhana::query()
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_penjualan', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_penjualan', '<=', $request->sampai))
                ->sum('total'),
        ]);
    }

    public function create(): View
    {
        return view('penjualan-sederhana.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_penjualan' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.nama_barang' => ['required', 'string', 'max:255'],
            'barang.*.jumlah' => ['required', 'integer', 'min:1'],
            'barang.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $penjualan = $request->user()->toko->penjualanSederhana()->create([
                'pengguna_id' => $request->user()->id,
                'tanggal_penjualan' => $data['tanggal_penjualan'],
                'catatan' => $data['catatan'] ?? null,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($data['barang'] as $baris) {
                $subtotal = (float) $baris['jumlah'] * (float) $baris['harga_satuan'];
                $total += $subtotal;

                ItemPenjualanSederhana::create([
                    'toko_id' => $penjualan->toko_id,
                    'penjualan_sederhana_id' => $penjualan->id,
                    'nama_barang' => $baris['nama_barang'],
                    'jumlah' => (int) $baris['jumlah'],
                    'harga_satuan' => $baris['harga_satuan'],
                    'subtotal' => $subtotal,
                ]);
            }

            $penjualan->update(['total' => $total]);
        });

        return redirect()->route('penjualan-sederhana.index')->with('status', 'Penjualan berhasil dicatat.');
    }

    public function destroy(PenjualanSederhana $penjualanSederhana): RedirectResponse
    {
        $penjualanSederhana->delete();

        return redirect()->route('penjualan-sederhana.index')->with('status', 'Penjualan dihapus.');
    }
}
