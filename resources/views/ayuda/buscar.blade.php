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
                        <li class="breadcrumb-item active">Búsqueda</li>
                    </ol>
                </nav>
                <h3 class="page-title"><i class="bi bi-search me-2"></i>Buscar en ayuda</h3>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <form action="{{ route('ayuda.buscar') }}" method="GET" class="input-group">
                <input type="search" name="q" class="form-control" value="{{ $q }}" placeholder="Ej: enlace, Word, crear orden…" autofocus>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
    </div>

    @if($q !== '')
        @if($resultados->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-search me-2"></i>No se encontraron artículos para «{{ $q }}». Pruebe con otras palabras o consulte el <a href="{{ route('ayuda.faq') }}">FAQ</a>.
        </div>
        @else
        <p class="text-muted">{{ $resultados->count() }} resultado(s) para «{{ $q }}»</p>
        <div class="row g-3">
            @foreach($resultados as $art)
            <div class="col-md-6">
                @include('ayuda.partials.articulo-card', ['articulo' => $art])
            </div>
            @endforeach
        </div>
        @endif
    @endif
</div>
@endsection
