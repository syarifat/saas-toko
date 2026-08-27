<?php

namespace Database\Factories;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paket>
 */
class PaketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(3, true),
            'jenis' => fake()->randomElement(['preset_1', 'preset_2', 'preset_3', 'custom']),
            'harga' => fake()->randomFloat(2, 0, 500000),
            'deskripsi' => fake()->sentence(),
            'aktif' => true,
        ];
    }
}
