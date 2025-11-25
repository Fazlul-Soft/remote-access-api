@extends('admin.layout')
@section('title', 'Commands History')

@section('content')
<h4 class="mb-4">Recent Commands</h4>

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>To</th>
                <th>Action</th>
                <th>Status</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse(\App\Models\Command::with(['fromDevice', 'toDevice'])->latest()->take(50)->get() as $cmd)
                <tr>
                    <td>{{ $cmd->id }}</td>
                    <td><small>{{ $cmd->fromDevice?->device_id }}</small></td>
                    <td><small>{{ $cmd->toDevice?->device_id }}</small></td>
                    <td>{{ ucwords(str_replace('_', ' ', $cmd->action)) }}</td>
                    <td>
                        <span class="badge bg-{{ $cmd->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($cmd->status) }}
                        </span>
                    </td>
                    <td>{{ $cmd->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No commands yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
