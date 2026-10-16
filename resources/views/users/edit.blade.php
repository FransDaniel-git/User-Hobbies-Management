@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
{{-- Formulir edit pengguna, id hobi lama dikirim agar bisa diperbarui. --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h2 class="h5 mb-0">Edit User</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (kosongkan jika tidak diganti)</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Baris yang dihapus di sini akan ikut terhapus di database. --}}
            <div class="mb-3">
                <label class="form-label">Daftar Hobi (baris yang dihapus akan ikut terhapus)</label>
                <div id="hobby-list">
                    @php
                        $rows = old('hobbies', $user->hobbies->map(fn ($h) => ['id' => $h->id, 'name' => $h->name])->all());
                        if (empty($rows)) $rows = [['name' => '']];
                    @endphp
                    @foreach ($rows as $i => $hobby)
                        <div class="input-group mb-2">
                            @if (isset($hobby['id']))
                                <input type="hidden" name="hobbies[{{ $i }}][id]" value="{{ $hobby['id'] }}">
                            @endif
                            <input type="text" name="hobbies[{{ $i }}][name]" value="{{ $hobby['name'] ?? '' }}" placeholder="Nama hobi" class="form-control">
                            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove();">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('hobbies.*.name')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                @error('hobbies.*.id')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-hobby">+ Tambah Hobi</button>
            </div>

            <button type="submit" class="btn btn-primary">Perbarui</button>
            <a class="btn btn-secondary" href="{{ route('users.index') }}">Kembali</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Menambahkan baris input hobi baru (tanpa id, dianggap hobi baru).
    document.getElementById('add-hobby').addEventListener('click', function () {
        const list = document.getElementById('hobby-list');
        const index = list.children.length + Date.now() % 100000;
        const row = document.createElement('div');
        row.className = 'input-group mb-2';
        row.innerHTML = '<input type="text" name="hobbies[' + index + '][name]" placeholder="Nama hobi" class="form-control">'
            + '<button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove();">Hapus</button>';
        list.appendChild(row);
    });

    // Membuang baris hobi baru yang kosong, hobi lama kosong tetap dikirim.
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('#hobby-list .input-group').forEach(function (row) {
            const nameInput = row.querySelector('input[name$="[name]"]');
            const idInput = row.querySelector('input[name$="[id]"]');
            if (nameInput && nameInput.value.trim() === '' && !idInput) row.remove();
        });
    });
</script>
@endpush
