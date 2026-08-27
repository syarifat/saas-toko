# Todo List Implementasi — Mega System SaaS Toko

## Fase 1 — Fondasi & Multi-Tenancy ✅
- [ ] Install Laravel Boost (`composer require laravel/boost --dev && php artisan boost:install`)
- [ ] Install Laravel Breeze (Blade + Tailwind)
- [ ] Migration inti: `toko`, `paket`, `addon`, `addon_toko`; ubah `pengguna` (peran, sub_peran, toko_id, aktif, dibuat_oleh)
- [ ] Model + relasi: Toko, Paket, Addon, Pengguna
- [ ] Trait `BelongsToToko` (global scope by toko_id)
- [ ] Middleware: `peran`, `konteks_toko`, `paket`, `addon`
- [ ] Helper `Toko::setidaknyaPaket()`, `Toko::punyaAddon()`
- [ ] Seeder: superadmin default + 3 master paket + 2 add-on
- [ ] Panel Superadmin: CRUD toko + admin toko, CRUD paket & add-on, toggle add-on per toko
- [ ] Flow registrasi toko oleh superadmin (auto-buat user admin)

## Fase 2 — Fitur Paket 1 (Basic Cashbook & Sales) ✅
- [ ] Migration: `pengeluaran`, `penjualan_sederhana`, `item_penjualan_sederhana`
- [ ] CRUD pengeluaran + upload struk
- [ ] Form pencatatan penjualan ringkas + item dinamis
- [ ] Halaman rekap harian/mingguan/bulanan + laba kotor sederhana

## Fase 3 — Fitur Paket 2 (POS & Stock Management) ✅
- [ ] Migration: `kategori`, `pemasok`, `produk`, `gudang` (default etalase), `stok_gudang`, `pergerakan_stok`, `transaksi`, `item_transaksi`
- [ ] CRUD produk/kategori/pemasok (gated tier ≥ 2)
- [ ] UI kasir: cari produk, keranjang, bayar, kembalian
- [ ] Service `StokService`: deduct stok + tulis `pergerakan_stok` (DB transaction)
- [ ] Stock alert di dashboard (produk < stok_minimum)
- [ ] Stok opname/adjustment
- [ ] Laporan laba per produk (pakai harga_beli_snapshot)

## Fase 4 — Fitur Paket 3 (Advanced Inventory & Warehouse) ✅
- [ ] CRUD gudang (etalase/gudang)
- [ ] Barang masuk: penerimaan barang dari pemasok ke gudang
- [ ] Transfer antar gudang (barang keluar)
- [ ] Halaman kartu stok per produk
- [ ] Dashboard stok per gudang

## Fase 5 — Add-on Absensi & Payroll ✅
- [ ] Migration: `karyawan`, `absensi`, `penggajian`, `komponen_gaji`
- [ ] CRUD karyawan + set skema gaji (harian/pokok)
- [ ] Setting koordinat & radius toko (admin)
- [ ] Halaman absensi: HTML5 Geolocation, validasi radius, capture foto, hitung telat/lembur
- [ ] Rekap kehadiran per periode
- [ ] Generate payroll: harian × hadir atau pokok, tambah tunjangan/potongan
- [ ] Payslip digital (view/print) untuk karyawan

## Fase 6 — Billing Manual Transfer ✅
- [ ] Migration `pembayaran` + Model `Pembayaran`
- [ ] Halaman tagihan toko: riwayat langganan, ajukan upgrade/add-on + upload bukti
- [ ] Panel superadmin: verifikasi pembayaran transfer, aktifkan paket/add-on, set langganan_berakhir_pada
- [ ] Banner status langganan di dashboard toko
- [ ] Test feature `BillingTest` (9 test)

## Verifikasi Tiap Fase ✅
- [ ] Feature test per modul (PHPUnit) — total 71 test, 211 assertions, lulus semua
- [ ] Cek isolation: query lintas-toko tidak bocor (global scope `BelongsToToko`)
- [ ] `npm run build` + review UI responsif mobile (absensi dipakai dari HP)
- [ ] `vendor/bin/pint` clean

> **Status: PROJECT COMPLETE** — semua 6 fase selesai, 71 test lulus. Lihat `docs/README-proyek.md` untuk deskripsi lengkap & panduan serah-terima.
