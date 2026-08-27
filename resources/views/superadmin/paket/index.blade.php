@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Paket & Konfigurasi Modul</h2>
            <p class="text-sm text-slate-500">Kelola paket langganan (preset default & custom tier) beserta kombinasi modulnya.</p>
        </div>
        <a href="{{ route('superadmin.paket.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Paket Baru
        </a>
    </div>

    <!-- Paket Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($pakets as $paket)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider 
                            {{ str_starts_with($paket->jenis, 'preset') ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                            {{ str_replace('_', ' ', strtoupper($paket->jenis)) }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $paket->toko_count }} Toko</span>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $paket->nama }}</h3>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1">
                            Rp {{ number_format($paket->harga, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-500">/bulan</span>
                        </p>
                        @if($paket->deskripsi)
                            <p class="text-xs text-slate-600 mt-2">{{ $paket->deskripsi }}</p>
                        @endif
                    </div>

                    <!-- Modul List in Package -->
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-700 mb-2">Modul Termasuk ({{ $paket->modul->count() }} modul):</p>
                        <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                            @foreach($paket->modul as $m)
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                                    {{ $m->nama }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 mt-6 flex items-center justify-between">
                    <a href="{{ route('superadmin.paket.edit', $paket) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Edit Paket & Modul →</a>
                    @if($paket->toko_count === 0 && !str_starts_with($paket->jenis, 'preset'))
                        <form method="POST" action="{{ route('superadmin.paket.destroy', $paket) }}" onsubmit="return confirm('Hapus paket ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800">Hapus</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
