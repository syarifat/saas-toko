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
        return view('gudang.index', [
            'gudang' => auth()->user()->toko->gudang()->with('stokGudang')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:etalase,gudang'],
        ]);

        $request->user()->toko->gudang()->create($data);

        return back()->with('status', 'Gudang "'.$data['nama'].'" ditambahkan.');
    }

    public function update(Request $request, Gudang $gudang): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:etalase,gudang'],
        ]);

        $gudang->update($data);

        return back()->with('status', 'Gudang diperbarui.');
    }

    public function destroy(Gudang $gudang): RedirectResponse
    {
        if ($gudang->stokGudang()->where('jumlah', '>', 0)->exists()) {
            return back()->withErrors(['gudang' => 'Gudang masih memiliki stok. Kosongkan atau pindahkan stoknya dulu.']);
        }

        $gudang->delete();

        return back()->with('status', 'Gudang dihapus.');
    }
}
