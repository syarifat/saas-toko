<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kartu Stok') }}</h2>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs text-gray-500">Pilih Produk</label>
                    <select name="produk_id" required onchange="this.form.submit()"
                        class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">— Pilih produk —</option>
                        @foreach ($produkList as $p)
                            <option value="{{ $p->id }}" {{ old('produk_id', request('produk_id')) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if ($produkDipilih)
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold">{{ $produkDipilih->nama }} — Total stok: {{ $produkDipilih->totalStok() }}</h3>
                <p class="text-sm text-gray-500">
                    @foreach ($produkDipilih->stokGudang as $sg)
                        {{ $sg->gudang->nama }}: {{ $sg->jumlah }}@if (! $loop->last) · @endif
                    @endforeach
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-right">Masuk</th>
                            <th class="px-4 py-3 text-right">Keluar</th>
                            <th class="px-4 py-3 text-left">Gudang</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pergerakan as $pg)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $pg->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs
                                        {{ in_array($pg->jenis, ['masuk']) ? 'bg-green-100 text-green-800' : ($pg->jenis === 'penjualan' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($pg->jenis) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-green-700">{{ $pg->jumlah > 0 ? '+'.$pg->jumlah : '-' }}</td>
                                <td class="px-4 py-3 text-right text-red-600">{{ $pg->jumlah < 0 ? abs($pg->jumlah) : '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ $pg->gudang?->nama }}
                                    @if ($pg->gudangTujuan)
                                        → {{ $pg->gudangTujuan->nama }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $pg->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pergerakan untuk produk ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $pergerakan->links() ?? '' }}</div>
        @endif
    </div>
</x-app-layout>
