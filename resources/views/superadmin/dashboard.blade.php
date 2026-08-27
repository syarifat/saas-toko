@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Toko</p>
                <p class="text-2xl font-bold text-slate-900">{{ number_format($totalToko) }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Toko Aktif</p>
                <p class="text-2xl font-bold text-slate-900">{{ number_format($tokoAktif) }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-slate-900">{{ number_format($pembayaranMenunggu) }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omset Bulan Ini</p>
                <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- 2 Column Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Toko Terbaru -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Toko Terdaftar Terbaru</h3>
                <a href="{{ route('superadmin.toko.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($tokoTerbaru as $t)
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <a href="{{ route('superadmin.toko.show', $t) }}" class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $t->nama }}</a>
                            <p class="text-xs text-slate-500">Paket: {{ $t->paket->nama ?? '-' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $t->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">Belum ada data toko.</div>
                @endforelse
            </div>
        </div>

        <!-- Pembayaran Terbaru -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Pembayaran Terbaru</h3>
                <a href="{{ route('superadmin.verifikasi.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($pembayaranTerbaru as $p)
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $p->toko->nama ?? 'Toko' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $p->jenis === 'upgrade_paket' ? 'Upgrade: '.($p->paket->nama ?? '-') : 'Addon: '.($p->modul->nama ?? '-') }}
                                • Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $p->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">Belum ada transaksi pembayaran.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
