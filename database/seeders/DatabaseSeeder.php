<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Paket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->superadmin()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@saastoko.test',
            'password' => bcrypt('password'),
        ]);

        Paket::insert([
            ['nama' => 'Basic Cashbook & Sales', 'tingkat' => 1, 'harga' => 0, 'deskripsi' => 'Pencatatan pengeluaran, penjualan ringkas, dan rekap dasar.', 'aktif' => true],
            ['nama' => 'POS & Stock Management', 'tingkat' => 2, 'harga' => 99000, 'deskripsi' => 'Semua fitur Paket 1 + master produk, kasir POS, dan stok otomatis.', 'aktif' => true],
            ['nama' => 'Advanced Inventory & Warehouse', 'tingkat' => 3, 'harga' => 199000, 'deskripsi' => 'Semua fitur Paket 2 + multi-gudang, transfer barang, dan kartu stok.', 'aktif' => true],
        ]);

        Addon::insert([
            ['kode' => 'absensi', 'nama' => 'Smart Attendance (Absensi Geotagging)', 'harga' => 49000, 'aktif' => true],
            ['kode' => 'penggajian', 'nama' => 'Payroll System (Penggajian)', 'harga' => 79000, 'aktif' => true],
        ]);
    }
}
