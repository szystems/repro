<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cuestionario Socioeconómico - REPRO')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Cuestionario Custom CSS -->
    <link href="{{ asset('css/cuestionario.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --repro-yellow: #ffb000;
            --repro-blue: #000555;
            --repro-light-blue: #1a1a6b;
            --repro-light-yellow: #ffcc33;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Header/Navbar estilo admin */
        .cuestionario-header {
            background: linear-gradient(135deg, var(--repro-blue) 0%, var(--repro-light-blue) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
        }
        
        .navbar-brand:hover {
            color: var(--repro-yellow) !important;
        }
        
        .logo-repro {
            max-height: 40px;
            padding: 6px 10px;
            background-color: #f8f9fa;
            border: 1px solid #000555;
            border-radius: 6px;
        }
        
        .progress-indicator {
            background: rgba(255, 176, 0, 0.2);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .progress-indicator i {
            color: var(--repro-yellow);
        }
        
        /* Contenido principal */
        .main-container {
            min-height: calc(100vh - 80px);
            padding: 2rem 0;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: visible;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--repro-yellow) 0%, var(--repro-light-yellow) 100%);
            color: var(--repro-blue);
            padding: 1.5rem 2rem;
            border-bottom: 3px solid var(--repro-blue);
        }
        
        .form-header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }
        
        .form-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.8;
            font-weight: 500;
        }
        
        .progress-container {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .progress {
            height: 12px;
            border-radius: 6px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--repro-yellow) 0%, var(--repro-light-yellow) 100%);
            border-radius: 6px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            text-align: center;
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: var(--repro-blue);
            font-weight: 600;
        }
        
        .form-content {
            padding: 2.5rem;
        }
        
        .section-title {
            color: var(--repro-blue);
            border-bottom: 3px solid var(--repro-yellow);
            padding-bottom: 0.75rem;
            margin-bottom: 2rem;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title i {
            color: var(--repro-yellow);
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--repro-blue);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--repro-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255, 176, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--repro-yellow) 0%, var(--repro-light-yellow) 100%);
            border: 2px solid var(--repro-blue);
            color: var(--repro-blue);
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--repro-light-yellow) 0%, var(--repro-yellow) 100%);
            color: var(--repro-blue);
            box-shadow: 0 4px 15px rgba(255, 176, 0, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #dee2e6;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-info {
            background-color: rgba(255, 176, 0, 0.1);
            color: var(--repro-blue);
            border-left: 4px solid var(--repro-yellow);
        }
        
        .required {
            color: #dc3545;
        }
        
        .footer-info {
            background: linear-gradient(135deg, var(--repro-blue) 0%, var(--repro-light-blue) 100%);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        
        .section-nav {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #dee2e6;
        }
        
        .section-nav .nav-item {
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .section-nav .nav-link {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        
        .section-nav .nav-link.active {
            background: var(--repro-yellow);
            color: var(--repro-blue);
            border-color: var(--repro-blue);
            font-weight: 600;
        }
        
        .section-nav .nav-link:hover {
            background: rgba(255, 176, 0, 0.1);
            color: var(--repro-blue);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--repro-yellow);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Navbar personalizada para evaluados */
        .evaluado-info {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        
        .evaluado-info strong {
            color: var(--repro-yellow);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .form-content {
                padding: 1.5rem;
            }
            
            .navigation-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .navigation-buttons .btn {
                width: 100%;
            }
            
            .cuestionario-header .container-fluid {
                padding: 0 1rem;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
        }
        
        /* Estilos de impresión para el layout */
        @media print {
            /* Ocultar elementos no necesarios */
            .footer-info,
            .loading-overlay,
            .progress-indicator,
            .evaluado-info {
                display: none !important;
            }
            
            /* Mostrar cabecera pero simplificada */
            .cuestionario-header {
                position: static !important;
                padding: 0.5rem 0 !important;
                margin-bottom: 0.5rem !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .cuestionario-header .container-fluid {
                padding: 0 !important;
            }
            
            .cuestionario-header .navbar-brand {
                font-size: 1rem !important;
            }
            
            .cuestionario-header .logo-repro {
                max-height: 30px !important;
            }
            
            .cuestionario-header .d-none {
                display: none !important;
            }
            
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .main-container {
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Header/Navbar -->
    <div class="cuestionario-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="#" class="navbar-brand d-flex align-items-center">
                        <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="REPRO" class="logo-repro me-3">
                        <span class="d-none d-md-inline">Cuestionario Socioeconómico</span>
                    </a>
                </div>
                
                @if(isset($currentSection) && isset($totalSections))
                <div class="progress-indicator">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Sección {{ $currentSection }} de {{ $totalSections }}</span>
                </div>
                @endif
                
                @if(isset($evaluado))
                <div class="evaluado-info d-none d-lg-block">
                    <div class="text-end">
                        <strong>{{ $evaluado->nombre ?? 'Evaluado' }}</strong>
                        <br>
                        <small>DPI: {{ $evaluado->dpi ?? 'N/A' }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div id="cuestionarioContainer" 
         class="main-container"
         data-token="{{ $token ?? '' }}"
         data-current-section="{{ $currentSection ?? 1 }}"
         data-base-url="{{ url('/cuestionario') }}">
        <div class="container">
            @yield('content')
            
            <div class="footer-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1"><strong>REPRO </strong></p>
                        <p class="mb-0">Este cuestionario es confidencial y será utilizado únicamente para fines laborales</p>
                    </div>
                    <div class="d-none d-md-block">
                        <img src="{{ asset('img/logos/logo.png') }}" alt="REPRO" style="height: 40px; opacity: 0.7;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Cuestionario JavaScript personalizado será incluido abajo -->
    
    <script>
        // Configuración global simplificada
        window.cuestionarioConfig = {
            csrfToken: '{{ csrf_token() }}',
            token: '{{ $token ?? "" }}',
            currentSection: {{ $currentSection ?? 0 }},
            totalSections: {{ $totalSections ?? 5 }}
        };
        
        // Funciones auxiliares básicas
        window.cuestionarioHelpers = {
            showLoading: function() {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) overlay.style.display = 'flex';
            },
            
            hideLoading: function() {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) overlay.style.display = 'none';
            },
            
            showAlert: function(message, type = 'info') {
                const alertDiv = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                const container = document.querySelector('.form-content');
                if (container) {
                    container.insertAdjacentHTML('afterbegin', alertDiv);
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(function() {
                        const alert = container.querySelector('.alert');
                        if (alert && window.bootstrap) {
                            const bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        }
                    }, 5000);
                }
            }
        };
        
        // Validación unificada al enviar (campos requeridos visibles, tablas, foto sección 1)
        window.cuestionarioValidarEnvio = function (form) {
            if (window.CamposCondicionales && typeof window.CamposCondicionales.syncAll === 'function') {
                window.CamposCondicionales.syncAll();
            }
            if (window.TablaDinamica && typeof window.TablaDinamica.removeEmptyRowsAll === 'function') {
                window.TablaDinamica.removeEmptyRowsAll();
            } else if (window.TablaDinamica && typeof window.TablaDinamica.syncAll === 'function') {
                window.TablaDinamica.syncAll();
            }

            function fieldIsHidden(field) {
                if (field.disabled) {
                    return true;
                }

                let node = field;
                while (node && node !== document.body) {
                    if (node.classList && node.classList.contains('d-none')) {
                        return true;
                    }
                    node = node.parentElement;
                }

                return false;
            }

            function fieldLabel(field) {
                const td = field.closest('td[data-label]');
                if (td && td.dataset.label) {
                    return td.dataset.label.replace(/\s*\*$/, '');
                }

                if (field.id) {
                    const label = form.querySelector('label[for="' + field.id + '"]');
                    if (label) {
                        return label.textContent.replace(/\*/g, '').trim();
                    }
                }

                return field.name || 'Campo';
            }

            let valid = true;
            let firstError = null;
            const missingLabels = [];

            form.querySelectorAll('[required]').forEach(function (field) {
                if (fieldIsHidden(field) || field.type === 'file') {
                    field.classList.remove('is-invalid');
                    return;
                }

                const value = String(field.value || '').trim();
                const patternOk = !field.pattern || field.validity.valid || value === '';

                if (!value || !patternOk) {
                    field.classList.add('is-invalid');
                    valid = false;
                    const label = fieldLabel(field);
                    if (missingLabels.indexOf(label) === -1) {
                        missingLabels.push(label);
                    }
                    if (!firstError) {
                        firstError = field;
                    }
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            const fotoInput = form.querySelector('[data-foto-input]');
            const fotoGroup = form.querySelector('[data-foto-candidato]');
            if (fotoInput && fotoGroup) {
                const hasFile = fotoInput.files && fotoInput.files.length > 0;
                const hasExistente = form.querySelector('[name="foto_candidato_existente"]');
                const fotoError = fotoGroup.querySelector('[data-foto-error]');
                if (!hasFile && !hasExistente) {
                    fotoGroup.classList.add('is-invalid');
                    if (fotoError) {
                        fotoError.textContent = 'Debe tomar o subir su fotografía para continuar.';
                        fotoError.style.display = 'block';
                        fotoError.classList.add('d-block');
                    }
                    valid = false;
                    if (missingLabels.indexOf('Fotografía del candidato') === -1) {
                        missingLabels.push('Fotografía del candidato');
                    }
                    // Priorizar la foto si es el único/principal faltante visual cerca del tope
                    if (!firstError) {
                        firstError = fotoGroup;
                    }
                } else {
                    fotoGroup.classList.remove('is-invalid');
                    if (fotoError && !fotoError.dataset.serverError) {
                        fotoError.style.display = 'none';
                        fotoError.classList.remove('d-block');
                    }
                }
            }

            let message = 'Revise los campos marcados en rojo.';
            if (missingLabels.length > 0) {
                const preview = missingLabels.slice(0, 5).join(', ');
                const extra = missingLabels.length > 5 ? '…' : '';
                message = 'Faltan o tienen formato inválido: ' + preview + extra + '.';
            }

            return { valid: valid, firstError: firstError, message: message };
        };

        // Inicialización básica
        document.addEventListener('DOMContentLoaded', function() {
            cuestionarioHelpers.hideLoading();

            const form = document.querySelector('#cuestionarioForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const result = window.cuestionarioValidarEnvio(form);

                    if (!result.valid) {
                        e.preventDefault();
                        cuestionarioHelpers.hideLoading();
                        cuestionarioHelpers.showAlert(result.message || 'Revise los campos marcados en rojo.', 'danger');
                        if (result.firstError && result.firstError.scrollIntoView) {
                            result.firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            if (result.firstError.focus) {
                                result.firstError.focus();
                            }
                        }
                    } else {
                        cuestionarioHelpers.showLoading();
                    }
                });
            }

            window.addEventListener('pageshow', function() {
                cuestionarioHelpers.hideLoading();
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>