<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kelola Toko') }}</h2>
            <a href="{{ route('superadmin.toko.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">+ Toko Baru</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Langganan s/d</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($toko as $t)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $t->nama }}</td>
                            <td class="px-4 py-3">{{ $t->paket?->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs
                                    {{ $t->status === 'aktif' ? 'bg-green-100 text-green-800' : ($t->status === 'nonaktif' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $t->admin?->email ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $t->langganan_berakhir_pada?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('superadmin.toko.edit', $t) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('superadmin.toko.destroy', $t) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus toko ini beserta seluruh datanya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        @if ($t->addonAktif->isNotEmpty() || true)
                            <tr class="bg-gray-50 text-sm text-gray-600">
                                <td colspan="4" class="px-4 py-2">
                                    Add-on:
                                    @foreach (\App\Models\Addon::all() as $addon)
                                        <form action="{{ route('superadmin.toko.addon.toggle', [$t, $addon]) }}" method="POST" class="inline ml-2">
                                            @csrf
                                            <button type="submit"
                                                class="px-2 py-0.5 rounded-full text-xs border {{ $t->punyaAddon($addon->kode) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300' }}">
                                                {{ $addon->nama }}
                                            </button>
                                        </form>
                                    @endforeach
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada toko terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $toko->links() }}</div>
    </div>
</x-app-layout>
