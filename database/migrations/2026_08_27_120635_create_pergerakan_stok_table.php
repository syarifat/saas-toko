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
        Schema::create('pergerakan_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->foreignId('gudang_tujuan_id')->nullable()->constrained('gudang')->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar', 'transfer', 'penjualan', 'opname'])->index();
            $table->integer('jumlah');
            $table->nullableMorphs('referensi');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'produk_id', 'created_at']);
            $table->index(['toko_id', 'gudang_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stok');
    }
};
