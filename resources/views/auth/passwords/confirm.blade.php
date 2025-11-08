@extends('layouts.app')

@section('content')

    <body class="login-container">
        <!-- Login box start -->
        <div class="container">
            <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
                <div class="login-box rounded-2 p-5">
                    <div class="login-form">
                        <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                            <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                            </div>
                        </a>
                        <h3 class="fw-bold text-center mb-3"><strong><u>Confirmar contraseña</u></strong></h3>

                        @if (session('status'))
                            <div class="alert alert-success mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                            </div>
                        @endif

                        <h5 class="fw-light mb-3">
                            Por favor confirme su contraseña para continuar
                        </h5>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
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
                        <div class="d-grid pt-3">
                            <button type="submit" class="btn btn-lg btn-primary">
                                Confirmar Contraseña
                            </button>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            @if (Route::has('password.request'))
                                <a class="text-blue" href="{{ route('password.request') }}">
                                    <i class="bi bi-question-circle me-1"></i> ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Login box end -->
        <script>
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
        </script>
    </body>

@endsection
