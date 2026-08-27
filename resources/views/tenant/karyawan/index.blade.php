@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manajemen Karyawan</h2>
            <p class="text-sm text-slate-500">Kelola akun staf, posisi kerja, dan konfigurasi skema penggajian.</p>
        </div>
        <a href="{{ route('karyawan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Karyawan
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Kode / Nama</th>
                    <th class="px-6 py-3.5">Email Login</th>
                    <th class="px-6 py-3.5">Posisi / Sub Peran</th>
                    <th class="px-6 py-3.5">Skema Gaji</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono text-slate-400 block">{{ $k->kode_karyawan }}</span>
                            <span class="font-bold text-slate-900">{{ $k->pengguna->nama ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $k->pengguna->email ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs">
                            <span class="font-semibold text-slate-800">{{ $k->posisi ?? '-' }}</span>
                            @if($k->pengguna && $k->pengguna->sub_peran)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase ml-1">{{ $k->pengguna->sub_peran }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($k->skema_gaji === 'harian')
                                <span class="font-semibold text-slate-800">Harian: Rp {{ number_format($k->tarif_harian, 0, ',', '.') }}/hari</span>
                            @else
                                <span class="font-semibold text-slate-800">Bulanan: Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}/bln</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $k->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $k->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('karyawan.edit', $k) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada data karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($karyawans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $karyawans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
