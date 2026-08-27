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
        Schema::create('item_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->string('nama_produk');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('harga_beli_snapshot', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['toko_id', 'transaksi_id']);
            $table->index(['toko_id', 'produk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transaksi');
    }
};
