<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 15 user beserta hobinya. Password semua: password123.
        $samples = [
            ['name' => 'Bima', 'email' => 'bima@example.com', 'hobbies' => ['Membaca', 'Ngoding']],
            ['name' => 'Budi', 'email' => 'budi@example.com', 'hobbies' => ['Membaca', 'Bersepeda']],
            ['name' => 'Siti', 'email' => 'siti@example.com', 'hobbies' => ['Memasak', 'Berkebun', 'Melukis']],
            ['name' => 'Andi', 'email' => 'andi@example.com', 'hobbies' => ['Gaming']],
            ['name' => 'Dewi', 'email' => 'dewi@example.com', 'hobbies' => ['Menari', 'Menyanyi']],
            ['name' => 'Joko', 'email' => 'joko@example.com', 'hobbies' => ['Futsal', 'Jogging', 'Renang']],
            ['name' => 'Rina', 'email' => 'rina@example.com', 'hobbies' => ['Fotografi']],
            ['name' => 'Agus', 'email' => 'agus@example.com', 'hobbies' => ['Memancing', 'Berkemah']],
            ['name' => 'Maya', 'email' => 'maya@example.com', 'hobbies' => ['Yoga', 'Mediasi', 'Jalan Pagi']],
            ['name' => 'Rudi', 'email' => 'rudi@example.com', 'hobbies' => ['Biliar']],
            ['name' => 'Nina', 'email' => 'nina@example.com', 'hobbies' => ['Baking', 'Dekorasi']],
            ['name' => 'Eko', 'email' => 'eko@example.com', 'hobbies' => ['Sepak Bola', 'Badminton']],
            ['name' => 'Fitri', 'email' => 'fitri@example.com', 'hobbies' => ['Menulis', 'Blogging', 'Puisi']],
            ['name' => 'Hendra', 'email' => 'hendra@example.com', 'hobbies' => ['Otomotif']],
            ['name' => 'Lina', 'email' => 'lina@example.com', 'hobbies' => ['Merajut', 'Sulaman']],
        ];

        foreach ($samples as $sample) {
            $user = User::firstOrCreate(
                ['email' => $sample['email']],
                ['name' => $sample['name'], 'password' => 'password123']
            );

            // Hanya isi hobi jika user belum punya hobi (aman di-run ulang).
            if ($user->hobbies()->count() === 0) {
                $user->hobbies()->createMany(
                    collect($sample['hobbies'])->map(fn (string $name) => ['name' => $name])->all()
                );
            }
        }
    }
}
