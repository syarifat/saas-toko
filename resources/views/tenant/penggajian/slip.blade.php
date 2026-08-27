@extends('layouts.tenant')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('penggajian.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Kembali</a>
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            Cetak Slip Gaji
        </button>
    </div>

    <!-- Official Payslip Box -->
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm text-slate-800 text-xs space-y-6">
        <div class="text-center pb-4 border-b border-slate-200 space-y-1">
            <h2 class="font-black text-base text-slate-900 uppercase">{{ $penggajian->toko->nama ?? 'SaaS Toko' }}</h2>
            <p class="text-xs text-slate-500 font-semibold tracking-wider uppercase">SLIP PEMBAYARAN GAJI KARYAWAN</p>
            <p class="text-[11px] text-slate-400">Periode: {{ $penggajian->periode_mulai->format('d M Y') }} - {{ $penggajian->periode_selesai->format('d M Y') }}</p>
        </div>

        <!-- Employee Info -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <p class="text-slate-400 font-semibold">Nama Karyawan:</p>
                <p class="font-bold text-slate-900 text-sm">{{ $penggajian->karyawan->pengguna->nama ?? '-' }}</p>
                <p class="text-[11px] text-slate-500">NIK: {{ $penggajian->karyawan->kode_karyawan ?? '-' }}</p>
            </div>
            <div class="text-right">
                <p class="text-slate-400 font-semibold">Jabatan / Skema:</p>
                <p class="font-bold text-slate-900">{{ $penggajian->karyawan->posisi ?? 'Staf' }}</p>
                <p class="text-[11px] text-slate-500 uppercase">{{ $penggajian->skema_gaji_snapshot }}</p>
            </div>
        </div>

        <!-- Table of earnings & deductions -->
        <div class="space-y-4">
            <div>
                <p class="font-bold text-slate-800 uppercase text-[11px] mb-1.5 text-emerald-800">A. Penghasilan & Tunjangan</p>
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($penggajian->komponen->where('jenis', 'tunjangan') as $t)
                        <div class="py-1.5 flex justify-between">
                            <span>{{ $t->nama }}</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($t->nominal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($penggajian->komponen->where('jenis', 'potongan')->isNotEmpty())
                <div>
                    <p class="font-bold text-slate-800 uppercase text-[11px] mb-1.5 text-rose-800">B. Potongan / Denda</p>
                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach($penggajian->komponen->where('jenis', 'potongan') as $p)
                            <div class="py-1.5 flex justify-between text-rose-600">
                                <span>{{ $p->nama }}</span>
                                <span class="font-bold">- Rp {{ number_format($p->nominal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Net Total -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-sm font-bold">
                <span class="text-slate-900">GAJI BERSIH DITERIMA:</span>
                <span class="text-base text-indigo-700 font-black">Rp {{ number_format($penggajian->gaji_bersih, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 grid grid-cols-2 text-center text-xs text-slate-500 pt-6">
            <div>
                <p>Penerima,</p>
                <div class="h-14"></div>
                <p class="font-bold text-slate-900">({{ $penggajian->karyawan->pengguna->nama ?? 'Karyawan' }})</p>
            </div>
            <div>
                <p>Manajemen Toko,</p>
                <div class="h-14"></div>
                <p class="font-bold text-slate-900">({{ $penggajian->toko->nama ?? 'Admin' }})</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .max-w-xl, .max-w-xl * { visibility: visible; }
    .no-print { display: none !important; }
    .max-w-xl { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
@endsection
