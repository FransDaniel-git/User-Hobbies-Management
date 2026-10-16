@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
{{-- Halaman daftar pengguna beserta hobinya. --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Daftar User</h2>
        <a class="btn btn-primary btn-sm" href="{{ route('users.create') }}">+ Tambah User</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Hobi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            {{-- Menampilkan seluruh hobi milik pengguna. --}}
                            <td>
                                @if ($user->hobbies->isEmpty())
                                    <span class="text-muted fst-italic">-</span>
                                @else
                                    @foreach ($user->hobbies as $hobby)
                                        <span class="badge text-bg-info me-1">{{ $hobby->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            {{-- Tombol edit dan hapus untuk setiap pengguna. --}}
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a class="btn btn-sm btn-warning" href="{{ route('users.edit', $user) }}">Edit</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini beserta hobinya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Navigasi halaman pagination. --}}
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection
