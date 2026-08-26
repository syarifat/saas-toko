<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PenjualanSederhana;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function index(): View
    {
        $periode = request()->string('periode')->toString() ?: 'harian';
        [$mulai, $selesai, $format] = match (true) {
            $periode === 'mingguan' => [now()->startOfWeek(), now(), 'Y-W'],
            $periode === 'bulanan' => [now()->startOfMonth()->subMonths(5), now(), 'Y-m'],
            default => [now()->subDays(29)->startOfDay(), now(), 'Y-m-d'],
        };

        $penjualan = PenjualanSederhana::whereBetween('tanggal_penjualan', [$mulai->toDateString(), $selesai->toDateString()])
            ->selectRaw('DATE_FORMAT(tanggal_penjualan, ?) as grup, SUM(total) as total_masuk', [$format])
            ->groupBy('grup')
            ->orderBy('grup')
            ->pluck('total_masuk', 'grup');

        $pengeluaran = Pengeluaran::whereBetween('tanggal_pengeluaran', [$mulai->toDateString(), $selesai->toDateString()])
            ->selectRaw('DATE_FORMAT(tanggal_pengeluaran, ?) as grup, SUM(nominal) as total_keluar', [$format])
            ->groupBy('grup')
            ->orderBy('grup')
            ->pluck('total_keluar', 'grup');

        $grup = collect(range(0, 29))
            ->map(fn ($i) => CarbonImmutable::parse($mulai)->addDays($i))
            ->when($periode === 'mingguan' || $periode === 'bulanan', fn ($c) => $c->unique(fn ($d) => $d->format($format)))
            ->mapWithKeys(function (CarbonImmutable $tanggal) use ($penjualan, $pengeluaran, $format) {
                $kunci = $tanggal->format($format);

                return [$kunci => [
                    'label' => $kunci,
                    'masuk' => (float) ($penjualan[$kunci] ?? 0),
                    'keluar' => (float) ($pengeluaran[$kunci] ?? 0),
                    'laba' => (float) ($penjualan[$kunci] ?? 0) - (float) ($pengeluaran[$kunci] ?? 0),
                ]];
            });

        return view('rekap.index', [
            'periode' => $periode,
            'rekap' => $grup->values(),
            'totalMasuk' => (float) $penjualan->sum(),
            'totalKeluar' => (float) $pengeluaran->sum(),
            'labaKotor' => (float) $penjualan->sum() - (float) $pengeluaran->sum(),
        ]);
    }
}
