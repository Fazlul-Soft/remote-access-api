@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    <h4 class="mb-4">
        {{ $selectedUserId ? 'User Statistics' : 'Global Statistics' }}
        @if ($selectedUserId)
            <small class="text-muted" style="font-size: 0.6em;">(Filtered)</small>
        @endif
    </h4>

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

    {{-- <h5 class="mt-4">Recent Commands <span class="badge bg-info" id="live-badge">LIVE</span></h5> --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h5>Recent Commands <span class="badge bg-info" id="live-badge">LIVE</span></h5>

        <div class="col-md-4">
            <select id="user-filter" class="form-select">
                <option value="">All Users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                        {{ $user->email ?? $user->phone }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="commands-table">
            <thead class="table-light">
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
                @forelse($recentCommands as $cmd)
                    <tr data-id="{{ $cmd->id }}">
                        <td>{{ $cmd->id }}</td>
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
                    <tr>
                        <td colspan="5" class="text-center text-muted">No commands yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-4">
            {{ $recentCommands->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userFilter = document.getElementById('user-filter');

            // 1. Handle Dropdown Change (Page Refresh with filter)
            userFilter.addEventListener('change', function() {
                const userId = this.value;
                window.location.href =
                    `{{ route('admin.dashboard') }}${userId ? '?user_id=' + userId : ''}`;
            });

            // 2. ---------- REAL-TIME COMMANDS ----------
            Echo.channel('admin-commands')
                .listen('.command.created', (e) => {
                    const selectedUser = userFilter.value;

                    // Only append row if "All Users" is selected OR if command belongs to the selected user
                    if (!selectedUser || e.command.user_id == selectedUser) {
                        const tbl = document.getElementById('commands-table').getElementsByTagName('tbody')[0];

                        // Clear "No commands yet" placeholder
                        if (tbl.rows[0] && tbl.rows[0].cells.length === 1) tbl.deleteRow(0);

                        const row = tbl.insertRow(0);
                        row.dataset.id = e.command.id;
                        row.innerHTML = `
                    <td>${e.command.from ?? '—'}</td>
                    <td>${e.command.to ?? '—'}</td>
                    <td>${e.command.action.replace(/_/g, ' ')}</td>
                    <td><span class="badge ${e.command.status === 'pending' ? 'bg-warning' : 'bg-success'}">${e.command.status}</span></td>
                    <td>just now</td>
                `;

                        if (tbl.rows.length > 20) tbl.deleteRow(20);
                    }
                })
                .listen('.command.updated', (e) => {
                    // Update the status badge if the command is already visible in the list
                    const row = document.querySelector(`#commands-table tr[data-id="${e.command.id}"]`);
                    if (row) {
                        const cells = row.cells;
                        cells[3].innerHTML =
                            `<span class="badge ${e.command.status === 'pending' ? 'bg-warning' : 'bg-success'}">${e.command.status}</span>`;
                        cells[4].textContent = 'just now';
                    }
                });

            // 3. ---------- REAL-TIME STATS ----------
            Echo.channel('admin-stats')
                .listen('.stats.updated', (e) => {
                    const selectedUser = userFilter.value;

                    if (!selectedUser) {
                        // GLOBAL MODE: Update all cards with broadcasted totals
                        document.querySelector('.stat-users').textContent = e.users;
                        document.querySelector('.stat-devices').textContent = e.devices;
                        document.querySelector('.stat-commands').textContent = e.commands;
                        document.querySelector('.stat-pending').textContent = e.pending;
                    } else {
                        // USER-FILTERED MODE: Only update if the event belongs to the current user
                        if (e.user_id == selectedUser) {
                            // Update Total Commands
                            let cmdEl = document.querySelector('.stat-commands');
                            cmdEl.textContent = parseInt(cmdEl.textContent) + 1;

                            // Update Pending if the new command is pending
                            if (e.status === 'pending') {
                                let pendEl = document.querySelector('.stat-pending');
                                pendEl.textContent = parseInt(pendEl.textContent) + 1;
                            }

                            // If your event includes total devices for this user, you can update that too:
                            // document.querySelector('.stat-devices').textContent = e.user_total_devices;
                        }
                    }
                });
        });
    </script>
@endsection
