<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Pemasok;
use App\Models\Produk;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $produk = Produk::with(['kategori', 'stokGudang'])
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('nama', 'like', '%'.$request->cari.'%')
                ->orWhere('sku', 'like', '%'.$request->cari.'%')))
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('kategori_id', $request->kategori_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('produk.index', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('produk.create', $this->opsiForm());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $produk = Produk::create($data + ['toko_id' => $request->user()->toko_id]);

        // Stok awal masuk ke gudang pertama (etalase default)
        if ((int) ($data['stok_awal'] ?? 0) > 0) {
            app(StokService::class)->masuk(
                $produk,
                $request->user()->toko->gudangUtama(),
                (int) $data['stok_awal'],
                $request->user()->id,
                'Stok awal',
            );
        }

        return redirect()->route('produk.index')->with('status', 'Produk "'.$produk->nama.'" ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        return view('produk.edit', [
            'produk' => $produk,
            ...$this->opsiForm(),
        ]);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $produk->update($this->validasi($request));

        return redirect()->route('produk.index')->with('status', 'Produk diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        $produk->delete();

        return redirect()->route('produk.index')->with('status', 'Produk dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        $id = $request->route('produk')?->id;

        return $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:produk,sku,'.($id ?? 'NULL').',id,toko_id,'.$request->user()->toko_id],
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'exists:kategori,id'],
            'pemasok_id' => ['nullable', 'exists:pemasok,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'stok_awal' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function opsiForm(): array
    {
        return [
            'kategori' => Kategori::orderBy('nama')->get(),
            'pemasok' => Pemasok::orderBy('nama')->get(),
        ];
    }
}
