<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Menampilkan daftar pengguna beserta hobinya dengan pagination.
    public function index(): JsonResponse
    {
        // Mengambil pengguna beserta hobinya (10 per halaman & diurutkan ascending berdasarkan ID).
        $users = User::with('hobbies')->orderBy('id')->paginate(10);

        return response()->json($users);
    }

    // Membuang baris hobi yang namanya kosong agar hobi bersifat opsional.
    protected function filterEmptyHobbies(Request $request): void
    {
        $hobbies = collect($request->input('hobbies', []))
            ->map(function ($hobby) {
                $hobby = is_array($hobby) ? $hobby : ['name' => $hobby];

                return [
                    'id' => $hobby['id'] ?? null,
                    'name' => trim((string) ($hobby['name'] ?? '')),
                ];
            })
            ->filter(fn (array $hobby) => $hobby['name'] !== '' || ! empty($hobby['id']))
            ->values()
            ->all();

        $request->merge(['hobbies' => $hobbies]);
    }

    // Menyimpan pengguna baru beserta daftar hobinya.
    public function store(Request $request): JsonResponse
    {
        // Membuang baris hobi yang kosong sebelum validasi.
        $this->filterEmptyHobbies($request);

        // Memvalidasi data yang dikirim.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*.name' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        // Menggunakan transaction agar seluruh operasi berhasil atau gagal bersama.
        $user = DB::transaction(function () use ($data) {
            // Menyimpan pengguna baru ke database.
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Menyiapkan daftar hobi.
            $hobbies = [];
            $now = now();

            foreach ($data['hobbies'] ?? [] as $hobby) {
                $hobbies[] = [
                    'user_id' => $user->id,
                    'name' => $hobby['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Menyimpan seluruh hobi.
            if (! empty($hobbies)) {
                DB::table('hobbies')->insert($hobbies);
            }

            return $user;
        });

        // Mengambil ulang pengguna beserta hobinya untuk response.
        $user->load('hobbies');

        return response()->json($user, 201);
    }

    // Menampilkan satu pengguna beserta hobinya.
    public function show(User $user): JsonResponse
    {
        // Mengambil seluruh hobi milik pengguna.
        $user->load('hobbies');

        return response()->json($user);
    }

    // Memperbarui data pengguna beserta daftar hobinya.
    public function update(Request $request, User $user): JsonResponse
    {
        // Membuang baris hobi yang kosong sebelum validasi.
        $this->filterEmptyHobbies($request);

        // Memvalidasi data yang dikirim.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'hobbies' => ['sometimes', 'array'],
            'hobbies.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('hobbies', 'id')->where('user_id', $user->id)],
            'hobbies.*.name' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        // Menggunakan transaction agar seluruh operasi berhasil atau gagal bersama.
        DB::transaction(function () use ($data, $user) {
            // Menyusun data pengguna yang akan diperbarui.
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Hanya perbarui password jika diisi.
            if (! empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            // Memperbarui data pengguna.
            $user->update($updateData);

            // Mengambil ID hobi yang dikirim dalam request.
            $hobbyIds = collect($data['hobbies'] ?? [])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Menghapus hobi yang tidak ada dalam request (tidak dikirim lagi).
            $user->hobbies()->whereNotIn('id', $hobbyIds)->delete();

            // Memperbarui atau membuat hobi baru.
            foreach ($data['hobbies'] ?? [] as $hobbyData) {
                if (isset($hobbyData['id'])) {
                    // Memperbarui hobi yang sudah ada.
                    $user->hobbies()->where('id', $hobbyData['id'])->update([
                        'name' => $hobbyData['name'],
                    ]);
                } else {
                    // Membuat hobi baru untuk pengguna.
                    $user->hobbies()->create([
                        'name' => $hobbyData['name'],
                    ]);
                }
            }
        });

        // Mengambil ulang pengguna beserta hobinya untuk response.
        $user->load('hobbies');

        return response()->json($user);
    }

    // Menghapus pengguna beserta seluruh hobinya.
    public function destroy(User $user): JsonResponse
    {
        // Menghapus pengguna beserta hobinya melalui ON DELETE CASCADE.
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
