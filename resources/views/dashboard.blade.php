<x-app-layout>
        <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-900 leading-tight">
            {{ __('Dashboard') }} {{ auth()->user()->toko ? '— '.auth()->user()->toko->nama : '' }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (auth()->user()->toko)
            <div class="rounded-lg p-4 text-sm font-medium {{ auth()->user()->toko->langganan_berakhir_pada && auth()->user()->toko->langganan_berakhir_pada->isFuture() ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}">
                Langganan {{ auth()->user()->toko->paket?->nama }} —
                @if (auth()->user()->toko->langganan_berakhir_pada && auth()->user()->toko->langganan_berakhir_pada->isFuture())
                    aktif s/d {{ auth()->user()->toko->langganan_berakhir_pada->format('d M Y') }}
                @else
                    belum aktif / sudah berakhir
                @endif
                @if (auth()->user()->peran === 'admin')
                    · <a href="{{ route('tagihan.index') }}" class="underline">Kelola langganan</a>
                @endif
            </div>
        @endif

        @if (auth()->user()->toko?->setidaknyaPaket(2))
            @php($produkMenipis = auth()->user()->toko->produk()
                ->with('stokGudang')
                ->get()
                ->filter(fn ($p) => $p->totalStok() <= $p->stok_minimum))

            @if ($produkMenipis->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">⚠ Stok Menipis</h3>
                    <ul class="text-sm text-yellow-800 space-y-1">
                        @foreach ($produkMenipis as $p)
                            <li>{{ $p->nama }} ({{ $p->sku }}) — sisa {{ $p->totalStok() }}, minimum {{ $p->stok_minimum }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <div class="card">
            <div class="p-6 text-slate-900 space-y-3">
                <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>!</p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('pengeluaran.create') }}" class="px-4 py-2 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-500">+ Catat Pengeluaran</a>
                    <a href="{{ route('penjualan-sederhana.create') }}" class="px-4 py-2 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-500">+ Catat Penjualan</a>
                    <a href="{{ route('rekap.index') }}" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-brand-700 hover:bg-brand-50">Lihat Rekap</a>
                    @if (auth()->user()->toko?->setidaknyaPaket(2))
                        <a href="{{ route('kasir.index') }}" class="px-4 py-2 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-500">Buka Kasir POS</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
