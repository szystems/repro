<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ayuda['titulo'] ?? 'Ayuda' }} — REPRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/cuestionario.css') }}" rel="stylesheet">
    <style>
        :root { --repro-blue: #000555; --repro-yellow: #ffb000; }
        .repro-header { background: var(--repro-blue); color: var(--repro-yellow); }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header repro-header py-3">
                    <h4 class="mb-0"><i class="bi bi-question-circle me-2"></i>{{ $ayuda['titulo'] ?? 'Ayuda' }}</h4>
                </div>
                <div class="card-body">
                    @if(!empty($ayuda['intro']))
                    <p class="lead">{{ $ayuda['intro'] }}</p>
                    @endif

                    @foreach($ayuda['secciones'] ?? [] as $sec)
                    <h5 class="mt-4"><i class="bi bi-chevron-right text-primary me-1"></i>{{ $sec['titulo'] }}</h5>
                    <ul>
                        @foreach($sec['puntos'] ?? [] as $punto)
                        <li>{{ $punto }}</li>
                        @endforeach
                    </ul>
                    @endforeach

                    @if(!empty($ayuda['contacto']))
                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>{{ $ayuda['contacto'] }}
                    </div>
                    @endif
                </div>
            </div>
            <p class="text-center mt-3">
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
