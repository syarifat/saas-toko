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
            $table->foreignId('pengguna_id')->constrained('pengguna');
            $table->enum('jenis', ['paket', 'addon']);
            $table->foreignId('paket_id')->nullable()->constrained('paket')->nullOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained('addon')->nullOnDelete();
            $table->integer('jumlah_bulan')->default(1);
            $table->decimal('nominal', 14, 2);
            $table->string('bukti_transfer')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_tenant')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();
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
