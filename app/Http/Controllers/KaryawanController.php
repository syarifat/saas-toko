<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    /**
     * Dapatkan daftar sub-peran yang diizinkan untuk toko ini berdasarkan paket/modul aktif.
     * - Paket 2 (kasir_pos): hanya 'kasir'
     * - Paket 3 (multi_gudang / barang_masuk / transfer_gudang): 'kasir' dan 'gudang'
     * - Modul 'karyawan' aktif: 'kasir', 'gudang', dan staf umum (null)
     */
    private function getAllowedSubPerans(?Toko $toko): array
    {
        if (! $toko) {
            return [];
        }

        $allowed = [];

        // Paket 2 & 3: Kasir POS
        if ($toko->modulAktif('kasir_pos')) {
            $allowed[] = 'kasir';
        }

        // Paket 3: Multi Gudang & Manajemen Pergudangan
        if ($toko->modulAktif('multi_gudang') || $toko->modulAktif('barang_masuk') || $toko->modulAktif('transfer_gudang')) {
            $allowed[] = 'gudang';
        }

        return array_values(array_unique($allowed));
    }

    private function ensureHasEmployeeAccess(Pengguna $user, Toko $toko): void
    {
        if (! $user->isAdmin()) {
            abort(403, 'Hanya Admin/Pemilik Toko yang memiliki wewenang mengelola akun dan hak akses staf.');
        }

        $allowed = $this->getAllowedSubPerans($toko);

        if (empty($allowed) && ! $toko->modulAktif('karyawan')) {
            abort(403, 'Akses kelola akun staf karyawan membutuhkan minimal Paket 2 (POS Kasir) atau Paket 3 (Gudang).');
        }
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $karyawans = Karyawan::with('pengguna')->latest()->paginate(15);
        $allowedSubPerans = $this->getAllowedSubPerans($toko);

        return view('tenant.karyawan.index', compact('karyawans', 'allowedSubPerans'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $allowedSubPerans = $this->getAllowedSubPerans($toko);
        $hasHrModule = $toko->modulAktif('karyawan');

        return view('tenant.karyawan.create', compact('allowedSubPerans', 'hasHrModule'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $allowedSubPerans = $this->getAllowedSubPerans($toko);
        $hasHrModule = $toko->modulAktif('karyawan');

        // Validasi sub-peran yang diizinkan sesuai paket
        $subPeranRule = ['nullable'];
        if (! empty($allowedSubPerans)) {
            $subPeranRule[] = Rule::in($allowedSubPerans);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:6'],
            'sub_peran' => $subPeranRule,
            'kode_karyawan' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('karyawan')->where('toko_id', $user->toko_id),
            ],
            'posisi' => ['nullable', 'string', 'max:100'],
            'skema_gaji' => ['nullable', 'in:harian,bulanan'],
            'tarif_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tanggal_masuk' => ['nullable', 'date'],
        ]);

        // Jika sub_peran kosong dan tidak punya modul karyawan, tentukan default
        if (empty($validated['sub_peran'])) {
            if (in_array('kasir', $allowedSubPerans) && ! in_array('gudang', $allowedSubPerans)) {
                $validated['sub_peran'] = 'kasir';
            } elseif (! $hasHrModule) {
                $validated['sub_peran'] = $allowedSubPerans[0] ?? null;
            }
        }

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
                'kode_karyawan' => $validated['kode_karyawan'] ?? 'KRY-'.rand(100, 999),
                'posisi' => $validated['posisi'] ?? ($validated['sub_peran'] ? 'Staff '.ucfirst($validated['sub_peran']) : 'Staff Toko'),
                'skema_gaji' => $validated['skema_gaji'] ?? 'bulanan',
                'tarif_harian' => $validated['tarif_harian'] ?? 0,
                'gaji_pokok' => $validated['gaji_pokok'] ?? 0,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? now()->toDateString(),
                'aktif' => true,
            ]);
        });

        return redirect()->route('karyawan.index')->with('success', 'Akun staf karyawan berhasil dibuat.');
    }

    public function edit(Request $request, Karyawan $karyawan): View
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $karyawan->load('pengguna');
        $allowedSubPerans = $this->getAllowedSubPerans($toko);
        $hasHrModule = $toko->modulAktif('karyawan');

        return view('tenant.karyawan.edit', compact('karyawan', 'allowedSubPerans', 'hasHrModule'));
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $allowedSubPerans = $this->getAllowedSubPerans($toko);

        $subPeranRule = ['nullable'];
        if (! empty($allowedSubPerans)) {
            $subPeranRule[] = Rule::in($allowedSubPerans);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'sub_peran' => $subPeranRule,
            'kode_karyawan' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('karyawan')->where('toko_id', $user->toko_id)->ignore($karyawan->id),
            ],
            'posisi' => ['nullable', 'string', 'max:100'],
            'skema_gaji' => ['nullable', 'in:harian,bulanan'],
            'tarif_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tanggal_masuk' => ['nullable', 'date'],
            'aktif' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $karyawan) {
            $karyawan->pengguna->update([
                'nama' => $validated['nama'],
                'sub_peran' => $validated['sub_peran'] ?? null,
                'aktif' => $validated['aktif'] ?? true,
            ]);

            $karyawan->update([
                'kode_karyawan' => $validated['kode_karyawan'] ?? $karyawan->kode_karyawan,
                'posisi' => $validated['posisi'] ?? $karyawan->posisi,
                'skema_gaji' => $validated['skema_gaji'] ?? $karyawan->skema_gaji,
                'tarif_harian' => $validated['tarif_harian'] ?? $karyawan->tarif_harian,
                'gaji_pokok' => $validated['gaji_pokok'] ?? $karyawan->gaji_pokok,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? $karyawan->tanggal_masuk,
                'aktif' => $validated['aktif'] ?? true,
            ]);
        });

        return redirect()->route('karyawan.index')->with('success', 'Data staf karyawan berhasil diperbarui.');
    }

    public function destroy(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        DB::transaction(function () use ($karyawan) {
            $karyawan->pengguna()->delete();
            $karyawan->delete();
        });

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    /**
     * Halaman konfigurasi hak akses menu untuk karyawan toko (Paket 2 & Paket 3).
     */
    public function hakAkses(Request $request): View
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $karyawans = Karyawan::with(['pengguna.aksesModul'])->latest()->get();
        $modulAktif = $toko->modul()->wherePivot('aktif', true)->orderBy('nama')->get();

        return view('tenant.karyawan.hak-akses', compact('karyawans', 'modulAktif'));
    }

    /**
     * Simpan perubahan toggle hak akses menu per karyawan.
     */
    public function simpanHakAkses(Request $request): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;
        $this->ensureHasEmployeeAccess($user, $toko);

        $karyawanPenggunaIds = Karyawan::pluck('pengguna_id')->toArray();
        $modulAktifIds = $toko->modul()->wherePivot('aktif', true)->pluck('modul.id')->toArray();

        foreach ($karyawanPenggunaIds as $penggunaId) {
            $pengguna = Pengguna::find($penggunaId);
            if ($pengguna && $pengguna->toko_id === $toko->id) {
                $selectedModulIds = $request->input("akses.{$penggunaId}", []);
                $validModulIds = array_intersect($selectedModulIds, $modulAktifIds);
                $pengguna->aksesModul()->sync($validModulIds);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan hak akses menu staf berhasil disimpan.');
    }
}
