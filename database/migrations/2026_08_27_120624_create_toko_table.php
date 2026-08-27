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
        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->foreignId('paket_id')->constrained('paket')->restrictOnDelete();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->decimal('garis_lintang', 10, 7)->nullable();
            $table->decimal('garis_bujur', 10, 7)->nullable();
            $table->integer('radius_absensi')->default(100);
            $table->timestamp('langganan_berakhir_pada')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toko');
    }
};
