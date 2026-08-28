@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Selamat Datang, {{ auth()->user()->nama }}! 👋</h2>
            <p class="text-xs text-slate-500 mt-1">
                Toko: <span class="font-bold text-slate-700">{{ $toko->nama }}</span> • 
                Paket: <span class="font-semibold text-indigo-600">{{ $toko->paket->nama ?? '-' }}</span> • 
                Status: <span class="text-emerald-700 font-semibold">{{ ucfirst($toko->status) }}</span>
            </p>
        </div>
        @if($toko->modulAktif('kasir_pos'))
            <a href="{{ route('kasir.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                Buka Kasir POS
            </a>
        @endif
    </div>

    <!-- Stat Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Omset Hari Ini</p>
                <p class="text-xl font-black text-slate-900">Rp {{ number_format($totalOmsetHariIni, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($toko->modulAktif('kasir_pos'))
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Transaksi Hari Ini</p>
                    <p class="text-xl font-black text-slate-900">{{ number_format($totalTransaksiHariIni) }} Transaksi</p>
                </div>
            </div>
        @endif

        @if($toko->modulAktif('pengeluaran'))
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Beban Pengeluaran Bln Ini</p>
                    <p class="text-xl font-black text-rose-600">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</p>
                </div>
            </div>
        @endif

        @if($toko->modulAktif('stock_alert') || $toko->modulAktif('stok_gudang'))
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Alert Stok Menipis</p>
                    <p class="text-xl font-black text-amber-600">{{ $stokMenipisCount }} Produk</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Active Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($toko->modulAktif('kasir_pos'))
            <!-- Transaksi Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800">Transaksi Kasir Terbaru</h3>
                    <a href="{{ route('kasir.riwayat') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat Riwayat →</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($transaksiTerbaru as $trx)
                        <div class="px-6 py-3 flex items-center justify-between text-xs hover:bg-slate-50">
                            <div>
                                <p class="font-bold text-slate-900">#{{ $trx->id }} • Rp {{ number_format($trx->total, 0, ',', '.') }}</p>
                                <p class="text-[11px] text-slate-500">{{ $trx->created_at->format('d M Y H:i') }} WIB • Kasir: {{ $trx->pengguna->nama ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] bg-slate-100 text-slate-700">{{ $trx->metode_pembayaran }}</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">Belum ada transaksi kasir hari ini.</div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($toko->modulAktif('stock_alert') || $toko->modulAktif('stok_gudang'))
            <!-- Produk Stok Menipis -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800">Produk Stok Menipis (Di Bawah Minimum)</h3>
                    @if($toko->modulAktif('stock_alert'))
                        <a href="{{ route('stok.alert') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-800">Lihat Semua →</a>
                    @endif
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($produkMenipis->take(5) as $pm)
                        <div class="px-6 py-3 flex items-center justify-between text-xs hover:bg-slate-50">
                            <div>
                                <p class="font-bold text-slate-900">{{ $pm->nama }} <span class="font-mono text-slate-400">({{ $pm->sku }})</span></p>
                                <p class="text-[11px] text-slate-500">Stok Minimum: {{ $pm->stok_minimum }} unit</p>
                            </div>
                            <span class="px-2 py-0.5 rounded font-bold text-xs bg-rose-100 text-rose-800">
                                Sisa {{ $pm->totalStok() }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">Semua stok produk dalam batas aman ✓</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
