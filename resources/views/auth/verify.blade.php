@extends('layouts.app')

@section('content')

    <body class="login-container">
        <!-- Login box start -->
        <div class="container">
            <div class="login-box rounded-2 p-5">
                <div class="login-form">
                    <a href="{{ url('/') }}" class="login-logo mb-3 align-self-center d-flex justify-content-center">
                        <div class="border border-primary custom-bg p-2 rounded" style="width: 100%;">
                            <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro Automotriz" class="img-fluid w-100" />
                        </div>
                    </a>
                    <h3 class="fw-bold text-center mb-3"><strong><u>Verifica tu dirección de email</u></strong></h3>

                    @if (session('resent'))
                        <div class="alert alert-success mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ __('Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.') }}
                        </div>
                    @endif

                    <h5 class="fw-light mb-3">Antes de continuar, revise su correo electrónico para obtener un enlace de verificación.</h5>
                    <h5 class="fw-light mb-3">Si no recibiste el correo electrónico:</h5>

                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary mb-3">
                                <i class="bi bi-envelope me-2"></i> Reenviar el enlace de verificación
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-blue">
                            <i class="bi bi-arrow-left me-1"></i> Volver a inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Login box end -->
    </body>

@endsection
