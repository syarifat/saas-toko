<?php

namespace Database\Seeders;

use App\Models\Modul;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KetergantunganModulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dependencies = [
            'penjualan_ringkas' => ['master_produk'],
            'rekap_keuangan' => ['penjualan_ringkas'],
            'stok_gudang' => ['master_produk'],
            'kasir_pos' => ['master_produk', 'stok_gudang'],
            'stock_alert' => ['stok_gudang'],
            'stok_opname' => ['stok_gudang'],
            'barang_masuk' => ['stok_gudang'],
            'kartu_stok' => ['stok_gudang'],
            'laporan_hpp' => ['kasir_pos'],
            'multi_gudang' => ['stok_gudang'],
            'transfer_gudang' => ['multi_gudang'],
            'absensi' => ['karyawan'],
            'payroll' => ['absensi'],
        ];

        foreach ($dependencies as $kodeModul => $requiresList) {
            $modul = Modul::where('kode', $kodeModul)->first();

            if (! $modul) {
                continue;
            }

            foreach ($requiresList as $kodeReq) {
                $req = Modul::where('kode', $kodeReq)->first();

                if (! $req) {
                    continue;
                }

                DB::table('ketergantungan_modul')->updateOrInsert([
                    'modul_id' => $modul->id,
                    'requires_modul_id' => $req->id,
                ]);
            }
        }
    }
}
