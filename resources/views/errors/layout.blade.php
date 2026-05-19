<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Error') — Repro</title>
    <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 480px;
            width: 100%;
        }
        .error-logo {
            max-width: 200px;
            margin-bottom: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            color: #0d6efd;
            opacity: .15;
            margin-bottom: -1.2rem;
        }
        .error-icon {
            font-size: 3.5rem;
            color: #0d6efd;
            margin-bottom: .75rem;
        }
        .error-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: .5rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: .95rem;
        }
        .btn-home {
            background-color: #0d6efd;
            color: #fff;
            border: none;
            padding: .6rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-home:hover { background-color: #0b5ed7; color: #fff; }
        .btn-back {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            padding: .6rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s;
            cursor: pointer;
        }
        .btn-back:hover { background-color: #e9ecef; color: #495057; }
    </style>
</head>
<body>
    <div class="error-card">
        <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Repro" class="error-logo">

        <div class="error-code">@yield('codigo')</div>
        <div class="error-icon"><i class="bi @yield('icono')"></i></div>
        <div class="error-title">@yield('titulo')</div>
        <p class="error-message">@yield('mensaje')</p>

        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ url('/dashboard') }}" class="btn-home">
                <i class="bi bi-house me-1"></i> Ir al inicio
            </a>
            <button onclick="history.back()" class="btn-back">
                <i class="bi bi-arrow-left me-1"></i> Regresar
            </button>
        </div>
    </div>
</body>
</html>
