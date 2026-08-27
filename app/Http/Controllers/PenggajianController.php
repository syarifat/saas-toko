<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KomponenGaji;
use App\Models\Penggajian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenggajianController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $parts = explode('-', $bulan);
        $tahun = (int) ($parts[0] ?? now()->year);
        $bln = (int) ($parts[1] ?? now()->month);

        $penggajians = Penggajian::with(['karyawan.pengguna', 'komponen'])
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bln)
            ->latest()
            ->paginate(15);

        $totalGaji = (float) Penggajian::whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bln)
            ->sum('gaji_bersih');

        return view('tenant.penggajian.index', compact('penggajians', 'bulan', 'totalGaji'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
        ]);

        $karyawans = Karyawan::where('toko_id', $user->toko_id)
            ->where('aktif', true)
            ->get();

        if ($karyawans->isEmpty()) {
            return back()->with('error', 'Tidak ada karyawan aktif untuk digenerate penggajiannya.');
        }

        DB::transaction(function () use ($validated, $karyawans, $user) {
            foreach ($karyawans as $karyawan) {
                // Ambil rekap absensi karyawan pada periode tersebut
                $absensis = Absensi::where('toko_id', $user->toko_id)
                    ->where('karyawan_id', $karyawan->id)
                    ->whereBetween('tanggal', [$validated['periode_mulai'], $validated['periode_selesai']])
                    ->get();

                $jumlahHadir = $absensis->whereNotNull('jam_masuk')->count();
                $totalMenitLembur = $absensis->sum('menit_lembur');
                $totalMenitTelat = $absensis->sum('menit_telat');

                $komponens = [];

                if ($karyawan->skema_gaji === 'harian') {
                    $jumlahDasar = $jumlahHadir * (float) $karyawan->tarif_harian;
                    $komponens[] = [
                        'jenis' => 'tunjangan',
                        'nama' => "Kehadiran ({$jumlahHadir} hari x Rp ".number_format($karyawan->tarif_harian, 0, ',', '.').')',
                        'nominal' => $jumlahDasar,
                    ];
                } else {
                    $jumlahDasar = (float) $karyawan->gaji_pokok;
                    $komponens[] = [
                        'jenis' => 'tunjangan',
                        'nama' => 'Gaji Pokok Bulanan',
                        'nominal' => $jumlahDasar,
                    ];
                }

                // Hitung lembur (Rp 15.000 per jam lembur)
                $totalTunjangan = 0;
                if ($totalMenitLembur > 0) {
                    $jamLembur = round($totalMenitLembur / 60, 1);
                    $nominalLembur = round(($totalMenitLembur / 60) * 15000);
                    $totalTunjangan += $nominalLembur;
                    $komponens[] = [
                        'jenis' => 'tunjangan',
                        'nama' => "Upah Lembur ({$jamLembur} jam)",
                        'nominal' => $nominalLembur,
                    ];
                }

                // Hitung potongan telat (Rp 500 per menit telat)
                $totalPotongan = 0;
                if ($totalMenitTelat > 0) {
                    $nominalPotonganTelat = $totalMenitTelat * 500;
                    $totalPotongan += $nominalPotonganTelat;
                    $komponens[] = [
                        'jenis' => 'potongan',
                        'nama' => "Denda Keterlambatan ({$totalMenitTelat} menit)",
                        'nominal' => $nominalPotonganTelat,
                    ];
                }

                $gajiBersih = max(0, $jumlahDasar + $totalTunjangan - $totalPotongan);

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'toko_id' => $user->toko_id,
                        'karyawan_id' => $karyawan->id,
                        'periode_mulai' => $validated['periode_mulai'],
                        'periode_selesai' => $validated['periode_selesai'],
                    ],
                    [
                        'skema_gaji_snapshot' => $karyawan->skema_gaji,
                        'jumlah_dasar' => $jumlahDasar,
                        'total_tunjangan' => $totalTunjangan,
                        'total_potongan' => $totalPotongan,
                        'gaji_bersih' => $gajiBersih,
                        'status' => 'draf',
                    ]
                );

                $penggajian->komponen()->delete();
                foreach ($komponens as $komp) {
                    KomponenGaji::create([
                        'toko_id' => $user->toko_id,
                        'penggajian_id' => $penggajian->id,
                        'jenis' => $komp['jenis'],
                        'nama' => $komp['nama'],
                        'nominal' => $komp['nominal'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Draf penggajian untuk seluruh karyawan berhasil dibuat/diperbarui.');
    }

    public function show(Penggajian $penggajian): View
    {
        $penggajian->load(['karyawan.pengguna', 'komponen', 'toko']);

        return view('tenant.penggajian.show', compact('penggajian'));
    }

    public function bayar(Penggajian $penggajian): RedirectResponse
    {
        if ($penggajian->status === 'dibayar') {
            return back()->with('error', 'Gaji ini sudah berstatus lunas.');
        }

        $penggajian->update([
            'status' => 'dibayar',
            'dibayar_pada' => now(),
        ]);

        return back()->with('success', "Gaji untuk {$penggajian->karyawan->pengguna->nama} berhasil dibayarkan.");
    }

    public function slip(Penggajian $penggajian): View
    {
        $penggajian->load(['karyawan.pengguna', 'komponen', 'toko']);

        return view('tenant.penggajian.slip', compact('penggajian'));
    }
}
