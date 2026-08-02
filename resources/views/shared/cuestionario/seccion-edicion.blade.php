{{--
  Edición unificada de sección (solo admin REPRO).
  Variables: $cuestionario, $numeroSeccion, $nombreSeccion, $fotoCandidatoUrl
--}}
@php
    use App\Support\CuestionarioPresentacionDashboard;

    $tipo = $cuestionario->tipo_formulario ?? 'preempleo';
    $slug = CuestionarioPresentacionDashboard::slugSeccion($cuestionario, $numeroSeccion);
    $respuestas = $respuestas ?? $cuestionario->obtenerRespuestasSeccion($numeroSeccion);
    $tablas = $tablas ?? $cuestionario->getTablasPorNumeroSeccion($numeroSeccion);
    $bloquesPreguntas = CuestionarioPresentacionDashboard::bloquesPreguntas($numeroSeccion, $tipo, false);
    $camposEscalares = CuestionarioPresentacionDashboard::camposEscalares($numeroSeccion, $tipo, false);
    $tablasConfig = CuestionarioPresentacionDashboard::tablasConfig($numeroSeccion, $tipo, false);
@endphp

<div class="section-edit-content">
    <h5 class="mb-3"><i class="bi bi-pencil-square"></i> {{ $nombreSeccion ?? 'Sección '.$numeroSeccion }}</h5>

    @if($numeroSeccion === 1)
        <x-foto-candidato :foto-url="$fotoCandidatoUrl ?? null" :requerido="false" />
    @endif

    @include('shared.cuestionario.bloques.campos-escalares-edicion', [
        'campos' => $camposEscalares,
        'respuestas' => $respuestas,
        'slug' => $slug,
    ])

    @include('shared.cuestionario.bloques.lista-preguntas-edicion', [
        'bloques' => $bloquesPreguntas,
        'respuestas' => $respuestas,
        'slug' => $slug,
    ])

    @if(count($tablasConfig) > 0)
        <div class="alert alert-light border small">
            <i class="bi bi-info-circle"></i>
            Las tablas dinámicas (empleos, referencias, deudas, etc.) se muestran en modo lectura.
            Para corregirlas, use el flujo del candidato o contacte soporte técnico.
        </div>
        @include('shared.cuestionario.bloques.tablas-seccion', [
            'configs' => $tablasConfig,
            'tablas' => $tablas,
        ])
    @endif
</div>
