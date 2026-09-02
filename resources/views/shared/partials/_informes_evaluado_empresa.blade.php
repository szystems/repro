{{--
    Informes liberados para el portal empresa (final > preliminar texto > preliminar PDF).
    No confundir con el PDF del formulario candidato — ver _formulario_candidato_empresa.blade.php
--}}
@if($evaluado->resultadosDisponiblesParaEmpresa())
    @if($evaluado->archivo_resultado_final)
    <div class="card border-success mb-3">
        <div class="card-header bg-success bg-opacity-10 py-2">
            <h6 class="mb-0 text-success">
                <i class="bi bi-file-earmark-check"></i> Informe Final
                <span class="badge bg-success ms-2">Disponible</span>
            </h6>
        </div>
        <div class="card-body py-2">
            <p class="text-muted small mb-2 mb-md-0">Documento definitivo de la evaluación preparado por REPRO.</p>
            <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'final']) }}" class="btn btn-success btn-sm" target="_blank">
                <i class="bi bi-download"></i> Descargar Informe Final
            </a>
        </div>
    </div>
    @endif

    @if($evaluado->texto_informe_preliminar)
    <div class="card border-info mb-3">
        <div class="card-header bg-info bg-opacity-10 py-2">
            <h6 class="mb-0 text-info">
                <i class="bi bi-file-earmark-text"></i> Informe Preliminar / Observaciones
            </h6>
        </div>
        <div class="card-body">
            <div class="border rounded p-3 bg-light">
                {!! $evaluado->texto_informe_preliminar !!}
            </div>
        </div>
    </div>
    @endif

    @if(!$evaluado->archivo_resultado_final && $evaluado->archivo_resultado_preliminar)
    <div class="card border-info mb-3">
        <div class="card-header bg-info bg-opacity-10 py-2">
            <h6 class="mb-0 text-info">
                <i class="bi bi-file-earmark-arrow-down"></i> Informe Preliminar
            </h6>
        </div>
        <div class="card-body py-2">
            <p class="text-muted small mb-2 mb-md-0">Documento preliminar de la evaluación preparado por REPRO.</p>
            <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'preliminar']) }}" class="btn btn-outline-info btn-sm" target="_blank">
                <i class="bi bi-download"></i> Descargar Informe Preliminar
            </a>
        </div>
    </div>
    @endif
@endif
