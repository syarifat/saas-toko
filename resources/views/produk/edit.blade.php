<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Produk: {{ $produk->nama }}</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('produk.update', $produk) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium">SKU *</label>
                        <input type="text" name="sku" value="{{ old('sku', $produk->sku) }}" required
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Kategori</label>
                        <select name="kategori_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">-</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}" {{ old('kategori_id', $produk->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Nama Produk *</label>
                    <input type="text" name="nama" value="{{ old('nama', $produk->nama) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Pemasok</label>
                    <select name="pemasok_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">-</option>
                        @foreach ($pemasok as $pm)
                            <option value="{{ $pm->id }}" {{ old('pemasok_id', $produk->pemasok_id) == $pm->id ? 'selected' : '' }}>{{ $pm->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Harga Beli *</label>
                        <input type="number" name="harga_beli" min="0" step="1" value="{{ old('harga_beli', $produk->harga_beli) }}" required
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Harga Jual *</label>
                        <input type="number" name="harga_jual" min="0" step="1" value="{{ old('harga_jual', $produk->harga_jual) }}" required
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Stok Minimum</label>
                        <input type="number" name="stok_minimum" min="0" value="{{ old('stok_minimum', $produk->stok_minimum) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('produk.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
