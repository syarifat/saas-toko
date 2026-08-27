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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->foreign('toko_id')->references('id')->on('toko')->nullOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('pengguna')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropForeign(['toko_id']);
            $table->dropForeign(['dibuat_oleh']);
        });
    }
};
