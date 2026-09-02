@extends(session('layout', 'layouts.admin'))

@push('styles')
@include('ayuda.partials.styles')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h3 class="page-title mb-1"><i class="bi bi-book me-2"></i>Centro de Ayuda</h3>
                    <p class="text-muted mb-0 small">Guías, flujos y respuestas para usar REPRO sin consultoría externa.</p>
                </div>
                <form action="{{ route('ayuda.buscar') }}" method="GET" class="d-flex gap-2" style="min-width: 280px;">
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar en ayuda…" autocomplete="off">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </div>

    @if(count($destacados) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-primary mb-3"><i class="bi bi-star me-1"></i> Recomendado para empezar</h6>
                    <div class="row g-3">
                        @foreach($destacados as $art)
                        <div class="col-md-4">
                            @include('ayuda.partials.articulo-card', ['articulo' => $art, 'compact' => true])
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($porModulo))
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-folder2-open me-2"></i>Por módulo del menú</h5>
        </div>
        @foreach($porModulo as $modKey => $articulosMod)
            @if($articulosMod->isNotEmpty())
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 ayuda-modulo-card">
                    <div class="card-header bg-transparent py-2">
                        <h6 class="mb-0">
                            <i class="bi {{ \App\Support\AyudaSupport::moduloIcono($modKey) }} me-2 text-primary"></i>
                            {{ \App\Support\AyudaSupport::moduloLabel($modKey) }}
                            <span class="badge bg-light text-muted border ms-1">{{ $articulosMod->count() }}</span>
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($articulosMod->take(5) as $art)
                        <a href="{{ route('ayuda.show', $art['slug']) }}" class="list-group-item list-group-item-action py-2">
                            <i class="bi {{ $art['icono'] ?? 'bi-chevron-right' }} me-1 small text-primary"></i>
                            {{ $art['titulo'] }}
                        </a>
                        @endforeach
                        @if($articulosMod->count() > 5)
                        <div class="list-group-item py-2 small text-muted">
                            + {{ $articulosMod->count() - 5 }} más en categorías abajo
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <div class="row g-4">
        @foreach($porCategoria as $catKey => $articulos)
            @if($articulos->isNotEmpty())
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="bi {{ \App\Support\AyudaSupport::categoriaIcono($catKey) }} me-2"></i>
                            {{ \App\Support\AyudaSupport::categoriaLabel($catKey) }}
                        </h5>
                    </div>
                    <div class="card-body pt-2">
                        <div class="list-group list-group-flush">
                            @foreach($articulos as $art)
                            <a href="{{ route('ayuda.show', $art['slug']) }}" class="list-group-item list-group-item-action px-0 border-0 py-2">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi {{ $art['icono'] ?? 'bi-file-text' }} text-primary mt-1"></i>
                                    <div>
                                        <strong>{{ $art['titulo'] }}</strong>
                                        <div class="small text-muted">{{ $art['resumen'] ?? '' }}</div>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <a href="{{ route('ayuda.faq') }}" class="card text-decoration-none h-100 ayuda-link-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-question-circle fs-2 text-info"></i>
                    <div>
                        <h6 class="mb-1 text-dark">Preguntas frecuentes</h6>
                        <small class="text-muted">Respuestas rápidas a dudas comunes</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="{{ route('ayuda.glosario') }}" class="card text-decoration-none h-100 ayuda-link-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-journal-text fs-2 text-secondary"></i>
                    <div>
                        <h6 class="mb-1 text-dark">Glosario</h6>
                        <small class="text-muted">Términos y estados del sistema</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
