<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $toko = $user->toko;
        $karyawan = $user->karyawan;

        $absensiHariIni = null;
        if ($karyawan) {
            $absensiHariIni = Absensi::where('toko_id', $user->toko_id)
                ->where('karyawan_id', $karyawan->id)
                ->where('tanggal', today())
                ->first();
        }

        return view('tenant.absensi.index', compact('toko', 'karyawan', 'absensiHariIni'));
    }

    public function masuk(Request $request): RedirectResponse
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (! $karyawan) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan profil karyawan.');
        }

        $toko = $user->toko;

        $validated = $request->validate([
            'lintang' => ['nullable', 'numeric'],
            'bujur' => ['nullable', 'numeric'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        // Verifikasi jarak Geofence (Haversine Formula) jika toko telah set koordinat
        if ($toko->garis_lintang && $toko->garis_bujur && $validated['lintang'] && $validated['bujur']) {
            $distance = $this->haversineGreatCircleDistance(
                (float) $toko->garis_lintang,
                (float) $toko->garis_bujur,
                (float) $validated['lintang'],
                (float) $validated['bujur']
            );

            if ($distance > $toko->radius_absensi) {
                return back()->with('error', "Lokasi Anda berada di luar radius toko ({$distance} meter, maksimal {$toko->radius_absensi}m).");
            }
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi_masuk', 'public');
        }

        $now = now();
        $jamMasukStandar = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
        $menitTelat = 0;
        $status = 'tepat_waktu';

        if ($now->greaterThan($jamMasukStandar)) {
            $menitTelat = $jamMasukStandar->diffInMinutes($now);
            $status = 'telat';
        }

        Absensi::updateOrCreate(
            [
                'toko_id' => $user->toko_id,
                'karyawan_id' => $karyawan->id,
                'tanggal' => today(),
            ],
            [
                'jam_masuk' => $now,
                'lintang_masuk' => $validated['lintang'] ?? null,
                'bujur_masuk' => $validated['bujur'] ?? null,
                'foto_masuk' => $fotoPath,
                'status' => $status,
                'menit_telat' => $menitTelat,
            ]
        );

        return back()->with('success', 'Presensi masuk berhasil dicatat. Selamat bekerja!');
    }

    public function keluar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (! $karyawan) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan profil karyawan.');
        }

        $absensi = Absensi::where('toko_id', $user->toko_id)
            ->where('karyawan_id', $karyawan->id)
            ->where('tanggal', today())
            ->first();

        if (! $absensi || ! $absensi->jam_masuk) {
            return back()->with('error', 'Anda belum melakukan absensi masuk hari ini.');
        }

        $validated = $request->validate([
            'lintang' => ['nullable', 'numeric'],
            'bujur' => ['nullable', 'numeric'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi_keluar', 'public');
        }

        $now = now();
        $jamPulangStandar = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
        $menitLembur = 0;

        if ($now->greaterThan($jamPulangStandar)) {
            $menitLembur = $jamPulangStandar->diffInMinutes($now);
        }

        $absensi->update([
            'jam_keluar' => $now,
            'lintang_keluar' => $validated['lintang'] ?? null,
            'bujur_keluar' => $validated['bujur'] ?? null,
            'foto_keluar' => $fotoPath,
            'menit_lembur' => $menitLembur,
        ]);

        return back()->with('success', 'Presensi keluar berhasil dicatat. Terima kasih atas kerja keras Anda!');
    }

    public function rekap(Request $request): View
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $parts = explode('-', $bulan);
        $tahun = (int) ($parts[0] ?? now()->year);
        $bln = (int) ($parts[1] ?? now()->month);

        $query = Absensi::with('karyawan.pengguna')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln);

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->latest('tanggal')->paginate(20)->withQueryString();
        $karyawans = Karyawan::with('pengguna')->get();

        return view('tenant.absensi.rekap', compact('absensis', 'karyawans', 'bulan'));
    }

    /**
     * Hitung jarak geolokasi (Haversine Formula) dalam satuan meter.
     */
    private function haversineGreatCircleDistance(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): int {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return (int) round($angle * $earthRadius);
    }
}
