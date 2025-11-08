@extends('layouts.app')

@section('content')

    <body class="login-container">
        <!-- Login box start -->
        <div class="container">
            <form method="POST" action="{{ route('password.email') }}">
            @csrf
                <div class="login-box rounded-2 p-5">
                    <div class="login-form">
                        <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                            <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                            </div>
                        </a>
                        <h3 class="fw-bold text-center mb-3"><strong><u>Recuperar acceso</u></strong></h3>

                        @if (session('status'))
                            <div class="alert alert-success mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                            </div>
                        @endif

                        <h5 class="fw-light mb-3">
                            Para acceder a su cuenta, ingrese el correo electrónico que
                            pertenece a su cuenta.
                        </h5>
                        <div class="mb-3">
                            <label for="email" class="form-label">Tu Email</label>
                            <input id="email" type="email" placeholder="Ingresa tu email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="d-grid pt-3">
                            <button type="submit" class="btn btn-lg btn-primary">
                                Enviar enlace de restablecimiento
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-blue">
                                <i class="bi bi-arrow-left me-1"></i> Volver a inicio de sesión
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Login box end -->
    </body>

@endsection
