@extends('admin.layout')
@section('title', 'Devices')

@section('content')
<h4 class="mb-4">All Devices ({{ \App\Models\Device::count() }})</h4>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th>Device ID</th>
                <th>User</th>
                <th>Role</th>
                <th>Paired To</th>
                <th>Status</th>
                <th>Last Seen</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Device::with('user')->latest()->get() as $device)
                <tr>
                    <td><code>{{ $device->device_id }}</code></td>
                    <td>{{ $device->user->email ?? $device->user->phone ?? '—' }}</td>
                    <td><span class="badge bg-{{ $device->role === 'controller' ? 'primary' : 'warning' }}">{{ ucfirst($device->role) }}</span></td>
                    <td>{{ $device->paired_to ? 'Yes' : '—' }}</td>
                    <td>{{ $device->fcm_token ? '<span class="text-success">Online</span>' : '<span class="text-danger">Offline</span>' }}</td>
                    <td>{{ $device->updated_at->diffForHumans() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
