{{--
  Vista de lectura compartida (portal empresa). Reutiliza partials de admin sin inputs.
  Variables: $cuestionario, $numeroSeccion, $nombreSeccion
--}}
@php
    use App\Support\CuestionarioPresentacionEmpresa;

    $respuestas = CuestionarioPresentacionEmpresa::respuestasSeccion($cuestionario, $numeroSeccion);
    $tablas = CuestionarioPresentacionEmpresa::tablasSeccion($cuestionario, $numeroSeccion);
    $partial = 'admin.cuestionarios.partials.seccion_' . $numeroSeccion;
@endphp

@if(View::exists($partial))
    @include($partial, [
        'cuestionario' => $cuestionario,
        'respuestas' => $respuestas,
        'tablas' => $tablas,
        'nombreSeccion' => $nombreSeccion,
        'completada' => (bool) ($cuestionario->completado ?? $cuestionario->estado === 'completado'),
        'ocultarEstadoSeccion' => true,
        'soloEmpresa' => true,
    ])
@else
    <div class="alert alert-info mb-0">
        <h6 class="mb-2"><i class="bi bi-info-circle"></i> {{ $nombreSeccion }}</h6>
        @if(count($respuestas) > 0)
            <table class="table table-sm table-striped mb-0">
                @foreach($respuestas as $campo => $valor)
                    <tr>
                        <td class="fw-bold" style="width: 30%;">{{ ucfirst(str_replace('_', ' ', $campo)) }}</td>
                        <td>{{ is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : $valor }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <p class="text-muted mb-0">No hay datos registrados en esta sección.</p>
        @endif
    </div>
@endif
