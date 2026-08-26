<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kasir POS') }}</h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('kasir.store') }}" method="POST">
            @csrf
            <input type="hidden" name="gudang_id" value="{{ $gudang->id }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Daftar produk --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau SKU..."
                                class="flex-1 rounded-md border-gray-300">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Cari</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Produk</th>
                                    <th class="px-4 py-2 text-right">Harga</th>
                                    <th class="px-4 py-2 text-right">Stok</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse ($produk as $p)
                                    @php($stok = $p->stokGudang->firstWhere('gudang_id', $gudang->id)?->jumlah ?? 0)
                                    <tr>
                                        <td class="px-4 py-2">
                                            {{ $p->nama }}
                                            <span class="text-xs text-gray-400 ml-1">{{ $p->sku }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right {{ $stok <= $p->stok_minimum ? 'text-red-600 font-semibold' : '' }}">{{ $stok }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" onclick="tambahKeKeranjang('{{ $p->id }}', '{{ addslashes($p->nama) }}', '{{ $p->harga_jual }}', {{ $stok }})"
                                                {{ $stok <= 0 ? 'disabled' :'' }}
                                                class="px-3 py-1 bg-indigo-600 text-white rounded text-xs disabled:opacity-40 hover:bg-indigo-500">+ Masukkan</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada produk.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Riwayat terakhir --}}
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="font-semibold text-sm mb-2">5 Transaksi Terakhir</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            @forelse ($riwayat as $t)
                                <li class="flex justify-between border-b pb-1">
                                    <span>#{{ $t->id }} — {{ $t->tanggal_transaksi->format('d M Y H:i') }}</span>
                                    <span class="font-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li>Belum ada transaksi.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Keranjang --}}
                <div class="bg-white p-4 rounded-lg shadow h-fit sticky top-20">
                    <h3 class="font-semibold mb-3">Keranjang</h3>
                    <div id="keranjang-list" class="space-y-2 text-sm"></div>

                    <div class="mt-4 border-t pt-3 space-y-2">
                        <div class="flex justify-between font-bold text-base">
                            <span>Total</span>
                            <span id="total-display">Rp 0</span>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Diskon (Rp)</label>
                            <input type="number" name="diskon" id="diskon" min="0" value="0" oninput="hitungTotal()"
                                class="w-full mt-1 rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="w-full mt-1 rounded-md border-gray-300">
                                <option value="tunai">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Jumlah Bayar (Rp)</label>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" min="0" step="1" oninput="hitungTotal()"
                                required class="w-full mt-1 rounded-md border-gray-300">
                        </div>
                        <div class="flex justify-between text-sm text-green-700">
                            <span>Kembalian</span>
                            <span id="kembalian-display">Rp 0</span>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 font-semibold">
                            Bayar & Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let keranjang = {};

        function tambahKeKeranjang(id, nama, harga, stokTersedia) {
            if (!keranjang[id]) {
                keranjang[id] = { nama, harga: parseFloat(harga), jumlah: 0, stokTersedia };
            }
            if (keranjang[id].jumlah + 1 > stokTersedia) {
                alert('Stok tidak cukup untuk ' + nama);
                return;
            }
            keranjang[id].jumlah++;
            render();
        }

        function ubahJumlah(id, delta) {
            keranjang[id].jumlah += delta;
            if (keranjang[id].jumlah > keranjang[id].stokTersedia) {
                keranjang[id].jumlah = keranjang[id].stokTersedia;
            }
            if (keranjang[id].jumlah <= 0) delete keranjang[id];
            render();
        }

        function total() {
            return Object.values(keranjang).reduce((sum, i) => sum + i.harga * i.jumlah, 0);
        }

        function hitungTotal() {
            const diskon = parseFloat(document.getElementById('diskon').value || 0);
            const bayar = parseFloat(document.getElementById('jumlah_bayar').value || 0);
            const total = Math.max(total() - diskon, 0);
            document.getElementById('total-display').textContent = 'Rp ' + total().toLocaleString('id-ID');
            document.getElementById('kembalian-display').textContent = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
        }

        function render() {
            const list = document.getElementById('keranjang-list');
            list.innerHTML = Object.entries(keranjang).map(([id, i]) => `
                <div class="flex items-center justify-between gap-1">
                    <div class="flex-1 truncate">${i.nama}</div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="ubahJumlah('${id}', -1)" class="px-2 bg-gray-200 rounded">−</button>
                        <span>${i.jumlah}</span>
                        <button type="button" onclick="ubahJumlah('${id}', 1)" class="px-2 bg-gray-200 rounded">+</button>
                    </div>
                    <div class="w-24 text-right">${(i.harga * i.jumlah).toLocaleString('id-ID')}</div>
                </div>
                <input type="hidden" name="barang[][produk_id]" value="${id}">
                <input type="hidden" name="barang[][jumlah]" value="${i.jumlah}">
            `).join('');
            hitungTotal();
        }
    </script>
</x-app-layout>
