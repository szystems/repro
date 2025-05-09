<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Meta -->
    <meta name="description" content="Sistema de Evaluaciones de Polígrafo" />
    <meta name="author" content="Szystems" />
    <link rel="canonical" href="https://www.szystems.com">
    <meta property="og:url" content="https://www.szystems.com">
    <meta property="og:title" content="Repro">
    <meta property="og:description" content="Sistema de Evaluaciones de Polígrafo">
    <meta property="og:type" content="Web App">
    <meta property="og:site_name" content="https://www.repro.com">
    <link rel="shortcut icon" href="assets/imgs/logos/favicon.ico" />

    <!-- Title -->
    <title>{{ config('app.name', 'REPRO') }} - Evaluado</title>

    <!-- Bootstrap css -->
    <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/bootstrap.min.css') }}" />

    <!-- Bootstrap font icons css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- jQuery (Necesario para JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Custom Evaluado Theme CSS -->
    <style>
        :root {
            --primary-color: #6c757d;     /* Gris para evaluados */
            --secondary-color: #5a6268;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            display: flex;
            flex: 1;
            padding: 20px 0;
        }

        .content-wrapper {
            flex: 1;
        }

        /* Customization for evaluado theme */
        .navbar {
            background-color: var(--primary-color) !important;
        }

        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            border-radius: 8px 8px 0 0;
            background-color: rgba(0, 0, 0, 0.03);
            padding: 15px 20px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Sidebar styles */
        .sidebar-column {
            position: sticky;
            top: 20px;
            height: fit-content;
        }
    </style>
</head>

<body>
    @include('layouts.incevaluado.nav')

    <div class="main-container">
        <div class="container">
            <div class="row">
                <!-- Sidebar - visible en pantallas md y más grandes -->
                <div class="col-md-3 sidebar-column d-none d-md-block">
                    @include('layouts.incevaluado.sidebar')
                </div>

                <!-- Contenido principal -->
                <div class="col-md-9 content-wrapper">
                    @yield('content')
                </div>

                <!-- Sidebar - visible en pantallas pequeñas, al principio del contenido -->
                <div class="col-12 d-block d-md-none mb-4">
                    @include('layouts.incevaluado.sidebar')
                </div>
            </div>
        </div>
    </div>

    @include('layouts.incevaluado.footer')

    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ url('change-password') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Required JavaScript Files -->
    <script src="{{ asset('dashboardtemplate/design/assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Sweet Alert -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    @stack('scripts')

    <script>
        // Asegurarnos que jQuery esté cargado correctamente
        if (typeof jQuery === 'undefined') {
            console.error('jQuery no está cargado. Verifique la inclusión de la biblioteca.');
            document.body.innerHTML = '<div style="text-align:center; margin-top:100px; color:red; font-size:24px;">Error: jQuery no está cargado. Por favor actualice la página o contacte al administrador.</div>';
        }
    </script>

    @if (session('status'))
    <script>
        swal("{{ session('status') }}");
    </script>
    @endif
</body>
</html>
