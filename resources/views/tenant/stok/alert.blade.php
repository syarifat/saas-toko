@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Alert Stok Menipis</h2>
            <p class="text-sm text-slate-500">Daftar produk dengan jumlah stok berada di bawah batas minimum.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">SKU / Nama Produk</th>
                    <th class="px-6 py-3.5">Kategori</th>
                    <th class="px-6 py-3.5 text-center">Batas Minimum</th>
                    <th class="px-6 py-3.5 text-center">Sisa Stok</th>
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
                        <td class="px-6 py-4 text-center text-xs font-semibold text-slate-600">{{ $p->stok_minimum }} unit</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                Sisa {{ $p->totalStok() }} unit
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(auth()->user()->toko->modulAktif('barang_masuk'))
                                <a href="{{ route('barang_masuk.index') }}" class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg">Restock Sekarang →</a>
                            @else
                                <a href="{{ route('produk.edit', $p) }}" class="text-xs text-slate-600 hover:text-slate-900 underline">Edit Threshold</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-emerald-700 text-xs font-semibold">
                            ✓ Semua stok produk dalam kondisi aman (di atas batas minimum).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
