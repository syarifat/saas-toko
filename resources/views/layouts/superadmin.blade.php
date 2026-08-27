<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Superadmin Panel' }} - SaaS Toko</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 bg-slate-950 font-bold text-lg text-white tracking-wide border-b border-slate-800 gap-2">
                <span class="p-1.5 bg-indigo-600 rounded-lg text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span>Superadmin</span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('superadmin.dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('superadmin.toko.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('superadmin.toko.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Kelola Toko
                </a>

                <a href="{{ route('superadmin.paket.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('superadmin.paket.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Paket & Modul
                </a>

                <a href="{{ route('superadmin.verifikasi.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('superadmin.verifikasi.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verifikasi Bayar
                    </div>
                    @php
                        $menungguCount = \App\Models\Pembayaran::where('status', 'menunggu')->count();
                    @endphp
                    @if($menungguCount > 0)
                        <span class="px-2 py-0.5 text-xs font-bold bg-amber-500 text-slate-950 rounded-full">{{ $menungguCount }}</span>
                    @endif
                </a>

                <a href="{{ route('superadmin.statistik') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('superadmin.statistik') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Statistik
                </a>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 border-t border-slate-800 bg-slate-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->nama ?? 'Super Admin' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0">
                <h1 class="text-xl font-bold text-slate-800">{{ $header ?? 'Superadmin Panel' }}</h1>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        ● Platform Online
                    </span>
                </div>
            </header>

            <!-- Alerts -->
            <main class="flex-1 p-6 overflow-y-auto">
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800">
                        <p class="font-semibold mb-1">Terdapat kesalahan:</p>
                        <ul class="list-disc list-inside text-sm space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
