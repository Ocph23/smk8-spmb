<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPanitia
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->isAdminOrPanitia()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk panitia atau admin.');
        }

        return $next($request);
    }
}
