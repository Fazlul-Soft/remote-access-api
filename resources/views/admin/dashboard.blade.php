@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4" id="stats-row">
    <div class="col-lg-3 col-md-6">
        <div class="card text-center border-primary h-100">
            <div class="card-body">
                <h5 class="card-title text-primary">Users</h5>
                <h3 class="stat-users">{{ $stats['total_users'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center border-success h-100">
            <div class="card-body">
                <h5 class="card-title text-success">Devices</h5>
                <h3 class="stat-devices">{{ $stats['total_devices'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center border-warning h-100">
            <div class="card-body">
                <h5 class="card-title text-warning">Commands</h5>
                <h3 class="stat-commands">{{ $stats['total_commands'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center border-danger h-100">
            <div class="card-body">
                <h5 class="card-title text-danger">Pending</h5>
                <h3 class="stat-pending">{{ $stats['pending_commands'] }}</h3>
            </div>
        </div>
    </div>
</div>

<h5 class="mt-4">Recent Commands <span class="badge bg-info" id="live-badge">LIVE</span></h5>
<div class="table-responsive">
    <table class="table table-hover align-middle" id="commands-table">
        <thead class="table-light">
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Action</th>
                <th>Status</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentCommands as $cmd)
                <tr data-id="{{ $cmd->id }}">
                    <td>{{ $cmd->fromDevice->device_id ?? '—' }}</td>
                    <td>{{ $cmd->toDevice->device_id ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $cmd->action)) }}</td>
                    <td>
                        <span class="badge {{ $cmd->status === 'pending' ? 'bg-warning' : 'bg-success' }}">
                            {{ ucfirst($cmd->status) }}
                        </span>
                    </td>
                    <td>{{ $cmd->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No commands yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ---------- REAL-TIME STATS ----------
    Echo.channel('admin-stats')
        .listen('.stats.updated', (e) => {
            document.querySelector('.stat-users').textContent   = e.users;
            document.querySelector('.stat-devices').textContent = e.devices;
            document.querySelector('.stat-commands').textContent = e.commands;
            document.querySelector('.stat-pending').textContent = e.pending;
        });

    // ---------- REAL-TIME COMMANDS ----------
    Echo.channel('admin-commands')
        .listen('.command.created', (e) => {
            const tbl = document.getElementById('commands-table').getElementsByTagName('tbody')[0];
            const row = tbl.insertRow(0);
            row.dataset.id = e.command.id;

            row.innerHTML = `
                <td>${e.command.from ?? '—'}</td>
                <td>${e.command.to ?? '—'}</td>
                <td>${e.command.action.replace(/_/g, ' ')}</td>
                <td><span class="badge ${e.command.status === 'pending' ? 'bg-warning' : 'bg-success'}">${e.command.status}</span></td>
                <td>just now</td>
            `;
            // keep only last 20 rows
            if (tbl.rows.length > 20) tbl.deleteRow(20);
        })
        .listen('.command.updated', (e) => {
            const row = document.querySelector(`#commands-table tr[data-id="${e.command.id}"]`);
            if (row) {
                const cells = row.cells;
                cells[3].innerHTML = `<span class="badge ${e.command.status === 'pending' ? 'bg-warning' : 'bg-success'}">${e.command.status}</span>`;
                cells[4].textContent = 'just now';
            }
        });
});
</script>
@endsection
