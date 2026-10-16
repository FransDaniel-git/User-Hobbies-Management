<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    // Mendaftarkan pengguna baru.
    public function register(Request $request): JsonResponse
    {
        // Memvalidasi data yang dikirim.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Membuat pengguna baru.
        $user = User::create($data);

        // Membuat token JWT untuk pengguna yang baru dibuat.
        $token = $this->guard()->login($user);

        return $this->respondWithToken($token);
    }

    // Melakukan login.
    public function login(Request $request): JsonResponse
    {
        // Memvalidasi data yang dikirim.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Mencoba autentikasi, mengembalikan 401 jika gagal.
        if (! $token = $this->guard()->attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        return $this->respondWithToken($token);
    }

    // Mengembalikan data pengguna yang sedang login.
    public function me(): JsonResponse
    {
        return response()->json($this->guard()->user());
    }

    // Melakukan logout.
    public function logout(): JsonResponse
    {
        // Membatalkan token yang sedang dipakai.
        $this->guard()->logout();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    // Memperbarui token yang hampir kedaluwarsa dengan token baru.
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    // Mengambil guard JWT dengan tipe yang tepat agar IDE tidak warning.
    protected function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    // Response berisi token dan data pengguna.
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $this->guard()->user(),
        ]);
    }
}
