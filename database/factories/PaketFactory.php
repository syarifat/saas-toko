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
            'tingkat' => fake()->unique()->numberBetween(1, 100),
            'harga' => fake()->randomFloat(2, 0, 500000),
            'deskripsi' => fake()->sentence(),
            'aktif' => true,
        ];
    }
}
