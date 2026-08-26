<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stok Opname') }} — {{ $gudang->nama }}</h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-sm text-gray-500 mb-4">
                Masukkan jumlah stok fisik hasil hitung. Sistem akan menyesuaikan selisihnya dan mencatatnya sebagai opname.
            </p>

            @include('partials.errors')

            <form action="{{ route('stok-opname.store') }}" method="POST">
                @csrf
                <input type="hidden" name="gudang_id" value="{{ $gudang->id }}">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Produk</th>
                            <th class="py-2 text-right">Stok Sistem</th>
                            <th class="py-2 text-right">Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produk as $p)
                            <tr class="border-b">
                                <td class="py-2">
                                    {{ $p->nama }}
                                    <input type="hidden" name="opname[{{ $loop->index }}][produk_id]" value="{{ $p->id }}">
                                </td>
                                <td class="py-2 text-right">{{ $p->stokGudang->first()?->jumlah ?? 0 }}</td>
                                <td class="py-2 text-right">
                                    <input type="number" name="opname[{{ $loop->index }}][jumlah_fisik]" min="0"
                                        value="{{ $p->stokGudang->first()?->jumlah ?? 0 }}"
                                        class="w-24 rounded-md border-gray-300 text-right">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-gray-500">Belum ada produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan Opname</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
