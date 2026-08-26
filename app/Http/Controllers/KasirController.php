<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\ItemTransaksi;
use App\Models\Produk;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(Request $request): View
    {
        $toko = $request->user()->toko;

        return view('kasir.index', [
            'produk' => Produk::with('stokGudang')
                ->when($request->filled('cari'), fn ($q) => $q->where(fn ($qq) => $qq
                    ->where('nama', 'like', '%'.$request->cari.'%')
                    ->orWhere('sku', 'like', '%'.$request->cari.'%')))
                ->orderBy('nama')
                ->get(),
            'gudang' => $toko->gudangUtama(),
            'riwayat' => $toko->transaksi()->with('item')->latest()->take(5)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gudang_id' => ['required', 'exists:gudang,id'],
            'metode_pembayaran' => ['required', 'in:tunai,qris,transfer'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.produk_id' => ['required', 'exists:produk,id'],
            'barang.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $gudang = Gudang::where('toko_id', $user->toko_id)->findOrFail($data['gudang_id']);
        $stokService = app(StokService::class);

        try {
            $transaksi = DB::transaction(function () use ($data, $user, $gudang, $stokService) {
                // Kunci & validasi semua produk dulu
                $barisProduk = [];
                $subtotal = 0;

                foreach ($data['barang'] as $baris) {
                    $produk = Produk::where('toko_id', $user->toko_id)->lockForUpdate()->findOrFail($baris['produk_id']);
                    $jumlah = (int) $baris['jumlah'];
                    $lineSubtotal = (float) $produk->harga_jual * $jumlah;
                    $subtotal += $lineSubtotal;

                    $barisProduk[] = ['produk' => $produk, 'jumlah' => $jumlah, 'lineSubtotal' => $lineSubtotal];
                }

                $diskon = min((float) ($data['diskon'] ?? 0), $subtotal);
                $total = $subtotal - $diskon;

                if ((float) $data['jumlah_bayar'] < $total) {
                    throw new \DomainException('Jumlah bayar kurang dari total belanja.');
                }

                $transaksi = $user->toko->transaksi()->create([
                    'pengguna_id' => $user->id,
                    'gudang_id' => $gudang->id,
                    'tanggal_transaksi' => now()->toDateString(),
                    'subtotal' => $subtotal,
                    'diskon' => $diskon,
                    'total' => $total,
                    'jumlah_bayar' => $data['jumlah_bayar'],
                    'kembalian' => (float) $data['jumlah_bayar'] - $total,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                ]);

                foreach ($barisProduk as $b) {
                    ItemTransaksi::create([
                        'toko_id' => $user->toko_id,
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $b['produk']->id,
                        'nama_produk' => $b['produk']->nama,
                        'jumlah' => $b['jumlah'],
                        'harga_satuan' => $b['produk']->harga_jual,
                        'subtotal' => $b['lineSubtotal'],
                        'harga_beli_snapshot' => $b['produk']->harga_beli,
                    ]);

                    $stokService->deductUntukTransaksi($b['produk'], $gudang, $b['jumlah'], $transaksi, $user->id);
                }

                return $transaksi;
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['stok' => $e->getMessage()])->withInput();
        }

        return redirect()->route('kasir.index')->with('status', 'Transaksi #'.$transaksi->id.' berhasil. Kembalian Rp '.number_format((float) $transaksi->kembalian, 0, ',', '.'));
    }
}
