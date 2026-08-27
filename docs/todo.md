# Todo List Implementasi — Mega System SaaS Toko

## Fase 1 — Fondasi & Multi-Tenancy ✅
- [x] Install Laravel Boost (`composer require laravel/boost --dev && php artisan boost:install`)
- [x] Install Laravel Breeze (Blade + Tailwind)
- [x] Migration inti: `toko`, `paket`, `addon`, `addon_toko`; ubah `pengguna` (peran, sub_peran, toko_id, aktif, dibuat_oleh)
- [x] Model + relasi: Toko, Paket, Addon, Pengguna
- [x] Trait `BelongsToToko` (global scope by toko_id)
- [x] Middleware: `peran`, `konteks_toko`, `paket`, `addon`
- [x] Helper `Toko::setidaknyaPaket()`, `Toko::punyaAddon()`
- [x] Seeder: superadmin default + 3 master paket + 2 add-on
- [x] Panel Superadmin: CRUD toko + admin toko, CRUD paket & add-on, toggle add-on per toko
- [x] Flow registrasi toko oleh superadmin (auto-buat user admin)

## Fase 2 — Fitur Paket 1 (Basic Cashbook & Sales) ✅
- [x] Migration: `pengeluaran`, `penjualan_sederhana`, `item_penjualan_sederhana`
- [x] CRUD pengeluaran + upload struk
- [x] Form pencatatan penjualan ringkas + item dinamis
- [x] Halaman rekap harian/mingguan/bulanan + laba kotor sederhana

## Fase 3 — Fitur Paket 2 (POS & Stock Management) ✅
- [x] Migration: `kategori`, `pemasok`, `produk`, `gudang` (default etalase), `stok_gudang`, `pergerakan_stok`, `transaksi`, `item_transaksi`
- [x] CRUD produk/kategori/pemasok (gated tier ≥ 2)
- [x] UI kasir: cari produk, keranjang, bayar, kembalian
- [x] Service `StokService`: deduct stok + tulis `pergerakan_stok` (DB transaction)
- [x] Stock alert di dashboard (produk < stok_minimum)
- [x] Stok opname/adjustment
- [x] Laporan laba per produk (pakai harga_beli_snapshot)

## Fase 4 — Fitur Paket 3 (Advanced Inventory & Warehouse) ✅
- [x] CRUD gudang (etalase/gudang)
- [x] Barang masuk: penerimaan barang dari pemasok ke gudang
- [x] Transfer antar gudang (barang keluar)
- [x] Halaman kartu stok per produk
- [x] Dashboard stok per gudang

## Fase 5 — Add-on Absensi & Payroll ✅
- [x] Migration: `karyawan`, `absensi`, `penggajian`, `komponen_gaji`
- [x] CRUD karyawan + set skema gaji (harian/pokok)
- [x] Setting koordinat & radius toko (admin)
- [x] Halaman absensi: HTML5 Geolocation, validasi radius, capture foto, hitung telat/lembur
- [x] Rekap kehadiran per periode
- [x] Generate payroll: harian × hadir atau pokok, tambah tunjangan/potongan
- [x] Payslip digital (view/print) untuk karyawan

## Fase 6 — Billing Manual Transfer ✅
- [x] Migration `pembayaran` + Model `Pembayaran`
- [x] Halaman tagihan toko: riwayat langganan, ajukan upgrade/add-on + upload bukti
- [x] Panel superadmin: verifikasi pembayaran transfer, aktifkan paket/add-on, set langganan_berakhir_pada
- [x] Banner status langganan di dashboard toko
- [x] Test feature `BillingTest` (9 test)

## Verifikasi Tiap Fase ✅
- [x] Feature test per modul (PHPUnit) — total 71 test, 211 assertions, lulus semua
- [x] Cek isolation: query lintas-toko tidak bocor (global scope `BelongsToToko`)
- [x] `npm run build` + review UI responsif mobile (absensi dipakai dari HP)
- [x] `vendor/bin/pint` clean

> **Status: PROJECT COMPLETE** — semua 6 fase selesai, 71 test lulus. Lihat `docs/README-proyek.md` untuk deskripsi lengkap & panduan serah-terima.
