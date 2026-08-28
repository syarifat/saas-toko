@extends('layouts.tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Edit Produk: {{ $produk->nama }}</h2>
            <p class="text-sm text-slate-500">Perbarui data produk dan harga jual.</p>
        </div>
        <a href="{{ route('produk.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('produk.update', $produk) }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode SKU <span class="text-rose-500">*</span></label>
                <input type="text" name="sku" value="{{ old('sku', $produk->sku) }}" required
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $produk->nama) }}" required
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                <select name="kategori_id" class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
                    <option value="">-- Tanpa Kategori --</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ old('kategori_id', $produk->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pemasok</label>
                <select name="pemasok_id" class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
                    <option value="">-- Tanpa Pemasok --</option>
                    @foreach($pemasoks as $p)
                        <option value="{{ $p->id }}" {{ old('pemasok_id', $produk->pemasok_id) == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli / HPP (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="harga_beli" value="{{ old('harga_beli', (int)$produk->harga_beli) }}" min="0" step="500" required
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="harga_jual" value="{{ old('harga_jual', (int)$produk->harga_jual) }}" min="0" step="500" required
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Minimum Alert</label>
                <input type="number" name="stok_minimum" value="{{ old('stok_minimum', $produk->stok_minimum) }}" min="0"
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="confirmAction('Yakin ingin menghapus produk ini beserta seluruh datanya?', () => document.getElementById('delete-produk-form').submit())"
                    class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                Hapus Produk
            </button>
            <div class="flex gap-2">
                <a href="{{ route('produk.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-produk-form" method="POST" action="{{ route('produk.destroy', $produk) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
