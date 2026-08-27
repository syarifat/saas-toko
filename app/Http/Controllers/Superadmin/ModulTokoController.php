<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Services\ModulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ModulTokoController extends Controller
{
    public function aktifkan(Request $request, Toko $toko, string $kode): RedirectResponse
    {
        try {
            $denganDependency = $request->boolean('dengan_dependency', false);

            if ($denganDependency) {
                app(ModulService::class)->aktifkanDenganDependency($toko, $kode);
                $message = "Modul [{$kode}] beserta seluruh dependensinya berhasil diaktifkan.";
            } else {
                app(ModulService::class)->aktifkan($toko, $kode);
                $message = "Modul [{$kode}] berhasil diaktifkan.";
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function nonaktifkan(Toko $toko, string $kode): RedirectResponse
    {
        try {
            app(ModulService::class)->nonaktifkan($toko, $kode);

            return back()->with('success', "Modul [{$kode}] berhasil dinonaktifkan.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
