<?php

namespace App\Services;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Toko;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ModulService
{
    /**
     * Aktifkan satu modul untuk toko.
     * Lempar ValidationException jika ada dependency yang belum aktif.
     */
    public function aktifkan(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $belumAktif = $this->getDependencyBelumAktif($toko, $modul);

        if ($belumAktif->isNotEmpty()) {
            $nama = $belumAktif->pluck('nama')->join(', ');
            throw ValidationException::withMessages([
                'modul' => "Modul [{$modul->nama}] membutuhkan modul berikut aktif terlebih dahulu: {$nama}.",
            ]);
        }

        ModulToko::updateOrCreate(
            ['toko_id' => $toko->id, 'modul_id' => $modul->id],
            ['aktif' => true, 'diaktifkan_pada' => now()]
        );
    }

    /**
     * Aktifkan modul beserta semua dependency-nya (rekursif).
     */
    public function aktifkanDenganDependency(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $deps = $this->semuaDependencyRekursif($modul);

        foreach ($deps as $dep) {
            if (! $toko->modulAktif($dep->kode)) {
                ModulToko::updateOrCreate(
                    ['toko_id' => $toko->id, 'modul_id' => $dep->id],
                    ['aktif' => true, 'diaktifkan_pada' => now()]
                );
            }
        }

        ModulToko::updateOrCreate(
            ['toko_id' => $toko->id, 'modul_id' => $modul->id],
            ['aktif' => true, 'diaktifkan_pada' => now()]
        );
    }

    /**
     * Nonaktifkan modul.
     * Lempar ValidationException jika ada modul dependen yang masih aktif.
     */
    public function nonaktifkan(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $dependanAktif = $this->getDependanAktif($toko, $modul);

        if ($dependanAktif->isNotEmpty()) {
            $nama = $dependanAktif->pluck('nama')->join(', ');
            throw ValidationException::withMessages([
                'modul' => "Tidak bisa menonaktifkan [{$modul->nama}]. Modul berikut bergantung padanya: {$nama}.",
            ]);
        }

        ModulToko::where('toko_id', $toko->id)
            ->where('modul_id', $modul->id)
            ->update(['aktif' => false]);
    }

    /**
     * Sinkronisasi semua modul dari preset ke toko.
     */
    public function pakaiPreset(Toko $toko, Paket $paket): void
    {
        $paket->loadMissing('modul');

        foreach ($paket->modul as $modul) {
            ModulToko::updateOrCreate(
                ['toko_id' => $toko->id, 'modul_id' => $modul->id],
                ['aktif' => true, 'diaktifkan_pada' => now()]
            );
        }

        $toko->update(['paket_id' => $paket->id]);
    }

    /**
     * Cek apakah modul aktif untuk toko.
     */
    public function aktifUntuk(Toko $toko, string $kodeModul): bool
    {
        return $toko->modulAktif($kodeModul);
    }

    /**
     * Ambil dependency yang belum aktif untuk toko.
     */
    public function getDependencyBelumAktif(Toko $toko, Modul $modul): Collection
    {
        $modul->loadMissing('ketergantungan');

        return $modul->ketergantungan->filter(
            fn ($dep) => ! $toko->modulAktif($dep->kode)
        );
    }

    /**
     * Ambil modul dependen yang masih aktif untuk toko.
     */
    public function getDependanAktif(Toko $toko, Modul $modul): Collection
    {
        $modul->loadMissing('dependan');

        return $modul->dependan->filter(
            fn ($dep) => $toko->modulAktif($dep->kode)
        );
    }

    /**
     * Rekursif DFS: ambil semua dependency terurut (daun dulu).
     *
     * @param  array<int, int>  $visited
     * @return Collection<int, Modul>
     */
    private function semuaDependencyRekursif(Modul $modul, array &$visited = []): Collection
    {
        $modul->loadMissing('ketergantungan');
        $result = collect();

        foreach ($modul->ketergantungan as $dep) {
            if (! in_array($dep->id, $visited, true)) {
                $visited[] = $dep->id;
                $result = $result->merge($this->semuaDependencyRekursif($dep, $visited));
                $result->push($dep);
            }
        }

        return $result;
    }
}
