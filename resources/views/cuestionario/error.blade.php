<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Cuestionario REPRO</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/cuestionario.css') }}" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --repro-yellow: #ffb000;
            --repro-blue: #000555;
            --repro-light-blue: #1a1a6b;
            --repro-light-yellow: #ffcc33;
        }
        
        .card-header.repro-header {
            background-color: var(--repro-blue);
        }
        
        .card-header.repro-header h3 {
            color: var(--repro-yellow);
        }
        
        .logo-container {
            background-color: #f8f9fa;
            border: 1px solid var(--repro-blue);
            border-radius: 6px;
            padding: 8px 12px;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--repro-yellow) 0%, var(--repro-light-yellow) 100%);
            border: 2px solid var(--repro-blue);
            color: var(--repro-blue);
            font-weight: 700;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--repro-light-yellow) 0%, var(--repro-yellow) 100%);
            color: var(--repro-blue);
            border-color: var(--repro-blue);
        }
        
        .card-footer {
            background: var(--repro-blue) !important;
            color: var(--repro-yellow);
        }
        
        .card-footer small {
            color: var(--repro-light-yellow) !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header repro-header text-white text-center py-4">
                        <div class="mb-3">
                            <div class="logo-container">
                                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Logo REPRO" class="img-fluid" style="max-height: 45px;">
                            </div>
                        </div>
                        <h3 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error de Acceso
                        </h3>
                    </div>
                    
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div class="error-icon mb-3">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            
                            <h4 class="text-danger mb-3">{{ $mensaje ?? 'Ha ocurrido un error' }}</h4>
                            
                            <p class="text-muted mb-4">
                                {{ $detalle ?? 'Por favor, verifique su información e intente nuevamente.' }}
                            </p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Volver Atrás
                            </a>
                            
                            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fas fa-refresh me-2"></i>
                                Intentar Nuevamente
                            </button>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="contact-info">
                            <p class="small text-muted mb-2">
                                <strong>¿Necesita ayuda?</strong>
                            </p>
                            <p class="small text-muted">
                                Contacte a REPRO al teléfono: <strong>(502) 2XXX-XXXX</strong><br>
                                O envíe un email a: <strong>info@repro.com.gt</strong>
                            </p>
                        </div>
                    </div>
                    
                    <div class="card-footer text-center py-3">
                        <small>
                            © {{ date('Y') }} REPRO - Recursos Profesionales de Guatemala
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>