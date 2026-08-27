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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->date('tanggal_transaksi')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('jumlah_bayar', 12, 2)->default(0);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'qris', 'transfer'])->default('tunai');
            $table->timestamps();

            $table->index(['toko_id', 'tanggal_transaksi']);
            $table->index(['toko_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
