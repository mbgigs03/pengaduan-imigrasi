<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Penggunaan di route: ->middleware('role:tikkim') atau ->middleware('role:tikkim,seksi')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        // Pastikan user sudah login dan punya profile
        if (!$user || !$user->profile) {
            abort(403, 'Akses ditolak.');
        }

        // Cek apakah role user ada di daftar role yang diizinkan
        if (!in_array($user->profile->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}