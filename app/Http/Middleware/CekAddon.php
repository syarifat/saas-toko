<?php

namespace App\Http\Middleware;

use App\Models\Toko;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekAddon
{
    /**
     * Pastikan toko pengguna telah mengaktifkan add-on tertentu.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $kode): Response
    {
        $toko = app()->bound('toko.aktif')
            ? app('toko.aktif')
            : Toko::find($request->user()?->toko_id);

        if ($toko === null || ! $toko->punyaAddon($kode)) {
            abort(403, 'Fitur ini membutuhkan add-on "'.$kode.'" yang belum diaktifkan untuk toko Anda.');
        }

        return $next($request);
    }
}
