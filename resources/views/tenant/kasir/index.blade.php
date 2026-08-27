@extends('layouts.tenant')

@section('content')
<div class="h-[calc(100vh-8rem)] flex flex-col lg:flex-row gap-6">
    <!-- Left Panel: Product Catalog (60-65%) -->
    <div class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col min-w-0 overflow-hidden">
        <!-- Top Toolbar: Search, Category & Warehouse -->
        <div class="p-4 border-b border-slate-100 space-y-3 shrink-0">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" id="pos-search" placeholder="Cari nama produk atau SKU (ketik cepat)..." autocomplete="off"
                           class="w-full pl-10 text-sm bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                @if($gudangs->count() > 1)
                    <div class="w-48 shrink-0">
                        <select id="pos-gudang-select" class="w-full text-xs font-semibold bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500">
                            @foreach($gudangs as $g)
                                <option value="{{ $g->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $g->nama }} ({{ ucfirst($g->jenis) }})</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" id="pos-gudang-select" value="{{ $gudangs->first()->id ?? 1 }}">
                @endif
            </div>

            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <button type="button" class="category-filter-btn px-3 py-1.5 rounded-lg font-semibold bg-indigo-600 text-white transition shrink-0" data-cat="all">Semua</button>
                @foreach($kategoris as $cat)
                    <button type="button" class="category-filter-btn px-3 py-1.5 rounded-lg font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 transition shrink-0" data-cat="{{ $cat->id }}">{{ $cat->nama }}</button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="flex-1 p-4 overflow-y-auto" id="pos-product-grid">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @foreach($produks as $produk)
                    @php
                        $stokTersedia = $produk->totalStok();
                    @endphp
                    <div class="product-card bg-slate-50 hover:bg-indigo-50/40 border border-slate-200 hover:border-indigo-300 rounded-xl p-3.5 flex flex-col justify-between transition cursor-pointer select-none group"
                         data-id="{{ $produk->id }}"
                         data-sku="{{ $produk->sku }}"
                         data-nama="{{ $produk->nama }}"
                         data-harga="{{ (float)$produk->harga_jual }}"
                         data-stok="{{ $stokTersedia }}"
                         data-cat="{{ $produk->kategori_id ?? 0 }}"
                         onclick="addToCart(this)">
                        <div>
                            <div class="flex items-start justify-between gap-1 mb-1">
                                <span class="text-[10px] font-mono text-slate-400">{{ $produk->sku }}</span>
                                <span class="stok-badge px-1.5 py-0.5 rounded text-[10px] font-bold {{ $stokTersedia <= $produk->stok_minimum ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    Stok: {{ $stokTersedia }}
                                </span>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 line-clamp-2 group-hover:text-indigo-600 transition">{{ $produk->nama }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $produk->kategori->nama ?? 'Tanpa Kategori' }}</p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
                            <span class="p-1 rounded bg-white shadow-sm text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Panel: Cart & Payment (35-40%) -->
    <div class="w-full lg:w-[420px] bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col shrink-0 overflow-hidden">
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50">
            <div>
                <h3 class="font-bold text-sm text-slate-800">Keranjang Kasir</h3>
                <p class="text-xs text-slate-500" id="cart-item-count">0 item dipilih</p>
            </div>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Kosongkan</button>
        </div>

        <!-- Cart Items List -->
        <div class="flex-1 p-4 overflow-y-auto space-y-2.5 divide-y divide-slate-100" id="cart-items-container">
            <div id="empty-cart-notice" class="h-full flex flex-col items-center justify-center text-slate-400 py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                <p class="text-xs font-semibold">Keranjang masih kosong</p>
                <p class="text-[11px]">Klik produk di sebelah kiri untuk menambahkan ke keranjang.</p>
            </div>
        </div>

        <!-- Payment & Summary Footer -->
        <form method="POST" action="{{ route('kasir.store') }}" id="pos-checkout-form" class="p-4 border-t border-slate-200 bg-slate-50/70 space-y-3 shrink-0">
            @csrf
            <input type="hidden" name="gudang_id" id="form-gudang-id">
            <input type="hidden" name="cart_data" id="form-cart-data">

            <!-- Subtotal & Diskon -->
            <div class="space-y-1.5 text-xs text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900" id="display-subtotal">Rp 0</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span>Diskon (Rp):</span>
                    <input type="number" name="diskon" id="input-diskon" value="0" min="0" step="500"
                           class="w-28 text-right text-xs py-1 px-2 border-slate-200 rounded-lg focus:ring-indigo-500" oninput="calculateTotal()">
                </div>
                <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Tagihan:</span>
                    <span class="text-indigo-600" id="display-total">Rp 0</span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="pt-2 border-t border-slate-200">
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer font-semibold has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-600 has-[:checked]:text-indigo-700">
                        <input type="radio" name="metode_pembayaran" value="tunai" checked class="hidden" onchange="toggleMetode('tunai')"> Tunai
                    </label>
                    <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer font-semibold has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-600 has-[:checked]:text-indigo-700">
                        <input type="radio" name="metode_pembayaran" value="qris" class="hidden" onchange="toggleMetode('qris')"> QRIS
                    </label>
                    <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer font-semibold has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-600 has-[:checked]:text-indigo-700">
                        <input type="radio" name="metode_pembayaran" value="transfer" class="hidden" onchange="toggleMetode('transfer')"> Transfer
                    </label>
                </div>
            </div>

            <!-- Section Tunai: Quick Cash Presets & Kembalian -->
            <div id="section-tunai" class="space-y-2">
                <div class="flex items-center gap-1.5 overflow-x-auto text-[11px]">
                    <button type="button" onclick="setCash('pas')" class="px-2 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-100">Uang Pas</button>
                    <button type="button" onclick="setCash(20000)" class="px-2 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-100">20k</button>
                    <button type="button" onclick="setCash(50000)" class="px-2 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-100">50k</button>
                    <button type="button" onclick="setCash(100000)" class="px-2 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-100">100k</button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Jumlah Bayar (Rp)</label>
                        <input type="number" name="jumlah_bayar" id="input-bayar" value="0" min="0" step="500" required
                               class="w-full text-sm font-bold text-slate-900 border-slate-200 rounded-lg focus:ring-indigo-500" oninput="calculateKembalian()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Kembalian</label>
                        <div class="w-full text-sm font-extrabold text-emerald-600 bg-slate-100 p-2 rounded-lg truncate" id="display-kembalian">
                            Rp 0
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" onclick="submitPOS()" id="btn-submit-pos" disabled
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                <span>Proses Transaksi & Simpan</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
        </form>
    </div>
</div>

<script>
let cart = []; // [{id, sku, nama, harga, stok, qty}]

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function addToCart(el) {
    const id = parseInt(el.dataset.id);
    const sku = el.dataset.sku;
    const nama = el.dataset.nama;
    const harga = parseFloat(el.dataset.harga);
    const stok = parseInt(el.dataset.stok);

    if (stok <= 0) {
        alert(`Stok produk [${nama}] habis.`);
        return;
    }

    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty + 1 > stok) {
            alert(`Jumlah tidak dapat melebihi sisa stok (${stok}).`);
            return;
        }
        existing.qty += 1;
    } else {
        cart.push({ id, sku, nama, harga, stok, qty: 1 });
    }

    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;

    const newQty = item.qty + delta;
    if (newQty <= 0) {
        cart = cart.filter(i => i.id !== id);
    } else if (newQty > item.stok) {
        alert(`Jumlah tidak dapat melebihi sisa stok (${item.stok}).`);
        return;
    } else {
        item.qty = newQty;
    }
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items-container');
    const countEl = document.getElementById('cart-item-count');
    const submitBtn = document.getElementById('btn-submit-pos');

    if (cart.length === 0) {
        container.innerHTML = `
            <div id="empty-cart-notice" class="h-full flex flex-col items-center justify-center text-slate-400 py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                <p class="text-xs font-semibold">Keranjang masih kosong</p>
                <p class="text-[11px]">Klik produk di sebelah kiri untuk menambahkan ke keranjang.</p>
            </div>
        `;
        countEl.innerText = '0 item dipilih';
        submitBtn.disabled = true;
        calculateTotal();
        return;
    }

    submitBtn.disabled = false;
    let totalItems = 0;
    let html = '';

    cart.forEach(item => {
        totalItems += item.qty;
        const subtotal = item.qty * item.harga;
        html += `
            <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3 text-xs">
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-900 truncate">${item.nama}</p>
                    <p class="text-[11px] text-slate-500">${formatRupiah(item.harga)} x ${item.qty} = <span class="font-bold text-slate-800">${formatRupiah(subtotal)}</span></p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="updateQty(${item.id}, -1)" class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">-</button>
                    <span class="w-7 text-center font-bold text-slate-900">${item.qty}</span>
                    <button type="button" onclick="updateQty(${item.id}, 1)" class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">+</button>
                    <button type="button" onclick="removeItem(${item.id})" class="ml-1 text-rose-500 hover:text-rose-700 p-1">✕</button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    countEl.innerText = `${totalItems} item (${cart.length} produk)`;
    calculateTotal();
}

function calculateTotal() {
    const subtotal = cart.reduce((acc, i) => acc + (i.qty * i.harga), 0);
    const diskon = parseFloat(document.getElementById('input-diskon').value) || 0;
    const total = Math.max(0, subtotal - diskon);

    document.getElementById('display-subtotal').innerText = formatRupiah(subtotal);
    document.getElementById('display-total').innerText = formatRupiah(total);

    calculateKembalian();
}

function toggleMetode(metode) {
    const secTunai = document.getElementById('section-tunai');
    const inputBayar = document.getElementById('input-bayar');
    if (metode === 'tunai') {
        secTunai.style.display = 'block';
    } else {
        secTunai.style.display = 'none';
        const subtotal = cart.reduce((acc, i) => acc + (i.qty * i.harga), 0);
        const diskon = parseFloat(document.getElementById('input-diskon').value) || 0;
        inputBayar.value = Math.max(0, subtotal - diskon);
    }
    calculateKembalian();
}

function setCash(val) {
    const subtotal = cart.reduce((acc, i) => acc + (i.qty * i.harga), 0);
    const diskon = parseFloat(document.getElementById('input-diskon').value) || 0;
    const total = Math.max(0, subtotal - diskon);

    if (val === 'pas') {
        document.getElementById('input-bayar').value = total;
    } else {
        document.getElementById('input-bayar').value = val;
    }
    calculateKembalian();
}

function calculateKembalian() {
    const subtotal = cart.reduce((acc, i) => acc + (i.qty * i.harga), 0);
    const diskon = parseFloat(document.getElementById('input-diskon').value) || 0;
    const total = Math.max(0, subtotal - diskon);
    const bayar = parseFloat(document.getElementById('input-bayar').value) || 0;
    const kembalian = Math.max(0, bayar - total);

    document.getElementById('display-kembalian').innerText = formatRupiah(kembalian);
}

function submitPOS() {
    if (cart.length === 0) return;

    const subtotal = cart.reduce((acc, i) => acc + (i.qty * i.harga), 0);
    const diskon = parseFloat(document.getElementById('input-diskon').value) || 0;
    const total = Math.max(0, subtotal - diskon);
    const bayar = parseFloat(document.getElementById('input-bayar').value) || 0;

    const metode = document.querySelector('input[name="metode_pembayaran"]:checked').value;
    if (metode === 'tunai' && bayar < total) {
        alert('Jumlah bayar tunai kurang dari total tagihan.');
        return;
    }

    const gudangSelect = document.getElementById('pos-gudang-select');
    document.getElementById('form-gudang-id').value = gudangSelect ? gudangSelect.value : 1;
    document.getElementById('form-cart-data').value = JSON.stringify(cart);

    document.getElementById('pos-checkout-form').submit();
}

// Client-side search and category filtering
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('pos-search');
    const catBtns = document.querySelectorAll('.category-filter-btn');
    const cards = document.querySelectorAll('.product-card');

    let currentCat = 'all';

    function filterCards() {
        const query = searchInput.value.toLowerCase().trim();

        cards.forEach(card => {
            const nama = card.dataset.nama.toLowerCase();
            const sku = card.dataset.sku.toLowerCase();
            const cat = card.dataset.cat;

            const matchSearch = nama.includes(query) || sku.includes(query);
            const matchCat = currentCat === 'all' || cat === currentCat;

            if (matchSearch && matchCat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterCards);

    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            catBtns.forEach(b => {
                b.classList.remove('bg-indigo-600', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            btn.classList.remove('bg-slate-100', 'text-slate-700');
            btn.classList.add('bg-indigo-600', 'text-white');

            currentCat = btn.dataset.cat;
            filterCards();
        });
    });
});
</script>
@endsection
