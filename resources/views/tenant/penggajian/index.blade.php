@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Penggajian Karyawan (Payroll)</h2>
            <p class="text-sm text-slate-500">Hitung otomatis gaji karyawan berdasarkan presensi, upah harian/bulanan, dan lembur.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('penggajian.index') }}" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" class="text-xs border-slate-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Filter</button>
            </form>
        </div>
    </div>

    <!-- Generate Payroll Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <h3 class="text-xs font-bold uppercase text-slate-700">Kalkulasi Otomatis Gaji Periode Ini</h3>
        <p class="text-xs text-slate-500">Sistem akan menarik seluruh rekap absensi karyawan, menghitung denda keterlambatan, upah lembur, dan mengakumulasikan gaji bersih.</p>
        <form method="POST" action="{{ route('penggajian.generate') }}" class="flex flex-col sm:flex-row items-center gap-3">
            @csrf
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="date" name="periode_mulai" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required class="text-xs border-slate-300 rounded-xl w-full sm:w-auto">
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" name="periode_selesai" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required class="text-xs border-slate-300 rounded-xl w-full sm:w-auto">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                ⚡ Hitung / Generate Draf Payroll
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Karyawan</th>
                    <th class="px-6 py-3.5">Skema</th>
                    <th class="px-6 py-3.5 text-right">Upah Dasar</th>
                    <th class="px-6 py-3.5 text-right">Tunjangan/Lembur</th>
                    <th class="px-6 py-3.5 text-right">Potongan</th>
                    <th class="px-6 py-3.5 text-right">Gaji Bersih</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($penggajians as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $p->karyawan->pengguna->nama ?? '-' }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $p->karyawan->kode_karyawan ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium uppercase text-slate-700">{{ $p->skema_gaji_snapshot }}</td>
                        <td class="px-6 py-4 text-right text-xs">Rp {{ number_format($p->jumlah_dasar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-xs text-indigo-600 font-semibold">+ Rp {{ number_format($p->total_tunjangan, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-xs text-rose-600 font-semibold">- Rp {{ number_format($p->total_potongan, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-black text-slate-900 text-sm">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $p->status === 'dibayar' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('penggajian.show', $p) }}" class="px-2.5 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg">Rincian</a>
                            <a href="{{ route('penggajian.slip', $p) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Slip</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada draf penggajian untuk periode ini. Klik "Generate Draf Payroll" di atas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
