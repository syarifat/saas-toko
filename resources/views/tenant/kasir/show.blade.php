@extends('layouts.tenant')

@section('content')
<div class="max-w-md mx-auto space-y-4">
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('kasir.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Transaksi Baru</a>
        <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            Cetak Struk
        </button>
    </div>

    <!-- Thermal Receipt Preview -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-slate-800 text-xs font-mono space-y-4">
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <h2 class="font-bold text-base text-slate-900">{{ $transaksi->toko->nama ?? 'SaaS Toko' }}</h2>
            <p class="text-[11px] text-slate-500">Struk Pembayaran Kasir</p>
            <p class="text-[10px] text-slate-400">ID: #{{ $transaksi->id }} • {{ $transaksi->created_at->format('d/m/Y H:i') }}</p>
            <p class="text-[10px] text-slate-400">Kasir: {{ $transaksi->pengguna->nama ?? '-' }}</p>
        </div>

        <div class="space-y-2 divide-y divide-dashed divide-slate-200">
            @foreach($transaksi->items as $item)
                <div class="pt-2 first:pt-0">
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>{{ $item->nama_produk }}</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-500">
                        <span>{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-3 border-t border-dashed border-slate-300 space-y-1.5">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaksi->diskon > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Diskon:</span>
                    <span>- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold text-sm text-slate-900 pt-1 border-t border-slate-200">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between pt-1">
                <span>Metode:</span>
                <span class="uppercase font-bold">{{ $transaksi->metode_pembayaran }}</span>
            </div>
            <div class="flex justify-between">
                <span>Bayar:</span>
                <span>Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-emerald-700">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="text-center pt-3 border-t border-dashed border-slate-300 text-[10px] text-slate-400">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .max-w-md, .max-w-md * { visibility: visible; }
    .no-print { display: none !important; }
    .max-w-md { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
@endsection
