# ERD — Mega System SaaS Toko

> Multi-tenant SaaS manajemen toko: pencatatan keuangan, POS, inventori/gudang, absensi & payroll.
> Pendekatan tenancy: **Single Database + kolom `toko_id`** (global scope).
> Semua tabel domain memiliki `toko_id` (FK → `toko`) + index.

## Diagram Relasi (teks)

```text
superadmin (pengguna.peran=superadmin, toko_id=NULL)
    └── mengelola ──> toko <── dimiliki oleh ── admin (pengguna.peran=admin)
                        │
                        ├── paket (preset/custom) <──> paket_modul <──> modul
                        │                                              └──< ketergantungan_modul
                        ├── modul_toko (modul aktif per toko) >── modul
                        ├── pengeluaran
                        ├── penjualan_sederhana ──< item_penjualan_sederhana
                        ├── transaksi ──< item_transaksi
                        ├── kategori ──< produk >── pemasok
                        │                   │
                        gudang ──< stok_gudang >── produk
                        │              │
                        │        pergerakan_stok (kartu stok)
                        ├── karyawan ──< absensi
                        │      └──< penggajian ──< komponen_gaji
                        └── pengguna (karyawan: sub_peran = kasir|gudang)
```

---

## Penjelasan Detail Per Tabel

### `toko` — Toko (tenant)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nama | string | Nama toko |
| slug | string, unique | Identifikasi URL/route |
| paket_id | FK → paket | Paket aktif (tier 1-3) |
| status | enum: aktif, nonaktif | Status langganan |
| garis_lintang | decimal, nullable | Koordinat lintang toko untuk geofencing absensi |
| garis_bujur | decimal, nullable | Koordinat bujur toko |
| radius_absensi | integer, default 100 | Radius geofencing (meter) |
| langganan_berakhir_pada | timestamp, nullable | Masa berlaku langganan |
| created_at / updated_at | timestamps | |

### `paket` — Master Paket / Preset (dikelola superadmin)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nama | string | Misal: "Paket 1 — Cashbook", "Custom Apotek Sejahtera" |
| jenis | enum: preset_1, preset_2, preset_3, custom | `preset_*` = bundel standar platform; `custom` = disusun superadmin khusus per tenant |
| harga | decimal | Harga per bulan |
| deskripsi | text | |
| aktif | boolean | |

### `modul` — Master Modul Platform

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | string, unique | Identifier: `master_produk`, `kasir_pos`, `absensi`, dll |
| nama | string | Nama tampilan |
| deskripsi | text | |
| aktif | boolean | Aktif di platform (superadmin bisa disable global) |

> **16 modul tersedia** (di-seed saat install):
> `pengeluaran`, `master_produk`, `penjualan_ringkas`, `rekap_keuangan`,
> `stok_gudang`, `kasir_pos`, `stock_alert`, `stok_opname`, `laporan_hpp`,
> `multi_gudang`, `barang_masuk`, `transfer_gudang`, `kartu_stok`,
> `karyawan`, `absensi`, `payroll`

### `ketergantungan_modul` — Dependency Graph Antar Modul

| Kolom | Tipe | Keterangan |
|---|---|---|
| modul_id | FK → modul | Modul yang memiliki ketergantungan |
| requires_modul_id | FK → modul | Modul yang wajib aktif terlebih dahulu |
| (composite PK) | | modul_id + requires_modul_id |

> Dependency yang didefinisikan:
> - `penjualan_ringkas` → `master_produk`
> - `rekap_keuangan` → `penjualan_ringkas`
> - `stok_gudang` → `master_produk`
> - `kasir_pos` → `master_produk`, `stok_gudang`
> - `stock_alert`, `stok_opname`, `barang_masuk`, `kartu_stok` → `stok_gudang`
> - `multi_gudang` → `stok_gudang`
> - `transfer_gudang` → `multi_gudang`
> - `laporan_hpp` → `kasir_pos`
> - `absensi` → `karyawan`
> - `payroll` → `absensi`

### `paket_modul` — Pivot Modul dalam Paket/Preset

| Kolom | Keterangan |
|---|---|
| paket_id + modul_id | composite PK |

> Mendefinisikan modul apa yang termasuk dalam preset atau paket custom.
> Saat preset di-assign ke toko, semua modul di sini disinkronkan ke `modul_toko`.

### `modul_toko` — Modul Aktif per Toko

| Kolom | Keterangan |
|---|---|
| toko_id + modul_id | composite PK |
| aktif | boolean |
| diaktifkan_pada | timestamp |
| berakhir_pada | timestamp nullable — masa berlaku modul; NULL = belum ada batas |

### `pengguna` — Semua pengguna login

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| toko_id | FK nullable | NULL hanya untuk superadmin |
| nama, email, kata_sandi | standar auth | |
| peran | enum: superadmin, admin, karyawan | Hierarki 3 tingkat |
| sub_peran | enum nullable: kasir, gudang | Hanya untuk karyawan; menentukan menu dinamis |
| aktif | boolean | Admin bisa nonaktifkan karyawan |
| dibuat_oleh | FK pengguna nullable | Siapa yang mendaftarkan |

### `kategori` — Kategori produk

id, toko_id, nama, created_at/updated_at.

### `pemasok` — Pemasok/supplier

id, toko_id, nama, telepon, alamat, created_at/updated_at.

### `produk` — Master barang (Semua Paket)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id, toko_id | | |
| kategori_id | FK nullable | |
| pemasok_id | FK nullable | |
| sku | string | Unique per toko |
| nama | string | |
| harga_beli | decimal | Harga beli |
| harga_jual | decimal | Harga jual |
| stok_minimum | integer | Ambang alert stok menipis — **disembunyikan di form Paket 1** (tidak ada `stok_gudang`) |

> Stok **tidak** disimpan di tabel ini — dikelola via `stok_gudang`.

### `gudang` — Lokasi penyimpanan (Paket 3)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id, toko_id | | |
| nama | string | Misal: "Etalase", "Gudang Utama" |
| jenis | enum: etalase, gudang | |

Tenant Paket 2+ mendapat 1 gudang default bertipe `etalase` secara otomatis saat pertama kali diaktifkan. Paket 1 memiliki master produk tetapi tidak memiliki `stok_gudang` — manajemen stok belum aktif di level ini.

### `stok_gudang` — Stok per produk per gudang

| Kolom | Tipe | Keterangan |
|---|---|---|
| id, toko_id | | |
| produk_id + gudang_id | FK, unique bersama | Satu baris stok per kombinasi |
| jumlah | integer | Jumlah stok saat ini |

### `pergerakan_stok` — Kartu stok (log semua pergerakan barang)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id, toko_id | | |
| produk_id | FK | |
| gudang_id | FK | Gudang asal |
| gudang_tujuan_id | FK nullable | Untuk transfer antar gudang |
| jenis | enum: masuk, keluar, transfer, penjualan, opname | |
| jumlah | integer, bertanda | Positif = masuk, negatif = keluar |
| referensi_tipe / referensi_id | polymorphic | Sumber gerakan (transaksi, barang masuk, dll) |
| catatan | text nullable | Alasan (rusak/hilang/opname) |

### `pengeluaran` — Pengeluaran/belanja (Paket 1+)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id, toko_id | | |
| pengguna_id | FK | Pencatat |
| tanggal_pengeluaran | date | Tanggal belanja |
| keterangan | string | Nama barang/keperluan |
| nominal | decimal | Nominal uang keluar |
| bukti_struk | string nullable | Upload foto bukti struk |

### `penjualan_sederhana` — Penjualan ringkas (Paket 1)

id, toko_id, pengguna_id, tanggal_penjualan, total, catatan, created_at/updated_at.

### `item_penjualan_sederhana` — Item penjualan ringkas

| Kolom | Keterangan |
|---|---|
| id, toko_id, penjualan_sederhana_id | |
| produk_id | FK → produk (wajib; dipilih dari master produk) |
| nama_produk | Snapshot nama produk saat transaksi |
| jumlah, harga_satuan, subtotal | |
| harga_beli_snapshot | Snapshot harga beli untuk estimasi laba kotor Paket 1 |

### `transaksi` — Transaksi kasir POS (Paket 2+)

| Kolom | Keterangan |
|---|---|
| id, toko_id, pengguna_id, gudang_id | Kasir & gudang sumber stok |
| tanggal_transaksi | |
| subtotal, diskon, total | |
| jumlah_bayar, kembalian | Uang bayar & kembalian |
| metode_pembayaran | enum: tunai, qris, transfer |

### `item_transaksi` — Item transaksi POS

| Kolom | Keterangan |
|---|---|
| id, toko_id, transaksi_id | |
| produk_id | |
| nama_produk | Snapshot nama produk |
| jumlah, harga_satuan, subtotal | |
| harga_beli_snapshot | Snapshot harga beli untuk hitung laba per produk |

### `karyawan` — Profil karyawan (untuk HRIS/add-on)

| Kolom | Keterangan |
|---|---|
| id, toko_id | |
| pengguna_id | FK (wajib) — karyawan harus punya akun login untuk absensi mandiri |
| kode_karyawan, posisi | |
| skema_gaji | enum: harian, bulanan |
| tarif_harian | Tarif per hari (jika harian) |
| gaji_pokok | Gaji pokok (jika bulanan) |
| tanggal_masuk, aktif | |

### `absensi` — Absensi (Add-on Absensi)

| Kolom | Keterangan |
|---|---|
| id, toko_id, karyawan_id | |
| tanggal | Tanggal absen |
| jam_masuk, jam_keluar | Timestamp |
| lintang_masuk/bujur_masuk, lintang_keluar/bujur_keluar | Geotagging |
| foto_masuk, foto_keluar | Selfie (nullable) |
| status | enum: tepat_waktu, telat |
| menit_telat, menit_lembur | |

### `penggajian` — Penggajian periode (Add-on Payroll)

| Kolom | Keterangan |
|---|---|
| id, toko_id, karyawan_id | |
| periode_mulai, periode_selesai | Periode penggajian |
| skema_gaji_snapshot | harian/bulanan saat digaji |
| jumlah_dasar | Dari absensi (harian × hadir) atau pokok |
| total_tunjangan, total_potongan | Total tunjangan & potongan |
| gaji_bersih | Take-home pay |
| status | enum: draf, dibayar |
| dibayar_pada | timestamp nullable |

### `komponen_gaji` — Rincian tunjangan/potongan

| Kolom | Keterangan |
|---|---|
| id, toko_id, penggajian_id | |
| jenis | enum: tunjangan, potongan |
| nama | Makan, transport, kasbon, telat, dll |
| nominal | decimal |

---

### `pembayaran` — Riwayat Pembayaran/Tagihan (Billing)

| Kolom | Keterangan |
|---|---|
| id, toko_id | |
| jenis | enum: upgrade_paket, aktivasi_addon |
| paket_id | FK → paket nullable (diisi saat jenis = upgrade_paket) |
| addon_id | FK → addon nullable (diisi saat jenis = aktivasi_addon) |
| jumlah | decimal — nominal yang dibayar (rupiah) |
| bukti_transfer | string — path file upload bukti transfer |
| status | enum: menunggu, disetujui, ditolak |
| catatan_penolakan | text nullable — wajib diisi superadmin saat menolak |
| diverifikasi_oleh | FK → pengguna nullable — superadmin yang memverifikasi |
| diverifikasi_pada | timestamp nullable |
| created_at / updated_at | timestamps |
