<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pengeluaran') }}</h2>
            <a href="{{ route('pengeluaran.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Catat Pengeluaran</a>
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

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nominal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Struk</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pengeluaran as $p)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $p->tanggal_pengeluaran->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $p->keterangan }}</td>
                            <td class="px-4 py-3 font-semibold text-red-600">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $p->pencatat?->name }}</td>
                            <td class="px-4 py-3">
                                @if ($p->bukti_struk)
                                    <a href="{{ asset('storage/'.$p->bukti_struk) }}" target="_blank"
                                        class="text-blue-600 hover:underline">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('pengeluaran.edit', $p) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('pengeluaran.destroy', $p) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $pengeluaran->links() }}</div>
    </div>
</x-app-layout>
