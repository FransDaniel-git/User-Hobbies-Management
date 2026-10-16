# User Hobbies Management

Aplikasi CRUD User beserta Hobinya (relasi one-to-many) dengan dua antarmuka:
Blade (tampilan web) dan REST API yang diproteksi JWT Authentication.
Dibuat untuk memenuhi technical test Laravel.

## Fitur

- **Blade (web, Bootstrap 5):** login, register, tabel daftar user + hobinya,
  form tambah/edit user dengan daftar hobi dinamis, tombol Edit & Delete.
- **REST API (JSON):** register, login, profile, refresh, logout,
  serta CRUD penuh user + hobi.
- **Keamanan:** seluruh endpoint `/api/users/*` wajib token JWT
  (`Authorization: Bearer <token>`). Halaman Blade juga wajib login
  (token disimpan di cookie via JavaScript).
- **Validasi kepemilikan:** id hobi yang dikirim saat update harus milik
  user target yang sedang diubah (tidak bisa mencampur hobi user lain).

## Teknologi

- PHP ^8.2, Laravel ^12, MySQL
- `tymon/jwt-auth` ^2.3 untuk JWT Authentication
- Blade + Bootstrap 5 (CDN, tanpa build step)

## Cara Setup

Syarat: PHP 8.2+, Composer, dan MySQL berjalan.

```bash
# 1. Install dependency
composer install

# 2. Salin konfigurasi environment
cp .env.example .env

# 3. Sesuaikan database di .env (contoh MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=user_hobbies_management
DB_USERNAME=root
DB_PASSWORD=

# 4. Generate application key & JWT secret
php artisan key:generate
php artisan jwt:secret

# 5. Migrasi + seeding (15 user beserta hobinya)
php artisan migrate --seed

# 6. Jalankan server
php artisan serve
```

Buka `http://127.0.0.1:8000` (otomatis ke halaman login bila belum login).

## Akun Demo (password semua: `password123`)

| Email | Keterangan |
|---|---|
| `bima@example.com` | User pertama, cocok untuk login awal |
| `budi@example.com`, `siti@example.com`, dst. | 14 user sampel lain beserta hobinya |

Atau daftar akun baru lewat halaman Register / `POST /api/register`.

## Endpoint API

Base URL: `http://127.0.0.1:8000/api`. Header wajib:
`Accept: application/json` dan `Content-Type: application/json`.

| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| POST | `/register` | Publik | Daftar + langsung dapat token |
| POST | `/login` | Publik | Login, dapat `access_token` |
| GET | `/me` | Bearer | Profil user login |
| POST | `/refresh` | Bearer | Perbarui token |
| POST | `/logout` | Bearer | Blacklist token |
| GET | `/users` | Bearer | List user + hobi (paginate 10) |
| POST | `/users` | Bearer | Buat user + hobi |
| GET | `/users/{id}` | Bearer | Detail user + hobi |
| PUT/PATCH | `/users/{id}` | Bearer | Update user + sinkron hobi |
| DELETE | `/users/{id}` | Bearer | Hapus user + hobinya |

Contoh body create/update (hobi ber-`id` = update, tanpa `id` = baru,
yang tidak dikirim = dihapus):

```json
{
  "name": "Rina",
  "email": "rina@example.com",
  "password": "password123",
  "hobbies": [{ "id": 7, "name": "Renang" }, { "name": "Yoga" }]
}
```

## Struktur Penting

- `app/Http/Controllers/Web/UserController.php` - CRUD Blade (return view/redirect)
- `app/Http/Controllers/Api/UserController.php` - CRUD API (return JSON)
- `app/Http/Controllers/Api/AuthController.php` - register/login/me/logout/refresh
- `app/Http/Middleware/EnsureJwtToken.php` - proteksi halaman Blade via cookie JWT
- `app/Models/User.php`, `app/Models/Hobby.php` - relasi `hasMany` / `belongsTo`
- `database/seeders/DatabaseSeeder.php` - 15 user + 30 hobi
- `routes/web.php`, `routes/api.php` - definisi route web & API
