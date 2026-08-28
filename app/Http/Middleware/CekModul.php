<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekModul
{
    /**
     * Usage: ->middleware('modul:kasir_pos')
     *
     * Redirect ke dashboard dengan flash message error jika modul belum aktif.
     */
    public function handle(Request $request, Closure $next, string $kodeModul): Response
    {
        $pengguna = $request->user();

        if (! $pengguna?->toko) {
            abort(403, 'Konteks toko tidak valid.');
        }

        if (! $pengguna->toko->modulAktif($kodeModul)) {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur ini tidak tersedia di paket Anda saat ini.');
        }

        if (! $pengguna->punyaAksesModul($kodeModul)) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki hak akses untuk membuka menu ini.');
        }

        return $next($request);
    }
}
