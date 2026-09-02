@if(!empty($compact))
<a href="{{ route('ayuda.show', $articulo['slug']) }}" class="text-decoration-none d-block p-3 border rounded h-100 ayuda-link-card">
    <i class="bi {{ $articulo['icono'] ?? 'bi-file-text' }} text-primary fs-4 d-block mb-2"></i>
    <strong class="text-dark d-block">{{ $articulo['titulo'] }}</strong>
    <small class="text-muted">{{ Str::limit($articulo['resumen'] ?? '', 80) }}</small>
</a>
@else
<div class="card h-100 ayuda-link-card">
    <div class="card-body">
        <h6><i class="bi {{ $articulo['icono'] ?? 'bi-file-text' }} me-1 text-primary"></i> {{ $articulo['titulo'] }}</h6>
        <p class="small text-muted mb-2">{{ $articulo['resumen'] ?? '' }}</p>
        <a href="{{ route('ayuda.show', $articulo['slug']) }}" class="btn btn-sm btn-outline-primary">Leer guía</a>
    </div>
</div>
@endif
