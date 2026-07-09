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
    <meta property="og:description" content="Sistema de Gestión de Evaluaciones de Polígrafo">
    <meta property="og:type" content="Web App">
    <meta property="og:site_name" content="https://www.repro.com">
    <link rel="shortcut icon" href="assets/imgs/logos/favicon.ico" />

    <!-- Title -->
    <title>{{ config('app.name', 'REPRO') }} - Portal Empresas</title>

    <!-- Bootstrap css -->
    <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/bootstrap.min.css') }}" />

    <!-- Bootstrap font icons css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/main.min.css') }}" />

    <!-- Scrollbar CSS -->
    <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/OverlayScrollbars.min.css') }}" />

    <!-- jQuery (Necesario para JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Custom Empresa Theme CSS -->
    <style>
        :root {
            --primary-color: #28a745;     /* Verde para empresas */
            --secondary-color: #218838;
            --sidebar-bg: #f8f9fa;
            --sidebar-header-bg: #e2f3e5;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            flex: 1 0 auto;
            /* R12: anular height fijo del template para que el body pueda scrollear */
            height: auto !important;
            min-height: calc(100vh - 65px);
        }

        /* Fase 8 (UI1/UI3): el scroll lo gestiona el documento, no este wrapper */
        .content-wrapper-scroll {
            flex: 1;
            overflow: visible;
            padding-bottom: 10px;
        }

        /* Anidamientos legacy: nunca scroll propio */
        .content-wrapper-scroll .content-wrapper-scroll {
            flex: unset;
            overflow: visible;
            padding-bottom: 0;
            height: auto;
        }

        /* Garantizar espacio al chevron del acordeón cuando el contenido interno usa w-100 */
        .accordion-button > .d-flex.w-100 {
            min-width: 0;
        }
        .accordion-button::after {
            flex-shrink: 0;
        }

        /* Colores de badge para estados del proceso */
        .bg-orange  { background-color: #dd5500 !important; color: #fff !important; }
        .bg-purple  { background-color: #7920d1 !important; color: #fff !important; }

        /* Customization for empresa theme */
        .navbar {
            background-color: var(--primary-color) !important;
        }

        .sidebar .sidebar-header {
            background-color: var(--sidebar-header-bg);
        }

        .sidebar .navigation-menu > li.active > a {
            background-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Bajo xl, col-xl-N debe apilarse al 100% para evitar shrink-to-content */
        @media (max-width: 1199.98px) {
            [class*="col-xl-"]:not([class*="col-lg-"]):not([class*="col-md-"]):not([class*="col-sm-"]):not([class*="col-12"]) {
                flex: 0 0 auto;
                width: 100%;
            }
        }
        /* Evitar padding duplicado cuando una vista anida .content-wrapper dentro de .content-wrapper-scroll */
        .content-wrapper-scroll .content-wrapper {
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
        }
        /* Navbar fijo al top: se mantiene visible al hacer scroll.
           Sólo aplica al .page-header del nav (hijo directo de .page-wrapper),
           no a los .page-header del contenido, para evitar solapamiento con dropdowns. */
        .page-wrapper > .page-header {
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        /* Cuestionario: lectura estilizada (portal empresa) */
        .section-content .section-title {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-content .section-title i {
            opacity: 0.85;
        }
        .section-content .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .section-content .table-borderless td:first-child {
            width: 35%;
        }
    </style>
</head>

<body>
    <!-- Page wrapper start -->
    <div class="page-wrapper">

        @include('layouts.incempresa.nav')

        <!-- Main container start -->
        <div class="main-container">

            @include('layouts.incempresa.sidebar')

            <!-- Content wrapper -->
            <div class="content-wrapper-scroll">
                @yield('content')
            </div>

        </div>
        <!-- Main container end -->

        @include('layouts.incempresa.footer')

    </div>
    <!-- Page wrapper end -->

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
                        <button type="submit" class="btn btn-success">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Required JavaScript Files -->
    <script src="{{ asset('dashboardtemplate/design/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dashboardtemplate/design/assets/js/modernizr.js') }}"></script>
    <script src="{{ asset('dashboardtemplate/design/assets/js/moment.js') }}"></script>

    <!-- Overlay Scroll JS -->
    <script src="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/custom-scrollbar.js') }}?v=20260507"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Sweet Alert -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    @stack('scripts')

    <script>
        // Asegurarnos que jQuery esté cargado correctamente
        if (typeof jQuery === 'undefined') {
            console.error('jQuery no está cargado. Verifique la inclusión de la biblioteca.');
            document.body.innerHTML = '<div style="text-align:center; margin-top:100px; color:red; font-size:24px;">Error: jQuery no está cargado. Por favor actualice la página o contacte al administrador.</div>';
        }

        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });
    </script>

    @if (session('status'))
    <script>
        swal("{{ session('status') }}");
    </script>
    @endif

    {{-- Hora y fecha --}}
    <script>
        function actualizarReloj() {
            const ahora = new Date();
            const horas = ahora.getHours();
            const minutos = ahora.getMinutes();
            const segundos = ahora.getSeconds();

            // Calcula si es AM o PM
            const amPm = horas >= 12 ? 'PM' : 'AM';
            const hora12 = horas % 12 || 12; // Convierte a formato de 12 horas

            // Formatea la hora con dos dígitos
            const horaFormateada = `${hora12.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')} ${amPm}`;

            // Obtiene la fecha actual
            const dia = ahora.getDate();
            const mes = ahora.getMonth() + 1; // Los meses en JavaScript son base 0 (enero = 0)
            const anio = ahora.getFullYear();
            const fechaFormateada = `${dia}/${mes}/${anio}`;

            // Actualiza el contenido del elemento con la fecha y la hora
            if (document.getElementById('reloj')) {
                document.getElementById('reloj').textContent = `${fechaFormateada} ${horaFormateada}`;
            }
        }

        // Actualiza la hora y la fecha cada segundo
        setInterval(actualizarReloj, 1000);
        actualizarReloj(); // Iniciar inmediatamente
    </script>
</body>
</html>
