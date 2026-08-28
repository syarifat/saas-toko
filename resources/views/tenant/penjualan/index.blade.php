@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Penjualan Ringkas (Buku Kas)</h2>
            <p class="text-sm text-slate-500">Pencatatan penjualan cepat tanpa sistem keranjang kasir POS.</p>
        </div>
        <a href="{{ route('penjualan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Catat Penjualan
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Pencatat</th>
                    <th class="px-6 py-3.5">Daftar Barang</th>
                    <th class="px-6 py-3.5">Total Penjualan</th>
                    <th class="px-6 py-3.5">Catatan</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($penjualans as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $p->tanggal_penjualan->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-xs text-slate-600">{{ $p->pengguna->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            {{ $p->items->pluck('nama_produk')->join(', ') }} ({{ $p->items->sum('jumlah') }} item)
                        </td>
                        <td class="px-6 py-4 font-bold text-emerald-700">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-xs text-slate-400 italic">{{ $p->catatan ?? '-' }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('penjualan.show', $p) }}" class="px-2.5 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg">Detail</a>
                            <form method="POST" action="{{ route('penjualan.destroy', $p) }}" class="inline" data-confirm="Yakin ingin menghapus catatan penjualan ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada catatan penjualan ringkas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($penjualans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $penjualans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
