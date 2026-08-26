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
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('kode_karyawan')->unique();
            $table->string('nama');
            $table->string('posisi')->nullable();
            $table->enum('skema_gaji', ['harian', 'bulanan'])->default('harian');
            $table->decimal('tarif_harian', 12, 2)->default(0);
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->date('tanggal_masuk');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_keluar')->nullable();
            $table->decimal('lintang_masuk', 10, 7)->nullable();
            $table->decimal('bujur_masuk', 10, 7)->nullable();
            $table->decimal('lintang_keluar', 10, 7)->nullable();
            $table->decimal('bujur_keluar', 10, 7)->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->enum('status', ['tepat_waktu', 'telat'])->default('tepat_waktu');
            $table->integer('menit_telat')->default(0);
            $table->integer('menit_lembur')->default(0);

            $table->unique(['karyawan_id', 'tanggal']);
        });

        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->enum('skema_gaji_snapshot', ['harian', 'bulanan']);
            $table->decimal('jumlah_dasar', 14, 2)->default(0);
            $table->integer('jumlah_hadir')->default(0);
            $table->decimal('total_tunjangan', 14, 2)->default(0);
            $table->decimal('total_potongan', 14, 2)->default(0);
            $table->decimal('gaji_bersih', 14, 2)->default(0);
            $table->enum('status', ['draf', 'dibayar'])->default('draf');
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('komponen_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('penggajian_id')->constrained('penggajian')->cascadeOnDelete();
            $table->enum('jenis', ['tunjangan', 'potongan']);
            $table->string('nama');
            $table->decimal('nominal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_gaji');
        Schema::dropIfExists('penggajian');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('karyawan');
    }
};
