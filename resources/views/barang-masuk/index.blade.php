<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Barang Masuk</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex justify-end">
            <a href="{{ route('barang-masuk.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Catat Barang Masuk</a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Gudang</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-left">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($riwayat as $r)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $r->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $r->produk?->nama }}</td>
                            <td class="px-4 py-3 text-right text-green-700 font-semibold">+{{ $r->jumlah }}</td>
                            <td class="px-4 py-3">{{ $r->gudang?->nama }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $r->catatan ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->pengguna?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada barang masuk tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
