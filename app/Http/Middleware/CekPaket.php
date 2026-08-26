<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPaket
{
    /**
     * Pastikan toko pengguna memiliki paket minimal tingkat tertentu.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, int $tingkat): Response
    {
        $toko = app()->bound('toko.aktif')
            ? app('toko.aktif')
            : Toko::find($request->user()?->toko_id);

        if ($toko === null || ! $toko->setidaknyaPaket($tingkat)) {
            abort(403, 'Fitur ini membutuhkan paket yang lebih tinggi. Silakan upgrade paket langganan Anda.');
        }

        return $next($request);
    }
}
