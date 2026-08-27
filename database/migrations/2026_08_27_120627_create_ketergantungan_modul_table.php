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
        Schema::create('ketergantungan_modul', function (Blueprint $table) {
            $table->foreignId('modul_id')->constrained('modul')->cascadeOnDelete();
            $table->foreignId('requires_modul_id')->constrained('modul')->cascadeOnDelete();
            $table->primary(['modul_id', 'requires_modul_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ketergantungan_modul');
    }
};
