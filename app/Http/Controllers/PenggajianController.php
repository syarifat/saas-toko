<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Penggajian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenggajianController extends Controller
{
    public function index(Request $request): View
    {
        $toko = $request->user()->toko;

        return view('penggajian.index', [
            'penggajian' => $toko->penggajian()
                ->with(['karyawan', 'komponen'])
                ->when($request->filled('periode_mulai'), fn ($q) => $q->where('periode_mulai', '>=', $request->periode_mulai))
                ->latest('periode_selesai')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('penggajian.create', [
            'karyawan' => auth()->user()->toko->karyawan()->where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'karyawan_id' => ['required', 'exists:karyawan,id'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'komponen' => ['nullable', 'array'],
            'komponen.*.jenis' => ['required_with:komponen.*.nama', 'in:tunjangan,potongan'],
            'komponen.*.nama' => ['nullable', 'string', 'max:255'],
            'komponen.*.nominal' => ['nullable', 'numeric', 'min:0'],
        ]);

        $toko = $request->user()->toko;
        /** @var Karyawan $karyawan */
        $karyawan = $toko->karyawan()->findOrFail($data['karyawan_id']);

        $jumlahHadir = $karyawan->jumlahHadir($data['periode_mulai'], $data['periode_selesai']);
        $jumlahDasar = $karyawan->skema_gaji === 'harian'
            ? (float) $karyawan->tarif_harian * $jumlahHadir
            : (float) $karyawan->gaji_pokok;

        $tunjangan = 0.0;
        $potongan = 0.0;

        foreach (($data['komponen'] ?? []) as $baris) {
            if (empty($baris['nama']) || ! isset($baris['nominal'])) {
                continue;
            }

            if ($baris['jenis'] === 'tunjangan') {
                $tunjangan += (float) $baris['nominal'];
            } else {
                $potongan += (float) $baris['nominal'];
            }
        }

        DB::transaction(function () use ($data, $toko, $karyawan, $jumlahHadir, $jumlahDasar, $tunjangan, $potongan) {
            $penggajian = $toko->penggajian()->create([
                'karyawan_id' => $karyawan->id,
                'periode_mulai' => $data['periode_mulai'],
                'periode_selesai' => $data['periode_selesai'],
                'skema_gaji_snapshot' => $karyawan->skema_gaji,
                'jumlah_dasar' => $jumlahDasar,
                'jumlah_hadir' => $jumlahHadir,
                'total_tunjangan' => $tunjangan,
                'total_potongan' => $potongan,
                'gaji_bersih' => max($jumlahDasar + $tunjangan - $potongan, 0),
                'status' => 'draf',
            ]);

            foreach (($data['komponen'] ?? []) as $baris) {
                if (empty($baris['nama']) || ! isset($baris['nominal'])) {
                    continue;
                }

                $penggajian->komponen()->create([
                    'toko_id' => $toko->id,
                    'jenis' => $baris['jenis'],
                    'nama' => $baris['nama'],
                    'nominal' => $baris['nominal'],
                ]);
            }
        });

        return redirect()->route('penggajian.index')->with('status', 'Penggajian untuk '.$karyawan->nama.' berhasil dibuat.');
    }

    public function tandaiDibayar(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $penggajian->update([
            'status' => 'dibayar',
            'dibayar_pada' => now(),
        ]);

        return back()->with('status', 'Penggajian #'.$penggajian->id.' ditandai sudah dibayar.');
    }
}
