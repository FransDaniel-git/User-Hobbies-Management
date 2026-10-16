@extends('layouts.app')

@section('title', 'Login')

@section('content')
{{-- Halaman login, meminta token JWT ke API lalu menyimpannya di browser. --}}
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0">Login</h2>
            </div>
            <div class="card-body">
                <div id="login-error" class="alert alert-danger d-none" role="alert"></div>

                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="login-btn">Login</button>
                </form>

                <p class="mt-3 mb-0 text-center">
                    Belum punya akun? <a href="{{ route('register') }}">Register</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('login-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('login-btn');
        const errBox = document.getElementById('login-error');
        btn.disabled = true;
        errBox.classList.add('d-none');

        try {
            // Meminta token JWT ke API login.
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }),
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Login gagal.');
            }

            // Menyimpan token agar dipakai middleware (cookie) dan request JS (localStorage).
            localStorage.setItem('jwt_token', data.access_token);
            document.cookie = 'jwt_token=' + data.access_token + '; path=/; max-age=3600';
            window.location.href = '/users';
        } catch (err) {
            errBox.textContent = err.message;
            errBox.classList.remove('d-none');
            btn.disabled = false;
        }
    });
</script>
@endpush
