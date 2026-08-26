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
        Schema::create('paket', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->tinyInteger('tingkat')->unique();
            $table->decimal('harga', 12, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('addon', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->decimal('harga', 12, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->foreignId('paket_id')->constrained('paket');
            $table->enum('status', ['coba_gratis', 'aktif', 'nonaktif'])->default('coba_gratis');
            $table->decimal('garis_lintang', 10, 7)->nullable();
            $table->decimal('garis_bujur', 10, 7)->nullable();
            $table->integer('radius_absensi')->default(100);
            $table->timestamp('langganan_berakhir_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('addon_toko', function (Blueprint $table) {
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addon')->cascadeOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamp('diaktifkan_pada')->nullable();
            $table->primary(['toko_id', 'addon_id']);
        });

        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->nullable()->constrained('toko')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('peran', ['superadmin', 'admin', 'karyawan']);
            $table->enum('sub_peran', ['kasir', 'gudang'])->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('toko');
        Schema::dropIfExists('addon_toko');
        Schema::dropIfExists('addon');
        Schema::dropIfExists('paket');
    }
};
