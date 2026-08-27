# Kebutuhan Fungsional — Mega System SaaS Toko

## Per Role

### Superadmin (Pemilik Platform)
- Login ke panel platform (terpisah dari tampilan toko)
- CRUD tenant/toko: daftarkan admin baru (nama toko, email, paket awal)
- Aktifkan/nonaktifkan tenant
- Kelola tagihan: catat pembayaran manual transfer, perpanjang langganan
- **CRUD Preset Paket** (preset_1, preset_2, preset_3): nama, harga, deskripsi, pilih modul yang termasuk
  - Validasi dependency: tidak bisa simpan preset jika modul dependency-nya tidak ikut disertakan
- **CRUD Paket Custom**: buat paket khusus per tenant dengan kombinasi modul bebas
  - Validasi dependency tetap berlaku
- **Kelola Modul per Toko**: toggle modul individual untuk tenant tertentu
  - Mengaktifkan modul: validasi dependency — jika ada modul wajib yang belum aktif, tampilkan peringatan + opsi aktifkan sekaligus
  - Menonaktifkan modul: validasi dependan — blokir jika ada modul lain yang bergantung padanya dan masih aktif
- Lihat statistik platform: jumlah tenant, tenant per paket, pendapatan

### Admin (Pemilik Toko/Tenant)
- Dashboard sesuai modul aktif tokonya
- CRUD data toko: profil, koordinat lokasi + radius absensi
- CRUD akun karyawan: buat login, set sub-role (kasir/gudang), aktif/nonaktifkan
- Semua fitur operasional sesuai modul yang aktif
- Ajukan aktivasi modul tambahan (request → superadmin verifikasi transfer manual)
- Lihat semua laporan tokonya

### Karyawan (dibuat oleh Admin)
- Login dengan akun yang dibuat admin
- Menu dinamis sesuai sub-role:
  - **Kasir**: transaksi penjualan (Paket 1: pencatatan ringkas; Paket 2+: POS produk), lihat riwayat transaksinya sendiri
  - **Gudang**: barang masuk/keluar/transfer, kartu stok, stok opname (Paket 3)
- Absensi: clock-in/clock-out dengan geolocation + foto (jika add-on aktif)
- Lihat & unduh payslip sendiri (jika add-on payroll aktif)

---

## Fitur per Paket (Preset)

> Paket 1/2/3 adalah **preset** — bundel modul standar yang dikonfigurasi superadmin.
> Superadmin dapat membuat paket **Custom** dengan kombinasi modul bebas (dengan validasi dependency).
> Middleware `CekModul('kode_modul')` menggantikan `CekPaket(N)` dan `CekAddon('kode')` lama.

| Modul | Kode | P1 Preset | P2 Preset | P3 Preset |
|---|---|:-:|:-:|:-:|
| Pencatatan Pengeluaran | `pengeluaran` | ✅ | ✅ | ✅ |
| Master Produk/Kategori/Pemasok | `master_produk` | ✅ | ✅ | ✅ |
| Penjualan Ringkas *(requires: master_produk)* | `penjualan_ringkas` | ✅ | ✅ | ✅ |
| Rekap & Laba Kotor *(requires: penjualan_ringkas)* | `rekap_keuangan` | ✅ | ✅ | ✅ |
| Manajemen Stok *(requires: master_produk)* | `stok_gudang` | ❌ | ✅ | ✅ |
| Kasir POS *(requires: master_produk, stok_gudang)* | `kasir_pos` | ❌ | ✅ | ✅ |
| Alert Stok Menipis *(requires: stok_gudang)* | `stock_alert` | ❌ | ✅ | ✅ |
| Stok Opname/Adjustment *(requires: stok_gudang)* | `stok_opname` | ❌ | ✅ | ✅ |
| Laporan Laba per Produk HPP *(requires: kasir_pos)* | `laporan_hpp` | ❌ | ✅ | ✅ |
| Multi Gudang *(requires: stok_gudang)* | `multi_gudang` | ❌ | ❌ | ✅ |
| Barang Masuk dari Supplier *(requires: stok_gudang)* | `barang_masuk` | ❌ | ❌ | ✅ |
| Transfer Antar Gudang *(requires: multi_gudang)* | `transfer_gudang` | ❌ | ❌ | ✅ |
| Kartu Stok Detail *(requires: stok_gudang)* | `kartu_stok` | ❌ | ❌ | ✅ |
| HRIS Karyawan | `karyawan` | ❌ | ❌ | ❌ |
| Absensi GPS *(requires: karyawan)* | `absensi` | ❌ | ❌ | ❌ |
| Penggajian/Payroll *(requires: absensi)* | `payroll` | ❌ | ❌ | ❌ |

### Add-on (sekarang menjadi Modul)

> Add-on Absensi dan Payroll kini adalah **modul biasa** dalam sistem modular.
> Dapat diaktifkan per toko oleh superadmin, dengan dependency validation tetap berlaku.

| Modul | Kode | Requires |
|---|---|---|
| HRIS Karyawan | `karyawan` | — |
| Absensi GPS (geofencing + foto) | `absensi` | `karyawan` |
| Penggajian / Payroll | `payroll` | `absensi` |

---

## Catatan Keputusan Desain

- **1 tenant = 1 toko** untuk MVP; fitur multi-cabang ditunda ke v2
- **Billing manual transfer**: admin request aktivasi modul → transfer → superadmin verifikasi → modul aktif
- **Status toko hanya `aktif` dan `nonaktif`**: tidak ada masa coba gratis
- **Langganan habis/expire**: belum ada pembatasan otomatis (fase lanjut)
- **Sub-role karyawan** menentukan menu yang tampil; admin melihat semua menu sesuai modul aktif
- **Karyawan wajib punya akun login** (`pengguna_id` tidak boleh NULL) agar bisa absensi mandiri via browser (geolocation + foto selfie)
- **Sistem Modular**: semua fitur berbasis modul (`modul_toko`); middleware `CekModul('kode')` menggantikan `CekPaket(N)` dan `CekAddon('kode')` lama
- **Preset vs Custom**: Paket 1/2/3 adalah preset dengan bundel modul standar; superadmin bisa buat paket Custom dengan kombinasi modul bebas untuk tenant tertentu
- **Dependency Validation (dua arah)**:
  - Aktifkan modul: cek semua dependency wajib aktif, blokir atau tawarkan aktifkan sekaligus
  - Nonaktifkan modul: cek modul dependen yang masih aktif, blokir hingga dependannya dinonaktifkan dulu
- **Master produk tersedia di semua paket**: Paket 1 sudah bisa CRUD produk/kategori/pemasok; perbedaannya adalah Paket 1 belum punya manajemen stok (`stok_gudang`, deduct stok, stock alert)
- **Penjualan Paket 1 wajib referensi master produk**: item `penjualan_sederhana` harus dipilih dari master produk — memungkinkan estimasi laba kotor dari `harga_beli_snapshot`
- **Validasi stok di POS (modul kasir_pos)**: tidak bisa menjual melebihi sisa stok di `stok_gudang`
- **`stok_minimum` disembunyikan di form Paket 1**: kolom ada di tabel `produk` tapi tidak ditampilkan di UI jika modul `stok_gudang` belum aktif
- **Geofencing** memakai HTML5 Geolocation browser; validasi jarak haversine dari koordinat toko
- **Nama tabel & field berbahasa Indonesia**: `toko`, `paket`, `modul`, `ketergantungan_modul`, `paket_modul`, `modul_toko`, `pengguna`, `kategori`, `pemasok`, `produk`, `gudang`, `stok_gudang`, `pergerakan_stok`, `pengeluaran`, `penjualan_sederhana`, `item_penjualan_sederhana`, `transaksi`, `item_transaksi`, `karyawan`, `absensi`, `penggajian`, `komponen_gaji`, `pembayaran` (detail di `docs/erd.md`)
