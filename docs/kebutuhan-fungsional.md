# Kebutuhan Fungsional — Mega System SaaS Toko

## Per Role

### Superadmin (Pemilik Platform)
- Login ke panel platform (terpisah dari tampilan toko)
- CRUD tenant/toko: daftarkan admin baru (nama toko, email, paket awal)
- Aktifkan/nonaktifkan tenant
- Kelola tagihan: catat pembayaran manual transfer, perpanjang langganan
- CRUD master paket (tier 1-3): nama, harga, fitur
- CRUD master add-on (absensi, payroll) + harga
- Aktivasi/deaktivasi add-on untuk tenant tertentu
- Lihat statistik platform: jumlah tenant, tenant per paket, pendapatan

### Admin (Pemilik Toko/Tenant)
- Dashboard sesuai paket tokonya
- CRUD data toko: profil, koordinat lokasi + radius absensi
- CRUD akun karyawan: buat login, set sub-role (kasir/gudang), aktif/nonaktifkan
- Semua fitur operasional sesuai paket (lihat tabel di bawah)
- Ajukan aktivasi add-on
- Ajukan upgrade paket (request → superadmin verifikasi transfer manual)
- Lihat semua laporan tokonya

### Karyawan (dibuat oleh Admin)
- Login dengan akun yang dibuat admin
- Menu dinamis sesuai sub-role:
  - **Kasir**: transaksi penjualan (Paket 1: pencatatan ringkas; Paket 2+: POS produk), lihat riwayat transaksinya sendiri
  - **Gudang**: barang masuk/keluar/transfer, kartu stok, stok opname (Paket 3)
- Absensi: clock-in/clock-out dengan geolocation + foto (jika add-on aktif)
- Lihat & unduh payslip sendiri (jika add-on payroll aktif)

---

## Fitur per Paket

| Fitur | P1 Basic | P2 + Stok | P3 + Gudang |
|---|:-:|:-:|:-:|
| Modul pengeluaran + upload struk | ✅ | ✅ | ✅ |
| Penjualan ringkas (nama bebas, qty, total) | ✅ | ✅ | ✅ |
| Rekap uang masuk/keluar harian/mingguan/bulanan | ✅ | ✅ | ✅ |
| Laba kotor sederhana | ✅ | ✅ | ✅ |
| Master produk (SKU, harga beli/jual) | ❌ | ✅ | ✅ |
| Kasir POS dengan auto stock deduct | ❌ | ✅ | ✅ |
| Stock alert (min_stock) | ❌ | ✅ | ✅ |
| Stok opname/adjustment | ❌ | ✅ | ✅ |
| Laporan laba per produk (HPP) | ❌ | ✅ | ✅ |
| Multi-gudang (etalase vs gudang) | ❌ | ❌ | ✅ |
| Barang masuk dari supplier (inbound) | ❌ | ❌ | ✅ |
| Transfer antar gudang (outbound) | ❌ | ❌ | ✅ |
| Kartu stok detail | ❌ | ❌ | ✅ |

### Add-on

| Fitur | Absensi | Payroll |
|---|:-:|:-:|
| Clock in/out dengan geofencing radius toko | ✅ | — |
| Foto selfie saat absen | ✅ | — |
| Log keterlambatan & lembur | ✅ | — |
| Gaji harian × jumlah hadir (butuh Add-on Absensi) | — | ✅ |
| Gaji pokok bulanan tetap | — | ✅ |
| Tunjangan & potongan (makan, transport, kasbon, telat) | — | ✅ |
| Payslip digital per karyawan | — | ✅ |

---

## Catatan Keputusan Desain

- **1 tenant = 1 toko** untuk MVP; fitur multi-cabang ditunda ke v2
- **Billing manual transfer**: admin request upgrade → transfer → superadmin verifikasi → paket/add-on aktif
- **Langganan habis/expire**: belum ada pembatasan otomatis (fase lanjut)
- **Sub-role karyawan** menentukan menu yang tampil; admin melihat semua menu sesuai paket
- **Geofencing** memakai HTML5 Geolocation browser; validasi jarak haversine dari koordinat toko
- **Nama tabel & field berbahasa Indonesia**: `toko`, `paket`, `addon`, `addon_toko`, `pengguna`, `kategori`, `pemasok`, `produk`, `gudang`, `stok_gudang`, `pergerakan_stok`, `pengeluaran`, `penjualan_sederhana`, `item_penjualan_sederhana`, `transaksi`, `item_transaksi`, `karyawan`, `absensi`, `penggajian`, `komponen_gaji` (detail di `docs/erd.md`)
