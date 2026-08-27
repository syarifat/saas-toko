<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PemasokController extends Controller
{
    public function index(): View
    {
        $pemasoks = Pemasok::withCount('produk')->latest()->paginate(15);

        return view('tenant.pemasok.index', compact('pemasoks'));
    }

    public function create(): View
    {
        return view('tenant.pemasok.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
        ]);

        Pemasok::create([
            'toko_id' => $request->user()->toko_id,
            'nama' => $validated['nama'],
            'telepon' => $validated['telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function edit(Pemasok $pemasok): View
    {
        return view('tenant.pemasok.edit', compact('pemasok'));
    }

    public function update(Request $request, Pemasok $pemasok): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
        ]);

        $pemasok->update($validated);

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Pemasok $pemasok): RedirectResponse
    {
        $pemasok->delete();

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
