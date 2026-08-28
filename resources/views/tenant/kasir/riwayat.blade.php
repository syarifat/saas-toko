@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Transaksi Kasir POS</h2>
            <p class="text-sm text-slate-500">Daftar struk transaksi penjualan kasir.</p>
        </div>
        <a href="{{ route('kasir.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
            + Buka Kasir POS
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('kasir.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full text-xs border-slate-300 rounded-lg">
            </div>
            <div>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full text-xs border-slate-300 rounded-lg">
            </div>
            <div>
                <select name="metode" class="w-full text-xs border-slate-300 rounded-lg">
                    <option value="">Semua Metode</option>
                    <option value="tunai" {{ request('metode') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ request('metode') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ request('metode') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Filter</button>
                @if(request()->anyFilled(['tanggal_mulai', 'tanggal_selesai', 'metode']))
                    <a href="{{ route('kasir.riwayat') }}" class="px-3 py-2 bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">ID Transaksi</th>
                    <th class="px-6 py-3.5">Waktu</th>
                    <th class="px-6 py-3.5">Kasir / Gudang</th>
                    <th class="px-6 py-3.5">Item</th>
                    <th class="px-6 py-3.5">Total Bayar</th>
                    <th class="px-6 py-3.5">Metode</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transaksis as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">#{{ $t->id }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $t->created_at->format('d M Y, H:i') }} WIB</td>
                        <td class="px-6 py-4 text-xs">
                            <span class="font-semibold text-slate-800 block">{{ $t->pengguna->nama ?? '-' }}</span>
                            <span class="text-[11px] text-slate-400">{{ $t->gudang->nama ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">{{ $t->items->count() }} jenis barang</td>
                        <td class="px-6 py-4 font-bold text-slate-900">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] bg-slate-100 text-slate-700">{{ $t->metode_pembayaran }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('kasir.show', $t) }}" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">Lihat Struk</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada data transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($transaksis->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
