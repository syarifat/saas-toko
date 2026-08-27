<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GudangController extends Controller
{
    public function index(): View
    {
        $gudangs = Gudang::withCount('stokGudang')->latest()->paginate(15);

        return view('tenant.gudang.index', compact('gudangs'));
    }

    public function create(): View
    {
        return view('tenant.gudang.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:etalase,gudang'],
        ]);

        Gudang::create([
            'toko_id' => $request->user()->toko_id,
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
        ]);

        return redirect()->route('gudang.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function edit(Gudang $gudang): View
    {
        return view('tenant.gudang.edit', compact('gudang'));
    }

    public function update(Request $request, Gudang $gudang): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:etalase,gudang'],
        ]);

        $gudang->update($validated);

        return redirect()->route('gudang.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Gudang $gudang): RedirectResponse
    {
        if ($gudang->stokGudang()->where('jumlah', '>', 0)->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki stok produk.');
        }

        $gudang->delete();

        return redirect()->route('gudang.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
