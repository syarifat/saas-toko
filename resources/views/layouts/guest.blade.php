<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Brand Panel -->
            <div class="relative hidden lg:flex lg:flex-col lg:justify-between bg-brand-900 text-white p-12 overflow-hidden">
                <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-brand-700/40 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo class="h-10 w-10" />
                    <span class="font-bold text-xl tracking-tight">SaasToko</span>
                </div>

                <div class="relative max-w-md">
                    <h1 class="text-3xl font-bold leading-snug">Kelola toko, stok & karyawan dalam satu sistem.</h1>
                    <p class="mt-4 text-brand-100/80 leading-relaxed">Mulai dari pencatatan kas sederhana hingga multi-gudang dan payroll — pilih paket yang pas untuk bisnis Anda.</p>
                    <ul class="mt-8 space-y-3 text-brand-100/90 text-sm">
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-brand-300"></span> Kasir POS & manajemen stok real-time</li>
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-brand-300"></span> Multi-gudang & transfer antar cabang</li>
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-brand-300"></span> Absensi GPS & penggajian otomatis</li>
                    </ul>
                </div>

                <p class="relative text-xs text-brand-300/70">© {{ date('Y') }} SaasToko — Mega System SaaS.</p>
            </div>

            <!-- Form Panel -->
            <div class="flex flex-col items-center justify-center px-6 py-12 bg-slate-50">
                <div class="w-full max-w-md">
                    <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
                        <x-application-logo class="h-11 w-11" />
                        <span class="font-bold text-xl tracking-tight text-brand-900">SaasToko</span>
                    </div>

                    <div class="card p-8 sm:p-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
