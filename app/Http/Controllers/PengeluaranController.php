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
        $pengeluaran = Pengeluaran::with('pencatat')
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_pengeluaran', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_pengeluaran', '<=', $request->sampai))
            ->latest('tanggal_pengeluaran')
            ->paginate(15)
            ->withQueryString();

        return view('pengeluaran.index', [
            'pengeluaran' => $pengeluaran,
            'total' => Pengeluaran::query()
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_pengeluaran', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_pengeluaran', '<=', $request->sampai))
                ->sum('nominal'),
        ]);
    }

    public function create(): View
    {
        return view('pengeluaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        if ($request->hasFile('bukti_struk')) {
            $data['bukti_struk'] = $request->file('bukti_struk')->store('struk', 'public');
        }

        $request->user()->toko->pengeluaran()->create([
            ...$data,
            'pengguna_id' => $request->user()->id,
        ]);

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Pengeluaran $pengeluaran): View
    {
        $this->authorize('update', $pengeluaran);

        return view('pengeluaran.edit', ['pengeluaran' => $pengeluaran]);
    }

    public function update(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->authorize('update', $pengeluaran);

        $data = $this->validasi($request);

        if ($request->hasFile('bukti_struk')) {
            if ($pengeluaran->bukti_struk) {
                Storage::disk('public')->delete($pengeluaran->bukti_struk);
            }
            $data['bukti_struk'] = $request->file('bukti_struk')->store('struk', 'public');
        }

        $pengeluaran->update($data);

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->authorize('delete', $pengeluaran);

        if ($pengeluaran->bukti_struk) {
            Storage::disk('public')->delete($pengeluaran->bukti_struk);
        }
        $pengeluaran->delete();

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        return $request->validate([
            'tanggal_pengeluaran' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'bukti_struk' => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
