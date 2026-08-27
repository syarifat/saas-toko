<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_penjualan_sederhana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('penjualan_sederhana_id')->constrained('penjualan_sederhana')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->string('nama_produk');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('harga_beli_snapshot', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['toko_id', 'penjualan_sederhana_id'], 'ips_toko_penjualan_idx');
            $table->index(['toko_id', 'produk_id'], 'ips_toko_produk_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_penjualan_sederhana');
    }
};
