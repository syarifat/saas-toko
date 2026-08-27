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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            $table->date('tanggal_pengeluaran')->index();
            $table->string('keterangan');
            $table->decimal('nominal', 12, 2);
            $table->string('bukti_struk')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'tanggal_pengeluaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
