@extends('layouts.app')

@section('title', 'Register')

@section('content')
{{-- Halaman register, mendaftar ke API lalu langsung login otomatis. --}}
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0">Register</h2>
            </div>
            <div class="card-body">
                <div id="register-error" class="alert alert-danger d-none" role="alert"></div>

                <form id="register-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (min 8 karakter)</label>
                        <input type="password" id="password" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="register-btn">Register</button>
                </form>

                <p class="mt-3 mb-0 text-center">
                    Sudah punya akun? <a href="{{ route('login') }}">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('register-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('register-btn');
        const errBox = document.getElementById('register-error');
        btn.disabled = true;
        errBox.classList.add('d-none');

        try {
            // Mendaftarkan akun sekaligus meminta token JWT ke API register.
            const res = await fetch('/api/register', {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }),
            });
            const data = await res.json();

            if (!res.ok) {
                const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                throw new Error(msgs || 'Register gagal.');
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
