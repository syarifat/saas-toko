<?php

namespace Database\Factories;

use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Karyawan>
 */
class KaryawanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxId = (int) DB::table('karyawan')->max('id');

        return [
            'toko_id' => Toko::factory(),
            'pengguna_id' => Pengguna::factory(),
            'kode_karyawan' => 'KRJ'.str_pad((string) ($maxId + fake()->unique()->numberBetween(1, 9999)), 4, '0', STR_PAD_LEFT),
            'posisi' => fake()->randomElement(['kasir', 'gudang', 'sales']),
            'skema_gaji' => fake()->randomElement(['harian', 'bulanan']),
            'tarif_harian' => fake()->numberBetween(50000, 150000),
            'gaji_pokok' => 0,
            'tanggal_masuk' => fake()->date(),
            'aktif' => true,
        ];
    }

    public function bulanan(int $gajiPokok = 3000000): static
    {
        return $this->state(fn () => [
            'skema_gaji' => 'bulanan',
            'gaji_pokok' => $gajiPokok,
            'tarif_harian' => 0,
        ]);
    }
}
