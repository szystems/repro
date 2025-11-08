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
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }

            .page-wrapper {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                overflow-x: hidden;
                overflow-y: auto;
                height: 100vh;
            }

            .main-container {
                display: flex;
                flex: 1;
                flex-direction: column;
                overflow-x: hidden;
                position: relative;
                overflow: visible;
            }

            .content-wrapper {
                flex: 1 0 auto;
                width: 100%;
                padding-bottom: 10px;
                overflow-y: auto;
                overflow-x: hidden;
                overflow: visible;
            }

            .custom-bg {
                background-color: #b6becc; /* Un color gris claro */
            }

            .app-footer {
                flex-shrink: 0;
                width: 100%;
            }

            /* Ajustes específicos para los contenedores del contenido */
            .container-fluid,
            .container {
                overflow-x: hidden;
            }

            /* Fijar el nav y el footer */
            .page-wrapper {
                display: flex;
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
            }

            .main-container {
                display: flex;
                flex-direction: column;
                flex: 1;
                overflow: hidden;
            }

            .content-wrapper {
                flex: 1;
                overflow-y: auto;
                padding: 10px;
            }

            nav {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
            }

            footer {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
            }

            .main-container {
                margin-top: 60px; /* Altura aproximada del nav */
                margin-bottom: 40px; /* Altura aproximada del footer */
            }
        </style>


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
        <script src="{{ asset('dashboardtemplate/design/assets/vendor/overlay-scroll/custom-scrollbar.js') }}"></script>

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
