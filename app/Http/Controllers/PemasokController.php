<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
        ]);

        $request->user()->toko->pemasok()->create($data);

        return back()->with('status', 'Pemasok ditambahkan.');
    }

    public function update(Request $request, Pemasok $pemasok): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
        ]);

        $pemasok->update($data);

        return back()->with('status', 'Pemasok diperbarui.');
    }

    public function destroy(Pemasok $pemasok): RedirectResponse
    {
        $pemasok->delete();

        return back()->with('status', 'Pemasok dihapus.');
    }
}
