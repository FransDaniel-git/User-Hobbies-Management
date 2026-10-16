<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'User Hobbies Management')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    {{-- Navigasi atas, tombol Login/Logout diisi via JS sesuai token. --}}
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('users.index') }}">User Hobbies Management</a>
            <span id="nav-auth"></span>
        </div>
    </nav>

    <div class="container pb-5">
        {{-- Notifikasi sukses dari redirect controller. --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Isi halaman dari masing-masing view. --}}
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menampilkan tombol Login atau Logout sesuai ada tidaknya token JWT.
        (function () {
            const nav = document.getElementById('nav-auth');
            if (!nav) return;
            if (localStorage.getItem('jwt_token')) {
                nav.innerHTML = '<button class="btn btn-outline-light btn-sm" onclick="jwtLogout()">Logout</button>';
            } else {
                nav.innerHTML = '<a class="btn btn-outline-light btn-sm" href="/login">Login</a>';
            }
        })();

        // Menghapus token di API (blacklist), browser, lalu kembali ke login.
        async function jwtLogout() {
            const token = localStorage.getItem('jwt_token');
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'Authorization': 'Bearer ' + token},
                });
            } catch (e) {}
            localStorage.removeItem('jwt_token');
            document.cookie = 'jwt_token=; path=/; max-age=0';
            window.location.href = '/login';
        }
    </script>
    @stack('scripts')
</body>
</html>
