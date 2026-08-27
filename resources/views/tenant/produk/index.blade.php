@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Master Produk</h2>
            <p class="text-sm text-slate-500">Kelola katalog barang, harga jual, harga beli (HPP), dan stok minimum.</p>
        </div>
        <a href="{{ route('produk.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Produk
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('produk.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau SKU..."
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select name="kategori_id" class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $cat)
                        <option value="{{ $cat->id }}" {{ request('kategori_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition">Filter</button>
                @if(request()->anyFilled(['q', 'kategori_id']))
                    <a href="{{ route('produk.index') }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">SKU / Nama</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Harga Beli (HPP)</th>
                        <th class="px-6 py-3.5">Harga Jual</th>
                        <th class="px-6 py-3.5">Total Stok</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($produks as $p)
                        @php $stok = $p->totalStok(); @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono text-slate-400 block">{{ $p->sku }}</span>
                                <a href="{{ route('produk.show', $p) }}" class="font-bold text-slate-900 hover:text-indigo-600">{{ $p->nama }}</a>
                            </td>
                            <td class="px-6 py-4 text-xs">{{ $p->kategori->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $stok <= $p->stok_minimum ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $stok }} unit
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('produk.edit', $p) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada produk terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($produks->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $produks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
