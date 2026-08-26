<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Rekap Kehadiran') }}</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-500">Dari</label>
                    <input type="date" name="mulai" value="{{ $mulai }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Sampai</label>
                    <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filter</button>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Skema</th>
                        <th class="px-4 py-3 text-right">Hadir</th>
                        <th class="px-4 py-3 text-right">Telat</th>
                        <th class="px-4 py-3 text-right">Total Lembur (mnt)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rekap as $k)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $k->nama }}</td>
                            <td class="px-4 py-3">{{ ucfirst($k->skema_gaji) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $k->hadir_count }} hari</td>
                            <td class="px-4 py-3 text-right {{ $k->telat_count > 0 ? 'text-yellow-700' : '' }}">{{ $k->telat_count }}x</td>
                            <td class="px-4 py-3 text-right">{{ (int) ($k->total_lembur ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
