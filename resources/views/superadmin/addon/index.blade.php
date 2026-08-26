<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Master Add-on') }}</h2>
            <a href="{{ route('superadmin.addon.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Add-on Baru</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga/bln</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($addon as $a)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $a->kode }}</td>
                            <td class="px-4 py-3 font-medium">{{ $a->nama }} {{ $a->aktif ? '' : '(nonaktif)' }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($a->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('superadmin.addon.edit', $a) }}" class="text-blue-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada add-on.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
