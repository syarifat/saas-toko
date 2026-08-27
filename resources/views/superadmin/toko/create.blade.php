@extends('layouts.superadmin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tambah Toko Baru</h2>
            <p class="text-sm text-slate-500">Daftarkan toko tenant beserta akun admin pertamanya.</p>
        </div>
        <a href="{{ route('superadmin.toko.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('superadmin.toko.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
        @csrf

        <!-- Informasi Toko -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 pb-2 border-b border-slate-100">1. Informasi Toko</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Toko Berkah Jaya"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Paket Langganan Awal <span class="text-rose-500">*</span></label>
                    <select name="paket_id" required class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($pakets as $paket)
                            <option value="{{ $paket->id }}" {{ old('paket_id') == $paket->id ? 'selected' : '' }}>
                                {{ $paket->nama }} (Rp {{ number_format($paket->harga, 0, ',', '.') }}/bln)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Garis Lintang (Latitude GPS)</label>
                    <input type="text" name="garis_lintang" value="{{ old('garis_lintang') }}" placeholder="-6.200000"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Garis Bujur (Longitude GPS)</label>
                    <input type="text" name="garis_bujur" value="{{ old('garis_bujur') }}" placeholder="106.816666"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Radius Absensi (meter)</label>
                    <input type="number" name="radius_absensi" value="{{ old('radius_absensi', 100) }}" min="10" max="5000"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Akun Admin Toko -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 pb-2 border-b border-slate-100">2. Akun Administrator Toko</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Admin <span class="text-rose-500">*</span></label>
                    <input type="text" name="admin_nama" value="{{ old('admin_nama') }}" required placeholder="Nama Admin"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@tokoberkah.com"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Awal <span class="text-rose-500">*</span></label>
                    <input type="password" name="admin_password" required placeholder="Minimal 8 karakter"
                           class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('superadmin.toko.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition">
                Simpan & Aktifkan Toko
            </button>
        </div>
    </form>
</div>
@endsection
