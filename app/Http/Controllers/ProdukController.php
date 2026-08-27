<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Kategori;
use App\Models\Pemasok;
use App\Models\Produk;
use App\Models\StokGudang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $query = Produk::with(['kategori', 'pemasok', 'stokGudang']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $produks = $query->latest()->paginate(15)->withQueryString();
        $kategoris = Kategori::all();

        return view('tenant.produk.index', compact('produks', 'kategoris'));
    }

    public function create(): View
    {
        $kategoris = Kategori::all();
        $pemasoks = Pemasok::all();
        $gudangs = Gudang::all();

        return view('tenant.produk.create', compact('kategoris', 'pemasoks', 'gudangs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('produk')->where('toko_id', $user->toko_id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'exists:kategori,id'],
            'pemasok_id' => ['nullable', 'exists:pemasok,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['nullable', 'integer', 'min:0'],
            'stok_awal' => ['nullable', 'integer', 'min:0'],
            'gudang_id' => ['nullable', 'exists:gudang,id'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $produk = Produk::create([
                'toko_id' => $user->toko_id,
                'sku' => $validated['sku'],
                'nama' => $validated['nama'],
                'kategori_id' => $validated['kategori_id'] ?? null,
                'pemasok_id' => $validated['pemasok_id'] ?? null,
                'harga_beli' => $validated['harga_beli'],
                'harga_jual' => $validated['harga_jual'],
                'stok_minimum' => $validated['stok_minimum'] ?? 0,
            ]);

            // Stok awal jika diisi
            $stokAwal = (int) ($validated['stok_awal'] ?? 0);
            if ($stokAwal > 0) {
                $gudangId = $validated['gudang_id'] ?? Gudang::where('toko_id', $user->toko_id)->first()?->id;

                if (! $gudangId) {
                    $gudangDefault = Gudang::create([
                        'toko_id' => $user->toko_id,
                        'nama' => 'Etalase Utama',
                        'jenis' => 'etalase',
                    ]);
                    $gudangId = $gudangDefault->id;
                }

                StokGudang::create([
                    'toko_id' => $user->toko_id,
                    'produk_id' => $produk->id,
                    'gudang_id' => $gudangId,
                    'jumlah' => $stokAwal,
                ]);
            }
        });

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Produk $produk): View
    {
        $produk->load(['kategori', 'pemasok', 'stokGudang.gudang', 'pergerakanStok']);

        return view('tenant.produk.show', compact('produk'));
    }

    public function edit(Produk $produk): View
    {
        $kategoris = Kategori::all();
        $pemasoks = Pemasok::all();

        return view('tenant.produk.edit', compact('produk', 'kategoris', 'pemasoks'));
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('produk')->where('toko_id', $user->toko_id)->ignore($produk->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'exists:kategori,id'],
            'pemasok_id' => ['nullable', 'exists:pemasok,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['nullable', 'integer', 'min:0'],
        ]);

        $produk->update($validated);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
