<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transfer Stok Antar Gudang</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            @if ($gudang->count() < 2)
                <div class="mb-4 p-4 bg-yellow-50 text-yellow-800 rounded text-sm">
                    Anda perlu minimal 2 gudang untuk transfer.
                    <a href="{{ route('gudang.index') }}" class="underline">Kelola gudang dulu.</a>
                </div>
            @endif

            <form action="{{ route('transfer-stok.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Produk *</label>
                    <select name="produk_id" id="produk" required onchange="tampilStok()" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($produk as $p)
                            @php($stokEtalase = $p->stokGudang->firstWhere('gudang_id', auth()->user()->toko->gudangUtama()->id)?->jumlah ?? 0)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Gudang Asal *</label>
                        <select name="gudang_asal_id" required class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($gudang as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Gudang Tujuan *</label>
                        <select name="gudang_tujuan_id" required class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($gudang as $i => $g)
                                <option value="{{ $g->id }}" {{ $loop->first && $i === 0 && $gudang->count() > 1 ? '' : '' }}>{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Jumlah *</label>
                    <input type="number" name="jumlah" min="1" value="{{ old('jumlah', 1) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Catatan (opsional)</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('transfer-stok.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
