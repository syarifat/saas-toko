<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Peran
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('peran:superadmin')
     *        ->middleware('peran:admin,karyawan')
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->peran, $peran, true)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
