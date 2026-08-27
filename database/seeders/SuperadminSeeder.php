<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pengguna::updateOrCreate(
            ['email' => 'superadmin@saastoko.test'],
            [
                'nama' => 'Super Admin Platform',
                'password' => Hash::make('password'),
                'peran' => 'superadmin',
                'sub_peran' => null,
                'toko_id' => null,
                'aktif' => true,
            ]
        );
    }
}
