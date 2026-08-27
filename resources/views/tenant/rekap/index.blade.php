@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rekap Keuangan & Laba Kotor</h2>
            <p class="text-sm text-slate-500">Ringkasan arus kas masuk, estimasi HPP, dan laba operasional.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('rekap.index') }}" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" class="text-xs border-slate-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Pilih Bulan</button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Penerimaan (Omset)</p>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-500 mt-1">POS: Rp {{ number_format($totalPos, 0, ',', '.') }} • Ringkas: Rp {{ number_format($totalPenjualanRingkas, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total HPP Barang Terjual</p>
            <p class="text-2xl font-black text-slate-700 mt-1">Rp {{ number_format($totalHpp, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Harga pokok modal barang</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Laba Kotor Penjualan</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($labaKotor, 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-600 mt-1">Margin: {{ $totalPemasukan > 0 ? round(($labaKotor / $totalPemasukan) * 100) : 0 }}%</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Beban Operasional</p>
            <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            <p class="text-[11px] text-rose-500 mt-1">Dari pencatatan pengeluaran</p>
        </div>
    </div>

    <!-- Final Calculation Card -->
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 rounded-2xl shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Estimasi Laba Bersih Toko</span>
            <p class="text-xs text-slate-300 mt-1">(Laba Kotor Penjualan - Beban Pengeluaran Operasional)</p>
        </div>
        <div class="text-right">
            <p class="text-3xl font-black {{ $labaBersihEstimasi >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                Rp {{ number_format($labaBersihEstimasi, 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
@endsection
