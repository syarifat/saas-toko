@extends('layouts.tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Edit Karyawan: {{ $karyawan->pengguna->nama ?? '-' }}</h2>
            <p class="text-sm text-slate-500">Perbarui data jabatan dan skema kompensasi gaji.</p>
        </div>
        <a href="{{ route('karyawan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('karyawan.update', $karyawan) }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $karyawan->pengguna->nama ?? '') }}" required class="w-full text-sm border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tugas Khusus (Sub Peran)</label>
                <select name="sub_peran" class="w-full text-sm border-slate-300 rounded-lg">
                    <option value="">Staf Umum</option>
                    <option value="kasir" {{ old('sub_peran', $karyawan->pengguna->sub_peran ?? '') === 'kasir' ? 'selected' : '' }}>Kasir POS</option>
                    <option value="gudang" {{ old('sub_peran', $karyawan->pengguna->sub_peran ?? '') === 'gudang' ? 'selected' : '' }}>Staf Gudang</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Karyawan <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_karyawan" value="{{ old('kode_karyawan', $karyawan->kode_karyawan) }}" required class="w-full text-sm border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                <input type="text" name="posisi" value="{{ old('posisi', $karyawan->posisi) }}" class="w-full text-sm border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Bekerja <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $karyawan->tanggal_masuk->format('Y-m-d')) }}" required class="w-full text-sm border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Karyawan</label>
                <select name="aktif" class="w-full text-sm border-slate-300 rounded-lg">
                    <option value="1" {{ old('aktif', $karyawan->aktif ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif Bekerja</option>
                    <option value="0" {{ old('aktif', $karyawan->aktif ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif / Berhenti</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Skema Gaji <span class="text-rose-500">*</span></label>
                <select name="skema_gaji" id="skema-gaji" class="w-full text-sm border-slate-300 rounded-lg" onchange="toggleSkema(this.value)">
                    <option value="harian" {{ old('skema_gaji', $karyawan->skema_gaji) === 'harian' ? 'selected' : '' }}>Gaji Harian</option>
                    <option value="bulanan" {{ old('skema_gaji', $karyawan->skema_gaji) === 'bulanan' ? 'selected' : '' }}>Gaji Pokok Bulanan</option>
                </select>
            </div>
            <div id="wrapper-harian">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Upah per Hari (Rp)</label>
                <input type="number" name="tarif_harian" value="{{ old('tarif_harian', (int)$karyawan->tarif_harian) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
            </div>
            <div id="wrapper-bulanan" class="hidden">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gaji Pokok Bulanan (Rp)</label>
                <input type="number" name="gaji_pokok" value="{{ old('gaji_pokok', (int)$karyawan->gaji_pokok) }}" min="0" step="1000" class="w-full text-sm border-slate-300 rounded-lg">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="if(confirm('Hapus karyawan ini beserta riwayatnya?')) document.getElementById('delete-karyawan-form').submit();"
                    class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                Hapus Karyawan
            </button>
            <div class="flex gap-2">
                <a href="{{ route('karyawan.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-karyawan-form" method="POST" action="{{ route('karyawan.destroy', $karyawan) }}" class="hidden">
        @csrf
        @method('DELETE')
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
