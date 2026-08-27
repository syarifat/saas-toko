@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rekap Presensi Karyawan</h2>
            <p class="text-sm text-slate-500">Pantau catatan kehadiran harian, keterlambatan, lembur, dan foto selfie.</p>
        </div>
        <form method="GET" action="{{ route('absensi.rekap') }}" class="flex items-center gap-2">
            <select name="karyawan_id" class="text-xs border-slate-300 rounded-lg">
                <option value="">Semua Karyawan</option>
                @foreach($karyawans as $krj)
                    <option value="{{ $krj->id }}" {{ request('karyawan_id') == $krj->id ? 'selected' : '' }}>{{ $krj->pengguna->nama ?? '-' }}</option>
                @endforeach
            </select>
            <input type="month" name="bulan" value="{{ $bulan }}" class="text-xs border-slate-300 rounded-lg">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Karyawan</th>
                    <th class="px-6 py-3.5 text-center">Jam Masuk</th>
                    <th class="px-6 py-3.5 text-center">Jam Keluar</th>
                    <th class="px-6 py-3.5 text-center">Keterlambatan</th>
                    <th class="px-6 py-3.5 text-center">Lembur</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-right">Foto Masuk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($absensis as $a)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900 text-xs">{{ $a->tanggal->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $a->karyawan->pengguna->nama ?? '-' }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $a->karyawan->kode_karyawan ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-800">
                            {{ $a->jam_masuk ? $a->jam_masuk->format('H:i:s') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-800">
                            {{ $a->jam_keluar ? $a->jam_keluar->format('H:i:s') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold {{ $a->menit_telat > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $a->menit_telat > 0 ? $a->menit_telat.' mnt' : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold {{ $a->menit_lembur > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                            {{ $a->menit_lembur > 0 ? $a->menit_lembur.' mnt' : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                {{ $a->status === 'tepat_waktu' ? 'bg-emerald-100 text-emerald-800' : ($a->status === 'telat' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $a->status ?? 'hadir')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($a->foto_masuk)
                                <a href="{{ asset('storage/'.$a->foto_masuk) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:underline">Foto Selfie</a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada riwayat presensi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($absensis->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $absensis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
