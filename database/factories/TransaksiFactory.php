<?php

namespace Database\Factories;

use App\Models\Gudang;
use App\Models\Pengguna;
use App\Models\Toko;
use App\Models\Transaksi;
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
            'toko_id' => Toko::factory(),
            'pengguna_id' => Pengguna::factory(),
            'gudang_id' => Gudang::factory(),
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
