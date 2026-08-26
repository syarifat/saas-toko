<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kelola Gudang') }}</h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="font-semibold mb-2">Tambah Gudang / Lokasi</h3>
            <form action="{{ route('gudang.store') }}" method="POST" class="flex flex-wrap gap-2 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="nama" placeholder="Nama (misal: Gudang Utama)" required
                        class="w-full rounded-md border-gray-300">
                </div>
                <select name="jenis" class="rounded-md border-gray-300">
                    <option value="etalase">Etalase</option>
                    <option value="gudang">Gudang</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Tambah</button>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jenis Barang Tersimpan</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach (auth()->user()->toko->gudang()->with('stokGudang')->orderBy('nama')->get() as $g)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $g->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs {{ $g->jenis === 'etalase' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $g->jenis }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $g->stokGudang()->where('jumlah', '>', 0)->count() }} produk berisi stok</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('gudang.destroy', $g) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus gudang ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="{{ route('barang-masuk.create') }}" class="block bg-white p-4 rounded-lg shadow hover:bg-gray-50">
                📥 <strong>Barang Masuk</strong><br><span class="text-sm text-gray-500">Penerimaan barang dari pemasok</span>
            </a>
            <a href="{{ route('transfer-stok.create') }}" class="block bg-white p-4 rounded-lg shadow hover:bg-gray-50">
                🔁 <strong>Transfer Stok</strong><br><span class="text-sm text-gray-500">Pindah barang antar gudang</span>
            </a>
            <a href="{{ route('kartu-stok.index') }}" class="block bg-white p-4 rounded-lg shadow hover:bg-gray-50">
                📋 <strong>Kartu Stok</strong><br><span class="text-sm text-gray-500">Histori pergerakan per barang</span>
            </a>
        </div>
    </div>
</x-app-layout>
