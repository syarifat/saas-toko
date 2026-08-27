<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Masuk ke akun Anda</h2>
        <p class="text-slate-500 text-sm mt-1">Kelola toko dan operasional dari satu tempat.</p>
    </div>

    <!-- Quick Demo Access -->
    <div class="mb-6 p-4 rounded-lg bg-slate-50 border border-slate-200">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Akses Demo</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button type="button" onclick="fillCredentials('superadmin@gmail.com', 'password')"
                    class="px-3 py-2 rounded-lg bg-white border border-slate-200 hover:border-slate-400 text-left transition text-sm">
                <span class="font-semibold text-slate-800">Superadmin</span>
                <span class="block text-xs text-slate-400 font-mono mt-0.5">superadmin@gmail.com</span>
            </button>
            <button type="button" onclick="fillCredentials('budi@kopinusantara.test', 'password123')"
                    class="px-3 py-2 rounded-lg bg-white border border-slate-200 hover:border-slate-400 text-left transition text-sm">
                <span class="font-semibold text-slate-800">Admin Toko</span>
                <span class="block text-xs text-slate-400 font-mono mt-0.5">budi@kopinusantara.test</span>
            </button>
        </div>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@domain.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-slate-500 hover:text-slate-700" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-500">
            <label for="remember_me" class="ms-2 text-sm text-slate-500">Ingat saya</label>
        </div>

        <x-primary-button class="w-full justify-center">
            Masuk
        </x-primary-button>
    </form>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</x-guest-layout>
