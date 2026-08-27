@extends('layouts.tenant')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Catat Penjualan Ringkas</h2>
            <p class="text-sm text-slate-500">Pilih produk dan masukkan kuantitas penjualan.</p>
        </div>
        <a href="{{ route('penjualan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('penjualan.store') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_penjualan" value="{{ old('tanggal_penjualan', date('Y-m-d')) }}" required
                       class="w-full text-sm border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan</label>
                <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Catatan transaksi..."
                       class="w-full text-sm border-slate-300 rounded-lg">
            </div>
        </div>

        <!-- Items Table -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Produk yang Terjual</h3>
                <button type="button" onclick="addRow()" class="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-xs font-bold">+ Tambah Baris</button>
            </div>

            <div class="space-y-2.5" id="items-wrapper">
                <div class="item-row grid grid-cols-12 gap-2 items-center bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <div class="col-span-6">
                        <select name="items[0][produk_id]" required onchange="setRowPrice(this)" class="w-full text-xs border-slate-300 rounded-lg produk-select">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}" data-harga="{{ (float)$p->harga_jual }}">{{ $p->nama }} (Rp {{ number_format($p->harga_jual, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="items[0][jumlah]" value="1" min="1" required placeholder="Qty" oninput="calcRow(this)"
                               class="w-full text-xs border-slate-300 rounded-lg qty-input">
                    </div>
                    <div class="col-span-3">
                        <input type="number" name="items[0][harga_satuan]" value="0" min="0" required placeholder="Harga" oninput="calcRow(this)"
                               class="w-full text-xs border-slate-300 rounded-lg harga-input">
                    </div>
                    <div class="col-span-1 text-center">
                        <button type="button" onclick="removeRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-xs">✕</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('penjualan.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                Simpan Penjualan
            </button>
        </div>
    </form>
</div>

<script>
let rowIndex = 1;
const produkOptions = `{!! collect($produks)->map(fn($p) => "<option value='{$p->id}' data-harga='{$p->harga_jual}'>{$p->nama} (Rp ".number_format($p->harga_jual, 0, ',', '.').")</option>")->join('') !!}`;

function addRow() {
    const wrapper = document.getElementById('items-wrapper');
    const div = document.createElement('div');
    div.className = 'item-row grid grid-cols-12 gap-2 items-center bg-slate-50 p-2.5 rounded-xl border border-slate-200';
    div.innerHTML = `
        <div class="col-span-6">
            <select name="items[${rowIndex}][produk_id]" required onchange="setRowPrice(this)" class="w-full text-xs border-slate-300 rounded-lg produk-select">
                <option value="">-- Pilih Produk --</option>
                ${produkOptions}
            </select>
        </div>
        <div class="col-span-2">
            <input type="number" name="items[${rowIndex}][jumlah]" value="1" min="1" required placeholder="Qty" oninput="calcRow(this)"
                   class="w-full text-xs border-slate-300 rounded-lg qty-input">
        </div>
        <div class="col-span-3">
            <input type="number" name="items[${rowIndex}][harga_satuan]" value="0" min="0" required placeholder="Harga" oninput="calcRow(this)"
                   class="w-full text-xs border-slate-300 rounded-lg harga-input">
        </div>
        <div class="col-span-1 text-center">
            <button type="button" onclick="removeRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-xs">✕</button>
        </div>
    `;
    wrapper.appendChild(div);
    rowIndex++;
}

function removeRow(btn) {
    const wrapper = document.getElementById('items-wrapper');
    if (wrapper.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
    }
}

function setRowPrice(select) {
    const row = select.closest('.item-row');
    const selectedOption = select.options[select.selectedIndex];
    const harga = selectedOption.dataset.harga || 0;
    row.querySelector('.harga-input').value = harga;
}

function calcRow(input) {
    // Dynamic calculation helper if needed
}
</script>
@endsection
