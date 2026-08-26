<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Absensi') }}</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Kartu absensi pribadi --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold mb-1">Absensi Saya</h3>
            @if (! $karyawanSaya)
                <p class="text-sm text-gray-500">Akun Anda belum terhubung dengan data karyawan. Hubungi admin toko.</p>
            @else
                <p class="text-sm text-gray-500 mb-4">
                    Toko: {{ $toko->nama }} · Radius absensi: {{ $toko->radius_absensi }} meter
                    @if (! $toko->garis_lintang || ! $toko->garis_bujur)
                        · <span class="text-red-600 font-semibold">Koordinat toko belum diatur admin!</span>
                    @endif
                </p>

                @if (! $sudahMasuk)
                    <form action="{{ route('absensi.clock-in') }}" method="POST" enctype="multipart/form-data"
                        onsubmit="isiLokasi(this); return false;" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <input type="hidden" name="lintang"><input type="hidden" name="bujur">
                        <div>
                            <label class="block text-xs text-gray-500">Selfie (opsional)</label>
                            <input type="file" name="foto" accept="image/*" class="mt-1 rounded-md border-gray-300">
                        </div>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-500">
                            🕐 Clock In
                        </button>
                    </form>
                @elseif (! $sudahKeluar)
                    <form action="{{ route('absensi.clock-out') }}" method="POST" enctype="multipart/form-data"
                        onsubmit="isiLokasi(this); return false;" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <input type="hidden" name="lintang"><input type="hidden" name="bujur">
                        <div>
                            <label class="block text-xs text-gray-500">Selfie (opsional)</label>
                            <input type="file" name="foto" accept="image/*" class="mt-1 rounded-md border-gray-300">
                        </div>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-500">
                            🏁 Clock Out
                        </button>
                    </form>
                @else
                    <p class="text-sm text-green-700 font-semibold">✓ Anda sudah lengkap absen hari ini.</p>
                @endif
            @endif
        </div>

        {{-- Riwayat --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4 border-b flex flex-wrap gap-3 items-end justify-between">
                <h3 class="font-semibold">Riwayat Kehadiran</h3>
                <form method="GET" class="flex gap-2">
                    <input type="date" name="mulai" value="{{ request('mulai') }}" class="rounded-md border-gray-300">
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="rounded-md border-gray-300">
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filter</button>
                </form>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Masuk</th>
                        <th class="px-4 py-3 text-left">Keluar</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Telat/Lembur (mnt)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($riwayat as $a)
                        <tr>
                            <td class="px-4 py-3">{{ $a->tanggal->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $a->karyawan?->nama }}</td>
                            <td class="px-4 py-3">{{ $a->jam_masuk?->format('H:i') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $a->jam_keluar?->format('H:i') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $a->status === 'telat' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $a->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $a->menit_telat }} / {{ $a->menit_lembur }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $riwayat->links() }}</div>
    </div>

    <script>
        function jarakMeter(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function isiLokasi(form) {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolocation.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    form.lintang.value = pos.coords.latitude.toFixed(7);
                    form.bujur.value = pos.coords.longitude.toFixed(7);
                    form.submit();
                },
                () => alert('Gagal mendapatkan lokasi. Izinkan akses lokasi di browser Anda.'),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    </script>
</x-app-layout>
