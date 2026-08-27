@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mutasi Kartu Stok: {{ $produk->nama }}</h2>
            <p class="text-xs text-slate-400 font-mono">SKU: {{ $produk->sku }} • Sisa Total: {{ $produk->totalStok() }} unit</p>
        </div>
        <a href="{{ route('stok.kartu') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Daftar</a>
    </div>

    <!-- Mutasi Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Seluruh Mutasi Produk</h3>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Waktu</th>
                    <th class="px-6 py-3.5">Jenis Mutasi</th>
                    <th class="px-6 py-3.5">Gudang</th>
                    <th class="px-6 py-3.5 text-center">Sebelum</th>
                    <th class="px-6 py-3.5 text-center">Perubahan</th>
                    <th class="px-6 py-3.5 text-center">Sesudah</th>
                    <th class="px-6 py-3.5">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pergerakans as $m)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] 
                                {{ $m->jenis === 'masuk' ? 'bg-emerald-100 text-emerald-800' : ($m->jenis === 'keluar' ? 'bg-rose-100 text-rose-800' : ($m->jenis === 'transfer' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800')) }}">
                                {{ $m->jenis }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $m->gudang->nama ?? '-' }}
                            @if($m->gudangTujuan)
                                → {{ $m->gudangTujuan->nama }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-xs">{{ $m->stok_sebelum }}</td>
                        <td class="px-6 py-4 text-center font-extrabold text-xs {{ $m->jumlah >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $m->jumlah > 0 ? '+'.$m->jumlah : $m->jumlah }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-900">{{ $m->stok_sesudah }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $m->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada catatan mutasi untuk produk ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($pergerakans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $pergerakans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
