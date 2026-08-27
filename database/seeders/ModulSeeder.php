<?php

namespace Database\Seeders;

use App\Models\Modul;
use Illuminate\Database\Seeder;

class ModulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduls = [
            ['kode' => 'pengeluaran',       'nama' => 'Pencatatan Pengeluaran',          'deskripsi' => 'Catat pengeluaran operasional toko.'],
            ['kode' => 'master_produk',     'nama' => 'Master Produk/Kategori/Pemasok',  'deskripsi' => 'Kelola data produk, kategori, dan pemasok.'],
            ['kode' => 'penjualan_ringkas', 'nama' => 'Penjualan Ringkas',               'deskripsi' => 'Catat penjualan dengan pilih produk dari master.'],
            ['kode' => 'rekap_keuangan',    'nama' => 'Rekap & Laba Kotor',              'deskripsi' => 'Lihat rekap uang masuk/keluar dan estimasi laba.'],
            ['kode' => 'stok_gudang',       'nama' => 'Manajemen Stok',                  'deskripsi' => 'Kelola stok produk per gudang.'],
            ['kode' => 'kasir_pos',         'nama' => 'Kasir POS',                       'deskripsi' => 'Transaksi kasir dengan auto deduct stok.'],
            ['kode' => 'stock_alert',       'nama' => 'Alert Stok Menipis',              'deskripsi' => 'Notifikasi produk stok di bawah minimum.'],
            ['kode' => 'stok_opname',       'nama' => 'Stok Opname/Adjustment',          'deskripsi' => 'Penyesuaian stok fisik vs sistem.'],
            ['kode' => 'laporan_hpp',       'nama' => 'Laporan Laba per Produk (HPP)',   'deskripsi' => 'Laporan laba kotor per produk berdasarkan HPP.'],
            ['kode' => 'multi_gudang',      'nama' => 'Multi Gudang',                    'deskripsi' => 'Kelola lebih dari satu gudang/etalase.'],
            ['kode' => 'barang_masuk',      'nama' => 'Barang Masuk dari Supplier',      'deskripsi' => 'Catat penerimaan barang dari pemasok.'],
            ['kode' => 'transfer_gudang',   'nama' => 'Transfer Antar Gudang',           'deskripsi' => 'Pindahkan stok antar gudang.'],
            ['kode' => 'kartu_stok',        'nama' => 'Kartu Stok Detail',               'deskripsi' => 'Histori pergerakan stok per produk.'],
            ['kode' => 'karyawan',          'nama' => 'HRIS Karyawan',                   'deskripsi' => 'Kelola data karyawan dan skema gaji.'],
            ['kode' => 'absensi',           'nama' => 'Absensi GPS',                     'deskripsi' => 'Presensi karyawan berbasis geolokasi.'],
            ['kode' => 'payroll',           'nama' => 'Penggajian/Payroll',              'deskripsi' => 'Hitung dan bayar gaji karyawan.'],
        ];

        foreach ($moduls as $data) {
            Modul::updateOrCreate(['kode' => $data['kode']], $data);
        }
    }
}
