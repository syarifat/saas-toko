@extends('layouts.tenant')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rincian Penggajian #{{ $penggajian->id }}</h2>
            <p class="text-xs text-slate-500">Karyawan: {{ $penggajian->karyawan->pengguna->nama ?? '-' }} • Periode: {{ $penggajian->periode_mulai->format('d M Y') }} s/d {{ $penggajian->periode_selesai->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('penggajian.slip', $penggajian) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl">Lihat Slip Gaji</a>
            <a href="{{ route('penggajian.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
        </div>
    </div>

    <!-- Breakdown Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase">Status Pembayaran</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold block mt-1 {{ $penggajian->status === 'dibayar' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ ucfirst($penggajian->status) }}
                </span>
            </div>
            @if($penggajian->status === 'draf')
                <form method="POST" action="{{ route('penggajian.bayar', $penggajian) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Tandai gaji ini sudah dibayarkan lunas?')"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        ✓ Bayar & Lunaskan Gaji
                    </button>
                </form>
            @endif
        </div>

        <!-- Components Breakdown -->
        <div class="space-y-4">
            <h3 class="font-bold text-sm text-slate-800">Komponen Penambahan & Pemotongan Gaji</h3>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">Komponen</th>
                        <th class="py-2.5 px-4">Jenis</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($penggajian->komponen as $k)
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $k->nama }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $k->jenis === 'tunjangan' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $k->jenis }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $k->jenis === 'tunjangan' ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $k->jenis === 'tunjangan' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-200 font-bold">
                    <tr>
                        <td colspan="2" class="py-3 px-4 text-slate-900 text-sm">TOTAL GAJI BERSIH (TAKE HOME PAY):</td>
                        <td class="py-3 px-4 text-right text-base text-indigo-700 font-black">Rp {{ number_format($penggajian->gaji_bersih, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
