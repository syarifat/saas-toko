@extends('layouts.tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tambah Karyawan Baru</h2>
            <p class="text-sm text-slate-500">Buat profil karyawan beserta akun login sistem.</p>
        </div>
        <a href="{{ route('karyawan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('karyawan.store') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
        @csrf

        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase text-slate-700 pb-2 border-b border-slate-100">1. Akun Login Karyawan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Nama Karyawan" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="karyawan@toko.com" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Login <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tugas Khusus (Sub Peran)</label>
                    <select name="sub_peran" class="w-full text-sm border-slate-300 rounded-lg">
                        <option value="">Staf Umum</option>
                        <option value="kasir" {{ old('sub_peran') === 'kasir' ? 'selected' : '' }}>Kasir POS</option>
                        <option value="gudang" {{ old('sub_peran') === 'gudang' ? 'selected' : '' }}>Staf Gudang</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase text-slate-700 pb-2 border-b border-slate-100">2. Data Pekerjaan & Penggajian</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Karyawan <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_karyawan" value="{{ old('kode_karyawan', 'KRJ-'.rand(100,999)) }}" required class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                    <input type="text" name="posisi" value="{{ old('posisi') }}" placeholder="Contoh: Barista, Kasir Utama" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Bekerja <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skema Gaji <span class="text-rose-500">*</span></label>
                    <select name="skema_gaji" id="skema-gaji" class="w-full text-sm border-slate-300 rounded-lg" onchange="toggleSkema(this.value)">
                        <option value="harian" {{ old('skema_gaji') === 'harian' ? 'selected' : '' }}>Gaji Harian (Berdasarkan Kehadiran)</option>
                        <option value="bulanan" {{ old('skema_gaji') === 'bulanan' ? 'selected' : '' }}>Gaji Pokok Bulanan</option>
                    </select>
                </div>
                <div id="wrapper-harian">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Upah per Hari (Rp)</label>
                    <input type="number" name="tarif_harian" value="{{ old('tarif_harian', 75000) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
                <div id="wrapper-bulanan" class="hidden">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gaji Pokok Bulanan (Rp)</label>
                    <input type="number" name="gaji_pokok" value="{{ old('gaji_pokok', 2000000) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('karyawan.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                Simpan Data Karyawan
            </button>
        </div>
    </form>
</div>

<script>
function toggleSkema(val) {
    if (val === 'harian') {
        document.getElementById('wrapper-harian').classList.remove('hidden');
        document.getElementById('wrapper-bulanan').classList.add('hidden');
    } else {
        document.getElementById('wrapper-harian').classList.add('hidden');
        document.getElementById('wrapper-bulanan').classList.remove('hidden');
    }
}
document.addEventListener('DOMContentLoaded', () => toggleSkema(document.getElementById('skema-gaji').value));
</script>
@endsection
