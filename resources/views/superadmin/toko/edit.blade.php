@extends('layouts.superadmin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Edit Toko: {{ $toko->nama }}</h2>
            <p class="text-sm text-slate-500">Perbarui pengaturan konfigurasi toko.</p>
        </div>
        <a href="{{ route('superadmin.toko.show', $toko) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Detail</a>
    </div>

    <form method="POST" action="{{ route('superadmin.toko.update', $toko) }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko <span class="text-rose-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama', $toko->nama) }}" required
                   class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Toko <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="aktif" {{ old('status', $toko->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status', $toko->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Aktif Langganan</label>
                <input type="date" name="langganan_berakhir_pada" value="{{ old('langganan_berakhir_pada', $toko->langganan_berakhir_pada?->format('Y-m-d')) }}"
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Garis Lintang (Latitude)</label>
                <input type="text" name="garis_lintang" value="{{ old('garis_lintang', $toko->garis_lintang) }}"
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Garis Bujur (Longitude)</label>
                <input type="text" name="garis_bujur" value="{{ old('garis_bujur', $toko->garis_bujur) }}"
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Radius Absensi (meter)</label>
            <input type="number" name="radius_absensi" value="{{ old('radius_absensi', $toko->radius_absensi) }}" min="10" max="5000"
                   class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="confirmAction('Yakin ingin menghapus toko ini beserta seluruh datanya?', () => document.getElementById('delete-toko-form').submit())"
                    class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">
                Hapus Toko
            </button>
            <div class="flex gap-2">
                <a href="{{ route('superadmin.toko.show', $toko) }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">Batal</a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-toko-form" method="POST" action="{{ route('superadmin.toko.destroy', $toko) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
