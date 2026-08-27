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
        Schema::create('stok_gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->timestamps();

            $table->unique(['produk_id', 'gudang_id']);
            $table->index(['toko_id', 'produk_id']);
            $table->index(['toko_id', 'gudang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_gudang');
    }
};
