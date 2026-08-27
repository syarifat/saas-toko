<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\ItemTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Services\StokService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $gudangs = Gudang::where('toko_id', $user->toko_id)->get();

        if ($gudangs->isEmpty()) {
            $gudangs = collect([
                Gudang::create([
                    'toko_id' => $user->toko_id,
                    'nama' => 'Etalase Utama',
                    'jenis' => 'etalase',
                ]),
            ]);
        }

        $produks = Produk::with(['kategori', 'stokGudang'])
            ->where('toko_id', $user->toko_id)
            ->get();

        $kategoris = Kategori::where('toko_id', $user->toko_id)->get();

        return view('tenant.kasir.index', compact('produks', 'kategoris', 'gudangs'));
    }

    public function cariProduk(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = $request->get('q', '');

        $produks = Produk::with(['kategori', 'stokGudang'])
            ->where('toko_id', $user->toko_id)
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->take(20)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'nama' => $p->nama,
                'harga' => (float) $p->harga_jual,
                'stok' => $p->totalStok(),
                'kategori' => $p->kategori->nama ?? '-',
            ]);

        return response()->json($produks);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'gudang_id' => ['required', 'exists:gudang,id'],
            'cart_data' => ['required', 'string'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'metode_pembayaran' => ['required', 'in:tunai,qris,transfer'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
        ]);

        $cartItems = json_decode($validated['cart_data'], true);

        if (empty($cartItems) || ! is_array($cartItems)) {
            return back()->with('error', 'Keranjang kasir tidak boleh kosong.');
        }

        $gudang = Gudang::findOrFail($validated['gudang_id']);

        try {
            $transaksi = DB::transaction(function () use ($validated, $cartItems, $gudang, $user) {
                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($cartItems as $item) {
                    $produk = Produk::where('toko_id', $user->toko_id)
                        ->where('id', $item['id'])
                        ->firstOrFail();

                    $qty = (int) $item['qty'];
                    $itemSubtotal = $qty * (float) $produk->harga_jual;
                    $subtotal += $itemSubtotal;

                    $itemsToCreate[] = [
                        'produk' => $produk,
                        'qty' => $qty,
                        'harga_satuan' => (float) $produk->harga_jual,
                        'subtotal' => $itemSubtotal,
                        'harga_beli_snapshot' => (float) $produk->harga_beli,
                    ];
                }

                $diskon = (float) ($validated['diskon'] ?? 0);
                $total = max(0, $subtotal - $diskon);
                $jumlahBayar = (float) $validated['jumlah_bayar'];

                if ($validated['metode_pembayaran'] === 'tunai' && $jumlahBayar < $total) {
                    throw new Exception('Jumlah pembayaran tunai kurang dari total tagihan.');
                }

                $kembalian = max(0, $jumlahBayar - $total);

                $transaksi = Transaksi::create([
                    'toko_id' => $user->toko_id,
                    'pengguna_id' => $user->id,
                    'gudang_id' => $gudang->id,
                    'tanggal_transaksi' => today(),
                    'subtotal' => $subtotal,
                    'diskon' => $diskon,
                    'total' => $total,
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                ]);

                $stokService = app(StokService::class);

                foreach ($itemsToCreate as $itemData) {
                    ItemTransaksi::create([
                        'toko_id' => $user->toko_id,
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $itemData['produk']->id,
                        'nama_produk' => $itemData['produk']->nama,
                        'jumlah' => $itemData['qty'],
                        'harga_satuan' => $itemData['harga_satuan'],
                        'subtotal' => $itemData['subtotal'],
                        'harga_beli_snapshot' => $itemData['harga_beli_snapshot'],
                    ]);

                    // Potong stok gudang
                    $stokService->kurangiStok(
                        $itemData['produk'],
                        $gudang,
                        $itemData['qty'],
                        Transaksi::class,
                        $transaksi->id,
                        "Penjualan Kasir POS #{$transaksi->id}"
                    );
                }

                return $transaksi;
            });

            return redirect()->route('kasir.show', $transaksi)
                ->with('success', "Transaksi #{$transaksi->id} berhasil disimpan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses transaksi: '.$e->getMessage());
        }
    }

    public function riwayat(Request $request): View
    {
        $query = Transaksi::with(['pengguna', 'gudang', 'items']);

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        $transaksis = $query->latest()->paginate(15)->withQueryString();

        return view('tenant.kasir.riwayat', compact('transaksis'));
    }

    public function show(Transaksi $transaksi): View
    {
        $transaksi->load(['pengguna', 'gudang', 'items.produk', 'toko']);

        return view('tenant.kasir.show', compact('transaksi'));
    }
}
