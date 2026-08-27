@extends('layouts.tenant')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Paket Langganan & Addon Modul</h2>
        <p class="text-sm text-slate-500">Kelola status langganan toko, perpanjang masa aktif, atau aktifkan fitur tambahan.</p>
    </div>

    <!-- Current Plan Card -->
    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white p-6 rounded-2xl shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-indigo-500/30 border border-indigo-400/40 text-indigo-200">
                    Paket Saat Ini
                </span>
                <h3 class="text-2xl font-black mt-2">{{ $toko->paket->nama ?? 'Custom Plan' }}</h3>
                <p class="text-xs text-indigo-200 mt-1">Rp {{ number_format($toko->paket->harga ?? 0, 0, ',', '.') }} / bulan</p>
            </div>
            <div class="sm:text-right">
                <span class="text-xs text-indigo-200 block">Masa Aktif Langganan</span>
                <p class="text-xl font-bold text-emerald-400 mt-0.5">
                    {{ $toko->langganan_berakhir_pada ? $toko->langganan_berakhir_pada->format('d M Y') : 'Aktif (Tak Terbatas)' }}
                </p>
                @if($toko->langganan_berakhir_pada)
                    <p class="text-[11px] text-indigo-300">
                        {{ now()->diffInDays($toko->langganan_berakhir_pada, false) >= 0 ? 'Tersisa '.(int)now()->diffInDays($toko->langganan_berakhir_pada, false).' hari lagi' : 'Langganan kadaluarsa' }}
                    </p>
                @endif
            </div>
        </div>

        <div class="pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-300">
            <span>{{ $toko->modulToko()->where('aktif', true)->count() }} dari 16 Modul Platform Aktif</span>
            <button type="button" onclick="bukaModalBayar('upgrade_paket', {{ $toko->paket_id ?? 1 }}, 'Perpanjangan Paket: {{ $toko->paket->nama ?? '' }}', {{ $toko->paket->harga ?? 0 }})"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow transition">
                Perpanjang Masa Aktif →
            </button>
        </div>
    </div>

    <!-- Upgrade Plans Grid -->
    <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-800">Pilihan Upgrade Paket</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($pakets as $p)
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">{{ str_replace('_', ' ', strtoupper($p->jenis)) }}</span>
                        <div>
                            <h4 class="font-bold text-lg text-slate-900">{{ $p->nama }}</h4>
                            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($p->harga, 0, ',', '.') }}<span class="text-xs font-normal text-slate-500">/bln</span></p>
                        </div>
                        @if($p->deskripsi)
                            <p class="text-xs text-slate-500">{{ $p->deskripsi }}</p>
                        @endif
                        <div class="pt-3 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-700 mb-1.5">{{ $p->modul->count() }} Modul Termasuk:</p>
                            <div class="flex flex-wrap gap-1 max-h-28 overflow-y-auto">
                                @foreach($p->modul as $m)
                                    <span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700">{{ $m->nama }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="bukaModalBayar('upgrade_paket', {{ $p->id }}, 'Upgrade: {{ $p->nama }}', {{ $p->harga }})"
                            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        Pilih Paket Ini →
                    </button>
                </div>
            @empty
                <div class="col-span-3 p-6 text-center text-xs text-slate-400 bg-white rounded-2xl border border-slate-200">
                    Anda saat ini sudah menggunakan paket tertinggi.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Standalone Addon Modules -->
    @if($addonModuls->isNotEmpty())
        <div class="space-y-4">
            <h3 class="text-base font-bold text-slate-800">Addon Fitur Tambahan (A la Carte)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($addonModuls as $addon)
                    @php $addonHarga = 49000; @endphp
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h5 class="font-bold text-xs text-slate-900 truncate">{{ $addon->nama }}</h5>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $addon->kode }}</p>
                            <p class="text-xs font-extrabold text-indigo-600 mt-1">Rp {{ number_format($addonHarga, 0, ',', '.') }}</p>
                        </div>
                        <button type="button" onclick="bukaModalBayar('aktivasi_addon', null, 'Aktivasi Addon: {{ $addon->nama }}', {{ $addonHarga }}, {{ $addon->id }})"
                                class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg shrink-0">
                            Beli +
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Payment History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Pengajuan Pembayaran & Langganan</h3>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Jenis Permintaan</th>
                    <th class="px-6 py-3.5">Nominal</th>
                    <th class="px-6 py-3.5">Bukti Transfer</th>
                    <th class="px-6 py-3.5 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayatPembayaran as $pay)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs text-slate-500">{{ $pay->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-900">
                            {{ $pay->jenis === 'upgrade_paket' ? 'Upgrade: '.($pay->paket->nama ?? '-') : 'Addon: '.($pay->modul->nama ?? '-') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 text-xs">Rp {{ number_format($pay->jumlah, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-xs">
                            <a href="{{ asset('storage/'.$pay->bukti_transfer) }}" target="_blank" class="text-indigo-600 underline">Lihat Struk</a>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $pay->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : ($pay->status === 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ ucfirst($pay->status) }}
                            </span>
                            @if($pay->catatan_penolakan)
                                <p class="text-[10px] text-rose-600 mt-0.5 italic">Alasan: {{ $pay->catatan_penolakan }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">Belum ada riwayat transaksi pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Konfirmasi Transfer -->
<div id="modal-bayar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-900" id="modal-title">Konfirmasi Pembayaran</h3>
            <button type="button" onclick="tutupModalBayar()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <!-- Rekening Transfer -->
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
            <p class="font-bold text-slate-800">Transfer Manual ke Rekening Resmi:</p>
            <p class="font-mono text-slate-700">Bank BCA: <span class="font-bold">123-456-7890</span> (PT SaaS Toko Indonesia)</p>
            <p class="text-[11px] text-slate-500">Nominal yang harus ditransfer: <span class="font-extrabold text-indigo-700 text-sm block" id="modal-amount">Rp 0</span></p>
        </div>

        <form method="POST" action="{{ route('tagihan.ajukan') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="jenis" id="modal-input-jenis">
            <input type="hidden" name="paket_id" id="modal-input-paket-id">
            <input type="hidden" name="modul_id" id="modal-input-modul-id">
            <input type="hidden" name="jumlah" id="modal-input-jumlah">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Foto Bukti Transfer <span class="text-rose-500">*</span></label>
                <input type="file" name="bukti_transfer" accept="image/*" required
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBayar()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow transition">
                    Kirim Bukti Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function bukaModalBayar(jenis, paketId, title, jumlah, modulId = null) {
    document.getElementById('modal-title').innerText = title;
    document.getElementById('modal-amount').innerText = formatRupiah(jumlah);
    document.getElementById('modal-input-jenis').value = jenis;
    document.getElementById('modal-input-paket-id').value = paketId || '';
    document.getElementById('modal-input-modul-id').value = modulId || '';
    document.getElementById('modal-input-jumlah').value = jumlah;
    document.getElementById('modal-bayar').classList.remove('hidden');
}

function tutupModalBayar() {
    document.getElementById('modal-bayar').classList.add('hidden');
}
</script>
@endsection
