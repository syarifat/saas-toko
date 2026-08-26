<?php

namespace Database\Factories;

use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengeluaran>
 */
class PengeluaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengguna_id' => User::factory(),
            'tanggal_pengeluaran' => fake()->date(),
            'keterangan' => fake()->words(3, true),
            'nominal' => fake()->numberBetween(1000, 1000000),
        ];
    }
}
