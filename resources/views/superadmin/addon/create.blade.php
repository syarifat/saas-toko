<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add-on Baru</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('superadmin.addon.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Kode * (misal: absensi, penggajian)</label>
                    <input type="text" name="kode" value="{{ old('kode') }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Nama *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Harga per bulan *</label>
                    <input type="number" name="harga" min="0" step="0.01" value="{{ old('harga') }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="aktif" value="0">
                    <input type="checkbox" name="aktif" value="1" checked> Aktif
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('superadmin.addon.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
