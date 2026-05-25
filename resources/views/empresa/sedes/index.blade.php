@extends('layouts.empresa')
@section('content')
<div class="content-wrapper-scroll">
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="page-title">
                <h5>Sedes REPRO</h5>
            </div>
        </div>
    </div>
    <div class="content-wrapper">

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($sedes->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay sedes activas registradas en este momento.
            </div>
        @else
            <div class="row g-4">
                @foreach($sedes as $sede)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-building text-primary fs-5"></i>
                            <strong>{{ $sede->nombre }}</strong>
                        </div>
                        <div class="card-body">
                            @if($sede->direccion)
                            <p class="mb-2">
                                <i class="bi bi-map-fill text-secondary me-1"></i>
                                {{ $sede->direccion }}
                            </p>
                            @endif

                            @if($sede->telefono)
                            <p class="mb-2">
                                <i class="bi bi-telephone-fill text-secondary me-1"></i>
                                <a href="tel:{{ $sede->telefono }}" class="text-decoration-none">{{ $sede->telefono }}</a>
                            </p>
                            @endif

                            @if($sede->whatsapp)
                            <p class="mb-2">
                                <i class="bi bi-whatsapp text-success me-1"></i>
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $sede->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="text-success text-decoration-none">
                                    {{ $sede->whatsapp }}
                                </a>
                            </p>
                            @endif

                            @if($sede->notas)
                            <p class="mb-2 text-muted small">
                                <i class="bi bi-info-circle me-1"></i>{{ $sede->notas }}
                            </p>
                            @endif
                        </div>

                        @if($sede->enlace_maps)
                        <div class="card-footer bg-transparent">
                            <a href="{{ $sede->enlace_maps }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-pin-map-fill me-1"></i> Ver en mapa
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
