{{--
  Contenido de sección para PDF admin REPRO (capa unificada CuestionarioPresentacionDashboard).
  Variables: $cuestionario, $numeroSeccion, $soloEmpresa (bool, default false)
--}}
@php
    use App\Support\CuestionarioPresentacionDashboard;
    use App\Support\TablaDinamica;

    $tipo = $cuestionario->tipo_formulario ?? 'preempleo';
    $soloEmpresa = $soloEmpresa ?? false;
    $respuestas = CuestionarioPresentacionDashboard::respuestasSeccion($cuestionario, $numeroSeccion, $soloEmpresa);
    $tablas = CuestionarioPresentacionDashboard::tablasSeccion($cuestionario, $numeroSeccion, $soloEmpresa);
    $bloquesPreguntas = CuestionarioPresentacionDashboard::bloquesPreguntas($numeroSeccion, $tipo, $soloEmpresa);
    $camposEscalares = CuestionarioPresentacionDashboard::camposEscalares($numeroSeccion, $tipo, $soloEmpresa);
    $tablasConfig = CuestionarioPresentacionDashboard::tablasConfig($numeroSeccion, $tipo, $soloEmpresa);
    $clavesConocidas = CuestionarioPresentacionDashboard::clavesEnPreguntasOBloques($numeroSeccion, $tipo, $soloEmpresa);
    $tieneContenido = count($respuestas) > 0 || count($tablas) > 0;

    $formatearValor = function ($campo, $valor) {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (in_array(strtolower((string) $valor), ['si', 'sí', '1', 'true'], true)) {
            return 'Sí';
        }
        if (in_array(strtolower((string) $valor), ['no', '0', 'false'], true)) {
            return 'No';
        }
        if ((str_contains($campo, 'ingreso') || str_contains($campo, 'gasto') ||
            str_contains($campo, 'salario') || str_contains($campo, 'monto') ||
            str_contains($campo, 'balance') || str_contains($campo, 'total')) && is_numeric($valor)) {
            return 'Q' . number_format((float) $valor, 2);
        }

        return is_string($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE);
    };
@endphp

@if(!$tieneContenido && empty($bloquesPreguntas) && empty($camposEscalares))
    <table class="datos-table">
        <tr>
            <td class="vacio" style="text-align: center;" colspan="2">
                Esta sección no tiene respuestas registradas.
            </td>
        </tr>
    </table>
@else
    @php
        $escalaresVisibles = collect($camposEscalares)->filter(
            fn ($c) => array_key_exists($c['key'], $respuestas) && ($respuestas[$c['key']] ?? '') !== ''
        );
    @endphp
    @if($escalaresVisibles->isNotEmpty())
        <table class="datos-table">
            @foreach($escalaresVisibles as $campo)
                @php $valorFormateado = $formatearValor($campo['key'], $respuestas[$campo['key']]); @endphp
                <tr>
                    <th>{{ $campo['label'] }}:</th>
                    <td class="{{ empty($valorFormateado) ? 'vacio' : '' }}">
                        {!! $valorFormateado ? nl2br(e($valorFormateado)) : 'No proporcionado' !!}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @foreach($tablasConfig as $config)
        @if(!empty($tablas[$config['key']]))
            <p class="subseccion-titulo" style="font-weight: bold; margin: 16px 0 8px;">{{ $config['titulo'] }}</p>
            @php $columnas = call_user_func([TablaDinamica::class, $config['metodo']]); @endphp
            @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                'filas' => $tablas[$config['key']],
                'columnas' => $columnas,
                'tableClass' => 'datos-table',
            ])
        @endif
    @endforeach

    @foreach($bloquesPreguntas as $bloque)
        @php
            $preguntasVisibles = collect($bloque['preguntas'] ?? [])
                ->filter(fn ($p) => array_key_exists($p['key'], $respuestas) && ($respuestas[$p['key']] ?? '') !== '')
                ->values();
        @endphp
        @if($preguntasVisibles->isNotEmpty())
            <p class="subseccion-titulo" style="font-weight: bold; margin: 16px 0 8px;">{{ $bloque['titulo'] }}</p>
            <table class="datos-table">
                @foreach($preguntasVisibles as $i => $pregunta)
                    @php $valorFormateado = $formatearValor($pregunta['key'], $respuestas[$pregunta['key']]); @endphp
                    <tr>
                        <th>{{ $i + 1 }}. {{ $pregunta['label'] }}</th>
                        <td class="{{ empty($valorFormateado) ? 'vacio' : '' }}">
                            {!! $valorFormateado ? nl2br(e($valorFormateado)) : 'No proporcionado' !!}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach

    @if($numeroSeccion === 6 && $tipo === 'socioeconomico')
        @if(!empty($respuestas['bienes_total']))
            <table class="datos-table">
                <tr>
                    <th>Total bienes:</th>
                    <td>Q{{ number_format((float) $respuestas['bienes_total'], 2) }}</td>
                </tr>
            </table>
        @endif
        @if(!empty($respuestas['presupuesto_total']))
            <table class="datos-table">
                <tr>
                    <th>Total presupuesto mensual:</th>
                    <td>Q{{ number_format((float) $respuestas['presupuesto_total'], 2) }}</td>
                </tr>
            </table>
        @endif
    @endif

    @php
        $otros = collect($respuestas)->filter(function ($valor, $campo) use ($clavesConocidas) {
            return ! in_array($campo, $clavesConocidas, true) && ($valor ?? '') !== '';
        });
    @endphp
    @if($otros->isNotEmpty() && ! $soloEmpresa)
        <p class="subseccion-titulo" style="font-weight: bold; margin: 16px 0 8px;">Otros campos registrados</p>
        <table class="datos-table">
            @foreach($otros as $campo => $valor)
                @php $valorFormateado = $formatearValor($campo, $valor); @endphp
                <tr>
                    <th>{{ CuestionarioPresentacionDashboard::etiquetaCampo($campo) }}:</th>
                    <td>{!! $valorFormateado ? nl2br(e($valorFormateado)) : 'No proporcionado' !!}</td>
                </tr>
            @endforeach
        </table>
    @endif
@endif
