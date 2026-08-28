@extends('layouts.tenant')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Kategori Produk</h2>
            <p class="text-sm text-slate-500">Kelola pengelompokan produk toko.</p>
        </div>
    </div>

    <!-- Form Tambah Kategori -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('kategori.store') }}" class="flex items-center gap-3">
            @csrf
            <div class="flex-1">
                <input type="text" name="nama" required placeholder="Nama kategori baru (contoh: Minuman Dingin, Snack, dll)..."
                       class="w-full text-sm border-slate-300 rounded-xl focus:ring-indigo-500">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition shrink-0">
                + Tambah Kategori
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nama Kategori</th>
                    <th class="px-6 py-3.5">Jumlah Produk</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kategoris as $cat)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $cat->nama }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $cat->produk_count }} produk</td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('kategori.destroy', $cat) }}" class="inline" data-confirm="Yakin ingin menghapus kategori '{{ $cat->nama }}'?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
