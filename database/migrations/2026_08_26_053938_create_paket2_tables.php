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
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('pemasok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
            $table->foreignId('pemasok_id')->nullable()->constrained('pemasok')->nullOnDelete();
            $table->string('sku');
            $table->string('nama');
            $table->decimal('harga_beli', 14, 2)->default(0);
            $table->decimal('harga_jual', 14, 2)->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->timestamps();

            $table->unique(['toko_id', 'sku']);
        });

        Schema::create('gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->string('nama');
            $table->enum('jenis', ['etalase', 'gudang'])->default('etalase');
            $table->timestamps();
        });

        Schema::create('stok_gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->integer('jumlah')->default(0);

            $table->unique(['produk_id', 'gudang_id']);
        });

        Schema::create('pergerakan_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
            $table->foreignId('gudang_tujuan_id')->nullable()->constrained('gudang')->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar', 'transfer', 'penjualan', 'opname']);
            $table->integer('jumlah');
            $table->string('referensi_tipe')->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('pengguna_id')->constrained('pengguna');
            $table->timestamps();
        });

        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna');
            $table->foreignId('gudang_id')->constrained('gudang');
            $table->date('tanggal_transaksi');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('diskon', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('jumlah_bayar', 14, 2)->default(0);
            $table->decimal('kembalian', 14, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'qris', 'transfer'])->default('tunai');
            $table->timestamps();
        });

        Schema::create('item_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk');
            $table->string('nama_produk');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('harga_beli_snapshot', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transaksi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('pergerakan_stok');
        Schema::dropIfExists('stok_gudang');
        Schema::dropIfExists('gudang');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('pemasok');
        Schema::dropIfExists('kategori');
    }
};
