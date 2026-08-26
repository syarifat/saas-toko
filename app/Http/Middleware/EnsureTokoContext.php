<?php

namespace App\Http\Middleware;

use App\Models\Toko;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokoContext
{
    /**
     * Mengikat toko aktif dari session ke container agar global scope bekerja.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isSuperadmin()) {
            return $next($request);
        }

        // Pengguna belum terhubung ke toko: biarkan lewat (fitur tenant akan dibatasi global scope).
        if (! $user->toko_id) {
            return $next($request);
        }

        if (! $user->aktif) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan.',
            ]);
        }

        $toko = Toko::findOrFail($user->toko_id);

        if ($toko->status === 'nonaktif') {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Langganan toko Anda sedang nonaktif. Hubungi superadmin.',
            ]);
        }

        app()->instance('toko.id', $toko->id);
        app()->instance('toko.aktif', $toko);
        $request->session()->put('toko_id', $toko->id);

        return $next($request);
    }
}
