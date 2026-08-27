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
        Schema::create('komponen_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('penggajian_id')->constrained('penggajian')->cascadeOnDelete();
            $table->enum('jenis', ['tunjangan', 'potongan'])->index();
            $table->string('nama');
            $table->decimal('nominal', 12, 2);
            $table->timestamps();

            $table->index(['toko_id', 'penggajian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_gaji');
    }
};
