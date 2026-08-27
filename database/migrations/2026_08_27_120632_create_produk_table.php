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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
            $table->foreignId('pemasok_id')->nullable()->constrained('pemasok')->nullOnDelete();
            $table->string('sku');
            $table->string('nama');
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->timestamps();

            $table->unique(['toko_id', 'sku']);
            $table->index(['toko_id', 'nama']);
            $table->index(['toko_id', 'kategori_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
