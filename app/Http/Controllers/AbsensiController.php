<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    /**
     * Jarak haversine antara dua koordinat dalam meter.
     */
    public static function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function index(Request $request): View
    {
        $toko = $request->user()->toko;
        $karyawanSaya = $toko->karyawan()->where('pengguna_id', $request->user()->id)->first();

        $riwayat = Absensi::with('karyawan')
            ->when($request->filled('mulai'), fn ($q) => $q->where('tanggal', '>=', $request->mulai))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->sampai))
            ->when($karyawanSaya && ! in_array($request->user()->peran, ['admin'], true), fn ($q) => $q->where('karyawan_id', $karyawanSaya->id))
            ->latest('tanggal')
            ->latest('jam_masuk')
            ->paginate(20)
            ->withQueryString();

        return view('absensi.index', [
            'karyawanSaya' => $karyawanSaya,
            'sudahMasuk' => $karyawanSaya?->absensi()->whereDate('tanggal', today())->whereNotNull('jam_masuk')->exists() ?? false,
            'sudahKeluar' => $karyawanSaya?->absensi()->whereDate('tanggal', today())->whereNotNull('jam_keluar')->exists() ?? false,
            'toko' => $toko,
            'riwayat' => $riwayat,
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lintang' => ['required', 'numeric', 'between:-90,90'],
            'bujur' => ['required', 'numeric', 'between:-180,180'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ]);

        $toko = $request->user()->toko;
        $karyawan = $this->karyawanAtauGagal($request);
        if ($karyawan instanceof RedirectResponse) {
            return $karyawan;
        }

        if (Absensi::where('karyawan_id', $karyawan->id)->whereDate('tanggal', today())->exists()) {
            return back()->withErrors(['absensi' => 'Anda sudah absen hari ini.']);
        }

        $jarak = self::jarakMeter(
            (float) $data['lintang'],
            (float) $data['bujur'],
            (float) $toko->garis_lintang,
            (float) $toko->garis_bujur,
        );

        $radius = (int) ($toko->radius_absensi ?: 100);
        if ($jarak > $radius) {
            return back()->withErrors(['absensi' => 'Anda berada '.round($jarak).' m dari toko (maksimal '.$radius.' m). Dekati lokasi toko untuk absen.']);
        }

        $sekarang = now();
        // Batas jam masuk: 08:00 — lewat dihitung telat
        $batas = $sekarang->copy()->setTime(8, 0);
        $menitTelat = max($sekarang->diffInMinutes($batas, false) * -1, 0);

        $path = $request->hasFile('foto') ? $request->file('foto')->store('absensi', 'public') : null;

        Absensi::create([
            'toko_id' => $toko->id,
            'karyawan_id' => $karyawan->id,
            'tanggal' => today(),
            'jam_masuk' => $sekarang,
            'lintang_masuk' => $data['lintang'],
            'bujur_masuk' => $data['bujur'],
            'foto_masuk' => $path,
            'status' => $menitTelat > 0 ? 'telat' : 'tepat_waktu',
            'menit_telat' => (int) $menitTelat,
        ]);

        return back()->with('status', 'Clock-in berhasil pada '.$sekarang->format('H:i').'.');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lintang' => ['required', 'numeric', 'between:-90,90'],
            'bujur' => ['required', 'numeric', 'between:-180,180'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ]);

        $toko = $request->user()->toko;
        $karyawan = $this->karyawanAtauGagal($request);
        if ($karyawan instanceof RedirectResponse) {
            return $karyawan;
        }

        $absensi = Absensi::where('karyawan_id', $karyawan->id)->whereDate('tanggal', today())->first();

        if (! $absensi || ! $absensi->jam_masuk) {
            return back()->withErrors(['absensi' => 'Anda belum clock-in hari ini.']);
        }
        if ($absensi->jam_keluar) {
            return back()->withErrors(['absensi' => 'Anda sudah clock-out hari ini.']);
        }

        $jarak = self::jarakMeter(
            (float) $data['lintang'],
            (float) $data['bujur'],
            (float) $toko->garis_lintang,
            (float) $toko->garis_bujur,
        );

        if ($jarak > (int) ($toko->radius_absensi ?: 100)) {
            return back()->withErrors(['absensi' => 'Anda terlalu jauh dari toko untuk clock-out.']);
        }

        $sekarang = now();
        // Lembur dihitung melewati jam 17:00
        $batasPulang = $sekarang->copy()->setTime(17, 0);

        $absensi->update([
            'jam_keluar' => $sekarang,
            'lintang_keluar' => $data['lintang'],
            'bujur_keluar' => $data['bujur'],
            'foto_keluar' => $request->hasFile('foto') ? $request->file('foto')->store('absensi', 'public') : $absensi->foto_keluar,
            'menit_lembur' => (int) max($sekarang->diffInMinutes($batasPulang, false) * -1, 0),
        ]);

        return back()->with('status', 'Clock-out berhasil pada '.$sekarang->format('H:i').'.');
    }

    private function karyawanAtauGagal(Request $request): Karyawan|RedirectResponse
    {
        $karyawan = $request->user()->toko->karyawan()
            ->where('pengguna_id', $request->user()->id)
            ->first();

        if (! $karyawan) {
            return back()->withErrors(['absensi' => 'Akun Anda belum terhubung dengan data karyawan. Hubungi admin toko.']);
        }

        return $karyawan;
    }
}
