@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Laporan HPP & Laba POS</h2>
            <p class="text-sm text-slate-500">Analisis margin keuntungan kotor per produk yang terjual.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('laporan.hpp') }}" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" class="text-xs border-slate-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Pilih Bulan</button>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Omset Kasir POS</p>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($grandOmset, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Pokok (HPP)</p>
            <p class="text-2xl font-black text-slate-700 mt-1">Rp {{ number_format($grandHpp, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Laba Kotor</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($grandLaba, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nama Produk</th>
                    <th class="px-6 py-3.5 text-center">Qty Terjual</th>
                    <th class="px-6 py-3.5 text-right">Total Omset</th>
                    <th class="px-6 py-3.5 text-right">Total HPP</th>
                    <th class="px-6 py-3.5 text-right">Laba Kotor</th>
                    <th class="px-6 py-3.5 text-right">Margin %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($laporanProduk as $row)
                    @php
                        $margin = $row->total_omset > 0 ? round(($row->total_laba_kotor / $row->total_omset) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $row->nama_produk }}</td>
                        <td class="px-6 py-4 text-center">{{ $row->total_terjual }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($row->total_omset, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-slate-500">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-700">Rp {{ number_format($row->total_laba_kotor, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800">{{ $margin }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada data penjualan POS pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
