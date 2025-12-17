<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin – @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Pass Laravel CSRF & Reverb env to JS
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
</head>

<body class="bg-light">

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white vh-100 p-3 flex-shrink-0" style="width:260px;">
            <h4 class="text-center mb-4">Remote Admin</h4>
            <div class="nav flex-column">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'bg-primary' : '' }}">Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link text-white">Users</a>
                <!-- In your admin layout sidebar -->
                <a href="{{ route('admin.subscription-plans.index') }}"
                    class="nav-link text-white {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-primary' : '' }}">
                    Subscription Plans
                </a>
                <a href="{{ route('admin.payment-methods') }}"
                    class="nav-link text-white {{ request()->routeIs('admin.payment-methods.*') ? 'bg-primary' : '' }}">
                    Payment Method
                </a>
                <a href="{{ route('admin.payments') }}"
                    class="nav-link text-white {{ request()->routeIs('admin.payments*') ? 'bg-primary' : '' }}">
                    Payment Verification
                </a>
                <a href="{{ route('admin.devices.index') }}" class="nav-link text-white">Devices</a>
                <a href="{{ route('admin.commands.index') }}" class="nav-link text-white">Commands</a>
            </div>
        </nav>

        <!-- Main -->
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>@yield('title')</h2>
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

</body>

</html>
