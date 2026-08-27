@extends('layouts.tenant')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Catat Pengeluaran Baru</h2>
            <p class="text-sm text-slate-500">Masukkan biaya operasional toko beserta bukti struk.</p>
        </div>
        <a href="{{ route('pengeluaran.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('pengeluaran.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
            <input type="date" name="tanggal_pengeluaran" value="{{ old('tanggal_pengeluaran', date('Y-m-d')) }}" required
                   class="w-full text-sm border-slate-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Pengeluaran <span class="text-rose-500">*</span></label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}" required placeholder="Contoh: Bayar Listrik Toko Bulan Ini"
                   class="w-full text-sm border-slate-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
            <input type="number" name="nominal" value="{{ old('nominal') }}" min="0" step="500" required placeholder="50000"
                   class="w-full text-sm border-slate-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Bukti / Struk (Opsional)</label>
            <input type="file" name="bukti_struk" accept="image/*"
                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl">
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('pengeluaran.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                Simpan Pengeluaran
            </button>
        </div>
    </form>
</div>
@endsection
