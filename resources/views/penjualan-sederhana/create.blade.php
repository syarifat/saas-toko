<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Penjualan</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('penjualan-sederhana.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium">Tanggal *</label>
                    <input type="date" name="tanggal_penjualan" value="{{ old('tanggal_penjualan', now()->toDateString()) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <h3 class="font-semibold text-sm border-b pb-2 mb-2">Daftar Barang Terjual *</h3>
                <div id="daftar-barang" class="space-y-2">
                    @foreach (old('barang', [['nama_barang' => '', 'jumlah' => 1, 'harga_satuan' => '']]) as $baris)
                        <div class="barang-row grid grid-cols-12 gap-2 items-center">
                            <input type="text" name="barang[][nama_barang]" placeholder="Nama barang" value="{{ $baris['nama_barang'] }}"
                                required class="col-span-6 rounded-md border-gray-300">
                            <input type="number" name="barang[][jumlah]" placeholder="Qty" value="{{ $baris['jumlah'] }}" min="1"
                                required class="col-span-2 rounded-md border-gray-300">
                            <input type="number" name="barang[][harga_satuan]" placeholder="Harga satuan" value="{{ $baris['harga_satuan'] }}" min="0"
                                required class="col-span-3 rounded-md border-gray-300">
                            <button type="button" onclick="this.closest('.barang-row').remove()"
                                class="col-span-1 text-red-600 hover:text-red-800">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="tambahBarang()" class="mt-2 text-sm text-blue-600 hover:underline">+ Tambah baris barang</button>

                <div class="mt-4">
                    <label class="block text-sm font-medium">Catatan</label>
                    <textarea name="catatan" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('catatan') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('penjualan-sederhana.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function tambahBarang() {
            const container = document.getElementById('daftar-barang');
            const row = document.createElement('div');
            row.className = 'barang-row grid grid-cols-12 gap-2 items-center';
            row.innerHTML = `
                <input type="text" name="barang[][nama_barang]" placeholder="Nama barang" required class="col-span-6 rounded-md border-gray-300">
                <input type="number" name="barang[][jumlah]" placeholder="Qty" min="1" required class="col-span-2 rounded-md border-gray-300">
                <input type="number" name="barang[][harga_satuan]" placeholder="Harga satuan" min="0" required class="col-span-3 rounded-md border-gray-300">
                <button type="button" onclick="this.closest('.barang-row').remove()" class="col-span-1 text-red-600 hover:text-red-800">&times;</button>
            `;
            container.appendChild(row);
        }
    </script>
</x-app-layout>
