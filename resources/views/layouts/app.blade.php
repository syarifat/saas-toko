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
    <body class="font-sans antialiased" x-data="{ open: false }">
        <div class="min-h-screen lg:flex">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-brand-900 text-brand-100">
                <div class="flex items-center gap-3 h-16 px-5 border-b border-brand-800">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-white font-bold text-lg tracking-tight">SaasToko</span>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4">
                    @include('partials.sidebar-nav')
                </nav>

                <div class="border-t border-brand-800 p-3">
                    <div class="flex items-center gap-3 px-2 py-2">
                        <div class="h-9 w-9 rounded-full bg-brand-700 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                            <p class="text-brand-300 text-xs capitalize truncate">{{ str_replace('_', ' ', Auth::user()->peran) }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile Sidebar Drawer -->
            <div x-show="open" class="lg:hidden" style="display: none;">
                <div x-show="open" x-transition.opacity
                    @click="open = false"
                    class="fixed inset-0 bg-slate-900/50 z-40"></div>

                <aside x-show="open" x-transition
                    class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-900 text-brand-100 flex flex-col">
                    <div class="flex items-center justify-between h-16 px-5 border-b border-brand-800">
                        <div class="flex items-center gap-3">
                            <x-application-logo class="h-9 w-9" />
                            <span class="text-white font-bold text-lg tracking-tight">SaasToko</span>
                        </div>
                        <button @click="open = false" class="text-brand-200 hover:text-white p-1">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <nav class="flex-1 overflow-y-auto px-3 py-4">
                        @include('partials.sidebar-nav')
                    </nav>

                    <div class="border-t border-brand-800 p-3">
                        <div class="flex items-center gap-3 px-2 py-2">
                            <div class="h-9 w-9 rounded-full bg-brand-700 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                                <p class="text-brand-300 text-xs capitalize truncate">{{ str_replace('_', ' ', Auth::user()->peran) }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Main Column -->
            <div class="lg:pl-64 flex-1 flex flex-col min-h-screen">
                <header class="sticky top-0 z-30 flex items-center gap-3 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-6 h-16">
                    <button @click="open = true"
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:text-brand-700 hover:bg-brand-50">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex-1 min-w-0 text-brand-900">
                        {{ $header ?? '' }}
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-brand-50 hover:text-brand-700 transition">
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </header>

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
