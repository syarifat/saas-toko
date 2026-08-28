@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Transfer Stok Antar Gudang</h2>
            <p class="text-sm text-slate-500">Pindahkan stok produk antar etalase dan gudang penyimpanan.</p>
        </div>
    </div>

    <!-- Form Transfer -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-xs font-bold uppercase text-slate-700">Form Mutasi Transfer Antar Gudang</h3>
        <form method="POST" action="{{ route('transfer_gudang.store') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Produk <span class="text-rose-500">*</span></label>
                <select name="produk_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} (Sisa: {{ $p->totalStok() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Gudang Asal <span class="text-rose-500">*</span></label>
                <select name="gudang_asal_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    @foreach($gudangs as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Ke Gudang Tujuan <span class="text-rose-500">*</span></label>
                <select name="gudang_tujuan_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    @foreach($gudangs as $g)
                        <option value="{{ $g->id }}" {{ $loop->last ? 'selected' : '' }}>{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jumlah Unit <span class="text-rose-500">*</span></label>
                <input type="number" name="jumlah" min="1" required placeholder="Qty" class="w-full text-xs border-slate-300 rounded-xl">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Aksi</label>
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm">
                    Pindahkan Stok →
                </button>
            </div>
        </form>
    </div>

    <!-- Riwayat Transfer -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Mutasi Antar Gudang</h3>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Waktu</th>
                    <th class="px-6 py-3.5">Produk</th>
                    <th class="px-6 py-3.5">Dari Gudang Asal</th>
                    <th class="px-6 py-3.5">Ke Gudang Tujuan</th>
                    <th class="px-6 py-3.5 text-center">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $r->created_at->format('d/m/Y H:i') }} WIB</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $r->produk->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs text-rose-700 font-semibold">{{ $r->gudang->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs text-emerald-700 font-semibold">{{ $r->gudangTujuan->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900 text-xs">{{ $r->jumlah }} unit</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada riwayat transfer gudang.</td>
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
