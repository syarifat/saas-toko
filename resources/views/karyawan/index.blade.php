<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Data Karyawan') }}</h2>
            <a href="{{ route('karyawan.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Karyawan Baru</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skema Gaji</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tarif/Gaji</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($karyawan as $k)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $k->kode_karyawan }}</td>
                            <td class="px-4 py-3 font-medium">{{ $k->nama }} {{ $k->aktif ? '' : '(nonaktif)' }}</td>
                            <td class="px-4 py-3">{{ $k->posisi ?? '-' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($k->skema_gaji) }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ $k->skema_gaji === 'harian'
                                    ? 'Rp '.number_format($k->tarif_harian, 0, ',', '.').'/hari'
                                    : 'Rp '.number_format($k->gaji_pokok, 0, ',', '.').'/bln' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('karyawan.edit', $k) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('karyawan.destroy', $k) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $karyawan->links() }}</div>
    </div>
</x-app-layout>
