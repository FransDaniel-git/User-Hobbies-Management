<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    // Menampilkan seluruh pengguna beserta daftar hobinya.
    public function index(): View
    {
        // Mengambil pengguna beserta hobinya (10 per halaman & diurutkan ascending berdasarkan ID).
        $users = User::with('hobbies')->orderBy('id')->simplePaginate(10);

        return view('users.index', ['users' => $users]);
    }

    // Menampilkan halaman formulir untuk menambahkan pengguna.
    public function create(): View
    {
        return view('users.create');
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
    public function store(Request $request): RedirectResponse
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
        DB::transaction(function () use ($data) {
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
        });

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    // Menampilkan halaman formulir untuk mengedit pengguna.
    public function edit(User $user): View
    {
        // Mengambil seluruh hobi milik pengguna.
        $user->load('hobbies');

        return view('users.edit', ['user' => $user]);
    }

    // Memperbarui data pengguna beserta daftar hobinya.
    public function update(Request $request, User $user): RedirectResponse
    {
        // Membuang baris hobi yang kosong sebelum validasi.
        $this->filterEmptyHobbies($request);

        // Memvalidasi data yang dikirim, id hobi harus milik user target ini.
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

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    // Menghapus pengguna beserta seluruh hobinya.
    public function destroy(User $user): RedirectResponse
    {
        // Menghapus pengguna beserta hobinya melalui ON DELETE CASCADE.
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
