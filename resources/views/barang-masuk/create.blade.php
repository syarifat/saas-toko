<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Barang Masuk</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('barang-masuk.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Produk *</label>
                    <select name="produk_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($produk as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                    @if ($produk->isEmpty())
                        <p class="text-xs text-red-600 mt-1">Belum ada produk. <a href="{{ route('produk.create') }}" class="underline">Tambah produk dulu.</a></p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium">Gudang Tujuan *</label>
                    <select name="gudang_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($gudang as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }} ({{ $g->jenis }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Jumlah Masuk *</label>
                    <input type="number" name="jumlah" min="1" value="{{ old('jumlah', 1) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Catatan (opsional)</label>
                    <input type="text" name="catatan" placeholder="misal: PO dari pemasok X, nota #123"
                        value="{{ old('catatan') }}" class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('gudang.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
