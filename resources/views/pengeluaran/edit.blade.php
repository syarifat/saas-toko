<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pengeluaran</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('pengeluaran.update', $pengeluaran) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium">Tanggal *</label>
                    <input type="date" name="tanggal_pengeluaran" value="{{ old('tanggal_pengeluaran', $pengeluaran->tanggal_pengeluaran->toDateString()) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Keterangan / Nama Barang *</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan', $pengeluaran->keterangan) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Nominal *</label>
                    <input type="number" name="nominal" min="0" step="1" value="{{ old('nominal', $pengeluaran->nominal) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Bukti Struk</label>
                    @if ($pengeluaran->bukti_struk)
                        <p class="text-sm text-gray-500 mb-1">
                            Saat ini:
                            <a href="{{ asset('storage/'.$pengeluaran->bukti_struk) }}" target="_blank" class="text-blue-600 hover:underline">lihat struk lama</a>
                        </p>
                    @endif
                    <input type="file" name="bukti_struk" accept="image/*"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('pengeluaran.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
