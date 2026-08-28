<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk — {{ config('app.name', 'SaaSToko') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
    </head>
    <body class="bg-white text-slate-800 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Left: branding -->
            <div class="hidden lg:flex flex-col justify-between bg-slate-900 text-white p-12">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg">SaaSToko</span>
                </a>

                <div class="max-w-sm">
                    <h1 class="text-2xl font-bold leading-snug">Kelola toko, stok, dan karyawan dalam satu sistem.</h1>
                    <p class="mt-3 text-slate-400 text-sm leading-relaxed">Pilih modul yang pas untuk bisnis Anda — dari pencatatan kas sederhana hingga multi-gudang dan payroll.</p>
                    <ul class="mt-6 space-y-2 text-sm text-slate-300">
                        <li class="flex gap-2">— Kasir POS & manajemen stok</li>
                        <li class="flex gap-2">— Multi-gudang & transfer</li>
                        <li class="flex gap-2">— Absensi GPS & penggajian</li>
                    </ul>
                </div>

                <p class="text-xs text-slate-500">© {{ date('Y') }} SaaSToko</p>
            </div>

            <!-- Right: form -->
            <div class="flex flex-col items-center justify-center px-6 py-12 bg-slate-50/50">
                <div class="w-full max-w-lg">
                    <div class="lg:hidden flex items-center gap-2.5 mb-8">
                        <div class="h-8 w-8 rounded-lg bg-slate-900 flex items-center justify-center">
                            <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="font-bold text-lg text-slate-900">SaaSToko</span>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
