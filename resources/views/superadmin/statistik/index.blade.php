@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Statistik Platform & Adopsi Fitur</h2>
        <p class="text-sm text-slate-500">Pantau pertumbuhan tenant, adopsi modul, dan pendapatan sistem.</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Toko Tenant</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalToko) }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Akun Pengguna</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalPengguna) }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pendapatan Terverifikasi</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribusi Paket -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-2">Distribusi Tenant per Paket</h3>
            <div class="space-y-3">
                @foreach($paketDistribution as $p)
                    @php
                        $pct = $totalToko > 0 ? round(($p->toko_count / $totalToko) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-800">{{ $p->nama }}</span>
                            <span class="text-slate-500">{{ $p->toko_count }} toko ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Popularitas Modul -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-2">Tingkat Aktivasi Modul (Top Adoption)</h3>
            <div class="space-y-2.5 max-h-72 overflow-y-auto pr-2">
                @foreach($modulPopularity as $m)
                    @php
                        $pct = $totalToko > 0 ? round(($m->toko_count / $totalToko) * 100) : 0;
                    @endphp
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                        <div>
                            <span class="font-semibold text-slate-800">{{ $m->nama }}</span>
                            <span class="text-slate-400 font-mono text-[10px] ml-1">({{ $m->kode }})</span>
                        </div>
                        <span class="font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                            {{ $m->toko_count }} toko
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
