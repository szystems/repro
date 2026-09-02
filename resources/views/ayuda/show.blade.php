@extends(session('layout', 'layouts.admin'))

@push('styles')
@include('ayuda.partials.styles')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('ayuda.index') }}">Centro de Ayuda</a></li>
                        <li class="breadcrumb-item active">{{ $articulo['titulo'] }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    @foreach(\App\Support\AyudaSupport::audienciaChips($articulo) as $chip)
                        @if($chip === 'ambos')
                            <span class="ayuda-chip ayuda-chip-ambos"><i class="bi bi-people me-1"></i>REPRO + Cliente</span>
                        @elseif($chip === 'repro')
                            <span class="ayuda-chip ayuda-chip-repro"><i class="bi bi-shield-check me-1"></i>REPRO</span>
                        @else
                            <span class="ayuda-chip ayuda-chip-empresa"><i class="bi bi-building me-1"></i>Cliente</span>
                        @endif
                    @endforeach
                    <span class="badge bg-light text-muted border">
                        <i class="bi bi-clock me-1"></i>{{ \App\Support\AyudaSupport::tiempoLectura($articulo) }} min lectura
                    </span>
                    @if(!empty($articulo['modulo']))
                    <span class="badge bg-light text-muted border">
                        <i class="bi {{ \App\Support\AyudaSupport::moduloIcono($articulo['modulo']) }} me-1"></i>
                        {{ \App\Support\AyudaSupport::moduloLabel($articulo['modulo']) }}
                    </span>
                    @endif
                </div>
                <h3 class="page-title">
                    <i class="bi {{ $articulo['icono'] ?? 'bi-file-text' }} me-2"></i>
                    {{ $articulo['titulo'] }}
                </h3>
                @if(!empty($articulo['resumen']))
                <p class="text-muted">{{ $articulo['resumen'] }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    @if(!empty($articulo['screenshot']) && file_exists(public_path('assets/ayuda/screens/'.$articulo['screenshot'])))
                        @include('ayuda.partials.screenshot', [
                            'src' => $articulo['screenshot'],
                            'alt' => 'Captura: ' . $articulo['titulo'],
                            'caption' => 'Captura real de la pantalla REPRO (datos de ejemplo).',
                        ])
                    @endif

                    @if(!empty($articulo['vista']))
                        @include($articulo['vista'])
                    @else
                        <p class="text-muted">Contenido no disponible.</p>
                    @endif

                    @if(!empty($articulo['botones']))
                        @include('ayuda.partials.botones-tabla', ['botones' => $articulo['botones']])
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ayuda-sidebar-sticky">
                @if(!empty($articulo['secciones']))
                    @include('ayuda.partials.toc', ['secciones' => $articulo['secciones']])
                @endif

                @if(count($relacionados) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-link-45deg me-1"></i> Artículos relacionados</h6>
                    </div>
                    <div class="card-body pt-2">
                        <ul class="list-unstyled mb-0">
                            @foreach($relacionados as $rel)
                            <li class="mb-2">
                                <a href="{{ route('ayuda.show', $rel['slug']) }}">
                                    <i class="bi {{ $rel['icono'] ?? 'bi-chevron-right' }} me-1 small"></i>
                                    {{ $rel['titulo'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h6><i class="bi bi-life-preserver me-1"></i> ¿Aún necesita ayuda?</h6>
                        <p class="small text-muted mb-2">Contacte a REPRO por WhatsApp desde el menú lateral, o consulte las preguntas frecuentes.</p>
                        <a href="{{ route('ayuda.faq') }}" class="btn btn-outline-info btn-sm w-100">Ver FAQ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
