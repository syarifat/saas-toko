<?php

namespace Database\Factories;

use App\Models\Gudang;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gudang>
 */
class GudangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'toko_id' => Toko::factory(),
            'nama' => 'Etalase Utama',
            'jenis' => 'etalase',
        ];
    }
}
