<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Verifikasi Pembayaran') }}</h2>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
        @if (session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        {{-- Menunggu verifikasi --}}
        <div class="space-y-4">
            <h3 class="font-semibold">Menunggu Verifikasi ({{ $menunggu->count() }})</h3>
            @forelse ($menunggu as $pb)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex flex-wrap justify-between items-start gap-3">
                        <div class="text-sm space-y-0.5">
                            <p class="font-bold">{{ $pb->labelItem() }} × {{ $pb->jumlah_bulan }} bln</p>
                            <p>Toko: <strong>{{ $pb->toko?->nama }}</strong></p>
                            <p>Diajukan oleh: {{ $pb->pengaju?->name }} ({{ $pb->pengaju?->email }})</p>
                            <p>Nominal transfer: <span class="font-semibold text-green-700">Rp {{ number_format($pb->nominal, 0, ',', '.') }}</span></p>
                            @if ($pb->catatan_tenant)
                                <p class="text-gray-500 italic">Catatan: {{ $pb->catatan_tenant }}</p>
                            @endif
                            @if ($pb->bukti_transfer)
                                <p><a href="{{ asset('storage/'.$pb->bukti_transfer) }}" target="_blank"
                                    class="text-blue-600 hover:underline">Lihat bukti transfer ↗</a></p>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 w-full sm:w-auto">
                            <form action="{{ route('superadmin.verifikasi.setujui', $pb) }}" method="POST"
                                onsubmit="return confirm('Setujui pembayaran ini dan aktifkan untuk toko terkait?')">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-500 font-semibold">✓ Setujui</button>
                            </form>
                            <details>
                                <summary class="text-red-600 hover:underline cursor-pointer text-sm text-center">Tolak…</summary>
                                <form action="{{ route('superadmin.verifikasi.tolak', $pb) }}" method="POST" class="mt-2 space-y-2">
                                    @csrf
                                    <input type="text" name="catatan_admin" placeholder="Alasan penolakan (wajib)" required
                                        class="w-full rounded-md border-gray-300 text-xs">
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-500">Tolak</button>
                                </form>
                            </details>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">Tidak ada pengajuan menunggu verifikasi.</div>
            @endforelse
        </div>

        {{-- Riwayat --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <h3 class="font-semibold p-4 border-b">Riwayat Verifikasi</h3>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Toko</th>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Diverifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($riwayat as $pb)
                        <tr>
                            <td class="px-4 py-3">{{ $pb->id }}</td>
                            <td class="px-4 py-3">{{ $pb->toko?->nama }}</td>
                            <td class="px-4 py-3">{{ $pb->labelItem() }} × {{ $pb->jumlah_bulan }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($pb->nominal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $pb->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pb->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $pb->verifier?->name }} · {{ $pb->diverifikasi_pada?->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
