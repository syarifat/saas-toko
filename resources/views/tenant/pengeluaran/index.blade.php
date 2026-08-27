@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Catatan Pengeluaran (Beban Operasional)</h2>
            <p class="text-sm text-slate-500">Pencatatan biaya listrik, sewa, gaji, belanja perlengkapan toko, dll.</p>
        </div>
        <a href="{{ route('pengeluaran.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Catat Pengeluaran
        </a>
    </div>

    <!-- Filter & Total Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase">Total Beban Periode Ini</p>
                <p class="text-2xl font-black text-rose-600">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="sm:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center">
            <form method="GET" action="{{ route('pengeluaran.index') }}" class="flex items-center gap-3 w-full">
                <input type="month" name="bulan" value="{{ request('bulan', now()->format('Y-m')) }}" class="text-xs border-slate-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Filter Bulan</button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Keterangan</th>
                    <th class="px-6 py-3.5">Pencatat</th>
                    <th class="px-6 py-3.5">Nominal</th>
                    <th class="px-6 py-3.5">Bukti Struk</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengeluarans as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $p->tanggal_pengeluaran->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-800">{{ $p->keterangan }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $p->pengguna->nama ?? '-' }}</td>
                        <td class="px-6 py-4 font-bold text-rose-600">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-xs">
                            @if($p->bukti_struk)
                                <a href="{{ asset('storage/'.$p->bukti_struk) }}" target="_blank" class="text-indigo-600 underline">Lihat Foto</a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('pengeluaran.edit', $p) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada catatan pengeluaran pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($pengeluarans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $pengeluarans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
