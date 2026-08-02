{{--
  Contenido unificado de sección (admin REPRO + portal empresa).
  Variables: $cuestionario, $numeroSeccion, $nombreSeccion, $soloEmpresa (bool), $completada, $ocultarEstadoSeccion, $fotoCandidatoUrl
--}}
@php
    use App\Support\CuestionarioPresentacionDashboard;

    $tipo = $cuestionario->tipo_formulario ?? 'preempleo';
    $soloEmpresa = $soloEmpresa ?? false;
    $respuestas = $respuestas ?? CuestionarioPresentacionDashboard::respuestasSeccion($cuestionario, $numeroSeccion, $soloEmpresa);
    $tablas = $tablas ?? CuestionarioPresentacionDashboard::tablasSeccion($cuestionario, $numeroSeccion, $soloEmpresa);
    $bloquesPreguntas = CuestionarioPresentacionDashboard::bloquesPreguntas($numeroSeccion, $tipo, $soloEmpresa);
    $camposEscalares = CuestionarioPresentacionDashboard::camposEscalares($numeroSeccion, $tipo, $soloEmpresa);
    $tablasConfig = CuestionarioPresentacionDashboard::tablasConfig($numeroSeccion, $tipo, $soloEmpresa);
    $clavesConocidas = CuestionarioPresentacionDashboard::clavesEnPreguntasOBloques($numeroSeccion, $tipo, $soloEmpresa);
    $tieneContenido = count($respuestas) > 0 || count($tablas) > 0;
@endphp

<div class="section-content">
    @if(!($ocultarEstadoSeccion ?? false))
        @if($completada ?? false)
            <div class="alert alert-success mb-3"><i class="bi bi-check-circle-fill"></i> Sección completada</div>
        @else
            <div class="alert alert-warning mb-3"><i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta</div>
        @endif
    @endif

    <h5 class="section-title mb-4">
        <i class="bi bi-file-text"></i> {{ $nombreSeccion ?? 'Sección '.$numeroSeccion }}
    </h5>

    @if($numeroSeccion === 1 && !empty($fotoCandidatoUrl))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Fotografía del candidato</h6></div>
            <div class="card-body text-center">
                <img src="{{ $fotoCandidatoUrl }}" alt="Foto candidato" class="img-fluid rounded border" style="max-height: 220px;">
            </div>
        </div>
    @endif

    @if(!$tieneContenido && empty($bloquesPreguntas))
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle"></i> No hay información registrada en esta sección.
        </div>
    @else
        @include('shared.cuestionario.bloques.campos-escalares', [
            'campos' => $camposEscalares,
            'respuestas' => $respuestas,
        ])

        @include('shared.cuestionario.bloques.tablas-seccion', [
            'configs' => $tablasConfig,
            'tablas' => $tablas,
        ])

        @include('shared.cuestionario.bloques.lista-preguntas', [
            'bloques' => $bloquesPreguntas,
            'respuestas' => $respuestas,
        ])

        @if($numeroSeccion === 6 && ($tipo ?? '') === 'socioeconomico')
            @if(!empty($respuestas['bienes_total']))
                <p class="mb-2"><strong>Total bienes:</strong> Q{{ number_format((float) $respuestas['bienes_total'], 2) }}</p>
            @endif
            @if(!empty($respuestas['presupuesto_total']))
                <p class="mb-2"><strong>Total presupuesto mensual:</strong> Q{{ number_format((float) $respuestas['presupuesto_total'], 2) }}</p>
            @endif
        @endif

        @php
            $otros = collect($respuestas)->filter(function ($valor, $campo) use ($clavesConocidas) {
                return ! in_array($campo, $clavesConocidas, true) && ($valor ?? '') !== '';
            });
        @endphp
        @if($otros->isNotEmpty() && ! $soloEmpresa)
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Otros campos registrados</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-striped mb-0">
                        @foreach($otros as $campo => $valor)
                            <tr>
                                <td class="fw-bold" style="width: 35%;">{{ \App\Support\CuestionarioPresentacionDashboard::etiquetaCampo($campo) }}</td>
                                <td>{!! nl2br(e(is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : $valor)) !!}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
