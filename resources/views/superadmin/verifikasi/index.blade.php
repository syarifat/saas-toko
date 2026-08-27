@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Verifikasi Pembayaran & Langganan</h2>
            <p class="text-sm text-slate-500">Tinjau bukti transfer manual untuk upgrade paket atau aktivasi modul toko.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex border-b border-slate-200 gap-6 text-sm font-semibold">
        <a href="{{ route('superadmin.verifikasi.index', ['status' => 'menunggu']) }}" 
           class="pb-3 border-b-2 transition {{ $status === 'menunggu' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Menunggu Approval ({{ $menungguCount }})
        </a>
        <a href="{{ route('superadmin.verifikasi.index', ['status' => 'disetujui']) }}" 
           class="pb-3 border-b-2 transition {{ $status === 'disetujui' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Disetujui ({{ $disetujuiCount }})
        </a>
        <a href="{{ route('superadmin.verifikasi.index', ['status' => 'ditolak']) }}" 
           class="pb-3 border-b-2 transition {{ $status === 'ditolak' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Ditolak ({{ $ditolakCount }})
        </a>
        <a href="{{ route('superadmin.verifikasi.index', ['status' => 'semua']) }}" 
           class="pb-3 border-b-2 transition {{ $status === 'semua' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Semua Riwayat
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Toko</th>
                        <th class="px-6 py-3.5">Jenis Transaksi</th>
                        <th class="px-6 py-3.5">Nominal</th>
                        <th class="px-6 py-3.5">Bukti Transfer</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembayarans as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $p->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                <a href="{{ route('superadmin.toko.show', $p->toko) }}" class="hover:text-indigo-600">{{ $p->toko->nama ?? 'Toko' }}</a>
                            </td>
                            <td class="px-6 py-4">
                                @if($p->jenis === 'upgrade_paket')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                        Upgrade: {{ $p->paket->nama ?? '-' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        Addon: {{ $p->modul->nama ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <a href="{{ asset('storage/'.$p->bukti_transfer) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Lihat Struk
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $p->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                                @if($p->catatan_penolakan)
                                    <p class="text-[11px] text-rose-600 mt-1 italic">Alasan: {{ $p->catatan_penolakan }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($p->status === 'menunggu')
                                    <form method="POST" action="{{ route('superadmin.verifikasi.setujui', $p) }}" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Setujui pembayaran ini? Layanan toko akan otomatis diperbarui.')"
                                                class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                                            Setujui ✓
                                        </button>
                                    </form>

                                    <!-- Tombol Tolak memicu prompt catatan -->
                                    <button type="button" onclick="tolakPembayaran({{ $p->id }})"
                                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                        Tolak ✗
                                    </button>

                                    <form id="form-tolak-{{ $p->id }}" method="POST" action="{{ route('superadmin.verifikasi.tolak', $p) }}" class="hidden">
                                        @csrf
                                        <input type="hidden" name="catatan_penolakan" id="input-catatan-{{ $p->id }}">
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">Tidak ada data pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pembayarans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $pembayarans->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function tolakPembayaran(id) {
    const alasan = prompt('Masukkan alasan penolakan pembayaran:');
    if (alasan && alasan.trim() !== '') {
        document.getElementById(`input-catatan-${id}`).value = alasan.trim();
        document.getElementById(`form-tolak-${id}`).submit();
    }
}
</script>
@endsection
