<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Penjualan (Ringkas)') }}</h2>
            <a href="{{ route('penjualan-sederhana.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Catat Penjualan</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white p-4 rounded-lg shadow mb-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-500">Dari</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Sampai</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filter</button>
                <p class="ml-auto font-semibold">Total: Rp {{ number_format($total, 0, ',', '.') }}</p>
            </form>
        </div>

        <div class="space-y-4">
            @forelse ($penjualan as $pj)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-center border-b pb-2 mb-2">
                        <div>
                            <span class="font-semibold">{{ $pj->tanggal_penjualan->format('d M Y') }}</span>
                            <span class="text-sm text-gray-500 ml-2">oleh {{ $pj->pencatat?->name }}</span>
                            @if ($pj->catatan)
                                <p class="text-sm text-gray-500 italic mt-1">{{ $pj->catatan }}</p>
                            @endif
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <span class="text-green-700 font-bold">Rp {{ number_format($pj->total, 0, ',', '.') }}</span>
                            <form action="{{ route('penjualan-sederhana.destroy', $pj) }}" method="POST"
                                onsubmit="return confirm('Hapus penjualan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="py-1">Barang</th>
                                <th class="py-1 text-right">Qty</th>
                                <th class="py-1 text-right">Harga</th>
                                <th class="py-1 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pj->item as $item)
                                <tr class="border-t">
                                    <td class="py-1">{{ $item->nama_barang }}</td>
                                    <td class="py-1 text-right">{{ $item->jumlah }}</td>
                                    <td class="py-1 text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="py-1 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">Belum ada penjualan tercatat.</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $penjualan->links() }}</div>
    </div>
</x-app-layout>
