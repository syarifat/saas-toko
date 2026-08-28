@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Hak Akses Menu Staf Karyawan</h2>
            <p class="text-sm text-slate-500">Tentukan menu dan fitur apa saja yang boleh diakses oleh masing-masing akun staf (Kasir, Gudang, dll).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('karyawan.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                ← Kelola Akun Staf
            </a>
        </div>
    </div>

    @if($karyawans->isEmpty())
        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center">
            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <h3 class="font-bold text-slate-800 text-base">Belum Ada Akun Staf Karyawan</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">Tambahkan akun staf terlebih dahulu untuk mengatur hak akses menu.</p>
            <a href="{{ route('karyawan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                + Tambah Akun Staf
            </a>
        </div>
    @else
        <form method="POST" action="{{ route('karyawan.hak-akses.simpan') }}" class="space-y-6">
            @csrf

            <div class="space-y-6">
                @foreach($karyawans as $karyawan)
                    @php
                        $pengguna = $karyawan->pengguna;
                        if (!$pengguna) continue;

                        $roleBadgeColor = match($pengguna->sub_peran) {
                            'kasir' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'gudang' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            default => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        };

                        $roleLabel = match($pengguna->sub_peran) {
                            'kasir' => 'Kasir POS',
                            'gudang' => 'Staff Gudang',
                            default => 'Staf Umum',
                        };
                    @endphp

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Employee Header -->
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($pengguna->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-900 text-sm">{{ $pengguna->nama }}</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $roleBadgeColor }}">
                                            {{ $roleLabel }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $pengguna->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="toggleAllForUser('user-{{ $pengguna->id }}', true)"
                                        class="px-2.5 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition border border-indigo-200">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="toggleAllForUser('user-{{ $pengguna->id }}', false)"
                                        class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition border border-slate-200">
                                    Hapus Semua
                                </button>
                            </div>
                        </div>

                        <!-- Module Permissions Grid -->
                        <div class="p-6" id="user-{{ $pengguna->id }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($modulAktif as $modul)
                                    @php
                                        $hasAccess = $pengguna->punyaAksesModul($modul->kode);
                                    @endphp
                                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/50 cursor-pointer transition select-none">
                                        <input type="checkbox"
                                               name="akses[{{ $pengguna->id }}][]"
                                               value="{{ $modul->id }}"
                                               {{ $hasAccess ? 'checked' : '' }}
                                               class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-semibold text-slate-800 text-xs block leading-tight">{{ $modul->nama }}</span>
                                            <span class="text-[11px] text-slate-500 block mt-0.5 leading-snug">{{ $modul->deskripsi ?? 'Akses menu '.$modul->nama }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Save Bar -->
            <div class="sticky bottom-6 p-4 rounded-2xl bg-slate-900 text-white shadow-xl flex items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-sm">Simpan Hak Akses Menu</p>
                    <p class="text-xs text-slate-400">Perubahan akan langsung berlaku saat staf membuka menu sistem.</p>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow transition shrink-0">
                    Simpan Semua Perubahan
                </button>
            </div>
        </form>
    @endif
</div>

<script>
function toggleAllForUser(containerId, check) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = check);
}
</script>
@endsection
