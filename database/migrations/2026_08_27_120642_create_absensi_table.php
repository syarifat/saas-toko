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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal')->index();
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_keluar')->nullable();
            $table->decimal('lintang_masuk', 10, 7)->nullable();
            $table->decimal('bujur_masuk', 10, 7)->nullable();
            $table->decimal('lintang_keluar', 10, 7)->nullable();
            $table->decimal('bujur_keluar', 10, 7)->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->enum('status', ['tepat_waktu', 'telat'])->nullable();
            $table->integer('menit_telat')->default(0);
            $table->integer('menit_lembur')->default(0);
            $table->timestamps();

            $table->unique(['toko_id', 'karyawan_id', 'tanggal']);
            $table->index(['toko_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
