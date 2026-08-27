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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->enum('skema_gaji_snapshot', ['harian', 'bulanan']);
            $table->decimal('jumlah_dasar', 12, 2)->default(0);
            $table->decimal('total_tunjangan', 12, 2)->default(0);
            $table->decimal('total_potongan', 12, 2)->default(0);
            $table->decimal('gaji_bersih', 12, 2)->default(0);
            $table->enum('status', ['draf', 'dibayar'])->default('draf')->index();
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'karyawan_id']);
            $table->index(['toko_id', 'periode_mulai', 'periode_selesai'], 'penggajian_toko_periode_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
