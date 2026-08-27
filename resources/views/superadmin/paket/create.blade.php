@extends('layouts.superadmin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Buat Paket Baru</h2>
            <p class="text-sm text-slate-500">Tentukan harga, deskripsi, dan kombinasi modul yang diaktifkan.</p>
        </div>
        <a href="{{ route('superadmin.paket.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('superadmin.paket.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Paket <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Paket Pro Kustom"
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Paket <span class="text-rose-500">*</span></label>
                <select name="jenis" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="custom" {{ old('jenis') === 'custom' ? 'selected' : '' }}>Custom Tier</option>
                    <option value="preset_1" {{ old('jenis') === 'preset_1' ? 'selected' : '' }}>Preset 1</option>
                    <option value="preset_2" {{ old('jenis') === 'preset_2' ? 'selected' : '' }}>Preset 2</option>
                    <option value="preset_3" {{ old('jenis') === 'preset_3' ? 'selected' : '' }}>Preset 3</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga per Bulan (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="harga" value="{{ old('harga', 0) }}" min="0" step="1000" required
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                <select name="aktif" class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="1" {{ old('aktif', '1') == '1' ? 'selected' : '' }}>Aktif (Bisa Dipilih)</option>
                    <option value="0" {{ old('aktif') == '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="2" placeholder="Penjelasan fitur yang didapat..."
                          class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('deskripsi') }}</textarea>
            </div>
        </div>

        <!-- Checklist Modul dengan Dependency Graph Info -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Pilih Modul yang Termasuk <span class="text-rose-500">*</span></h3>
                    <p class="text-xs text-slate-500">Mencentang modul akan otomatis mencentang modul prasyaratnya.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="modul-container">
                @foreach($moduls as $modul)
                    @php
                        $depIds = $modul->ketergantungan->pluck('id')->toArray();
                        $oldSelected = in_array($modul->id, old('modul_ids', []));
                    @endphp
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition cursor-pointer">
                        <input type="checkbox" name="modul_ids[]" value="{{ $modul->id }}" 
                               data-id="{{ $modul->id }}"
                               data-deps="{{ json_encode($depIds) }}"
                               {{ $oldSelected ? 'checked' : '' }}
                               class="modul-checkbox mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                        <div class="text-xs">
                            <span class="font-bold text-slate-900 block">{{ $modul->nama }}</span>
                            <span class="text-slate-500 font-mono">{{ $modul->kode }}</span>
                            @if($modul->ketergantungan->isNotEmpty())
                                <span class="text-[11px] text-amber-700 block mt-0.5">
                                    Requires: {{ $modul->ketergantungan->pluck('nama')->join(', ') }}
                                </span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('superadmin.paket.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition">
                Simpan Paket
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.modul-checkbox');
    
    function autoCheckDeps(cb) {
        if (!cb.checked) return;
        const deps = JSON.parse(cb.dataset.deps || '[]');
        deps.forEach(depId => {
            const target = document.querySelector(`.modul-checkbox[data-id="${depId}"]`);
            if (target && !target.checked) {
                target.checked = true;
                autoCheckDeps(target);
            }
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => autoCheckDeps(cb));
    });
});
</script>
@endsection
