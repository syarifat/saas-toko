@extends('layouts.tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Detail Penjualan Ringkas #{{ $penjualan->id }}</h2>
            <p class="text-xs text-slate-500">Tanggal: {{ $penjualan->tanggal_penjualan->format('d M Y') }} • Pencatat: {{ $penjualan->pengguna->nama ?? '-' }}</p>
        </div>
        <a href="{{ route('penjualan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-2.5">Produk</th>
                    <th class="py-2.5 text-center">Jumlah</th>
                    <th class="py-2.5 text-right">Harga Satuan</th>
                    <th class="py-2.5 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($penjualan->items as $item)
                    <tr>
                        <td class="py-3 font-semibold text-slate-900">{{ $item->nama_produk }}</td>
                        <td class="py-3 text-center">{{ $item->jumlah }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="py-3 text-right font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 font-bold text-slate-900">
                    <td colspan="3" class="py-3 text-right">TOTAL:</td>
                    <td class="py-3 text-right text-base text-emerald-700">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($penjualan->catatan)
            <div class="pt-3 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Catatan:</span> {{ $penjualan->catatan }}
            </div>
        @endif
    </div>
</div>
@endsection
