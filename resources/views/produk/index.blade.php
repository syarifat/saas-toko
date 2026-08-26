<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Master Produk') }}</h2>
            <a href="{{ route('produk.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Produk Baru</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white p-4 rounded-lg shadow mb-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / SKU..."
                    class="rounded-md border-gray-300">
                <select name="kategori_id" class="rounded-md border-gray-300">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filter</button>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">H. Beli</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">H. Jual</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($produk as $p)
                        @php($stok = $p->totalStok())
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->sku }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->nama }}</td>
                            <td class="px-4 py-3">{{ $p->kategori?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right {{ $stok <= $p->stok_minimum ? 'text-red-600 font-semibold' : '' }}">
                                {{ $stok }} {{ $stok <= $p->stok_minimum ? '⚠' : '' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('produk.edit', $p) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('produk.destroy', $p) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $produk->links() }}</div>

        {{-- Kategori & Pemasok --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Kategori</h3>
                <form action="{{ route('kategori.store') }}" method="POST" class="flex gap-2 mb-3">
                    @csrf
                    <input type="text" name="nama" placeholder="Kategori baru..." required class="flex-1 rounded-md border-gray-300">
                    <button type="submit" class="px-3 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Tambah</button>
                </form>
                <ul class="text-sm space-y-1">
                    @foreach (\App\Models\Kategori::withCount('produk')->get() as $k)
                        <li class="flex justify-between border-b pb-1">
                            <span>{{ $k->nama }} ({{ $k->produk_count }})</span>
                            <form action="{{ route('kategori.destroy', $k) }}" method="POST"
                                onsubmit="return confirm('Hapus kategori?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Pemasok</h3>
                <form action="{{ route('pemasok.store') }}" method="POST" class="flex gap-2 mb-3">
                    @csrf
                    <input type="text" name="nama" placeholder="Nama pemasok..." required class="flex-1 rounded-md border-gray-300">
                    <button type="submit" class="px-3 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Tambah</button>
                </form>
                <ul class="text-sm space-y-1">
                    @foreach (\App\Models\Pemasok::all() as $pm)
                        <li class="flex justify-between border-b pb-1">
                            <span>{{ $pm->nama }} {{ $pm->telepon ? '— '.$pm->telepon : '' }}</span>
                            <form action="{{ route('pemasok.destroy', $pm) }}" method="POST"
                                onsubmit="return confirm('Hapus pemasok?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
