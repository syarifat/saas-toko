<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function index(): View
    {
        $karyawans = Karyawan::with('pengguna')->latest()->paginate(15);

        return view('tenant.karyawan.index', compact('karyawans'));
    }

    public function create(): View
    {
        return view('tenant.karyawan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:8'],
            'sub_peran' => ['nullable', 'in:kasir,gudang'],
            'kode_karyawan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('karyawan')->where('toko_id', $user->toko_id),
            ],
            'posisi' => ['nullable', 'string', 'max:100'],
            'skema_gaji' => ['required', 'in:harian,bulanan'],
            'tarif_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tanggal_masuk' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $pengguna = Pengguna::create([
                'toko_id' => $user->toko_id,
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'peran' => 'karyawan',
                'sub_peran' => $validated['sub_peran'] ?? null,
                'aktif' => true,
                'dibuat_oleh' => $user->id,
            ]);

            Karyawan::create([
                'toko_id' => $user->toko_id,
                'pengguna_id' => $pengguna->id,
                'kode_karyawan' => $validated['kode_karyawan'],
                'posisi' => $validated['posisi'] ?? null,
                'skema_gaji' => $validated['skema_gaji'],
                'tarif_harian' => $validated['tarif_harian'] ?? 0,
                'gaji_pokok' => $validated['gaji_pokok'] ?? 0,
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'aktif' => true,
            ]);
        });

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan dan akun login berhasil dibuat.');
    }

    public function edit(Karyawan $karyawan): View
    {
        $karyawan->load('pengguna');

        return view('tenant.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'sub_peran' => ['nullable', 'in:kasir,gudang'],
            'kode_karyawan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('karyawan')->where('toko_id', $user->toko_id)->ignore($karyawan->id),
            ],
            'posisi' => ['nullable', 'string', 'max:100'],
            'skema_gaji' => ['required', 'in:harian,bulanan'],
            'tarif_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tanggal_masuk' => ['required', 'date'],
            'aktif' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $karyawan) {
            $karyawan->pengguna->update([
                'nama' => $validated['nama'],
                'sub_peran' => $validated['sub_peran'] ?? null,
                'aktif' => $validated['aktif'] ?? true,
            ]);

            $karyawan->update([
                'kode_karyawan' => $validated['kode_karyawan'],
                'posisi' => $validated['posisi'] ?? null,
                'skema_gaji' => $validated['skema_gaji'],
                'tarif_harian' => $validated['tarif_harian'] ?? 0,
                'gaji_pokok' => $validated['gaji_pokok'] ?? 0,
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'aktif' => $validated['aktif'] ?? true,
            ]);
        });

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        DB::transaction(function () use ($karyawan) {
            $karyawan->pengguna()->delete();
            $karyawan->delete();
        });

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
