<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel Superadmin') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Total Toko</p>
                <p class="text-3xl font-bold">{{ $jumlahToko }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Toko Aktif</p>
                <p class="text-3xl font-bold">{{ $tokoAktif }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Total Pengguna</p>
                <p class="text-3xl font-bold">{{ $jumlahPengguna }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-4">Toko per Paket</h3>
                <ul class="space-y-2">
                    @foreach ($paket as $p)
                        <li class="flex justify-between border-b pb-1">
                            <span>{{ $p->nama }} (Tier {{ $p->tingkat }})</span>
                            <span class="font-semibold">{{ $p->toko_count }} toko</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-4">Master Add-on</h3>
                <ul class="space-y-2">
                    @foreach ($addon as $a)
                        <li class="flex justify-between border-b pb-1">
                            <span>{{ $a->nama }}</span>
                            <span class="font-semibold">Rp {{ number_format($a->harga, 0, ',', '.') }}/bln</span>
                        </li>
                    @endforeach
                </ul>

                @php($menungguVerifikasi = \App\Models\Pembayaran::where('status', 'menunggu')->count())
                <a href="{{ route('superadmin.verifikasi.index') }}"
                    class="mt-4 block px-4 py-2 {{ $menungguVerifikasi > 0 ? 'bg-yellow-500 hover:bg-yellow-400' : 'bg-gray-800 hover:bg-gray-700' }} text-white rounded-md text-center text-sm font-semibold">
                    Verifikasi Pembayaran{{ $menungguVerifikasi > 0 ? " ({$menungguVerifikasi} menunggu)" : '' }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
