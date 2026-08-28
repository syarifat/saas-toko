@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900">{{ $toko->nama }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $toko->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ ucfirst($toko->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Slug: <code class="bg-slate-100 px-1 py-0.5 rounded">{{ $toko->slug }}</code> • Terdaftar: {{ $toko->created_at->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.toko.edit', $toko) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition">Edit Info Toko</a>
            <a href="{{ route('superadmin.toko.index') }}" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
        </div>
    </div>

    <!-- Toko Detail Summary & Preset Switcher -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Card -->
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">Informasi Langganan & Lokasi</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Paket Saat Ini</span>
                    <span class="font-semibold text-indigo-700">{{ $toko->paket->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Masa Aktif Hingga</span>
                    <span class="font-semibold text-slate-800">{{ $toko->langganan_berakhir_pada ? $toko->langganan_berakhir_pada->format('d M Y') : 'Tak Terbatas' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Radius Absensi GPS</span>
                    <span class="font-semibold text-slate-800">{{ $toko->radius_absensi }} meter</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Koordinat GPS</span>
                    <span class="text-xs text-slate-600 font-mono">{{ $toko->garis_lintang ? "{$toko->garis_lintang}, {$toko->garis_bujur}" : 'Belum diatur' }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Apply Preset Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <h3 class="font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">Terapkan Preset Paket</h3>
            <p class="text-xs text-slate-500">Menerapkan preset akan mengaktifkan seluruh modul bawaan paket tersebut.</p>
            <div class="space-y-2">
                @foreach($pakets as $p)
                    <form method="POST" action="{{ route('superadmin.toko.pakai-preset', [$toko, $p]) }}" data-confirm="Terapkan preset [{{ $p->nama }}]? Modul terkait akan otomatis diaktifkan.">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 text-xs font-semibold rounded-lg border flex items-center justify-between transition {{ $toko->paket_id === $p->id ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                            <span>{{ $p->nama }}</span>
                            @if($toko->paket_id === $p->id)
                                <span class="text-[10px] uppercase font-bold bg-indigo-600 text-white px-1.5 py-0.5 rounded">Aktif</span>
                            @else
                                <span class="text-indigo-600 hover:underline">Terapkan →</span>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modul Management (16 Modul Table) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800">Manajemen Modul Toko (16 Modul Platform)</h3>
                <p class="text-xs text-slate-500">Superadmin dapat mengaktifkan atau menonaktifkan modul individual untuk toko ini.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Modul</th>
                        <th class="px-6 py-3.5">Prasyarat (Dependencies)</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi Toggle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($modulStatus as $item)
                        @php
                            $modul = $item['modul'];
                            $isAktif = $item['aktif'];
                            $deps = $modul->ketergantungan;
                            $belumAktif = $item['dependency_belum_aktif'];
                            $dependanAktif = $item['dependan_aktif'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isAktif ? 'bg-indigo-50/20' : '' }}">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $modul->nama }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $modul->kode }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($deps->isEmpty())
                                    <span class="text-xs text-slate-400 italic">Tidak ada (Mandiri)</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($deps as $dep)
                                            @php $depAktif = $toko->modulAktif($dep->kode); @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $depAktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                {{ $depAktif ? '✓' : '✗' }} {{ $dep->nama }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($isAktif)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        ○ Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($isAktif)
                                    <form method="POST" action="{{ route('superadmin.toko.modul.nonaktifkan', [$toko, $modul->kode]) }}" class="inline" data-confirm="Nonaktifkan modul [{{ $modul->nama }}]?">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                            Matikan
                                        </button>
                                    </form>
                                @else
                                    @if($belumAktif->isEmpty())
                                        <form method="POST" action="{{ route('superadmin.toko.modul.aktifkan', [$toko, $modul->kode]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superadmin.toko.modul.aktifkan', [$toko, $modul->kode]) }}" class="inline" data-confirm="Modul ini membutuhkan: {{ $belumAktif->pluck('nama')->join(', ') }}. Aktifkan modul beserta semua dependensinya?">
                                            @csrf
                                            <input type="hidden" name="dengan_dependency" value="1">
                                            <button type="submit" 
                                                    class="px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition"
                                                    title="Aktifkan modul beserta {{ $belumAktif->count() }} dependensinya">
                                                Aktifkan + Dep ↗
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
