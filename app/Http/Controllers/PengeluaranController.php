<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengeluaranController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengeluaran::with('pengguna');

        if ($request->filled('bulan')) {
            $parts = explode('-', $request->bulan);
            if (count($parts) === 2) {
                $query->whereYear('tanggal_pengeluaran', $parts[0])
                    ->whereMonth('tanggal_pengeluaran', $parts[1]);
            }
        }

        $pengeluarans = $query->latest('tanggal_pengeluaran')->paginate(15)->withQueryString();
        $totalPengeluaran = (float) $query->sum('nominal');

        return view('tenant.pengeluaran.index', compact('pengeluarans', 'totalPengeluaran'));
    }

    public function create(): View
    {
        return view('tenant.pengeluaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pengeluaran' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'bukti_struk' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('bukti_struk')) {
            $path = $request->file('bukti_struk')->store('bukti_pengeluaran', 'public');
        }

        Pengeluaran::create([
            'toko_id' => $request->user()->toko_id,
            'pengguna_id' => $request->user()->id,
            'tanggal_pengeluaran' => $validated['tanggal_pengeluaran'],
            'keterangan' => $validated['keterangan'],
            'nominal' => $validated['nominal'],
            'bukti_struk' => $path,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Pengeluaran $pengeluaran): View
    {
        return view('tenant.pengeluaran.edit', compact('pengeluaran'));
    }

    public function update(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pengeluaran' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'bukti_struk' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('bukti_struk')) {
            if ($pengeluaran->bukti_struk) {
                Storage::disk('public')->delete($pengeluaran->bukti_struk);
            }
            $validated['bukti_struk'] = $request->file('bukti_struk')->store('bukti_pengeluaran', 'public');
        }

        $pengeluaran->update($validated);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran): RedirectResponse
    {
        if ($pengeluaran->bukti_struk) {
            Storage::disk('public')->delete($pengeluaran->bukti_struk);
        }
        $pengeluaran->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
