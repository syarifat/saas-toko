<?php

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
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
            'tanggal_transaksi' => fake()->date(),
            'subtotal' => 0,
            'diskon' => 0,
            'total' => 0,
            'jumlah_bayar' => 0,
            'kembalian' => 0,
            'metode_pembayaran' => 'tunai',
        ];
    }
}
