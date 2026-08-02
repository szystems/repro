{{-- Cuerpo agrupado del PDF de cuestionario para empresa (sin campos internos). --}}
@php
    use App\Support\CuestionarioPresentacionEmpresa;
    use App\Support\InformacionComplementaria;
    use App\Support\TablaDinamica;

    $cuestionario = $evaluado->cuestionario;
    $seccionesConfig = $cuestionario->getSeccionesConfig();
@endphp

@if($cuestionario && $seccionesConfig)
    @foreach($seccionesConfig as $numeroSeccion => $nombreSeccion)
        @php
            $respuestasSeccion = CuestionarioPresentacionEmpresa::respuestasSeccion($cuestionario, $numeroSeccion);
            $tablasSeccion = CuestionarioPresentacionEmpresa::tablasSeccion($cuestionario, $numeroSeccion);
            $tieneContenido = count($respuestasSeccion) > 0 || count($tablasSeccion) > 0;
        @endphp
        <div class="seccion">
            <div class="seccion-titulo">
                Sección {{ $numeroSeccion }}: {{ $nombreSeccion }}
            </div>

            @if(!$tieneContenido)
                <p class="vacio" style="padding: 8px;">Esta sección no tiene datos visibles para la empresa.</p>
            @else
                @if($numeroSeccion === 1)
                    <div class="subseccion-titulo">Información personal y contacto</div>
                @elseif($numeroSeccion === 2)
                    <div class="subseccion-titulo">Composición familiar</div>
                @elseif($numeroSeccion === 3)
                    <div class="subseccion-titulo">Formación y experiencia laboral</div>
                @elseif($numeroSeccion === 4)
                    <div class="subseccion-titulo">Resumen económico</div>
                @elseif($numeroSeccion === 5)
                    @if(in_array($cuestionario->tipo_formulario, ['periodica', 'especifica'], true))
                        <div class="subseccion-titulo">Aspecto judicial</div>
                    @else
                        <div class="subseccion-titulo">Información complementaria visible</div>
                    @endif
                @elseif($numeroSeccion === 6)
                    <div class="subseccion-titulo">Información socioeconómica complementaria</div>
                @endif

                @php
                    if ($numeroSeccion === 6) {
                        $camposEnTabla = [
                            'referencias_familiares', 'referencias_personales', 'referencias_vecinales',
                            'referencias_laborales', 'bienes', 'presupuesto',
                        ];
                        $compKeys = [];
                        $tablasPdf = [
                            'referencias_familiares' => [TablaDinamica::class, 'columnasReferenciasFamiliares', 'Referencias familiares'],
                            'referencias_personales' => [TablaDinamica::class, 'columnasReferenciasPersonales', 'Referencias personales'],
                            'referencias_laborales' => [TablaDinamica::class, 'columnasReferenciasLaborales', 'Referencias laborales'],
                            'bienes' => [TablaDinamica::class, 'columnasBienes', 'Bienes y pertenencias'],
                            'presupuesto' => [TablaDinamica::class, 'columnasPresupuesto', 'Presupuesto personal'],
                        ];
                    } else {
                        $camposEnTabla = ['hijos', 'hermanos', 'formacion_academica', 'empleos', 'empleo_actual', 'deudas', 'tatuajes', 'perforaciones'];
                        $compKeys = array_column(InformacionComplementaria::PREGUNTAS, 'key');
                        $tablasPdf = [
                            'hijos' => [TablaDinamica::class, 'columnasHijos', 'Detalle de hijos'],
                            'hermanos' => [TablaDinamica::class, 'columnasHermanos', 'Hermanos'],
                            'formacion_academica' => [TablaDinamica::class, 'columnasFormacionAcademica', 'Formación académica'],
                            'empleos' => [TablaDinamica::class, 'columnasEmpleos', 'Historial de empleos'],
                            'empleo_actual' => [TablaDinamica::class, 'columnasEmpleoActualPeriodico', 'Empleo actual'],
                            'deudas' => [TablaDinamica::class, 'columnasDeudas', 'Detalle de deudas'],
                            'tatuajes' => [TablaDinamica::class, 'columnasTatuajes', 'Tatuajes'],
                            'perforaciones' => [TablaDinamica::class, 'columnasPerforaciones', 'Perforaciones'],
                        ];
                    }
                    $camposExcluidos = array_merge($camposEnTabla, $compKeys);
                @endphp

                @if(count($respuestasSeccion) > 0)
                    <table class="datos-table">
                        @foreach($respuestasSeccion as $campo => $valor)
                            @if(in_array($campo, $camposEnTabla, true))
                                @continue
                            @endif
                            @if(in_array($campo, $compKeys, true))
                                @continue
                            @endif
                            @php
                                $etiqueta = ucfirst(str_replace('_', ' ', $campo));
                                $valorFormateado = $valor;
                                if (empty($valor)) {
                                    $valorFormateado = null;
                                } elseif (in_array(strtolower((string) $valor), ['si', 'sí', '1', 'true'], true)) {
                                    $valorFormateado = 'Sí';
                                } elseif (in_array(strtolower((string) $valor), ['no', '0', 'false'], true)) {
                                    $valorFormateado = 'No';
                                } elseif ((str_contains($campo, 'ingreso') || str_contains($campo, 'gasto') ||
                                    str_contains($campo, 'salario') || str_contains($campo, 'monto') ||
                                    str_contains($campo, 'balance') || str_contains($campo, 'total')) && is_numeric($valor)) {
                                    $valorFormateado = 'Q' . number_format((float) $valor, 2);
                                } else {
                                    $valorFormateado = ucfirst(str_replace('_', ' ', (string) $valor));
                                }
                            @endphp
                            <tr>
                                <th>{{ $etiqueta }}:</th>
                                <td class="{{ empty($valorFormateado) ? 'vacio' : '' }}">
                                    {!! $valorFormateado ? nl2br(e($valorFormateado)) : 'No proporcionado' !!}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                @foreach($tablasPdf as $campoTabla => [$clase, $metodo, $titulo])
                    @if(!empty($tablasSeccion[$campoTabla]))
                        <div class="subseccion-titulo">{{ $titulo }}</div>
                        @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                            'filas' => $tablasSeccion[$campoTabla],
                            'columnas' => $clase::$metodo(),
                            'tableClass' => 'datos-table',
                        ])
                    @endif
                @endforeach

                @php
                    $esSeccion5Judicial = $numeroSeccion === 5
                        && in_array($cuestionario->tipo_formulario, ['periodica', 'especifica'], true);
                    $tieneComplementaria = ! $esSeccion5Judicial
                        && collect($compKeys)->contains(fn ($k) => !empty($respuestasSeccion[$k] ?? null));
                @endphp
                @if($tieneComplementaria && $numeroSeccion === 5)
                    <div class="subseccion-titulo">Información complementaria</div>
                    <table class="datos-table">
                        @foreach(InformacionComplementaria::PREGUNTAS as $p)
                            @if(!empty($respuestasSeccion[$p['key']] ?? null))
                                <tr>
                                    <th>{{ $p['label'] }}</th>
                                    <td>{!! nl2br(e($respuestasSeccion[$p['key']])) !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @endif
            @endif
        </div>
    @endforeach
@endif
