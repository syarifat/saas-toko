<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Paket: {{ $paket->nama }}</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('superadmin.paket.update', $paket) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium">Nama Paket</label>
                    <input type="text" name="nama" value="{{ old('nama', $paket->nama) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Tier (1-3)</label>
                    <input type="number" name="tingkat" min="1" max="3" value="{{ old('tingkat', $paket->tingkat) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Harga per bulan</label>
                    <input type="number" name="harga" min="0" step="0.01" value="{{ old('harga', $paket->harga) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="mt-1 w-full rounded-md border-gray-300">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="aktif" value="0">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', $paket->aktif) ? 'checked' : '' }}> Aktif
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('superadmin.paket.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
