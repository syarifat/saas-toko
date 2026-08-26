<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Karyawan: {{ $karyawan->nama }}</h2>
    </x-slot>

    <div class="py-12 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('karyawan.update', $karyawan) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium">Nama *</label>
                    <input type="text" name="nama" value="{{ old('nama', $karyawan->nama) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Posisi</label>
                    <input type="text" name="posisi" value="{{ old('posisi', $karyawan->posisi) }}"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Skema Gaji *</label>
                    <select name="skema_gaji" id="skema_gaji" onchange="toggleGaji()" required
                        class="mt-1 w-full rounded-md border-gray-300">
                        <option value="harian" {{ old('skema_gaji', $karyawan->skema_gaji) === 'harian' ? 'selected' : '' }}>Harian</option>
                        <option value="bulanan" {{ old('skema_gaji', $karyawan->skema_gaji) === 'bulanan' ? 'selected' : '' }}>Bulanan (pokok)</option>
                    </select>
                </div>
                <div id="field-harian">
                    <label class="block text-sm font-medium">Tarif Harian</label>
                    <input type="number" name="tarif_harian" min="0" step="1" value="{{ old('tarif_harian', $karyawan->tarif_harian) }}"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div id="field-bulanan" class="hidden">
                    <label class="block text-sm font-medium">Gaji Pokok Bulanan</label>
                    <input type="number" name="gaji_pokok" min="0" step="1" value="{{ old('gaji_pokok', $karyawan->gaji_pokok) }}"
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium">Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $karyawan->tanggal_masuk?->toDateString()) }}" required
                        class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="aktif" value="0">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', $karyawan->aktif) ? 'checked' : '' }}> Aktif
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('karyawan.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleGaji() {
            const skema = document.getElementById('skema_gaji').value;
            document.getElementById('field-harian').classList.toggle('hidden', skema !== 'harian');
            document.getElementById('field-bulanan').classList.toggle('hidden', skema !== 'bulanan');
        }
        toggleGaji();
    </script>
</x-app-layout>
