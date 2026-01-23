@extends('layouts.app')

@section('content')
    <body class="login-container">
        <!-- Register box start -->
        <div class="container">
            <form method="POST" action="{{ route('register') }}">
            @csrf
                <div class="login-box rounded-2 p-5">
                    <div class="login-form">
                        <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                            <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                            </div>
                        </a>
                        <h3 class="fw-bold text-center mb-3"><strong><u>Registro de usuario</u></strong></h3>
                        <h5 class="fw-light mb-3">
                            Complete los siguientes datos para crear una nueva cuenta.
                        </h5>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Ingrese su nombre">
                            @error('name')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Ingrese su correo">
                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Ingrese su contraseña">
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

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirme su contraseña">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid pt-3">
                            <button type="submit" class="btn btn-lg btn-primary" id="btn-registrar">
                                <span class="btn-text">Registrar cuenta</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Procesando...
                                </span>
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-blue">Iniciar sesión</a></p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Register box end -->
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
            
            // Protección contra doble clic en registro
            const form = document.querySelector('form[action*="register"]');
            const btnRegistrar = document.getElementById('btn-registrar');
            let formSubmitting = false;
            
            if (form && btnRegistrar) {
                form.addEventListener('submit', function(e) {
                    if (formSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    
                    formSubmitting = true;
                    btnRegistrar.disabled = true;
                    btnRegistrar.querySelector('.btn-text').classList.add('d-none');
                    btnRegistrar.querySelector('.btn-loading').classList.remove('d-none');
                    
                    return true;
                });
            }
        </script>
    </body>
@endsection
