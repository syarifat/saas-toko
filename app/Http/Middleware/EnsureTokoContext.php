<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokoContext
{
    /**
     * Pastikan pengguna tenant memiliki toko_id yang valid.
     * Superadmin dikecualikan dari kewajiban toko_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        if ($pengguna && ! $pengguna->isSuperadmin() && ! $pengguna->toko_id) {
            abort(403, 'Konteks toko tidak ditemukan.');
        }

        return $next($request);
    }
}
