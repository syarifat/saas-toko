<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Penggajian</h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
            @include('partials.errors')

            <form action="{{ route('penggajian.store') }}" method="POST" class="space-y-4" id="form-gaji">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Karyawan *</label>
                    <select name="karyawan_id" id="karyawan" required onchange="tampilInfo()" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($karyawan as $k)
                            <option value="{{ $k->id }}" data-skema="{{ $k->skema_gaji }}"
                                data-harian="{{ $k->tarif_harian }}" data-pokok="{{ $k->gaji_pokok }}">
                                {{ $k->nama }} — {{ ucfirst($k->skema_gaji) }}
                            </option>
                        @endforeach
                    </select>
                    @if ($karyawan->isEmpty())
                        <p class="text-xs text-red-600 mt-1">Belum ada karyawan aktif. <a href="{{ route('karyawan.create') }}" class="underline">Tambah dulu.</a></p>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Periode Mulai *</label>
                        <input type="date" name="periode_mulai" value="{{ old('periode_mulai', now()->startOfMonth()->toDateString()) }}" required
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Periode Selesai *</label>
                        <input type="date" name="periode_selesai" value="{{ old('periode_selesai', now()->toDateString()) }}" required
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                </div>

                <p id="info-skema" class="text-xs text-gray-500"></p>

                <h3 class="font-semibold text-sm border-t pt-4">Tunjangan & Potongan</h3>

                {{-- Tunjangan --}}
                <div class="space-y-2" id="daftar-komponen">
                    <div class="komponen-row grid grid-cols-12 gap-2 items-center">
                        <select name="komponen[0][jenis]" class="col-span-3 rounded-md border-gray-300 text-sm">
                            <option value="tunjangan">+ Tunjangan</option>
                            <option value="potongan">− Potongan</option>
                        </select>
                        <input type="text" name="komponen[0][nama]" placeholder="misal: Uang makan" class="col-span-6 rounded-md border-gray-300 text-sm">
                        <input type="number" name="komponen[0][nominal]" placeholder="Nominal" min="0" step="1" class="col-span-3 rounded-md border-gray-300 text-sm">
                    </div>
                </div>
                <button type="button" onclick="tambahKomponen()" class="text-sm text-blue-600 hover:underline">+ Tambah baris</button>

                <p class="text-xs text-gray-500">
                    Gaji dasar dihitung otomatis: skema harian = tarif × jumlah hadir (dari absensi); skema bulanan = gaji pokok tetap.
                </p>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('penggajian.index') }}" class="px-4 py-2 rounded-md border">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let baris = 1;
        function tambahKomponen() {
            const row = document.createElement('div');
            row.className = 'komponen-row grid grid-cols-12 gap-2 items-center mt-2';
            row.innerHTML = `
                <select name="komponen[${baris}][jenis]" class="col-span-3 rounded-md border-gray-300 text-sm">
                    <option value="tunjangan">+ Tunjangan</option>
                    <option value="potongan">− Potongan</option>
                </select>
                <input type="text" name="komponen[${baris}][nama]" placeholder="misal: Kasbon, Telat" class="col-span-6 rounded-md border-gray-300 text-sm">
                <input type="number" name="komponen[${baris}][nominal]" placeholder="Nominal" min="0" step="1" class="col-span-3 rounded-md border-gray-300 text-sm">
            `;
            document.getElementById('daftar-komponen').appendChild(row);
            baris++;
        }

        function tampilInfo() {
            const sel = document.getElementById('karyawan');
            const opt = sel.options[sel.selectedIndex];
            if (!opt) return;
            const skema = opt.dataset.skema === 'harian'
                ? 'Harian: Rp ' + Number(opt.dataset.harian).toLocaleString('id-ID') + ' × jumlah hari hadir'
                : 'Bulanan: gaji pokok Rp ' + Number(opt.dataset.pokok).toLocaleString('id-ID');
            document.getElementById('info-skema').textContent = skema;
        }
        tampilInfo();
    </script>
</x-app-layout>
