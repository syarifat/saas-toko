<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Rekap Keuangan') }}</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="flex gap-2 mb-4">
            @foreach (['harian', 'mingguan', 'bulanan'] as $p)
                <a href="{{ route('rekap.index', ['periode' => $p]) }}"
                    class="px-4 py-2 rounded-md text-sm {{ $periode === $p ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
                    {{ ucfirst($p) }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Uang Masuk (penjualan)</p>
                <p class="text-2xl font-bold text-green-700">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Uang Keluar</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Laba Kotor</p>
                <p class="text-2xl font-bold {{ $labaKotor >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    Rp {{ number_format($labaKotor, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Masuk</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Keluar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rekap as $r)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $r['label'] }}</td>
                            <td class="px-4 py-3 text-right text-green-700">Rp {{ number_format($r['masuk'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format($r['keluar'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $r['laba'] >= 0 ? '' : 'text-red-600' }}">
                                Rp {{ number_format($r['laba'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada data pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
