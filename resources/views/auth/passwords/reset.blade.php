@extends('layouts.app')

@section('content')

    <body class="login-container">
        <!-- Login box start -->
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <form method="POST" action="{{ route('password.update') }}" id="resetForm" novalidate>
            @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="login-box rounded-2 p-5 shadow-sm">
                    <div class="login-form">
                        <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                            <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                            </div>
                        </a>
                        <h3 class="fw-bold text-center mb-3"><strong><u>Recuperar acceso</u></strong></h3>
                        <h5 class="fw-light mb-3">
                            Ingrese los datos siguientes para recuperar el acceso a su cuenta.
                        </h5>
                        <div class="mb-3">
                            <label for="email" class="form-label">Tu Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="mt-2 d-none">
                                <div class="progress" style="height: 5px;">
                                    <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="passwordStrengthText" class="form-text"></small>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirmar nueva contraseña</label>
                            <div class="input-group">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid pt-3">
                            <button type="submit" class="btn btn-lg btn-primary" id="resetButton">
                                <i class="bi bi-key me-2"></i>Restaurar Contraseña
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Login box end -->
        <script>
            // Para la contraseña principal
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

            // Para la confirmación de contraseña
            document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
                const passwordInput = document.getElementById('password-confirm');
                const toggleIcon = document.getElementById('toggleConfirmIcon');

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

            // Password strength meter
            document.getElementById('password').addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrengthBar');
                const strengthText = document.getElementById('passwordStrengthText');
                const strengthContainer = document.getElementById('passwordStrength');

                if (password.length > 0) {
                    strengthContainer.classList.remove('d-none');

                    // Calculate strength
                    let strength = 0;

                    // Length check
                    if (password.length >= 8) strength += 25;

                    // Contains lowercase
                    if (/[a-z]/.test(password)) strength += 25;

                    // Contains uppercase
                    if (/[A-Z]/.test(password)) strength += 25;

                    // Contains number
                    if (/\d/.test(password)) strength += 25;

                    // Update UI
                    strengthBar.style.width = strength + '%';

                    if (strength < 25) {
                        strengthBar.className = 'progress-bar bg-danger';
                        strengthText.textContent = 'Muy débil';
                    } else if (strength < 50) {
                        strengthBar.className = 'progress-bar bg-warning';
                        strengthText.textContent = 'Débil';
                    } else if (strength < 75) {
                        strengthBar.className = 'progress-bar bg-info';
                        strengthText.textContent = 'Buena';
                    } else {
                        strengthBar.className = 'progress-bar bg-success';
                        strengthText.textContent = 'Fuerte';
                    }
                } else {
                    strengthContainer.classList.add('d-none');
                }
            });

            // Form validation
            document.getElementById('resetForm').addEventListener('submit', function(e) {
                let emailInput = document.getElementById('email');
                let passwordInput = document.getElementById('password');
                let confirmInput = document.getElementById('password-confirm');
                let isValid = true;

                // Simple validation
                if (!emailInput.value || !emailInput.value.includes('@')) {
                    emailInput.classList.add('is-invalid');
                    isValid = false;
                }

                if (!passwordInput.value || passwordInput.value.length < 8) {
                    passwordInput.classList.add('is-invalid');
                    isValid = false;
                }

                if (confirmInput.value !== passwordInput.value) {
                    confirmInput.classList.add('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    document.getElementById('resetButton').innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...';
                }
            });
        </script>
    </body>

@endsection
