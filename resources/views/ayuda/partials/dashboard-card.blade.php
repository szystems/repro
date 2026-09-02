@php $destacadosAyuda = \App\Support\AyudaSupport::destacadosDashboard(auth()->user()); @endphp
@if(count($destacadosAyuda) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-start border-4 border-info">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <h6 class="mb-0 text-info"><i class="bi bi-book me-1"></i> Centro de Ayuda</h6>
                    <a href="{{ route('ayuda.index') }}" class="btn btn-sm btn-outline-info">Ver todo</a>
                </div>
                <div class="row g-2">
                    @foreach($destacadosAyuda as $art)
                    <div class="col-md-4">
                        <a href="{{ route('ayuda.show', $art['slug']) }}" class="small text-decoration-none d-block">
                            <i class="bi {{ $art['icono'] ?? 'bi-chevron-right' }} me-1"></i>{{ $art['titulo'] }}
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
