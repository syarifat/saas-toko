<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Pemasok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nama' => ['required', 'string', 'max:255']]);

        $request->user()->toko->kategori()->create(['nama' => $request->nama]);

        return back()->with('status', 'Kategori ditambahkan.');
    }

    public function index(): View
    {
        return view('kategori.index', [
            'kategori' => Kategori::withCount('produk')->orderBy('nama')->paginate(15),
            'pemasok' => Pemasok::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $request->validate(['nama' => ['required', 'string', 'max:255']]);
        $kategori->update(['nama' => $request->nama]);

        return back()->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $kategori->delete();

        return back()->with('status', 'Kategori dihapus.');
    }
}
