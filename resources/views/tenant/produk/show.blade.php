@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ $produk->nama }}</h2>
            <p class="text-xs text-slate-400 font-mono">SKU: {{ $produk->sku }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('produk.edit', $produk) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl">Edit Produk</a>
            <a href="{{ route('produk.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
        </div>
    </div>

    <!-- Details Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs text-slate-400 font-semibold uppercase">Informasi Harga</span>
            <p class="text-xs text-slate-600">Harga Beli (HPP): <span class="font-bold text-slate-900">Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</span></p>
            <p class="text-sm text-slate-600">Harga Jual: <span class="font-extrabold text-indigo-700">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span></p>
            <p class="text-xs text-emerald-700 font-semibold">Margin Laba: Rp {{ number_format($produk->harga_jual - $produk->harga_beli, 0, ',', '.') }} ({{ $produk->harga_beli > 0 ? round((($produk->harga_jual - $produk->harga_beli)/$produk->harga_beli)*100) : 0 }}%)</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs text-slate-400 font-semibold uppercase">Stok & Gudang</span>
            <p class="text-2xl font-black text-slate-900">{{ $produk->totalStok() }} <span class="text-xs font-normal text-slate-500">unit</span></p>
            <p class="text-xs text-slate-500">Batas Stok Minimum: {{ $produk->stok_minimum }} unit</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs text-slate-400 font-semibold uppercase">Kategori & Pemasok</span>
            <p class="text-xs text-slate-600">Kategori: <span class="font-bold text-slate-800">{{ $produk->kategori->nama ?? '-' }}</span></p>
            <p class="text-xs text-slate-600">Pemasok: <span class="font-bold text-slate-800">{{ $produk->pemasok->nama ?? '-' }}</span></p>
        </div>
    </div>

    <!-- Stok per Gudang Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Sebaran Stok di Seluruh Gudang / Etalase</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($produk->stokGudang as $sg)
                <div class="px-6 py-3.5 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-900">{{ $sg->gudang->nama ?? 'Gudang' }} ({{ ucfirst($sg->gudang->jenis ?? 'gudang') }})</span>
                    <span class="font-extrabold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $sg->jumlah }} unit</span>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Belum ada alokasi stok di gudang.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
