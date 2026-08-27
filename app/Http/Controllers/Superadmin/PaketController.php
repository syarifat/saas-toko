<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Paket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaketController extends Controller
{
    public function index(): View
    {
        $pakets = Paket::with(['modul' => fn ($q) => $q->orderBy('id')])
            ->withCount('toko')
            ->get();

        return view('superadmin.paket.index', compact('pakets'));
    }

    public function create(): View
    {
        $moduls = Modul::with('ketergantungan')->get();

        return view('superadmin.paket.create', compact('moduls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:preset_1,preset_2,preset_3,custom'],
            'harga' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'aktif' => ['boolean'],
            'modul_ids' => ['required', 'array', 'min:1'],
            'modul_ids.*' => ['exists:modul,id'],
        ]);

        // Validasi graph dependency pada pilihan modul
        $modulDipilih = Modul::whereIn('id', $validated['modul_ids'])->with('ketergantungan')->get();
        foreach ($modulDipilih as $modul) {
            foreach ($modul->ketergantungan as $dep) {
                if (! in_array($dep->id, $validated['modul_ids'], false)) {
                    return back()->withInput()->withErrors([
                        'modul_ids' => "Modul [{$modul->nama}] membutuhkan modul [{$dep->nama}] untuk ikut dipilih.",
                    ]);
                }
            }
        }

        $paket = Paket::create([
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'harga' => $validated['harga'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'aktif' => $request->boolean('aktif', true),
        ]);

        $paket->modul()->sync($validated['modul_ids']);

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil dibuat.');
    }

    public function show(Paket $paket): View
    {
        $paket->load(['modul.ketergantungan', 'toko']);

        return view('superadmin.paket.show', compact('paket'));
    }

    public function edit(Paket $paket): View
    {
        $paket->load('modul');
        $moduls = Modul::with('ketergantungan')->get();

        return view('superadmin.paket.edit', compact('paket', 'moduls'));
    }

    public function update(Request $request, Paket $paket): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:preset_1,preset_2,preset_3,custom'],
            'harga' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'aktif' => ['boolean'],
            'modul_ids' => ['required', 'array', 'min:1'],
            'modul_ids.*' => ['exists:modul,id'],
        ]);

        // Validasi graph dependency pada pilihan modul
        $modulDipilih = Modul::whereIn('id', $validated['modul_ids'])->with('ketergantungan')->get();
        foreach ($modulDipilih as $modul) {
            foreach ($modul->ketergantungan as $dep) {
                if (! in_array($dep->id, $validated['modul_ids'], false)) {
                    return back()->withInput()->withErrors([
                        'modul_ids' => "Modul [{$modul->nama}] membutuhkan modul [{$dep->nama}] untuk ikut dipilih.",
                    ]);
                }
            }
        }

        $paket->update([
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'harga' => $validated['harga'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'aktif' => $request->boolean('aktif', true),
        ]);

        $paket->modul()->sync($validated['modul_ids']);

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket): RedirectResponse
    {
        if ($paket->toko()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus paket yang sedang digunakan oleh toko.');
        }

        $paket->modul()->detach();
        $paket->delete();

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
