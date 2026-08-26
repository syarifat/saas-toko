<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tagihan & Langganan') }}</h2>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Status langganan --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="font-semibold mb-1">Status Langganan: {{ $toko->nama }}</h3>
            <p class="text-sm text-gray-600">
                Paket aktif: <strong>{{ $toko->paket?->nama }}</strong> (Tier {{ $toko->paket?->tingkat }})
                · Status: <strong>{{ $toko->status }}</strong>
                · Berlaku s/d: <strong>{{ $toko->langganan_berakhir_pada?->format('d M Y') ?? '-' }}</strong>
                · Add-on: {{ $toko->addonAktif->pluck('addon.nama')->join(', ') ?: 'belum ada' }}
            </p>
        </div>

        {{-- Form pengajuan --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold mb-3">Ajukan Upgrade / Beli Add-on (Transfer Manual)</h3>
            <form action="{{ route('tagihan.ajukan') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="form-tagihan">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Jenis *</label>
                        <select name="jenis" id="jenis" onchange="tampilItem()" required class="mt-1 w-full rounded-md border-gray-300">
                            <option value="paket">Paket Langganan</option>
                            <option value="addon">Add-on</option>
                        </select>
                    </div>
                    <div id="wrap-paket">
                        <label class="block text-sm font-medium">Paket Tujuan *</label>
                        <select name="paket_id" id="paket_id" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($paket as $p)
                                <option value="{{ $p->id }}" data-harga="{{ $p->harga }}" {{ $toko->paket_id === $p->id ? 'selected' : '' }}>
                                    Tier {{ $p->tingkat }} — {{ $p->nama }} (Rp {{ number_format($p->harga, 0, ',', '.') }}/bln)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="wrap-addon" class="hidden">
                        <label class="block text-sm font-medium">Add-on *</label>
                        <select name="addon_id" id="addon_id" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($addon as $a)
                                <option value="{{ $a->id }}" data-harga="{{ $a->harga }}">{{ $a->nama }} (Rp {{ number_format($a->harga, 0, ',', '.') }}/bln)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Jumlah Bulan *</label>
                        <input type="number" name="jumlah_bulan" min="1" max="24" value="1" required oninput="hitungNominal()"
                            class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                </div>
                <p class="text-sm"><strong>Total: <span id="nominal-display">Rp 0</span></strong></p>
                <div>
                    <label class="block text-sm font-medium">Bukti Transfer * (foto, maks 4MB)</label>
                    <input type="file" name="bukti_transfer" accept="image/*" required class="mt-1 rounded-md border-gray-300">
                    <p class="text-xs text-gray-500 mt-1">Transfer ke rekening platform, lalu upload buktinya. Superadmin akan memverifikasi.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Catatan (opsional)</label>
                    <textarea name="catatan_tenant" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('catatan_tenant') }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 font-semibold">Kirim Pengajuan</button>
                </div>
            </form>
        </div>

        {{-- Riwayat --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <h3 class="font-semibold p-4 border-b">Riwayat Pengajuan</h3>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-right">Bulan</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Catatan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pembayaran as $pb)
                        <tr>
                            <td class="px-4 py-3">{{ $pb->id }}</td>
                            <td class="px-4 py-3">{{ $pb->labelItem() }}</td>
                            <td class="px-4 py-3 text-right">{{ $pb->jumlah_bulan }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($pb->nominal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $pb->status === 'disetujui' ? 'bg-green-100 text-green-800' : ($pb->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $pb->status }}
                                </span>
                                @if ($pb->bukti_transfer)
                                    <a href="{{ asset('storage/'.$pb->bukti_transfer) }}" target="_blank" class="ml-1 text-blue-600 hover:underline">bukti</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $pb->catatan_admin ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function tampilItem() {
            const jenis = document.getElementById('jenis').value;
            document.getElementById('wrap-paket').classList.toggle('hidden', jenis !== 'paket');
            document.getElementById('wrap-addon').classList.toggle('hidden', jenis !== 'addon');
            hitungNominal();
        }

        function hitungNominal() {
            const jenis = document.getElementById('jenis').value;
            const sel = document.getElementById(jenis === 'paket' ? 'paket_id' : 'addon_id');
            const harga = parseFloat(sel?.selectedOptions[0]?.dataset.harga || 0);
            const bulan = parseInt(document.querySelector('[name=jumlah_bulan]').value || 1);
            document.getElementById('nominal-display').textContent = 'Rp ' + (harga * bulan).toLocaleString('id-ID');
        }

        tampilItem();
    </script>
</x-app-layout>
