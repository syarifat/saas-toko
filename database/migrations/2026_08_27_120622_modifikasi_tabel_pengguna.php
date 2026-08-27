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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nama');
            $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            $table->enum('peran', ['superadmin', 'admin', 'karyawan'])->default('karyawan')->after('email')->index();
            $table->enum('sub_peran', ['kasir', 'gudang'])->nullable()->after('peran');
            $table->boolean('aktif')->default(true)->after('sub_peran')->index();
            $table->unsignedBigInteger('dibuat_oleh')->nullable()->after('aktif');
        });

        Schema::rename('users', 'pengguna');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('pengguna', 'users');

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nama', 'name');
            $table->dropColumn(['toko_id', 'peran', 'sub_peran', 'aktif', 'dibuat_oleh']);
        });
    }
};
