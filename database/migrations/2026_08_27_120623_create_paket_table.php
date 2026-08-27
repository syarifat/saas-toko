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
            $table->enum('jenis', ['preset_1', 'preset_2', 'preset_3', 'custom'])->index();
            $table->decimal('harga', 12, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
