{{-- resources/views/admin/users/show.blade.php --}}
@extends('admin.layout')
@section('title', 'User: ' . ($user->email ?? $user->phone))

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>User Profile</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar bg-light border rounded-circle mx-auto d-flex align-items-center justify-content-center"
                         style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr($user->email ?? $user->phone, 0, 2)) }}
                    </div>
                    <h5 class="mt-3">{{ $user->email ?? $user->phone }}</h5>
                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }} fs-6">
                        {{ ucfirst($user->role ?? 'user') }}
                    </span>
                </div>

                <hr>

                <p><strong>Email:</strong> {{ $user->email ?? '—' }}</p>
                <p><strong>Phone:</strong> {{ $user->phone ?? '—' }}</p>
                <p><strong>Status:</strong>
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
                <p><strong>Registered:</strong> {{ $user->created_at->format('d M Y, H:i') }}</p>
                <p><strong>Last Login:</strong>
                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                </p>

                <div class="mt-4">
                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-lg w-100 {{ $user->is_active ? 'btn-warning' : 'btn-success' }}">
                            {{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#devices">Devices ({{ $user->devices->count() }})</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#commands">Commands ({{ $user->commandsAsController->count() + $user->commandsAsTarget->count() }})</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#subscription">Subscription</a></li>
        </ul>

        <div class="tab-content">
            <!-- Devices Tab -->
            <div class="tab-pane fade show active" id="devices">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Device ID</th><th>Role</th><th>Paired</th><th>Last Seen</th></tr></thead>
                        <tbody>
                            @foreach($user->devices as $device)
                            <tr>
                                <td><code>{{ $device->device_id }}</code></td>
                                <td><span class="badge bg-{{ $device->role === 'controller' ? 'primary' : 'warning' }}">{{ ucfirst($device->role) }}</span></td>
                                <td>{{ $device->paired_to ? 'Yes' : 'No' }}</td>
                                <td>{{ $device->updated_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Commands Tab -->
            <div class="tab-pane fade" id="commands">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Action</th><th>Target</th><th>Status</th><th>Time</th></tr></thead>
                        <tbody>
                            @foreach($user->commandsAsController->merge($user->commandsAsTarget)->sortByDesc('created_at') as $cmd)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $cmd->action)) }}</td>
                                <td><small>{{ $cmd->toDevice?->device_id ?? '—' }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $cmd->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($cmd->status) }}
                                    </span>
                                </td>
                                <td>{{ $cmd->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Subscription Tab -->
            <div class="tab-pane fade" id="subscription">
                <div class="card">
                    <div class="card-body">
                        <h5>Subscription Plan</h5>
                        <p><strong>Plan:</strong> {{ $user->subscriptionPlan?->name ?? 'Free' }}</p>
                        <p><strong>Max Devices:</strong> {{ $user->subscriptionPlan?->max_devices ?? '1' }}</p>
                        <p><strong>Expires:</strong> {{ $user->subscription_expires_at?->format('d M Y') ?? 'Never' }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-success">Active</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
