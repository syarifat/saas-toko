@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Barang Masuk (Restock Supplier)</h2>
            <p class="text-sm text-slate-500">Pencatatan pasokan barang yang masuk dari pemasok ke gudang toko.</p>
        </div>
    </div>

    <!-- Form Barang Masuk -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-xs font-bold uppercase text-slate-700">Input Pasokan Barang Masuk</h3>
        <form method="POST" action="{{ route('barang_masuk.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                <select name="produk_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} (Sisa: {{ $p->totalStok() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Gudang Tujuan <span class="text-rose-500">*</span></label>
                <select name="gudang_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    @foreach($gudangs as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }} ({{ ucfirst($g->jenis) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jumlah Masuk (Unit) <span class="text-rose-500">*</span></label>
                <input type="number" name="jumlah" min="1" required placeholder="Jumlah unit..." class="w-full text-xs border-slate-300 rounded-xl">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Catatan / No. PO / Supplier</label>
                <div class="flex gap-2">
                    <input type="text" name="catatan" placeholder="Keterangan restock..." class="w-full text-xs border-slate-300 rounded-xl">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shrink-0">
                        + Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Riwayat Restock -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Barang Masuk</h3>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Waktu</th>
                    <th class="px-6 py-3.5">Produk</th>
                    <th class="px-6 py-3.5">Gudang</th>
                    <th class="px-6 py-3.5 text-center">Jumlah Masuk</th>
                    <th class="px-6 py-3.5">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $r->created_at->format('d/m/Y H:i') }} WIB</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $r->produk->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs">{{ $r->gudang->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center font-black text-emerald-700 text-xs">+{{ $r->jumlah }} unit</td>
                        <td class="px-6 py-4 text-xs text-slate-500 italic">{{ $r->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada riwayat pasokan barang masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($riwayat->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
