<?php

namespace Database\Factories;

use App\Models\Paket;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Toko>
 */
class TokoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->company();

        return [
            'nama' => $nama,
            'slug' => str($nama)->slug()->value() ?? fake()->unique()->slug(),
            'paket_id' => Paket::factory(),
            'status' => 'aktif',
            'radius_absensi' => 100,
        ];
    }
}
