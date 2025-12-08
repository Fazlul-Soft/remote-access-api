{{-- resources/views/admin/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Remote Access</title>
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script> --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0e1225 0%, #180927 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .login-container {
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .form-floating>.form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            height: 58px;
            padding: 1rem 1rem;
        }

        .form-floating>.form-control:focus,
        .form-floating>.form-control:not(:placeholder-shown) {
            background: rgba(255, 255, 255, 0.2);
            border-color: #667eea;
        }

        .form-floating>label {
            color: rgba(255, 255, 255, 0.7);
            padding: 1rem 1rem;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #667eea;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
            background: transparent;
        }

        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center login-container">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-sm-8">
                <div class="glass-card p-5">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <div class="logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="#667eea"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM6.5 13a.5.5 0 0 1-.5-.5V7a.5.5 0 0 1 1 0v5.5a.5.5 0 0 1-.5.5zm3 0a.5.5 0 0 1-.5-.5V7a.5.5 0 0 1 1 0v5.5a.5.5 0 0 1-.5.5z" />
                            </svg>
                        </div>
                        <h3 class="text-white fw-bold">Admin Portal</h3>
                        <p class="text-white-50">Remote Access Control Panel</p>
                    </div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('admin.login') }}">
                        @csrf

                        <!-- Email Field -->
                        <div class="form-floating mb-3">
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" id="email" placeholder=" "
                                value="{{ old('email') }}" required autofocus>
                            <label for="email">Email Address</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" id="password"
                                placeholder=" " required>
                            <label for="password">Password</label>
                            <button type="button"
                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-white z-3"
                                style="z-index: 10;" onclick="togglePassword()">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-4 text-white small">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="text-white text-decoration-none hover-underline">Forgot
                                password?</a>
                        </div>

                        <button type="submit" class="btn btn-login btn-lg w-100 text-white">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-white-50">
                            © {{ date('Y') }} Remote Access • All Rights Reserved
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }
    </script>

</body>

</html>
