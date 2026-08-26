<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function index(): View
    {
        return view('karyawan.index', [
            'karyawan' => auth()->user()->toko->karyawan()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('karyawan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $toko = $request->user()->toko;

        $data = $this->validasi($request);
        $data['kode_karyawan'] = $this->buatKode($toko->id);

        $toko->karyawan()->create($data);

        return redirect()->route('karyawan.index')->with('status', 'Karyawan "'.$data['nama'].'" ditambahkan.');
    }

    public function edit(Karyawan $karyawan): View
    {
        return view('karyawan.edit', ['karyawan' => $karyawan]);
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update($this->validasi($request));

        return redirect()->route('karyawan.index')->with('status', 'Data karyawan diperbarui.');
    }

    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('status', 'Karyawan dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'posisi' => ['nullable', 'string', 'max:255'],
            'skema_gaji' => ['required', 'in:harian,bulanan'],
            'tarif_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tanggal_masuk' => ['required', 'date'],
            'aktif' => ['boolean'],
        ]);
    }

    private function buatKode(int $tokoId): string
    {
        $urut = (int) Karyawan::where('toko_id', $tokoId)->max('id') + 1;

        return 'KRJ'.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
