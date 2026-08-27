@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Stok Opname (Penyesuaian Fisik)</h2>
            <p class="text-sm text-slate-500">Sesuaikan jumlah fisik produk di gudang secara berkala.</p>
        </div>
    </div>

    <!-- Form Opname -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-xs font-bold uppercase text-slate-700">Input Penyesuaian Fisik</h3>
        <form method="POST" action="{{ route('stok.opname.simpan') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                <select name="produk_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} (Sistem: {{ $p->totalStok() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Gudang / Lokasi <span class="text-rose-500">*</span></label>
                <select name="gudang_id" required class="w-full text-xs border-slate-300 rounded-xl">
                    @foreach($gudangs as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jumlah Fisik Sebenarnya <span class="text-rose-500">*</span></label>
                <input type="number" name="jumlah_fisik" min="0" required placeholder="Hasil hitung..." class="w-full text-xs border-slate-300 rounded-xl">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Alasan / Catatan</label>
                <div class="flex gap-2">
                    <input type="text" name="catatan" placeholder="Hilang / Rusak..." class="w-full text-xs border-slate-300 rounded-xl">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shrink-0">
                        Sesuaikan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Riwayat Opname -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Penyesuaian Stok Opname</h3>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Produk</th>
                    <th class="px-6 py-3.5">Gudang</th>
                    <th class="px-6 py-3.5 text-center">Sebelum</th>
                    <th class="px-6 py-3.5 text-center">Sesudah</th>
                    <th class="px-6 py-3.5 text-center">Selisih</th>
                    <th class="px-6 py-3.5">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayatOpname as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $r->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $r->produk->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs">{{ $r->gudang->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center text-xs font-semibold">{{ $r->stok_sebelum }}</td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-900">{{ $r->stok_sesudah }}</td>
                        <td class="px-6 py-4 text-center font-bold text-xs {{ $r->jumlah >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $r->jumlah > 0 ? '+'.$r->jumlah : $r->jumlah }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 italic">{{ $r->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada riwayat penyesuaian stok opname.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
