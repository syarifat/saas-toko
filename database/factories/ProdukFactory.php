<?php

namespace Database\Factories;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{4}'),
            'nama' => fake()->words(2, true),
            'harga_beli' => fake()->numberBetween(1000, 50000),
            'harga_jual' => fake()->numberBetween(51000, 100000),
            'stok_minimum' => 5,
        ];
    }
}
