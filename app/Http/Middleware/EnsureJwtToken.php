<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJwtToken
{
    // Memastikan request web membawa token JWT yang valid (via cookie jwt_token atau header Bearer).
    public function handle(Request $request, Closure $next): Response
    {
        // Mengambil token dari cookie (diisi JS saat login) atau header Authorization.
        $token = $request->cookie('jwt_token') ?? $request->bearerToken();

        if (! $token) {
            return $this->unauthenticated($request);
        }

        try {
            // Memvalidasi token ke guard api (JWT). Gagal = null/exception.
            $guard = auth('api');
            $guard->setToken($token);
            $user = $guard->authenticate();
        } catch (\Throwable $e) {
            $user = null;
        }

        if (! $user) {
            return $this->unauthenticated($request);
        }

        // Mendaftarkan user agar auth()->user() bisa dipakai di Blade.
        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);

        return $next($request);
    }

    // Menolak akses: JSON 401 untuk API, redirect ke login untuk browser.
    protected function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
