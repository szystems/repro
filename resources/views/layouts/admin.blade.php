<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

		<!-- Meta -->
		<meta name="description" content="" />
		<meta name="author" content="Szystems" />
		<link rel="canonical" href="https://www.szystems.com">
		<meta property="og:url" content="https://www.szystems.com">
		<meta property="og:title" content="Repro">
		<meta property="og:description" content="Software de manejo de centro de servicios de Car Wash y Taller">
		<meta property="og:type" content="Web App">
		<meta property="og:site_name" content="https://www.repro.com">
		<link rel="shortcut icon" href="assets/imgs/logos/favicon.ico" />

		<!-- Title -->
		<title>{{ config('app.name', 'REPRO') }}</title>

		<!-- *************
			************ Common Css Files *************
		************ -->
		<!-- Bootstrap css -->
        <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/bootstrap.min.css') }}" />

        <!-- Bootstrap font icons css -->
        {{-- <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/fonts/bootstrap/bootstrap-icons.css') }}" /> --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Main css -->
        <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/main.min.css') }}" />

        <!-- Vendor Css Files -->
        <!-- Scrollbar CSS -->
        <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/OverlayScrollbars.min.css') }}" />

        <!-- Date Range CSS -->
        {{-- <link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/vendor/daterange/daterange.css') }}" /> --}}

        <!-- Dropzone CSS -->
        <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" />

        {{-- Datepicker --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <!-- Select2 Bootstrap 5 Theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

        <!-- jQuery (Necesario para JavaScript plugins) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- Bootstrap Bundle JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Datepicker JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- CKeditor 5 -->
        {{-- <script src="{{ asset('assets/ckeditor5/ckeditor.js') }}"></script> --}}

        <style>
            .custom-bg {
                background-color: #b6becc; /* Un color gris claro */
            }
            
            /* Asegurar que la paginación tenga espacio suficiente */
            .content-wrapper {
                padding-bottom: 50px;
            }

            /* Evitar scrollbars duplicados cuando una vista anida otro .content-wrapper-scroll
               dentro del que ya pone el layout. El externo conserva su scroll; el interno
               se vuelve transparente. */
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

            /* === Fase 8 (UI1, UI2, UI3): scroll natural del navegador y footer anclado === */
            html, body {
                overflow-x: hidden;
            }
            /* Eliminar scrollbar interno del content-wrapper para dejar solo la del navegador */
            .content-wrapper {
                height: auto !important;
                overflow: visible !important;
                flex: 1 0 auto;
            }
            /* El wrapper de las vistas tampoco debe scrollear: se delega al documento */
            .content-wrapper-scroll {
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }
            /* main-container como flex column para anclar el footer al final */
            .page-wrapper .main-container {
                height: auto !important;
                min-height: calc(100vh - 65px);
                display: flex;
                flex-direction: column;
            }
            /* Footer: que se empuje al final cuando el contenido es corto */
            .app-footer {
                margin-top: auto;
                flex-shrink: 0;
            }
            /* Navbar fijo al top: se mantiene visible al hacer scroll */
            .page-header {
                position: sticky;
                top: 0;
                z-index: 1030;
            }
            /* Bajo el breakpoint xl, cualquier col-xl-N debe apilarse al 100% para
               evitar que el flex item se encoja al ancho del contenido. */
            @media (max-width: 1199.98px) {
                [class*="col-xl-"]:not([class*="col-lg-"]):not([class*="col-md-"]):not([class*="col-sm-"]):not([class*="col-12"]) {
                    flex: 0 0 auto;
                    width: 100%;
                }
            }
            /* Muchas vistas anidan otro .content-wrapper dentro de .content-wrapper-scroll,
               lo que duplica el padding 30/30 del template. Como el .content-wrapper externo
               (del layout) ya aporta el padding, el interno debe quedar sin padding/altura. */
            .content-wrapper-scroll .content-wrapper {
                padding: 0 !important;
                height: auto !important;
                overflow: visible !important;
            }
        </style>

        @stack('styles')

	</head>

	<body>

		<!-- Loading wrapper start -->
		{{-- <div id="loading-wrapper">
			<div class="spinner">
				<div class="line1"></div>
				<div class="line2"></div>
				<div class="line3"></div>
				<div class="line4"></div>
				<div class="line5"></div>
				<div class="line6"></div>
			</div>
		</div> --}}
		<!-- Loading wrapper end -->

		<!-- Page wrapper start -->
		<div class="page-wrapper">

			@include('layouts.incadmin.nav')

			<!-- Main container start -->
			<div class="main-container">

				@include('layouts.incadmin.sidebar')

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    @yield('content')
                </div>

				@include('layouts.incadmin.footer')

			</div>
			<!-- Main container end -->

		</div>
		<!-- Page wrapper end -->

		<!-- Required JavaScript Files -->
        <!-- Bootstrap Bundle JS -->
        <script src="{{ asset('dashboardtemplate/design/assets/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('dashboardtemplate/design/assets/js/modernizr.js') }}"></script>
        <script src="{{ asset('dashboardtemplate/design/assets/js/moment.js') }}"></script>

        <!-- Vendor Js Files -->
        <!-- Overlay Scroll JS -->
        <script src="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/custom-scrollbar.js') }}?v=20260507"></script>

        <!-- News ticker -->
        <script src="{{ asset('dashboardtemplate/design/assets/vendor/newsticker/newsTicker.min.js') }}"></script>
        <script src="{{ asset('dashboardtemplate/design/assets/vendor/newsticker/custom-newsTicker.js') }}"></script>

        <!-- Dropzone JS -->
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

        <!-- Main Js Required -->
        <script src="{{ asset('dashboardtemplate/design/assets/js/main.js') }}"></script>

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
                document.getElementById('reloj').textContent = `${fechaFormateada} ${horaFormateada}`;
            }

            // Actualiza la hora y la fecha cada segundo
            setInterval(actualizarReloj, 1000);
        </script>
	</body>

</html>
