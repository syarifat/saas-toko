<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-xl font-bold text-slate-900">Masuk ke Akun Anda</h2>
        <p class="text-slate-500 text-sm mt-0.5">Pilih salah satu akun demo di bawah untuk mencoba langsung.</p>
    </div>

    <!-- Quick Demo Access Dynamic List with Package Tags -->
    @if(isset($penggunas) && $penggunas->isNotEmpty())
        <div class="mb-6 p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Akses Cepat Demo (1-Klik Isi Form)
                </span>
                <span class="text-[11px] text-slate-500 font-mono">Password: <strong class="text-slate-900">password</strong></span>
            </div>

            <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                @foreach($penggunas as $user)
                    @php
                        $isSuper = $user->isSuperadmin();
                        $toko = $user->toko;
                        $paket = $toko?->paket;

                        $paketLabel = match(true) {
                            $isSuper => 'Superadmin Platform',
                            $paket?->jenis === 'preset_1' => 'Paket 1 — Cashbook',
                            $paket?->jenis === 'preset_2' => 'Paket 2 — POS & Stok',
                            $paket?->jenis === 'preset_3' => 'Paket 3 — Multi-Gudang',
                            default => $paket?->nama ?? 'Custom Paket',
                        };

                        $paketBadgeColor = match(true) {
                            $isSuper => 'bg-purple-50 text-purple-700 border-purple-200',
                            $paket?->jenis === 'preset_1' => 'bg-slate-100 text-slate-700 border-slate-300',
                            $paket?->jenis === 'preset_2' => 'bg-blue-50 text-blue-700 border-blue-200',
                            $paket?->jenis === 'preset_3' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };

                        $roleLabel = match($user->peran) {
                            'superadmin' => 'Superadmin',
                            'admin' => 'Admin Toko',
                            'karyawan' => match($user->sub_peran) {
                                'kasir' => 'Kasir POS',
                                'gudang' => 'Staff Gudang',
                                default => 'Staf',
                            },
                            default => ucfirst($user->peran),
                        };

                        $rolePillColor = match($user->peran) {
                            'superadmin' => 'bg-purple-600 text-white',
                            'admin' => 'bg-slate-900 text-white',
                            default => match($user->sub_peran) {
                                'kasir' => 'bg-amber-600 text-white',
                                'gudang' => 'bg-emerald-600 text-white',
                                default => 'bg-indigo-600 text-white',
                            },
                        };

                        $tokoName = $toko ? $toko->nama : 'Platform SaaS Superadmin';
                    @endphp

                    <button type="button"
                            onclick="fillCredentials('{{ $user->email }}', 'password', this)"
                            class="demo-account-btn w-full p-2.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/40 text-left transition flex items-center justify-between gap-3 group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $user->nama }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $rolePillColor }}">
                                    {{ $roleLabel }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $paketBadgeColor }}">
                                    {{ $paketLabel }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-500 truncate">
                                <span class="font-mono font-medium text-slate-700">{{ $user->email }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="truncate text-slate-600 font-medium">{{ $tokoName }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 opacity-0 group-hover:opacity-100 transition shrink-0">
                            Pilih →
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@gmail.com" />
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

        <x-primary-button class="w-full justify-center py-2.5">
            Masuk ke Aplikasi
        </x-primary-button>
    </form>

    <script>
        function fillCredentials(email, password, btnElement) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            emailInput.value = email;
            passwordInput.value = password;

            // Highlight selected button
            document.querySelectorAll('.demo-account-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-50', 'border-indigo-500', 'ring-1', 'ring-indigo-500');
            });

            if (btnElement) {
                btnElement.classList.add('bg-indigo-50', 'border-indigo-500', 'ring-1', 'ring-indigo-500');
            }

            // Animate input focus
            emailInput.classList.add('ring-2', 'ring-indigo-400');
            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-indigo-400');
            }, 600);
        }
    </script>
</x-guest-layout>
