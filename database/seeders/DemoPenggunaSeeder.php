<?php

namespace Database\Seeders;

use App\Models\Gudang;
use App\Models\ItemPenjualanSederhana;
use App\Models\ItemTransaksi;
use App\Models\Karyawan;
use App\Models\Kategori;
use App\Models\Paket;
use App\Models\Pemasok;
use App\Models\Pengeluaran;
use App\Models\Pengguna;
use App\Models\PenjualanSederhana;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\StokGudang;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Services\ModulService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $modulService = app(ModulService::class);
        $paket1 = Paket::where('jenis', 'preset_1')->first();
        $paket2 = Paket::where('jenis', 'preset_2')->first();
        $paket3 = Paket::where('jenis', 'preset_3')->first();

        // ═════════════════════════════════════════════════════════════════════
        // 1. TOKO PAKET 1 — BASIC / CASHBOOK: "Toko Kelontong Berkah"
        // ═════════════════════════════════════════════════════════════════════
        $toko1 = Toko::updateOrCreate(
            ['slug' => 'kelontong-berkah'],
            [
                'nama' => 'Toko Kelontong Berkah',
                'paket_id' => $paket1?->id,
                'status' => 'aktif',
                'garis_lintang' => -6.175392,
                'garis_bujur' => 106.827153,
                'radius_absensi' => 100,
                'langganan_berakhir_pada' => now()->addMonths(6),
            ]
        );
        if ($paket1) {
            $modulService->pakaiPreset($toko1, $paket1);
        }

        $adminP1 = Pengguna::updateOrCreate(
            ['email' => 'admin.paket1@gmail.com'],
            [
                'nama' => 'Siti Aminah',
                'password' => Hash::make('password'),
                'peran' => 'admin',
                'sub_peran' => null,
                'toko_id' => $toko1->id,
                'aktif' => true,
            ]
        );

        // Kategori & Produk Toko 1
        $katSembako = Kategori::firstOrCreate(['toko_id' => $toko1->id, 'nama' => 'Sembako']);
        $katMinuman = Kategori::firstOrCreate(['toko_id' => $toko1->id, 'nama' => 'Minuman']);

        $p1Items = [
            ['nama' => 'Beras Rojolele 5kg', 'kategori_id' => $katSembako->id, 'harga_beli' => 60000, 'harga_jual' => 72000, 'sku' => 'BRS-01'],
            ['nama' => 'Minyak Goreng 2L', 'kategori_id' => $katSembako->id, 'harga_beli' => 28000, 'harga_jual' => 34000, 'sku' => 'MYK-01'],
            ['nama' => 'Gula Pasir 1kg', 'kategori_id' => $katSembako->id, 'harga_beli' => 14000, 'harga_jual' => 17500, 'sku' => 'GLA-01'],
            ['nama' => 'Telur Ayam 1kg', 'kategori_id' => $katSembako->id, 'harga_beli' => 24000, 'harga_jual' => 29000, 'sku' => 'TLR-01'],
            ['nama' => 'Air Mineral Botol 600ml', 'kategori_id' => $katMinuman->id, 'harga_beli' => 2500, 'harga_jual' => 4000, 'sku' => 'AIR-01'],
        ];

        $produkP1List = [];
        foreach ($p1Items as $item) {
            $produkP1List[] = Produk::firstOrCreate(
                ['toko_id' => $toko1->id, 'sku' => $item['sku']],
                [
                    'kategori_id' => $item['kategori_id'],
                    'nama' => $item['nama'],
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'stok_minimum' => 5,
                ]
            );
        }

        // Data 30 Hari Penjualan Sederhana & Pengeluaran Toko 1
        for ($i = 30; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);

            $penjualan = PenjualanSederhana::firstOrCreate(
                ['toko_id' => $toko1->id, 'tanggal_penjualan' => $tgl->toDateString()],
                [
                    'pengguna_id' => $adminP1->id,
                    'total' => 0,
                    'catatan' => 'Rekap penjualan harian toko kelontong',
                ]
            );

            $totalHarian = 0;
            if ($penjualan->items()->count() === 0) {
                foreach ($produkP1List as $prod) {
                    $qty = rand(2, 6);
                    $sub = $qty * $prod->harga_jual;
                    $totalHarian += $sub;

                    ItemPenjualanSederhana::create([
                        'toko_id' => $toko1->id,
                        'penjualan_sederhana_id' => $penjualan->id,
                        'produk_id' => $prod->id,
                        'nama_produk' => $prod->nama,
                        'jumlah' => $qty,
                        'harga_satuan' => $prod->harga_jual,
                        'subtotal' => $sub,
                        'harga_beli_snapshot' => $prod->harga_beli,
                    ]);
                }
                $penjualan->update(['total' => $totalHarian]);
            }

            if ($i % 5 === 0) {
                Pengeluaran::firstOrCreate(
                    ['toko_id' => $toko1->id, 'tanggal_pengeluaran' => $tgl->toDateString(), 'keterangan' => 'Kulakan Barang Dagangan'],
                    [
                        'pengguna_id' => $adminP1->id,
                        'nominal' => rand(400000, 800000),
                    ]
                );
            }
            if ($i % 10 === 0) {
                Pengeluaran::firstOrCreate(
                    ['toko_id' => $toko1->id, 'tanggal_pengeluaran' => $tgl->toDateString(), 'keterangan' => 'Listrik & Kebersihan Toko'],
                    [
                        'pengguna_id' => $adminP1->id,
                        'nominal' => 150000,
                    ]
                );
            }
        }

        // ═════════════════════════════════════════════════════════════════════
        // 2. TOKO PAKET 2 — PRO / POS & STOK: "Kedai Kopi Senja"
        // ═════════════════════════════════════════════════════════════════════
        $toko2 = Toko::updateOrCreate(
            ['slug' => 'kopi-senja'],
            [
                'nama' => 'Kedai Kopi Senja',
                'paket_id' => $paket2?->id,
                'status' => 'aktif',
                'garis_lintang' => -6.200000,
                'garis_bujur' => 106.816666,
                'radius_absensi' => 150,
                'langganan_berakhir_pada' => now()->addMonths(8),
            ]
        );
        if ($paket2) {
            $modulService->pakaiPreset($toko2, $paket2);
        }

        $gudangP2 = Gudang::firstOrCreate(
            ['toko_id' => $toko2->id, 'nama' => 'Etalase Bar Utama'],
            ['jenis' => 'etalase']
        );

        // Akun Toko 2: Admin, Kasir 1, Kasir 2
        $adminP2 = Pengguna::updateOrCreate(
            ['email' => 'admin.paket2@gmail.com'],
            [
                'nama' => 'Budi Setiawan',
                'password' => Hash::make('password'),
                'peran' => 'admin',
                'sub_peran' => null,
                'toko_id' => $toko2->id,
                'aktif' => true,
            ]
        );

        $kasir1P2 = Pengguna::updateOrCreate(
            ['email' => 'kasir1.paket2@gmail.com'],
            [
                'nama' => 'Rina (Kasir Shift Pagi)',
                'password' => Hash::make('password'),
                'peran' => 'karyawan',
                'sub_peran' => 'kasir',
                'toko_id' => $toko2->id,
                'aktif' => true,
            ]
        );
        Karyawan::updateOrCreate(
            ['toko_id' => $toko2->id, 'pengguna_id' => $kasir1P2->id],
            ['kode_karyawan' => 'KSR-01', 'posisi' => 'Kasir Pagi', 'tanggal_masuk' => now()->subMonths(3), 'aktif' => true]
        );

        $kasir2P2 = Pengguna::updateOrCreate(
            ['email' => 'kasir2.paket2@gmail.com'],
            [
                'nama' => 'Doni (Kasir Shift Malam)',
                'password' => Hash::make('password'),
                'peran' => 'karyawan',
                'sub_peran' => 'kasir',
                'toko_id' => $toko2->id,
                'aktif' => true,
            ]
        );
        Karyawan::updateOrCreate(
            ['toko_id' => $toko2->id, 'pengguna_id' => $kasir2P2->id],
            ['kode_karyawan' => 'KSR-02', 'posisi' => 'Kasir Malam', 'tanggal_masuk' => now()->subMonths(2), 'aktif' => true]
        );

        // Kategori & Produk Toko 2
        $katCoffee = Kategori::firstOrCreate(['toko_id' => $toko2->id, 'nama' => 'Coffee Series']);
        $katNonCoffee = Kategori::firstOrCreate(['toko_id' => $toko2->id, 'nama' => 'Non-Coffee']);
        $katSnack = Kategori::firstOrCreate(['toko_id' => $toko2->id, 'nama' => 'Pastry & Snack']);

        $p2Items = [
            ['nama' => 'Espresso Double', 'kategori_id' => $katCoffee->id, 'harga_beli' => 6000, 'harga_jual' => 18000, 'sku' => 'COF-01', 'stok' => 150],
            ['nama' => 'Caffe Latte 350ml', 'kategori_id' => $katCoffee->id, 'harga_beli' => 9000, 'harga_jual' => 28000, 'sku' => 'COF-02', 'stok' => 120],
            ['nama' => 'Caramel Macchiato', 'kategori_id' => $katCoffee->id, 'harga_beli' => 11000, 'harga_jual' => 32000, 'sku' => 'COF-03', 'stok' => 90],
            ['nama' => 'Matcha Ice Fusion', 'kategori_id' => $katNonCoffee->id, 'harga_beli' => 10000, 'harga_jual' => 26000, 'sku' => 'NCF-01', 'stok' => 80],
            ['nama' => 'Croissant Butter Warm', 'kategori_id' => $katSnack->id, 'harga_beli' => 8000, 'harga_jual' => 22000, 'sku' => 'SNK-01', 'stok' => 45],
            ['nama' => 'French Fries Original', 'kategori_id' => $katSnack->id, 'harga_beli' => 7000, 'harga_jual' => 20000, 'sku' => 'SNK-02', 'stok' => 60],
        ];

        $produkP2List = [];
        foreach ($p2Items as $item) {
            $prod = Produk::firstOrCreate(
                ['toko_id' => $toko2->id, 'sku' => $item['sku']],
                [
                    'kategori_id' => $item['kategori_id'],
                    'nama' => $item['nama'],
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'stok_minimum' => 10,
                ]
            );

            StokGudang::updateOrCreate(
                ['toko_id' => $toko2->id, 'gudang_id' => $gudangP2->id, 'produk_id' => $prod->id],
                ['jumlah' => $item['stok']]
            );

            $produkP2List[] = $prod;
        }

        // Data 30 Hari Transaksi POS Kasir 1 & 2
        for ($i = 30; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);

            foreach ([$kasir1P2, $kasir2P2] as $idx => $kasirUser) {
                $trxTime = $tgl->copy()->setTime($idx === 0 ? rand(9, 14) : rand(16, 21), rand(0, 59));

                $subtotal = 0;
                $itemsToBuy = [];
                $sampleProducts = collect($produkP2List)->random(rand(2, 3));

                foreach ($sampleProducts as $p) {
                    $qty = rand(1, 2);
                    $sub = $qty * $p->harga_jual;
                    $subtotal += $sub;
                    $itemsToBuy[] = [
                        'produk' => $p,
                        'jumlah' => $qty,
                        'harga' => $p->harga_jual,
                        'subtotal' => $sub,
                    ];
                }

                $trx = Transaksi::create([
                    'toko_id' => $toko2->id,
                    'pengguna_id' => $kasirUser->id,
                    'gudang_id' => $gudangP2->id,
                    'tanggal_transaksi' => $tgl->toDateString(),
                    'subtotal' => $subtotal,
                    'diskon' => 0,
                    'total' => $subtotal,
                    'jumlah_bayar' => $subtotal + (rand(0, 2) * 10000),
                    'kembalian' => (rand(0, 2) * 10000),
                    'metode_pembayaran' => rand(0, 1) ? 'qris' : 'tunai',
                    'created_at' => $trxTime,
                    'updated_at' => $trxTime,
                ]);

                foreach ($itemsToBuy as $it) {
                    ItemTransaksi::create([
                        'toko_id' => $toko2->id,
                        'transaksi_id' => $trx->id,
                        'produk_id' => $it['produk']->id,
                        'nama_produk' => $it['produk']->nama,
                        'jumlah' => $it['jumlah'],
                        'harga_satuan' => $it['harga'],
                        'subtotal' => $it['subtotal'],
                        'harga_beli_snapshot' => $it['produk']->harga_beli,
                    ]);

                    PergerakanStok::create([
                        'toko_id' => $toko2->id,
                        'produk_id' => $it['produk']->id,
                        'gudang_id' => $gudangP2->id,
                        'jenis' => 'penjualan',
                        'jumlah' => -$it['jumlah'],
                        'referensi_type' => Transaksi::class,
                        'referensi_id' => $trx->id,
                        'catatan' => 'Penjualan Kasir POS #'.$trx->id,
                        'created_at' => $trxTime,
                        'updated_at' => $trxTime,
                    ]);
                }
            }

            if ($i % 7 === 0) {
                Pengeluaran::create([
                    'toko_id' => $toko2->id,
                    'pengguna_id' => $adminP2->id,
                    'tanggal_pengeluaran' => $tgl->toDateString(),
                    'keterangan' => 'Restock Fresh Milk 20L & Es Kristal',
                    'nominal' => 450000,
                    'created_at' => $tgl->copy()->setTime(8, 0),
                ]);
            }
        }

        // ═════════════════════════════════════════════════════════════════════
        // 3. TOKO PAKET 3 — ENTERPRISE / MULTI-GUDANG: "Sentosa Mart & Rantai Pasok"
        // ═════════════════════════════════════════════════════════════════════
        $toko3 = Toko::updateOrCreate(
            ['slug' => 'sentosa-mart'],
            [
                'nama' => 'Sentosa Mart & Rantai Pasok',
                'paket_id' => $paket3?->id,
                'status' => 'aktif',
                'garis_lintang' => -6.214620,
                'garis_bujur' => 106.845130,
                'radius_absensi' => 200,
                'langganan_berakhir_pada' => now()->addYear(),
            ]
        );
        if ($paket3) {
            $modulService->pakaiPreset($toko3, $paket3);
        }

        $gudangEtalaseP3 = Gudang::firstOrCreate(
            ['toko_id' => $toko3->id, 'nama' => 'Etalase Kasir Utama'],
            ['jenis' => 'etalase']
        );
        $gudangPusatP3 = Gudang::firstOrCreate(
            ['toko_id' => $toko3->id, 'nama' => 'Gudang Pusat Cikarang'],
            ['jenis' => 'gudang']
        );

        // Akun Toko 3: Admin, 2 Kasir, 1 Gudang
        $adminP3 = Pengguna::updateOrCreate(
            ['email' => 'admin.paket3@gmail.com'],
            [
                'nama' => 'Hendra Wijaya',
                'password' => Hash::make('password'),
                'peran' => 'admin',
                'sub_peran' => null,
                'toko_id' => $toko3->id,
                'aktif' => true,
            ]
        );

        $kasir1P3 = Pengguna::updateOrCreate(
            ['email' => 'kasir1.paket3@gmail.com'],
            [
                'nama' => 'Maya (Kasir Shift 1)',
                'password' => Hash::make('password'),
                'peran' => 'karyawan',
                'sub_peran' => 'kasir',
                'toko_id' => $toko3->id,
                'aktif' => true,
            ]
        );
        Karyawan::updateOrCreate(
            ['toko_id' => $toko3->id, 'pengguna_id' => $kasir1P3->id],
            ['kode_karyawan' => 'M-KSR-01', 'posisi' => 'Kasir Shift 1', 'tanggal_masuk' => now()->subMonths(6), 'aktif' => true]
        );

        $kasir2P3 = Pengguna::updateOrCreate(
            ['email' => 'kasir2.paket3@gmail.com'],
            [
                'nama' => 'Aldi (Kasir Shift 2)',
                'password' => Hash::make('password'),
                'peran' => 'karyawan',
                'sub_peran' => 'kasir',
                'toko_id' => $toko3->id,
                'aktif' => true,
            ]
        );
        Karyawan::updateOrCreate(
            ['toko_id' => $toko3->id, 'pengguna_id' => $kasir2P3->id],
            ['kode_karyawan' => 'M-KSR-02', 'posisi' => 'Kasir Shift 2', 'tanggal_masuk' => now()->subMonths(4), 'aktif' => true]
        );

        $gudangP3User = Pengguna::updateOrCreate(
            ['email' => 'gudang.paket3@gmail.com'],
            [
                'nama' => 'Agus Logistik',
                'password' => Hash::make('password'),
                'peran' => 'karyawan',
                'sub_peran' => 'gudang',
                'toko_id' => $toko3->id,
                'aktif' => true,
            ]
        );
        Karyawan::updateOrCreate(
            ['toko_id' => $toko3->id, 'pengguna_id' => $gudangP3User->id],
            ['kode_karyawan' => 'LOG-01', 'posisi' => 'Kepala Logistik & Gudang', 'tanggal_masuk' => now()->subMonths(5), 'aktif' => true]
        );

        // Pemasok & Kategori Toko 3
        $pemasok1 = Pemasok::firstOrCreate(
            ['toko_id' => $toko3->id, 'nama' => 'PT Indofood Distribusi Utama'],
            ['telepon' => '08123456789', 'alamat' => 'Kawasan Industri MM2100']
        );
        $pemasok2 = Pemasok::firstOrCreate(
            ['toko_id' => $toko3->id, 'nama' => 'CV Sumber Rejeki Pangan'],
            ['telepon' => '08198765432', 'alamat' => 'Sentra Pergudangan Marunda']
        );

        $katSembakoP3 = Kategori::firstOrCreate(['toko_id' => $toko3->id, 'nama' => 'Pangan & Sembako']);
        $katFMCG = Kategori::firstOrCreate(['toko_id' => $toko3->id, 'nama' => 'FMCG & Minuman']);

        $p3Items = [
            ['nama' => 'Beras Pandan Wangi 10kg', 'kategori_id' => $katSembakoP3->id, 'pemasok_id' => $pemasok2->id, 'harga_beli' => 135000, 'harga_jual' => 160000, 'sku' => 'STR-01', 'stok_pusat' => 200, 'stok_etalase' => 25],
            ['nama' => 'Minyak Goreng Premium 2L', 'kategori_id' => $katSembakoP3->id, 'pemasok_id' => $pemasok1->id, 'harga_beli' => 30000, 'harga_jual' => 37000, 'sku' => 'STR-02', 'stok_pusat' => 300, 'stok_etalase' => 40],
            ['nama' => 'Gula Pasir Kristal 1kg', 'kategori_id' => $katSembakoP3->id, 'pemasok_id' => $pemasok2->id, 'harga_beli' => 14500, 'harga_jual' => 18000, 'sku' => 'STR-03', 'stok_pusat' => 250, 'stok_etalase' => 50],
            ['nama' => 'Susu UHT Full Cream 1L', 'kategori_id' => $katFMCG->id, 'pemasok_id' => $pemasok1->id, 'harga_beli' => 15500, 'harga_jual' => 20000, 'sku' => 'STR-04', 'stok_pusat' => 180, 'stok_etalase' => 30],
            ['nama' => 'Teh Kemasan Botol 450ml', 'kategori_id' => $katFMCG->id, 'pemasok_id' => $pemasok1->id, 'harga_beli' => 4000, 'harga_jual' => 6500, 'sku' => 'STR-05', 'stok_pusat' => 400, 'stok_etalase' => 60],
        ];

        $produkP3List = [];
        foreach ($p3Items as $item) {
            $prod = Produk::firstOrCreate(
                ['toko_id' => $toko3->id, 'sku' => $item['sku']],
                [
                    'kategori_id' => $item['kategori_id'],
                    'pemasok_id' => $item['pemasok_id'],
                    'nama' => $item['nama'],
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'stok_minimum' => 15,
                ]
            );

            StokGudang::updateOrCreate(
                ['toko_id' => $toko3->id, 'gudang_id' => $gudangPusatP3->id, 'produk_id' => $prod->id],
                ['jumlah' => $item['stok_pusat']]
            );

            StokGudang::updateOrCreate(
                ['toko_id' => $toko3->id, 'gudang_id' => $gudangEtalaseP3->id, 'produk_id' => $prod->id],
                ['jumlah' => $item['stok_etalase']]
            );

            $produkP3List[] = $prod;
        }

        // Data 30 Hari: Restock ke Gudang Pusat + Transfer ke Etalase + Transaksi Kasir
        for ($i = 30; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);

            if ($i % 6 === 0) {
                foreach ($produkP3List as $prod) {
                    $qtyMasuk = rand(25, 50);
                    PergerakanStok::create([
                        'toko_id' => $toko3->id,
                        'produk_id' => $prod->id,
                        'gudang_id' => $gudangPusatP3->id,
                        'jenis' => 'masuk',
                        'jumlah' => $qtyMasuk,
                        'catatan' => 'Penerimaan restock supplier #PO-'.($i + 100),
                        'created_at' => $tgl->copy()->setTime(7, 30),
                        'updated_at' => $tgl->copy()->setTime(7, 30),
                    ]);
                }
            }

            if ($i % 3 === 0) {
                foreach ($produkP3List as $prod) {
                    $qtyTransfer = rand(8, 15);
                    PergerakanStok::create([
                        'toko_id' => $toko3->id,
                        'produk_id' => $prod->id,
                        'gudang_id' => $gudangPusatP3->id,
                        'gudang_tujuan_id' => $gudangEtalaseP3->id,
                        'jenis' => 'transfer',
                        'jumlah' => $qtyTransfer,
                        'catatan' => 'Transfer restock etalase kasir',
                        'created_at' => $tgl->copy()->setTime(8, 30),
                        'updated_at' => $tgl->copy()->setTime(8, 30),
                    ]);
                }
            }

            foreach ([$kasir1P3, $kasir2P3] as $idx => $kasirUser) {
                $trxTime = $tgl->copy()->setTime($idx === 0 ? rand(8, 14) : rand(15, 22), rand(0, 59));

                $subtotal = 0;
                $itemsToBuy = [];
                $sampleProducts = collect($produkP3List)->random(rand(2, 4));

                foreach ($sampleProducts as $p) {
                    $qty = rand(1, 3);
                    $sub = $qty * $p->harga_jual;
                    $subtotal += $sub;
                    $itemsToBuy[] = [
                        'produk' => $p,
                        'jumlah' => $qty,
                        'harga' => $p->harga_jual,
                        'subtotal' => $sub,
                    ];
                }

                $trx = Transaksi::create([
                    'toko_id' => $toko3->id,
                    'pengguna_id' => $kasirUser->id,
                    'gudang_id' => $gudangEtalaseP3->id,
                    'tanggal_transaksi' => $tgl->toDateString(),
                    'subtotal' => $subtotal,
                    'diskon' => 0,
                    'total' => $subtotal,
                    'jumlah_bayar' => $subtotal,
                    'kembalian' => 0,
                    'metode_pembayaran' => rand(0, 1) ? 'qris' : 'transfer',
                    'created_at' => $trxTime,
                    'updated_at' => $trxTime,
                ]);

                foreach ($itemsToBuy as $it) {
                    ItemTransaksi::create([
                        'toko_id' => $toko3->id,
                        'transaksi_id' => $trx->id,
                        'produk_id' => $it['produk']->id,
                        'nama_produk' => $it['produk']->nama,
                        'jumlah' => $it['jumlah'],
                        'harga_satuan' => $it['harga'],
                        'subtotal' => $it['subtotal'],
                        'harga_beli_snapshot' => $it['produk']->harga_beli,
                    ]);

                    PergerakanStok::create([
                        'toko_id' => $toko3->id,
                        'produk_id' => $it['produk']->id,
                        'gudang_id' => $gudangEtalaseP3->id,
                        'jenis' => 'penjualan',
                        'jumlah' => -$it['jumlah'],
                        'referensi_type' => Transaksi::class,
                        'referensi_id' => $trx->id,
                        'catatan' => 'Penjualan Supermarket POS #'.$trx->id,
                        'created_at' => $trxTime,
                        'updated_at' => $trxTime,
                    ]);
                }
            }

            if ($i % 10 === 0) {
                Pengeluaran::create([
                    'toko_id' => $toko3->id,
                    'pengguna_id' => $adminP3->id,
                    'tanggal_pengeluaran' => $tgl->toDateString(),
                    'keterangan' => 'Biaya Operasional Gudang & Logistik',
                    'nominal' => rand(750000, 1500000),
                    'created_at' => $tgl->copy()->setTime(9, 0),
                ]);
            }
        }
    }
}
