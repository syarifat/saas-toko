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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->enum('jenis', ['upgrade_paket', 'aktivasi_addon'])->index();
            $table->foreignId('paket_id')->nullable()->constrained('paket')->nullOnDelete();
            $table->foreignId('modul_id')->nullable()->constrained('modul')->nullOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti_transfer');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->index();
            $table->text('catatan_penolakan')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
