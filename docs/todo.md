# Todo List Implementasi — Mega System SaaS Toko

## Fase 1 — Fondasi & Multi-Tenancy ✅
- [ ] Install Laravel Boost (`composer require laravel/boost --dev && php artisan boost:install`)
- [ ] Install Laravel Breeze (Blade + Tailwind)
- [ ] Migration inti: `toko`, `paket` (jenis: preset_1/2/3/custom), `modul`, `ketergantungan_modul`, `paket_modul`, `modul_toko`; ubah `pengguna` (peran, sub_peran, toko_id, aktif, dibuat_oleh)
- [ ] Model + relasi: Toko, Paket, Modul, KetergantunganModul, PaketModul, ModulToko, Pengguna
- [ ] Trait `BelongsToToko` (global scope by toko_id)
- [ ] Middleware: `peran`, `konteks_toko`, `CekModul` (gantikan `CekPaket`+`CekAddon`)
- [ ] Service `ModulService`: aktivasi modul (validasi dependency), deaktivasi modul (validasi dependan), sinkronisasi preset ke modul_toko
- [ ] Helper `Toko::modulAktif('kode')`, `Toko::pakaiPreset(Paket $paket)`
- [ ] Seeder: superadmin default + 3 preset paket + 16 modul + dependency graph lengkap
- [ ] Panel Superadmin — Manajemen Paket: CRUD preset + custom (pilih modul, harga, deskripsi; validasi dependency)
- [ ] Panel Superadmin — Manajemen Toko: CRUD toko + admin toko, toggle modul per toko dengan dependency validation
- [ ] Flow registrasi toko oleh superadmin (auto-buat user admin + aktifkan modul sesuai preset)

## Fase 2 — Fitur Paket 1 (Basic Cashbook & Sales) ✅
- [ ] Migration: `pengeluaran`, `penjualan_sederhana`, `item_penjualan_sederhana`, `kategori`, `pemasok`, `produk`
- [ ] CRUD pengeluaran + upload struk
- [ ] CRUD produk/kategori/pemasok (gated tier ≥ 1 — semua paket)
- [ ] Form pencatatan penjualan ringkas: pilih produk dari master, qty, harga satuan, subtotal + harga_beli_snapshot
- [ ] Halaman rekap harian/mingguan/bulanan + laba kotor sederhana (dari harga_beli_snapshot)

## Fase 3 — Fitur Modul POS & Stok (kasir_pos, stok_gudang, dll) ✅
- [ ] Migration: `gudang` (default etalase saat modul `stok_gudang` diaktifkan), `stok_gudang`, `pergerakan_stok`, `transaksi`, `item_transaksi`
- [ ] UI kasir POS (gate: `CekModul('kasir_pos')`): cari produk dari master, keranjang, bayar, kembalian — validasi sisa stok
- [ ] Service `StokService`: deduct stok + tulis `pergerakan_stok` (DB transaction)
- [ ] Stock alert di dashboard (gate: `CekModul('stock_alert')`) — `stok_minimum` tampil di form produk hanya jika modul `stok_gudang` aktif
- [ ] Stok opname/adjustment (gate: `CekModul('stok_opname')`)
- [ ] Laporan laba per produk (gate: `CekModul('laporan_hpp')`; pakai harga_beli_snapshot di item_transaksi)

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
