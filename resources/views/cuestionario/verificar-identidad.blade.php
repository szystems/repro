<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Identidad - Cuestionario REPRO</title>
    
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
        
        .btn-primary:disabled {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        .text-primary {
            color: var(--repro-blue) !important;
        }
        
        .verification-icon i {
            color: var(--repro-yellow);
        }
        
        .card-footer {
            background: var(--repro-blue);
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
                            <i class="fas fa-shield-alt me-2"></i>
                            Verificación de Identidad
                        </h3>
                    </div>
                    
                    <div class="card-body py-5">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <div class="text-center mb-4">
                            <div class="verification-icon mb-3">
                                <i class="fas fa-user-check text-primary" style="font-size: 3rem;"></i>
                            </div>
                            
                            <h4 class="text-primary mb-3">Bienvenido/a</h4>
                            
                            <p class="text-muted mb-4">
                                Para continuar con su cuestionario socioeconómico, necesitamos verificar su identidad.
                                Por favor ingrese su <strong>Documento Personal de Identificación (DPI)</strong>.
                            </p>
                        </div>
                        
                        <form action="{{ route('cuestionario.verificar', $token) }}" method="POST" id="formVerificar">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="dpi_ingresado" class="form-label fw-bold">
                                    <i class="fas fa-id-card me-2"></i>
                                    Número de DPI
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg text-center @error('dpi_ingresado') is-invalid @enderror" 
                                       id="dpi_ingresado" 
                                       name="dpi_ingresado" 
                                       placeholder="0000000000000"
                                       maxlength="13"
                                       pattern="[0-9]{13}"
                                       value="{{ old('dpi_ingresado') }}"
                                       required
                                       autocomplete="off">
                                
                                @error('dpi_ingresado')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ingrese los 13 dígitos de su DPI sin espacios ni guiones
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnVerificar">
                                    <i class="fas fa-check me-2"></i>
                                    Verificar Identidad
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="security-info text-center">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-lock me-1"></i>
                                <strong>Su información está protegida</strong>
                            </p>
                            <p class="small text-muted">
                                Este cuestionario es confidencial y cumple con las normas de protección de datos personales.
                            </p>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            © {{ date('Y') }} REPRO - Recursos Profesionales de Guatemala
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dpiInput = document.getElementById('dpi_ingresado');
            const btnVerificar = document.getElementById('btnVerificar');
            const form = document.getElementById('formVerificar');
            
            // Formatear entrada del DPI (solo números)
            dpiInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 13);
                
                // Habilitar/deshabilitar botón
                if (this.value.length === 13) {
                    btnVerificar.disabled = false;
                    btnVerificar.classList.remove('btn-secondary');
                    btnVerificar.classList.add('btn-primary');
                } else {
                    btnVerificar.disabled = true;
                    btnVerificar.classList.remove('btn-primary');
                    btnVerificar.classList.add('btn-secondary');
                }
            });
            
            // Validación en el envío
            form.addEventListener('submit', function(e) {
                const dpi = dpiInput.value;
                
                if (dpi.length !== 13) {
                    e.preventDefault();
                    alert('Por favor ingrese un DPI válido de 13 dígitos.');
                    dpiInput.focus();
                    return false;
                }
                
                // Mostrar loading
                btnVerificar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando...';
                btnVerificar.disabled = true;
            });
            
            // Focus automático
            dpiInput.focus();
            
            // Inicializar estado del botón
            dpiInput.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>