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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->string('kode_karyawan');
            $table->string('posisi')->nullable();
            $table->enum('skema_gaji', ['harian', 'bulanan'])->default('bulanan');
            $table->decimal('tarif_harian', 12, 2)->default(0);
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->date('tanggal_masuk');
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();

            $table->unique(['toko_id', 'kode_karyawan']);
            $table->unique('pengguna_id');
            $table->index(['toko_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
