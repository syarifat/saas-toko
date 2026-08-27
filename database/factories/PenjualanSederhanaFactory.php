<?php

namespace Database\Factories;

use App\Models\ItemPenjualanSederhana;
use App\Models\Pengguna;
use App\Models\PenjualanSederhana;
use App\Models\Produk;
use App\Models\Toko;
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
            'toko_id' => Toko::factory(),
            'pengguna_id' => Pengguna::factory(),
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
                $produk = Produk::factory()->create(['toko_id' => $penjualan->toko_id]);
                $jumlah = fake()->numberBetween(1, 5);
                $harga = $produk->harga_jual;
                $subtotal = $jumlah * $harga;
                $total += $subtotal;

                ItemPenjualanSederhana::create([
                    'toko_id' => $penjualan->toko_id,
                    'penjualan_sederhana_id' => $penjualan->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                    'harga_beli_snapshot' => $produk->harga_beli,
                ]);
            }

            $penjualan->update(['total' => $total]);
        });
    }
}
