<!DOCTYPE html>
<html lang="es">

	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

		<!-- Meta -->
		<meta name="description" content="Repro" />
		<meta name="author" content="Repro" />
		<link rel="canonical" href="{{ url('/') }}">
		<meta property="og:url" content="{{ url('/') }}">
		<meta property="og:title" content="Repro">
		<meta property="og:description" content="Repro">
		<meta property="og:type" content="Website">
		<meta property="og:site_name" content="Repro">
		<link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}" />

		<!-- Title -->
		<title>Repro</title>

		<!-- *************
			************ Common Css Files *************
		************ -->
		<!-- Bootstrap css -->
		<link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/bootstrap.min.css') }}" />

		<!-- Bootstrap font icons css -->
		<link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/fonts/bootstrap/bootstrap-icons.css') }}" />

		<!-- Main css -->
		<link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/main.min.css') }}" />

		<!-- Login css -->
		<link rel="stylesheet" href="{{ asset('dashboardtemplate/design/assets/css/login.css') }}" />

        <style>
            .custom-bg {
                background-color: #b6becc; /* Un color gris claro */
            }

            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 80%;
                color: #dc3545;
            }

            .form-control.is-invalid {
                border-color: #dc3545;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            /* Estilos adicionales para las vistas de autenticación */
            .login-box {
                transition: box-shadow 0.3s ease;
            }

            .login-box:hover {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            }

            .btn-primary {
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
            }
        </style>

        <!-- Auth scripts -->
        <script src="{{ asset('js/auth.js') }}" defer></script>
	</head>

    @yield('content')

</html>
