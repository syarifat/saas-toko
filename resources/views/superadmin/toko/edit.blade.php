<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Toko: {{ $toko->nama }}</h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('superadmin.toko.update', $toko) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium">Nama Toko</label>
                    <input type="text" name="nama" value="{{ old('nama', $toko->nama) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Paket</label>
                    <select name="paket_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($paket as $p)
                            <option value="{{ $p->id }}" {{ old('paket_id', $toko->paket_id) == $p->id ? 'selected' : '' }}>
                                Tier {{ $p->tingkat }} — {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach (['coba_gratis', 'aktif', 'nonaktif'] as $s)
                            <option value="{{ $s }}" {{ old('status', $toko->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Garis Lintang</label>
                        <input type="text" name="garis_lintang" value="{{ old('garis_lintang', $toko->garis_lintang) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Garis Bujur</label>
                        <input type="text" name="garis_bujur" value="{{ old('garis_bujur', $toko->garis_bujur) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Radius Absensi (m)</label>
                        <input type="number" name="radius_absensi" value="{{ old('radius_absensi', $toko->radius_absensi) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Langganan Berakhir Pada</label>
                    <input type="date" name="langganan_berakhir_pada"
                        value="{{ old('langganan_berakhir_pada', $toko->langganan_berakhir_pada?->format('Y-m-d')) }}"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('superadmin.toko.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
