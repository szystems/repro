<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} — Cuestionario REPRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/cuestionario.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --repro-yellow: #ffb000;
            --repro-blue: #000555;
            --repro-light-yellow: #ffcc33;
        }
        .card-header.repro-header { background-color: var(--repro-blue); }
        .card-header.repro-header h3 { color: var(--repro-yellow); }
        .logo-container {
            background-color: #f8f9fa;
            border: 1px solid var(--repro-blue);
            border-radius: 6px;
            padding: 8px 12px;
            display: inline-block;
        }
        .card-footer {
            background: var(--repro-blue) !important;
            color: var(--repro-yellow);
        }
        .card-footer small { color: var(--repro-light-yellow) !important; }
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
                            <i class="fas fa-link-slash me-2"></i>
                            {{ $titulo }}
                        </h3>
                    </div>

                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas {{ ($motivo ?? '') === 'expirado' ? 'fa-clock' : 'fa-unlink' }} text-warning mb-3" style="font-size: 4rem;"></i>
                            <h4 class="text-dark mb-3">{{ $mensaje }}</h4>
                            <p class="text-muted mb-0">{{ $detalle }}</p>
                        </div>

                        <div class="contact-info mt-4 pt-3 border-top">
                            <p class="small text-muted mb-2"><strong>¿Necesita ayuda?</strong></p>
                            <p class="small text-muted mb-0">
                                Contacte a la empresa que lo evalúa o a REPRO para solicitar un enlace actualizado.
                            </p>
                        </div>
                    </div>

                    <div class="card-footer text-center py-3">
                        <small>© {{ date('Y') }} REPRO — Recursos Profesionales de Guatemala</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
