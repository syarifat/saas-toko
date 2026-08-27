@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manajemen Toko</h2>
            <p class="text-sm text-slate-500">Kelola daftar toko tenant, status langganan, dan modul aktif.</p>
        </div>
        <a href="{{ route('superadmin.toko.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Toko Baru
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('superadmin.toko.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama toko atau slug..."
                       class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select name="status" class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition">Filter</button>
                @if(request()->anyFilled(['q', 'status', 'paket_id']))
                    <a href="{{ route('superadmin.toko.index') }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama Toko</th>
                        <th class="px-6 py-3.5">Paket</th>
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Langganan Hingga</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tokos as $toko)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                <a href="{{ route('superadmin.toko.show', $toko) }}" class="hover:text-indigo-600">{{ $toko->nama }}</a>
                                <p class="text-xs text-slate-400 font-normal">slug: {{ $toko->slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $toko->paket->nama ?? 'Custom' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $toko->pengguna_count }} pengguna
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $toko->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($toko->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $toko->langganan_berakhir_pada ? $toko->langganan_berakhir_pada->format('d M Y') : 'Tak Terbatas' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('superadmin.toko.show', $toko) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">Kelola Modul</a>
                                <a href="{{ route('superadmin.toko.edit', $toko) }}" class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Tidak ada toko yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tokos->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $tokos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
