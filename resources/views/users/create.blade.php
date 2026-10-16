@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
{{-- Formulir tambah pengguna beserta hobinya. --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h2 class="h5 mb-0">Tambah User</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (min 8 karakter)</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Daftar hobi dinamis, bisa tambah atau hapus baris. --}}
            <div class="mb-3">
                <label class="form-label">Daftar Hobi</label>
                <div id="hobby-list">
                    @php $oldHobbies = old('hobbies', [['name' => '']]); @endphp
                    @foreach ($oldHobbies as $i => $hobby)
                        <div class="input-group mb-2">
                            <input type="text" name="hobbies[{{ $i }}][name]" value="{{ $hobby['name'] ?? '' }}" placeholder="Nama hobi" class="form-control">
                            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove();">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('hobbies.*.name')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-hobby">+ Tambah Hobi</button>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('users.index') }}">Kembali</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Menambahkan baris input hobi baru.
    document.getElementById('add-hobby').addEventListener('click', function () {
        const list = document.getElementById('hobby-list');
        const index = list.children.length;
        const row = document.createElement('div');
        row.className = 'input-group mb-2';
        row.innerHTML = '<input type="text" name="hobbies[' + index + '][name]" placeholder="Nama hobi" class="form-control">'
            + '<button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove();">Hapus</button>';
        list.appendChild(row);
    });

    // Membuang baris hobi yang kosong sebelum dikirim.
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('#hobby-list .input-group').forEach(function (row) {
            const nameInput = row.querySelector('input[name$="[name]"]');
            if (nameInput && nameInput.value.trim() === '') row.remove();
        });
    });
</script>
@endpush
