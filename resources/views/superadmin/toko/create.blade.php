<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftarkan Toko Baru</h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('superadmin.toko.store') }}" method="POST" class="space-y-4">
                @csrf
                <h3 class="font-semibold border-b pb-2">Data Toko</h3>
                <div>
                    <label class="block text-sm font-medium">Nama Toko *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium">Paket *</label>
                    <select name="paket_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($paket as $p)
                            <option value="{{ $p->id }}" {{ old('paket_id') == $p->id ? 'selected' : '' }}>
                                Tier {{ $p->tingkat }} — {{ $p->nama }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="coba_gratis">Coba Gratis</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <h3 class="font-semibold border-b pb-2 pt-4">Akun Admin Toko</h3>
                <div>
                    <label class="block text-sm font-medium">Nama Admin *</label>
                    <input type="text" name="nama_admin" value="{{ old('nama_admin') }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Email Admin *</label>
                    <input type="email" name="email_admin" value="{{ old('email_admin') }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Password (kosongkan = "password")</label>
                    <input type="password" name="password_admin"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('superadmin.toko.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
