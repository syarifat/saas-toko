<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Transfer Stok</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex justify-end">
            <a href="{{ route('transfer-stok.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Transfer Baru</a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Dari → Ke</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($riwayat as $r)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $r->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $r->produk?->nama }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ abs($r->jumlah) }}</td>
                            <td class="px-4 py-3">{{ $r->gudang?->nama }} → {{ $r->gudangTujuan?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $r->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada transfer tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
