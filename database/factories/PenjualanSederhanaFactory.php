<?php

namespace Database\Factories;

use App\Models\ItemPenjualanSederhana;
use App\Models\PenjualanSederhana;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenjualanSederhana>
 */
class PenjualanSederhanaFactory extends Factory
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
            'tanggal_penjualan' => fake()->date(),
            'total' => 0,
            'catatan' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PenjualanSederhana $penjualan) {
            $total = 0;
            $jumlahItem = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $jumlahItem; $i++) {
                $jumlah = fake()->numberBetween(1, 5);
                $harga = fake()->numberBetween(1000, 50000);
                $subtotal = $jumlah * $harga;
                $total += $subtotal;

                ItemPenjualanSederhana::create([
                    'toko_id' => $penjualan->toko_id,
                    'penjualan_sederhana_id' => $penjualan->id,
                    'nama_barang' => fake()->word(),
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                ]);
            }

            $penjualan->update(['total' => $total]);
        });
    }
}
