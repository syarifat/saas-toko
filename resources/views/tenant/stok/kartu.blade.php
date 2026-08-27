@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Kartu Stok Produk</h2>
            <p class="text-sm text-slate-500">Pilih produk untuk melihat histori mutasi stok masuk, keluar, dan opname.</p>
        </div>
        <form method="GET" action="{{ route('stok.kartu') }}" class="w-72">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau SKU..."
                   class="w-full text-xs border-slate-300 rounded-xl">
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">SKU / Nama Produk</th>
                    <th class="px-6 py-3.5">Kategori</th>
                    <th class="px-6 py-3.5">Lokasi Gudang</th>
                    <th class="px-6 py-3.5 text-center">Total Stok</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($produks as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono text-slate-400 block">{{ $p->sku }}</span>
                            <span class="font-bold text-slate-900">{{ $p->nama }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $p->kategori->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs">
                            {{ $p->stokGudang->map(fn($sg) => "{$sg->gudang->nama}: {$sg->jumlah}")->join(', ') }}
                        </td>
                        <td class="px-6 py-4 text-center font-extrabold text-slate-900">{{ $p->totalStok() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('stok.kartu.detail', $p) }}" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg">
                                Buka Mutasi →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($produks->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $produks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
