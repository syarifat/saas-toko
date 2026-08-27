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
        Schema::create('penjualan_sederhana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            $table->date('tanggal_penjualan')->index();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'tanggal_penjualan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_sederhana');
    }
};
