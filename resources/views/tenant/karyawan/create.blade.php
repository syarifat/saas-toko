@extends('layouts.tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tambah Akun Staf Karyawan</h2>
            <p class="text-sm text-slate-500">Buat akun staf login sistem sesuai paket toko Anda.</p>
        </div>
        <a href="{{ route('karyawan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('karyawan.store') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
        @csrf

        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase text-slate-700 pb-2 border-b border-slate-100">1. Informasi Akun Login</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Nama Karyawan / Staf" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="staf@toko.com" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Login <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran / Tugas Staf <span class="text-rose-500">*</span></label>
                    <select name="sub_peran" class="w-full text-sm border-slate-300 rounded-lg">
                        @if(in_array('kasir', $allowedSubPerans))
                            <option value="kasir" {{ old('sub_peran') === 'kasir' ? 'selected' : '' }}>Kasir (Akses Kasir POS)</option>
                        @endif
                        @if(in_array('gudang', $allowedSubPerans))
                            <option value="gudang" {{ old('sub_peran') === 'gudang' ? 'selected' : '' }}>Staff Gudang (Akses Gudang & Stok)</option>
                        @endif
                        @if($hasHrModule)
                            <option value="" {{ old('sub_peran') === '' ? 'selected' : '' }}>Staf Umum</option>
                        @endif
                    </select>
                    @if(!in_array('gudang', $allowedSubPerans) && !in_array('kasir', $allowedSubPerans))
                        <p class="text-[11px] text-amber-600 mt-1">Upgrade paket toko untuk menambah akun staf.</p>
                    @elseif(!in_array('gudang', $allowedSubPerans))
                        <p class="text-[11px] text-slate-500 mt-1">Paket 2 (POS) mendukung akun Kasir. Upgrade ke Paket 3 untuk staf Gudang.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase text-slate-700 pb-2 border-b border-slate-100">2. Profil Staf & Data Kerja</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Karyawan</label>
                    <input type="text" name="kode_karyawan" value="{{ old('kode_karyawan', 'KRY-'.rand(100,999)) }}" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                    <input type="text" name="posisi" value="{{ old('posisi') }}" placeholder="Contoh: Kasir Shift Pagi, Kepala Gudang" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Bekerja</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" class="w-full text-sm border-slate-300 rounded-lg">
                </div>

                @if($hasHrModule)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Skema Gaji</label>
                        <select name="skema_gaji" id="skema-gaji" class="w-full text-sm border-slate-300 rounded-lg" onchange="toggleSkema(this.value)">
                            <option value="bulanan" {{ old('skema_gaji') === 'bulanan' ? 'selected' : '' }}>Gaji Pokok Bulanan</option>
                            <option value="harian" {{ old('skema_gaji') === 'harian' ? 'selected' : '' }}>Gaji Harian (Berdasarkan Kehadiran)</option>
                        </select>
                    </div>
                    <div id="wrapper-bulanan">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Gaji Pokok Bulanan (Rp)</label>
                        <input type="number" name="gaji_pokok" value="{{ old('gaji_pokok', 2500000) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
                    </div>
                    <div id="wrapper-harian" class="hidden">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Upah per Hari (Rp)</label>
                        <input type="number" name="tarif_harian" value="{{ old('tarif_harian', 100000) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('karyawan.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                Simpan Akun Staf
            </button>
        </div>
    </form>
</div>

@if($hasHrModule)
<script>
function toggleSkema(val) {
    if (val === 'harian') {
        document.getElementById('wrapper-harian')?.classList.remove('hidden');
        document.getElementById('wrapper-bulanan')?.classList.add('hidden');
    } else {
        document.getElementById('wrapper-harian')?.classList.add('hidden');
        document.getElementById('wrapper-bulanan')?.classList.remove('hidden');
    }
}
document.addEventListener('DOMContentLoaded', () => toggleSkema(document.getElementById('skema-gaji')?.value));
</script>
@endif
@endsection
