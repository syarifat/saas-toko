<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Penggajian') }}</h2>
            <a href="{{ route('penggajian.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Buat Penggajian</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-right">Dasar</th>
                        <th class="px-4 py-3 text-right">Tunjangan</th>
                        <th class="px-4 py-3 text-right">Potongan</th>
                        <th class="px-4 py-3 text-right">Gaji Bersih</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($penggajian as $pg)
                        <tr>
                            <td class="px-4 py-3">{{ $pg->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $pg->karyawan?->nama }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $pg->periode_mulai->format('d M') }} – {{ $pg->periode_selesai->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                Rp {{ number_format($pg->jumlah_dasar, 0, ',', '.') }}
                                <span class="text-xs text-gray-400">({{ $pg->skema_gaji_snapshot === 'harian' ? $pg->jumlah_hadir.' hari' : 'pokok' }})</span>
                            </td>
                            <td class="px-4 py-3 text-right text-green-700">Rp {{ number_format($pg->total_tunjangan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format($pg->total_potongan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($pg->gaji_bersih, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $pg->status === 'dibayar' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $pg->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('payslip.show', $pg) }}" target="_blank" class="text-blue-600 hover:underline">Payslip</a>
                                @if ($pg->status === 'draf')
                                    <form action="{{ route('penggajian.dibayar', $pg) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Tandai sudah dibayar?')">
                                        @csrf
                                        <button type="submit" class="text-green-700 hover:underline ml-2">Bayar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data penggajian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $penggajian->links() }}</div>
    </div>
</x-app-layout>
