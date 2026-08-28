<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ auth()->user()->toko->nama ?? 'SaaS Toko' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    @php
        $toko = auth()->user()->toko;
    @endphp

    <div class="h-screen flex overflow-hidden">
        <!-- Dynamic Tenant Sidebar -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 h-screen border-r border-slate-800">
            <!-- Store Header (Fixed at top) -->
            <div class="h-16 flex items-center px-6 bg-slate-950 font-bold text-white border-b border-slate-800 gap-3 shrink-0">
                <span class="p-2 bg-indigo-600 rounded-lg text-white font-bold text-sm">
                    {{ strtoupper(substr($toko->nama ?? 'T', 0, 2)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-white truncate">{{ $toko->nama ?? 'Toko Saya' }}</p>
                    <p class="text-[11px] text-slate-400 font-normal truncate">{{ $toko->paket->nama ?? 'Langganan' }}</p>
                </div>
            </div>

            <!-- Dynamic Navigation Links based on Active Modules (Scrollable) -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto sidebar-scroll">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Dashboard
                </a>

                <!-- Modul: Kasir POS -->
                @if($toko && $toko->modulAktif('kasir_pos') && auth()->user()->punyaAksesModul('kasir_pos'))
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Point of Sale</p>
                        <a href="{{ route('kasir.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('kasir.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            Kasir POS
                        </a>
                        <a href="{{ route('kasir.riwayat') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('kasir.riwayat') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Riwayat Transaksi
                        </a>
                    </div>
                @endif

                <!-- Modul: Master Produk -->
                @if($toko && $toko->modulAktif('master_produk') && auth()->user()->punyaAksesModul('master_produk'))
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Katalog Produk</p>
                        <a href="{{ route('produk.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('produk.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            Master Produk
                        </a>
                        <a href="{{ route('kategori.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('kategori.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            Kategori
                        </a>
                        <a href="{{ route('pemasok.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('pemasok.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Pemasok
                        </a>
                    </div>
                @endif

                <!-- Modul: Stok & Pergudangan -->
                @if($toko && (
                    ($toko->modulAktif('stock_alert') && auth()->user()->punyaAksesModul('stock_alert')) ||
                    ($toko->modulAktif('stok_opname') && auth()->user()->punyaAksesModul('stok_opname')) ||
                    ($toko->modulAktif('multi_gudang') && auth()->user()->punyaAksesModul('multi_gudang')) ||
                    ($toko->modulAktif('barang_masuk') && auth()->user()->punyaAksesModul('barang_masuk')) ||
                    ($toko->modulAktif('transfer_gudang') && auth()->user()->punyaAksesModul('transfer_gudang')) ||
                    ($toko->modulAktif('kartu_stok') && auth()->user()->punyaAksesModul('kartu_stok'))
                ))
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Stok & Inventori</p>
                        @if($toko->modulAktif('stock_alert') && auth()->user()->punyaAksesModul('stock_alert'))
                            <a href="{{ route('stok.alert') }}"
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('stok.alert') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    Alert Stok Menipis
                                </div>
                            </a>
                        @endif
                        @if($toko->modulAktif('stok_opname') && auth()->user()->punyaAksesModul('stok_opname'))
                            <a href="{{ route('stok.opname') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('stok.opname') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                Stok Opname
                            </a>
                        @endif
                        @if($toko->modulAktif('multi_gudang') && auth()->user()->punyaAksesModul('multi_gudang'))
                            <a href="{{ route('gudang.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('gudang.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                Kelola Gudang
                            </a>
                        @endif
                        @if($toko->modulAktif('barang_masuk') && auth()->user()->punyaAksesModul('barang_masuk'))
                            <a href="{{ route('barang_masuk.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('barang_masuk.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>
                                Barang Masuk (Restock)
                            </a>
                        @endif
                        @if($toko->modulAktif('transfer_gudang') && auth()->user()->punyaAksesModul('transfer_gudang'))
                            <a href="{{ route('transfer_gudang.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('transfer_gudang.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                Transfer Gudang
                            </a>
                        @endif
                        @if($toko->modulAktif('kartu_stok') && auth()->user()->punyaAksesModul('kartu_stok'))
                            <a href="{{ route('stok.kartu') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('stok.kartu*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Kartu Stok Detail
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Modul: Keuangan & Laporan -->
                @if($toko && (
                    ($toko->modulAktif('penjualan_ringkas') && auth()->user()->punyaAksesModul('penjualan_ringkas')) ||
                    ($toko->modulAktif('pengeluaran') && auth()->user()->punyaAksesModul('pengeluaran')) ||
                    ($toko->modulAktif('rekap_keuangan') && auth()->user()->punyaAksesModul('rekap_keuangan')) ||
                    ($toko->modulAktif('laporan_hpp') && auth()->user()->punyaAksesModul('laporan_hpp'))
                ))
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Keuangan & Pembukuan</p>
                        @if($toko->modulAktif('penjualan_ringkas') && auth()->user()->punyaAksesModul('penjualan_ringkas'))
                            <a href="{{ route('penjualan.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('penjualan.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Penjualan Ringkas
                            </a>
                        @endif
                        @if($toko->modulAktif('pengeluaran') && auth()->user()->punyaAksesModul('pengeluaran'))
                            <a href="{{ route('pengeluaran.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('pengeluaran.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Catat Pengeluaran
                            </a>
                        @endif
                        @if($toko->modulAktif('rekap_keuangan') && auth()->user()->punyaAksesModul('rekap_keuangan'))
                            <a href="{{ route('rekap.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('rekap.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                Rekap Laba Kotor
                            </a>
                        @endif
                        @if($toko->modulAktif('laporan_hpp') && auth()->user()->punyaAksesModul('laporan_hpp'))
                            <a href="{{ route('laporan.hpp') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('laporan.hpp') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Laporan HPP & Laba POS
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Modul: SDM / Karyawan / Absensi / Payroll -->
                @if($toko && ($toko->modulAktif('karyawan') || $toko->modulAktif('kasir_pos') || $toko->modulAktif('multi_gudang') || $toko->modulAktif('absensi') || $toko->modulAktif('payroll')))
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Karyawan & Staf</p>
                        @if(auth()->user()->isAdmin() && ($toko->modulAktif('karyawan') || $toko->modulAktif('kasir_pos') || $toko->modulAktif('multi_gudang')))
                            <a href="{{ route('karyawan.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('karyawan.index') || request()->routeIs('karyawan.create') || request()->routeIs('karyawan.edit') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Kelola Akun Staf
                            </a>
                            <a href="{{ route('karyawan.hak-akses') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('karyawan.hak-akses*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                Hak Akses Menu Staf
                            </a>
                        @endif
                        @if($toko->modulAktif('absensi') && auth()->user()->punyaAksesModul('absensi'))
                            <a href="{{ route('absensi.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('absensi.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Absensi GPS (Presensi)
                            </a>
                            <a href="{{ route('absensi.rekap') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('absensi.rekap') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Rekap Kehadiran
                            </a>
                        @endif
                        @if($toko->modulAktif('payroll') && auth()->user()->punyaAksesModul('payroll'))
                            <a href="{{ route('penggajian.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('penggajian.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                Penggajian & Slip Gaji
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Langganan & Addon (Hanya Admin) -->
                @if(auth()->user()->isAdmin())
                    <div class="pt-2">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Paket & Langganan</p>
                        <a href="{{ route('tagihan.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('tagihan.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            Upgrade Paket & Addon
                        </a>
                    </div>
                @endif
            </nav>

            <!-- User Footer (Fixed at bottom, ALWAYS in view) -->
            <div class="p-3.5 border-t border-slate-800 bg-slate-950 flex items-center justify-between shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->nama }}</p>
                    <p class="text-[10px] text-slate-400 truncate uppercase">{{ auth()->user()->peran }} {{ auth()->user()->sub_peran ? '('.auth()->user()->sub_peran.')' : '' }}</p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('profile.edit') }}" title="Profil" class="p-1.5 text-slate-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-red-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <!-- Topbar (Fixed at top) -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-bold text-slate-800">{{ $header ?? 'Dashboard' }}</h1>
                </div>

                <!-- Expiry Notification -->
                @if($toko && $toko->langganan_berakhir_pada)
                    @php
                        $sisaHari = (int) now()->diffInDays($toko->langganan_berakhir_pada, false);
                    @endphp
                    @if($sisaHari <= 7 && $sisaHari >= 0)
                        <div class="flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 rounded-lg text-xs font-semibold text-amber-800">
                            <span>Masa aktif tersisa {{ $sisaHari }} hari</span>
                            <a href="{{ route('tagihan.index') }}" class="underline text-indigo-700">Perpanjang</a>
                        </div>
                    @elseif($sisaHari < 0)
                        <div class="flex items-center gap-2 px-3 py-1 bg-rose-50 border border-rose-200 rounded-lg text-xs font-semibold text-rose-800">
                            <span>Masa langganan telah berakhir</span>
                            <a href="{{ route('tagihan.index') }}" class="underline font-bold text-rose-900">Perpanjang Sekarang</a>
                        </div>
                    @endif
                @endif
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- SweetAlert2 Global Notifications -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Toast.fire({
                    icon: 'success',
                    title: '{{ addslashes(session('success')) }}'
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian',
                    text: '{{ addslashes(session('error')) }}',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Tutup'
                });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Terdapat Kendala Input',
                    html: '<ul style="text-align:left; font-size: 14px; margin-top: 8px; line-height: 1.6;">@foreach($errors->all() as $err)<li>• {{ addslashes($err) }}</li>@endforeach</ul>',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Perbaiki'
                });
            });
        </script>
    @endif
</body>
</html>
