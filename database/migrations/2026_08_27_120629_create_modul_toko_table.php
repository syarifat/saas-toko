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
        Schema::create('modul_toko', function (Blueprint $table) {
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('modul_id')->constrained('modul')->cascadeOnDelete();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamp('diaktifkan_pada')->nullable();
            $table->timestamp('berakhir_pada')->nullable();
            $table->primary(['toko_id', 'modul_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modul_toko');
    }
};
