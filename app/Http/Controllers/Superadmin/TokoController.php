<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use App\Services\ModulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TokoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Toko::with('paket')->withCount('pengguna');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('paket_id')) {
            $query->where('paket_id', $request->paket_id);
        }

        $tokos = $query->latest()->paginate(15)->withQueryString();
        $pakets = Paket::where('aktif', true)->get();

        return view('superadmin.toko.index', compact('tokos', 'pakets'));
    }

    public function create(): View
    {
        $pakets = Paket::where('aktif', true)->get();

        return view('superadmin.toko.create', compact('pakets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'paket_id' => ['required', 'exists:paket,id'],
            'admin_nama' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:pengguna,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'garis_lintang' => ['nullable', 'numeric', 'between:-90,90'],
            'garis_bujur' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_absensi' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        DB::transaction(function () use ($validated) {
            $slug = Str::slug($validated['nama']);
            $baseSlug = $slug;
            $counter = 1;
            while (Toko::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $paket = Paket::findOrFail($validated['paket_id']);

            $toko = Toko::create([
                'nama' => $validated['nama'],
                'slug' => $slug,
                'paket_id' => $paket->id,
                'status' => 'aktif',
                'garis_lintang' => $validated['garis_lintang'] ?? null,
                'garis_bujur' => $validated['garis_bujur'] ?? null,
                'radius_absensi' => $validated['radius_absensi'] ?? 100,
                'langganan_berakhir_pada' => now()->addMonth(),
            ]);

            // Sinkronisasi modul preset
            app(ModulService::class)->pakaiPreset($toko, $paket);

            // Buat admin pengguna
            Pengguna::create([
                'toko_id' => $toko->id,
                'nama' => $validated['admin_nama'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'peran' => 'admin',
                'aktif' => true,
            ]);
        });

        return redirect()->route('superadmin.toko.index')
            ->with('success', 'Toko dan akun admin berhasil dibuat.');
    }

    public function show(Toko $toko): View
    {
        $toko->load(['paket', 'modulToko.modul']);

        $semuaModul = Modul::with(['ketergantungan', 'dependan'])->get();
        $pakets = Paket::where('aktif', true)->get();

        $modulStatus = $semuaModul->map(function ($modul) use ($toko) {
            $isAktif = $toko->modulAktif($modul->kode);
            $belumAktif = app(ModulService::class)->getDependencyBelumAktif($toko, $modul);
            $dependanAktif = app(ModulService::class)->getDependanAktif($toko, $modul);

            return [
                'modul' => $modul,
                'aktif' => $isAktif,
                'siap_aktif' => $belumAktif->isEmpty(),
                'dependency_belum_aktif' => $belumAktif,
                'dependan_aktif' => $dependanAktif,
            ];
        });

        return view('superadmin.toko.show', compact('toko', 'modulStatus', 'pakets'));
    }

    public function edit(Toko $toko): View
    {
        $pakets = Paket::where('aktif', true)->get();

        return view('superadmin.toko.edit', compact('toko', 'pakets'));
    }

    public function update(Request $request, Toko $toko): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'garis_lintang' => ['nullable', 'numeric', 'between:-90,90'],
            'garis_bujur' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_absensi' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'langganan_berakhir_pada' => ['nullable', 'date'],
        ]);

        $toko->update($validated);

        return redirect()->route('superadmin.toko.show', $toko)
            ->with('success', 'Data toko berhasil diperbarui.');
    }

    public function destroy(Toko $toko): RedirectResponse
    {
        $toko->delete();

        return redirect()->route('superadmin.toko.index')
            ->with('success', 'Toko berhasil dihapus.');
    }

    public function pakaiPreset(Request $request, Toko $toko, Paket $paket): RedirectResponse
    {
        app(ModulService::class)->pakaiPreset($toko, $paket);

        return back()->with('success', "Preset [{$paket->nama}] berhasil diterapkan ke toko.");
    }
}
