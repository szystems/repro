@extends('layouts.app')

@section('content')

    <body class="login-container">
        <!-- Login box start -->
        <div class="container">
            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
            @csrf
                <div class="login-box rounded-2 p-5 shadow-sm">
                    <div class="login-form">

                        <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                            <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                            </div>
                        </a>
                        <h3 class="fw-bold text-center mb-3"><strong><u>Iniciar sesión</u></strong></h3>
                        <h5 class="fw-light mb-3">
                            Coloca tus credenciales para poder iniciar sesión.
                        </h5>

                        <div class="mb-3">
                            <label for="email" class="form-label">Tu Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="Ingresa tu email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <div class="form-text text-muted" id="emailHelp">Nunca compartiremos tu correo con terceros.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Tu Contraseña</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Ingresa tu contraseña" name="password" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Recordar</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-blue text-decoration-underline">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="d-grid py-3">
                            <button type="submit" class="btn btn-lg btn-primary" id="loginButton">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Login box end -->
        <script>
            // Toggle password visibility
            document.getElementById('togglePassword').addEventListener('click', function () {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('toggleIcon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            });

            // Form validation
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                let emailInput = document.getElementById('email');
                let passwordInput = document.getElementById('password');
                let isValid = true;

                // Validate email
                if (!emailInput.value || !emailInput.value.includes('@')) {
                    emailInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    emailInput.classList.remove('is-invalid');
                    emailInput.classList.add('is-valid');
                }

                // Validate password
                if (!passwordInput.value) {
                    passwordInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    passwordInput.classList.remove('is-invalid');
                    passwordInput.classList.add('is-valid');
                }

                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Change button text to show loading state
                    document.getElementById('loginButton').innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Iniciando...';
                }
            });

            // Clear validation when typing
            document.getElementById('email').addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });

            document.getElementById('password').addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        </script>
    </body>

@endsection
