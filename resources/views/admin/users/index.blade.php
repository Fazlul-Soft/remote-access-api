@extends('admin.layout')
@section('title', 'Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>All Users</h4>
        <span class="badge bg-primary fs-6">{{ \App\Models\User::count() }} Total</span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Devices</th>
                    <th>Registered</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                {{-- resources/views/admin/users/index.blade.php --}}
                @foreach (\App\Models\User::latest()->get() as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                                {{ ucfirst($user->role ?? 'user') }}
                            </span>
                        </td>

                        <td>{{ $user->devices->count() }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                {{-- <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-sm {{ $user->is_active ? 'btn-success' : 'btn-danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form> --}}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
