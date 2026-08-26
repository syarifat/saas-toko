<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaketController extends Controller
{
    public function index(): View
    {
        return view('superadmin.paket.index', [
            'paket' => Paket::withCount('toko')->orderBy('tingkat')->get(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.paket.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Paket::create($this->validasi($request));

        return redirect()->route('superadmin.paket.index')->with('status', 'Paket baru ditambahkan.');
    }

    public function edit(Paket $paket): View
    {
        return view('superadmin.paket.edit', ['paket' => $paket]);
    }

    public function update(Request $request, Paket $paket): RedirectResponse
    {
        $paket->update($this->validasi($request));

        return redirect()->route('superadmin.paket.index')->with('status', 'Paket diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'integer', 'min:1', 'max:3', 'unique:paket,tingkat,'.($request->route('paket')?->id ?? '')],
            'harga' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'aktif' => ['boolean'],
        ]);
    }
}
