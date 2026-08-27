@extends('layouts.tenant')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Daftar Pemasok (Supplier)</h2>
            <p class="text-sm text-slate-500">Kelola informasi kontak distributor dan pemasok barang.</p>
        </div>
    </div>

    <!-- Form Tambah Pemasok -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-xs font-bold uppercase text-slate-700 mb-3">Tambah Pemasok Baru</h3>
        <form method="POST" action="{{ route('pemasok.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf
            <div>
                <input type="text" name="nama" required placeholder="Nama supplier/distributor..."
                       class="w-full text-xs border-slate-300 rounded-xl focus:ring-indigo-500">
            </div>
            <div>
                <input type="text" name="telepon" placeholder="Nomor Telepon / WA..."
                       class="w-full text-xs border-slate-300 rounded-xl focus:ring-indigo-500">
            </div>
            <div class="flex gap-2">
                <input type="text" name="alamat" placeholder="Alamat / Kota..."
                       class="w-full text-xs border-slate-300 rounded-xl focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition shrink-0">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nama Pemasok</th>
                    <th class="px-6 py-3.5">Kontak</th>
                    <th class="px-6 py-3.5">Alamat</th>
                    <th class="px-6 py-3.5">Produk</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pemasoks as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $p->nama }}</td>
                        <td class="px-6 py-4 text-xs">{{ $p->telepon ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $p->alamat ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs">{{ $p->produk_count }} item</td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('pemasok.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus pemasok ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada pemasok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
