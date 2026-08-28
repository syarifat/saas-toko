@extends('layouts.tenant')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manajemen Gudang & Etalase</h2>
            <p class="text-sm text-slate-500">Kelola lokasi penyimpanan stok fisik toko.</p>
        </div>
    </div>

    <!-- Form Tambah Gudang -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-xs font-bold uppercase text-slate-700 mb-3">Tambah Lokasi Gudang Baru</h3>
        <form method="POST" action="{{ route('gudang.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf
            <div>
                <input type="text" name="nama" required placeholder="Nama gudang (contoh: Gudang Belakang)..."
                       class="w-full text-xs border-slate-300 rounded-xl">
            </div>
            <div>
                <select name="jenis" required class="w-full text-xs border-slate-300 rounded-xl">
                    <option value="gudang">Gudang Penyimpanan</option>
                    <option value="etalase">Etalase Toko</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition">
                    + Tambah Gudang
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nama Gudang</th>
                    <th class="px-6 py-3.5">Jenis</th>
                    <th class="px-6 py-3.5">Variasi Produk</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($gudangs as $g)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $g->nama }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $g->jenis === 'etalase' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($g->jenis) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">{{ $g->stok_gudang_count }} jenis produk</td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('gudang.destroy', $g) }}" class="inline" data-confirm="Yakin ingin menghapus gudang '{{ $g->nama }}'?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada lokasi gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
