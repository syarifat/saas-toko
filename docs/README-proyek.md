# Deskripsi Proyek — Mega System SaaS Toko

## Apa itu proyek ini?

**Mega System SaaS Toko** adalah aplikasi **SaaS (Software as a Service) untuk manajemen toko** yang memungkinkan satu instalasi Laravel melayani banyak toko (multi-tenant). Setiap toko mendaftar, memilih paket berlangganan, dan menggunakan fitur sesuai tier yang dibayar. Cocok untuk penyedia layanan yang ingin menjual sistem kasir + stok + payroll ke banyak pedagang kecil-menengah.

### Model Bisnis
- **3 Tingkat Paket** (upgrade bertingkat):
  - **Paket 1 — Cashbook**: pencatatan pengeluaran & penjualan ringkas + rekap laba kotor.
  - **Paket 2 — POS & Stok**: tambah master produk/kategori/pemasok, kasir POS, stok opname, alert stok menipis.
  - **Paket 3 — Gudang**: multi-gudang, barang masuk, transfer antar gudang, kartu stok.
- **2 Add-on** (bisa dibeli terpisah, butuh minimal Paket 1):
  - **Absensi**: presensi karyawan berbasis GPS (geotagging + radius).
  - **Payroll**: perhitungan gaji + slip gaji digital.
- **Billing manual**: tenant upload bukti transfer, superadmin memverifikasi secara manual (belum integrasi payment gateway).

### Teknologi
- **Backend**: Laravel 13, PHP 8.4, Eloquent ORM.
- **Frontend**: Blade + Tailwind CSS v4, Vite.
- **Database**: MySQL (single database, isolasi via kolom `toko_id` + global scope).
- **Auth**: Laravel Breeze.
- **Testing**: PHPUnit (feature tests).
- **Arsitektur**: single-DB multi-tenant (1 tenant = 1 toko), role `superadmin`/`admin`/`karyawan` (sub-peran `kasir`/`gudang`).

---

## Yang Dikerjakan oleh AI Agent (Ringkasan Serah-Terima)

Jika Anda berhenti menggunakan AI agent ini, berikut catatan lengkap apa yang sudah dibangun agar pengembang berikutnya (atau Anda sendiri) bisa lanjut:

### Struktur Kode Penting
| Komponen | Lokasi | Fungsi |
|---|---|---|
| Global scope tenant | `app/Models/Concerns/BelongsToToko.php` | Otomatis filter semua query by `toko_id` → isolasi data antar toko |
| Model inti | `app/Models/Toko.php`, `Paket.php`, `Addon.php`, `Pembayaran.php`, `User.php` (Pengguna) | Entitas domain |
| Middleware akses | `app/Http/Middleware/` (`Peran`, `EnsureTokoContext`, `CekPaket`, `CekAddon`) | Gate peran, konteks toko, tier paket, add-on |
| Controller tenant | `app/Http/Controllers/` (Pengeluaran, PenjualanSederhana, Produk, Kasir, Gudang, Karyawan, Absensi, Penggajian, Tagihan, dll) | Logika fitur |
| Controller superadmin | `app/Http/Controllers/Superadmin/` | CRUD toko/paket/add-on + verifikasi pembayaran |
| Service stok | `app/Services/StokService.php` | Deduct/restock stok + tulis `pergerakan_stok` (transaction) |
| Views | `resources/views/` (termasuk `superadmin/`, `kasir/`, `gudang/`, `absensi/`, `penggajian/`, `tagihan/`) | UI Blade |
| Routes | `routes/web.php` | Rute web + grouping gate paket/add-on |

### Alur Bisnis Kunci
1. **Superadmin** buat toko + user admin → tenant login.
2. Tenant pakai fitur sesuai paket (diblokir middleware jika tier kurang).
3. Tenant **ajukan upgrade/add-on** di `/tagihan` + upload bukti transfer.
4. **Superadmin** verifikasi di `/superadmin/verifikasi` → setujui (paket naik + langganan diperpanjang otomatis) atau tolak (wajib catatan).

### Konvensi yang Sudah Diterapkan (Ikuti Saat Lanjut)
- **Nama tabel & field berbahasa Indonesia** (snake_case): `toko`, `paket`, `pengguna`, `penjualan_sederhana`, `pergerakan_stok`, dll.
- **Fitur berbayar selalu di-gate** middleware `paket:N` / `addon:kode` di route.
- **Setiap model tenant** harus `use BelongsToToko` agar otomatis ter-isolasi.
- **Testing**: setiap modul punya feature test di `tests/Feature/` (jalankan `php artisan test`).
- **Code style**: jalankan `vendor/bin/pint --dirty` setelah edit PHP.

### Status Saat Ini
- ✅ 6 fase selesai (fondasi → billing).
- ✅ **71 test PHPUnit lulus** (211 assertions).
- ✅ Pint clean, `npm run build` sukses.
- 📌 Belum: integrasi payment gateway otomatis, blokir akses saat langganan expired, deploy ke production (Laravel Cloud/VPS), migrasi ke Docker Sail.

### Cara Menjalankan
```bash
# DB lokal: MySQL port 8889, db saas_toko, user root / root (lihat .env)
php artisan migrate --seed      # seed superadmin + 3 paket + 2 add-on
php artisan serve
# Login superadmin: superadmin@saastoko.test / password
php artisan test --compact      # jalankan semua test
```

### Rekomendasi Lanjutan (bila dilanjut)
1. Blokir akses tenant saat `langganan_berakhir_pada` lewat (middleware `konteks_toko`).
2. Integrasi Midtrans/Xendit untuk billing otomatis.
3. Export PDF slip gaji (LibreOffice/FPDI).
4. Deploy + setup queue worker untuk notifikasi.
5. Audit log perubahan stok (sudah ada `pergerakan_stok`, tinggal view audit).
